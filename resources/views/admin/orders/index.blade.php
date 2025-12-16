@extends('admin.layouts.app')

@section('title', 'Admin - Orders')
@section('page_title', 'Orders Management')

@section('content')
<!-- Filters & Actions -->
<div class="flex-between gap-lg mb-lg flex-wrap">
    <div class="flex gap-md" style="flex: 1;">
        <input type="text" placeholder="Search by Order ID or Customer..." class="form-control" style="min-width: 250px;">
        <select class="form-control" style="min-width: 150px;">
            <option value="">All Status</option>
            <option value="pending">Pending</option>
            <option value="processing">Processing</option>
            <option value="shipped">Shipped</option>
            <option value="delivered">Delivered</option>
            <option value="cancelled">Cancelled</option>
        </select>
    </div>
    <div class="flex gap-md">
        <button class="btn btn-ghost">📥 Export CSV</button>
        <button class="btn btn-primary">🔄 Refresh</button>
    </div>
</div>

<!-- Statistics Cards -->
<div class="grid grid-cols-4 grid-gap-lg mb-xl">
    <div class="card">
        <div style="text-align: center;">
            <div style="font-size: 2rem; margin-bottom: var(--spacing-sm);">📦</div>
            <div style="color: var(--gray); font-size: 0.9rem;">Total Orders</div>
            <div style="font-weight: 700; font-size: 1.5rem; color: var(--primary);">{{ rand(1000, 5000) }}</div>
        </div>
    </div>

    <div class="card">
        <div style="text-align: center;">
            <div style="font-size: 2rem; margin-bottom: var(--spacing-sm);">⏳</div>
            <div style="color: var(--gray); font-size: 0.9rem;">Pending</div>
            <div style="font-weight: 700; font-size: 1.5rem; color: var(--warning);">{{ rand(50, 200) }}</div>
        </div>
    </div>

    <div class="card">
        <div style="text-align: center;">
            <div style="font-size: 2rem; margin-bottom: var(--spacing-sm);">🚚</div>
            <div style="color: var(--gray); font-size: 0.9rem;">Shipped</div>
            <div style="font-weight: 700; font-size: 1.5rem; color: var(--primary);">{{ rand(100, 300) }}</div>
        </div>
    </div>

    <div class="card">
        <div style="text-align: center;">
            <div style="font-size: 2rem; margin-bottom: var(--spacing-sm);">✓</div>
            <div style="color: var(--gray); font-size: 0.9rem;">Delivered</div>
            <div style="font-weight: 700; font-size: 1.5rem; color: var(--success);">{{ rand(500, 2000) }}</div>
        </div>
    </div>
</div>

<!-- Orders Table -->
<div class="card">
    <div class="card-header">
        <h3 style="margin: 0;">All Orders</h3>
    </div>

    <div class="card-body">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Items</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @for ($i = 1; $i <= 15; $i++)
                        <tr class="draggable" draggable="true">
                            <td>
                                <strong>#ORD-{{ 100000 + $i }}</strong>
                            </td>
                            <td>
                                <div>
                                    <div style="font-weight: 600;">Customer {{ $i }}</div>
                                    <div style="color: var(--gray); font-size: 0.85rem;">customer{{ $i }}@example.com</div>
                                </div>
                            </td>
                            <td>
                                {{ rand(1, 5) }} items
                            </td>
                            <td>
                                <strong>${{ number_format(99.99 * rand(1, 5), 2) }}</strong>
                            </td>
                            <td>
                                @php
                                $statuses = [
                                    ['badge' => 'warning', 'label' => '⏳ Pending'],
                                    ['badge' => 'primary', 'label' => '⚙️ Processing'],
                                    ['badge' => 'primary', 'label' => '📤 Shipped'],
                                    ['badge' => 'success', 'label' => '✓ Delivered'],
                                    ['badge' => 'danger', 'label' => '❌ Cancelled']
                                ];
                                $status = $statuses[$i % 5];
                                @endphp
                                <span class="badge badge-{{ $status['badge'] }}">{{ $status['label'] }}</span>
                            </td>
                            <td>
                                {{ now()->subDays(rand(0, 30))->format('M d, Y') }}
                            </td>
                            <td>
                                <div class="table-actions">
                                    <button class="btn btn-sm btn-ghost" title="View" onclick="openModal('orderDetailModal{{ $i }}')">👁️</button>
                                    <button class="btn btn-sm btn-ghost" title="Edit">✎</button>
                                    <button class="btn btn-sm btn-ghost" title="Print">🖨️</button>
                                </div>
                            </td>
                        </tr>
                    @endfor
                </tbody>
            </table>
        </div>
    </div>

    <div class="card-footer">
        <div class="pagination">
            <button class="pagination-item disabled">← Previous</button>
            <button class="pagination-item active">1</button>
            <button class="pagination-item">2</button>
            <button class="pagination-item">3</button>
            <button class="pagination-item">4</button>
            <button class="pagination-item">Next →</button>
        </div>
    </div>
