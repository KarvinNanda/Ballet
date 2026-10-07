<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/** One stock movement per stock item over the last days. first_qty is the quantity before the movement. */
class ReportStockSeeder extends Seeder
{
    public function run()
    {
        $stocks = DB::table('stocks')->orderBy('id')->get(['id', 'quantity']);

        foreach ($stocks as $i => $stock) {
            DB::table('report_stocks')->insert([
                'stock_id' => $stock->id,
                'first_qty' => $stock->quantity,
                'in' => $i % 2 === 0 ? 10 : 0,
                'out' => $i % 2 === 0 ? 0 : 2,
                'report_date' => now('Asia/Jakarta')->subDays($i + 1)->toDateString(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
