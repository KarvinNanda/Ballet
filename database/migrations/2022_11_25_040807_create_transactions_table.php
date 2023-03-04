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
            $table->foreignId('students_id')->references('id')->on('students')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('class_transactions_id')->references('id')->on('class_transactions')->onDelete('cascade')->onUpdate('cascade');
            $table->date('transaction_date');
            $table->date('transaction_payment')->nullable(true);
            $table->string('payment_status');
			$table->integer('discount')->nullable()->default(0);
            $table->integer('price');
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
