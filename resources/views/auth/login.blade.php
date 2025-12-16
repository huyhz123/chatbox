<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('auth.login') }} - {{ config('app.name') }}</title>
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <h1>{{ __('auth.login') }}</h1>

            @if ($errors->any())
                <div class="alert alert-error">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="form-group">
                    <label for="email">{{ __('auth.email') }}</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus>
                </div>

                <div class="form-group">
                    <label for="password">{{ __('auth.password') }}</label>
                    <input type="password" id="password" name="password" required>
                </div>

                <div class="form-group">
                    <label>
                        <input type="checkbox" name="remember"> {{ __('auth.remember_me') }}
                    </label>
                </div>

                <button type="submit" class="btn btn-primary btn-block">{{ __('auth.login') }}</button>
            </form>

            <div class="auth-links">
                <a href="{{ route('register') }}">{{ __('auth.register') }}</a>
            </div>
        </div>
    </div>
</body>
</html>
