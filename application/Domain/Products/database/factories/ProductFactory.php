<?php

namespace Domain\Products\database\factories;

use Domain\Products\Model\Product;
use Domain\Products\Enums\Status;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Product::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'id' => $this->faker->randomDigitNotNull(),
            'name' => $this->faker->title,
            'article' => $this->faker->slug,
            'status' => $this->faker->randomDigit() < 5 ? Status::AVAILABLE : Status::UNAVAILABLE,
            'data' => [
                'param1' => $this->faker->randomElement(),
                'param2' => $this->faker->randomElement(),
                'param3' => $this->faker->randomElement(),
            ],
            'deleted_at' => $this->faker->dateTime,
            'created_at' => $this->faker->dateTime,
            'updated_at' => $this->faker->dateTime,
        ];
    }
}
