<?php

namespace Database\Seeders;

use App\Models\Route;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RouteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
      $items = [
            [
                'category_name' => 'Kullanıcılar',
                'route_name' => 'backend.user_list',
                'name' => 'Listeleme',
                'is_active' => 1,
            ],
            [
                'category_name' => 'Kullanıcılar',
                'route_name' => 'backend.user_form',
                'name' => 'Form',
                'is_active' => 1,
            ],
            [
                'category_name' => 'Kullanıcılar',
                'route_name' => 'backend.user_save',
                'name' => 'Kaydetme',
                'is_active' => 1,
            ],
            [
                'category_name' => 'Kullanıcılar',
                'route_name' => 'backend.user_delete',
                'name' => 'Silme',
                'is_active' => 1,
            ],
            [
                'category_name' => 'Routelar',
                'route_name' => 'backend.route_list',
                'name' => 'Listeleme',
                'is_active' => 1,
            ],
            [
                'category_name' => 'Routelar',
                'route_name' => 'backend.route_form',
                'name' => 'Form',
                'is_active' => 1,
            ],
            [
                'category_name' => 'Routelar',
                'route_name' => 'backend.route_save',
                'name' => 'Kaydetme',
                'is_active' => 1,
            ],
            [
                'category_name' => 'Routelar',
                'route_name' => 'backend.route_delete',
                'name' => 'Silme',
                'is_active' => 1,
            ],
            // Dosya Tipleri
            [
                'category_name' => 'Dosya Tipleri',
                'route_name' => 'file_type',
                'name' => 'Menü',
                'is_active' => 1,
            ],
            [
                'category_name' => 'Dosya Tipleri',
                'route_name' => 'backend.file_type_list',
                'name' => 'Listeleme',
                'is_active' => 1,
            ],
            [
                'category_name' => 'Dosya Tipleri',
                'route_name' => 'backend.file_type_form',
                'name' => 'Form',
                'is_active' => 1,
            ],
            [
                'category_name' => 'Dosya Tipleri',
                'route_name' => 'backend.file_type_save',
                'name' => 'Kaydetme',
                'is_active' => 1,
            ],
            [
                'category_name' => 'Dosya Tipleri',
                'route_name' => 'backend.file_type_delete',
                'name' => 'Silme',
                'is_active' => 1,
            ],
            // Personel Dosyaları
            [
                'category_name' => 'Personel Dosyaları',
                'route_name' => 'user_file',
                'name' => 'Menü',
                'is_active' => 1,
            ],
            [
                'category_name' => 'Personel Dosyaları',
                'route_name' => 'backend.user_file_list',
                'name' => 'Listeleme',
                'is_active' => 1,
            ],
            [
                'category_name' => 'Personel Dosyaları',
                'route_name' => 'backend.user_file_form',
                'name' => 'Form',
                'is_active' => 1,
            ],
            [
                'category_name' => 'Personel Dosyaları',
                'route_name' => 'backend.user_file_save',
                'name' => 'Kaydetme',
                'is_active' => 1,
            ],
            [
                'category_name' => 'Personel Dosyaları',
                'route_name' => 'backend.user_file_delete',
                'name' => 'Silme',
                'is_active' => 1,
            ],
            [
                'category_name' => 'Personel Dosyaları',
                'route_name' => 'backend.user_file_upload_temp',
                'name' => 'Geçici Yükleme',
                'is_active' => 1,
            ],
            [
                'category_name' => 'Personel Dosyaları',
                'route_name' => 'backend.user_file_delete_temp',
                'name' => 'Geçici Silme',
                'is_active' => 1,
            ],

      ];

        DB::table('routes')->insert($items);
    }
}
