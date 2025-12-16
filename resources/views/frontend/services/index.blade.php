@extends('frontend.layouts.app')

@section('title', 'Services - MyApp')
@section('description', 'Browse our comprehensive range of professional services')

@section('content')
<!-- Page Header -->
<section class="py-2xl" style="background: linear-gradient(135deg, rgba(99, 102, 241, 0.1) 0%, rgba(139, 92, 246, 0.1) 100%);">
    <div class="container">
        <h1 style="text-align: center; margin: 0 0 var(--spacing-lg) 0;">Our Services</h1>
        <p style="text-align: center; max-width: 600px; margin: 0 auto; font-size: 1.1rem;">
            Choose from our comprehensive range of professional services designed to help your business succeed.
        </p>
    </div>
</section>

<!-- Filter Section -->
<section class="py-lg">
    <div class="container">
        <div class="flex-between gap-lg flex-wrap">
            <h2 style="margin: 0;">Services</h2>
            <div class="flex gap-md flex-wrap">
                <input type="text" placeholder="Search services..." class="form-control" style="min-width: 200px;">
                <select class="form-control" style="min-width: 150px;">
                    <option value="">All Categories</option>
                    <option value="web">Web Development</option>
                    <option value="mobile">Mobile Apps</option>
                    <option value="cloud">Cloud Solutions</option>
                    <option value="consulting">Consulting</option>
                </select>
            </div>
        </div>
    </div>
</section>

<!-- Services Grid -->
<section class="py-lg">
    <div class="container">
        <div class="grid grid-cols-3 grid-gap-lg">
            @for ($i = 1; $i <= 12; $i++)
                <div class="card">
                    <div style="aspect-ratio: 16/9; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: var(--radius-lg); margin-bottom: var(--spacing-lg); display: flex; align-items: center; justify-content: center; color: white; font-size: 3rem;">
                        @switch($i % 4)
                            @case(1)
                                🚀
                                @break
                            @case(2)
                                💻
                                @break
                            @case(3)
                                ☁️
                                @break
                            @default
                                🔧
                        @endswitch
                    </div>

                    <div class="card-header">
                        <h3 style="margin: 0;">Service {{ $i }}</h3>
                        <span class="badge badge-primary">Featured</span>
                    </div>

                    <div class="card-body">
                        <p>
                            Professional service offering cutting-edge solutions for modern businesses. We deliver quality results on time.
                        </p>

                        <div style="background: var(--gray-lightest); padding: var(--spacing-md); border-radius: var(--radius-md); margin-bottom: var(--spacing-md);">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--spacing-sm);">
                                <span style="font-weight: 600;">Starting at</span>
                                <span style="font-size: 1.5rem; font-weight: 700; color: var(--primary);">$999</span>
                            </div>
                            <ul style="margin: 0; padding-left: var(--spacing-lg); font-size: 0.95rem;">
                                <li>✓ Expert Support</li>
                                <li>✓ Quality Assured</li>
                                <li>✓ On-time Delivery</li>
                            </ul>
                        </div>
                    </div>

                    <div class="card-footer">
                        <a href="#" class="btn btn-sm btn-outline">View Details</a>
                        <a href="{{ route('checkout.index') }}" class="btn btn-sm btn-primary">Request Quote</a>
                    </div>
                </div>
            @endfor
        </div>

        <!-- Pagination -->
        <div class="pagination">
            <button class="pagination-item disabled">← Previous</button>
            <button class="pagination-item active">1</button>
            <button class="pagination-item">2</button>
            <button class="pagination-item">3</button>
            <button class="pagination-item">Next →</button>
        </div>
    </div>
</section>
@endsection
