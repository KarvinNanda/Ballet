<?php

namespace App\Support;

use App\Models\DetailAbsen;
use App\Models\HeaderAbsen;
use App\Models\Schedule;
use App\Models\Student;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;

/**
 * Saves the attendance form of one schedule. Rows are matched to students by id (NIS is nullable and not
 * unique), only students of the schedule's class count. A student recorded for the first time on this schedule must pass the payment
 * gate and uses one session (Quota + 1, whatever the description). Editing an existing row changes only
 * Description/Notes.
 */
class AttendanceRecorder
{
    /**
     * @param list<array{student_id: int, check: string, keterangan: string, notes: string}> $rows
     * @return int rows saved; count($rows) minus this is the number skipped (outsider or payment gate)
     */
    public static function record(Schedule $schedule, array $rows, ?int $teacherId, bool $allowEdit): int
    {
        $students = Student::whereIn('id', array_column($rows, 'student_id'))
            ->whereIn('id', DB::table('mapping_class_children')->where('class_id', $schedule->class_id)->pluck('student_id'))
            ->get()
            ->keyBy('id');

        $className = (string) DB::table('class_transactions as ct')
            ->join('class_types as t', 't.id', 'ct.class_type_id')
            ->where('ct.id', $schedule->class_id)
            ->value('t.class_name');

        return DB::transaction(function () use ($schedule, $rows, $teacherId, $allowEdit, $students, $className) {
            $header = HeaderAbsen::where('schedules_id', $schedule->id)->lockForUpdate()->first();
            if ($header !== null && ! $allowEdit) {
                return 0;
            }
            if ($header === null) {
                try {
                    $header = HeaderAbsen::create(['schedules_id' => $schedule->id, 'teacher_id' => $teacherId]);
                } catch (UniqueConstraintViolationException) {
                    // Another request recorded this schedule a moment ago (unique index on schedules_id).
                    if (! $allowEdit) {
                        return 0;
                    }
                    $header = HeaderAbsen::where('schedules_id', $schedule->id)->lockForUpdate()->firstOrFail();
                }
            }

            $saved = 0;
            foreach ($rows as $row) {
                $student = $students->get($row['student_id']);
                if ($student === null) {
                    continue;
                }

                // detail_absens has no primary key: find and update the row by (header, student), not through a model.
                $detail = DetailAbsen::where('header_absen_id', $header->id)->where('student_id', $student->id);
                $isNew = ! $detail->exists();
                if ($isNew && AttendancePaymentGate::requiresPayment($student, $schedule->date, $className)) {
                    continue;
                }

                $note = $row['keterangan'] ?: 'Select...';
                $values = [
                    'Description' => $row['check'] === 'on' ? 'Attend' : ($note === 'Select...' ? 'Absent' : $note),
                    'Notes' => $note === 'Permission' ? $row['notes'] : '',
                ];

                if ($isNew) {
                    DetailAbsen::create($values + ['header_absen_id' => $header->id, 'student_id' => $student->id]);
                    DB::table('students')->where('id', $student->id)->increment('Quota');
                } else {
                    $detail->update($values);
                }
                $saved++;
            }

            return $saved;
        });
    }

    /** Form arrays (student_id[], check[], keterangan[], notes[]) → rows. */
    public static function rowsFromRequest(array $input): array
    {
        $ids = (array) ($input['student_id'] ?? []);

        return array_map(fn ($i, $id) => [
            'student_id' => (int) $id,
            'check' => $input['check'][$i] ?? 'off',
            'keterangan' => $input['keterangan'][$i] ?? 'Select...',
            'notes' => (string) ($input['notes'][$i] ?? ''),
        ], array_keys($ids), $ids);
    }

    /** Success flash; names how many submitted rows were not saved so a skip is never silent. */
    public static function message(string $success, int $submitted, int $saved): string
    {
        $skipped = $submitted - $saved;

        return $skipped > 0 ? sprintf('%s (%d %s skipped)', $success, $skipped, $skipped === 1 ? 'row' : 'rows') : $success;
    }
}
