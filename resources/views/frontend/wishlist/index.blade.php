@extends('frontend.layouts.app')

@section('title', 'My Wishlist')

@section('content')
<div class="container py-5">
    <div class="row">
        <div class="col-12">
            <h1 class="mb-4">My Wishlist</h1>

            @if($wishlists->count() > 0)
                <div class="row">
                    @foreach($wishlists as $wishlist)
                        @php
                            $item = $wishlist->wishlistable;
                            $itemName = $item->name ?? $item->title ?? 'Unknown Item';
                            $itemPrice = $item->price ?? 0;
                            $itemImage = $item->image ?? null;
                            $itemType = class_basename($wishlist->wishlistable_type);
                        @endphp

                        <div class="col-md-6 col-lg-4 col-xl-3 mb-4">
                            <div class="card h-100 shadow-sm">
                                @if($itemImage)
                                    <img src="{{ asset('storage/' . $itemImage) }}"
                                         class="card-img-top"
                                         alt="{{ $itemName }}"
                                         style="height: 200px; object-fit: cover;">
                                @else
                                    <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                                        <i class="fas fa-image fa-3x text-muted"></i>
                                    </div>
                                @endif

                                <div class="card-body d-flex flex-column">
                                    <div class="mb-2">
                                        <span class="badge bg-info">{{ $itemType }}</span>
                                    </div>
                                    <h5 class="card-title">{{ $itemName }}</h5>
                                    @if($item->description ?? false)
                                        <p class="card-text text-muted small">
                                            {{ Str::limit($item->description, 100) }}
                                        </p>
                                    @endif
                                    <div class="mt-auto">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <strong class="text-primary">${{ number_format($itemPrice, 2) }}</strong>
                                        </div>
                                        <div class="btn-group w-100" role="group">
                                            <form action="{{ route('cart.add') }}" method="POST" class="flex-fill">
                                                @csrf
                                                <input type="hidden" name="type" value="{{ strtolower($itemType) }}">
                                                <input type="hidden" name="id" value="{{ $item->id }}">
                                                <button type="submit" class="btn btn-primary w-100">
                                                    <i class="fas fa-cart-plus me-1"></i> Add to Cart
                                                </button>
                                            </form>
                                        </div>
                                        <form action="{{ route('wishlist.remove', $wishlist->id) }}" method="POST" class="mt-2">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger btn-sm w-100">
                                                <i class="fas fa-trash me-1"></i> Remove
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                <div class="mt-4">
                    {{ $wishlists->links() }}
                </div>
            @else
                <!-- Empty Wishlist -->
                <div class="card shadow-sm">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-heart fa-5x text-muted mb-4"></i>
                        <h3>Your wishlist is empty</h3>
                        <p class="text-muted mb-4">Save items you love for later!</p>
                        <a href="{{ route('products.index') }}" class="btn btn-primary">
                            <i class="fas fa-shopping-bag me-2"></i> Browse Products
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
