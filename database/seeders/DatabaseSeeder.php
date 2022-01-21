<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        if (!Role::exists()) {
            Role::create(['name' => 'Seller']);
            Role::create(['name' => 'Buyer']);
        }

        if (!User::exists()) {
            User::create([
                'name' => 'MVP Seller',
                'email' => 'seller@mvpfactory.co',
                'password' => bcrypt('seller123'),
            ])->assignRole('Seller');

            User::create([
                'name' => 'MVP Buyer',
                'email' => 'buyer@mvpfactory.co',
                'password' => bcrypt('buyer123'),
            ])->assignRole('Buyer');
        }
    }
}
