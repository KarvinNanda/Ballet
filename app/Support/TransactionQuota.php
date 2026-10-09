<?php

namespace App\Support;

use Closure;
use Illuminate\Support\Facades\DB;

class TransactionQuota
{
    /**
     * Runs $change (save, bulk update or delete of transactions of one student and class) and grants the
     * difference in paid quota it caused: Unpaid→Paid adds the row's quota once, re-saving adds nothing,
     * un-paying or deleting a paid row takes it back. Only payment_status 'Paid' counts.
     * A null $studentId (legacy orphan row) runs $change without touching any quota.
     * Must not be called inside an outer DB transaction: the "before" read would use the outer snapshot.
     */
    public static function track(?int $studentId, ?int $classId, Closure $change): mixed
    {
        if ($studentId === null) {
            return $change();
        }

        return DB::transaction(function () use ($studentId, $classId, $change) {
            // Serialise quota changes per student so two requests cannot both read the same "before".
            DB::table('students')->where('id', $studentId)->lockForUpdate()->first();

            $before = self::paidQuota($studentId, $classId);
            $result = $change();
            $delta = self::paidQuota($studentId, $classId) - $before;

            if ($delta !== 0) {
                if ($classId !== null) {
                    DB::table('mapping_class_children')
                        ->where('student_id', $studentId)->where('class_id', $classId)
                        ->update(['quota' => DB::raw('COALESCE(quota, 0) + '.$delta)]);
                }
                DB::table('students')->where('id', $studentId)
                    ->update(['MaxQuota' => DB::raw('COALESCE(MaxQuota, 0) + '.$delta)]);
            }

            return $result;
        });
    }

    private static function paidQuota(int $studentId, ?int $classId): int
    {
        return (int) DB::table('transactions')
            ->where('students_id', $studentId)
            ->where('class_transactions_id', $classId) // null becomes "is null"
            ->where('payment_status', 'Paid')
            ->sum('transaction_quota');
    }
}
