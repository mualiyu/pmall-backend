<?php

namespace App\Http\Controllers;

use App\Models\Wallet;
use App\Models\Withdrawal;
use Illuminate\Http\Request;

class WithdrawalController extends Controller
{
    public function withdrawal_list(Request $request)
    {
        if ($request->user()->tokenCan("Admin")) {

            // if request has a filter for status, user_id, or date range, apply those filters
            $query = Withdrawal::with('user');
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }
            if ($request->has('user_id')) {
                $query->where('user_id', $request->user_id);
            }
            if ($request->has('start_date') && $request->has('end_date')) {
                $query->whereBetween('created_at', [date($request->start_date), date($request->end_date)]);
            }
            if ($request->has('amount_min') && $request->has('amount_max')) {
                $query->whereBetween('amount', [$request->amount_min, $request->amount_max]);
            } else {
                $query->orderBy('created_at', 'desc');
            }

            $withdrawals = $query->get();
            return response()->json([
                'status' => true,
                'withdrawals' => $withdrawals
            ]);
        }
        return response()->json([
            'status' => false,
            'message' => 'Unauthorized'
        ], 403);
    }

    public function single_withdrawal(Request $request, $id)
    {
        if ($request->user()->tokenCan("Admin")) {
            $withdrawal = Withdrawal::with('user')->with('wallet')->find($id);
            if (!$withdrawal) {
                return response()->json([
                    'status' => false,
                    'message' => 'Withdrawal not found'
                ], 404);
            }
            return response()->json([
                'status' => true,
                'withdrawal' => $withdrawal
            ]);
        }
        return response()->json([
            'status' => false,
            'message' => 'Unauthorized'
        ], 403);
    }

    public function approve_withdrawal(Request $request, $id)
    {
        if ($request->user()->tokenCan("Admin")) {
            $withdrawal = Withdrawal::find($id);
            if (!$withdrawal) {
                return response()->json([
                    'status' => false,
                    'message' => 'Withdrawal not found'
                ], 404);
            }

            $withdrawal->status = 'approved';
            $withdrawal->save();

            return response()->json([
                'status' => true,
                'message' => 'Withdrawal approved successfully'
            ]);
        }
        return response()->json([
            'status' => false,
            'message' => 'Unauthorized'
        ], 403);
    }

    public function reject_withdrawal(Request $request, $id)
    {
        if ($request->user()->tokenCan("Admin")) {
            $withdrawal = Withdrawal::find($id);
            if (!$withdrawal) {
                return response()->json([
                    'status' => false,
                    'message' => 'Withdrawal not found'
                ], 404);
            }

            $withdrawal->status = 'rejected';
            $withdrawal->save();

            // Return user's balance
            $w = Wallet::find($withdrawal->wallet_id);
            if (!$w) {
                return response()->json([
                    'status' => false,
                    'message' => 'Wallet not found!'
                ], 404);
            }
            $w->amount += $withdrawal->amount;
            $w->save();

            return response()->json([
                'status' => true,
                'message' => 'Withdrawal rejected successfully'
            ]);
        }
        return response()->json([
            'status' => false,
            'message' => 'Unauthorized'
        ], 403);
    }

    public function complete_withdrawal(Request $request, $id)
    {
        if ($request->user()->tokenCan("Admin")) {
            $withdrawal = Withdrawal::find($id);
            if (!$withdrawal) {
                return response()->json([
                    'status' => false,
                    'message' => 'Withdrawal not found'
                ], 404);
            }

            // Check if the withdrawal is already completed or rejected
            if ($withdrawal->status === 'completed' || $withdrawal->status === 'rejected') {
                return response()->json([
                    'status' => false,
                    'message' => 'Withdrawal already completed or rejected'
                ], 422);
            }

            $withdrawal->status = 'completed';
            $withdrawal->save();

            return response()->json([
                'status' => true,
                'message' => 'Withdrawal completed successfully'
            ]);
        }
        return response()->json([
            'status' => false,
            'message' => 'Unauthorized'
        ], 403);
    }




    // Vendor and Affiliate
    public function requestWithdrawal(Request $request)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'wallet_id' => 'required|exists:wallets,id',
            'amount' => 'required|numeric|min:1',
            'method' => 'required|string',
            'remarks' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if the user has sufficient balance in the wallet
        $wallet = $request->user()->wallet()->find($request->wallet_id);
        if (!$wallet) {
            return response()->json([
                'status' => false,
                'message' => 'Wallet not found'
            ], 422);
        }
        if ($wallet->amount < $request->amount) {
            return response()->json([
                'status' => false,
                'message' => 'Insufficient balance in the wallet'
            ], 422);
        }

        $withdrawal = Withdrawal::create([
            'user_id' => $request->user()->id,
            'wallet_id' => $request->wallet_id,
            'amount' => $request->amount,
            'status' => 'pending',
            'method' => $request->method,
            'remarks' => $request->remarks,
        ]);

        $w = Wallet::find($request->wallet_id);
        $w->amount -= $request->amount;
        $w->save();

        if (!$withdrawal) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to submit withdrawal request'
            ], 422);
        }

        return response()->json([
            'status' => true,
            'message' => 'Withdrawal request submitted',
            'withdrawal' => $withdrawal
        ], 201);
    }

    public function history(Request $request)
    {
        $withdrawals = Withdrawal::where('user_id', $request->user()->id)->orderBy('created_at', 'desc')->get();

        if ($withdrawals->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'No withdrawal history found'
            ], 422);
        }
        return response()->json([
            'status' => true,
            'message' => 'Withdrawal history',
            'withdrawals' => $withdrawals
        ], 200);
    }

    public function single($id, Request $request)
    {
        $withdrawal = Withdrawal::where('user_id', $request->user()->id)->where('id', $id)->firstOrFail();

        if (!$withdrawal) {
            return response()->json([
                'status' => false,
                'message' => 'Withdrawal not found'
            ], 422);
        }
        return response()->json([
            'status' => true,
            'message' => 'Withdrawal details',
            'withdrawal' => $withdrawal
        ], 200);
    }
}