</div>

<!-- Order Detail Modal -->
<div id="orderDetailModal1" class="modal">
    <div class="modal-content" style="max-width: 700px;">
        <div class="modal-header">
            <h3 class="modal-title">Order Details - #ORD-100001</h3>
            <button type="button" class="modal-close">×</button>
        </div>
        <div class="modal-body">
            <div class="grid grid-cols-2 grid-gap-lg mb-lg">
                <div>
                    <h5 style="margin: 0 0 var(--spacing-md) 0; color: var(--gray);">Customer Information</h5>
                    <div style="margin-bottom: var(--spacing-sm);">
                        <div style="color: var(--gray); font-size: 0.85rem;">Name</div>
                        <div style="font-weight: 600;">Customer 1</div>
                    </div>
                    <div style="margin-bottom: var(--spacing-sm);">
                        <div style="color: var(--gray); font-size: 0.85rem;">Email</div>
                        <div style="font-weight: 600;">customer1@example.com</div>
                    </div>
                    <div>
                        <div style="color: var(--gray); font-size: 0.85rem;">Phone</div>
                        <div style="font-weight: 600;">+1 (555) 000-0001</div>
                    </div>
                </div>

                <div>
                    <h5 style="margin: 0 0 var(--spacing-md) 0; color: var(--gray);">Order Information</h5>
                    <div style="margin-bottom: var(--spacing-sm);">
                        <div style="color: var(--gray); font-size: 0.85rem;">Order Date</div>
                        <div style="font-weight: 600;">{{ now()->subDays(5)->format('M d, Y H:i') }}</div>
                    </div>
                    <div style="margin-bottom: var(--spacing-sm);">
                        <div style="color: var(--gray); font-size: 0.85rem;">Status</div>
                        <div>
                            <select class="form-control" style="min-width: 100%; margin-top: 4px;">
                                <option>Pending</option>
                                <option selected>Processing</option>
                                <option>Shipped</option>
                                <option>Delivered</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <h5 style="margin-bottom: var(--spacing-md); color: var(--gray);">Order Items</h5>
            <div style="background: var(--gray-lightest); padding: var(--spacing-md); border-radius: var(--radius-md); margin-bottom: var(--spacing-lg);">
                <div style="display: flex; justify-content: space-between; margin-bottom: var(--spacing-md); padding-bottom: var(--spacing-md); border-bottom: 1px solid var(--gray-lighter);">
                    <span>Product 1 (x1)</span>
                    <span style="font-weight: 600;">$99.99</span>
                </div>
                <div style="display: flex; justify-content: space-between; font-weight: 700;">
                    <span>Total</span>
                    <span style="color: var(--primary); font-size: 1.1rem;">$99.99</span>
                </div>
            </div>

            <h5 style="margin-bottom: var(--spacing-md); color: var(--gray);">Shipping Address</h5>
            <div style="background: var(--gray-lightest); padding: var(--spacing-md); border-radius: var(--radius-md);">
                <div style="margin-bottom: var(--spacing-sm);">123 Main Street</div>
                <div style="margin-bottom: var(--spacing-sm);">New York, NY 10001</div>
                <div>United States</div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-sm btn-ghost" onclick="closeModal('orderDetailModal1')">Close</button>
            <button type="button" class="btn btn-sm btn-primary">💾 Save Changes</button>
        </div>
    </div>
</div>
@endsection
