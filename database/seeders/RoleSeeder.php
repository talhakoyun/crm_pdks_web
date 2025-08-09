<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $items = [
            [
                'name' => 'Süper Admin',
                'slug' => 'super-admin',
                'permissions' => json_encode(
                    [
                        "backend.role_list",
                        "backend.role_form",
                        "backend.role_save",
                        "backend.role_delete",
                        "backend.user_list",
                        "backend.user_form",
                        "backend.user_save",
                        "backend.user_delete",
                        "backend.menu_list",
                        "backend.menu_form",
                        "backend.menu_save",
                        "backend.menu_delete",
                        "backend.company_list",
                        "backend.company_form",
                        "backend.company_save",
                        "backend.company_delete",
                    ]
                ),
            ],
            [
                'name' => 'Admin',
                'slug' => 'admin',
                'permissions' => json_encode(
                    [
                        "backend.user_list",
                        "backend.user_form",
                        "backend.user_save",
                        "backend.user_delete",
                        "backend.branch_list",
                        "backend.branch_form",
                        "backend.branch_save",
                        "backend.branch_delete",
                        "backend.department_list",
                        "backend.department_form",
                        "backend.department_save",
                        "backend.department_delete",
                        "backend.shift_definition_list",
                        "backend.shift_definition_form",
                        "backend.shift_definition_save",
                        "backend.shift_definition_delete",
                    ]
                ),
            ],
            [
                'name' => 'Şirket Sahibi',
                'slug' => 'company-owner',
                'permissions' => json_encode(
                    [
                        "backend.user_list",
                        "backend.user_form",
                        "backend.user_save",
                        "backend.user_delete",
                        "backend.branch_list",
                        "backend.branch_form",
                        "backend.branch_save",
                        "backend.branch_delete",
                        "backend.department_list",
                        "backend.department_form",
                        "backend.department_save",
                        "backend.department_delete",
                    ]
                ),
            ],
            [
                'name' => 'Şirket Yetkilisi',
                'slug' => 'company-admin',
                'permissions' => json_encode(
                    [
                        "backend.user_list",
                        "backend.user_form",
                        "backend.user_save",
                        "backend.user_delete",
                        "backend.branch_list",
                        "backend.branch_form",
                        "backend.branch_save",
                        "backend.branch_delete",
                        "backend.department_list",
                        "backend.department_form",
                        "backend.department_save",
                        "backend.department_delete",
                    ]
                ),
            ],
            [
                'name' => 'Şube Yetkilisi',
                'slug' => 'branch-admin',
                'permissions' => json_encode(
                    [
                        "backend.user_list",
                        "backend.user_form",
                        "backend.user_save",
                        "backend.user_delete",
                        "backend.department_list",
                        "backend.department_form",
                        "backend.department_save",
                        "backend.department_delete",
                    ]
                ),
            ],
            [
                'name' => 'Departman Yetkilisi',
                'slug' => 'department-admin',
                'permissions' => json_encode(
                    [
                        "backend.user_list",
                        "backend.user_form",
                        "backend.user_save",
                        "backend.user_delete",
                    ]
                ),
            ],
            [
                'name' => 'Personel',
                'slug' => 'personnel',
                'permissions' => json_encode([]),
            ],
        ];

        DB::table('roles')->insert($items);
    }
}
