<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\File;
use App\Models\FileDownload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FileController extends Controller
{
    /**
     * Get all files
     */
    public function index(Request $request)
    {
        $query = File::with('category')->where('is_active', true);

        // Filtering
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->has('type')) {
            $query->where('file_type', $request->type);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = $request->get('per_page', 15);
        $files = $query->paginate($perPage);

        return response()->json([
            'files' => $files->items(),
            'pagination' => [
                'total' => $files->total(),
                'per_page' => $files->perPage(),
                'current_page' => $files->currentPage(),
                'last_page' => $files->lastPage(),
            ],
        ]);
    }

    /**
     * Get single file
     */
    public function show(File $file)
    {
        $file->load('category');

        return response()->json([
            'file' => $file,
        ]);
    }

    /**
     * Get user's purchased files
     */
    public function myFiles(Request $request)
    {
        $downloads = FileDownload::where('user_id', $request->user()->id)
            ->with('file')
            ->get();

        return response()->json([
            'files' => $downloads,
            'total' => $downloads->count(),
        ]);
    }

    /**
     * Download file
     */
    public function download(Request $request, File $file)
    {
        $user = $request->user();

        // Check if user has purchased this file
        $download = FileDownload::where('user_id', $user->id)
            ->where('file_id', $file->id)
            ->first();

        if (!$download) {
            return response()->json([
                'message' => 'You need to purchase this file first',
            ], 403);
        }

        // Check download limit
        if ($file->download_limit > 0 && $download->downloads_count >= $file->download_limit) {
            return response()->json([
                'message' => 'Download limit reached',
            ], 403);
        }

        // Increment download count
        $download->increment('downloads_count');
        $download->update(['last_downloaded_at' => now()]);

        // Generate download URL
        $downloadUrl = Storage::url($file->file_path);

        return response()->json([
            'message' => 'Download authorized',
            'download_url' => $downloadUrl,
            'downloads_remaining' => $file->download_limit > 0
                ? $file->download_limit - $download->downloads_count
                : 'unlimited',
        ]);
    }
}
