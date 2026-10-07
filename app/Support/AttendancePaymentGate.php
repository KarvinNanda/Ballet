<?php

namespace App\Support;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * A student must pay before more attendance is taken when:
 * the class is not Intensive/Pointe, the quota is used up (Quota + 1 > 3),
 * and the oldest unpaid bill is 20-39 days away from the class date.
 * Used by the attendance form (to show "Please Completed Payment") and by the save (to refuse the row).
 */
class AttendancePaymentGate
{
    /** @param object $student needs ->id and ->Quota */
    public static function requiresPayment(object $student, string $classDate, string $className): bool
    {
        if (str_contains($className, 'Intensive') || str_contains($className, 'Pointe')) {
            return false;
        }

        if ($student->Quota + 1 <= 3) {
            return false;
        }

        $oldestUnpaid = DB::table('transactions')
            ->where('students_id', $student->id)
            ->where('payment_status', 'Unpaid')
            ->orderBy('transaction_date')
            ->value('transaction_date');

        if ($oldestUnpaid === null) {
            return false;
        }

        // Whole days, either direction (Carbon 3 diffInDays is signed and fractional).
        $days = (int) Carbon::parse($classDate)->diffInDays($oldestUnpaid, true);

        return $days >= 20 && $days <= 39;
    }
}
