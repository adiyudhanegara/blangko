<?php

namespace Database\Seeders;

use App\Models\Division;
use App\Models\Participant;
use Illuminate\Database\Seeder;

class ParticipantSeeder extends Seeder
{
    public function run(): void
    {
        $divisions = Division::all()->keyBy('slug');

        $participants = [
            // Engineering
            ['name' => 'Budi Santoso',  'email' => 'budi@example.com',    'phone' => '+628111000001', 'division' => 'engineering'],
            ['name' => 'Sari Dewi',     'email' => 'sari@example.com',     'phone' => '+628111000002', 'division' => 'engineering'],
            ['name' => 'Andi Pratama',  'email' => 'andi@example.com',     'phone' => '+628111000003', 'division' => 'engineering'],
            ['name' => 'Rina Wahyu',    'email' => 'rina@example.com',     'phone' => '+628111000004', 'division' => 'engineering'],
            ['name' => 'Doni Kurnia',   'email' => 'doni@example.com',     'phone' => '+628111000005', 'division' => 'engineering'],
            // Marketing
            ['name' => 'Maya Sari',     'email' => 'maya@example.com',     'phone' => '+628111000006', 'division' => 'marketing'],
            ['name' => 'Fajar Rahman',  'email' => 'fajar@example.com',    'phone' => '+628111000007', 'division' => 'marketing'],
            ['name' => 'Citra Lestari', 'email' => 'citra@example.com',    'phone' => '+628111000008', 'division' => 'marketing'],
            ['name' => 'Hendra Putra',  'email' => 'hendra@example.com',   'phone' => '+628111000009', 'division' => 'marketing'],
            ['name' => 'Nita Anggraini','email' => 'nita@example.com',     'phone' => '+628111000010', 'division' => 'marketing'],
            // Operations
            ['name' => 'Agus Susanto',  'email' => 'agus@example.com',     'phone' => '+628111000011', 'division' => 'operations'],
            ['name' => 'Dewi Rahayu',   'email' => 'dewi@example.com',     'phone' => '+628111000012', 'division' => 'operations'],
            ['name' => 'Irfan Maulana', 'email' => 'irfan@example.com',    'phone' => '+628111000013', 'division' => 'operations'],
            ['name' => 'Siti Nurhaliza','email' => 'siti@example.com',     'phone' => '+628111000014', 'division' => 'operations'],
            ['name' => 'Rizky Fauzan',  'email' => 'rizky@example.com',    'phone' => '+628111000015', 'division' => 'operations'],
        ];

        foreach ($participants as $data) {
            $division = $divisions->get($data['division']);
            Participant::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name'        => $data['name'],
                    'phone'       => $data['phone'],
                    'division_id' => $division?->id,
                    'status'      => 'active',
                ]
            );
        }
    }
}
