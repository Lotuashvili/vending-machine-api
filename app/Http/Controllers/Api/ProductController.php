<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;

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

    public function show(Product $product): ProductResource
    {
        return new ProductResource($product);
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

    public function buy(Product $product, Request $request): JsonResponse
    {
        throw_unless($product->amount > 0, ValidationException::withMessages([
            'product' => 'Product is not in stock',
        ]));

        $request->validate([
            'amount' => 'nullable|integer|min:1|max:' . $product->amount,
        ], [
            'max' => 'Product has only :max items in stock',
        ]);

        $price = $product->price;
        $amount = $request->input('amount', 1);
        $total = $price * $amount;

        $user = $request->user();
        // If user does not have enough balance, it will throw a validation error
        $user->withdraw($total);

        // If user has enough balance, then reduce the amount
        $product->update([
            'amount' => $product->amount - $amount,
        ]);

        return response()->json([
            'product' => $product->name,
            'available' => $product->amount,
            'total_spent' => $total,
            'balance' => $user->balance,
        ], 201);
    }
}
