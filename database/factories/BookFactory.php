<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Book>
 */
class BookFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            "title" => fake()->sentence(rand(1,5)),
            "description" => fake()->paragraph(rand(10,20)),
            "author" => fake()->name(),
            "number_of_pages"=>rand(80,2000),
            "category" => "Fantasy",
            "path" => "BlaBla",
            "cover" =>"BlaBla",
            "rating" => fake()->randomFloat(2,0,5)
        ];
    }
}
