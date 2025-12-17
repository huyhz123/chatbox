@extends('frontend.layouts.app')

@section('title', 'Order #' . $order->order_number)

@section('content')
<div class="container py-5">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1>Order #{{ $order->order_number }}</h1>
                <a href="{{ route('orders.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i> Back to Orders
                </a>
            </div>

            <div class="row">
                <!-- Order Details -->
                <div class="col-lg-8">
                    <!-- Order Status -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <h6 class="text-muted mb-1">Order Date</h6>
                                    <p>{{ $order->created_at->format('M d, Y') }}</p>
                                </div>
                                <div class="col-md-3">
                                    <h6 class="text-muted mb-1">Order Status</h6>
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
                                    <p><span class="badge bg-{{ $color }}">{{ ucfirst($order->status) }}</span></p>
                                </div>
                                <div class="col-md-3">
                                    <h6 class="text-muted mb-1">Payment Status</h6>
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
                                        <p><span class="badge bg-{{ $color }}">{{ ucfirst($order->payment->status) }}</span></p>
                                    @else
                                        <p><span class="badge bg-secondary">No payment</span></p>
                                    @endif
                                </div>
                                <div class="col-md-3">
                                    <h6 class="text-muted mb-1">Total Amount</h6>
                                    <p><strong class="text-primary">${{ number_format($order->total_amount, 2) }}</strong></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Order Items -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">Order Items</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Item</th>
                                            <th>Type</th>
                                            <th>Price</th>
                                            <th>Quantity</th>
                                            <th>Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($order->items as $item)
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        @if($item->itemable && ($item->itemable->image ?? false))
                                                            <img src="{{ asset('storage/' . $item->itemable->image) }}"
                                                                 alt="{{ $item->itemable->name ?? $item->itemable->title }}"
                                                                 class="img-thumbnail me-3"
                                                                 style="width: 60px; height: 60px; object-fit: cover;">
                                                        @endif
                                                        <div>
                                                            <h6 class="mb-0">{{ $item->itemable->name ?? $item->itemable->title ?? 'Item' }}</h6>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="badge bg-info">
                                                        {{ ucfirst(class_basename($item->item_type)) }}
                                                    </span>
                                                </td>
                                                <td>${{ number_format($item->price, 2) }}</td>
                                                <td>{{ $item->quantity }}</td>
                                                <td><strong>${{ number_format($item->price * $item->quantity, 2) }}</strong></td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Shipping Information -->
                    @if($order->shippingMethod)
                        <div class="card shadow-sm mb-4">
                            <div class="card-header bg-light">
                                <h5 class="mb-0">Shipping Information</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h6 class="text-muted mb-1">Shipping Method</h6>
                                        <p>{{ $order->shippingMethod->name }}</p>
                                        <p class="text-muted small">{{ $order->shippingMethod->description }}</p>
                                    </div>
                                    <div class="col-md-3">
                                        <h6 class="text-muted mb-1">Shipping Cost</h6>
                                        <p>${{ number_format($order->shipping_cost ?? 0, 2) }}</p>
                                    </div>
                                    <div class="col-md-3">
                                        <h6 class="text-muted mb-1">Tracking Number</h6>
                                        <p>{{ $order->tracking_number ?? 'Not available yet' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Sidebar -->
                <div class="col-lg-4">
                    <!-- Order Summary -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">Order Summary</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Subtotal:</span>
                                <span>${{ number_format($order->subtotal ?? $order->total_amount, 2) }}</span>
                            </div>
                            @if($order->shipping_cost > 0)
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Shipping:</span>
                                    <span>${{ number_format($order->shipping_cost, 2) }}</span>
                                </div>
                            @endif
                            @if($order->discount_amount > 0)
                                <div class="d-flex justify-content-between mb-2 text-success">
                                    <span>Discount:</span>
                                    <span>-${{ number_format($order->discount_amount, 2) }}</span>
                                </div>
                            @endif
                            <hr>
                            <div class="d-flex justify-content-between mb-3">
                                <strong>Total:</strong>
                                <strong class="text-primary">${{ number_format($order->total_amount, 2) }}</strong>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">Actions</h5>
                        </div>
                        <div class="card-body">
                            @if($order->status === 'completed')
                                <a href="{{ route('orders.invoice', $order->id) }}" class="btn btn-primary w-100 mb-2" target="_blank">
                                    <i class="fas fa-file-pdf me-2"></i> Download Invoice
                                </a>
                            @endif

                            @if(in_array($order->status, ['pending', 'processing']))
                                <form action="{{ route('orders.cancel', $order->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-danger w-100" onclick="return confirm('Are you sure you want to cancel this order?')">
                                        <i class="fas fa-times me-2"></i> Cancel Order
                                    </button>
                                </form>
                            @endif

                            @if($order->payment && $order->payment->status === 'pending')
                                <a href="{{ route('checkout.index') }}" class="btn btn-success w-100 mt-2">
                                    <i class="fas fa-credit-card me-2"></i> Complete Payment
                                </a>
                            @endif
                        </div>
                    </div>

                    <!-- Payment Information -->
                    @if($order->payment)
                        <div class="card shadow-sm">
                            <div class="card-header bg-light">
                                <h5 class="mb-0">Payment Information</h5>
                            </div>
                            <div class="card-body">
                                <h6 class="text-muted mb-1">Payment Method</h6>
                                <p>{{ ucfirst($order->payment->payment_method) }}</p>

                                <h6 class="text-muted mb-1">Transaction ID</h6>
                                <p><code>{{ $order->payment->transaction_id ?? 'N/A' }}</code></p>

                                <h6 class="text-muted mb-1">Payment Date</h6>
                                <p>{{ $order->payment->created_at->format('M d, Y H:i') }}</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
