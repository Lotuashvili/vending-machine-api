<?php

namespace Tests\Feature;

use Tests\TestCase;

class ProductsTest extends TestCase
{
    public function test_no_access_for_buyer_to_create(): void
    {
        $this->withBuyerToken()
            ->post('api/products')
            ->assertForbidden();
    }

    public function test_no_access_for_buyer_to_update(): void
    {
        $this->withBuyerToken()
            ->patch('api/products/' . $this->product()->id)
            ->assertForbidden();
    }

    public function test_creates_product(): void
    {
        $this->withSellerToken()
            ->postJson('api/products', $data = [
                'name' => $this->faker->name(),
                'price' => $this->faker->randomNumber(3) * 5,
                'amount' => $this->faker->randomNumber(3),
            ])
            ->assertCreated()
            ->assertJson($data);

        $this->assertDatabaseHas('products', array_merge($data, [
            'seller_id' => $this->seller()->id,
        ]));
    }

    public function test_validation(): void
    {
        $this->withSellerToken()
            ->postJson('api/products', [
                'price' => 53,
            ])
            ->assertUnprocessable()
            ->assertInvalid([
                'name',
                'price',
                'amount',
            ]);
    }

    public function test_product_info(): void
    {
        $product = $this->product();

        // Test with buyer token
        $this->withBuyerToken()
            ->get('api/products/' . $product->id)
            ->assertOk()
            ->assertJson($product->only([
                'id',
                'name',
                'amount',
                'price',
            ]));
    }

    public function test_product_update(): void
    {
        $product = $this->product();

        $oldData = $product->only([
            'id',
            'name',
            'price',
            'amount',
        ]);

        $this->withSellerToken()
            ->patchJson('api/products/' . $product->id, $newData = [
                'name' => $this->faker->name,
                'price' => $this->faker->randomNumber(3) * 5,
                'amount' => $this->faker->randomNumber(3),
            ])
            ->assertOk()
            ->assertJson($newData);

        $this->assertNotEquals($newData, $oldData);
        $this->assertDatabaseHas('products', array_merge($newData, ['id' => $product->id]));
        $this->assertDatabaseMissing('products', $oldData);
    }

    public function test_product_deletion(): void
    {
        $product = $this->product();

        $this->withSellerToken()
            ->delete('api/products/' . $product->id)
            ->assertNoContent();

        $this->assertDatabaseMissing('products', $product->only('id'));
    }
}
