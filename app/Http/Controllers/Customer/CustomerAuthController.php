<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CustomerAuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'fname' => 'required|string|max:255',
            'lname' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:customers',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                "status" => false,
                "message" => $validator->errors(),
            ], 422);
        }

        $customer = Customer::create([
            'fname' => $request->fname,
            'lname' => $request->lname,
            'email' => $request->email,
            'username' => $request->username,
            'phone' => $request->phone,
            'address' => $request->address,
            'state' => $request->state,
            'lga' => $request->lga,
            'password' => Hash::make($request->password),
        ]);

        $can = "Customer";
        $token = $customer->createToken('customerAuthToken', [$can])->plainTextToken;

        return response()->json([
            "status" => true,
            'customer' => $customer,
            'token' => $token
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        $customer = Customer::where('email', $request->username)->first();

        if (!$customer) {
            $customer = Customer::where('username', $request->username)->first();
        }

        if (!$customer || !Hash::check($request->password, $customer->password)) {
            return response()->json([
                "status" => false,
                'message' => 'Invalid credentials'
            ], 401);
        }

        $can = "Customer";
        $token = $customer->createToken('customerAuthToken', [$can])->plainTextToken;

        return response()->json([
            "status" => true,
            'customer' => $customer,
            'token' => $token
        ], 200);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json([
            "status" => true,
            'message' => 'Logged out'
        ], 200);
    }
}
