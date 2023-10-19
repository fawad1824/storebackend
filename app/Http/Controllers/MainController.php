<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Orders;
use App\Models\Products;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

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

        $galleryPicturesEn = [];
        $galleryPicturesAr = [];

        foreach ($request->all() as $key => $value) {
            if (strpos($key, 'galleryPictureen') === 0) {
                // This is an English file input
                $galleryPicturesEn[] = $value;
            } elseif (strpos($key, 'galleryPicturear') === 0) {
                // This is an Arabic file input
                $galleryPicturesAr[] = $value;
            }
        }


        $enPaths = [];
        $arPaths = [];

        foreach ($galleryPicturesEn as $file) {
            $imageName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->storeAs('public/products/', $imageName);
            $enPaths[] = asset('storage/products/' . $imageName);
        }

        foreach ($galleryPicturesAr as $file) {
            $imageName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->storeAs('public/products/', $imageName);
            $arPaths[] = asset('storage/products/' . $imageName);
        }



        $string = $request->input('nameen');
        // Remove spaces and convert to lowercase with hyphens
        $modifiedString = strtolower(str_replace(' ', '-', $string));

        // Check if English data is present
        if ($request->has('nameen')) {
            $product = new Products();
            $product->name = $request->input('nameen');
            $product->slug = $modifiedString; // Set the slug for the English product
            $product->price = $request->input('priceen');
            $product->discount = $request->input('discounten');
            $product->qty = $request->input('quantityen');
            $product->category_id = $request->input('categoryen');
            $product->description = $request->input('descriptionen');
            $product->is_lan = 'en';
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = time() . '.' . $image->getClientOriginalExtension();
                $image->storeAs('public/products/', $imageName);;
                $product->image = asset('storage/products/' . $imageName);
            }

            $enGalleryUrlse = implode(',', $enPaths);
            $product->gallery = $enGalleryUrlse;
            $product->save();
        }

        // Check if Arabic data is present
        if ($request->has('namear')) {
            $product1 = new Products();
            $product1->name = $request->input('namear');
            $product1->slug = $modifiedString; // Set the slug for the Arabic product
            $product1->price = $request->input('pricear');
            $product1->discount = $request->input('discountar');
            $product1->qty = $request->input('quantityar');
            $product1->category_id = $request->input('categoryar');
            $product1->description = $request->input('descriptionar');
            $product1->is_lan = 'ar';
            if ($request->hasFile('image2')) {
                $image1 = $request->file('image2');
                $imageName1 = time() . '.' . $image1->getClientOriginalExtension();
                $image1->storeAs('public/products/', $imageName);
                $product1->image = asset('storage/products/' . $imageName1);
            }

            $enGalleryUrls = implode(',', $arPaths);
            $product1->gallery = $enGalleryUrls;
            $product1->save();
        }

        return response()->json(['message' => 'Product saved successfully']);
        die();



        return response()->json(['product' => $request->all()], 201);


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
            'discount' => $request->input('discount'),
            'image' => $imagePath ?? "", // Store as a single image path
            'gallery' => $galleryPathString ?? "", // Store gallery images as a comma-separated string
        ]);

        // Save the product to the database
        $product->save();

        return response()->json(['message' => 'Product added successfully', 'product' => $product], 201);
    }

    public function deleteProduct(Request $request)
    {
        $productslug = Products::where('id', $request->id)->first();
        $product = Products::where('slug', $productslug->slug)->where('is_lan', 'en')->first();
        $this->deleteImage($product);
        $product1 = Products::where('slug', $productslug->slug)->where('is_lan', 'ar')->first();
        $this->deleteImage($product1);

        // Delete the product from the database
        Products::where('slug', $productslug->slug)->delete();
        return response()->json(['message' => 'Product Deleted successfully'], 200);
    }
    public function deleteImage($product)
    {

        // Delete the associated images and gallery from storage
        if (isset($product->image) && !empty($product->image)) {
            // Construct the local image path
            $imagePath = public_path($product->image);
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }

        if (isset($product->gallery) && !empty($product->gallery)) {
            // Delete the product's gallery images
            $galleryPaths = explode(',', $product->gallery);
            foreach ($galleryPaths as $galleryPath) {
                // Construct the local gallery image path
                $galleryPath = public_path($galleryPath);
                if (file_exists($galleryPath)) {
                    unlink($galleryPath);
                }
            }
        }
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
        $products = Products::where('id', $request->product_id)->first();
        $calulatedqty = $products->qty - $request->qty;
        $products->qty = $calulatedqty;
        $products->save();
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
