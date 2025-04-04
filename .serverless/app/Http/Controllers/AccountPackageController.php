<?php

namespace App\Http\Controllers;

use App\Models\AccountPackage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AccountPackageController extends Controller
{
    // Store package
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'description' => 'required',
            'price' => 'required',
            'type' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $package = AccountPackage::create($request->all());

        if ($package) {
            return response()->json([
                'status' => true,
                'data' => [
                    'package' => $package,
                ],
                'message' => 'Package created successfull.'
            ]);
        }else{
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

    }

    // get all package
    public function get_all()
    {
        return response()->json([
            'status' => true,
            'data' => [
                'packages' => AccountPackage::all(),
            ]
        ]);
    }


}
