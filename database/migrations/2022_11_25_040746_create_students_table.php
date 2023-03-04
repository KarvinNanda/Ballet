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
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('nis');
            $table->string('LongName');
            $table->string('ShortName');
            $table->date('Dob');
            $table->date('EnrollDate');
            $table->string('bank_rek');
            $table->foreign('bank_rek')->references('bank_rek')->on('rekenings')->onDelete('cascade')->onUpdate('cascade');
            $table->string('nama_orang_tua');
            $table->string('Address');
            $table->string('City');
            $table->string('kode_pos');
            $table->string('Phone1');
            $table->string('Phone2');
            $table->string('Whatsapp');
            $table->string('Instagram')->nullable(true);
            $table->string('Line')->nullable(true);
            $table->string('Email');
            $table->string('Status');
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
        Schema::dropIfExists('students');
    }
};
