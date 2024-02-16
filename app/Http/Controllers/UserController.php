<?php

namespace App\Http\Controllers;

use App\Models\AccountPackage;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    // vendor resources {{Admin, affiliate, vendor}}
    public function all_vendors(Request $request)
    {

        if ($request->user()->tokenCan("Vendor")) {

            return response()->json([
                'status' => true,
                'data' => [
                    'vendors' => User::where(['store_id' => $request->user()->store_id, 'user_type' => 'Vendor'])->get(),
                ],
            ], 200);
        } elseif ($request->user()->tokenCan("Affiliate")) {
            return response()->json([
                'status' => true,
                'data' => [
                    'vendors' => User::where(['ref_id' => $request->user()->my_ref_id, 'user_type' => 'Vendor'])->get(),
                ],
            ], 200);
        } elseif ($request->user()->tokenCan("Admin")) {
            return response()->json([
                'status' => true,
                'data' => [
                    'vendors' => User::where(['user_type' => 'Vendor'])->get(),
                ],
            ], 200);
        } else {
            return response()->json([
                'status' => false,
                'message' => trans('auth.failed')
            ], 422);
        }
    }

    // Affiliate resources  {{Admin, affiliate}}
    public function all_affiliates(Request $request)
    {

        if ($request->user()->tokenCan("Affiliate")) {
            return response()->json([
                'status' => true,
                'data' => [
                    'affiliates' => User::where(['ref_id' => $request->user()->my_ref_id, 'user_type' => 'Affiliate'])->get(),
                ],
            ], 200);
        } elseif ($request->user()->tokenCan("Admin")) {
            return response()->json([
                'status' => true,
                'data' => [
                    'affiliates' => User::where(['user_type' => 'Affiliate'])->get(),
                ],
            ], 200);
        } else {
            return response()->json([
                'status' => false,
                'message' => trans('auth.failed')
            ], 422);
        }
    }

    // All users resources {{Admin}}
    public function all_users(Request $request)
    {
        if ($request->user()->tokenCan("Admin")) {
            return response()->json([
                'status' => true,
                'data' => [
                    'users' => User::orderBy('created_at', 'DESC')->get(),
                ],
            ], 200);
        } else {
            return response()->json([
                'status' => false,
                'message' => trans('auth.failed')
            ], 422);
        }
    }


    // Add New User
    public function add_user()
    {
    }

    public function acct_pay(Request $request)
    {
        if ($request->user()->tokenCan($request->user()->user_type)) {

            $validator = Validator::make($request->all(), [
                'package_id' => 'required',
                'amount' => 'required',
                'method' => 'required',
                'note' => 'required',
                'ref_id' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => $validator->errors()->first()
                ], 422);
            }

            $package = AccountPackage::find($request->package_id);

            if ($package) {

                $request['user_id'] = $request->user()->id;
                $request['isapproved'] = 0;

                $pay = Payment::create($request->all());

                if ($pay) {
                    if ($package->price == $request->amount) {

                        $pay->isapproved = 1;
                        $pay->save();

                        User::where('id', '=', $request->user()->id)->update([
                            'isActive'=>1
                        ]);

                        return response()->json([
                            'status' => true,
                            'message' => 'Payment successfull.'
                        ], 200);
                    }else {
                        return response()->json([
                            'status' => false,
                            'message' => "Successfull payment, But amount paid is not the same as the package price. please contact your admin for support.",
                        ], 422);
                    }

                } else {
                    return response()->json([
                        'status' => false,
                        'message' => "Failed, Try again.",
                    ], 422);
                }

            } else {
                return response()->json([
                    'status' => false,
                    'message' => "Package not found, Try again.",
                ], 422);
            }
        }
    }
}
