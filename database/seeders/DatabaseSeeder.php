<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            AdminUserSeeder::class,
            DivisionSeeder::class,
            ParticipantSeeder::class,
            ExampleFormSeeder::class,
        ]);
    }
}
