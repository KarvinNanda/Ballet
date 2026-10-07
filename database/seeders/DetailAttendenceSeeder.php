<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/** One attendance row per student per header. "Attend" is the value the app writes for a checked student. */
class DetailAttendenceSeeder extends Seeder
{
    private const ABSENCES = [['Sakit', 'Demam'], ['Izin', 'Acara keluarga']];

    public function run()
    {
        $rows = DB::table('header_absens as h')
            ->join('schedules as s', 's.id', 'h.schedules_id')
            ->join('mapping_class_children as c', 'c.class_id', 's.class_id')
            ->orderBy('h.id')
            ->orderBy('c.student_id')
            ->get(['h.id as header_id', 'c.student_id']);

        foreach ($rows as $i => $row) {
            // Every 7th row is an absence, so reports show more than "Attend".
            $absence = $i % 7 === 6 ? self::ABSENCES[intdiv($i, 7) % 2] : null;

            DB::table('detail_absens')->insert([
                'header_absen_id' => $row->header_id,
                'student_id' => $row->student_id,
                'Description' => $absence[0] ?? 'Attend',
                'Notes' => $absence[1] ?? '-',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
