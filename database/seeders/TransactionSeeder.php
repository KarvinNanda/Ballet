<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        DB::table('transactions')->insert([
           [
               'students_id' => 1,
               'class_transactions_id' => 1,
               'transaction_date' => '2022-12-01',
               'payment_status' => 'lunas',
               'discount' => 0,
               'desc' => 'ganteng',
               'price' => 500000
           ],

            [
                'students_id' => 2,
                'class_transactions_id' => 5,
                'transaction_date' => '2022-11-14',
                'payment_status' => 'belum lunas',
                'discount' => 10,
                'desc' => 'rajin',
                'price' => 350000
            ],
        ]);
    }
}
