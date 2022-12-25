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
                'date' => '2022-12-21',
                'time' => '07:00'
            ],

            [
                'class_id' => '2',
                'date' => '2022-12-22',
                'time' => '08:00'
            ],

            [
                'class_id' => '3',
                'date' => '2022-12-23',
                'time' => '09:00'
            ],

            [
                'class_id' => '4',
                'date' => '2022-12-24',
                'time' => '10:00'
            ],

            [
                'class_id' => '5',
                'date' => '2022-12-25',
                'time' => '11:00'
            ],

            [
                'class_id' => '6',
                'date' => '2022-12-26',
                'time' => '12:00'
            ],

            [
                'class_id' => '6',
                'date' => '2022-12-27',
                'time' => '13:00'
            ],
        ]);
    }
}
