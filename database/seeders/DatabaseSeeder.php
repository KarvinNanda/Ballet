<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(BankSeeder::class);
        $this->call(RekeningSeeder::class);
        $this->call(ClassTypeSeeder::class);
        $this->call(ClassTransactionSeeder::class);
        $this->call(StudentSeeder::class);
        // $this->call(TransactionSeeder::class);
        // $this->call(ScheduleSeeder::class);
        $this->call(StockSeeder::class);

        DB::table('users')->insert([
            [
                'name' => 'Admin',
                'email' => 'admin@gmail.com',
                'password' => bcrypt('admin123'),
                'role' => 'admin',
                'address' => 'Jl.Depan U',
                'dob' => '2002-10-01',
                'phone' => '018239222222',
                'percent' => 0,
            ],

            [
                'name' => 'Head',
                'email' => 'head@gmail.com',
                'password' => bcrypt('head123'),
                'role' => 'head',
                'address' => 'Jl.CepeSebelah',
                'dob' => '2002-05-01',
                'phone' => '018239211111',
                'percent' => 0,
            ],

            [
                'name' => 'Teacher',
                'address' => 'Jl.riau ujung',
                'role' => 'teacher',
                'dob' => '2002-03-01',
                'email' => 'teacher@gmail.com',
                'phone' => '018239210222',
                'password' => bcrypt('teacher123'),
                'percent' => 35,
            ],

            [
                'name' => 'Finance',
                'dob' => '2002-06-01',
                'address' => 'Jl.kamboja',
                'role' => 'finance',
                'phone' => '019283746574',
                'email' => 'finance@gmail.com',
                'password' => bcrypt('finance123'),
                'percent' => 0,
            ],
        ]);

        $this->call(MappingClassTeacherSeeder::class);
        // $this->call(MappingClassChildSeeder::class);
    }
}
