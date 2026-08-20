<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class BusinessFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->company();

        return [
            'name' => $name,
            'slug' => fake()->unique()->slug(2),
            'phone' => fake()->phoneNumber(),
            'email' => fake()->companyEmail(),
            'address' => fake()->address(),
            'description' => fake()->paragraph(),
            'timezone' => 'Asia/Kuala_Lumpur',
            'booking_interval_minutes' => 30,
            'is_active' => true,
        ];
    }
}
