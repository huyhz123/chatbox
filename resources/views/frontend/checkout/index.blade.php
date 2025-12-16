@extends('frontend.layouts.app')

@section('title', 'Checkout - MyApp')
@section('description', 'Complete your purchase securely')

@section('content')
<!-- Page Header -->
<section class="py-2xl" style="background: linear-gradient(135deg, rgba(99, 102, 241, 0.1) 0%, rgba(139, 92, 246, 0.1) 100%);">
    <div class="container">
        <h1 style="text-align: center; margin: 0;">Secure Checkout</h1>
    </div>
</section>

<!-- Checkout Form -->
<section class="py-2xl">
    <div class="container-sm">
        <div class="grid grid-cols-2 grid-gap-xl">
            <!-- Left Column - Billing Form -->
            <div>
                <h2 style="margin-bottom: var(--spacing-xl);">Billing Information</h2>

                <form>
                    <!-- Personal Info -->
                    <div class="form-row">
                        <div class="form-group">
                            <label>First Name *</label>
                            <input type="text" placeholder="John" required>
                        </div>
                        <div class="form-group">
                            <label>Last Name *</label>
                            <input type="text" placeholder="Doe" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Email Address *</label>
                        <input type="email" placeholder="john@example.com" required>
                    </div>

                    <div class="form-group">
                        <label>Phone Number *</label>
                        <input type="tel" placeholder="+1 (555) 000-0000" required>
                    </div>

                    <!-- Address -->
                    <div class="form-group">
                        <label>Street Address *</label>
                        <input type="text" placeholder="123 Main Street" required>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>City *</label>
                            <input type="text" placeholder="New York" required>
                        </div>
                        <div class="form-group">
                            <label>State/Province *</label>
                            <input type="text" placeholder="NY" required>
                        </div>
                        <div class="form-group">
                            <label>ZIP Code *</label>
                            <input type="text" placeholder="10001" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Country *</label>
                        <select required>
                            <option value="">Select Country</option>
                            <option value="us">United States</option>
                            <option value="ca">Canada</option>
                            <option value="uk">United Kingdom</option>
                            <option value="au">Australia</option>
                        </select>
                    </div>

                    <!-- Payment Method -->
                    <h3 style="margin: var(--spacing-xl) 0 var(--spacing-md) 0;">Payment Method</h3>

                    <div style="display: flex; gap: var(--spacing-md); margin-bottom: var(--spacing-lg);">
                        <label style="display: flex; align-items: center; gap: var(--spacing-sm); cursor: pointer;">
                            <input type="radio" name="payment" value="card" checked>
                            <span>💳 Credit/Debit Card</span>
                        </label>
                        <label style="display: flex; align-items: center; gap: var(--spacing-sm); cursor: pointer;">
                            <input type="radio" name="payment" value="paypal">
                            <span>🅿️ PayPal</span>
                        </label>
                    </div>

                    <!-- Card Details -->
                    <div class="form-group">
                        <label>Card Number *</label>
                        <input type="text" placeholder="1234 5678 9012 3456" required>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Expiry Date *</label>
                            <input type="text" placeholder="MM/YY" required>
                        </div>
                        <div class="form-group">
                            <label>CVV *</label>
                            <input type="text" placeholder="123" required>
                        </div>
                    </div>

                    <label style="display: flex; align-items: center; gap: var(--spacing-sm); margin: var(--spacing-lg) 0; cursor: pointer;">
                        <input type="checkbox" checked>
                        <span>Save this card for future purchases</span>
                    </label>
                </form>
            </div>

            <!-- Right Column - Order Summary -->
            <div>
                <div class="card" style="position: sticky; top: 100px;">
                    <div class="card-header">
                        <h3 style="margin: 0;">Order Summary</h3>
                    </div>

                    <div class="card-body">
                        <!-- Items -->
                        <div style="margin-bottom: var(--spacing-lg);">
                            @for ($i = 1; $i <= 3; $i++)
                                <div style="display: flex; justify-content: space-between; margin-bottom: var(--spacing-md); padding-bottom: var(--spacing-md); border-bottom: 1px solid var(--gray-lighter);">
                                    <div>
                                        <div style="font-weight: 600;">Product {{ $i }}</div>
                                        <div style="color: var(--gray); font-size: 0.9rem;">Qty: 1</div>
                                    </div>
                                    <div style="font-weight: 600;">$99.99</div>
                                </div>
                            @endfor
                        </div>

                        <!-- Promo Code -->
                        <div style="display: flex; gap: var(--spacing-sm); margin-bottom: var(--spacing-lg);">
                            <input type="text" placeholder="Promo code" class="form-control" style="flex: 1; margin-bottom: 0;">
                            <button type="button" class="btn btn-sm btn-outline">Apply</button>
                        </div>

                        <!-- Pricing Summary -->
                        <div style="background: var(--gray-lightest); padding: var(--spacing-md); border-radius: var(--radius-md); margin-bottom: var(--spacing-lg);">
                            <div style="display: flex; justify-content: space-between; margin-bottom: var(--spacing-sm);">
                                <span>Subtotal</span>
                                <span>$299.97</span>
                            </div>
                            <div style="display: flex; justify-content: space-between; margin-bottom: var(--spacing-sm);">
                                <span>Shipping</span>
                                <span>$10.00</span>
                            </div>
                            <div style="display: flex; justify-content: space-between; margin-bottom: var(--spacing-sm);">
                                <span>Tax</span>
                                <span>$24.80</span>
                            </div>
                            <div style="display: flex; justify-content: space-between; font-weight: 700; border-top: 1px solid var(--gray-lighter); padding-top: var(--spacing-md);">
                                <span>Total</span>
                                <span style="font-size: 1.3rem; color: var(--primary);">$334.77</span>
                            </div>
                        </div>

                        <!-- Trust Badges -->
                        <div style="background: rgba(16, 185, 129, 0.1); padding: var(--spacing-md); border-radius: var(--radius-md); margin-bottom: var(--spacing-lg); text-align: center;">
                            <div style="font-size: 1.5rem; margin-bottom: var(--spacing-sm);">🔒 ✓ 💳</div>
                            <div style="font-size: 0.85rem; color: var(--gray);">SSL Secure • PCI Compliant</div>
                        </div>

                        <!-- CTA -->
                        <button type="submit" class="btn btn-primary btn-block btn-lg">
                            💳 Complete Purchase
                        </button>

                        <p style="text-align: center; color: var(--gray); font-size: 0.85rem; margin-top: var(--spacing-md);">
                            or <a href="{{ route('products.index') }}" style="color: var(--primary);">continue shopping</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
