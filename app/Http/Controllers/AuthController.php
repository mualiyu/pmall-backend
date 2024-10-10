<?php

namespace App\Http\Controllers;

use App\Mail\ResetPassword;
use App\Mail\VerifyEmail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function register(Request $request, $is)
    {
        if (isset($is)) {

            // For vendor
            if ($is == "vendor") {
                $validator = Validator::make($request->all(), [
                    'fname' => 'required|string',
                    'lname' => 'required',
                    'email' => 'required|email|unique:users,email',
                    'phone' => 'required',
                    'store_name' => 'required|unique:users,store_name',
                    'ref_id' => 'nullable',
                    'package_id' => 'required',
                ]);

                if ($validator->fails()) {
                    return response()->json([
                        'status' => false,
                        'message' => $validator->errors()->first()
                    ], 422);
                }

                $request['user_type'] = "Vendor";
                $request['store_id'] = "PMS-".rand(100000, 999999);
                $request['status'] = "1";
                $request['photo'] = "assets.pmall.ng/user/default.png";

                $request['my_ref_id'] = "PM-".rand(100000, 999999);

                $pass = Str::random(8);

                $request['password'] = Hash::make($pass);

                $user = User::create($request->all());

                if ($user) {
                    $verify2 =  DB::table('password_reset_tokens')->where([
                        ['email', $request->all()['email']]
                    ]);

                    if ($verify2->exists()) {
                        $verify2->delete();
                    }
                    $pin = rand(10000000, 99999999);
                    DB::table('password_reset_tokens')
                        ->insert(
                            [
                                'email' => $request->all()['email'],
                                'token' => $pin
                            ]
                        );
                }else{
                    return response()->json([
                        'status' => false,
                        'message' => "Failed, Try again!"
                    ], 422);
                }

                Mail::to($request->email)->send(new VerifyEmail($pin, $pass, $request->email, $request->store_name));

                $token = $user->createToken('pmall-Vendor', ['Vendor'])->plainTextToken;

                return response()->json([
                    'status' => true,
                    'data' => [
                        'user' => User::where('id', '=', $user->id)->with('package')->get()[0],
                        'token' =>$token,
                        // test
                        // 'pin' =>$pin,
                        // 'pass' =>$pass,
                    ],
                    'message' => 'Registration successfull, an email has been sent for verification.'
                ]);


            // For Affiliate
            }elseif ($is == "affiliate") {
                $validator = Validator::make($request->all(), [
                    'fname' => 'required|string',
                    'lname' => 'required',
                    'email' => 'required|email|unique:users,email',
                    'username' => 'required|unique:users,username',
                    'password' => 'required',
                    'phone' => 'required',
                    'ref_id' => 'nullable',
                    'package_id' => 'required',
                ]);

                if ($validator->fails()) {
                    return response()->json([
                        'status' => false,
                        'message' => $validator->errors()->first()
                    ], 422);
                }

                $request['user_type'] = "Affiliate";
                $request['status'] = "1";
                $request['photo'] = "assets.pmall.ng/user/default.png";

                $request['my_ref_id'] = "PM-".rand(100000, 999999);

                $user = User::create($request->all());

                if ($user) {
                    $verify2 =  DB::table('password_reset_tokens')->where([
                        ['email', $request->all()['email']]
                    ]);

                    if ($verify2->exists()) {
                        $verify2->delete();
                    }
                    $pin = rand(10000000, 99999999);
                    DB::table('password_reset_tokens')
                        ->insert(
                            [
                                'email' => $request->all()['email'],
                                'token' => $pin
                            ]
                        );
                }else{
                    return response()->json([
                        'status' => false,
                        'message' => "Failed, Try again!"
                    ], 422);
                }

                Mail::to($request->email)->send(new VerifyEmail($pin, $request->password, $request->email));

                $token = $user->createToken('pmall-Affiliate', ['Affiliate'])->plainTextToken;

                return response()->json([
                    'status' => true,
                    'data' => [
                        'user' => User::where('id', '=', $user->id)->with('package')->get()[0],
                        'token' =>$token,
                        // test
                        // 'pin' =>$pin,
                    ],
                    'message' => 'Registration successfull, an email has been sent for verification.'
                ], 200);
                //

            // else if not Both 🤪
            }else{

                return response()->json([
                    'status' => false,
                    'message' => "Failed, End-point not found!!!"
                ], 422);
            }

            // bussiness logic here

        }else {
            return response()->json([
                'status' => false,
                'message' => "Failed, End-point not found!"
            ], 422);
        }
    }

    public function register_admin(Request $request)
    {
            if (!$request->user()->tokenCan("Admin")) {
                return response()->json([
                    'status' => false,
                    'message' => "you have to be authorized"
                ], 422);
            }
            $validator = Validator::make($request->all(), [
                'fname' => 'required|string',
                'lname' => 'required',
                'email' => 'required|email|unique:users,email',
                'username' => 'required|unique:users,username',
                'password' => 'required',
                'phone' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => $validator->errors()->first()
                ], 422);
            }

            $request['user_type'] = "Admin";
            $request['status'] = "1";
            $request['photo'] = "assets.pmall.ng/user/default.png";

            $request['my_ref_id'] = "PM-".rand(100000, 999999);

            $user = User::create($request->all());

            if ($user) {
                $verify2 =  DB::table('password_reset_tokens')->where([
                    ['email', $request->all()['email']]
                ]);

                if ($verify2->exists()) {
                    $verify2->delete();
                }
                $pin = rand(10000000, 99999999);
                DB::table('password_reset_tokens')
                    ->insert(
                        [
                            'email' => $request->all()['email'],
                            'token' => $pin
                        ]
                    );
            }else{
                return response()->json([
                    'status' => false,
                    'message' => "Failed, Try again!"
                ], 422);
            }

            Mail::to($request->email)->send(new VerifyEmail($pin, $request->password));

            $token = $user->createToken('pmall-Admin', ['Admin'])->plainTextToken;

            return response()->json([
                'status' => true,
                'data' => [
                    'user' => User::where('id', '=', $user->id)->with('package')->get()[0],
                    'token' =>$token,
                    // test
                    // 'pin' =>$pin,
                ],
                'message' => 'Registration successfull, an email has been sent for verification.'
            ], 200);
            //

    }


    // verify email with token link
    public function verifyEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => ['required'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }
        $select = DB::table('password_reset_tokens')
            ->where('token', $request->token);

        if ($select->get()->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => "Token doesn't exist!"
            ], 422);
        }

        // dd($select->get()[0]->email);

        $u = User::where('email', '=', $select->get()[0]->email)->get()[0];

        $select2 = DB::table('password_reset_tokens')
            ->where('token', $request->token)
            ->delete();


        $user = User::find($u->id);
        $user->email_verified_at = Carbon::now()->getTimestamp();
        $user->save();

        $token = $user->createToken('pmall-'.$user->user_type, [$user->user_type])->plainTextToken;

        return response()->json([
            'status' => true,
            'data' => [
                'user' => User::where('id', '=', $user->id)->with('package')->get()[0],
                'token' =>$token,
            ],
            'message' => 'Email verification successfull.'
        ], 200);
    }

    // Login user
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required',
            'password' => 'required',
            'device_name' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        // $user = Applicant::where('username', $request->username)->first();
        $user = User::where('username', '=', $request->username)->get();
        if (!count($user)>0) {
            $user = User::where('email', '=', $request->username)->get();
            // $user = Customer::where('email', $request->username)->first();
            if(!count($user)>0){
                return response()->json([
                    'status' => false,
                    'message' => "User not found or invalid credentials"
                ], 422);
            }
        }
        $user = $user[0];

        if (!Hash::check($request->password, $user->password) || !$user->status=='1') {
            return response()->json([
                'status' => false,
                'message' => "User not found or invalid credentials"
            ], 422);
        }else{
            // if ($user->email_verified_at==null) {
            //     return response()->json([
            //         'status' => false,
            //         'message' => "Email is not verified"
            //     ], 422);
            // }
            // if (!$user->isActive == 1) {
            //     return response()->json([
            //         'status' => false,
            //         'message' => "Sorry your account is not active, Pay for a package to acitvate your account."
            //     ], 422);
            // }
            $can = $user->user_type;
            return response()->json([
                'status' => true,
                'data' => [
                    'user' => $user,
                    'token' => $user->createToken("pmall-".$can, [$can])->plainTextToken
                ],
                'message' => 'Login successfull.'
            ]);
        }

    }


    //logout
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        return response()->json([
            'status' => true,
            'message' => "Logged out",
        ], 200);

    }



    // Forgot password logic
    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'string', 'email', 'max:255'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $verify = User::where('email', $request->all()['email'])->exists();

        if ($verify) {
            $verify2 =  DB::table('password_reset_tokens')->where([
                    ['email', $request->all()['email']]
                ]);

            if ($verify2->exists()) {
                $verify2->delete();
            }

            $token = random_int(100000,
                999999
            );
            $password_reset = DB::table('password_reset_tokens')->insert([
                'email' => $request->all()['email'],
                'token' =>  $token,
                'created_at' => Carbon::now()
            ]);

            if ($password_reset) {
                Mail::to($request->all()['email'])->send(new ResetPassword($token));

                return response()->json([
                    'status' => true,
                    'message' => 'Please check your email for a 6 digit pin.',
                    // 'pin'=>$token,
                ], 200);
            }
        } else {
            return response()->json([
                'status' => false,
                'message' => "This email does not exist"
            ], 422);
        }
    }

    // Verify forgon password pin logic
    public function verifyPin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'string', 'email', 'max:255'],
            'token' => ['required'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $check = DB::table('password_reset_tokens')->where([
            ['email', $request->all()['email']],
            ['token', $request->all()['token']],
        ]);

        if ($check->exists()) {
            $difference = Carbon::now()->diffInSeconds($check->first()->created_at);
            if ($difference > 3600) {
                return response()->json([
                    'status' => false,
                    'message' => "Token Expired",
                ], 422);
            }

            $delete = DB::table('password_reset_tokens')->where([
                ['email', $request->all()['email']],
                ['token', $request->all()['token']],
            ])->delete();

            return response()->json([
                'status' => true,
                'message' => 'You can now reset your password.'
            ], 200);
        } else {
            return response()->json([
                'status' => false,
                'message' => "Invalid token",
            ], 401);
        }
    }

    // Resset password with email and the new password
    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }
        $u = User::where('email', '=', $request->email)->get()[0];
        $user = User::where('email', '=', $request->email);
        $user->update([
            'password' => Hash::make($request->password)
        ]);

        $can = $u->user_type;
        return response()->json([
            'status' => true,
            'data' => [
                'user' => $u,
                'token' => $u->createToken("pmall-" . $can, [$can])->plainTextToken
            ],
            'message' => 'Your password has been reset.'
        ], 200);
    }




    // ends of class AuthController
}
