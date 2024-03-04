<?php

use App\Http\Controllers\AccountPackageController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix("v1")->group(function() {
    // No auth error
    Route::get('/error', function () {
        return response()->json([
            'status' => false,
            'message' => "Not Authenticated"
        ], 422);
    })->name('not_auth');

    // Main API for Version One
    # Register
    Route::post('register/{is}', [AuthController::class, 'register']); //is = affliate or vendor
    # Register
    Route::middleware('auth:sanctum')->post('admin/register', [AuthController::class, 'register_admin']);
    # Verify email
    Route::post('email/verify', [AuthController::class, 'verifyEmail']);
    # login
    Route::post('login', [AuthController::class, 'login']);
    # Logout
    Route::middleware('auth:sanctum')->post('logout', [AuthController::class, 'logout']);
    # forgot password
    Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
    # recover
    Route::post('verify-code', [AuthController::class, 'verifyPin']);
    # reset
    Route::post('reset-password', [AuthController::class, 'resetPassword']);

    // upload user images
    Route::post('upload-user-image', [ProfileController::class, 'upload']);

    Route::middleware('auth:sanctum')->prefix("profile")->group(function() {
        // get profile details
        Route::get('', [ProfileController::class, 'index']);
        // update profile details
        Route::post('update', [ProfileController::class, 'update']);

        // Delete Account
        Route::delete('delete-account', [ProfileController::class, 'destroy']);
    });

    // get infos
    Route::middleware('auth:sanctum')->get('get-all-vendors', [UserController::class, 'all_vendors']);
    Route::middleware('auth:sanctum')->get('get-all-affiliates', [UserController::class, 'all_affiliates']);
    Route::middleware('auth:sanctum')->get('get-all-users', [UserController::class, 'all_users']);

    Route::middleware('auth:sanctum')->post('add-user/{is}', [UserController::class, 'add_user']);

    // Account packages
    Route::get('account-packages/all', [AccountPackageController::class, 'get_all']);
    Route::post('account-packages/create', [AccountPackageController::class, 'store']);

    // payment route
    Route::post('account-payment/store', [UserController::class, 'acct_pay']);

    // products management
    Route::middleware('auth:sanctum')->prefix("products")->group(function() {
        // get all products (admin, vendor & affiliate)
        Route::get('', [ProductController::class, 'index']);

        Route::post('upload-file', [ProductController::class, 'upload']);

        Route::post('create', [ProductController::class, 'create']);

        Route::get('get-single', [ProductController::class, 'show']);

        // update profile details
        Route::post('update/{product}', [ProductController::class, 'update']);

        // Delete Account
        Route::delete('delete-account', [ProductController::class, 'destroy']);
    });

    // Product brand
    Route::middleware('auth:sanctum')->get('product-brand/get-all', [ProductController::class, 'get_all_brands']);
    Route::middleware('auth:sanctum')->post('product-brand/create', [ProductController::class, 'create_brand']);
    // Product category
    Route::middleware('auth:sanctum')->get('product-category/get-all', [ProductController::class, 'get_all_categories']);
    Route::middleware('auth:sanctum')->post('product-category/create', [ProductController::class, 'create_category']);


});
