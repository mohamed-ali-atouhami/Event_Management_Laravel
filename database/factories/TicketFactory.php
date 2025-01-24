<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Registration;
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Ticket>
 */
class TicketFactory extends Factory
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
            'registration_id' => Registration::count() ? Registration::inRandomOrder()->first()->id : Registration::factory()->create()->id,
            'qr_code' => $this->faker->uuid(),      
        ];
    }
}
