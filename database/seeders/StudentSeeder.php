<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StudentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        DB::table('students')->insert([
            [
                'nis' => '2440041392',
                'LongName' => 'Karvin Nanda',
                'ShortName' => 'KV',
                'Dob' => '2022-12-01',
                'EnrollDate' => '2002-12-21',
                'bank_rek' => '1978646789',
                'nama_orang_tua' => 'Adalah',
                'Address' => 'Jl.kenangan indah',
                'City' => 'Jakarta',
                'kode_pos' => '14480',
                'Phone1' => '089726152322',
                'Phone2' => '085275586585',
                'Whatsapp' => '085275586585',
                'Instagram' => '@khg12',
                'Email' => 'karv@gmail.com',
                'Status' => 'aktif',
            ],

            [
                'nis' => '2440099123',
                'LongName' => 'Jose Susanto',
                'ShortName' => 'SE',
                'Dob' => '2012-11-01',
                'EnrollDate' => '2022-11-22',
                'bank_rek' => '5271992837',
                'nama_orang_tua' => 'MauTahuAja',
                'Address' => 'Jl.ternah jodoh',
                'City' => 'Jakarta',
                'kode_pos' => '22008',
                'Phone1' => '018232342912',
                'Phone2' => '081239837744',
                'Whatsapp' => '081239837744',
                'Instagram' => '@itsj',
                'Email' => 'jose@gmail.com',
                'Status' => 'non-aktif',
            ],

            [
                'nis' => '2340099123',
                'LongName' => 'Brian Imanuel',
                'ShortName' => 'BI',
                'Dob' => '2012-08-01',
                'EnrollDate' => '2022-09-22',
                'bank_rek' => '4271992837',
                'nama_orang_tua' => 'SiapaHayo',
                'Address' => 'Jl.Cari Pacar',
                'City' => 'Jakarta',
                'kode_pos' => '111210',
                'Phone1' => '018232342912',
                'Phone2' => '081239837744',
                'Whatsapp' => '081239837744',
                'Instagram' => '@BIi',
                'Email' => 'Brian@gmail.com',
                'Status' => 'non-aktif',
            ],

        ]);
    }
}
