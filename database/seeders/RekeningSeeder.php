<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RekeningSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        DB::table('rekenings')->insert([
            [
                'bank_rek' => '1978646789',
                'banks_id' => '2',
                'nama_pengirim' => 'Udin'
            ],

            [
                'bank_rek' => '5271992837',
                'banks_id' => 1,
                'nama_pengirim' => 'Budi'
            ],

            [
                'bank_rek' => '4271992837',
                'banks_id' => 1,
                'nama_pengirim' => 'Budi'
            ],

        ]);
    }
}
