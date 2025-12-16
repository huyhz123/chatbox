@extends('installer.layout', [
    'title' => 'Create Admin Account',
    'subtitle' => 'Setup Your Administrator',
    'showSteps' => true,
    'currentStep' => 4
])

@section('content')
<h2 style="margin-bottom: 30px; color: #333;">Create Administrator Account</h2>

<div class="info-box">
    <strong>🔐 Important:</strong> This will be your main administrator account. Make sure to use a strong password and keep the credentials safe.
</div>

<form method="POST" action="{{ route('installer.install') }}" id="installForm">
    @csrf

    <div class="form-group">
        <label for="name">Full Name *</label>
        <input type="text" id="name" name="name" value="{{ old('name') }}" required autofocus>
        <small>Your full name for the admin account</small>
    </div>

    <div class="form-group">
        <label for="email">Email Address *</label>
        <input type="email" id="email" name="email" value="{{ old('email') }}" required>
        <small>You'll use this email to login</small>
    </div>

    <div class="form-group">
        <label for="password">Password *</label>
        <input type="password" id="password" name="password" required minlength="8">
        <small>Minimum 8 characters</small>
    </div>

    <div class="form-group">
        <label for="password_confirmation">Confirm Password *</label>
        <input type="password" id="password_confirmation" name="password_confirmation" required minlength="8">
        <small>Re-enter your password</small>
    </div>

    <div class="form-group" style="margin-top: 30px;">
        <label style="display: flex; align-items: center; cursor: pointer; background: #f8f9fa; padding: 15px; border-radius: 8px; border: 2px solid #e9ecef;">
            <input type="checkbox" id="import_demo_data" name="import_demo_data" value="1" style="width: auto; margin-right: 10px; cursor: pointer;">
            <div>
                <strong>📦 Import Demo Data</strong>
                <div style="font-size: 14px; color: #6c757d; margin-top: 5px;">
                    Import sample categories, services, products, files, and courses for testing. You can delete them later from the admin panel.
                </div>
            </div>
        </label>
    </div>

    <div class="alert alert-warning">
        <strong>⚠️ Important Notice:</strong>
        <p style="margin-top: 10px;">
            Clicking "Install Now" will:
        </p>
        <ul style="margin: 10px 0 0 20px; line-height: 1.8;">
            <li>Generate application key</li>
            <li>Run database migrations</li>
            <li>Create roles and permissions</li>
            <li>Create your admin account</li>
            <li>Setup storage links</li>
        </ul>
        <p style="margin-top: 10px;">
            This process may take 1-2 minutes. Please do not close this window.
        </p>
    </div>

    <div class="button-group">
        <a href="{{ route('installer.environment') }}" class="btn btn-secondary">
            ← Back
        </a>
        <button type="submit" class="btn btn-success" style="flex: 1;" id="installBtn">
            🚀 Install Now
        </button>
    </div>
</form>
@endsection

@push('scripts')
<script>
document.getElementById('installForm').addEventListener('submit', function(e) {
    const btn = document.getElementById('installBtn');
    btn.disabled = true;
    btn.innerHTML = '⏳ Installing... Please wait';
    btn.style.opacity = '0.6';
    btn.style.cursor = 'wait';
});
</script>
@endpush
