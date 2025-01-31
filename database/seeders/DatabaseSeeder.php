<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Database\Seeders\UserSeeder;
use Database\Seeders\CategorySeeder;
use Database\Seeders\EventSeeder;
use Database\Seeders\RegistrationSeeder;
use Database\Seeders\PaymentSeeder;
use Database\Seeders\ReviewSeeder;
use Database\Seeders\TicketSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
        $this->call([
            UserSeeder::class,
            CategorySeeder::class,
            EventSeeder::class,
            RegistrationSeeder::class,
            PaymentSeeder::class,
            ReviewSeeder::class,
            TicketSeeder::class,
        ]);
    }
}
