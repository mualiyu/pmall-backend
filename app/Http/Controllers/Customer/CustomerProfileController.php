<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CustomerProfileController extends Controller
{
    public function profile(Request $request)
    {
        $customer = $request->user(); // The authenticated customer

        return response()->json([
            "status" => true,
            'customer' => $customer,
        ], 200);
    }

    // Update Profile
    public function update(Request $request)
    {
        $customer = $request->user();

        $validator = Validator::make($request->all(), [
            'fname' => 'sometimes|string|max:255',
            'lname' => 'sometimes|string|max:255',
            'email' => ['sometimes', 'email', Rule::unique('customers')->ignore($customer->id)],
            'username' => ['sometimes', 'string', Rule::unique('customers')->ignore($customer->id)],
            'phone' => ['sometimes', 'string', 'max:15', Rule::unique('customers')->ignore($customer->id)],
            'address' => 'sometimes|string',
            'state' => 'sometimes|string',
            'lga' => 'sometimes|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                "status" => false,
                "message" => $validator->errors(),
            ], 422);
        }

        // $cus = Customer::where('id', '=', $customer->id)->update($request->all());
        // $customer->update($request->all());
        $cus = $customer->update($request->only(['fname', 'lname', 'email', 'username', 'phone', 'address', 'state', 'lga']));

        if ($cus) {
            $cus = Customer::where('id', '=', $customer->id)->get()[0];
            return response()->json([
                "status" => true,
                'customer' => $customer,
            ], 200);
        }else{
            return response()->json([
                "status" => false,
                'message' => "Failed to update.",
            ], 422);
        }
    }

    public function destroy(Request $request)
    {
        $customer = $request->user();

        $customer->delete();

        return response()->json([
            "status" => true,
            "message" => "Customer account deleted successfully.",
        ], 200);
    }
}
