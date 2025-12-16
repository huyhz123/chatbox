<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InventoryService
{
    /**
     * Reserve stock for order
     */
    public function reserveStock($productId, $quantity)
    {
        $product = Product::find($productId);

        if (!$product) {
            throw new \Exception('Product not found');
        }

        if ($product->stock < $quantity) {
            throw new \Exception('Insufficient stock');
        }

        // Use transaction to prevent race conditions
        DB::transaction(function () use ($product, $quantity) {
            $product->decrement('stock', $quantity);

            // Log stock change
            Log::info('Stock reserved', [
                'product_id' => $product->id,
                'quantity' => $quantity,
                'remaining_stock' => $product->stock,
            ]);
        });

        // Check if low stock
        if ($product->stock <= 10) {
            $this->sendLowStockAlert($product);
        }

        return true;
    }

    /**
     * Release stock (e.g., order cancelled)
     */
    public function releaseStock($productId, $quantity)
    {
        $product = Product::find($productId);

        if (!$product) {
            throw new \Exception('Product not found');
        }

        DB::transaction(function () use ($product, $quantity) {
            $product->increment('stock', $quantity);

            Log::info('Stock released', [
                'product_id' => $product->id,
                'quantity' => $quantity,
                'current_stock' => $product->stock,
            ]);
        });

        return true;
    }

    /**
     * Update stock level
     */
    public function updateStock($productId, $newStock, $reason = null)
    {
        $product = Product::find($productId);

        if (!$product) {
            throw new \Exception('Product not found');
        }

        $oldStock = $product->stock;

        $product->update(['stock' => $newStock]);

        Log::info('Stock updated', [
            'product_id' => $product->id,
            'old_stock' => $oldStock,
            'new_stock' => $newStock,
            'reason' => $reason,
        ]);

        // Check if low stock
        if ($newStock <= 10) {
            $this->sendLowStockAlert($product);
        }

        return true;
    }

    /**
     * Get low stock products
     */
    public function getLowStockProducts($threshold = 10)
    {
        return Product::where('stock', '<=', $threshold)
            ->where('is_active', true)
            ->get();
    }

    /**
     * Get out of stock products
     */
    public function getOutOfStockProducts()
    {
        return Product::where('stock', 0)
            ->where('is_active', true)
            ->get();
    }

    /**
     * Send low stock alert
     */
    protected function sendLowStockAlert($product)
    {
        // TODO: Send email or notification to admin
        Log::warning('Low stock alert', [
            'product_id' => $product->id,
            'product_name' => $product->name,
            'current_stock' => $product->stock,
        ]);
    }

    /**
     * Bulk stock update
     */
    public function bulkUpdateStock(array $updates)
    {
        DB::transaction(function () use ($updates) {
            foreach ($updates as $update) {
                $this->updateStock(
                    $update['product_id'],
                    $update['stock'],
                    $update['reason'] ?? 'Bulk update'
                );
            }
        });

        return true;
    }
}
