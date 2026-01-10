<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\VipPackage;
use App\Models\Transaction;
use App\Models\ChatUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    /**
     * Get available coin packages
     */
    public function coinPackages(Request $request): JsonResponse
    {
        try {
            $packages = [
                ['id' => 1, 'coins' => 100, 'price' => 20000, 'bonus' => 0],
                ['id' => 2, 'coins' => 500, 'price' => 95000, 'bonus' => 50],
                ['id' => 3, 'coins' => 1000, 'price' => 180000, 'bonus' => 150],
                ['id' => 4, 'coins' => 5000, 'price' => 850000, 'bonus' => 1000],
                ['id' => 5, 'coins' => 10000, 'price' => 1600000, 'bonus' => 2500],
            ];

            return response()->json([
                'success' => true,
                'packages' => $packages,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch packages',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Purchase coins
     */
    public function purchaseCoins(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            $validator = Validator::make($request->all(), [
                'package_id' => 'required|integer|min:1|max:5',
                'payment_method' => 'required|in:vnpay,momo,zalopay',
                'return_url' => 'required|url',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors(),
                ], 422);
            }

            // Define packages
            $packages = [
                1 => ['coins' => 100, 'price' => 20000, 'bonus' => 0],
                2 => ['coins' => 500, 'price' => 95000, 'bonus' => 50],
                3 => ['coins' => 1000, 'price' => 180000, 'bonus' => 150],
                4 => ['coins' => 5000, 'price' => 850000, 'bonus' => 1000],
                5 => ['coins' => 10000, 'price' => 1600000, 'bonus' => 2500],
            ];

            $package = $packages[$request->package_id];

            // Create transaction
            $transaction = Transaction::create([
                'user_id' => $user->id,
                'type' => 'coin_purchase',
                'amount' => $package['coins'] + $package['bonus'],
                'balance_before' => $user->balance,
                'balance_after' => $user->balance, // Will update after payment
                'description' => "Purchase {$package['coins']} coins + {$package['bonus']} bonus",
                'status' => 'pending',
                'payment_method' => $request->payment_method,
                'payment_amount' => $package['price'],
            ]);

            // Generate payment URL based on method
            $paymentUrl = match($request->payment_method) {
                'vnpay' => $this->generateVNPayUrl($transaction, $package, $request->return_url),
                'momo' => $this->generateMoMoUrl($transaction, $package, $request->return_url),
                'zalopay' => $this->generateZaloPayUrl($transaction, $package, $request->return_url),
            };

            return response()->json([
                'success' => true,
                'transaction_id' => $transaction->id,
                'payment_url' => $paymentUrl,
                'message' => 'Redirect to payment gateway',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create payment',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Purchase VIP package
     */
    public function purchaseVip(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            $validator = Validator::make($request->all(), [
                'package_id' => 'required|exists:vip_packages,id',
                'payment_method' => 'required|in:vnpay,momo,zalopay',
                'return_url' => 'required|url',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors(),
                ], 422);
            }

            $package = VipPackage::find($request->package_id);

            // Create transaction
            $transaction = Transaction::create([
                'user_id' => $user->id,
                'type' => 'vip_purchase',
                'amount' => 0, // VIP doesn't add coins
                'balance_before' => $user->balance,
                'balance_after' => $user->balance,
                'description' => "Purchase VIP {$package->name} - {$package->duration_days} days",
                'status' => 'pending',
                'payment_method' => $request->payment_method,
                'payment_amount' => $package->price,
                'metadata' => json_encode(['package_id' => $package->id]),
            ]);

            // Generate payment URL
            $paymentUrl = match($request->payment_method) {
                'vnpay' => $this->generateVNPayUrl($transaction, ['price' => $package->price], $request->return_url),
                'momo' => $this->generateMoMoUrl($transaction, ['price' => $package->price], $request->return_url),
                'zalopay' => $this->generateZaloPayUrl($transaction, ['price' => $package->price], $request->return_url),
            };

            return response()->json([
                'success' => true,
                'transaction_id' => $transaction->id,
                'payment_url' => $paymentUrl,
                'message' => 'Redirect to payment gateway',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create payment',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * VNPAY callback
     */
    public function vnpayCallback(Request $request): JsonResponse
    {
        try {
            // TODO: Verify VNPAY signature
            $transactionId = $request->vnp_TxnRef;
            $responseCode = $request->vnp_ResponseCode;

            $transaction = Transaction::find($transactionId);
            if (!$transaction) {
                return response()->json(['success' => false, 'message' => 'Transaction not found'], 404);
            }

            if ($responseCode === '00') {
                // Payment successful
                DB::transaction(function () use ($transaction) {
                    $this->processSuccessfulPayment($transaction);
                });

                return response()->json([
                    'success' => true,
                    'message' => 'Payment successful',
                ]);
            } else {
                // Payment failed
                $transaction->update(['status' => 'failed']);

                return response()->json([
                    'success' => false,
                    'message' => 'Payment failed',
                ], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Callback processing failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * MoMo callback
     */
    public function momoCallback(Request $request): JsonResponse
    {
        try {
            // TODO: Verify MoMo signature
            $transactionId = $request->orderId;
            $resultCode = $request->resultCode;

            $transaction = Transaction::find($transactionId);
            if (!$transaction) {
                return response()->json(['success' => false, 'message' => 'Transaction not found'], 404);
            }

            if ($resultCode === 0) {
                // Payment successful
                DB::transaction(function () use ($transaction) {
                    $this->processSuccessfulPayment($transaction);
                });

                return response()->json([
                    'success' => true,
                    'message' => 'Payment successful',
                ]);
            } else {
                // Payment failed
                $transaction->update(['status' => 'failed']);

                return response()->json([
                    'success' => false,
                    'message' => 'Payment failed',
                ], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Callback processing failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * ZaloPay callback
     */
    public function zaloPayCallback(Request $request): JsonResponse
    {
        try {
            // TODO: Verify ZaloPay signature
            $data = json_decode($request->data, true);
            $transactionId = $data['app_trans_id'];

            $transaction = Transaction::find($transactionId);
            if (!$transaction) {
                return response()->json(['success' => false, 'message' => 'Transaction not found'], 404);
            }

            if ($request->return_code === 1) {
                // Payment successful
                DB::transaction(function () use ($transaction) {
                    $this->processSuccessfulPayment($transaction);
                });

                return response()->json([
                    'success' => true,
                    'message' => 'Payment successful',
                ]);
            } else {
                // Payment failed
                $transaction->update(['status' => 'failed']);

                return response()->json([
                    'success' => false,
                    'message' => 'Payment failed',
                ], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Callback processing failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get transaction history
     */
    public function transactionHistory(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            $transactions = Transaction::where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->paginate(20);

            return response()->json([
                'success' => true,
                'transactions' => $transactions->items(),
                'pagination' => [
                    'current_page' => $transactions->currentPage(),
                    'total' => $transactions->total(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch history',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Process successful payment
     */
    private function processSuccessfulPayment(Transaction $transaction): void
    {
        $user = ChatUser::find($transaction->user_id);

        if ($transaction->type === 'coin_purchase') {
            // Add coins
            $user->addCoins($transaction->amount, $transaction->description);

            $transaction->update([
                'status' => 'completed',
                'balance_after' => $user->fresh()->balance,
            ]);
        } elseif ($transaction->type === 'vip_purchase') {
            // Activate VIP
            $metadata = json_decode($transaction->metadata, true);
            $package = VipPackage::find($metadata['package_id']);

            $user->update([
                'vip_level' => $package->level,
                'vip_expires_at' => now()->addDays($package->duration_days),
            ]);

            $transaction->update(['status' => 'completed']);
        }
    }

    /**
     * Generate VNPAY payment URL
     */
    private function generateVNPayUrl(Transaction $transaction, array $package, string $returnUrl): string
    {
        // TODO: Implement actual VNPAY URL generation
        $baseUrl = 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html';

        $params = [
            'vnp_Version' => '2.1.0',
            'vnp_Command' => 'pay',
            'vnp_TmnCode' => env('VNPAY_TMN_CODE'),
            'vnp_Amount' => $package['price'] * 100,
            'vnp_CreateDate' => now()->format('YmdHis'),
            'vnp_CurrCode' => 'VND',
            'vnp_IpAddr' => request()->ip(),
            'vnp_Locale' => 'vn',
            'vnp_OrderInfo' => $transaction->description,
            'vnp_OrderType' => 'other',
            'vnp_ReturnUrl' => $returnUrl,
            'vnp_TxnRef' => $transaction->id,
        ];

        // TODO: Add signature
        return $baseUrl . '?' . http_build_query($params);
    }

    /**
     * Generate MoMo payment URL
     */
    private function generateMoMoUrl(Transaction $transaction, array $package, string $returnUrl): string
    {
        // TODO: Implement actual MoMo URL generation
        return 'https://test-payment.momo.vn/pay?orderId=' . $transaction->id;
    }

    /**
     * Generate ZaloPay payment URL
     */
    private function generateZaloPayUrl(Transaction $transaction, array $package, string $returnUrl): string
    {
        // TODO: Implement actual ZaloPay URL generation
        return 'https://sb-openapi.zalopay.vn/v2/create?app_trans_id=' . $transaction->id;
    }

    /**
     * Get VIP packages
     */
    public function vipPackages(Request $request): JsonResponse
    {
        try {
            $packages = VipPackage::orderBy('level', 'asc')->get();

            return response()->json([
                'success' => true,
                'packages' => $packages,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch VIP packages',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get user's VIP status
     */
    public function myVipStatus(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            $status = [
                'is_vip' => $user->isVip(),
                'vip_level' => $user->vip_level,
                'vip_expires_at' => $user->vip_expires_at,
                'days_remaining' => $user->vip_expires_at ? now()->diffInDays($user->vip_expires_at) : 0,
            ];

            // Get current VIP package benefits
            if ($user->vip_level > 0) {
                $package = VipPackage::where('level', $user->vip_level)->first();
                $status['benefits'] = $package ? json_decode($package->benefits, true) : [];
            }

            return response()->json([
                'success' => true,
                'status' => $status,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch VIP status',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
