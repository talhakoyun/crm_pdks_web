<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ShiftFollowTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $items = [
            [
                'title' => 'Giriş',
                'type' => 'in ',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'title' => 'Çıkış',
                'type' => 'out',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'title' => 'Bölgeye Giriş',
                'type' => 'zone_entry',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'title' => 'Bölgeden Çıkış',
                'type' => 'zone_exit',
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];

        DB::table('shift_follow_types')->insert($items);

    }
}
