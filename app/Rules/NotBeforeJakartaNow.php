<?php

namespace App\Rules;

use App\Support\AttendanceWindow;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * A datetime that is not in the past, read the way schedules.date is stored: as Asia/Jakarta wall-clock time.
 * Laravel's after_or_equal:now compares in the app timezone (UTC) and would be 7 hours off.
 * datetime-local has minute precision, so "now" is cut to the minute: the current minute still passes.
 * Run it after BoundedDate's rules (they check required and format).
 */
class NotBeforeJakartaNow implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value)) {
            return; // format and presence belong to BoundedDate
        }

        if (AttendanceWindow::start($value)->lt(AttendanceWindow::now()->startOfMinute())) {
            $fail('Choose a date and time that has not passed yet.');
        }
    }
}
