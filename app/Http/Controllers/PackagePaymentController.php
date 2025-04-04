<?php

namespace App\Http\Controllers;

use App\Models\AccountPackage;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Http\Request;
use Yabacon\Paystack;

class PackagePaymentController extends Controller
{
    public function verifyPayment($reference)
    {
        try {
            $paystack = new Paystack(env('PAYSTACK_SECRET_KEY'));
            $tranx = $paystack->transaction->verify(['reference' => $reference]);

            if ($tranx->data->status === 'success') {
                // Find the sale associated with the payment reference (this assumes you passed the sale ID in the initial request)
                $package = AccountPackage::findOrFail($tranx->data->metadata->package_id);

                // Store payment details
                $payment = Payment::create([
                    'user_id' => $tranx->data->metadata->user_id,
                    'package_id' => $package->id,
                    'amount' => $tranx->data->amount / 100, // Amount in Naira
                    'method' => 'paystack',
                    'ref_id' => $reference,
                    'isapproved' => true,
                    'note' => 'Payment processed via Paystack'
                ]);

                // Optionally, update the sale status
                // $sale->update([
                //     'status' => 'completed',
                //     'payment_status' => 'paid'
                // ]);

                $user = User::find($tranx->data->metadata->user_id);
                $user->isActive = true;
                $user->save();


                return response()->json([
                    'status' => true,
                    'message' => 'Payment successful',
                    'data' => $payment,
                ], 200);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Payment failed',
                ], 422);
            }
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function initPayment(Request $request)
    {
        try {
            $request->validate([
                'package_id' => 'required|exists:account_packages,id',
                'email' => 'required|email',
            ]);

            $package = AccountPackage::findOrFail($request->package_id);
            $user = $request->user();

            $paystack = new Paystack(env('PAYSTACK_SECRET_KEY'));

            // Initialize transaction
            $response = $paystack->transaction->initialize([
                'amount' => $package->price * 100, // Convert to kobo (Paystack uses the smallest currency unit)
                'email' => $request->email,
                'callback_url' =>  env('FRONT_URL').'/package/payment/verification',
                'metadata' => [
                    'package_id' => $package->id,
                    'user_id' => $user->id,
                ]
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Payment initialized',
                'data' => [
                    'authorization_url' => $response->data->authorization_url,
                    'reference' => $response->data->reference,
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }
}
