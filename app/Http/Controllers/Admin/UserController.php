<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:admin|superadmin');
    }

    /**
     * Display a listing of users
     */
    public function index(Request $request)
    {
        $query = User::query();

        // Search functionality
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
        }

        // Filter by role
        if ($request->has('role') && $request->get('role')) {
            $query->role($request->get('role'));
        }

        // Filter by status
        if ($request->has('status') && $request->get('status')) {
            $query->where('is_active', $request->get('status') === 'active' ? true : false);
        }

        // Filter by date range
        if ($request->has('from_date') && $request->get('from_date')) {
            $query->whereDate('created_at', '>=', $request->get('from_date'));
        }

        if ($request->has('to_date') && $request->get('to_date')) {
            $query->whereDate('created_at', '<=', $request->get('to_date'));
        }

        // Pagination
        $users = $query->paginate(20);
        $roles = Role::all();

        return view('admin.users.index', compact('users', 'roles'));
    }

    /**
     * Show the form for creating a new user
     */
    public function create()
    {
        $roles = Role::all();
        return view('admin.users.create', compact('roles'));
    }

    /**
     * Store a newly created user
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:20|unique:users,phone',
            'password' => 'required|string|min:8|confirmed',
            'roles' => 'required|array|min:1',
            'roles.*' => 'exists:roles,name',
            'is_active' => 'boolean',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'password' => Hash::make($validated['password']),
            'is_active' => $validated['is_active'] ?? true,
        ]);

        $user->syncRoles($validated['roles']);

        activity()
            ->causedBy(auth()->user())
            ->performedOn($user)
            ->withProperties(['roles' => $validated['roles']])
            ->log('created');

        return redirect()->route('admin.users.show', $user)
                       ->with('success', 'User created successfully!');
    }

    /**
     * Display the specified user
     */
    public function show(User $user)
    {
        $user->load(['orders', 'tickets', 'courseEnrollments']);
        return view('admin.users.show', compact('user'));
    }

    /**
     * Show the form for editing a user
     */
    public function edit(User $user)
    {
        $roles = Role::all();
        return view('admin.users.edit', compact('user', 'roles'));
    }

    /**
     * Update the specified user
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:20', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:8|confirmed',
            'roles' => 'required|array|min:1',
            'roles.*' => 'exists:roles,name',
            'balance' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
        ]);

        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'is_active' => $validated['is_active'] ?? $user->is_active,
        ];

        if (isset($validated['balance'])) {
            $updateData['balance'] = $validated['balance'];
        }

        if ($validated['password'] ?? null) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $user->update($updateData);
        $user->syncRoles($validated['roles']);

        activity()
            ->causedBy(auth()->user())
            ->performedOn($user)
            ->withProperties(['roles' => $validated['roles']])
            ->log('updated');

        return redirect()->route('admin.users.show', $user)
                       ->with('success', 'User updated successfully!');
    }

    /**
     * Remove the specified user
     */
    public function destroy(User $user)
    {
        // Prevent deleting the current user
        if ($user->id === auth()->id()) {
            return redirect()->back()->with('error', 'You cannot delete your own account!');
        }

        activity()
            ->causedBy(auth()->user())
            ->performedOn($user)
            ->log('deleted');

        $user->delete();

        return redirect()->route('admin.users.index')
                       ->with('success', 'User deleted successfully!');
    }

    /**
     * Assign role to user
     */
    public function assignRole(Request $request, User $user)
    {
        $validated = $request->validate([
            'roles' => 'required|array|min:1',
            'roles.*' => 'exists:roles,name',
        ]);

        $oldRoles = $user->getRoleNames()->toArray();
        $user->syncRoles($validated['roles']);

        activity()
            ->causedBy(auth()->user())
            ->performedOn($user)
            ->withProperties([
                'old_roles' => $oldRoles,
                'new_roles' => $validated['roles'],
            ])
            ->log('roles updated');

        return redirect()->back()->with('success', 'User roles updated successfully!');
    }

    /**
     * Toggle active status
     */
    public function toggleActive(User $user)
    {
        // Prevent deactivating the current user
        if ($user->id === auth()->id()) {
            return redirect()->back()->with('error', 'You cannot deactivate your own account!');
        }

        $user->update(['is_active' => !$user->is_active]);

        activity()
            ->causedBy(auth()->user())
            ->performedOn($user)
            ->log('status toggled');

        return redirect()->back()->with('success', 'User status updated!');
    }

    /**
     * Reset user password
     */
    public function resetPassword(Request $request, User $user)
    {
        $validated = $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user->update(['password' => Hash::make($validated['password'])]);

        activity()
            ->causedBy(auth()->user())
            ->performedOn($user)
            ->log('password reset');

        return redirect()->back()->with('success', 'User password reset successfully!');
    }

    /**
     * Adjust user balance
     */
    public function adjustBalance(Request $request, User $user)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric',
            'adjustment_type' => 'required|in:add,subtract',
            'reason' => 'nullable|string',
        ]);

        $previousBalance = $user->balance;

        if ($validated['adjustment_type'] === 'add') {
            $user->addBalance($validated['amount']);
        } else {
            $user->deductBalance($validated['amount']);
        }

        activity()
            ->causedBy(auth()->user())
            ->performedOn($user)
            ->withProperties([
                'previous_balance' => $previousBalance,
                'new_balance' => $user->balance,
                'adjustment_type' => $validated['adjustment_type'],
                'amount' => $validated['amount'],
                'reason' => $validated['reason'] ?? null,
            ])
            ->log('balance adjusted');

        return redirect()->back()->with('success', 'User balance adjusted successfully!');
    }

    /**
     * Get user statistics
     */
    public function getStats()
    {
        $stats = [
            'total_users' => User::count(),
            'active_users' => User::where('is_active', true)->count(),
            'inactive_users' => User::where('is_active', false)->count(),
            'total_balance' => User::sum('balance'),
            'average_balance' => User::avg('balance'),
        ];

        $roleStats = [];
        foreach (Role::all() as $role) {
            $roleStats[$role->name] = User::role($role->name)->count();
        }

        $stats['by_role'] = $roleStats;

        return response()->json($stats);
    }
}
