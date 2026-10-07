<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/** Past purchases. served_by stores the staff name, as BuyerController does. */
class BuyerSeeder extends Seeder
{
    public function run()
    {
        $stockIds = DB::table('stocks')->orderBy('id')->pluck('id');
        $buyers = ['Ibu Rina', 'Bapak Andi', 'Ibu Lestari', 'Ibu Fransiska', 'Bapak Hendra', 'Ibu Melinda'];

        foreach ($buyers as $i => $name) {
            DB::table('buyers')->insert([
                'name' => $name,
                'qty' => $i % 3 + 1,
                'served_by' => $i % 2 === 0 ? 'Admin' : 'Finance',
                'stock_id' => $stockIds[$i % $stockIds->count()],
                'created_at' => now()->subDays($i),
                'updated_at' => now()->subDays($i),
            ]);
        }
    }
}
