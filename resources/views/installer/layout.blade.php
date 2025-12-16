<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Installation' }} - Laravel E-Commerce</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .installer-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 800px;
            width: 100%;
            overflow: hidden;
        }

        .installer-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px;
            text-align: center;
        }

        .installer-header h1 {
            font-size: 32px;
            margin-bottom: 10px;
        }

        .installer-header p {
            font-size: 16px;
            opacity: 0.9;
        }

        .installer-steps {
            display: flex;
            justify-content: space-between;
            padding: 20px 40px;
            background: #f8f9fa;
            border-bottom: 1px solid #e9ecef;
        }

        .step {
            flex: 1;
            text-align: center;
            position: relative;
            padding: 10px;
        }

        .step::after {
            content: '';
            position: absolute;
            top: 20px;
            right: -50%;
            width: 100%;
            height: 2px;
            background: #dee2e6;
        }

        .step:last-child::after {
            display: none;
        }

        .step-number {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #dee2e6;
            color: #6c757d;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-bottom: 8px;
            position: relative;
            z-index: 1;
        }

        .step.active .step-number {
            background: #667eea;
            color: white;
        }

        .step.completed .step-number {
            background: #28a745;
            color: white;
        }

        .step-label {
            font-size: 12px;
            color: #6c757d;
        }

        .step.active .step-label {
            color: #667eea;
            font-weight: 600;
        }

        .installer-content {
            padding: 40px;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 12px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #667eea;
        }

        .form-group small {
            display: block;
            margin-top: 5px;
            color: #6c757d;
            font-size: 12px;
        }

        .btn {
            padding: 14px 30px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.4);
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-success {
            background: #28a745;
            color: white;
        }

        .btn-block {
            width: 100%;
        }

        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-warning {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeeba;
        }

        .requirement-list {
            list-style: none;
        }

        .requirement-item {
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #f8f9fa;
        }

        .requirement-item.passed {
            background: #d4edda;
        }

        .requirement-item.failed {
            background: #f8d7da;
        }

        .status-badge {
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .status-badge.passed {
            background: #28a745;
            color: white;
        }

        .status-badge.failed {
            background: #dc3545;
            color: white;
        }

        .button-group {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }

        .welcome-icon {
            font-size: 64px;
            margin-bottom: 20px;
        }

        .feature-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin: 30px 0;
        }

        .feature-item {
            text-align: center;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 12px;
        }

        .feature-icon {
            font-size: 32px;
            margin-bottom: 10px;
        }

        .info-box {
            background: #e7f3ff;
            border-left: 4px solid #2196F3;
            padding: 15px 20px;
            border-radius: 4px;
            margin: 20px 0;
        }

        @media (max-width: 768px) {
            .installer-steps {
                flex-wrap: wrap;
            }

            .step {
                flex: 1 1 50%;
                margin-bottom: 20px;
            }

            .step::after {
                display: none;
            }

            .button-group {
                flex-direction: column;
            }
        }
    </style>
    @stack('styles')
</head>
<body>
    <div class="installer-container">
        <div class="installer-header">
            <h1>🚀 Laravel E-Commerce</h1>
            <p>{{ $subtitle ?? 'Web Installer' }}</p>
        </div>

        @if(isset($showSteps) && $showSteps)
        <div class="installer-steps">
            <div class="step {{ $currentStep >= 1 ? 'completed' : '' }} {{ $currentStep == 1 ? 'active' : '' }}">
                <div class="step-number">1</div>
                <div class="step-label">Welcome</div>
            </div>
            <div class="step {{ $currentStep >= 2 ? 'completed' : '' }} {{ $currentStep == 2 ? 'active' : '' }}">
                <div class="step-number">2</div>
                <div class="step-label">Requirements</div>
            </div>
            <div class="step {{ $currentStep >= 3 ? 'completed' : '' }} {{ $currentStep == 3 ? 'active' : '' }}">
                <div class="step-number">3</div>
                <div class="step-label">Environment</div>
            </div>
            <div class="step {{ $currentStep >= 4 ? 'completed' : '' }} {{ $currentStep == 4 ? 'active' : '' }}">
                <div class="step-number">4</div>
                <div class="step-label">Admin Account</div>
            </div>
            <div class="step {{ $currentStep >= 5 ? 'completed' : '' }} {{ $currentStep == 5 ? 'active' : '' }}">
                <div class="step-number">5</div>
                <div class="step-label">Complete</div>
            </div>
        </div>
        @endif

        <div class="installer-content">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <strong>Error!</strong>
                    <ul style="margin: 10px 0 0 20px;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if (session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            @yield('content')
        </div>
    </div>

    @stack('scripts')
</body>
</html>
