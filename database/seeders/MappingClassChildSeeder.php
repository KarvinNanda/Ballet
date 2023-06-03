<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MappingClassChildSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('mapping_class_children')->insert([




            // == kelas 3

            [
                'class_id' => '1',
                'student_id' => '1'
            ],

            [
                'class_id' => '1',
                'student_id' => '2'
            ],
            [
                'class_id' => '1',
                'student_id' => '3'
            ],
            [
                'class_id' => '1',
                'student_id' => '4'
            ],
            [
                'class_id' => '1',
                'student_id' => '5'
            ],
            [
                'class_id' => '1',
                'student_id' => '6'
            ],
            [
                'class_id' => '1',
                'student_id' => '8'
            ],
            [
                'class_id' => '1',
                'student_id' => '10'
            ],
            [
                'class_id' => '1',
                'student_id' => '11'
            ],

            [
                'class_id' => '1',
                'student_id' => '15'
            ],

            [
                'class_id' => '2',
                'student_id' => '1'
            ],

            [
                'class_id' => '3',
                'student_id' => '1'
            ],


            // kelas 4

            [
                'class_id' => '4',
                'student_id' => '3'
            ],

            [
                'class_id' => '4',
                'student_id' => '5'
            ],

            [
                'class_id' => '4',
                'student_id' => '17'
            ],

            [
                'class_id' => '4',
                'student_id' => '18'
            ],

            [
                'class_id' => '4',
                'student_id' => '16'
            ],

            [
                'class_id' => '4',
                'student_id' => '1'
            ],

            [
                'class_id' => '4',
                'student_id' => '2'
            ],

        ]);
    }
}
