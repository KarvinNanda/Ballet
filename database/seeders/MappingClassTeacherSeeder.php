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
                'user_id' => '1'
            ],

            [
                'class_id' => '1',
                'user_id' => '4'
            ],

        ]);
    }
}
