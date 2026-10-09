<?php

namespace Tests\Unit;

use App\Support\AttendanceWindow;
use Carbon\CarbonImmutable;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\TestCase;

class AttendanceWindowTest extends TestCase
{
    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private static function jakarta(string $wallClock): CarbonImmutable
    {
        return CarbonImmutable::parse($wallClock, 'Asia/Jakarta');
    }

    public function test_a_session_opens_at_its_start_time(): void
    {
        $this->assertFalse(AttendanceWindow::isOpen('2026-10-09 16:00:00', self::jakarta('2026-10-09 15:59:59')));
        $this->assertTrue(AttendanceWindow::isOpen('2026-10-09 16:00:00', self::jakarta('2026-10-09 16:00:00')));
    }

    public function test_a_session_stays_open_until_the_end_of_the_next_day(): void
    {
        $this->assertTrue(AttendanceWindow::isOpen('2026-10-09 16:00:00', self::jakarta('2026-10-10 23:59:59')));
        $this->assertFalse(AttendanceWindow::isOpen('2026-10-09 16:00:00', self::jakarta('2026-10-11 00:00:00')));
    }

    public function test_a_late_evening_session_still_closes_at_the_end_of_the_next_day(): void
    {
        $this->assertTrue(AttendanceWindow::isOpen('2026-10-09 23:30:00', self::jakarta('2026-10-10 23:59:59')));
        $this->assertFalse(AttendanceWindow::isOpen('2026-10-09 23:30:00', self::jakarta('2026-10-11 00:00:01')));
    }

    public function test_now_is_compared_in_jakarta_time_whatever_its_zone(): void
    {
        // 2026-10-09 17:30 UTC is 2026-10-10 00:30 in Jakarta.
        $now = CarbonImmutable::parse('2026-10-09 17:30:00', 'UTC');

        $this->assertFalse(AttendanceWindow::isOpen('2026-10-08 16:00:00', $now), 'closes at Jakarta midnight, not UTC midnight');
        $this->assertTrue(AttendanceWindow::isOpen('2026-10-10 00:15:00', $now), 'already started in Jakarta');
    }

    public function test_a_stored_datetime_object_is_read_as_jakarta_wall_clock_time(): void
    {
        // Eloquent hands a DATETIME back in the app zone (UTC) with the stored digits unchanged.
        $stored = new \DateTimeImmutable('2026-10-09 16:00:00', new \DateTimeZone('UTC'));

        $this->assertSame('2026-10-09T16:00:00+07:00', AttendanceWindow::start($stored)->toIso8601String());
        $this->assertSame('2026-10-10T23:59:59+07:00', AttendanceWindow::closesAt($stored)->toIso8601String());
    }

    public function test_status_names_each_state(): void
    {
        $now = self::jakarta('2026-10-09 10:00:00');

        $this->assertSame(AttendanceWindow::RECORDED, AttendanceWindow::status('2026-10-01 16:00:00', true, $now));
        $this->assertSame(AttendanceWindow::NOT_STARTED, AttendanceWindow::status('2026-10-09 16:00:00', false, $now));
        $this->assertSame(AttendanceWindow::OPEN, AttendanceWindow::status('2026-10-08 16:00:00', false, $now));
        $this->assertSame(AttendanceWindow::MISSED, AttendanceWindow::status('2026-10-07 16:00:00', false, $now));
        $this->assertSame(AttendanceWindow::RECORDED, AttendanceWindow::status('2026-10-20 16:00:00', true, $now), 'recorded wins over the clock');
    }

    public function test_a_null_date_is_never_open_and_counts_as_missed(): void
    {
        $now = self::jakarta('2026-10-09 10:00:00');

        $this->assertFalse(AttendanceWindow::isOpen(null, $now));
        $this->assertSame(AttendanceWindow::MISSED, AttendanceWindow::status(null, false, $now));
        $this->assertSame(AttendanceWindow::RECORDED, AttendanceWindow::status(null, true, $now), 'recorded still wins');
    }

    public function test_without_a_now_it_uses_the_test_clock(): void
    {
        Carbon::setTestNow(self::jakarta('2026-10-09 16:30:00'));

        $this->assertTrue(AttendanceWindow::isOpen('2026-10-09 16:00:00'));
        $this->assertSame('2026-10-09', AttendanceWindow::now()->toDateString());
    }
}
