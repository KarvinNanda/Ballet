<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KelasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //


        DB::table('kelas')->insert([
            [
                'nama_kelas' => 'Baby Class'
            ],

            [
                'nama_kelas' => 'Pre School'
            ],

            [
                'nama_kelas' => 'Pre Primary'
            ],

            [
                'nama_kelas' => 'Primary'
            ],

            [
                'nama_kelas' => 'Grace 1'
            ],

            [
                'nama_kelas' => 'Grace 2'
            ],

            [
                'nama_kelas' => 'Grace 3'
            ],

            [
                'nama_kelas' => 'Grace 4'
            ],

            [
                'nama_kelas' => 'Grace 5'
            ],

            [
                'nama_kelas' => 'Intermediate Foundation'
            ],

            [
                'nama_kelas' => 'Intermediate'
            ],

            [
                'nama_kelas' => 'Advance Foundation'
            ],

            [
                'nama_kelas' => 'Advance 1'
            ],

            [
                'nama_kelas' => 'Advance 2'
            ],

            [
                'nama_kelas' => 'Intensive Class'
            ],

            [
                'nama_kelas' => 'Intensive Kids'
            ],

            [
                'nama_kelas' => 'Pointe Class'
            ],


        ]);
    }
}
