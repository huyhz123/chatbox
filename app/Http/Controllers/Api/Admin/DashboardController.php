<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use App\Models\Product;
use App\Models\Service;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

class DashboardController extends Controller
{
    /**
     * Get dashboard statistics
     */
    public function stats(Request $request)
    {
        $period = $request->get('period', '30'); // days

        $stats = [
            'total_users' => User::count(),
            'active_users' => User::where('is_active', true)->count(),
            'new_users_today' => User::whereDate('created_at', today())->count(),

            'total_orders' => Order::count(),
            'pending_orders' => Order::where('status', 'pending')->count(),
            'processing_orders' => Order::where('status', 'processing')->count(),
            'completed_orders' => Order::where('status', 'completed')->count(),

            'total_revenue' => Order::where('payment_status', 'paid')->sum('total_amount'),
            'revenue_today' => Order::where('payment_status', 'paid')
                ->whereDate('created_at', today())
                ->sum('total_amount'),
            'revenue_this_month' => Order::where('payment_status', 'paid')
                ->whereMonth('created_at', now()->month)
                ->sum('total_amount'),

            'total_products' => Product::count(),
            'active_products' => Product::where('is_active', true)->count(),
            'low_stock_products' => Product::where('stock', '<=', 10)->count(),

            'total_services' => Service::count(),
            'active_services' => Service::where('is_active', true)->count(),
        ];

        // Recent orders trend
        $recentOrders = Order::selectRaw('DATE(created_at) as date, COUNT(*) as count, SUM(total_amount) as revenue')
            ->where('created_at', '>=', now()->subDays($period))
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        return response()->json([
            'stats' => $stats,
            'recent_orders' => $recentOrders,
        ]);
    }

    /**
     * Get activity logs
     */
    public function activityLogs(Request $request)
    {
        $perPage = $request->get('per_page', 20);

        $logs = Activity::with('causer', 'subject')
            ->latest()
            ->paginate($perPage);

        return response()->json([
            'logs' => $logs->items(),
            'pagination' => [
                'total' => $logs->total(),
                'per_page' => $logs->perPage(),
                'current_page' => $logs->currentPage(),
                'last_page' => $logs->lastPage(),
            ],
        ]);
    }
}
