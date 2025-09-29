<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        // return ProductResource::collection(Product::all()); --- IGNORE ---
        // Eager load the category relationship to avoid N+1 query problem
        $products = Product::with('category')->paginate(2);

        return ProductResource::collection($products);
    }
}
