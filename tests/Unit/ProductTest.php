<?php

namespace Tests\Unit;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function product_can_be_created()
    {
        $product = Product::factory()->create([
            'name' => 'Test Product',
            'sku' => 'TEST-001',
            'stock' => 100,
        ]);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Test Product',
            'sku' => 'TEST-001',
        ]);
    }

    /** @test */
    public function product_has_required_attributes()
    {
        $product = Product::factory()->create([
            'name' => 'Premium Product',
            'price' => 500000,
            'cost' => 200000,
            'stock' => 50,
            'is_active' => true,
        ]);

        $this->assertEquals('Premium Product', $product->name);
        $this->assertEquals(500000, $product->price);
        $this->assertEquals(200000, $product->cost);
        $this->assertEquals(50, $product->stock);
        $this->assertTrue($product->is_active);
    }

    /** @test */
    public function active_scope_filters_active_products()
    {
        Product::factory()->create(['is_active' => true]);
        Product::factory()->create(['is_active' => true]);
        Product::factory()->create(['is_active' => false]);

        $activeProducts = Product::active()->get();

        $this->assertEquals(2, $activeProducts->count());
    }

    /** @test */
    public function featured_scope_filters_featured_products()
    {
        Product::factory()->create(['is_featured' => true]);
        Product::factory()->create(['is_featured' => false]);

        $featuredProducts = Product::featured()->get();

        $this->assertEquals(1, $featuredProducts->count());
        $this->assertTrue($featuredProducts->first()->is_featured);
    }

    /** @test */
    public function in_stock_scope_returns_products_with_stock()
    {
        Product::factory()->create(['stock' => 10, 'stock_status' => 'in_stock']);
        Product::factory()->create(['stock' => 0, 'stock_status' => 'out_of_stock']);
        Product::factory()->create(['stock' => 5, 'stock_status' => 'in_stock']);

        $inStockProducts = Product::inStock()->get();

        $this->assertEquals(2, $inStockProducts->count());
    }

    /** @test */
    public function low_stock_scope_filters_low_stock_products()
    {
        Product::factory()->create(['stock' => 5, 'low_stock_alert' => 10]);
        Product::factory()->create(['stock' => 20, 'low_stock_alert' => 10]);
        Product::factory()->create(['stock' => 10, 'low_stock_alert' => 10]);

        $lowStockProducts = Product::lowStock()->get();

        $this->assertEquals(1, $lowStockProducts->count());
    }

    /** @test */
    public function get_current_price_returns_special_price_if_available()
    {
        $product = Product::factory()->create([
            'price' => 500000,
            'special_price' => 400000,
        ]);

        $this->assertEquals(400000, $product->getCurrentPrice());
    }

    /** @test */
    public function get_current_price_returns_regular_price_if_no_special_price()
    {
        $product = Product::factory()->create([
            'price' => 500000,
            'special_price' => null,
        ]);

        $this->assertEquals(500000, $product->getCurrentPrice());
    }

    /** @test */
    public function get_profit_calculates_correctly()
    {
        $product = Product::factory()->create([
            'price' => 500000,
            'cost' => 200000,
            'special_price' => null,
        ]);

        $this->assertEquals(300000, $product->getProfit());
    }

    /** @test */
    public function get_profit_uses_special_price()
    {
        $product = Product::factory()->create([
            'price' => 500000,
            'special_price' => 400000,
            'cost' => 200000,
        ]);

        $this->assertEquals(200000, $product->getProfit());
    }

    /** @test */
    public function is_in_stock_returns_true_when_in_stock()
    {
        $product = Product::factory()->create([
            'stock' => 10,
            'stock_status' => 'in_stock',
        ]);

        $this->assertTrue($product->isInStock());
    }

    /** @test */
    public function is_in_stock_returns_false_when_out_of_stock()
    {
        $product = Product::factory()->create([
            'stock' => 0,
            'stock_status' => 'out_of_stock',
        ]);

        $this->assertFalse($product->isInStock());
    }

    /** @test */
    public function is_in_stock_returns_false_when_stock_zero()
    {
        $product = Product::factory()->create([
            'stock' => 0,
            'stock_status' => 'in_stock',
        ]);

        $this->assertFalse($product->isInStock());
    }

    /** @test */
    public function is_low_stock_returns_true_when_low()
    {
        $product = Product::factory()->create([
            'stock' => 5,
            'low_stock_alert' => 10,
        ]);

        $this->assertTrue($product->isLowStock());
    }

    /** @test */
    public function is_low_stock_returns_false_when_sufficient()
    {
        $product = Product::factory()->create([
            'stock' => 20,
            'low_stock_alert' => 10,
        ]);

        $this->assertFalse($product->isLowStock());
    }

    /** @test */
    public function decrease_stock_reduces_quantity()
    {
        $product = Product::factory()->create([
            'stock' => 100,
            'stock_status' => 'in_stock',
        ]);

        $product->decreaseStock(30);

        $this->assertEquals(70, $product->refresh()->stock);
    }

    /** @test */
    public function decrease_stock_updates_status_to_out_of_stock_when_zero()
    {
        $product = Product::factory()->create([
            'stock' => 10,
            'stock_status' => 'in_stock',
        ]);

        $product->decreaseStock(10);

        $this->assertEquals('out_of_stock', $product->refresh()->stock_status);
    }

    /** @test */
    public function decrease_stock_updates_status_to_out_of_stock_when_negative()
    {
        $product = Product::factory()->create([
            'stock' => 5,
            'stock_status' => 'in_stock',
        ]);

        $product->decreaseStock(10);

        $this->assertEquals('out_of_stock', $product->refresh()->stock_status);
    }

    /** @test */
    public function increase_stock_adds_quantity()
    {
        $product = Product::factory()->create([
            'stock' => 50,
            'stock_status' => 'in_stock',
        ]);

        $product->increaseStock(25);

        $this->assertEquals(75, $product->refresh()->stock);
    }

    /** @test */
    public function increase_stock_updates_status_to_in_stock_when_from_out_of_stock()
    {
        $product = Product::factory()->create([
            'stock' => 0,
            'stock_status' => 'out_of_stock',
        ]);

        $product->increaseStock(10);

        $this->assertEquals('in_stock', $product->refresh()->stock_status);
    }

    /** @test */
    public function increment_sold_count_increases_count()
    {
        $product = Product::factory()->create(['sold_count' => 10]);

        $product->incrementSoldCount();

        $this->assertEquals(11, $product->refresh()->sold_count);
    }

    /** @test */
    public function increment_sold_count_increases_by_quantity()
    {
        $product = Product::factory()->create(['sold_count' => 10]);

        $product->incrementSoldCount(5);

        $this->assertEquals(15, $product->refresh()->sold_count);
    }

    /** @test */
    public function product_belongs_to_category()
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);

        $this->assertInstanceOf(Category::class, $product->category);
        $this->assertEquals($category->id, $product->category->id);
    }

    /** @test */
    public function product_soft_delete_works()
    {
        $product = Product::factory()->create();
        $productId = $product->id;

        $product->delete();

        $this->assertNull(Product::find($productId));
        $this->assertNotNull(Product::withTrashed()->find($productId));
    }

    /** @test */
    public function product_price_is_cast_to_decimal()
    {
        $product = Product::factory()->create(['price' => '999.99']);

        $this->assertIsNumeric($product->price);
    }

    /** @test */
    public function product_images_are_cast_to_array()
    {
        $images = ['img1.jpg', 'img2.jpg', 'img3.jpg'];
        $product = Product::factory()->create(['images' => $images]);

        $this->assertIsArray($product->images);
        $this->assertEquals($images, $product->images);
    }

    /** @test */
    public function active_and_featured_scopes_can_be_chained()
    {
        Product::factory()->create(['is_active' => true, 'is_featured' => true]);
        Product::factory()->create(['is_active' => true, 'is_featured' => false]);
        Product::factory()->create(['is_active' => false, 'is_featured' => true]);

        $products = Product::active()->featured()->get();

        $this->assertEquals(1, $products->count());
    }

    /** @test */
    public function active_and_in_stock_scopes_can_be_chained()
    {
        Product::factory()->create(['is_active' => true, 'stock' => 10, 'stock_status' => 'in_stock']);
        Product::factory()->create(['is_active' => true, 'stock' => 0, 'stock_status' => 'out_of_stock']);
        Product::factory()->create(['is_active' => false, 'stock' => 10, 'stock_status' => 'in_stock']);

        $products = Product::active()->inStock()->get();

        $this->assertEquals(1, $products->count());
    }
}
