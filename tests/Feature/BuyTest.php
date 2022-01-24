<?php

namespace Tests\Feature;

use Tests\TestCase;

class BuyTest extends TestCase
{
    public function test_buy_product(): void
    {
        $user = $this->buyer();
        $product = $this->product([
            'price' => 20,
            'amount' => 1,
        ]);

        $user->deposit(20);

        $this->withBuyerToken()
            ->postJson('api/products/buy/' . $product->id)
            ->assertCreated()
            ->assertJson([
                'product' => $product->name,
                'available' => $product->amount - 1,
                'total_spent' => $product->price,
                'balance' => $user->balance - $product->price,
            ]);

        $this->assertEquals(0, $product->fresh()->amount);
        $this->assertEquals(0, $user->fresh()->balance);
    }

    public function test_insufficient_funds(): void
    {
        $product = $this->product([
            'price' => 50,
        ]);

        $this->withBuyerToken()
            ->postJson('api/products/buy/' . $product->id)
            ->assertUnprocessable()
            ->assertInvalid('balance');
    }

    public function test_no_stock(): void
    {
        $product = $this->product([
            'amount' => 0,
        ]);

        $this->withBuyerToken()
            ->postJson('api/products/buy/' . $product->id)
            ->assertUnprocessable()
            ->assertInvalid('amount');
    }

    public function test_maximum_stock(): void
    {
        $product = $this->product([
            'amount' => 10,
        ]);

        $this->withBuyerToken()
            ->postJson('api/products/buy/' . $product->id, [
                'amount' => 15,
            ])
            ->assertUnprocessable()
            ->assertInvalid('amount');
    }
}
