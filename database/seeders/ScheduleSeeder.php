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
                'date' => '2023-04-12',
            ],

            [
                'class_id' => '2',
                'date' => '2023-04-11',
            ],

        ]);
    }
}
