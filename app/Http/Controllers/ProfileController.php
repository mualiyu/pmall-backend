<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{

    public function index(Request $request)
    {
        if ($request->user()->tokenCan($request->user()->user_type)) {

            return response()->json([
                'status' => true,
                'data' => [
                    'user' => User::where('id', '=', $request->user()->id)->with('referrals')->with('referrer')->get()[0],
                ],
            ], 200);

        }else{
            return response()->json([
                'status' => false,
                'message' => trans('auth.failed')
            ], 422);
        }
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'fname' => 'required|string',
            'lname' => 'required',
            'phone' => 'required',
            'store_name' => 'nullable',
            'acct_name' => 'nullable',
            'acct_number' => 'nullable',
            'acct_type' => 'nullable',
            'bank' => 'nullable',
            'state' => 'nullable',
            'lga' => 'nullable',
            'address' => 'nullable',
            'photo' => 'nullable',
            'description' => 'nullable',
            // User ID that we want to change
            'user_id' => 'nullable',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        if ($request->has('user_id') && !$request->user_id==null) {
            $check = User::find($request->user_id);
            if ($check) {
                $idd = $request->user_id;
                // $request->offsetUnset('user_id');
                $request->replace( $request->except('user_id') );
                $user = User::where('id', '=', $idd)->update($request->all());
                if ($user) {
                    return response()->json([
                        'status' => true,
                        'data' => [
                            'user' => User::where('id', '=', $idd)->get()[0],
                        ],
                        'message' => 'User profile update successfull.'
                    ]);
                } else {
                    return response()->json([
                        'status' => false,
                        'message' => "Failed, Try again!"
                    ], 422);
                }
            }else{
                return response()->json([
                    'status' => false,
                    'message' => "Failed, User not found!"
                ], 422);
            }
        }else{
            $user = User::where('id', '=', $request->user()->id)->update($request->all());
            if ($user) {
                return response()->json([
                    'status' => true,
                    'data' => [
                        'user' => User::where('id', '=', $request->user()->id)->get()[0],
                    ],
                    'message' => 'User profile update successfull.'
                ]);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => "Failed, Try again!"
                ], 422);
            }
        }

    }

    // upload user picture
    public function upload(Request $request)
    {
            $validator = Validator::make($request->all(), [
                'file' => 'required|max:5000|mimes:jpg,png,jpeg,heic',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => $validator->errors()->first()
                ], 422);
            }

            if ($request->hasFile("file")) {
                $fileNameWExt = $request->file("file")->getClientOriginalName();
                $fileName = pathinfo($fileNameWExt, PATHINFO_FILENAME);
                $fileExt = $request->file("file")->getClientOriginalExtension();
                $fileNameToStore = $fileName."_".time().".".$fileExt;
                $request->file("file")->storeAs("public/user", $fileNameToStore);

                $url = url('/storage/user/'.$fileNameToStore);

                return response()->json([
                    'status' => true,
                    'url'=> $url,
                    'message' => "File is successfully uploaded.",
                ]);

            }else{
                return response()->json([
                    'status' => false,
                    'message' => "Error! file upload invalid. Try again."
                ], 422);
            }

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        if ($request->user()->tokenCan($request->user()->user_type)) {

            $request->user()->update([
                'status' => 0,
            ]);

            $request->user()->tokens()->delete();

            return response()->json([
                'status' => true,
                'message' => 'Account Deleted.'
            ], 200);

        }else{
            return response()->json([
                'status' => false,
                'message' => trans('auth.failed')
            ], 422);
        }
    }
}
