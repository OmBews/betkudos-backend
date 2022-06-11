<?php

namespace Database\Factories\Currencies;

use App\Models\Currencies\CryptoCurrency;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CryptoCurrencyFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = CryptoCurrency::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'ticker' => Str::random(3),
            'name' => $name = $this->faker->name,
            'icon' => "assets/img/currencies/{$name}.png",
        ];
    }
}
