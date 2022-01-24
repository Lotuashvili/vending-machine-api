<?php

namespace Tests\Feature;

use Illuminate\Support\Arr;
use Tests\TestCase;

class UsersTest extends TestCase
{
    public function test_registration(): void
    {
        $this->postJson('api/auth/register', $data = [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->email(),
            'password' => $password = $this->faker->password(8),
            'password_confirmation' => $password,
            'role' => $this->faker->randomElement(['Buyer', 'Seller']),
        ])->assertCreated();

        $this->assertDatabaseHas('users', Arr::only($data, ['id', 'name', 'email']));
    }

    public function test_registration_validation(): void
    {
        $this->post('api/auth/register')
            ->assertUnprocessable()
            ->assertInvalid([
                'name',
                'email',
                'password',
                'role',
            ]);
    }

    public function test_login_validation(): void
    {
        $this->post('api/auth/login')
            ->assertUnprocessable()
            ->assertInvalid([
                'email',
                'password',
            ]);
    }

    public function test_login_failure(): void
    {
        $this->post('api/auth/login', [
            'email' => $this->faker->email(),
            'password' => $this->faker->password(8),
        ])->assertUnauthorized();
    }

    public function test_login_and_token_works(): void
    {
        $user = $this->buyer();

        $response = $this->postJson('api/auth/login', [
            'email' => $user->email,
            'password' => 'password', // Default in UserFactory
        ])->assertOk()
            ->assertJsonStructure([
                'token',
                'other_devices',
            ]);

        $data = json_decode($response->content(), true);

        $token = $data['token'];

        $this->withToken($token)
            ->get('api/user')
            ->assertOk()
            ->assertJsonStructure([
                'id',
                'name',
                'email',
                'role',
            ]);
    }

    public function test_login_and_logout_other_devices(): void
    {
        // This already creates a token
        $user = $this->buyer();

        $this->postJson('api/auth/login', [
            'email' => $user->email,
            'password' => 'password', // Default in UserFactory
        ])->assertOk()
            ->assertJsonStructure([
                'token',
                'other_devices',
            ])
            ->assertJson([
                'other_devices' => true,
            ]);

        // Add some more dummy tokens
        foreach (range(1, mt_rand(1, 5)) as $i) {
            $user->createToken('API');
        }

        $this->assertGreaterThanOrEqual(2, $user->tokens()->count());

        $this->withBuyerToken()
            ->post('api/auth/logout/all')
            ->assertNoContent();

        $this->assertEquals(1, $user->tokens()->count());
    }
}
