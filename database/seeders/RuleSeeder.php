<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/** Studio rules shown on the Add Student page. content is varchar(255), so each rule stays short. */
class RuleSeeder extends Seeder
{
    public function run()
    {
        $rules = [
            ['Indonesia', '<p>Siswa wajib datang 10 menit sebelum kelas dimulai.</p>'],
            ['Indonesia', '<p>Pembatalan kelas paling lambat H-1. Kelas yang batal tanpa kabar dianggap hadir.</p>'],
            ['Indonesia', '<p>Orang tua tidak diperbolehkan mengambil foto atau video selama kelas berlangsung.</p>'],
            ['English', '<p>Students must wear the studio uniform: leotard, tights and ballet shoes.</p>'],
            ['English', '<p>Monthly fees are due on the 10th of each month.</p>'],
        ];

        DB::table('rules')->insert(array_map(fn ($r) => [
            'lang' => $r[0],
            'content' => $r[1],
            'created_at' => now(),
            'updated_at' => now(),
        ], $rules));
    }
}
