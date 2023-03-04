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
        Schema::create('rekenings', function (Blueprint $table) {
            $table->string('bank_rek');
            $table->unsignedBigInteger('banks_id')->nullable(true);
            $table->foreign('banks_id')->references('id')->on('banks')->onUpdate('cascade')->onDelete('cascade');
            $table->string('nama_pengirim')->nullable(true);
            $table->primary(['bank_rek']);
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
        Schema::dropIfExists('banks');
    }
};
