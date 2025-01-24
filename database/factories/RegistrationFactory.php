<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Event;
use App\Models\User;
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Registration>
 */
class RegistrationFactory extends Factory
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
            'event_id' => Event::count() ? Event::inRandomOrder()->first()->id : Event::factory()->create()->id,
            'user_id' => User::count() ? User::inRandomOrder()->first()->id : User::factory()->create()->id,
            'status' => $this->faker->randomElement(['confirmed', 'pending']),

        ];
    }
}
