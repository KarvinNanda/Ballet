<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/** Local demo accounts. The first four are the documented logins; do not change them. */
class UserSeeder extends Seeder
{
    public function run()
    {
        $users = [
            ['Admin', 'admin@gmail.com', 'admin123', 'admin', 'Jl.Depan U', '2002-10-01', '018239222222', 0],
            ['Head', 'head@gmail.com', 'head123', 'head', 'Jl.CepeSebelah', '2002-05-01', '018239211111', 0],
            ['Teacher', 'teacher@gmail.com', 'teacher123', 'teacher', 'Jl.riau ujung', '2002-03-01', '018239210222', 35],
            ['Finance', 'finance@gmail.com', 'finance123', 'finance', 'Jl.kamboja', '2002-06-01', '019283746574', 0],
            ['Sari Wulandari', 'sari.teacher@gmail.com', 'teacher123', 'teacher', 'Jl. Melati 12', '1995-04-12', '081211110001', 35],
            ['Dewi Anggraini', 'dewi.teacher@gmail.com', 'teacher123', 'teacher', 'Jl. Kenanga 8', '1993-09-03', '081211110002', 30],
            ['Maya Putri', 'maya.teacher@gmail.com', 'teacher123', 'teacher', 'Jl. Anggrek 21', '1997-01-25', '081211110003', 30],
        ];

        DB::table('users')->insert(array_map(fn ($u) => [
            'name' => $u[0],
            'email' => $u[1],
            'password' => Hash::make($u[2]),
            'role' => $u[3],
            'address' => $u[4],
            'dob' => $u[5],
            'phone' => $u[6],
            'percent' => $u[7],
            'created_at' => now(),
            'updated_at' => now(),
        ], $users));
    }
}
