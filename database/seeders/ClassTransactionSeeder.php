<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/** 8 classes: 6 active, 1 non-active, 1 frozen. Price is copied from the course, like the app does. */
class ClassTransactionSeeder extends Seeder
{
    public function run()
    {
        $courses = DB::table('class_types')->orderBy('id')->limit(8)->get();
        $states = [
            ['aktif', 0], ['aktif', 0], ['aktif', 0], ['aktif', 0], ['aktif', 0], ['aktif', 0],
            ['non-aktif', 0],
            ['aktif', 1],
        ];

        foreach ($courses as $i => $course) {
            DB::table('class_transactions')->insert([
                'class_type_id' => $course->id,
                'Status' => $states[$i][0],
                'is_freeze' => $states[$i][1],
                'class_transaction_price' => $course->class_price,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
