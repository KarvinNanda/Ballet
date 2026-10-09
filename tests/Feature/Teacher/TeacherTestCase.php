<?php

namespace Tests\Feature\Teacher;

use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Tests\Feature\Staff\StaffTestCase;

/**
 * Teacher pages. Every test builds its own teacher, class, students and sessions and freezes the clock,
 * so the seed (dated from the day it ran) and the real clock never decide a result.
 * Inherits rowFor(), formFields() and expectedClassLabel() from StaffTestCase.
 */
abstract class TeacherTestCase extends StaffTestCase
{
    /** Freeze the clock at a Jakarta wall-clock time ("2026-10-09 10:00:00"); the app clock itself is UTC. */
    protected function at(string $jakarta): void
    {
        $this->travelTo(Carbon::parse($jakarta, 'Asia/Jakarta'));
    }

    protected function newTeacher(string $name = 'Window Teacher'): User
    {
        return User::factory()->create(['role' => 'teacher', 'name' => $name]);
    }

    /** Log in as $teacher; safe to call repeatedly in one test (same reason as StaffTestCase::asRole()). */
    protected function asTeacher(User $teacher): static
    {
        $this->flushSession();
        $this->app['auth']->forgetGuards();

        return $this->actingAs($teacher);
    }

    /** A class of $course taught by $teacher with $active active students; returns the class id. */
    protected function classFor(User $teacher, string $course = 'Grade 1', int $active = 2, string $status = 'aktif'): int
    {
        $typeId = DB::table('class_types')->where('class_name', $course)->value('id');
        $this->assertNotNull($typeId, "seed has no course {$course}");

        $classId = DB::table('class_transactions')->insertGetId([
            'class_type_id' => $typeId, 'Status' => $status, 'is_freeze' => 0, 'class_transaction_price' => 100000,
            'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('mapping_class_teachers')->insert([
            'class_id' => $classId, 'user_id' => $teacher->id, 'created_at' => now(), 'updated_at' => now(),
        ]);
        foreach (Student::factory()->count($active)->create() as $student) {
            $this->enrol($classId, $student);
        }

        return $classId;
    }

    /** $classQuota is mapping_class_children.quota (the class max the staff class detail shows; 0 = course default). */
    protected function enrol(int $classId, Student $student, int $classQuota = 0): void
    {
        DB::table('mapping_class_children')->insert([
            'class_id' => $classId, 'student_id' => $student->id, 'quota' => $classQuota,
            'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    /** A session at a Jakarta wall-clock time, stored the way schedules.date stores it (no zone). */
    protected function sessionAt(int $classId, string $jakarta): int
    {
        return DB::table('schedules')->insertGetId([
            'class_id' => $classId, 'date' => $jakarta, 'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    /** @param array<int, array{0: ?string, 1: ?string}> $rows student id => [Description, Notes], raw as older data has them */
    protected function recordRaw(int $scheduleId, array $rows = []): int
    {
        $header = DB::table('header_absens')->insertGetId([
            'schedules_id' => $scheduleId, 'teacher_id' => null, 'created_at' => now(), 'updated_at' => now(),
        ]);
        foreach ($rows as $studentId => [$description, $notes]) {
            DB::table('detail_absens')->insert([
                'header_absen_id' => $header, 'student_id' => $studentId, 'Description' => $description, 'Notes' => $notes,
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }

        return $header;
    }

    /** Active students of a class, by id (the order the attendance form lists them). */
    protected function studentsOf(int $classId): Collection
    {
        return Student::whereIn('id', DB::table('mapping_class_children')->where('class_id', $classId)->pluck('student_id'))
            ->where('Status', 'aktif')
            ->orderBy('id')
            ->get();
    }

    /** A valid getAbsen payload: every active student present. */
    protected function everyonePresent(int $classId, string $returnUrl): array
    {
        $ids = $this->studentsOf($classId)->pluck('id')->all();

        return [
            'student_id' => $ids,
            'check' => array_fill(0, count($ids), 'on'),
            'keterangan' => array_fill(0, count($ids), ''),
            'notes' => array_fill(0, count($ids), ''),
            'return_url' => $returnUrl,
        ];
    }
}
