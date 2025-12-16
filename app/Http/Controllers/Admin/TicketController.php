<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:admin|superadmin');
    }

    /**
     * Display a listing of tickets
     */
    public function index(Request $request)
    {
        $query = Ticket::query()->with(['user', 'service', 'order']);

        // Search functionality
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where('ticket_number', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%");
        }

        // Filter by status
        if ($request->has('status') && $request->get('status')) {
            $query->where('status', $request->get('status'));
        }

        // Filter by date range
        if ($request->has('from_date') && $request->get('from_date')) {
            $query->whereDate('created_at', '>=', $request->get('from_date'));
        }

        if ($request->has('to_date') && $request->get('to_date')) {
            $query->whereDate('created_at', '<=', $request->get('to_date'));
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $tickets = $query->paginate(20);

        return view('admin.tickets.index', compact('tickets'));
    }

    /**
     * Display the specified ticket
     */
    public function show(Ticket $ticket)
    {
        $ticket->load(['user', 'service', 'order', 'history']);
        return view('admin.tickets.show', compact('ticket'));
    }

    /**
     * Show the form for editing ticket status
     */
    public function edit(Ticket $ticket)
    {
        $statuses = ['pending', 'processing', 'completed', 'on_hold', 'cancelled'];
        return view('admin.tickets.edit', compact('ticket', 'statuses'));
    }

    /**
     * Update ticket status manually
     */
    public function update(Request $request, Ticket $ticket)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,processing,completed,on_hold,cancelled',
            'notes' => 'nullable|string',
        ]);

        $oldStatus = $ticket->status;

        $ticket->updateStatus(
            $validated['status'],
            $validated['notes'] ?? null
        );

        activity()
            ->causedBy(auth()->user())
            ->performedOn($ticket)
            ->withProperties([
                'old_status' => $oldStatus,
                'new_status' => $validated['status'],
                'notes' => $validated['notes'] ?? null,
            ])
            ->log('status updated');

        return redirect()->route('admin.tickets.show', $ticket)
                       ->with('success', 'Ticket status updated successfully!');
    }

    /**
     * Get ticket statistics
     */
    public function getStats()
    {
        $stats = [
            'total' => Ticket::count(),
            'pending' => Ticket::where('status', 'pending')->count(),
            'processing' => Ticket::where('status', 'processing')->count(),
            'completed' => Ticket::where('status', 'completed')->count(),
            'on_hold' => Ticket::where('status', 'on_hold')->count(),
            'cancelled' => Ticket::where('status', 'cancelled')->count(),
            'average_resolution_time' => $this->calculateAverageResolutionTime(),
        ];

        return response()->json($stats);
    }

    /**
     * Calculate average resolution time in hours
     */
    private function calculateAverageResolutionTime()
    {
        $completedTickets = Ticket::where('status', 'completed')
            ->whereNotNull('completed_at')
            ->get();

        if ($completedTickets->isEmpty()) {
            return 0;
        }

        $totalHours = $completedTickets->sum(function ($ticket) {
            return $ticket->created_at->diffInHours($ticket->completed_at);
        });

        return round($totalHours / $completedTickets->count(), 2);
    }

    /**
     * Bulk update ticket status
     */
    public function bulkUpdateStatus(Request $request)
    {
        $validated = $request->validate([
            'ticket_ids' => 'required|array|min:1',
            'ticket_ids.*' => 'integer|exists:tickets,id',
            'status' => 'required|in:pending,processing,completed,on_hold,cancelled',
        ]);

        $tickets = Ticket::whereIn('id', $validated['ticket_ids'])->get();

        foreach ($tickets as $ticket) {
            $ticket->updateStatus($validated['status']);

            activity()
                ->causedBy(auth()->user())
                ->performedOn($ticket)
                ->withProperties([
                    'status' => $validated['status'],
                    'bulk_update' => true,
                ])
                ->log('status updated');
        }

        return redirect()->back()
                       ->with('success', count($tickets) . ' tickets updated successfully!');
    }

    /**
     * Export tickets to CSV
     */
    public function export(Request $request)
    {
        $query = Ticket::query()->with(['user', 'service']);

        // Apply filters
        if ($request->has('status') && $request->get('status')) {
            $query->where('status', $request->get('status'));
        }

        if ($request->has('from_date') && $request->get('from_date')) {
            $query->whereDate('created_at', '>=', $request->get('from_date'));
        }

        if ($request->has('to_date') && $request->get('to_date')) {
            $query->whereDate('created_at', '<=', $request->get('to_date'));
        }

        $tickets = $query->get();

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="tickets_' . now()->format('Y-m-d_His') . '.csv"',
        ];

        $callback = function () use ($tickets) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Ticket Number', 'Customer', 'Email', 'Service', 'Status', 'Created At', 'Notes']);

            foreach ($tickets as $ticket) {
                fputcsv($file, [
                    $ticket->ticket_number,
                    $ticket->user->name,
                    $ticket->user->email,
                    $ticket->service->name ?? 'N/A',
                    ucfirst(str_replace('_', ' ', $ticket->status)),
                    $ticket->created_at->format('Y-m-d H:i:s'),
                    $ticket->notes,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
