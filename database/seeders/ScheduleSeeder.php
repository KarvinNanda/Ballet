<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ScheduleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('schedules')->insert([
            [
                'class_id' => '1',
                'date' => '2023-06-16',
            ],

            [
                'class_id' => '3',
                'date' => '2023-06-07',
            ],

            [
                'class_id' => '4',
                'date' => '2023-06-06',
            ],

            [
                'class_id' => '3',
                'date' => '2023-06-13',
            ],

            [
                'class_id' => '3',
                'date' => '2023-06-10',
            ],

            [
                'class_id' => '1',
                'date' => '2023-06-07',
            ],

            [
                'class_id' => '4',
                'date' => '2023-06-05',
            ],

            [
                'class_id' => '3',
                'date' => '2023-06-08',
            ],

            [
                'class_id' => '1',
                'date' => '2023-06-12',
            ],

            [
                'class_id' => '3',
                'date' => '2023-06-11',
            ],

        ]);
    }
}
