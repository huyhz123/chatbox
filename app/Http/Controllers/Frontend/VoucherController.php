<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Voucher;
use Illuminate\Http\Request;

class VoucherController extends Controller
{
    /**
     * Validate voucher code
     */
    public function validate(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
            'order_amount' => 'required|numeric|min:0',
        ]);

        $voucher = Voucher::where('code', strtoupper($request->code))->first();

        if (!$voucher) {
            return response()->json([
                'valid' => false,
                'message' => 'Invalid voucher code',
            ], 404);
        }

        // Check if voucher is valid
        $validCheck = $voucher->isValid();
        if (!$validCheck['valid']) {
            return response()->json($validCheck, 400);
        }

        // Check if user can use
        if ($request->user()) {
            $userCheck = $voucher->canUserUse($request->user()->id);
            if (!$userCheck['valid']) {
                return response()->json($userCheck, 400);
            }
        }

        // Check minimum order amount
        if ($request->order_amount < $voucher->min_order_amount) {
            return response()->json([
                'valid' => false,
                'message' => 'Minimum order amount is ' . number_format($voucher->min_order_amount, 2),
            ], 400);
        }

        // Calculate discount
        $discount = $voucher->calculateDiscount($request->order_amount);

        return response()->json([
            'valid' => true,
            'voucher' => $voucher,
            'discount_amount' => $discount,
            'final_amount' => max(0, $request->order_amount - $discount),
        ]);
    }

    /**
     * Apply voucher to cart/order
     */
    public function apply(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        $voucher = Voucher::where('code', strtoupper($request->code))->first();

        if (!$voucher) {
            return back()->withErrors(['voucher' => 'Invalid voucher code']);
        }

        // Validate voucher
        $validCheck = $voucher->isValid();
        if (!$validCheck['valid']) {
            return back()->withErrors(['voucher' => $validCheck['message']]);
        }

        // Store in session
        session(['applied_voucher' => $voucher->id]);

        return back()->with('success', 'Voucher applied successfully!');
    }

    /**
     * Remove voucher from cart/order
     */
    public function remove()
    {
        session()->forget('applied_voucher');

        return back()->with('success', 'Voucher removed');
    }
}
