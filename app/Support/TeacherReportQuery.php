<?php

namespace App\Support;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/** Teacher attendance report rows for one month of the current year, grouped by teacher name. Shared by staff and finance. */
class TeacherReportQuery
{
    public static function rows(int $month): Collection
    {
        // Whole month of the current year, WIB: first day 00:00:00 to last day 23:59:59.
        $first = now()->setTimezone('GMT+7')->setDate(now()->setTimezone('GMT+7')->year, $month, 1)->startOfDay();
        $last = $first->copy()->endOfMonth();

        return DB::table('header_absens as ha')
            ->join('schedules as s','s.id','ha.schedules_id')
            ->join('users as u','u.id','ha.teacher_id')
            ->join('mapping_class_children as mcc','s.class_id','mcc.class_id')
            ->join('students as st','st.id','mcc.student_id')
            ->join('transactions as t','mcc.student_id','t.students_id')
            ->join('class_transactions as ct','ct.id','s.class_id')
            ->join('class_types as ct2','ct.class_type_id','ct2.id')
            ->selectRaw("
                st.LongName as studentName,
                u.name as teacherName,
                ct2.class_name,
                ct.class_transaction_price,
                st.Quota as meet,
                case
                 when ct2.class_name = 'Intensive Class' then (ct.class_transaction_price / 12)
                    when ct2.class_name = 'Intensive Kids' then (ct.class_transaction_price / 12)
                    when ct2.class_name = 'Pointe Class' then (ct.class_transaction_price / 4)
                    else (ct.class_transaction_price / 8)
                end as paid,
                u.percent as teacher_reward
            ")
            ->whereBetween('s.date',[$first,$last])
            ->whereRaw("(
                (ct2.class_name like '%intensive%' and st.Quota > 0)
                or (ct2.class_name like '%Pointe%' and st.Quota > 0)
                or ((ct2.class_name not like '%pointe%' and ct2.class_name not like '%intensive%') and st.Quota > 5)
                or ((ct2.class_name not like '%pointe%' and ct2.class_name not like '%intensive%') and st.is_new = 1)
            )")
            // Fees billed in the report month or the month after; December wraps to January.
            ->whereIn(DB::raw('month(t.transaction_date)'), [$month, $month % 12 + 1])
            ->distinct()
            ->orderBy('ct.class_transaction_price')
            ->get()
            ->groupBy('teacherName');
    }
}
