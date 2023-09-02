<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Exception;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $products = Product::all();
        return response()->json([
            'status' => 200,
            'products' => $products
        ]);
        
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            "title"=>"required",
            "description"=>"required",
            "image"=>"required|image"
        ]);

        try {
            $imageName = Str::random().".".$request->image->getClientOriginalExtension();
            Storage::putFileAs("/public/products/image", $request->image, $imageName, 'public');
            // Store the form data along with the dynamic image name into the database.
            Product::create($request->post()+[
                "image"=>$imageName
            ]);

            return response()->json([
                "message"=>"Product created successfully!"
            ]);

        } catch (Exception $e) {
            Log::error($e->getMessage());

            return response()->json([
                "message"=>"Error for creating a product"
            ]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        // $product = Product::find($id);
        return response()->json(['product'=>$product]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        $request->validate([
            "title"=>"required",
            "description"=>"required",
            "image"=>"nullable"
        ]);

        try {
            $product->fill($request->post())->update();

            if ($request->hasFile('image')) {
                // Remove the image
                if ($product->image) {
                    $oldImage = Storage::disk('public')->exists("products/image/{$product->image}");
                    if ($oldImage) {
                        Storage::disk('public')->delete("products/image/{$product->image}");
                    }
                }

                $imageName = Str::random().".".$request->image->getClientOriginalExtension();
                Storage::putFileAs("/public/products/image", $request->image, $imageName, 'public');
                // Store the form data along with the dynamic image name into the database.
                $product->image = $imageName;
                $product->save();
            }

            return response()->json([
                "message"=>"Product updated successfully!"
            ]);

        } catch (Exception $e) {
            Log::error($e->getMessage());

            return response()->json([
                "message"=>"Error for updating a product"
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        try {
            if ($product->image) {
                $oldImage = Storage::disk('public')->exists("products/image/{$product->image}");
                if ($oldImage) {
                    Storage::disk('public')->delete("products/image/{$product->image}");
                }
            }
            $product->delete();
            return response()->json([
                'message' => 'Product deleted successfully'
            ]);
        } catch (Exception $e) {
            Log::error($e->getMessage());

            return response()->json([
                'message' => 'Error while deleting a product!'
            ]);
        }
    }
}
