<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/** Monthly fees for the first 4 running classes: last month paid, this month unpaid. Same shape the app inserts. */
class TransactionSeeder extends Seeder
{
    public function run()
    {
        $enrollments = DB::table('mapping_class_children as c')
            ->join('class_transactions as ct', 'ct.id', 'c.class_id')
            ->where('ct.Status', 'aktif')
            ->where('ct.is_freeze', 0)
            ->whereIn('ct.id', DB::table('class_transactions')->where('Status', 'aktif')->where('is_freeze', 0)->orderBy('id')->limit(4)->pluck('id'))
            ->orderBy('c.class_id')
            ->get(['c.class_id', 'c.student_id', 'ct.class_transaction_price as price']);

        $thisMonth = now('Asia/Jakarta')->startOfMonth()->addDays(9);
        $lastMonth = $thisMonth->copy()->subMonth();

        foreach ($enrollments as $i => $e) {
            DB::table('transactions')->insert([
                [
                    'students_id' => $e->student_id,
                    'class_transactions_id' => $e->class_id,
                    'transaction_date' => $lastMonth->toDateString(),
                    'transaction_payment' => $lastMonth->copy()->addDays(2)->toDateString(),
                    'payment_status' => 'Paid',
                    'discount' => $i % 5 === 0 ? 10 : 0,
                    'price' => $e->price,
                    'desc' => '-',
                    'transaction_quota' => 8,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'students_id' => $e->student_id,
                    'class_transactions_id' => $e->class_id,
                    'transaction_date' => $thisMonth->toDateString(),
                    'transaction_payment' => null,
                    'payment_status' => 'Unpaid',
                    'discount' => 0,
                    'price' => $e->price,
                    'desc' => '-',
                    'transaction_quota' => 8,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);
        }
    }
}
