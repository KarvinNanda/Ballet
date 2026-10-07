<?php

namespace App\Http\Controllers\teacher\Concerns;

use App\Models\Schedule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/** A teacher may only act on classes mapped to them in mapping_class_teachers. Anything else is 403. */
trait AuthorizesTeacherClasses
{
    protected function authorizeClass(mixed $classId): void
    {
        $teaches = DB::table('mapping_class_teachers')
            ->where('class_id', $classId)
            ->where('user_id', Auth::id())
            ->exists();

        abort_unless($teaches, 403);
    }

    protected function authorizeSchedule(mixed $scheduleId): Schedule
    {
        $schedule = Schedule::findOrFail($scheduleId);
        $this->authorizeClass($schedule->class_id);

        return $schedule;
    }

    protected function authorizeSelf(mixed $userId): void
    {
        abort_unless((int) $userId === Auth::id(), 403);
    }
}
