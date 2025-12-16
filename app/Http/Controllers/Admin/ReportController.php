<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Ticket;
use App\Models\User;
use App\Models\Product;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:admin|superadmin');
    }

    /**
     * Display the reports index
     */
    public function index()
    {
        return view('admin.reports.index');
    }

    /**
     * Show sales report
     */
    public function salesReport(Request $request)
    {
        $fromDate = $request->get('from_date') ? Carbon::parse($request->get('from_date')) : Carbon::now()->subDays(30);
        $toDate = $request->get('to_date') ? Carbon::parse($request->get('to_date')) : Carbon::now();

        $orders = Order::where('payment_status', 'paid')
            ->whereBetween('created_at', [$fromDate, $toDate])
            ->get();

        $data = [
            'total_orders' => $orders->count(),
            'total_revenue' => $orders->sum('total'),
            'average_order_value' => $orders->avg('total'),
            'total_profit' => $orders->sum(function ($order) {
                return $order->getTotalProfit();
            }),
            'by_date' => $this->getOrdersByDate($fromDate, $toDate),
            'by_status' => $this->getOrdersByStatus($fromDate, $toDate),
            'top_products' => $this->getTopProducts($fromDate, $toDate),
        ];

        return view('admin.reports.sales', compact('data', 'fromDate', 'toDate'));
    }

    /**
     * Show customer report
     */
    public function customerReport(Request $request)
    {
        $fromDate = $request->get('from_date') ? Carbon::parse($request->get('from_date')) : Carbon::now()->subDays(30);
        $toDate = $request->get('to_date') ? Carbon::parse($request->get('to_date')) : Carbon::now();

        $totalCustomers = User::role('customer')->count();
        $newCustomers = User::role('customer')
            ->whereBetween('created_at', [$fromDate, $toDate])
            ->count();

        $data = [
            'total_customers' => $totalCustomers,
            'new_customers' => $newCustomers,
            'active_customers' => User::role('customer')->where('is_active', true)->count(),
            'total_spent' => Order::whereHas('user', function ($q) {
                $q->role('customer');
            })->where('payment_status', 'paid')->sum('total'),
            'by_date' => $this->getCustomersByDate($fromDate, $toDate),
            'top_customers' => $this->getTopCustomers($fromDate, $toDate),
        ];

        return view('admin.reports.customers', compact('data', 'fromDate', 'toDate'));
    }

    /**
     * Show ticket report
     */
    public function ticketReport(Request $request)
    {
        $fromDate = $request->get('from_date') ? Carbon::parse($request->get('from_date')) : Carbon::now()->subDays(30);
        $toDate = $request->get('to_date') ? Carbon::parse($request->get('to_date')) : Carbon::now();

        $tickets = Ticket::whereBetween('created_at', [$fromDate, $toDate])->get();

        $data = [
            'total_tickets' => $tickets->count(),
            'pending_tickets' => Ticket::where('status', 'pending')
                ->whereBetween('created_at', [$fromDate, $toDate])
                ->count(),
            'completed_tickets' => Ticket::where('status', 'completed')
                ->whereBetween('created_at', [$fromDate, $toDate])
                ->count(),
            'average_resolution_time' => $this->calculateAverageResolutionTime($fromDate, $toDate),
            'by_status' => $this->getTicketsByStatus($fromDate, $toDate),
            'by_date' => $this->getTicketsByDate($fromDate, $toDate),
        ];

        return view('admin.reports.tickets', compact('data', 'fromDate', 'toDate'));
    }

    /**
     * Show product report
     */
    public function productReport(Request $request)
    {
        $data = [
            'total_products' => Product::count(),
            'active_products' => Product::where('is_active', true)->count(),
            'low_stock_products' => Product::whereRaw('stock <= low_stock_alert')->count(),
            'out_of_stock_products' => Product::where('stock_status', 'out_of_stock')->count(),
            'total_value' => Product::sum(DB::raw('stock * price')),
            'top_selling' => $this->getTopSellingProducts(),
            'low_stock' => Product::whereRaw('stock <= low_stock_alert')
                ->orderBy('stock')
                ->limit(10)
                ->get(),
        ];

        return view('admin.reports.products', compact('data'));
    }

    /**
     * Generate and export sales report as CSV
     */
    public function exportSalesReportCsv(Request $request)
    {
        $fromDate = $request->get('from_date') ? Carbon::parse($request->get('from_date')) : Carbon::now()->subDays(30);
        $toDate = $request->get('to_date') ? Carbon::parse($request->get('to_date')) : Carbon::now();

        $orders = Order::where('payment_status', 'paid')
            ->whereBetween('created_at', [$fromDate, $toDate])
            ->get();

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="sales_report_' . now()->format('Y-m-d_His') . '.csv"',
        ];

        $callback = function () use ($orders) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Order Number', 'Customer', 'Total', 'Profit', 'Status', 'Date']);

            foreach ($orders as $order) {
                fputcsv($file, [
                    $order->order_number,
                    $order->customer_name,
                    $order->total,
                    $order->getTotalProfit(),
                    $order->status,
                    $order->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Generate and export customer report as CSV
     */
    public function exportCustomerReportCsv(Request $request)
    {
        $fromDate = $request->get('from_date') ? Carbon::parse($request->get('from_date')) : Carbon::now()->subDays(30);
        $toDate = $request->get('to_date') ? Carbon::parse($request->get('to_date')) : Carbon::now();

        $customers = User::role('customer')
            ->whereBetween('created_at', [$fromDate, $toDate])
            ->get();

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="customer_report_' . now()->format('Y-m-d_His') . '.csv"',
        ];

        $callback = function () use ($customers) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Name', 'Email', 'Phone', 'Orders', 'Total Spent', 'Status', 'Joined Date']);

            foreach ($customers as $customer) {
                $totalSpent = Order::where('user_id', $customer->id)
                    ->where('payment_status', 'paid')
                    ->sum('total');

                fputcsv($file, [
                    $customer->name,
                    $customer->email,
                    $customer->phone,
                    $customer->orders()->count(),
                    $totalSpent,
                    $customer->is_active ? 'Active' : 'Inactive',
                    $customer->created_at->format('Y-m-d'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Generate and export ticket report as CSV
     */
    public function exportTicketReportCsv(Request $request)
    {
        $fromDate = $request->get('from_date') ? Carbon::parse($request->get('from_date')) : Carbon::now()->subDays(30);
        $toDate = $request->get('to_date') ? Carbon::parse($request->get('to_date')) : Carbon::now();

        $tickets = Ticket::whereBetween('created_at', [$fromDate, $toDate])->get();

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="ticket_report_' . now()->format('Y-m-d_His') . '.csv"',
        ];

        $callback = function () use ($tickets) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Ticket Number', 'Customer', 'Service', 'Status', 'Created Date', 'Completed Date']);

            foreach ($tickets as $ticket) {
                fputcsv($file, [
                    $ticket->ticket_number,
                    $ticket->user->name,
                    $ticket->service->name ?? 'N/A',
                    ucfirst(str_replace('_', ' ', $ticket->status)),
                    $ticket->created_at->format('Y-m-d H:i:s'),
                    $ticket->completed_at ? $ticket->completed_at->format('Y-m-d H:i:s') : 'Not Completed',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Generate and export product report as CSV
     */
    public function exportProductReportCsv()
    {
        $products = Product::all();

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="product_report_' . now()->format('Y-m-d_His') . '.csv"',
        ];

        $callback = function () use ($products) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['SKU', 'Name', 'Category', 'Price', 'Cost', 'Stock', 'Status', 'Sold Count']);

            foreach ($products as $product) {
                fputcsv($file, [
                    $product->sku,
                    $product->name,
                    $product->category->name ?? 'N/A',
                    $product->price,
                    $product->cost,
                    $product->stock,
                    $product->stock_status,
                    $product->sold_count,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Get orders by date for chart
     */
    private function getOrdersByDate($fromDate, $toDate)
    {
        return Order::where('payment_status', 'paid')
            ->whereBetween('created_at', [$fromDate, $toDate])
            ->groupBy(DB::raw('DATE(created_at)'))
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count, SUM(total) as revenue')
            ->get()
            ->pluck('revenue', 'date')
            ->toArray();
    }

    /**
     * Get orders by status
     */
    private function getOrdersByStatus($fromDate, $toDate)
    {
        return Order::whereBetween('created_at', [$fromDate, $toDate])
            ->groupBy('status')
            ->selectRaw('status, COUNT(*) as count')
            ->get()
            ->pluck('count', 'status')
            ->toArray();
    }

    /**
     * Get top selling products
     */
    private function getTopProducts($fromDate, $toDate)
    {
        return Product::whereBetween('created_at', [$fromDate, $toDate])
            ->orderByDesc('sold_count')
            ->limit(10)
            ->get();
    }

    /**
     * Get customers by date
     */
    private function getCustomersByDate($fromDate, $toDate)
    {
        return User::role('customer')
            ->whereBetween('created_at', [$fromDate, $toDate])
            ->groupBy(DB::raw('DATE(created_at)'))
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->get()
            ->pluck('count', 'date')
            ->toArray();
    }

    /**
     * Get top customers by spending
     */
    private function getTopCustomers($fromDate, $toDate)
    {
        return User::role('customer')
            ->whereHas('orders', function ($q) use ($fromDate, $toDate) {
                $q->whereBetween('created_at', [$fromDate, $toDate])
                  ->where('payment_status', 'paid');
            })
            ->withCount(['orders' => function ($q) use ($fromDate, $toDate) {
                $q->whereBetween('created_at', [$fromDate, $toDate])
                  ->where('payment_status', 'paid');
            }])
            ->limit(10)
            ->get();
    }

    /**
     * Get tickets by status
     */
    private function getTicketsByStatus($fromDate, $toDate)
    {
        return Ticket::whereBetween('created_at', [$fromDate, $toDate])
            ->groupBy('status')
            ->selectRaw('status, COUNT(*) as count')
            ->get()
            ->pluck('count', 'status')
            ->toArray();
    }

    /**
     * Get tickets by date
     */
    private function getTicketsByDate($fromDate, $toDate)
    {
        return Ticket::whereBetween('created_at', [$fromDate, $toDate])
            ->groupBy(DB::raw('DATE(created_at)'))
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->get()
            ->pluck('count', 'date')
            ->toArray();
    }

    /**
     * Calculate average ticket resolution time
     */
    private function calculateAverageResolutionTime($fromDate, $toDate)
    {
        $tickets = Ticket::where('status', 'completed')
            ->whereNotNull('completed_at')
            ->whereBetween('created_at', [$fromDate, $toDate])
            ->get();

        if ($tickets->isEmpty()) {
            return 0;
        }

        $totalHours = $tickets->sum(function ($ticket) {
            return $ticket->created_at->diffInHours($ticket->completed_at);
        });

        return round($totalHours / $tickets->count(), 2);
    }

    /**
     * Get top selling products
     */
    private function getTopSellingProducts()
    {
        return Product::orderByDesc('sold_count')
            ->limit(10)
            ->get();
    }

    /**
     * Activity log for reports generated
     */
    public function logReportGenerated($reportType, $filters = [])
    {
        activity()
            ->causedBy(auth()->user())
            ->withProperties(['report_type' => $reportType, 'filters' => $filters])
            ->log('report generated');
    }
}
