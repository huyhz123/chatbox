@extends('emails.layout')

@section('title', 'Payment Receipt - ' . config('app.name'))

@section('content')
    <h2>Payment Received</h2>

    <p>Hi {{ $payment->order->user->name }},</p>

    <p>We have successfully received your payment. Thank you!</p>

    <div class="info-box">
        <strong>Transaction ID:</strong> {{ $payment->transaction_id }}<br>
        <strong>Payment Date:</strong> {{ $payment->created_at->format('F d, Y H:i') }}<br>
        <strong>Payment Method:</strong> {{ strtoupper($payment->payment_gateway) }}<br>
        <strong>Amount Paid:</strong> {{ $payment->currency }} {{ number_format($payment->amount, 2) }}
    </div>

    <h3 style="margin-top: 30px;">Order Summary</h3>

    <div class="info-box">
        <strong>Order Number:</strong> {{ $payment->order->order_number }}<br>
        <strong>Order Total:</strong> {{ $payment->order->currency }} {{ number_format($payment->order->total_amount, 2) }}
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>Item</th>
                <th>Quantity</th>
                <th>Price</th>
            </tr>
        </thead>
        <tbody>
            @foreach($payment->order->items as $item)
            <tr>
                <td>{{ $item->orderable->name }}</td>
                <td>{{ $item->quantity }}</td>
                <td>{{ $payment->currency }} {{ number_format($item->price * $item->quantity, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <p style="text-align: center; margin-top: 30px;">
        <a href="{{ route('payment.receipt', $payment->id) }}" class="button">
            Download Receipt (PDF)
        </a>
    </p>

    <p>If you have any questions about this payment, please don't hesitate to contact us.</p>
@endsection
