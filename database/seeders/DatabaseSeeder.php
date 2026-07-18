<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    private const DEMO_EMAIL = 'ogukahjoy@gmail.com';
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(CategorySeeder::class);
        $email = env('DEMO_EMAIL', self::DEMO_EMAIL);

        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => $email,
            'password' => 'iHbC4zb9a@pp7'
        ]);
        $this->call(DemoDataSeeder::class);
    }
}
