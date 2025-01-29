<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Event>
 */
class EventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Get a random organizer (admin or user with organizer role)
        $organizer = User::where('role', 'admin')
            ->orWhere('role', 'organizer')
            ->inRandomOrder()
            ->first();

        // If no eligible organizer exists, create one with organizer role
        if (!$organizer) {
            $organizer = User::factory()->create(['role' => 'organizer']);
        }

        return [
            'title' => $this->faker->sentence(),
            'description' => $this->faker->paragraph(),
            'date' => $this->faker->date(),
            'time' => $this->faker->time(),
            'location' => $this->faker->address(),
            'image' => 'https://picsum.photos/800/600?random=' . rand(1, 1000),
            'organizer_id' => $organizer->id,
            'ticket_price' => $this->faker->randomFloat(2, 10, 100),
            'status' => $this->faker->randomElement(['pending', 'approved', 'rejected']),
            'rejection_reason' => function (array $attributes) {
                // Only add rejection reason if status is 'rejected'
                return $attributes['status'] === 'rejected' 
                    ? $this->faker->sentence() 
                    : null;
            },
        ];
    }
}
