<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Notification>
 */
class NotificationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            //
            'user_id' => User::count() ? User::inRandomOrder()->first()->id : User::factory()->create()->id,
            'message' => $this->faker->sentence(),
            'is_read' => $this->faker->boolean(),
        ];
    }
}
