<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class RoleSeeder extends Seeder
{

    public function run()
    {
        DB::table('roles')->insert([
            ['name' => 'Admin', 'description' => 'Quản trị hệ thống'],
            ['name' => 'Seller', 'description' => 'Người bán hàng'],
            ['name' => 'Customer', 'description' => 'Khách hàng'],
        ]);
    }
}
