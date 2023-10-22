<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Domain\Products\Models\Product;
use Domain\Users\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        //        Product::factory(10)->create();

        User::factory(20)->create();
        //        User::factory()->create([
        //            'name' => fake()->name,
        //            'email' => fake()->email,
        //            'password' => hash('sha256', '0000'),
        //        ]);
    }
}
