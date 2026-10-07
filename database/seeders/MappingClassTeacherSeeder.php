<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/** One teacher per class, round robin. teacher@gmail.com is first, so it always gets the first active class. */
class MappingClassTeacherSeeder extends Seeder
{
    public function run()
    {
        $teacherIds = DB::table('users')->where('role', 'teacher')->orderBy('id')->pluck('id');
        $classIds = DB::table('class_transactions')->orderBy('id')->pluck('id');

        foreach ($classIds as $i => $classId) {
            DB::table('mapping_class_teachers')->insert([
                'class_id' => $classId,
                'user_id' => $teacherIds[$i % $teacherIds->count()],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
