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
               'transaction_payment' => '2022-12-01',
               'payment_status' => 'Paid',
               'discount' => 0,
               'desc' => '',
               'price' => 500000
           ],

            [
                'students_id' => 1,
                'class_transactions_id' => 2,
                'transaction_date' => '2022-12-01',
                'transaction_payment' => '2022-12-01',
                'payment_status' => 'Paid',
                'discount' => 0,
                'desc' => '',
                'price' => 250000
            ],

            [
                'students_id' => 1,
                'class_transactions_id' => 3,
                'transaction_date' => '2022-12-01',
                'transaction_payment' => '2022-12-01',
                'payment_status' => 'Paid',
                'discount' => 0,
                'desc' => '',
                'price' => 700000
            ],

            [
                'students_id' => 1,
                'class_transactions_id' => 4,
                'transaction_date' => '2022-12-01',
                'transaction_payment' => '2022-12-01',
                'payment_status' => 'Paid',
                'discount' => 0,
                'desc' => '',
                'price' => 800000
            ],

            [
                'students_id' => 2,
                'class_transactions_id' => 1,
                'transaction_date' => '2022-11-14',
                'transaction_payment' => null,
                'payment_status' => 'Unpaid',
                'discount' => 10,
                'desc' => '',
                'price' => 200000
            ],

            [
                'students_id' => 2,
                'class_transactions_id' => 4,
                'transaction_date' => '2022-11-14',
                'transaction_payment' => null,
                'payment_status' => 'Unpaid',
                'discount' => 10,
                'desc' => '',
                'price' => 120000
            ],

            [
                'students_id' => 3,
                'class_transactions_id' => 4,
                'transaction_date' => '2022-11-14',
                'transaction_payment' => null,
                'payment_status' => 'Unpaid',
                'discount' => 10,
                'desc' => '',
                'price' => 110000
            ],

            [
                'students_id' => 5,
                'class_transactions_id' => 4,
                'transaction_date' => '2022-11-14',
                'transaction_payment' => null,
                'payment_status' => 'Unpaid',
                'discount' => 10,
                'desc' => '',
                'price' => 190000
            ],

            [
                'students_id' => 3,
                'class_transactions_id' => 1,
                'transaction_date' => '2022-12-01',
                'transaction_payment' => '2022-12-01',
                'payment_status' => 'Paid',
                'discount' => 0,
                'desc' => '',
                'price' => 180000
            ],

            [
                'students_id' => 4,
                'class_transactions_id' => 1,
                'transaction_date' => '2022-11-14',
                'transaction_payment' => null,
                'payment_status' => 'Unpaid',
                'discount' => 10,
                'desc' => '',
                'price' => 100000
            ],

            [
                'students_id' => 5,
                'class_transactions_id' => 1,
                'transaction_date' => '2022-12-01',
                'transaction_payment' => '2022-12-01',
                'payment_status' => 'Paid',
                'discount' => 0,
                'desc' => '',
                'price' => 380000
            ],

            [
                'students_id' => 6,
                'class_transactions_id' => 1,
                'transaction_date' => '2022-11-14',
                'transaction_payment' => null,
                'payment_status' => 'Unpaid',
                'discount' => 10,
                'desc' => '',
                'price' => 320000
            ],

            [
                'students_id' => 8,
                'class_transactions_id' => 1,
                'transaction_date' => '2022-11-14',
                'transaction_payment' => null,
                'payment_status' => 'Unpaid',
                'discount' => 10,
                'desc' => '',
                'price' => 300000
            ],

            [
                'students_id' => 10,
                'class_transactions_id' => 1,
                'transaction_date' => '2022-11-14',
                'transaction_payment' => null,
                'payment_status' => 'Unpaid',
                'discount' => 10,
                'desc' => '',
                'price' => 150000
            ],

            [
                'students_id' => 11,
                'class_transactions_id' => 1,
                'transaction_date' => '2022-12-01',
                'transaction_payment' => '2022-12-01',
                'payment_status' => 'Paid',
                'discount' => 0,
                'desc' => '',
                'price' => 310000
            ],

            [
                'students_id' => 15,
                'class_transactions_id' => 1,
                'transaction_date' => '2022-12-01',
                'transaction_payment' => '2022-12-01',
                'payment_status' => 'Paid',
                'discount' => 0,
                'desc' => '',
                'price' => 190000
            ],

            [
                'students_id' => 16,
                'class_transactions_id' => 4,
                'transaction_date' => '2022-11-14',
                'transaction_payment' => null,
                'payment_status' => 'Unpaid',
                'discount' => 10,
                'desc' => '',
                'price' => 290000
            ],

            [
                'students_id' => 17,
                'class_transactions_id' => 4,
                'transaction_date' => '2022-12-01',
                'transaction_payment' => '2022-12-01',
                'payment_status' => 'Paid',
                'discount' => 0,
                'desc' => '',
                'price' => 210000
            ],

            [
                'students_id' => 18,
                'class_transactions_id' => 4,
                'transaction_date' => '2022-11-14',
                'transaction_payment' => null,
                'payment_status' => 'Unpaid',
                'discount' => 10,
                'desc' => '',
                'price' => 230000
            ],
        ]);
    }
}
