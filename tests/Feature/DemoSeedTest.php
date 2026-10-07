<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\DataProvider;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/** The base TestCase seeds once with DatabaseSeeder, so these tests read the demo data. */
class DemoSeedTest extends TestCase
{
    use RefreshDatabase;

    public static function filledTables(): array
    {
        return array_map(fn ($table) => [$table], [
            'banks', 'users', 'class_transactions', 'mapping_class_teachers', 'mapping_class_children',
            'schedules', 'header_absens', 'detail_absens', 'transactions', 'stocks', 'report_stocks',
            'buyers', 'rules',
        ]);
    }

    #[DataProvider('filledTables')]
    public function test_table_has_at_least_five_rows(string $table): void
    {
        $this->assertGreaterThanOrEqual(5, DB::table($table)->count(), $table);
    }

    public function test_login_accounts_are_unchanged(): void
    {
        foreach (['admin', 'head', 'teacher', 'finance'] as $role) {
            $this->assertTrue(User::where('email', "{$role}@gmail.com")->where('role', $role)->exists(), $role);
        }
    }

    public function test_every_active_class_has_a_teacher_with_the_teacher_role(): void
    {
        $classesWithoutTeacher = DB::table('class_transactions as ct')
            ->where('ct.Status', 'aktif')
            ->whereNotExists(fn ($q) => $q->from('mapping_class_teachers as m')
                ->join('users as u', 'u.id', 'm.user_id')
                ->whereColumn('m.class_id', 'ct.id')
                ->where('u.role', 'teacher'))
            ->pluck('ct.id');

        $this->assertSame([], $classesWithoutTeacher->all());
    }

    public function test_seeded_teacher_has_a_class_scheduled_today_that_is_not_yet_attended(): void
    {
        $teacher = User::where('email', 'teacher@gmail.com')->firstOrFail();
        $today = now('Asia/Jakarta')->toDateString();

        $schedule = DB::table('schedules as s')
            ->join('mapping_class_teachers as m', 'm.class_id', 's.class_id')
            ->leftJoin('header_absens as h', 'h.schedules_id', 's.id')
            ->where('m.user_id', $teacher->id)
            ->whereDate('s.date', $today)
            ->whereNull('h.id')
            ->select('s.id')
            ->first();

        $this->assertNotNull($schedule, 'teacher has no unattended schedule today');

        $this->actingAs($teacher)->get('/teacher')->assertOk()->assertSee(route('viewAbsen', $schedule->id), false);
        $this->actingAs($teacher)->post(route('viewAbsen', $schedule->id))->assertOk();
    }

    public function test_teacher_dashboard_lists_only_own_classes_today(): void
    {
        $teacher = User::where('email', 'teacher@gmail.com')->firstOrFail();
        $today = now('Asia/Jakarta')->toDateString();
        $schedulesToday = fn (string $operator) => DB::table('schedules as s')
            ->join('mapping_class_teachers as m', 'm.class_id', 's.class_id')
            ->whereDate('s.date', $today)
            ->where('m.user_id', $operator, $teacher->id)
            ->pluck('s.id');

        $html = $this->actingAs($teacher)->get('/teacher')->assertOk()->getContent();

        foreach ($schedulesToday('=') as $own) {
            $this->assertStringContainsString(route('viewAbsen', $own), $html, "own schedule {$own} missing");
        }
        foreach ($schedulesToday('!=') as $other) {
            $this->assertStringNotContainsString(route('viewAbsen', $other).'"', $html, "other teacher's schedule {$other} shown");
        }
    }

    public function test_past_schedules_have_attendance_for_reports(): void
    {
        $attendedPast = DB::table('schedules as s')
            ->join('header_absens as h', 'h.schedules_id', 's.id')
            ->join('detail_absens as d', 'd.header_absen_id', 'h.id')
            ->whereDate('s.date', '<', now('Asia/Jakarta')->toDateString())
            ->count();

        $this->assertGreaterThan(0, $attendedPast);
    }

    public function test_transactions_have_paid_and_unpaid(): void
    {
        $this->assertTrue(DB::table('transactions')->where('payment_status', 'Paid')->exists());
        $this->assertTrue(DB::table('transactions')->where('payment_status', 'Unpaid')->exists());
    }
}
