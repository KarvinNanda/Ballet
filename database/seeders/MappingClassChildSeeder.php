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
            [
                'class_id' => '2',
                'student_id' => '1'
            ],

            [
                'class_id' => '1',
                'student_id' => '1'
            ],

            [
                'class_id' => '3',
                'student_id' => '1'
            ],

            [
                'class_id' => '4',
                'student_id' => '1'
            ],

            [
                'class_id' => '5',
                'student_id' => '1'
            ],

            [
                'class_id' => '6',
                'student_id' => '1'
            ],

            [
                'class_id' => '7',
                'student_id' => '1'
            ],

            [
                'class_id' => '8',
                'student_id' => '1'
            ],

            [
                'class_id' => '2',
                'student_id' => '2'
            ],

            [
                'class_id' => '1',
                'student_id' => '2'
            ],

            [
                'class_id' => '3',
                'student_id' => '2'
            ],

            [
                'class_id' => '4',
                'student_id' => '2'
            ],

            [
                'class_id' => '5',
                'student_id' => '2'
            ],

            [
                'class_id' => '6',
                'student_id' => '2'
            ],

            [
                'class_id' => '7',
                'student_id' => '2'
            ],

            [
                'class_id' => '8',
                'student_id' => '2'
            ],
        ]);
    }
}
