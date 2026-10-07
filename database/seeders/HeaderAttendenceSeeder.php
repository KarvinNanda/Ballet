<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/** Attendance header for every past schedule, taken by the class teacher. Today and later stay open. */
class HeaderAttendenceSeeder extends Seeder
{
    public function run()
    {
        $pastSchedules = DB::table('schedules as s')
            ->join('mapping_class_teachers as m', 'm.class_id', 's.class_id')
            ->where('s.date', '<', now('Asia/Jakarta')->startOfDay())
            ->orderBy('s.id')
            ->get(['s.id', 'm.user_id']);

        foreach ($pastSchedules as $schedule) {
            DB::table('header_absens')->insert([
                'schedules_id' => $schedule->id,
                'teacher_id' => $schedule->user_id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
