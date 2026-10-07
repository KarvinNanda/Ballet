<?php

namespace Tests\Unit;

use App\Support\TransactionQuota;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class TransactionQuotaTest extends TestCase
{
    public static function months(): array
    {
        return [
            'January' => [1, [1, 3]],
            'March (end of Q1)' => [3, [1, 3]],
            'April (start of Q2)' => [4, [4, 6]],
            'June' => [6, [4, 6]],
            'July' => [7, [7, 9]],
            'September' => [9, [7, 9]],
            'October' => [10, [10, 12]],
            'December' => [12, [10, 12]],
        ];
    }

    #[DataProvider('months')]
    public function test_quarter_months_cover_the_calendar_quarter(int $month, array $expected): void
    {
        $this->assertSame($expected, TransactionQuota::quarterMonths($month));
    }

    public function test_month_outside_1_to_12_is_rejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        TransactionQuota::quarterMonths(13);
    }
}
