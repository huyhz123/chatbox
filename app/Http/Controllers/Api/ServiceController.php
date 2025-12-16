<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    /**
     * Get all services
     */
    public function index(Request $request)
    {
        $query = Service::with('category')->where('is_active', true);

        // Filtering
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->has('provider')) {
            $query->where('api_provider', $request->provider);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = $request->get('per_page', 15);
        $services = $query->paginate($perPage);

        return response()->json([
            'services' => $services->items(),
            'pagination' => [
                'total' => $services->total(),
                'per_page' => $services->perPage(),
                'current_page' => $services->currentPage(),
                'last_page' => $services->lastPage(),
            ],
        ]);
    }

    /**
     * Get single service
     */
    public function show(Service $service)
    {
        $service->load('category');

        return response()->json([
            'service' => $service,
        ]);
    }
}
