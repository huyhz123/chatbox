@extends('frontend.layouts.app')

@section('title', 'Home - MyApp')
@section('description', 'Welcome to MyApp - Your trusted platform for services, products, and courses')

@section('content')
<!-- Hero Section -->
<section class="py-2xl" style="background: linear-gradient(135deg, rgba(99, 102, 241, 0.1) 0%, rgba(139, 92, 246, 0.1) 100%);">
    <div class="container">
        <div class="flex-center flex-col text-center gap-lg" style="padding: var(--spacing-2xl) 0;">
            <h1 style="margin: 0;">Welcome to MyApp</h1>
            <p style="font-size: 1.2rem; max-width: 600px; margin: 0;">
                Discover a world of possibilities with our comprehensive services, premium products, and expert-led courses.
            </p>
            <div class="flex gap-md justify-center flex-wrap">
                <a href="{{ route('services.index') }}" class="btn btn-primary btn-lg">
                    Explore Services
                </a>
                <a href="{{ route('products.index') }}" class="btn btn-outline btn-lg">
                    Browse Products
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="py-2xl">
    <div class="container">
        <h2 class="text-center mb-xl">Why Choose Us</h2>
        <div class="grid grid-cols-3 grid-gap-lg">
            <!-- Feature 1 -->
            <div class="card text-center">
                <div style="font-size: 3rem; margin-bottom: var(--spacing-lg);">⚡</div>
                <h3>Lightning Fast</h3>
                <p>Experience blazing-fast performance with our optimized platform built for speed.</p>
            </div>

            <!-- Feature 2 -->
            <div class="card text-center">
                <div style="font-size: 3rem; margin-bottom: var(--spacing-lg);">🛡️</div>
                <h3>Secure & Reliable</h3>
                <p>Your data is protected with enterprise-grade security and 99.9% uptime guarantee.</p>
            </div>

            <!-- Feature 3 -->
            <div class="card text-center">
                <div style="font-size: 3rem; margin-bottom: var(--spacing-lg);">🤝</div>
                <h3>24/7 Support</h3>
                <p>Our dedicated support team is always ready to help you succeed every step of the way.</p>
            </div>
        </div>
    </div>
</section>

<!-- Popular Services Section -->
<section class="py-2xl" style="background: var(--gray-lightest);">
    <div class="container">
        <h2 class="text-center mb-xl">Our Services</h2>
        <div class="grid grid-cols-2 grid-gap-lg">
            <div class="card card-primary">
                <h3>Web Development</h3>
                <p>Custom web applications tailored to your business needs with modern technologies.</p>
                <a href="{{ route('services.index') }}" class="btn btn-sm btn-primary">Learn More →</a>
            </div>

            <div class="card card-success">
                <h3>Mobile Apps</h3>
                <p>Native and cross-platform mobile applications for iOS and Android devices.</p>
                <a href="{{ route('services.index') }}" class="btn btn-sm btn-primary">Learn More →</a>
            </div>

            <div class="card card-warning">
                <h3>Cloud Solutions</h3>
                <p>Scalable cloud infrastructure and services to grow your business effortlessly.</p>
                <a href="{{ route('services.index') }}" class="btn btn-sm btn-primary">Learn More →</a>
            </div>

            <div class="card card-danger">
                <h3>Consulting</h3>
                <p>Expert consulting services to guide your digital transformation journey.</p>
                <a href="{{ route('services.index') }}" class="btn btn-sm btn-primary">Learn More →</a>
            </div>
        </div>
    </div>
</section>

<!-- Popular Products Section -->
<section class="py-2xl">
    <div class="container">
        <h2 class="text-center mb-xl">Featured Products</h2>
        <div class="grid grid-cols-3 grid-gap-lg">
            @for ($i = 1; $i <= 6; $i++)
                <div class="card">
                    <div style="aspect-ratio: 4/3; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: var(--radius-lg); margin-bottom: var(--spacing-md); display: flex; align-items: center; justify-content: center; color: white; font-size: 3rem;">
                        📦
                    </div>
                    <h4>Product {{ $i }}</h4>
                    <p>High-quality product with premium features and excellent performance.</p>
                    <div class="flex-between">
                        <span style="font-size: 1.3rem; font-weight: 700; color: var(--primary);">$99.99</span>
                        <a href="{{ route('products.index') }}" class="btn btn-sm btn-primary">View</a>
                    </div>
                </div>
            @endfor
        </div>
    </div>
</section>

<!-- Courses Section -->
<section class="py-2xl" style="background: var(--gray-lightest);">
    <div class="container">
        <h2 class="text-center mb-xl">Popular Courses</h2>
        <div class="grid grid-cols-4 grid-gap-lg">
            @for ($i = 1; $i <= 8; $i++)
                <div class="card">
                    <div style="aspect-ratio: 16/9; background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); border-radius: var(--radius-lg); margin-bottom: var(--spacing-md); display: flex; align-items: center; justify-content: center; color: white; font-size: 2rem;">
                        📚
                    </div>
                    <h4>Course {{ $i }}</h4>
                    <p style="font-size: 0.9rem; margin-bottom: var(--spacing-md);">Learn from industry experts in this comprehensive course.</p>
                    <div class="flex-between" style="align-items: flex-end;">
                        <div>
                            <div style="font-size: 0.85rem; color: var(--gray); margin-bottom: var(--spacing-xs);">24 Lessons</div>
                            <span style="font-weight: 700; color: var(--primary);">$49.99</span>
                        </div>
                        <a href="{{ route('courses.index') }}" class="btn btn-sm btn-primary">Enroll</a>
                    </div>
                </div>
            @endfor
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="py-2xl" style="background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); color: white;">
    <div class="container">
        <div class="flex-center flex-col text-center gap-lg">
            <h2 style="color: white; margin: 0;">Ready to Get Started?</h2>
            <p style="color: rgba(255,255,255,0.9); font-size: 1.1rem; margin: 0;">
                Join thousands of satisfied customers who trust MyApp for their business solutions.
            </p>
            <a href="{{ route('register') }}" class="btn btn-lg" style="background: white; color: #6366f1;">
                Sign Up Now
            </a>
        </div>
    </div>
</section>

<!-- Testimonials Section -->
<section class="py-2xl">
    <div class="container-sm">
        <h2 class="text-center mb-xl">What Our Customers Say</h2>
        <div class="grid grid-cols-3 grid-gap-lg">
            @for ($i = 1; $i <= 3; $i++)
                <div class="card">
                    <div style="display: flex; gap: var(--spacing-sm); margin-bottom: var(--spacing-md);">
                        @for ($j = 0; $j < 5; $j++)
                            <span style="color: #fbbf24; font-size: 1.2rem;">⭐</span>
                        @endfor
                    </div>
                    <p style="font-style: italic; margin-bottom: var(--spacing-md);">
                        "Amazing experience! The platform is user-friendly, and the support team is incredibly responsive. Highly recommended!"
                    </p>
                    <div class="flex gap-md">
                        <img src="https://via.placeholder.com/50" alt="Customer" class="rounded-full" style="width: 50px; height: 50px; object-fit: cover;">
                        <div>
                            <h5 style="margin: 0;">Customer {{ $i }}</h5>
                            <p style="margin: 0; font-size: 0.9rem;">CEO at Company {{ $i }}</p>
                        </div>
                    </div>
                </div>
            @endfor
        </div>
    </div>
</section>
@endsection
