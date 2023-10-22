<?php

namespace Domain\Products\database\seeders;

use Domain\Products\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        Product::factory(10)->create();
    }
}
