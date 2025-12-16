@extends('emails.layout')

@section('title', 'Reset Your Password - ' . config('app.name'))

@section('content')
    <h2>Reset Your Password</h2>

    <p>Hi {{ $user->name }},</p>

    <p>We received a request to reset the password for your account. Click the button below to reset it:</p>

    <p style="text-align: center; margin: 30px 0;">
        <a href="{{ $resetUrl }}" class="button">
            Reset Password
        </a>
    </p>

    <div class="info-box">
        <strong>⏰ This link will expire in 60 minutes</strong><br>
        For your security, this password reset link is only valid for 1 hour.
    </div>

    <p>If you didn't request a password reset, you can safely ignore this email. Your password will not be changed.</p>

    <p>Alternatively, you can copy and paste this URL into your browser:</p>
    <p style="font-size: 13px; word-break: break-all; color: #667eea;">{{ $resetUrl }}</p>

    <p style="margin-top: 30px; color: #999; font-size: 14px;">
        If you're having trouble clicking the button, copy and paste the URL above into your web browser.
    </p>
@endsection
