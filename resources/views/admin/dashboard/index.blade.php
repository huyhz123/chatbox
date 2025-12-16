@extends('admin.layouts.app')

@section('title', 'Admin Dashboard')
@section('page_title', 'Dashboard')

@section('content')
<!-- Stats Row -->
<div class="grid grid-cols-4 grid-gap-lg mb-xl">
    <!-- Total Revenue -->
    <div class="stat-card">
        <div class="stat-icon primary">💰</div>
        <div class="stat-info">
            <h4>Total Revenue</h4>
            <div class="stat-value">$45,230.00</div>
            <div class="stat-change up">↑ 12% from last month</div>
        </div>
    </div>

    <!-- Total Orders -->
    <div class="stat-card">
        <div class="stat-icon success">📦</div>
        <div class="stat-info">
            <h4>Total Orders</h4>
            <div class="stat-value">1,234</div>
            <div class="stat-change up">↑ 8% from last month</div>
        </div>
    </div>

    <!-- Active Users -->
    <div class="stat-card">
        <div class="stat-icon warning">👥</div>
        <div class="stat-info">
            <h4>Active Users</h4>
            <div class="stat-value">856</div>
            <div class="stat-change up">↑ 5% from last month</div>
        </div>
    </div>

    <!-- Conversion Rate -->
    <div class="stat-card">
        <div class="stat-icon danger">📈</div>
        <div class="stat-info">
            <h4>Conversion Rate</h4>
            <div class="stat-value">3.24%</div>
            <div class="stat-change down">↓ 2% from last month</div>
        </div>
    </div>
</div>

<div class="grid grid-cols-2 grid-gap-lg">
    <!-- Revenue Chart -->
    <div class="card">
        <div class="card-header">
            <h3 style="margin: 0;">Revenue Trend</h3>
            <select class="form-control" style="width: 120px; margin: 0;">
                <option>This Month</option>
                <option>Last Month</option>
                <option>Last 3 Months</option>
                <option>Last Year</option>
            </select>
        </div>

        <div class="card-body">
            <svg viewBox="0 0 400 200" style="width: 100%; height: 200px;">
                <!-- Grid lines -->
                <line x1="0" y1="50" x2="400" y2="50" stroke="#e5e7eb" stroke-width="1"/>
                <line x1="0" y1="100" x2="400" y2="100" stroke="#e5e7eb" stroke-width="1"/>
                <line x1="0" y1="150" x2="400" y2="150" stroke="#e5e7eb" stroke-width="1"/>

                <!-- Chart bars -->
                <rect x="20" y="100" width="30" height="60" fill="#6366f1" rx="4"/>
                <rect x="55" y="80" width="30" height="80" fill="#6366f1" rx="4"/>
                <rect x="90" y="60" width="30" height="100" fill="#6366f1" rx="4"/>
                <rect x="125" y="40" width="30" height="120" fill="#6366f1" rx="4"/>
                <rect x="160" y="50" width="30" height="110" fill="#6366f1" rx="4"/>
                <rect x="195" y="30" width="30" height="130" fill="#6366f1" rx="4"/>
                <rect x="230" y="70" width="30" height="90" fill="#8b5cf6" rx="4"/>
                <rect x="265" y="20" width="30" height="140" fill="#8b5cf6" rx="4"/>
                <rect x="300" y="10" width="30" height="150" fill="#8b5cf6" rx="4"/>
                <rect x="335" y="35" width="30" height="125" fill="#8b5cf6" rx="4"/>

                <!-- X-axis labels -->
                <text x="35" y="190" font-size="12" fill="#9ca3af" text-anchor="middle">Jan</text>
                <text x="70" y="190" font-size="12" fill="#9ca3af" text-anchor="middle">Feb</text>
                <text x="105" y="190" font-size="12" fill="#9ca3af" text-anchor="middle">Mar</text>
                <text x="140" y="190" font-size="12" fill="#9ca3af" text-anchor="middle">Apr</text>
                <text x="175" y="190" font-size="12" fill="#9ca3af" text-anchor="middle">May</text>
                <text x="210" y="190" font-size="12" fill="#9ca3af" text-anchor="middle">Jun</text>
                <text x="245" y="190" font-size="12" fill="#9ca3af" text-anchor="middle">Jul</text>
                <text x="280" y="190" font-size="12" fill="#9ca3af" text-anchor="middle">Aug</text>
                <text x="315" y="190" font-size="12" fill="#9ca3af" text-anchor="middle">Sep</text>
                <text x="350" y="190" font-size="12" fill="#9ca3af" text-anchor="middle">Oct</text>
            </svg>
        </div>
    </div>

    <!-- Order Status Distribution -->
    <div class="card">
        <div class="card-header">
            <h3 style="margin: 0;">Order Status Distribution</h3>
        </div>

        <div class="card-body">
            <div style="display: flex; flex-direction: column; gap: var(--spacing-lg);">
                <div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: var(--spacing-sm);">
                        <span>Pending</span>
                        <span style="font-weight: 600;">234</span>
                    </div>
                    <div style="height: 8px; background: var(--gray-lighter); border-radius: var(--radius-full); overflow: hidden;">
                        <div style="height: 100%; width: 30%; background: var(--warning);"></div>
                    </div>
                </div>

                <div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: var(--spacing-sm);">
                        <span>Processing</span>
                        <span style="font-weight: 600;">445</span>
                    </div>
                    <div style="height: 8px; background: var(--gray-lighter); border-radius: var(--radius-full); overflow: hidden;">
                        <div style="height: 100%; width: 55%; background: var(--primary);"></div>
                    </div>
                </div>

                <div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: var(--spacing-sm);">
                        <span>Shipped</span>
                        <span style="font-weight: 600;">320</span>
                    </div>
                    <div style="height: 8px; background: var(--gray-lighter); border-radius: var(--radius-full); overflow: hidden;">
                        <div style="height: 100%; width: 40%; background: var(--success);"></div>
                    </div>
                </div>

                <div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: var(--spacing-sm);">
                        <span>Delivered</span>
                        <span style="font-weight: 600;">235</span>
                    </div>
                    <div style="height: 8px; background: var(--gray-lighter); border-radius: var(--radius-full); overflow: hidden;">
                        <div style="height: 100%; width: 29%; background: var(--success);"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Orders Table -->
