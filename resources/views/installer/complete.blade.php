@extends('installer.layout', [
    'title' => 'Installation Complete',
    'subtitle' => 'Your Site is Ready!',
    'showSteps' => true,
    'currentStep' => 5
])

@section('content')
<div style="text-align: center;">
    <div class="welcome-icon">✅</div>
    <h2 style="margin-bottom: 20px; color: #28a745;">Installation Completed Successfully!</h2>
    <p style="color: #6c757d; font-size: 16px; line-height: 1.6;">
        Congratulations! Your Laravel E-Commerce platform has been installed successfully and is ready to use.
    </p>
</div>

<div class="alert alert-success" style="margin: 30px 0;">
    <strong>🎉 What's been set up:</strong>
    <ul style="margin: 10px 0 0 20px; line-height: 1.8;">
        <li>✓ Database tables created</li>
        <li>✓ Administrator account created</li>
        <li>✓ Roles and permissions configured</li>
        <li>✓ Application key generated</li>
        <li>✓ Storage directories linked</li>
    </ul>
</div>

@if($adminInfo)
<div class="info-box" style="background: #fff3cd; border-left-color: #ffc107;">
    <strong>🔑 Your Admin Credentials:</strong>
    <div style="margin-top: 15px; padding: 15px; background: white; border-radius: 8px; font-family: monospace;">
        <div style="margin-bottom: 10px;">
            <strong>Email:</strong> {{ $adminInfo['email'] }}
        </div>
        <div>
            <strong>Password:</strong> (as entered during installation)
        </div>
    </div>
    <p style="margin-top: 10px; color: #856404;">
        <strong>⚠️ Important:</strong> Please save these credentials in a secure location. You'll need them to access the admin panel.
    </p>
</div>
@endif

<div style="margin: 30px 0;">
    <h3 style="margin-bottom: 15px; color: #555;">Next Steps:</h3>
    <ol style="line-height: 2; padding-left: 20px;">
        <li><strong>Delete install files:</strong> For security, delete the <code>/install</code> route from your routes file</li>
        <li><strong>Configure payment gateways:</strong> Add your payment gateway credentials in Settings</li>
        <li><strong>Setup email:</strong> Configure SMTP settings for email notifications</li>
        <li><strong>Add products:</strong> Start adding your services, products, files, and courses</li>
        <li><strong>Customize design:</strong> Edit views and CSS to match your brand</li>
        <li><strong>Test everything:</strong> Place test orders to ensure everything works</li>
    </ol>
</div>

<div class="feature-grid">
    <div class="feature-item">
        <div class="feature-icon">👤</div>
        <h4>Admin Panel</h4>
        <a href="/admin" class="btn btn-primary" style="margin-top: 10px;">
            Go to Admin
        </a>
    </div>
    <div class="feature-item">
        <div class="feature-icon">🏪</div>
        <h4>Frontend</h4>
        <a href="/" class="btn btn-secondary" style="margin-top: 10px;">
            View Website
        </a>
    </div>
    <div class="feature-item">
        <div class="feature-icon">📚</div>
        <h4>Documentation</h4>
        <a href="/README.md" class="btn btn-secondary" style="margin-top: 10px;">
            Read Docs
        </a>
    </div>
    <div class="feature-item">
        <div class="feature-icon">🔧</div>
        <h4>Settings</h4>
        <a href="/admin/settings" class="btn btn-secondary" style="margin-top: 10px;">
            Configure
        </a>
    </div>
</div>

<div style="text-align: center; margin-top: 40px; padding: 30px; background: #f8f9fa; border-radius: 12px;">
    <p style="font-size: 18px; color: #333; margin-bottom: 15px;">
        <strong>Thank you for choosing Laravel E-Commerce!</strong>
    </p>
    <p style="color: #6c757d;">
        Need help? Check our documentation or contact support at <a href="mailto:support@example.com" style="color: #667eea;">support@example.com</a>
    </p>
</div>
@endsection
