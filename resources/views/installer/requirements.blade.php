@extends('installer.layout', [
    'title' => 'System Requirements',
    'subtitle' => 'Checking Your Server',
    'showSteps' => true,
    'currentStep' => 2
])

@section('content')
<h2 style="margin-bottom: 30px; color: #333;">System Requirements Check</h2>

<!-- PHP Version -->
<div style="margin-bottom: 30px;">
    <h3 style="margin-bottom: 15px; color: #555;">PHP Version</h3>
    <ul class="requirement-list">
        <li class="requirement-item {{ $requirements['php']['status'] ? 'passed' : 'failed' }}">
            <div>
                <strong>PHP {{ $requirements['php']['required'] }}+</strong>
                <br>
                <small>Current: {{ $requirements['php']['current'] }}</small>
            </div>
            <span class="status-badge {{ $requirements['php']['status'] ? 'passed' : 'failed' }}">
                {{ $requirements['php']['status'] ? '✓ Passed' : '✗ Failed' }}
            </span>
        </li>
    </ul>
</div>

<!-- PHP Extensions -->
<div style="margin-bottom: 30px;">
    <h3 style="margin-bottom: 15px; color: #555;">PHP Extensions</h3>
    <ul class="requirement-list">
        @foreach($requirements['extensions'] as $extension => $status)
        <li class="requirement-item {{ $status ? 'passed' : 'failed' }}">
            <strong>{{ $extension }}</strong>
            <span class="status-badge {{ $status ? 'passed' : 'failed' }}">
                {{ $status ? '✓ Enabled' : '✗ Disabled' }}
            </span>
        </li>
        @endforeach
    </ul>
</div>

<!-- Directory Permissions -->
<div style="margin-bottom: 30px;">
    <h3 style="margin-bottom: 15px; color: #555;">Directory Permissions</h3>
    <ul class="requirement-list">
        @foreach($requirements['permissions'] as $directory => $status)
        <li class="requirement-item {{ $status ? 'passed' : 'failed' }}">
            <div>
                <strong>{{ $directory }}</strong>
                <br>
                <small>Must be writable</small>
            </div>
            <span class="status-badge {{ $status ? 'passed' : 'failed' }}">
                {{ $status ? '✓ Writable' : '✗ Not Writable' }}
            </span>
        </li>
        @endforeach
    </ul>
</div>

<!-- Server Information -->
@if(isset($serverInfo))
<div style="margin-bottom: 30px;">
    <h3 style="margin-bottom: 15px; color: #555;">Server Information</h3>
    <div class="info-box" style="background: #f8f9fa; border-left-color: #6c757d;">
        <div style="font-family: monospace; font-size: 13px; line-height: 1.8;">
            <div><strong>Server:</strong> {{ $serverInfo['software'] }}</div>
            <div><strong>Memory Limit:</strong> {{ $serverInfo['memory_limit'] }}</div>
            <div><strong>Max Execution Time:</strong> {{ $serverInfo['max_execution_time'] }}s</div>
            <div><strong>Upload Max Filesize:</strong> {{ $serverInfo['upload_max_filesize'] }}</div>
            <div><strong>Post Max Size:</strong> {{ $serverInfo['post_max_size'] }}</div>
        </div>
    </div>
</div>
@endif

@if(!$allPassed)
<div class="alert alert-danger">
    <strong>⚠️ Requirements Not Met</strong>
    <p style="margin-top: 10px;">
        Please fix the issues above before continuing with the installation.
        Contact your hosting provider if you need help enabling PHP extensions or fixing permissions.
    </p>
</div>
@else
<div class="alert alert-success">
    <strong>✓ All Requirements Met!</strong>
    <p style="margin-top: 10px;">
        Your server meets all the requirements. You can proceed to the next step.
    </p>
</div>
@endif

<div class="button-group">
    <a href="{{ route('installer.welcome') }}" class="btn btn-secondary">
        ← Back
    </a>
    @if($allPassed)
    <a href="{{ route('installer.environment') }}" class="btn btn-primary" style="flex: 1;">
        Continue →
    </a>
    @else
    <button class="btn btn-primary" style="flex: 1; opacity: 0.5; cursor: not-allowed;" disabled>
        Continue →
    </button>
    @endif
</div>
@endsection
