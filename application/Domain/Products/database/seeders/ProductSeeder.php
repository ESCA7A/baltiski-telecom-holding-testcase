<?php

namespace Domain\Products\database\seeders;

use Illuminate\Database\Seeder;
use Domain\Products\Models\Product;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        Product::factory(10)->create();
    }
}