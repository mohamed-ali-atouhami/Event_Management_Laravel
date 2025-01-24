<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use App\Models\Event;
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Payment>
 */
class PaymentFactory extends Factory
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
            'event_id' => Event::count() ? Event::inRandomOrder()->first()->id : Event::factory()->create()->id,
            'amount' => $this->faker->randomFloat(2, 10, 100),
            'status' => $this->faker->randomElement(['completed', 'pending']),
        ];
    }
}