<div class="card mt-xl">
    <div class="card-header">
        <h3 style="margin: 0;">Recent Orders</h3>
        <a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-ghost">View All</a>
    </div>

    <div class="card-body">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @for ($i = 1; $i <= 5; $i++)
                        <tr>
                            <td><strong>#ORD-{{ 10000 + $i }}</strong></td>
                            <td>Customer {{ $i }}</td>
                            <td>${{ number_format(99.99 * $i, 2) }}</td>
                            <td>
                                @switch($i % 4)
                                    @case(0)
                                        <span class="badge badge-primary">📦 Processing</span>
                                        @break
                                    @case(1)
                                        <span class="badge badge-success">✓ Delivered</span>
                                        @break
                                    @case(2)
                                        <span class="badge badge-warning">📤 Shipped</span>
                                        @break
                                    @default
                                        <span class="badge badge-danger">❌ Cancelled</span>
                                @endswitch
                            </td>
                            <td>{{ now()->subDays($i * 2)->format('M d, Y') }}</td>
                            <td>
                                <button class="btn btn-sm btn-ghost" onclick="openModal('orderModal{{ $i }}')">View</button>
                            </td>
                        </tr>
                    @endfor
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Top Products -->
<div class="card mt-xl">
    <div class="card-header">
        <h3 style="margin: 0;">Top Products</h3>
    </div>

    <div class="card-body">
        <div style="display: flex; flex-direction: column; gap: var(--spacing-md);">
            @for ($i = 1; $i <= 5; $i++)
                <div style="display: flex; align-items: center; justify-content: space-between; padding: var(--spacing-md); background: var(--gray-lightest); border-radius: var(--radius-md);">
                    <div style="display: flex; align-items: center; gap: var(--spacing-md); flex: 1;">
                        <div style="width: 50px; height: 50px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: var(--radius-md); display: flex; align-items: center; justify-content: center; color: white; font-size: 1.5rem;">
                            📦
                        </div>
                        <div>
                            <div style="font-weight: 600;">Product {{ $i }}</div>
                            <div style="color: var(--gray); font-size: 0.9rem;">{{ rand(100, 1000) }} sales</div>
                        </div>
                    </div>
                    <div style="text-align: right;">
                        <div style="font-weight: 700; font-size: 1.1rem;">${{ number_format(999.99 - ($i * 100), 2) }}</div>
                        <div style="color: var(--success); font-size: 0.85rem;">↑ {{ rand(5, 25) }}% trending</div>
                    </div>
                </div>
            @endfor
        </div>
    </div>
</div>
@endsection
