<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MappingClassTeacherSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        DB::table('mapping_class_teachers')->insert([
            [
                'class_id' => '2',
                'teacher_id' => '1'
            ],

            [
                'class_id' => '1',
                'teacher_id' => '4'
            ],

            [
                'class_id' => '3',
                'teacher_id' => '5'
            ],

            [
                'class_id' => '4',
                'teacher_id' => '1'
            ],

            [
                'class_id' => '5',
                'teacher_id' => '4'
            ],

            [
                'class_id' => '6',
                'teacher_id' => '5'
            ],

            [
                'class_id' => '7',
                'teacher_id' => '1'
            ],

            [
                'class_id' => '8',
                'teacher_id' => '4'
            ],
        ]);
    }
}
