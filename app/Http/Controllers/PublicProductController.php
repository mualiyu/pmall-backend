<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\User;
use Illuminate\Http\Request;

class PublicProductController extends Controller
{
    public function index(Request $request)
    {

        $products = Product::orderBy("created_at", 'DESC')->get();

        if (count($products) > 0) {
            # code...
            return response()->json([
                'status' => true,
                'data' => $products
            ], 200);
        }
        return response()->json([
            'status' => false,
            'message' => "No product foun at the moment"
        ], 422);
    }


    public function single_product(Request $request)
    {

        $product = Product::where('id', '=', $request->product_id)->with('vendor')->with('brand')->with('category')->with('sub_category')->get();

        if (count($product) > 0) {
            $product = $product[0];

            return response()->json([
                'status' => true,
                'data' => $product
            ], 200);
        }
        return response()->json([
            'status' => false,
            'message' => "Product not found, Try again"
        ], 422);
    }

    public function get_all_categories(Request $request)
    {

        $cats = ProductCategory::orderBy('created_at', "ASC")->with('sub_categories')->get();

        if (count($cats) > 0) {
            return response()->json([
                'status' => true,
                'data' => $cats
            ], 200);
        }
        return response()->json([
            'status' => false,
            'message' => "Categories not found, Try again"
        ], 422);
    }


    public function get_all_products_by_category(Request $request)
    {

        $products = Product::where('category_id', '=', $request->category_id)->get();

        if (count($products) > 0) {
            // $product = $product[0];

            return response()->json([
                'status' => true,
                'data' => $products
            ], 200);
        }
        return response()->json([
            'status' => false,
            'message' => "Products not found in this category, Try again"
        ], 422);
    }

    public function get_all_products_by_sub_category(Request $request)
    {

        $products = Product::where('sub_category_id', '=', $request->sub_category_id)->get();

        if (count($products) > 0) {
            // $product = $product[0];

            return response()->json([
                'status' => true,
                'data' => $products
            ], 200);
        }
        return response()->json([
            'status' => false,
            'message' => "Products not found from this sub category, Try again"
        ], 422);
    }

    public function get_all_products_by_vendor(Request $request)
    {

        $user = User::where(['user_type' => 'Vendor', 'store_id' => $request->store_id])->with('products')->get();

        if (count($user) > 0) {
            $user = $user[0];

            $products = $user->products()->get();
            if (count($products) > 0) {
                // $product = $product[0];

                return response()->json([
                    'status' => true,
                    'data' => $products
                ], 200);
            }

            return response()->json([
                'status' => false,
                'message' => "Products not found from this vendor, Try again"
            ], 422);
        }
        return response()->json([
            'status' => false,
            'message' => "Products not found from this vendor, Try again"
        ], 422);
    }
}
