<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice - {{ $order->order_number }}</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #667eea; padding-bottom: 20px; }
        .header h1 { color: #667eea; margin: 0; }
        .info-section { margin: 20px 0; }
        .info-section h3 { color: #667eea; margin-bottom: 10px; }
        .info-box { background: #f8f9fa; padding: 15px; margin: 10px 0; border-radius: 5px; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th { background: #667eea; color: white; padding: 12px; text-align: left; }
        td { padding: 10px; border-bottom: 1px solid #ddd; }
        .text-right { text-align: right; }
        .total-row { font-weight: bold; font-size: 14px; background: #f8f9fa; }
        .footer { margin-top: 50px; text-align: center; font-size: 10px; color: #666; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ config('app.name') }}</h1>
        <p>{{ config('app.url') }}</p>
        <h2>INVOICE</h2>
    </div>

    <div class="info-section">
        <div class="info-box">
            <strong>Invoice To:</strong><br>
            {{ $order->user->name }}<br>
            {{ $order->user->email }}<br>
            {{ $order->user->phone ?? 'N/A' }}<br>
            {{ $order->user->address ?? 'N/A' }}
        </div>
    </div>

    <div class="info-section">
        <h3>Order Details</h3>
        <div class="info-box">
            <strong>Order Number:</strong> {{ $order->order_number }}<br>
            <strong>Order Date:</strong> {{ $order->created_at->format('F d, Y') }}<br>
            <strong>Payment Status:</strong> {{ strtoupper($order->payment_status) }}<br>
            <strong>Order Status:</strong> {{ strtoupper($order->status) }}
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Item</th>
                <th>Quantity</th>
                <th class="text-right">Price</th>
                <th class="text-right">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $item)
            <tr>
                <td>{{ $item->orderable->name }}</td>
                <td>{{ $item->quantity }}</td>
                <td class="text-right">{{ $order->currency }} {{ number_format($item->price, 2) }}</td>
                <td class="text-right">{{ $order->currency }} {{ number_format($item->price * $item->quantity, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="3" class="text-right">TOTAL:</td>
                <td class="text-right">{{ $order->currency }} {{ number_format($order->total_amount, 2) }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        <p>Thank you for your business!</p>
        <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
    </div>
</body>
</html>
