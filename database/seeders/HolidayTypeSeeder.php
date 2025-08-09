<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HolidayTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $holidayTypes = [
            [
                'title' => 'Yıllık İzin',
                'icon_name' => 'calendar',
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Hastalık İzni',
                'icon_name' => 'medical-bag',
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Analık İzni',
                'icon_name' => 'baby',
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Doğum İzni',
                'icon_name' => 'heart',
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Babalık İzni',
                'icon_name' => 'user',
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Evlilik İzni',
                'icon_name' => 'rings',
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Ölüm İzni',
                'icon_name' => 'flower',
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Askerlik İzni',
                'icon_name' => 'shield',
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Eğitim İzni',
                'icon_name' => 'book',
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Mazeret İzni',
                'icon_name' => 'info',
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'İdari İzin',
                'icon_name' => 'briefcase',
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Ücretsiz İzin',
                'icon_name' => 'pause',
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Emzirme İzni',
                'icon_name' => 'baby-bottle',
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Kan Bağışı İzni',
                'icon_name' => 'droplet',
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Rapor İzni',
                'icon_name' => 'file-text',
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Sınav İzni',
                'icon_name' => 'edit',
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Resmi Tatil',
                'icon_name' => 'flag',
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Hafta Sonu',
                'icon_name' => 'calendar-days',
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ];

        DB::table('holiday_type')->insert($holidayTypes);
    }
}
