<?php

namespace Domain\Products\database\seeders;

use Illuminate\Database\Seeder;
use Domain\Products\Model\Product;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        Product::factory(10)->create();
    }
}