@extends('emails.layout')

@section('title', 'Your Download Link - ' . config('app.name'))

@section('content')
    <h2>Your File is Ready to Download 📥</h2>

    <p>Hi {{ $download->user->name }},</p>

    <p>Thank you for your purchase! Your file is now ready to download.</p>

    <div class="info-box">
        <h3 style="margin: 0 0 10px 0; color: #667eea;">{{ $download->file->name }}</h3>
        <p style="margin: 5px 0;">
            <strong>File Type:</strong> {{ strtoupper($download->file->file_type) }}<br>
            <strong>File Size:</strong> {{ $download->file->file_size }}<br>
            <strong>Purchase Date:</strong> {{ $download->created_at->format('F d, Y') }}
        </p>
    </div>

    @if($download->file->download_limit > 0)
    <div class="info-box" style="border-left-color: #ffc107; background-color: #fff3cd;">
        <strong>⚠️ Download Limit:</strong><br>
        You can download this file {{ $download->file->download_limit }} times.<br>
        Downloads remaining: {{ $download->file->download_limit - $download->downloads_count }}
    </div>
    @endif

    <p style="text-align: center; margin: 30px 0;">
        <a href="{{ route('files.download', $download->file->id) }}" class="button">
            Download File
        </a>
    </p>

    <p style="font-size: 14px; color: #666;">
        <strong>Note:</strong> Keep this email for your records. You can also access your downloaded files from your account dashboard.
    </p>

    <p>If you have any issues downloading the file, please contact our support team.</p>
@endsection
