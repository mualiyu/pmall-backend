<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductBrand;
use App\Models\ProductCategory;
use App\Models\ProductSubCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        if ($request->user()->tokenCan("Admin")) {
            return response()->json([
                'status' => true,
                'data' => Product::where(['store_id'=>$request->store_id])->orderBy("created_at", "desc")->paginate(15),
            ]);
        } elseif ($request->user()->tokenCan("Affiliete")) {
            return response()->json([
                'status' => true,
                'data' => Product::where(['store_id'=>$request->store_id])->orderBy("created_at", "desc")->get(),
            ]);
        } elseif ($request->user()->tokenCan("Vendor")) {
            return response()->json([
                'status' => true,
                'data' => Product::where(['store_id'=>$request->user()->store_id])->orderBy("created_at", "desc")->get(),
            ]);
        } else {
            return response()->json([
                'status' => false,
                'message' => trans('auth.failed')
            ], 422);
        }
    }

    public function upload(Request $request)
    {
        // if ($request->user()->tokenCan('Customer')) {

        $validator = Validator::make($request->all(), [
            'file' => 'required|max:5000|mimes:jpg,png,jpeg',
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
            $fileNameToStore = $fileName . "_" . time() . "." . $fileExt;
            $request->file("file")->storeAs("public/productImages", $fileNameToStore, "s3");

            // $url = url('/storage/productImages/' . $fileNameToStore);
            $url = Storage::disk('s3')->url("user/".$fileNameToStore);


            return response()->json([
                'status' => true,
                'message' => "File successfully uploaded.",
                'url' => $url,
            ]);
        } else {
            return response()->json([
                'status' => false,
                'message' => "Error! file upload invalid. Try again."
            ], 422);
        }

        // }else{
        //     return response()->json([
        //         'status' => false,
        //         'message' => trans('auth.failed')
        //     ], 422);
        // }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function create(Request $request)
    {
        if ($request->user()->tokenCan('Vendor')) {

            $validator = Validator::make($request->all(), [
                'category_id' => 'required',
                'sub_category_id' => 'nullable',
                'brand_id' => 'required',
                'name' => 'required|string',
                'image' => 'required',
                'description' => 'nullable',
                'cost_price' => 'required',
                'selling_price' => 'required',
                'inStock' => 'required',
                'quantity' => 'required',
                'tags' => 'required',
                'more_images' => 'nullable',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => $validator->errors()->first()
                ], 422);
            }

            $request['store_id'] = $request->user()->store_id;
            $request['status'] = 0;

            $product = Product::create($request->all());

            if ($product) {
                return response()->json([
                    'status' => true,
                    'message' => "Your product is been submited for review, Thank you!",
                    'data' => $product,
                ]);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => "Failed to submit item, Try again later.",
                ], 422);
            }
        } else {
            return response()->json([
                'status' => false,
                'message' => trans('auth.failed')
            ], 422);
        }
    }

    public function update(Request $request, Product $product)
    {
        if ($request->user()->tokenCan($request->user()->user_type)) {

            $validator = Validator::make($request->all(), [
                'category_id' => 'required',
                'sub_category_id' => 'nullable',
                'brand_id' => 'required',
                'name' => 'required|string',
                'image' => 'required',
                'description' => 'nullable',
                'cost_price' => 'required',
                'selling_price' => 'required',
                'inStock' => 'required',
                'quantity' => 'required',
                'tags' => 'required',
                'more_images' => 'nullable',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => $validator->errors()->first()
                ], 422);
            }

            $request['status'] = 0;

            $prod = $product->update($request->all());

            if ($prod) {
                return response()->json([
                    'status' => true,
                    'message' => "Your product is been updated and is placed under review, Thank you!",
                    'data' => Product::find($product->id),
                ]);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => "Failed to submit item, Try again later.",
                ], 422);
            }
        } else {
            return response()->json([
                'status' => false,
                'message' => trans('auth.failed')
            ], 422);
        }
    }


    public function show(Request $request)
    {
        $product = Product::where('id', '=', $request->product_id)->with("brand")->with("category")->with('sub_category')->get();

        if (count($product) > 0) {
            return response()->json([
                'status' => true,
                'data' => [
                    "product" =>  $product[0],
                ],
            ]);
        } else {
            return response()->json([
                'status' => false,
                'message' => "Product not found"
            ], 422);
        }
    }

    public function destroy(Request $request)
    {
        if ($request->user()->tokenCan($request->user()->user_type)) {

            $product = Product::where('id', '=', $request->product_id)->delete();

            if ($product) {
                return response()->json([
                    'status' => true,
                    'message' => 'Product Deleted.'
                ], 200);
            }else{
                return response()->json([
                    'status' => false,
                    'message' => "Failed to delete product, Try again later."
                ], 422);
            }

        }else{
            return response()->json([
                'status' => false,
                'message' => trans('auth.failed')
            ], 422);
        }
    }

    // Product brand section
    public function create_brand(Request $request)
    {
        if ($request->user()->tokenCan('Admin')) {

            $validator = Validator::make($request->all(), [
                'brand_image' => 'nullable',
                'name' => 'required',
                'description' => 'nullable',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => $validator->errors()->first()
                ], 422);
            }

            $brand = ProductBrand::create($request->all());

            if ($brand) {
                return response()->json([
                    'status' => true,
                    'message' => "A new brand has been created",
                    'data' => $brand,
                ]);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => "Failed to create brand, Try again later.",
                ], 422);
            }
        } else {
            return response()->json([
                'status' => false,
                'message' => trans('auth.failed')
            ], 422);
        }
    }

    public function update_brand(Request $request)
    {
        if ($request->user()->tokenCan('Admin')) {

            $validator = Validator::make($request->all(), [
                'brand_id' => 'required',
                'brand_image' => 'nullable',
                'name' => 'required',
                'description' => 'nullable',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => $validator->errors()->first()
                ], 422);
            }

            $cid = $request->brand_id;
            $data = $request->except(['brand_id']);
            // print_r($request->except(['brand_id']));
            // $request->replace($request->except('brand_id'));

            $brand = ProductBrand::where('id', '=', $cid)->update($data);

            if ($brand) {
                return response()->json([
                    'status' => true,
                    'message' => "Brand has been Updated",
                    'data' =>  ProductBrand::where('id', '=', $cid)->get()[0],
                ]);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => "Failed to create brand, Try again later.",
                ], 422);
            }
        } else {
            return response()->json([
                'status' => false,
                'message' => trans('auth.failed')
            ], 422);
        }
    }

    public function delete_brand(Request $request)
    {
        if ($request->user()->tokenCan("Admin")) {

            $s = ProductBrand::where('id', '=', $request->brand_id)->delete();

            if ($s) {
                return response()->json([
                    'status' => true,
                    'message' => 'Brand Deleted.'
                ], 200);
            }else{
                return response()->json([
                    'status' => false,
                    'message' => "Failed to delete brand, Try again later."
                ], 422);
            }

        }else{
            return response()->json([
                'status' => false,
                'message' => trans('auth.failed')
            ], 422);
        }
    }

    public function get_all_brands(Request $request)
    {
        if ($request->user()->tokenCan($request->user()->user_type)) {

            return response()->json([
                'status' => true,
                'data' => [
                    'brands' => ProductBrand::all(),
                ],
            ], 200);

        }else{
            return response()->json([
                'status' => false,
                'message' => trans('auth.failed')
            ], 422);
        }
    }


    // Category section
    public function create_category(Request $request)
    {
        if ($request->user()->tokenCan('Admin')) {

            $validator = Validator::make($request->all(), [
                'category_image' => 'nullable',
                'name' => 'required',
                'description' => 'nullable',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => $validator->errors()->first()
                ], 422);
            }

            $category = ProductCategory::create($request->all());

            if ($category) {
                return response()->json([
                    'status' => true,
                    'message' => "A new category has been created",
                    'data' => $category,
                ]);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => "Failed to create category, Try again later.",
                ], 422);
            }
        } else {
            return response()->json([
                'status' => false,
                'message' => "Un authorised user"
            ], 422);
        }
    }

    public function get_all_categories(Request $request)
    {
        if ($request->user()->tokenCan($request->user()->user_type)) {

            return response()->json([
                'status' => true,
                'data' => [
                    'categories' => ProductCategory::orderBy('created_at', 'ASC')->with('sub_categories')->get(),
                ],
            ], 200);

        }else{
            return response()->json([
                'status' => false,
                'message' => trans('auth.failed')
            ], 422);
        }
    }

    public function update_category(Request $request)
    {
        if ($request->user()->tokenCan('Admin')) {

            $validator = Validator::make($request->all(), [
                'category_id' => 'required',
                'category_image' => 'nullable',
                'name' => 'required',
                'description' => 'nullable',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => $validator->errors()->first()
                ], 422);
            }

            $cid = $request->category_id;

            // $request->replace($request->except('category_id'));

            $category = ProductCategory::where('id', '=', $cid)->update($request->except(['category_id']));

            if ($category) {
                return response()->json([
                    'status' => true,
                    'message' => "category has been updated",
                    'data' => ProductCategory::where('id', '=', $cid)->get()[0],
                ]);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => "Failed to update category, Try again later.",
                ], 422);
            }
        } else {
            return response()->json([
                'status' => false,
                'message' => "Un authorised user"
            ], 422);
        }
    }

    public function delete_category(Request $request)
    {
        if ($request->user()->tokenCan("Admin")) {

            $s = ProductCategory::where('id', '=', $request->category_id)->delete();

            if ($s) {
                return response()->json([
                    'status' => true,
                    'message' => 'Category Deleted.'
                ], 200);
            }else{
                return response()->json([
                    'status' => false,
                    'message' => "Failed to delete category, Try again later."
                ], 422);
            }

        }else{
            return response()->json([
                'status' => false,
                'message' => trans('auth.failed')
            ], 422);
        }
    }


    // Sub Categories Section
    public function create_sub_category(Request $request)
    {
        if ($request->user()->tokenCan('Admin')) {

            $validator = Validator::make($request->all(), [
                'category_id' => 'nullable',
                'name' => 'required',
                'description' => 'nullable',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => $validator->errors()->first()
                ], 422);
            }

            $subcategory = ProductSubCategory::create($request->all());

            if ($subcategory) {
                return response()->json([
                    'status' => true,
                    'message' => "A new sub-category has been created",
                    'data' => $subcategory,
                ]);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => "Failed to create sub-category, Try again later.",
                ], 422);
            }
        } else {
            return response()->json([
                'status' => false,
                'message' => "Unauthorised user"
            ], 422);
        }
    }

    public function update_sub_category(Request $request)
    {
        if ($request->user()->tokenCan('Admin')) {

            $validator = Validator::make($request->all(), [
                'sub_category_id' => 'required',
                'name' => 'required',
                'description' => 'nullable',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => $validator->errors()->first()
                ], 422);
            }

            $cid = $request->sub_category_id;

            $request->replace($request->except('sub_category_id'));

            $category = ProductSubCategory::where('id', '=', $cid)->update($request->except(['sub_category_id']));

            if ($category) {
                return response()->json([
                    'status' => true,
                    'message' => "Sub-category has been updated",
                    'data' => ProductSubCategory::where('id', '=', $cid)->get()[0],
                ]);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => "Failed to update category, Try again later.",
                ], 422);
            }
        } else {
            return response()->json([
                'status' => false,
                'message' => "Un authorised user"
            ], 422);
        }
    }

    public function delete_sub_category(Request $request)
    {
        if ($request->user()->tokenCan("Admin")) {

            $s = ProductSubCategory::where('id', '=', $request->sub_category_id)->delete();

            if ($s) {
                return response()->json([
                    'status' => true,
                    'message' => 'Sub-category Deleted.'
                ], 200);
            }else{
                return response()->json([
                    'status' => false,
                    'message' => "Failed to delete category, Try again later."
                ], 422);
            }

        }else{
            return response()->json([
                'status' => false,
                'message' => trans('auth.failed')
            ], 422);
        }
    }
}
