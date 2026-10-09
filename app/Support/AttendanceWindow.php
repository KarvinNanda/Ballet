<?php

namespace App\Support;

use Carbon\CarbonImmutable;
use DateTimeInterface;

/**
 * When a teacher may record a session: from its start until the end of the next day, Jakarta time.
 * schedules.date holds Jakarta wall-clock time without a zone while the app clock is UTC, so the stored
 * value is read as Asia/Jakarta and "now" is converted to Asia/Jakarta before comparing.
 */
final class AttendanceWindow
{
    public const TIMEZONE = 'Asia/Jakarta'; // GMT+7, no daylight saving

    public const RECORDED = 'recorded';
    public const NOT_STARTED = 'not_started';
    public const OPEN = 'open';
    public const MISSED = 'missed';

    /**
     * A DateTimeInterface argument is read by its digits as Jakarta wall-clock time, whatever its own zone
     * (pass DB values, not now()).
     */
    public static function start(DateTimeInterface|string $date): CarbonImmutable
    {
        // A DateTimeInterface keeps its digits; its zone (the app zone) is not the zone the value was written in.
        $wallClock = $date instanceof DateTimeInterface ? $date->format('Y-m-d H:i:s') : $date;

        return CarbonImmutable::parse($wallClock, self::TIMEZONE);
    }

    public static function closesAt(DateTimeInterface|string $date): CarbonImmutable
    {
        return self::start($date)->addDay()->endOfDay();
    }

    public static function now(?DateTimeInterface $now = null): CarbonImmutable
    {
        return CarbonImmutable::instance($now ?? CarbonImmutable::now())->setTimezone(self::TIMEZONE);
    }

    /** A null date (a schedule without one) is never open. */
    public static function isOpen(DateTimeInterface|string|null $date, ?DateTimeInterface $now = null): bool
    {
        if ($date === null) {
            return false;
        }

        $now = self::now($now);

        return $now->gte(self::start($date)) && $now->lte(self::closesAt($date));
    }

    /** A null date counts as missed (unless recorded), so one bad row cannot break a list page. */
    public static function status(DateTimeInterface|string|null $date, bool $recorded, ?DateTimeInterface $now = null): string
    {
        if ($recorded) {
            return self::RECORDED;
        }

        if ($date === null) {
            return self::MISSED;
        }

        $now = self::now($now);
        if ($now->lt(self::start($date))) {
            return self::NOT_STARTED;
        }

        return $now->lte(self::closesAt($date)) ? self::OPEN : self::MISSED;
    }
}
