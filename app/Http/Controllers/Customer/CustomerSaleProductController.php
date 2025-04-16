<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Mail\CustomerSaleSuccess;
use App\Mail\SaleSuccess;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SalePayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Yabacon\Paystack;

class CustomerSaleProductController extends Controller
{
    public function checkout(Request $request)
    {
        // return $request->customer_id;

        if ($request->user()->tokenCan("Customer")) {

            $validator = Validator::make($request->all(), [
                'customer_id' => 'required|exists:customers,id',
                'products' => 'required|array',
                'products.*.product_id' => 'required|exists:products,id',
                'products.*.quantity' => 'required|integer|min:1',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    "status" => false,
                    "message" => $validator->errors(),
                ], 422);
            }

            try {
                DB::beginTransaction();

                // Get customer from the request
                $customer = Customer::find($request->customer_id);

                // Initialize sale total amount
                $totalAmount = 0;

                // Register sale
                $sale = Sale::create([
                    'customer_id' => $customer->id,
                    'total_amount' => $totalAmount,  // to be updated later
                    'status' => 'pending',  // initial sale status
                ]);

                // Iterate over each product and add them to the sale
                foreach ($request->products as $item) {
                    $product = Product::find($item['product_id']);
                    $quantity = $item['quantity'];

                    // Calculate the total for this product
                    $totalProductAmount = $product->selling_price * $quantity;

                    // Store each sale item (you may have a SaleItem model/table)
                    DB::table('product_sale')->insert([
                        'sale_id' => $sale->id,
                        'product_id' => $product->id,
                        'quantity' => $quantity,
                        'price' => $product->selling_price,
                        'total' => $totalProductAmount,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    // Add this product's total to the sale's total amount
                    $totalAmount += $totalProductAmount;

                    // Reduce product quantity in stock
                    $product->decrement('quantity', $quantity);
                }

                // Update sale total amount
                $sale->update([
                    'total_amount' => $totalAmount,
                    // 'status' => 'completed',  // sale completed after checkout
                ]);

                DB::commit();

                $sale = Sale::where('id', '=', $sale->id)->with('products')->get()[0];

                return response()->json([
                    'status' => true,
                    'message' => 'Sale registered successfully',
                    'sale' => $sale,
                ], 201);
            } catch (\Exception $e) {

                DB::rollBack();

                return response()->json([
                    'status' => false,
                    'message' => 'Failed to register sale: ' . $e->getMessage(),
                ], 422);
            }
        } else {

            return response()->json([
                'status' => false,
                'message' => trans('auth.failed')
            ], 422);
        }
    }


    // Payments Logic
    public function initiatePayment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric',
            'sale_id' => 'required|exists:sales,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()
            ], 422);
        }

        $sale = Sale::where('id', '=', $request->sale_id)->with('customer')->get();

        if ($sale->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'Sale not found'
            ], 422);
        }

        if ($sale[0]->customer_id != $request->user()->id) {
            return response()->json([
                'status' => false,
                'message' => 'You are not authorized to make this payment'
            ], 422);
        }

        if ($sale[0]->status != 'pending') {
            return response()->json([
                'status' => false,
                'message' => 'This cart has already been paid for'
            ], 422);
        }

        $sale = $sale[0];

        try {
            $paystack = new Paystack(env('PAYSTACK_SECRET_KEY'));
            $tranx = $paystack->transaction->initialize([
                'amount' => ($sale->total_amount == $request->amount) ? $request->amount * 100 : $sale->total_amount * 100, // Amount in kobo (or cents)
                'email' => $sale->customer->email ? $sale->customer->email : $sale->customer->phone,
                'callback_url' => env('FRONT_URL') . '/checkout/transaction/verify',
                // 'callback_url' => url('/api/v1/customer/paystack/verify-callback'),
                'metadata' => [
                    'sale_id' => $request->sale_id, // Custom metadata
                ],
            ]);

            return response()->json([
                'status' => true,
                'authorization_url' => $tranx->data->authorization_url,
                'reference' => $tranx->data->reference,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function verifyPayment($reference)
    {
        try {
            $paystack = new Paystack(env('PAYSTACK_SECRET_KEY'));
            $tranx = $paystack->transaction->verify(['reference' => $reference]);

            if ($tranx->data->status === 'success') {
                // Find the sale associated with the payment reference (this assumes you passed the sale ID in the initial request)
                $sale = Sale::findOrFail($tranx->data->metadata->sale_id);

                // Store payment details
                $payment = SalePayment::create([
                    'sale_id' => $sale->id,
                    'reference' => $reference,
                    'amount' => $tranx->data->amount / 100, // Amount in Naira
                    'status' => $tranx->data->status,
                    'gateway' => 'paystack',
                ]);

                // Optionally, update the sale status
                $sale->update([
                    'status' => 'completed',
                    'payment_status' => 'paid'
                ]);

                // // Send mail to the customer
                Mail::to($sale->customer->email)->send(new CustomerSaleSuccess($sale = Sale::find($sale->id)));

                // // Send mail to the vendor
                // Mail::to($sale->vendor->email)->send(new SaleSuccess());

                return response()->json([
                    'status' => true,
                    'message' => 'Payment successful',
                    'data' => $payment,
                ], 200);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Payment failed',
                ], 400);
            }
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function verifyCallBack(Request $request)
    {
        return $request;
    }

    // sales_history
    public function salesHistory(Request $request)
    {
        $sales = Sale::where('customer_id', '=', $request->user()->id)->get();

        if ($sales->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'No sales found'
            ], 422);
        }

        return response()->json([
            'status' => true,
            'message' => 'No sales found',
            'sales' => $sales,
        ], 200);
    }

    // single-sale
    public function singleSale(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sale_id' => 'required|exists:sales,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }
        $sale = Sale::where('id', '=', $request->sale_id)->with('products')->with('payments')->get();

        if ($sale->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'No sales found'
            ], 422);
        }

        return response()->json([
            'status' => true,
            'message' => 'No sales found',
            'sale' => $sale,
        ], 200);
    }
}
