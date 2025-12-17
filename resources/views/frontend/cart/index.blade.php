@extends('frontend.layouts.app')

@section('title', 'Shopping Cart')

@section('content')
<div class="container py-5">
    <div class="row">
        <div class="col-12">
            <h1 class="mb-4">Shopping Cart</h1>

            @if($cart && $cart->items->count() > 0)
                <div class="row">
                    <!-- Cart Items -->
                    <div class="col-lg-8">
                        <div class="card shadow-sm mb-4">
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Product</th>
                                                <th>Price</th>
                                                <th>Quantity</th>
                                                <th>Subtotal</th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($cart->items as $item)
                                                <tr>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            @if($item->cartable->image ?? false)
                                                                <img src="{{ asset('storage/' . $item->cartable->image) }}"
                                                                     alt="{{ $item->cartable->name }}"
                                                                     class="img-thumbnail me-3"
                                                                     style="width: 80px; height: 80px; object-fit: cover;">
                                                            @endif
                                                            <div>
                                                                <h6 class="mb-0">{{ $item->cartable->name ?? $item->cartable->title }}</h6>
                                                                <small class="text-muted">
                                                                    {{ ucfirst(class_basename($item->cartable_type)) }}
                                                                </small>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>${{ number_format($item->price, 2) }}</td>
                                                    <td>
                                                        <form action="{{ route('cart.update', $item->id) }}" method="POST" class="d-inline update-quantity-form">
                                                            @csrf
                                                            @method('PATCH')
                                                            <div class="input-group" style="width: 120px;">
                                                                <input type="number"
                                                                       name="quantity"
                                                                       value="{{ $item->quantity }}"
                                                                       min="1"
                                                                       max="100"
                                                                       class="form-control form-control-sm quantity-input">
                                                            </div>
                                                        </form>
                                                    </td>
                                                    <td>
                                                        <strong>${{ number_format($item->subtotal, 2) }}</strong>
                                                    </td>
                                                    <td>
                                                        <form action="{{ route('cart.remove', $item->id) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Remove this item from cart?')">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                <div class="d-flex justify-content-between align-items-center mt-3">
                                    <a href="{{ route('home') }}" class="btn btn-outline-primary">
                                        <i class="fas fa-arrow-left me-2"></i> Continue Shopping
                                    </a>
                                    <form action="{{ route('cart.clear') }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger" onclick="return confirm('Clear entire cart?')">
                                            <i class="fas fa-trash me-2"></i> Clear Cart
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Cart Summary -->
                    <div class="col-lg-4">
                        <div class="card shadow-sm">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0">Order Summary</h5>
                            </div>
                            <div class="card-body">
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Subtotal:</span>
                                    <span>${{ number_format($cart->subtotal, 2) }}</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Tax (10%):</span>
                                    <span>${{ number_format($cart->tax, 2) }}</span>
                                </div>
                                <hr>
                                <div class="d-flex justify-content-between mb-3">
                                    <strong>Total:</strong>
                                    <strong class="text-primary">${{ number_format($cart->total, 2) }}</strong>
                                </div>

                                @auth
                                    <a href="{{ route('checkout.index') }}" class="btn btn-primary w-100 mb-2">
                                        <i class="fas fa-lock me-2"></i> Proceed to Checkout
                                    </a>
                                @else
                                    <a href="{{ route('login') }}" class="btn btn-primary w-100 mb-2">
                                        <i class="fas fa-sign-in-alt me-2"></i> Login to Checkout
                                    </a>
                                @endauth

                                <small class="text-muted d-block text-center">
                                    <i class="fas fa-shield-alt"></i> Secure Checkout
                                </small>
                            </div>
                        </div>

                        <!-- Voucher Section -->
                        <div class="card shadow-sm mt-3">
                            <div class="card-body">
                                <h6 class="mb-3">Have a voucher code?</h6>
                                <form action="{{ route('voucher.apply') }}" method="POST">
                                    @csrf
                                    <div class="input-group">
                                        <input type="text" name="code" class="form-control" placeholder="Enter code" required>
                                        <button type="submit" class="btn btn-outline-primary">Apply</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <!-- Empty Cart -->
                <div class="card shadow-sm">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-shopping-cart fa-5x text-muted mb-4"></i>
                        <h3>Your cart is empty</h3>
                        <p class="text-muted mb-4">Looks like you haven't added anything to your cart yet.</p>
                        <a href="{{ route('products.index') }}" class="btn btn-primary">
                            <i class="fas fa-shopping-bag me-2"></i> Start Shopping
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Auto-submit quantity update form after change
    document.querySelectorAll('.quantity-input').forEach(input => {
        let timeout;
        input.addEventListener('change', function() {
            clearTimeout(timeout);
            timeout = setTimeout(() => {
                this.closest('form').submit();
            }, 500);
        });
    });
</script>
@endpush
@endsection
