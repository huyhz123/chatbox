@extends('emails.layout')

@section('title', 'Ticket Status Update - ' . config('app.name'))

@section('content')
    <h2>Ticket Status Update 🎫</h2>

    <p>Hi {{ $ticket->user->name }},</p>

    <p>Your support ticket has been updated.</p>

    <div class="info-box">
        <strong>Ticket ID:</strong> #{{ $ticket->id }}<br>
        <strong>Service:</strong> {{ $ticket->service->name }}<br>
        <strong>Status:</strong> <span style="color: {{ $ticket->status === 'completed' ? '#28a745' : '#667eea' }}; font-weight: 600;">{{ strtoupper($ticket->status) }}</span><br>
        <strong>Created:</strong> {{ $ticket->created_at->format('F d, Y H:i') }}<br>
        @if($ticket->completed_at)
        <strong>Completed:</strong> {{ $ticket->completed_at->format('F d, Y H:i') }}
        @endif
    </div>

    @if($ticket->status === 'completed' && $ticket->response)
    <h3>Response:</h3>
    <div class="info-box" style="background-color: #f0f8ff;">
        {!! nl2br(e($ticket->response)) !!}
    </div>
    @endif

    @if($ticket->status === 'processing')
    <p>Your request is currently being processed. We'll notify you once it's complete.</p>
    @endif

    @if($ticket->status === 'completed')
    <p>Your ticket has been completed successfully. If you have any questions about the result, please don't hesitate to contact us.</p>
    @endif

    @if($ticket->status === 'failed')
    <p>Unfortunately, we couldn't complete your request. Please contact our support team for assistance.</p>
    @endif

    <p style="text-align: center; margin-top: 30px;">
        <a href="{{ route('profile.tickets') }}" class="button">
            View Ticket Details
        </a>
    </p>

    <p>Thank you for using our service!</p>
@endsection
