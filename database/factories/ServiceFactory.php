<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ServiceFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);

        return [
            'category_id' => Category::factory(),
            'business_id' => fn (array $attributes) => Category::findOrFail($attributes['category_id'])->business_id,
            'name' => Str::title($name),
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(100, 9999),
            'description' => fake()->sentence(),
            'price' => fake()->randomFloat(2, 25, 250),
            'duration_minutes' => fake()->randomElement([30, 45, 60, 90]),
            'is_active' => true,
            'sort_order' => 0,
        ];
    }
}
