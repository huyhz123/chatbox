@extends('installer.layout', [
    'title' => 'Welcome',
    'subtitle' => 'Easy Installation Wizard',
    'showSteps' => true,
    'currentStep' => 1
])

@section('content')
<div style="text-align: center;">
    <div class="welcome-icon">🎉</div>
    <h2 style="margin-bottom: 20px; color: #333;">Welcome to Laravel E-Commerce Platform!</h2>
    <p style="color: #6c757d; font-size: 16px; line-height: 1.6;">
        Thank you for choosing our platform. This installation wizard will help you set up your e-commerce website in just a few minutes.
    </p>
</div>

<div class="feature-grid">
    <div class="feature-item">
        <div class="feature-icon">🛒</div>
        <h4>Multi-Product Types</h4>
        <p style="font-size: 14px; color: #6c757d;">Services, Products, Files & Courses</p>
    </div>
    <div class="feature-item">
        <div class="feature-icon">💳</div>
        <h4>7 Payment Gateways</h4>
        <p style="font-size: 14px; color: #6c757d;">VNPay, Momo, Stripe & More</p>
    </div>
    <div class="feature-item">
        <div class="feature-icon">🌍</div>
        <h4>Multi-Language</h4>
        <p style="font-size: 14px; color: #6c757d;">Vietnamese, English, Chinese</p>
    </div>
    <div class="feature-item">
        <div class="feature-icon">🤖</div>
        <h4>AI Chatbot</h4>
        <p style="font-size: 14px; color: #6c757d;">Powered by OpenAI</p>
    </div>
</div>

<div class="info-box">
    <strong>📋 Before you begin:</strong>
    <ul style="margin: 10px 0 0 20px; line-height: 1.8;">
        <li>Ensure you have database credentials ready</li>
        <li>Make sure your server meets all requirements</li>
        <li>Have your admin email and password ready</li>
        <li>This process will take approximately 5 minutes</li>
    </ul>
</div>

<div class="button-group">
    <a href="{{ route('installer.requirements') }}" class="btn btn-primary btn-block">
        Get Started →
    </a>
</div>

<p style="text-align: center; margin-top: 30px; color: #6c757d; font-size: 14px;">
    Need help? Check our <a href="#" style="color: #667eea;">documentation</a> or <a href="#" style="color: #667eea;">contact support</a>
</p>
@endsection
