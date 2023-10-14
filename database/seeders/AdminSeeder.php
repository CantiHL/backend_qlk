<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run()
    {
        DB::table('users')->insert([
            'username' => 'phuong',
            'password' => Hash::make('password'),
            'email' => 'phuong@gmail.com',
            'fullname' => 'Nguyễn Đắc Phương',
            'role' => '1',
            'warehouse_id' => '1',
        ]);
    }
}
