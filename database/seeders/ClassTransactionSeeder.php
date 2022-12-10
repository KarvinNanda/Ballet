<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ClassTransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('class_transactions')->insert([
            [
                'ClassName' => 'Baby Class',
                'ClassPrice' => 390000,
                'Status' => 'aktif'
            ],

            [
                'ClassName' => 'Pre School',
                'ClassPrice' => 390000,
                'Status' => 'aktif'
            ],

            [
                'ClassName' => 'Pre Primary',
                'ClassPrice' => 400000,
                'Status' => 'aktif'
            ],

            [
                'ClassName' => 'Primary',
                'ClassPrice' => 410000,
                'Status' => 'aktif'
            ],

            [
                'ClassName' => 'Grace 1',
                'ClassPrice' => 420000,
                'Status' => 'aktif'
            ],

            [
                'ClassName' => 'Grace 2',
                'ClassPrice' => 430000,
                'Status' => 'aktif'
            ],

            [
                'ClassName' => 'Grace 3',
                'ClassPrice' => 440000,
                'Status' => 'aktif'
            ],

            [
                'ClassName' => 'Grace 4',
                'ClassPrice' => 450000,
                'Status' => 'aktif'
            ],

            [
                'ClassName' => 'Grace 5',
                'ClassPrice' => 460000,
                'Status' => 'non-aktif'
            ],

            [
                'ClassName' => 'Intermediate Foundation',
                'ClassPrice' => 490000,
                'Status' => 'non-aktif'
            ],

            [
                'ClassName' => 'Intermediate',
                'ClassPrice' => 535000,
                'Status' => 'non-aktif'
            ],

            [
                'ClassName' => 'Advance Foundation',
                'ClassPrice' => 585000,
                'Status' => 'non-aktif'
            ],

            [
                'ClassName' => 'Advance 1',
                'ClassPrice' => 615000,
                'Status' => 'non-aktif'
            ],

            [
                'ClassName' => 'Advance 2',
                'ClassPrice' => 645000,
                'Status' => 'non-aktif'
            ],

            [
                'ClassName' => 'Intensive Class',
                'ClassPrice' => 1200000,
                'Status' => 'non-aktif'
            ],

            [
                'ClassName' => 'Intensive Kids',
                'ClassPrice' => 1200000,
                'Status' => 'non-aktif'
            ],

            [
                'ClassName' => 'Pointe Class',
                'ClassPrice' => 25000,
                'Status' => 'non-aktif'
            ],


        ]);
    }
}
