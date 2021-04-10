<?php

namespace Database\Factories;

use App\Models\Buyer;
use App\Models\Coop;
use App\Models\Purchase;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PurchaseFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Purchase::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'coop_id'                => Coop::factory(),
            'buyer_id'               => Buyer::factory(),
            'amount'                 => $this->faker->randomFloat(2, 1000, 1000000),
            'package_quantity'       => $this->faker->randomNumber(),
            'package_id'             => $this->faker->randomNumber(),
            'banking_customer_token' => Str::random(30),
            'coop_canceled'          => false,
        ];
    }
}
