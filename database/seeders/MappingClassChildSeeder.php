<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/** 5 active students per class, rotating through the student list so each class has a different group. */
class MappingClassChildSeeder extends Seeder
{
    private const STUDENTS_PER_CLASS = 5;

    public function run()
    {
        $studentIds = DB::table('students')->where('Status', 'aktif')->orderBy('id')->pluck('id');
        $classIds = DB::table('class_transactions')->orderBy('id')->pluck('id');

        foreach ($classIds as $i => $classId) {
            for ($n = 0; $n < self::STUDENTS_PER_CLASS; $n++) {
                DB::table('mapping_class_children')->insert([
                    'class_id' => $classId,
                    'student_id' => $studentIds[($i * 2 + $n) % $studentIds->count()],
                    'quota' => 8,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
