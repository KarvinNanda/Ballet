<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/*
| Code has used class_transactions.class_transaction_price since commit 5678622 (2024-05-01),
| but no migration renamed the column created as `price` in 2024_04_05_180620.
| Raw ALTER because Laravel 9 renameColumn() needs doctrine/dbal.
| Guarded, so a database that was already renamed by hand is left alone.
*/
return new class extends Migration
{
    public function up()
    {
        if (Schema::hasColumn('class_transactions', 'price') && ! Schema::hasColumn('class_transactions', 'class_transaction_price')) {
            DB::statement('ALTER TABLE class_transactions RENAME COLUMN price TO class_transaction_price');
        }
    }

    public function down()
    {
        if (Schema::hasColumn('class_transactions', 'class_transaction_price') && ! Schema::hasColumn('class_transactions', 'price')) {
            DB::statement('ALTER TABLE class_transactions RENAME COLUMN class_transaction_price TO price');
        }
    }
};
