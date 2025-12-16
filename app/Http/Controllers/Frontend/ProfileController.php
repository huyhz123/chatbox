<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Order;
use App\Models\Ticket;
use App\Models\CourseEnrollment;
use App\Models\FileDownload;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    /**
     * Create a new instance of the controller
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display user profile
     */
    public function show(): View
    {
        $user = auth()->user();

        return view('frontend.profile.show', [
            'user' => $user,
        ]);
    }

    /**
     * Edit user profile
     */
    public function edit(): View
    {
        $user = auth()->user();

        return view('frontend.profile.edit', [
            'user' => $user,
        ]);
    }

    /**
     * Update user profile
     */
    public function update(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . auth()->id(),
            'phone' => 'nullable|string|max:20',
            'avatar' => 'nullable|image|max:2048',
            'preferred_language' => 'nullable|string|in:en,vi',
        ]);

        try {
            $user = auth()->user();
            $data = $request->only(['name', 'email', 'phone', 'preferred_language']);

            // Handle avatar upload
            if ($request->hasFile('avatar')) {
                if ($user->avatar) {
                    // Delete old avatar
                    \Storage::disk('public')->delete($user->avatar);
                }

                $avatarPath = $request->file('avatar')->store('avatars', 'public');
                $data['avatar'] = $avatarPath;
            }

            $user->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully',
                'user' => $user,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update profile: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Change password
     */
    public function changePassword(Request $request): JsonResponse
    {
        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        try {
            $user = auth()->user();

            if (!Hash::check($request->current_password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Current password is incorrect',
                ], 422);
            }

            $user->update([
                'password' => Hash::make($request->new_password),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Password changed successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to change password: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Display user orders
     */
    public function orders(): View
    {
        $page = request()->input('page', 1);
        $perPage = request()->input('per_page', 10);
        $status = request()->input('status');

        $query = auth()->user()->orders();

        if ($status) {
            $query->where('status', $status);
        }

        $orders = $query
            ->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);

        return view('frontend.profile.orders', [
            'orders' => $orders,
            'status' => $status,
        ]);
    }

    /**
     * Display order details
     */
    public function orderDetails(Order $order): View
    {
        if ($order->user_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        $items = $order->items()->with('itemable')->get();
        $payments = $order->payments;

        return view('frontend.profile.order-details', [
            'order' => $order,
            'items' => $items,
            'payments' => $payments,
        ]);
    }

    /**
     * Display user support tickets
     */
    public function tickets(): View
    {
        $page = request()->input('page', 1);
        $perPage = request()->input('per_page', 10);
        $status = request()->input('status');

        $query = auth()->user()->tickets();

        if ($status) {
            $query->where('status', $status);
        }

        $tickets = $query
            ->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);

        return view('frontend.profile.tickets', [
            'tickets' => $tickets,
            'status' => $status,
        ]);
    }

    /**
     * Display ticket details
     */
    public function ticketDetails(Ticket $ticket): View
    {
        if ($ticket->user_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        $history = $ticket->history()->orderBy('created_at', 'asc')->get();

        return view('frontend.profile.ticket-details', [
            'ticket' => $ticket,
            'history' => $history,
        ]);
    }

    /**
     * Create new ticket
     */
    public function createTicket(Request $request): JsonResponse
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:5000',
            'type' => 'required|string|in:general,technical,billing,other',
        ]);

        try {
            $user = auth()->user();

            $ticket = Ticket::create([
                'user_id' => $user->id,
                'subject' => $request->subject,
                'description' => $request->message,
                'type' => $request->type,
                'status' => 'open',
                'priority' => 'medium',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Ticket created successfully',
                'ticket_id' => $ticket->id,
                'redirect' => route('profile.ticket-details', $ticket->id),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create ticket: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Display user courses
     */
    public function courses(): View
    {
        $page = request()->input('page', 1);
        $perPage = request()->input('per_page', 10);

        $enrollments = auth()->user()->courseEnrollments()
            ->with('course')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);

        return view('frontend.profile.courses', [
            'enrollments' => $enrollments,
        ]);
    }

    /**
     * Display user files/downloads
     */
    public function files(): View
    {
        $page = request()->input('page', 1);
        $perPage = request()->input('per_page', 10);

        $downloads = auth()->user()->fileDownloads()
            ->with('file')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);

        return view('frontend.profile.files', [
            'downloads' => $downloads,
        ]);
    }

    /**
     * Display account settings
     */
    public function settings(): View
    {
        $user = auth()->user();

        return view('frontend.profile.settings', [
            'user' => $user,
        ]);
    }

    /**
     * Update account settings
     */
    public function updateSettings(Request $request): JsonResponse
    {
        $request->validate([
            'email_notifications' => 'nullable|boolean',
            'marketing_emails' => 'nullable|boolean',
            'two_factor_enabled' => 'nullable|boolean',
        ]);

        try {
            $user = auth()->user();

            // TODO: Implement actual settings storage
            // For now, just return success

            return response()->json([
                'success' => true,
                'message' => 'Settings updated successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update settings: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Delete account
     */
    public function deleteAccount(Request $request): JsonResponse
    {
        $request->validate([
            'password' => 'required|string',
            'confirmation' => 'required|accepted',
        ]);

        try {
            $user = auth()->user();

            if (!Hash::check($request->password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Password is incorrect',
                ], 422);
            }

            $user->delete();

            auth()->logout();

            return response()->json([
                'success' => true,
                'message' => 'Account deleted successfully',
                'redirect' => route('home'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete account: ' . $e->getMessage(),
            ], 422);
        }
    }
}
