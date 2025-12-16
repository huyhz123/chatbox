<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Ticket;
use App\Models\User;
use App\Models\Product;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:admin|superadmin');
    }

    /**
     * Display the admin dashboard with statistics
     */
    public function index()
    {
        // Time period for stats (last 30 days)
        $startDate = Carbon::now()->subDays(30);

        $stats = [
            // Order Statistics
            'total_orders' => Order::count(),
            'pending_orders' => Order::where('status', 'pending')->count(),
            'completed_orders' => Order::where('status', 'completed')->count(),
            'monthly_orders' => Order::whereBetween('created_at', [$startDate, now()])->count(),
            'monthly_order_value' => Order::whereBetween('created_at', [$startDate, now()])->sum('total'),

            // Ticket Statistics
            'total_tickets' => Ticket::count(),
            'pending_tickets' => Ticket::where('status', 'pending')->count(),
            'processing_tickets' => Ticket::where('status', 'processing')->count(),
            'completed_tickets' => Ticket::where('status', 'completed')->count(),

            // Revenue Statistics
            'total_revenue' => Order::where('payment_status', 'paid')->sum('total'),
            'monthly_revenue' => Order::where('payment_status', 'paid')
                ->whereBetween('created_at', [$startDate, now()])
                ->sum('total'),
            'pending_revenue' => Order::where('payment_status', 'pending')->sum('total'),

            // Profit Statistics
            'total_profit' => $this->calculateTotalProfit(),
            'monthly_profit' => $this->calculateMonthlyProfit($startDate),

            // Product Statistics
            'total_products' => Product::count(),
            'active_products' => Product::where('is_active', true)->count(),
            'low_stock_products' => Product::whereRaw('stock <= low_stock_alert')->count(),
            'out_of_stock_products' => Product::where('stock_status', 'out_of_stock')->count(),

            // User Statistics
            'total_users' => User::count(),
            'active_users' => User::where('is_active', true)->count(),
            'new_users_this_month' => User::whereBetween('created_at', [$startDate, now()])->count(),

            // Payment Statistics
            'total_payments' => Payment::count(),
            'successful_payments' => Payment::where('status', 'successful')->count(),
            'failed_payments' => Payment::where('status', 'failed')->count(),
        ];

        // Recent activities
        $recentOrders = Order::with('user')
            ->latest()
            ->limit(5)
            ->get();

        $recentTickets = Ticket::with(['user', 'service'])
            ->latest()
            ->limit(5)
            ->get();

        $lowStockProducts = Product::whereRaw('stock <= low_stock_alert')
            ->limit(10)
            ->get();

        return view('admin.dashboard.index', compact('stats', 'recentOrders', 'recentTickets', 'lowStockProducts'));
    }

    /**
     * Calculate total profit from all orders
     */
    private function calculateTotalProfit()
    {
        $orders = Order::with('items')->where('payment_status', 'paid')->get();

        return $orders->sum(function ($order) {
            return $order->getTotalProfit();
        });
    }

    /**
     * Calculate profit for a specific period
     */
    private function calculateMonthlyProfit($startDate)
    {
        $orders = Order::with('items')
            ->where('payment_status', 'paid')
            ->whereBetween('created_at', [$startDate, now()])
            ->get();

        return $orders->sum(function ($order) {
            return $order->getTotalProfit();
        });
    }
}
