<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BankSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        DB::table('banks')->insert([
            [
                'bank_name' => 'BCA'
            ],

            [
                'bank_name' => 'BRI'
            ],

            [
                'bank_name' => 'BNI'
            ],
        ]);
    }
}
