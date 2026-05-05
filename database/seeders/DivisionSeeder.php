<?php

namespace Database\Seeders;

use App\Models\Division;
use Illuminate\Database\Seeder;

class DivisionSeeder extends Seeder
{
    public function run(): void
    {
        $divisions = [
            ['name' => 'Engineering', 'slug' => 'engineering', 'description' => 'Software engineering team'],
            ['name' => 'Marketing', 'slug' => 'marketing', 'description' => 'Marketing and growth team'],
            ['name' => 'Operations', 'slug' => 'operations', 'description' => 'Operations and support team'],
        ];

        foreach ($divisions as $data) {
            Division::firstOrCreate(['slug' => $data['slug']], $data);
        }
    }
}
