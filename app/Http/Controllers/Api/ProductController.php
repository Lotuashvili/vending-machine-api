<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class ProductController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Product::class);
    }

    public function index(): AnonymousResourceCollection
    {
        // Not paginating on purpose.
        return ProductResource::collection(Product::orderBy('name')->get());
    }

    public function store(ProductRequest $request): ProductResource
    {
        $product = $request->user()->products()->create($request->validated());

        return new ProductResource($product);
    }

    public function update(Product $product, ProductRequest $request): ProductResource
    {
        $product->update($request->validated());

        return new ProductResource($product);
    }

    public function destroy(Product $product): Response
    {
        $product->delete();

        return response()->noContent();
    }
}
