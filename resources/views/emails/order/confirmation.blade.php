@extends('emails.layout')

@section('title', 'Order Confirmation - ' . config('app.name'))

@section('content')
    <h2>Order Confirmation</h2>

    <p>Hi {{ $order->user->name }},</p>

    <p>Thank you for your order! We've received your order and will process it shortly.</p>

    <div class="info-box">
        <strong>Order Number:</strong> {{ $order->order_number }}<br>
        <strong>Order Date:</strong> {{ $order->created_at->format('F d, Y') }}<br>
        <strong>Order Status:</strong> {{ ucfirst($order->status) }}
    </div>

    <h3 style="margin-top: 30px;">Order Details</h3>

    <table class="table">
        <thead>
            <tr>
                <th>Item</th>
                <th>Quantity</th>
                <th>Price</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $item)
            <tr>
                <td>{{ $item->orderable->name }}</td>
                <td>{{ $item->quantity }}</td>
                <td>{{ $order->currency }} {{ number_format($item->price, 2) }}</td>
                <td>{{ $order->currency }} {{ number_format($item->price * $item->quantity, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3" style="text-align: right; font-weight: 600;">Total:</td>
                <td style="font-weight: 600;">{{ $order->currency }} {{ number_format($order->total_amount, 2) }}</td>
            </tr>
        </tfoot>
    </table>

    @if($order->payment_status === 'pending')
    <p style="text-align: center;">
        <a href="{{ route('payment.show', $order->id) }}" class="button">
            Complete Payment
        </a>
    </p>
    @endif

    <p style="margin-top: 30px;">You can view your order status anytime by visiting your account.</p>

    <p style="text-align: center; margin-top: 30px;">
        <a href="{{ route('profile.orders') }}" class="button">
            View Order
        </a>
    </p>

    <p>Thank you for shopping with us!</p>
@endsection
