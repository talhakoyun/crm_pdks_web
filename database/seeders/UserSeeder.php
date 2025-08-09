<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $items = [[
            'name' => 'Sinem',
            'surname' => 'Koyun',
            'tc' => '11111111111',
            'title' => 'Süper Admin',
            'role_id' => 1,
            'phone' => '0(111)111 11 11',
            'email' => 's.koyun@bilmos.com.tr',
            'password' => Hash::make('123123'),
            'is_active' => 1,
        ]];

        DB::table('users')->insert($items);
    }
}
