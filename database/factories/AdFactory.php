<?php

namespace Database\Factories;

use App\Models\Ad;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AdFactory extends Factory
{
    protected $model = Ad::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph(),
            'price' => $this->faker->numberBetween(100, 5000),
            'category' => 'Prodaja',
            'location' => $this->faker->city(),
            'contact_info' => $this->faker->email(),
            'status' => 'pending',
        ];
    }
}
