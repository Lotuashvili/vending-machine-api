<?php

namespace Tests;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\WithFaker;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication, DatabaseTransactions, WithFaker;

    protected ?User $buyer = null;

    protected ?User $seller = null;

    protected function user(): User
    {
        $user = User::factory()->create();

        $token = $user->createToken('API');

        return $user->withAccessToken($token);
    }

    protected function buyer(): User
    {
        if ($this->buyer) {
            return $this->buyer;
        }

        return $this->buyer = $this->user()->assignRole('Buyer');
    }

    protected function seller(): User
    {
        if ($this->seller) {
            return $this->seller;
        }

        return $this->seller = $this->user()->assignRole('Seller');
    }

    protected function withBuyerToken(): self
    {
        return $this->withToken($this->buyer()->currentAccessToken()->plainTextToken);
    }

    protected function withSellerToken(): self
    {
        return $this->withToken($this->seller()->currentAccessToken()->plainTextToken);
    }

    protected function product(array $data = []): Product
    {
        return Product::factory()->create(array_merge([
            'seller_id' => $this->seller()->id,
        ], $data));
    }
}
