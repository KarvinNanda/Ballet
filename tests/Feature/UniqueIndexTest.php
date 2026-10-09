<?php

namespace Tests\Feature;

use App\Support\DuplicateGuard;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Tests\TestCase;

class UniqueIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_guard_reports_duplicates_with_table_columns_and_count(): void
    {
        // TEMPORARY tables do not cause an implicit commit, so RefreshDatabase's transaction survives.
        DB::statement('CREATE TEMPORARY TABLE dup_probe (a INT NULL, b INT NULL)');
        DB::table('dup_probe')->insert([['a' => 1, 'b' => 1], ['a' => 1, 'b' => 1], ['a' => 2, 'b' => 2], ['a' => null, 'b' => 3], ['a' => null, 'b' => 3]]);

        try {
            DuplicateGuard::assertNone('dup_probe', ['a', 'b']);
            $this->fail('Expected a RuntimeException');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('dup_probe', $e->getMessage());
            $this->assertStringContainsString('(a, b)', $e->getMessage());
            $this->assertStringContainsString('1 duplicated', $e->getMessage()); // NULL rows ignored
        }
    }

    public function test_guard_passes_on_clean_data(): void
    {
        DB::statement('CREATE TEMPORARY TABLE dup_probe (a INT NULL)');
        DB::table('dup_probe')->insert([['a' => 1], ['a' => 2]]);
        DuplicateGuard::assertNone('dup_probe', ['a']);
        $this->addToAssertionCount(1);
    }

    public function test_second_header_for_a_schedule_is_rejected(): void
    {
        $header = DB::table('header_absens')->first();
        $this->expectException(UniqueConstraintViolationException::class);
        DB::table('header_absens')->insert(['schedules_id' => $header->schedules_id, 'teacher_id' => $header->teacher_id]);
    }

    public function test_second_detail_for_a_student_in_one_header_is_rejected(): void
    {
        $detail = DB::table('detail_absens')->first();
        $this->expectException(UniqueConstraintViolationException::class);
        DB::table('detail_absens')->insert(['header_absen_id' => $detail->header_absen_id, 'student_id' => $detail->student_id]);
    }

    public function test_second_mapping_for_a_student_in_one_class_is_rejected(): void
    {
        $m = DB::table('mapping_class_children')->first();
        $this->expectException(UniqueConstraintViolationException::class);
        DB::table('mapping_class_children')->insert(['class_id' => $m->class_id, 'student_id' => $m->student_id]);
    }

    public function test_second_rekening_with_the_same_number_is_rejected(): void
    {
        $r = DB::table('rekenings')->first();
        $this->expectException(UniqueConstraintViolationException::class);
        DB::table('rekenings')->insert(['bank_rek' => $r->bank_rek]);
    }
}
