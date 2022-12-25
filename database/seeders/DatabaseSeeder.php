<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\ClassTransaction;
use App\Models\DetailAbsen;
use App\Models\HeaderAbsen;
use App\Models\MappingClassChild;
use App\Models\MappingClassTeacher;
use App\Models\Schedule;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\Transaction;
use App\Models\User;
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
        $this->call(ClassTransactionSeeder::class);
        $this->call(StudentSeeder::class);
        $this->call(TransactionSeeder::class);
        $this->call(ScheduleSeeder::class);


        DB::table('users')->insert([
            [
                'name' => 'saminjo',
                'email' => 'saminjo@gmail.com',
                'password' => bcrypt('saminjo123'),
                'role' => 'teacher',
                'address' => 'Jl.Kasih Selamat',
                'dob' => '2002-12-01',
                'phone' => '018239233333',
            ],

            [
                'name' => 'karvin',
                'email' => 'karvin@gmail.com',
                'password' => bcrypt('karvin123'),
                'role' => 'admin',
                'address' => 'Jl.Depan U',
                'dob' => '2002-10-01',
                'phone' => '018239222222',
            ],

            [
                'name' => 'jose',
                'email' => 'jose@gmail.com',
                'password' => bcrypt('jose123'),
                'role' => 'head',
                'address' => 'Jl.CepeSebelah',
                'dob' => '2002-05-01',
                'phone' => '018239211111',
            ],

            [
                'name' => 'CT',
                'address' => 'Jl.riau ujung',
                'role' => 'teacher',
                'dob' => '2002-03-01',
                'email' => 'cete@gmail.com',
                'phone' => '018239210222',
                'password' => bcrypt('cete123'),
            ],

            [
                'name' => 'NA',
                'dob' => '2002-05-01',
                'address' => 'Jl.sudriamn',
                'role' => 'teacher',
                'phone' => '088281239283',
                'email' => 'ana@gmail.com',
                'password' => bcrypt('ana123'),
            ]
        ]);
        $this->call(MappingClassTeacherSeeder::class);
    }
}
