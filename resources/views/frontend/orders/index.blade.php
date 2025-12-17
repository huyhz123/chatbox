@extends('frontend.layouts.app')

@section('title', 'My Orders')

@section('content')
<div class="container py-5">
    <div class="row">
        <div class="col-12">
            <h1 class="mb-4">My Orders</h1>

            @if($orders->count() > 0)
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Order #</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                        <th>Items</th>
                                        <th>Total</th>
                                        <th>Payment</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($orders as $order)
                                        <tr>
                                            <td>
                                                <a href="{{ route('orders.show', $order->id) }}" class="text-decoration-none">
                                                    <strong>{{ $order->order_number }}</strong>
                                                </a>
                                            </td>
                                            <td>{{ $order->created_at->format('M d, Y') }}</td>
                                            <td>
                                                @php
                                                    $statusColors = [
                                                        'pending' => 'warning',
                                                        'processing' => 'info',
                                                        'completed' => 'success',
                                                        'cancelled' => 'danger',
                                                        'refunded' => 'secondary',
                                                    ];
                                                    $color = $statusColors[$order->status] ?? 'secondary';
                                                @endphp
                                                <span class="badge bg-{{ $color }}">
                                                    {{ ucfirst($order->status) }}
                                                </span>
                                            </td>
                                            <td>{{ $order->items->count() }} items</td>
                                            <td><strong>${{ number_format($order->total_amount, 2) }}</strong></td>
                                            <td>
                                                @if($order->payment)
                                                    @php
                                                        $paymentColors = [
                                                            'pending' => 'warning',
                                                            'completed' => 'success',
                                                            'failed' => 'danger',
                                                            'refunded' => 'secondary',
                                                        ];
                                                        $color = $paymentColors[$order->payment->status] ?? 'secondary';
                                                    @endphp
                                                    <span class="badge bg-{{ $color }}">
                                                        {{ ucfirst($order->payment->status) }}
                                                    </span>
                                                @else
                                                    <span class="badge bg-secondary">No payment</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('orders.show', $order->id) }}" class="btn btn-sm btn-outline-primary" title="View Details">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    @if(in_array($order->status, ['pending', 'processing']))
                                                        <form action="{{ route('orders.cancel', $order->id) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Cancel Order" onclick="return confirm('Are you sure you want to cancel this order?')">
                                                                <i class="fas fa-times"></i>
                                                            </button>
                                                        </form>
                                                    @endif
                                                    @if($order->status === 'completed')
                                                        <a href="{{ route('orders.invoice', $order->id) }}" class="btn btn-sm btn-outline-secondary" title="Download Invoice" target="_blank">
                                                            <i class="fas fa-file-pdf"></i>
                                                        </a>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="mt-4">
                            {{ $orders->links() }}
                        </div>
                    </div>
                </div>
            @else
                <!-- No Orders -->
                <div class="card shadow-sm">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-shopping-bag fa-5x text-muted mb-4"></i>
                        <h3>No orders yet</h3>
                        <p class="text-muted mb-4">You haven't placed any orders yet.</p>
                        <a href="{{ route('products.index') }}" class="btn btn-primary">
                            <i class="fas fa-shopping-cart me-2"></i> Start Shopping
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
