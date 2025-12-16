@extends('emails.layout')

@section('title', 'Welcome to ' . config('app.name'))

@section('content')
    <h2>Welcome to {{ config('app.name') }}! 🎉</h2>

    <p>Hi {{ $user->name }},</p>

    <p>Thank you for creating an account with us! We're excited to have you on board.</p>

    <p>With your account, you can:</p>

    <div style="margin: 25px 0;">
        <p style="margin: 10px 0;">✅ Browse and purchase products and services</p>
        <p style="margin: 10px 0;">✅ Enroll in online courses</p>
        <p style="margin: 10px 0;">✅ Download digital files</p>
        <p style="margin: 10px 0;">✅ Track your orders and purchases</p>
        <p style="margin: 10px 0;">✅ Get support through our ticket system</p>
    </div>

    <div class="info-box">
        <strong>Your Account Details:</strong><br>
        Email: {{ $user->email }}<br>
        Account Created: {{ $user->created_at->format('F d, Y') }}
    </div>

    <p style="text-align: center; margin-top: 30px;">
        <a href="{{ route('home') }}" class="button">
            Start Exploring
        </a>
    </p>

    <p>If you have any questions, our support team is here to help!</p>

    <p>Best regards,<br>The {{ config('app.name') }} Team</p>
@endsection
