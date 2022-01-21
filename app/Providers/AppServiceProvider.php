<?php

namespace App\Providers;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        JsonResource::withoutWrapping();

        $coins = [5, 10, 20, 50, 100];
        Validator::extend(
            'coin',
            fn($attribute, $value) => in_array($value, $coins),
            'Machine accepts only ' . implode(', ', $coins) . ' coins'
        );

        Validator::extend(
            'price',
            fn($attribute, $value) => $value % 5 === 0,
            'Price should be divisible by 5'
        );
    }
}
