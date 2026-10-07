<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BankSeeder extends Seeder
{
    public function run()
    {
        // BCA and Mandiri stay first: RekeningSeeder refers to banks 1 and 2.
        DB::table('banks')->insert(array_map(
            fn ($name) => ['bank_name' => $name, 'created_at' => now(), 'updated_at' => now()],
            ['BCA', 'Mandiri', 'BNI', 'BRI', 'CIMB Niaga']
        ));
    }
}
