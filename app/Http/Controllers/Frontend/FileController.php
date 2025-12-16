<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\File;
use App\Models\FileDownload;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\FileService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\View\View;

class FileController extends Controller
{
    protected FileService $fileService;

    public function __construct(FileService $fileService)
    {
        $this->fileService = $fileService;
    }

    /**
     * Display a listing of files
     */
    public function index(): View
    {
        $page = request()->input('page', 1);
        $perPage = request()->input('per_page', 12);
        $sortBy = request()->input('sort_by', 'latest');
        $categoryId = request()->input('category_id');
        $fileType = request()->input('file_type');

        $query = File::active();

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        if ($fileType) {
            $query->where('file_type', $fileType);
        }

        $files = match ($sortBy) {
            'popular' => $query->orderBy('download_count', 'desc'),
            'price_asc' => $query->orderByRaw('COALESCE(special_price, price) ASC'),
            'price_desc' => $query->orderByRaw('COALESCE(special_price, price) DESC'),
            default => $query->latest(),
        };

        $files = $files->paginate($perPage, ['*'], 'page', $page);

        return view('frontend.files.index', [
            'files' => $files,
            'sortBy' => $sortBy,
            'categoryId' => $categoryId,
            'fileType' => $fileType,
        ]);
    }

    /**
     * Display a specific file
     */
    public function show(File $file): View
    {
        if (!$file->is_active) {
            abort(404);
        }

        $relatedFiles = File::active()
            ->where('category_id', $file->category_id)
            ->where('id', '!=', $file->id)
            ->limit(4)
            ->get();

        $userHasAccess = false;
        if (auth()->check()) {
            $userHasAccess = $this->fileService->userHasAccessToFile(auth()->user(), $file);
        }

        return view('frontend.files.show', [
            'file' => $file,
            'relatedFiles' => $relatedFiles,
            'userHasAccess' => $userHasAccess,
        ]);
    }

    /**
     * Purchase file
     */
    public function purchase(Request $request): JsonResponse
    {
        $request->validate([
            'file_id' => 'required|exists:files,id',
        ]);

        $file = File::findOrFail($request->file_id);

        if (!$file->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'File is not available',
            ], 422);
        }

        try {
            $user = auth()->user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please login to purchase',
                    'redirect' => route('login'),
                ], 401);
            }

            // Create order
            $order = Order::create([
                'order_number' => 'ORD-' . time() . '-' . random_int(1000, 9999),
                'user_id' => $user->id,
                'type' => 'file',
                'status' => 'pending',
                'payment_status' => 'pending',
                'currency' => config('app.currency', 'VND'),
                'customer_name' => $user->name,
                'customer_email' => $user->email,
                'customer_phone' => $user->phone,
                'ip_address' => $request->ip(),
            ]);

            // Add order item
            $price = $file->getCurrentPrice();

            OrderItem::create([
                'order_id' => $order->id,
                'itemable_type' => File::class,
                'itemable_id' => $file->id,
                'quantity' => 1,
                'price' => $price,
                'options' => [],
            ]);

            // Update order totals
            $order->update([
                'subtotal' => $price,
                'total' => $price,
            ]);

            $file->incrementSoldCount();

            return response()->json([
                'success' => true,
                'message' => 'Proceeding to checkout',
                'order_id' => $order->id,
                'redirect' => route('checkout.show', $order->id),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to process purchase: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Download file
     */
    public function download(File $file): Response
    {
        if (!$file->is_active) {
            abort(404);
        }

        $user = auth()->user();
        if (!$user) {
            abort(403, 'Unauthorized');
        }

        if (!$this->fileService->userHasAccessToFile($user, $file)) {
            abort(403, 'You do not have access to this file');
        }

        if ($file->download_limit > 0) {
            $downloadCount = $user->fileDownloads()
                ->where('file_id', $file->id)
                ->count();

            if ($downloadCount >= $file->download_limit) {
                abort(403, 'Download limit exceeded');
            }
        }

        // Log the download
        FileDownload::create([
            'file_id' => $file->id,
            'user_id' => $user->id,
            'ip_address' => request()->ip(),
        ]);

        $file->incrementDownloadCount();

        return response()->download(storage_path('app/' . $file->file_path), $file->name);
    }

    /**
     * Get file preview
     */
    public function preview(File $file): Response
    {
        if (!$file->is_active || !$file->preview_url) {
            abort(404);
        }

        return response()->redirectTo($file->preview_url);
    }
}
