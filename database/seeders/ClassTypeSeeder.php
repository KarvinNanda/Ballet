<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ClassTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('class_types')->insert([
            [
                'class_name' => 'Baby Class',
                'class_price' => 390000,
            ],

            [
                'class_name' => 'Pre School',
                'class_price' => 390000,
            ],

            [
                'class_name' => 'Pre Primary',
                'class_price' => 400000,
            ],

            [
                'class_name' => 'Primary',
                'class_price' => 410000,
            ],

            [
                'class_name' => 'Grade 1',
                'class_price' => 420000,
            ],

            [
                'class_name' => 'Grade 2',
                'class_price' => 430000,
            ],

            [
                'class_name' => 'Grade 3',
                'class_price' => 440000,
            ],

            [
                'class_name' => 'Grade 4',
                'class_price' => 450000,
            ],

            [
                'class_name' => 'Grade 5',
                'class_price' => 460000,
            ],

            [
                'class_name' => 'Intermediate Foundation',
                'class_price' => 490000,
            ],

            [
                'class_name' => 'Intermediate',
                'class_price' => 535000,
            ],

            [
                'class_name' => 'Advance Foundation',
                'class_price' => 585000,
            ],

            [
                'class_name' => 'Advance 1',
                'class_price' => 615000,
            ],

            [
                'class_name' => 'Advance 2',
                'class_price' => 645000,
            ],

            [
                'class_name' => 'Intensive Class',
                'class_price' => 1200000,
            ],

            [
                'class_name' => 'Intensive Kids',
                'class_price' => 1200000,
            ],

            [
                'class_name' => 'Pointe Class',
                'class_price' => 25000,
            ],


        ]);
    }
}
