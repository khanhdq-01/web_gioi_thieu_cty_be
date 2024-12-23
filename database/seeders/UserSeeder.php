<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Schema::disableForeignKeyConstraints();
        // User::truncate();
        // Schema::enableForeignKeyConstraints();

        // $data =[
        //     'waitress', 'chef', 'cashier', 'manager'
        // ];

        //     User::insert([
        //         'name' => 'manager',
        //         'email' => 'manager@email.com',
        //         'password'=> Hash::make('123'),
        //         'role_id' => 4
        //     ]);
        
        // khac manager thi pass chung la 124
        
    }
}
