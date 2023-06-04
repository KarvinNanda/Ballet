<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StockSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('stocks')->insert([
            [
                'name' => 'Kaos Kaki Ballet',
                'size' => 'L',
                'quantity' => 30
            ],

            [
                'name' => 'Sepatu Ballet',
                'size' => '40',
                'quantity' => 50
            ],

            [
                'name' => 'Baju Ballet',
                'size' => 'M',
                'quantity' => 15
            ],

            [
                'name' => 'Sepatu Ballet',
                'size' => '40',
                'quantity' => 50
            ],

            [
                'name' => 'Unitard',
                'size' => 'L',
                'quantity' => 10
            ],

            [
                'name' => 'Leotard',
                'size' => 'M',
                'quantity' => 5
            ],

            [
                'name' => 'Stocking',
                'size' => 'M',
                'quantity' => 13
            ],

            [
                'name' => 'Rok Tutu',
                'size' => 'S',
                'quantity' => 10
            ],
        ]);
    }
}
