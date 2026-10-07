<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/** Local demo data. Order matters: each seeder reads ids created by the ones before it. */
class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call([
            BankSeeder::class,
            RekeningSeeder::class,
            ClassTypeSeeder::class,
            StudentSeeder::class,
            StockSeeder::class,
            UserSeeder::class,
            ClassTransactionSeeder::class,
            MappingClassTeacherSeeder::class,
            MappingClassChildSeeder::class,
            ScheduleSeeder::class,
            HeaderAttendenceSeeder::class,
            DetailAttendenceSeeder::class,
            TransactionSeeder::class,
            ReportStockSeeder::class,
            BuyerSeeder::class,
            RuleSeeder::class,
        ]);
    }
}
