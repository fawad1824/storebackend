<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Orders;
use App\Models\Products;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class MainController extends Controller
{
    public function __invoke()
    {
        // Your controller logic here
    }

    // category
    public function Category()
    {
        $category = Category::all();
        return response()->json($category, 200);
    }

    public function AddCategory(Request $request)
    {

        if ($request->id) {
            if ($request->delete) {
                $category = Category::where("id", $request->id)->delete();
                if ($category) {
                    return $this->MessageError($category, "Category Deleted Successfully");
                }
            } else {
                $category = Category::where("id", $request->id)->first();
                $category->name = $request->name;
                if ($category->save()) {
                    return $this->MessageError($category, "Category Updated Successfully");
                }
            }
        } else {
            $category = new Category();
            $category->name = $request->name;
            if ($category->save()) {
                return $this->MessageError($category, "Category Added Successfully");
            }
        }
    }


    // Products
    public function Product()
    {
        $category = DB::table('category')
            ->join('products', 'category.id', '=', 'products.category_id')
            ->select('*', 'category.name as category')
            ->get();

        return response()->json($category, 200);
    }
    public function GetSingleProduct($name)
    {
        $category = DB::table('category')
            ->join('products', 'category.id', '=', 'products.category_id')
            ->select('*', 'category.name as category')
            ->where('slug', $name)
            ->first();

        return response()->json($category, 200);
    }
    public function AddProduct(Request $request)
    {
        // Handle image uploads
        if ($request->hasFile('images')) {
            $image = $request->file('images')[0];
            $path = $image->store('products/images', 'public');
            $imagePath = asset('storage/' . $path); // Store image URL
        }

        // Create an array to store gallery image URLs
        $galleryPaths = [];

        // Handle gallery picture uploads (if applicable)
        if ($request->hasFile('gallery')) {
            foreach ($request->file('gallery') as $galleryImage) {
                $galleryPath = $galleryImage->store('products/gallery', 'public');
                $galleryPaths[] = asset('storage/' . $galleryPath); // Store gallery image URLs
            }
        }

        // Join the gallery image URLs with commas
        $galleryPathString = implode(',', $galleryPaths);

        $string = $request->input('name');
        // Remove spaces and convert to lowercase with hyphens
        $modifiedString = strtolower(str_replace(' ', '-', $string));

        // Create a new product
        $product = new Products([
            'name' => $request->input('name'),
            'slug' => $modifiedString,
            'price' => $request->input('price'),
            'qty' => $request->input('quantity'),
            'description' => $request->input('description'),
            'category_id' => $request->input('category_id'),
            'discount' =>$request->input('discount'),
            'image' => $imagePath ?? "", // Store as a single image path
            'gallery' => $galleryPathString ?? "", // Store gallery images as a comma-separated string
        ]);

        // Save the product to the database
        $product->save();

        return response()->json(['message' => 'Product added successfully', 'product' => $product], 201);
    }

    public function deleteProduct(Request $request)
    {
        Products::where('id', $request->id)->delete();
        return response()->json(['message' => 'Product Deleted successfully'], 200);
    }


    // Orders
    public function Orders()
    {
        $category = DB::table('category')
            ->join('products', 'category.id', '=', 'products.category_id')
            ->join('orders', 'products.id', '=', 'orders.product_id')
            ->select('orders.*', 'products.name as product', 'category.name as category')
            ->get();

        return response()->json($category, 200);
    }
    public function AddOrders(Request $request)
    {
        $order = new Orders();
        $order->product_id = $request->product_id;
        $order->name = $request->name;
        $order->phone = $request->phone;
        $order->city = $request->city;
        $order->address = $request->address;
        $order->qty = $request->qty;
        $order->price = $request->price;
        $order->status = "pending";
        $order->save();
        return response()->json(['message' => 'Order Added successfully'], 200);
    }
    public function DeleteOrders()
    {
        return "Deleted Order";
    }

    public function MessageError($data, $message)
    {
        return response()->json([
            "status" => "ok", // Use => instead of =
            "data" => $data, // Use => instead of =
            "message" => $message, // Use => instead of =
        ], 200);
    }
}
