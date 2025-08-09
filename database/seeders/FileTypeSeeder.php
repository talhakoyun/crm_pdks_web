<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FileTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $now = Carbon::now();

        $items = [
            [
                'name' => 'Kimlik Belgesi',
                'allowed_extensions' => 'pdf,jpg,jpeg,png',
                'description' => 'Kimlik belgesi dosyaları (PDF, JPG, JPEG, PNG)',
                'is_active' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Sözleşme',
                'allowed_extensions' => 'pdf,docx',
                'description' => 'Sözleşme dosyaları (PDF, DOCX)',
                'is_active' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Sertifika',
                'allowed_extensions' => 'pdf,jpg,jpeg,png',
                'description' => 'Sertifika dosyaları (PDF, JPG, JPEG, PNG)',
                'is_active' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Diğer Belgeler',
                'allowed_extensions' => 'pdf,docx,jpg,jpeg,png,xlsx',
                'description' => 'Diğer belgeler (PDF, DOCX, JPG, JPEG, PNG, XLSX)',
                'is_active' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        DB::table('file_types')->insert($items);
    }
}
