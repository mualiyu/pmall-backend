<?php

use App\Http\Controllers\AccountPackageController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Customer\CustomerAuthController;
use App\Http\Controllers\Customer\CustomerProfileController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicProductController;
use App\Http\Controllers\UserController;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PackagePaymentController;
use App\Http\Controllers\WithdrawalController;
use App\Models\Withdrawal;

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

/*
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
*/

Route::prefix("v1")->group(function () {
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

    Route::middleware('auth:sanctum')->prefix("profile")->group(function () {
        // get profile details
        Route::get('', [ProfileController::class, 'index']);
        // update profile details
        Route::post('update', [ProfileController::class, 'update']);
        // Delete Account
        Route::delete('delete-account', [ProfileController::class, 'destroy']);


        Route::get('hierarchy-l1', [ProfileController::class, 'hierarchy_l1']);
        Route::get('hierarchy-all-downline', [ProfileController::class, 'hierarchy_all_downline']);
    });

    // get infos
    Route::middleware('auth:sanctum')->get('get-all-vendors', [UserController::class, 'all_vendors']);
    Route::middleware('auth:sanctum')->get('get-all-affiliates', [UserController::class, 'all_affiliates']);
    Route::middleware('auth:sanctum')->get('get-all-users', [UserController::class, 'all_users']);

    Route::middleware('auth:sanctum')->post('add-user/{is}', [UserController::class, 'add_user']);
    Route::middleware('auth:sanctum')->post('user/add-vendor', [ProfileController::class, 'Vendor_register_another']);

    // Account packages
    Route::get('account-packages/all', [AccountPackageController::class, 'get_all']);
    Route::post('account-packages/create', [AccountPackageController::class, 'store']);

    // Payment routes
    Route::middleware('auth:sanctum')->post('account-packages/payment/initialize', [PackagePaymentController::class, 'initPayment']);
    Route::middleware('auth:sanctum')->get('account-packages/payment/verify/{reference}', [PackagePaymentController::class, 'verifyPayment']);

    // payment route
    Route::middleware('auth:sanctum')->post('account-payment/store', [UserController::class, 'acct_pay']);

    // products management
    Route::middleware('auth:sanctum')->prefix("products")->group(function () {
        // get all products (admin, vendor & affiliate)
        Route::get('', [ProductController::class, 'index']);

        Route::post('upload-file', [ProductController::class, 'upload']);

        Route::post('create', [ProductController::class, 'create']);

        Route::get('get-single', [ProductController::class, 'show']);

        // update profile details
        Route::post('update/{product}', [ProductController::class, 'update']);

        // Delete Account
        Route::delete('delete-account', [ProductController::class, 'destroy']);

        Route::post('update-status/admin', [ProductController::class, 'adminUpdateStatus']);
    });

    // Admin Withdrawal list, admin can approve or reject
    Route::middleware('auth:sanctum')->prefix('admin-withdrawal')->group(function () {
        Route::get('list', [WithdrawalController::class, 'withdrawal_list']);
        Route::get('single/{id}', [WithdrawalController::class, 'single_withdrawal']);
        Route::post('approve/{id}', [WithdrawalController::class, 'approve_withdrawal']);
        Route::post('reject/{id}', [WithdrawalController::class, 'reject_withdrawal']);
        Route::post('complete/{id}', [WithdrawalController::class, 'complete_withdrawal']);
    });

    // Vendor & Affiliate Withdrawal
    Route::middleware('auth:sanctum')->prefix('withdrawal')->group(function () {
        Route::post('request', [WithdrawalController::class, 'requestWithdrawal']);
        Route::get('history', [WithdrawalController::class, 'history']);
        Route::get('single/{id}', [WithdrawalController::class, 'single']);
    });

    // Product brand
    Route::get('product-brand/get-all', [ProductController::class, 'get_all_brands']);
    Route::middleware('auth:sanctum')->post('product-brand/create', [ProductController::class, 'create_brand']);
    Route::middleware('auth:sanctum')->post('product-brand/update', [ProductController::class, 'update_brand']);
    Route::middleware('auth:sanctum')->post('product-brand/delete', [ProductController::class, 'delete_brand']);
    // Product category
    Route::middleware('auth:sanctum')->get('product-category/get-all', [ProductController::class, 'get_all_categories']);
    Route::middleware('auth:sanctum')->post('product-category/create', [ProductController::class, 'create_category']);
    Route::middleware('auth:sanctum')->post('product-category/update', [ProductController::class, 'update_category']);
    Route::middleware('auth:sanctum')->post('product-category/delete', [ProductController::class, 'delete_category']);
    // Sub category
    Route::middleware('auth:sanctum')->post('product-sub-category/create', [ProductController::class, 'create_sub_category']);
    Route::middleware('auth:sanctum')->post('product-sub-category/update', [ProductController::class, 'update_sub_category']);
    Route::middleware('auth:sanctum')->post('product-sub-category/delete', [ProductController::class, 'delete_sub_category']);


    // Include the customer routes
    require __DIR__ . '/customer.php';
});
