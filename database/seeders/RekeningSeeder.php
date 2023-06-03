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
            // ==========

            [
                'bank_rek' => '4271912337',
                'banks_id' => 1,
                'nama_pengirim' => 'Budi'
            ],

            [
                'bank_rek' => '4273992837',
                'banks_id' => 1,
                'nama_pengirim' => 'Budi'
            ],

            [
                'bank_rek' => '4271932837',
                'banks_id' => 1,
                'nama_pengirim' => 'Budi'
            ],

            [
                'bank_rek' => '4265992837',
                'banks_id' => 1,
                'nama_pengirim' => 'Budi'
            ],

            [
                'bank_rek' => '4271322837',
                'banks_id' => 1,
                'nama_pengirim' => 'Budi'
            ],

            [
                'bank_rek' => '4271992117',
                'banks_id' => 1,
                'nama_pengirim' => 'Budi'
            ],

            [
                'bank_rek' => '4271909837',
                'banks_id' => 1,
                'nama_pengirim' => 'Budi'
            ],

            [
                'bank_rek' => '4278492837',
                'banks_id' => 1,
                'nama_pengirim' => 'Budi'
            ],

            [
                'bank_rek' => '4271942837',
                'banks_id' => 1,
                'nama_pengirim' => 'Budi'
            ],

            [
                'bank_rek' => '4271984737',
                'banks_id' => 1,
                'nama_pengirim' => 'Budi'
            ],

            [
                'bank_rek' => '4229482837',
                'banks_id' => 1,
                'nama_pengirim' => 'Budi'
            ],

            [
                'bank_rek' => '4271594837',
                'banks_id' => 1,
                'nama_pengirim' => 'Budi'
            ],

            [
                'bank_rek' => '4271032837',
                'banks_id' => 1,
                'nama_pengirim' => 'Budi'
            ],

            [
                'bank_rek' => '4271002837',
                'banks_id' => 1,
                'nama_pengirim' => 'Budi'
            ],

            [
                'bank_rek' => '4271983237',
                'banks_id' => 1,
                'nama_pengirim' => 'Budi'
            ],

            [
                'bank_rek' => '4271008837',
                'banks_id' => 1,
                'nama_pengirim' => 'Budi'
            ],

            [
                'bank_rek' => '4272957837',
                'banks_id' => 1,
                'nama_pengirim' => 'Budi'
            ],

        ]);
    }
}
