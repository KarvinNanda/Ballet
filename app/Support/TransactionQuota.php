<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

class TransactionQuota
{
    /**
     * Adds the quota of this quarter's paid transactions (by transaction_date, Jakarta time) to the student's quota
     * in one class, then refreshes students.MaxQuota. Moved verbatim from the old admin/head controllers.
     */
    public static function recalculate(int $studentId, int $classId): void
    {
        $months = self::quarterMonths(now('Asia/Jakarta')->month);

        $student_quota_class = DB::table('mapping_class_children')
            ->where('student_id', $studentId)
            ->where('class_id', $classId)
            ->selectRaw('sum(quota) as Quota')
            ->first();

        $get_trans_paid = DB::table('transactions')
            ->where('students_id', $studentId)
            ->where('class_transactions_id', $classId)
            ->where('payment_status', 'Paid')
            ->whereRaw('month(transaction_date) between ? and ?', $months)
            ->selectRaw('sum(transaction_quota) as quota')
            ->first();

        DB::table('mapping_class_children')
            ->where('student_id', $studentId)
            ->where('class_id', $classId)
            ->update([
                'quota' => $student_quota_class->Quota + $get_trans_paid->quota,
            ]);

        $student_all_quota_class = DB::table('mapping_class_children')
            ->where('student_id', $studentId)
            ->selectRaw('sum(quota) as Quota')
            ->first();

        DB::table('students')->where('id', $studentId)->update([
            'MaxQuota' => $student_all_quota_class->Quota,
        ]);
    }

    /**
     * First and last month of the calendar quarter that contains $month (1-12), e.g. 5 → [4, 6].
     *
     * @return array{0: int, 1: int}
     */
    public static function quarterMonths(int $month): array
    {
        if ($month < 1 || $month > 12) {
            throw new \InvalidArgumentException("Month must be 1-12, got {$month}.");
        }

        $first = intdiv($month - 1, 3) * 3 + 1;

        return [$first, $first + 2];
    }
}
