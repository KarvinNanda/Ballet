<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('students_id')->nullable();
            $table->unsignedBigInteger('class_transactions_id')->nullable();
            $table->date('transaction_date')->nullable();
            $table->date('transaction_payment')->nullable();
            $table->string('payment_status')->nullable();
			$table->string('discount')->nullable();
            $table->integer('price')->nullable();
            $table->string('desc')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('transactions');
    }
};
