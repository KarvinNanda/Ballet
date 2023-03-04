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
        ]);
    }
}
