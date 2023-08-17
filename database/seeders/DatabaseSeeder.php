<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Teacher;
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
        $this->call(TransactionSeeder::class);
        $this->call(ScheduleSeeder::class);
        $this->call(StockSeeder::class);

        DB::table('users')->insert([
            [
                'name' => 'Saminjo',
                'email' => 'saminjo@gmail.com',
                'password' => bcrypt('saminjo123'),
                'role' => 'teacher',
                'address' => 'Jl.Kasih Selamat',
                'dob' => '2002-12-01',
                'phone' => '018239233333',
                'percent' => 35,
            ],

            [
                'name' => 'karvin',
                'email' => 'karvin@gmail.com',
                'password' => bcrypt('karvin123'),
                'role' => 'admin',
                'address' => 'Jl.Depan U',
                'dob' => '2002-10-01',
                'phone' => '018239222222',
                'percent' => 0,
            ],

            [
                'name' => 'jose',
                'email' => 'jose@gmail.com',
                'password' => bcrypt('jose123'),
                'role' => 'head',
                'address' => 'Jl.CepeSebelah',
                'dob' => '2002-05-01',
                'phone' => '018239211111',
                'percent' => 0,
            ],

            [
                'name' => 'Carolyn Tanujaya',
                'address' => 'Jl.riau ujung',
                'role' => 'teacher',
                'dob' => '2002-03-01',
                'email' => 'cete@gmail.com',
                'phone' => '018239210222',
                'password' => bcrypt('cete123'),
                'percent' => 35,
            ],

            [
                'name' => 'Nabila',
                'dob' => '2002-05-01',
                'address' => 'Jl.sudriamn',
                'role' => 'teacher',
                'phone' => '088281239283',
                'email' => 'ana@gmail.com',
                'password' => bcrypt('ana123'),
                'percent' => 35,
            ],

            [
                'name' => 'Felix',
                'dob' => '2002-06-01',
                'address' => 'Jl.kamboja',
                'role' => 'finance',
                'phone' => '019283746574',
                'email' => 'felix@gmail.com',
                'password' => bcrypt('felix123'),
                'percent' => 0,
            ],
        ]);

        $this->call(MappingClassTeacherSeeder::class);
        $this->call(MappingClassChildSeeder::class);
    }
}
