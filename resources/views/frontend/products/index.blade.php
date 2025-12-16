@extends('frontend.layouts.app')

@section('title', 'Products - MyApp')
@section('description', 'Discover our premium products and solutions')

@section('content')
<!-- Page Header -->
<section class="py-2xl" style="background: linear-gradient(135deg, rgba(99, 102, 241, 0.1) 0%, rgba(139, 92, 246, 0.1) 100%);">
    <div class="container">
        <h1 style="text-align: center; margin: 0 0 var(--spacing-lg) 0;">Our Products</h1>
        <p style="text-align: center; max-width: 600px; margin: 0 auto; font-size: 1.1rem;">
            Explore our curated collection of premium products designed for excellence.
        </p>
    </div>
</section>

<!-- Filter and Sort -->
<section class="py-lg">
    <div class="container">
        <div class="flex-between gap-lg flex-wrap">
            <h2 style="margin: 0;">Products</h2>
            <div class="flex gap-md flex-wrap">
                <input type="text" placeholder="Search products..." class="form-control" style="min-width: 200px;">
                <select class="form-control" style="min-width: 150px;">
                    <option value="">Sort By</option>
                    <option value="newest">Newest</option>
                    <option value="popular">Most Popular</option>
                    <option value="price-low">Price: Low to High</option>
                    <option value="price-high">Price: High to Low</option>
                </select>
            </div>
        </div>
    </div>
</section>

<!-- Products Grid -->
<section class="py-lg">
    <div class="container">
        <div class="grid grid-cols-4 grid-gap-lg">
            @for ($i = 1; $i <= 12; $i++)
                <div class="card">
                    <div style="position: relative; margin-bottom: var(--spacing-md);">
                        <div style="aspect-ratio: 1; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: var(--radius-lg); display: flex; align-items: center; justify-content: center; color: white; font-size: 3rem;">
                            📦
                        </div>
                        @if($i % 3 == 0)
                            <span class="badge badge-danger" style="position: absolute; top: 10px; right: 10px;">-20%</span>
                        @endif
                    </div>

                    <h4 style="margin: 0 0 var(--spacing-xs) 0;">Product {{ $i }}</h4>

                    <!-- Rating -->
                    <div style="display: flex; gap: 2px; margin-bottom: var(--spacing-sm);">
                        @for ($j = 0; $j < 5; $j++)
                            <span style="color: #fbbf24; font-size: 0.9rem;">⭐</span>
                        @endfor
                        <span style="color: var(--gray); font-size: 0.85rem;">(248)</span>
                    </div>

                    <p style="font-size: 0.95rem; margin-bottom: var(--spacing-md);">
                        Premium quality product with excellent features and durability.
                    </p>

                    <!-- Price Section -->
                    <div style="background: var(--gray-lightest); padding: var(--spacing-md); border-radius: var(--radius-md); margin-bottom: var(--spacing-md);">
                        <div style="display: flex; gap: var(--spacing-md); align-items: center;">
                            @if($i % 3 == 0)
                                <span style="font-size: 1.3rem; font-weight: 700; color: var(--primary);">$79.99</span>
                                <span style="font-size: 1rem; font-weight: 600; text-decoration: line-through; color: var(--gray);">$99.99</span>
                            @else
                                <span style="font-size: 1.3rem; font-weight: 700; color: var(--primary);">$99.99</span>
                            @endif
                        </div>
                    </div>

                    <!-- Stock Status -->
                    <div style="padding: var(--spacing-sm); background: rgba(16, 185, 129, 0.1); border-radius: var(--radius-md); margin-bottom: var(--spacing-md); text-align: center; color: var(--success); font-weight: 600;">
                        ✓ In Stock
                    </div>

                    <!-- Actions -->
                    <div class="flex gap-md">
                        <button class="btn btn-sm btn-outline" style="flex: 1;">
                            ♥ Wishlist
                        </button>
                        <a href="{{ route('checkout.index') }}" class="btn btn-sm btn-primary" style="flex: 1;">
                            🛒 Add Cart
                        </a>
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
