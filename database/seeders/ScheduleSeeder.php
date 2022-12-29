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
                'date' => '2023-01-01',
            ],

            [
                'class_id' => '2',
                'date' => '2023-01-02',
            ],

            [
                'class_id' => '3',
                'date' => '2023-01-03',
            ],

            [
                'class_id' => '4',
                'date' => '2023-01-04',
            ],

            [
                'class_id' => '5',
                'date' => '2023-01-05',
            ],

            [
                'class_id' => '6',
                'date' => '2023-01-06',
            ],

            [
                'class_id' => '6',
                'date' => '2023-01-07',
            ],
        ]);
    }
}
