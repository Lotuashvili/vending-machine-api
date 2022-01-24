<?php

namespace Tests\Feature;

use Tests\TestCase;

class BalanceTest extends TestCase
{
    public function test_deposit(): void
    {
        $user = $this->buyer();

        $this->assertEquals(0, $user->balance);

        $amount = $this->faker->randomElement(config('app.coins'));

        $this->withBuyerToken()
            ->postJson('api/balance/deposit', compact('amount'))
            ->assertCreated()
            ->assertJson([
                'balance' => $amount,
            ]);

        $this->assertEquals($amount, $user->fresh()->balance);
    }

    public function test_unacceptable_coin(): void
    {
        $user = $this->buyer();

        $this->withBuyerToken()
            ->postJson('api/balance/deposit', [
                'amount' => 22,
            ])
            ->assertUnprocessable()
            ->assertInvalid('amount');

        $this->assertEquals(0, $user->fresh()->balance);
    }

    public function test_balance_reset_with_change(): void
    {
        $user = $this->buyer();

        $user->deposit(95);

        $this->withBuyerToken()
            ->postJson('api/balance/reset')
            ->assertOk()
            ->assertExactJson([
                'balance' => 95,
                'finalBalance' => 0,
                'change' => [
                    50 => 1,
                    20 => 2,
                    5 => 1,
                ],
            ]);

        $this->assertEquals(0, $user->fresh()->balance);
    }
}
