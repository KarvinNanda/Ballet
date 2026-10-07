<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Dates are relative to the day the seed runs (WIB), so "today" always has classes to take attendance for.
 * Running classes: 3 past weeks, today, next week. Non-active and frozen classes: past weeks only.
 * The teacher dashboard shows the Attendance button only after the class start time,
 * so today's classes start one hour before the seed runs (16:00 for the other days).
 */
class ScheduleSeeder extends Seeder
{
    private const RUNNING_OFFSETS = [-21, -14, -7, 0, 7];
    private const STOPPED_OFFSETS = [-28, -21];

    public function run()
    {
        $today = now('Asia/Jakarta')->startOfDay();
        $todayStart = now('Asia/Jakarta')->subHour()->startOfHour()->max($today);
        $classes = DB::table('class_transactions')->orderBy('id')->get(['id', 'Status', 'is_freeze']);

        foreach ($classes as $class) {
            $running = $class->Status === 'aktif' && (int) $class->is_freeze === 0;

            foreach ($running ? self::RUNNING_OFFSETS : self::STOPPED_OFFSETS as $days) {
                DB::table('schedules')->insert([
                    'class_id' => $class->id,
                    'date' => $days === 0 ? $todayStart : $today->copy()->addDays($days)->setTime(16, 0),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
