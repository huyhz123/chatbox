<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\TicketHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TicketController extends Controller
{
    /**
     * Display list of user's tickets
     */
    public function index()
    {
        $tickets = Ticket::where('user_id', auth()->id())
            ->with(['order'])
            ->latest()
            ->paginate(20);

        return view('frontend.tickets.index', compact('tickets'));
    }

    /**
     * Show form to create new ticket
     */
    public function create()
    {
        return view('frontend.tickets.create');
    }

    /**
     * Store new ticket
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|string|in:technical,billing,general,other',
            'priority' => 'required|string|in:low,medium,high,urgent',
            'message' => 'required|string|max:2000',
            'order_id' => 'nullable|exists:orders,id',
        ]);

        try {
            DB::beginTransaction();

            // Generate ticket number
            $ticketNumber = 'TKT-' . date('Ymd') . '-' . str_pad(Ticket::count() + 1, 4, '0', STR_PAD_LEFT);

            // Create ticket
            $ticket = Ticket::create([
                'ticket_number' => $ticketNumber,
                'user_id' => auth()->id(),
                'order_id' => $request->order_id,
                'title' => $request->title,
                'category' => $request->category,
                'priority' => $request->priority,
                'status' => 'open',
                'description' => $request->message,
            ]);

            // Create initial history entry
            TicketHistory::create([
                'ticket_id' => $ticket->id,
                'user_id' => auth()->id(),
                'action' => 'created',
                'message' => $request->message,
                'is_customer_reply' => true,
            ]);

            DB::commit();

            Log::info('Ticket created', [
                'ticket_id' => $ticket->id,
                'ticket_number' => $ticketNumber,
                'user_id' => auth()->id(),
            ]);

            return redirect()->route('tickets.show', $ticket->id)
                ->with('success', 'Support ticket created successfully! Our team will respond soon.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating ticket: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Error creating ticket. Please try again.');
        }
    }

    /**
     * Display ticket details
     */
    public function show(Ticket $ticket)
    {
        // Ensure user owns this ticket
        if ($ticket->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access to this ticket.');
        }

        $ticket->load(['order', 'history.user']);

        return view('frontend.tickets.show', compact('ticket'));
    }

    /**
     * Reply to ticket
     */
    public function reply(Request $request, Ticket $ticket)
    {
        // Ensure user owns this ticket
        if ($ticket->user_id !== auth()->id()) {
            return back()->with('error', 'Unauthorized access to this ticket.');
        }

        $request->validate([
            'message' => 'required|string|max:2000',
        ]);

        try {
            // Create reply in history
            TicketHistory::create([
                'ticket_id' => $ticket->id,
                'user_id' => auth()->id(),
                'action' => 'replied',
                'message' => $request->message,
                'is_customer_reply' => true,
            ]);

            // Update ticket status if closed
            if ($ticket->status === 'closed') {
                $ticket->update(['status' => 'open']);
            }

            // Update last reply time
            $ticket->touch();

            Log::info('Ticket reply added', [
                'ticket_id' => $ticket->id,
                'user_id' => auth()->id(),
            ]);

            return back()->with('success', 'Reply added successfully.');
        } catch (\Exception $e) {
            Log::error('Error adding ticket reply: ' . $e->getMessage());
            return back()->with('error', 'Error adding reply. Please try again.');
        }
    }
}
