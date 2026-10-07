<?php

namespace Tests\Unit;

use App\Support\AttendanceRecorder;
use PHPUnit\Framework\TestCase;

class AttendanceRecorderTest extends TestCase
{
    public function test_form_arrays_become_one_row_per_student(): void
    {
        $rows = AttendanceRecorder::rowsFromRequest([
            'student_id' => ['7', '9'],
            'check' => ['on', 'off'],
            'keterangan' => ['Select...', 'Permission'],
            'notes' => ['', 'Family event'],
        ]);

        $this->assertSame([
            ['student_id' => 7, 'check' => 'on', 'keterangan' => 'Select...', 'notes' => ''],
            ['student_id' => 9, 'check' => 'off', 'keterangan' => 'Permission', 'notes' => 'Family event'],
        ], $rows);
    }

    public function test_missing_fields_get_safe_defaults(): void
    {
        $rows = AttendanceRecorder::rowsFromRequest(['student_id' => ['3']]);

        $this->assertSame([['student_id' => 3, 'check' => 'off', 'keterangan' => 'Select...', 'notes' => '']], $rows);
    }

    public function test_rows_keep_their_form_index_when_keys_are_not_sequential(): void
    {
        $rows = AttendanceRecorder::rowsFromRequest([
            'student_id' => [2 => '11', 5 => '12'],
            'check' => [2 => 'off', 5 => 'on'],
            'keterangan' => [2 => 'Sick'],
        ]);

        $this->assertSame('Sick', $rows[0]['keterangan']);
        $this->assertSame('on', $rows[1]['check']);
        $this->assertSame('Select...', $rows[1]['keterangan']);
    }

    public function test_no_students_gives_no_rows(): void
    {
        $this->assertSame([], AttendanceRecorder::rowsFromRequest([]));
    }

    public function test_message_is_plain_when_every_row_was_saved(): void
    {
        $this->assertSame('Saved', AttendanceRecorder::message('Saved', 4, 4));
    }

    public function test_message_names_skipped_rows(): void
    {
        $this->assertSame('Saved (1 row skipped)', AttendanceRecorder::message('Saved', 4, 3));
        $this->assertSame('Saved (3 rows skipped)', AttendanceRecorder::message('Saved', 4, 1));
    }
}
