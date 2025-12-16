@extends('frontend.layouts.app')

@section('title', 'My Profile - MyApp')
@section('description', 'Manage your profile and account settings')

@section('content')
<!-- Page Header -->
<section class="py-2xl" style="background: linear-gradient(135deg, rgba(99, 102, 241, 0.1) 0%, rgba(139, 92, 246, 0.1) 100%);">
    <div class="container">
        <h1 style="text-align: center; margin: 0;">My Profile</h1>
    </div>
</section>

<!-- Profile Content -->
<section class="py-2xl">
    <div class="container-sm">
        <div class="grid grid-cols-4 grid-gap-lg">
            <!-- Sidebar Navigation -->
            <div style="grid-column: 1;">
                <div class="card">
                    <div style="text-align: center; margin-bottom: var(--spacing-lg);">
                        <img src="https://via.placeholder.com/100" alt="Profile" class="rounded-full" style="width: 100px; height: 100px; object-fit: cover; margin-bottom: var(--spacing-md);">
                        <h3 style="margin: 0 0 var(--spacing-xs) 0;">John Doe</h3>
                        <p style="margin: 0; color: var(--gray);">john@example.com</p>
                    </div>

                    <hr style="border: none; border-top: 1px solid var(--gray-lighter); margin: var(--spacing-md) 0;">

                    <div style="display: flex; flex-direction: column; gap: var(--spacing-sm);">
                        <button class="btn btn-sm btn-ghost" style="justify-content: flex-start; border-radius: 0; text-align: left;">
                            👤 Account Details
                        </button>
                        <button class="btn btn-sm btn-ghost" style="justify-content: flex-start; border-radius: 0; text-align: left;">
                            🔒 Security & Password
                        </button>
                        <button class="btn btn-sm btn-ghost" style="justify-content: flex-start; border-radius: 0; text-align: left;">
                            📦 My Orders
                        </button>
                        <button class="btn btn-sm btn-ghost" style="justify-content: flex-start; border-radius: 0; text-align: left;">
                            📚 My Courses
                        </button>
                        <button class="btn btn-sm btn-ghost" style="justify-content: flex-start; border-radius: 0; text-align: left;">
                            ❤️ Wishlist
                        </button>
                        <button class="btn btn-sm btn-ghost" style="justify-content: flex-start; border-radius: 0; text-align: left;">
                            🎁 Subscriptions
                        </button>
                        <button class="btn btn-sm btn-ghost" style="justify-content: flex-start; border-radius: 0; text-align: left;">
                            ⚙️ Preferences
                        </button>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div style="grid-column: 2 / -1;">
                <!-- Account Details Tab -->
                <div class="card">
                    <div class="card-header">
                        <h2 style="margin: 0;">Account Details</h2>
                        <button class="btn btn-sm btn-outline">Edit Profile</button>
                    </div>

                    <div class="card-body">
                        <div class="form-row">
                            <div>
                                <div style="color: var(--gray); font-size: 0.9rem;">First Name</div>
                                <div style="font-weight: 600; font-size: 1.1rem; margin-top: var(--spacing-xs);">John</div>
                            </div>
                            <div>
                                <div style="color: var(--gray); font-size: 0.9rem;">Last Name</div>
                                <div style="font-weight: 600; font-size: 1.1rem; margin-top: var(--spacing-xs);">Doe</div>
                            </div>
                        </div>

                        <div class="form-row" style="margin-top: var(--spacing-lg);">
                            <div>
                                <div style="color: var(--gray); font-size: 0.9rem;">Email Address</div>
                                <div style="font-weight: 600; font-size: 1.1rem; margin-top: var(--spacing-xs);">john@example.com</div>
                            </div>
                            <div>
                                <div style="color: var(--gray); font-size: 0.9rem;">Phone Number</div>
                                <div style="font-weight: 600; font-size: 1.1rem; margin-top: var(--spacing-xs);">+1 (555) 000-0000</div>
                            </div>
                        </div>

                        <div style="margin-top: var(--spacing-lg);">
                            <div style="color: var(--gray); font-size: 0.9rem;">Member Since</div>
                            <div style="font-weight: 600; font-size: 1.1rem; margin-top: var(--spacing-xs);">January 15, 2024</div>
                        </div>
                    </div>
                </div>

                <!-- Statistics Cards -->
                <div class="grid grid-cols-3 grid-gap-lg" style="margin-top: var(--spacing-xl);">
                    <div class="card">
                        <div class="flex-between gap-md">
                            <div>
                                <p style="color: var(--gray); margin: 0;">Total Orders</p>
                                <h3 style="margin: var(--spacing-sm) 0 0 0; color: var(--primary);">12</h3>
                            </div>
                            <div style="font-size: 2.5rem;">📦</div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="flex-between gap-md">
                            <div>
                                <p style="color: var(--gray); margin: 0;">Courses Enrolled</p>
                                <h3 style="margin: var(--spacing-sm) 0 0 0; color: var(--success);">5</h3>
                            </div>
                            <div style="font-size: 2.5rem;">📚</div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="flex-between gap-md">
                            <div>
                                <p style="color: var(--gray); margin: 0;">Total Spent</p>
                                <h3 style="margin: var(--spacing-sm) 0 0 0; color: var(--warning);">$2,499</h3>
                            </div>
                            <div style="font-size: 2.5rem;">💳</div>
                        </div>
                    </div>
                </div>

                <!-- Recent Orders -->
                <div class="card" style="margin-top: var(--spacing-xl);">
                    <div class="card-header">
                        <h3 style="margin: 0;">Recent Orders</h3>
                        <a href="#" class="btn btn-sm btn-ghost">View All</a>
                    </div>

                    <div class="card-body">
                        <div class="table-container">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Order ID</th>
                                        <th>Date</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @for ($i = 1; $i <= 3; $i++)
                                        <tr>
                                            <td>#ORD-{{ 10000 + $i }}</td>
                                            <td>{{ now()->subDays($i * 5)->format('M d, Y') }}</td>
                                            <td>${{ 99.99 * $i }}</td>
                                            <td>
                                                @if ($i == 1)
                                                    <span class="badge badge-success">✓ Delivered</span>
                                                @else
                                                    <span class="badge badge-primary">📦 Shipped</span>
                                                @endif
                                            </td>
                                            <td>
                                                <a href="#" class="btn btn-sm btn-ghost">View →</a>
                                            </td>
                                        </tr>
                                    @endfor
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Danger Zone -->
                <div class="card" style="margin-top: var(--spacing-xl); border-left-color: var(--danger);">
                    <div class="card-header">
                        <h3 style="margin: 0; color: var(--danger);">⚠️ Danger Zone</h3>
                    </div>

                    <div class="card-body">
                        <p style="color: var(--gray);">
                            Once you delete your account, there is no going back. Please be certain.
                        </p>
                    </div>

                    <div class="card-footer">
                        <button class="btn btn-sm btn-danger">🗑️ Delete Account</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
