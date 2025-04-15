<?php

use App\Http\Controllers\Customer\CustomerAuthController;
use App\Http\Controllers\Customer\CustomerProfileController;
use App\Http\Controllers\Customer\CustomerSaleProductController;
use App\Http\Controllers\PublicProductController;
use Illuminate\Support\Facades\Route;

// Customers Routes
Route::prefix("customer")->group(function () {

    Route::post('register', [CustomerAuthController::class, 'register']);
    Route::post('login', [CustomerAuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('profile', [CustomerProfileController::class, 'profile']);
        Route::put('profile', [CustomerProfileController::class, 'update']);
        Route::delete('profile', [CustomerProfileController::class, 'destroy']);
        Route::post('logout', [CustomerAuthController::class, 'logout']);
    });

    // Customer checking-out routes
    Route::middleware('auth:sanctum')->prefix("checkout")->group(function () {
        Route::post('initiate', [CustomerSaleProductController::class, 'checkout']);


        Route::post('paystack/initiate', [CustomerSaleProductController::class, 'initiatePayment']);
        Route::get('paystack/verify/{reference}', [CustomerSaleProductController::class, 'verifyPayment']);
    });

    // Customer sale history
    Route::middleware('auth:sanctum')->prefix("sales")->group(function () {
        Route::get('history', [CustomerSaleProductController::class, 'salesHistory']);
        Route::get('single-sale', [CustomerSaleProductController::class, 'singleSale']);
    });

    // just for verification
    Route::get('paystack/verify-callback', [CustomerSaleProductController::class, 'verifyCallBack']);
});



// Public Pages
Route::prefix("public")->group(function () {
    // products
    Route::prefix("products")->group(function () {
        Route::get('list-all', [PublicProductController::class, 'index']);
        Route::get('single-product', [PublicProductController::class, 'single_product']);

        Route::get('get-all-categories', [PublicProductController::class, 'get_all_categories']);
        Route::get('list-all-by-category', [PublicProductController::class, 'get_all_products_by_category']);
        Route::get('list-all-by-sub-category', [PublicProductController::class, 'get_all_products_by_sub_category']);
    });
});
