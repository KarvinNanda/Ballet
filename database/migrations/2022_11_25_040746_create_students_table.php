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
            $table->id('StudentId');
            $table->string('nis');
            $table->string('LongName');
            $table->string('ShortName');
            $table->date('Dob');
            $table->date('EnrollDate');
            $table->integer('Bank_rek');
            $table->string('nama_bank_pengirim');
            $table->string('nama_orang_tua');
            $table->string('Address');
            $table->string('City');
            $table->string('kode_pos');
            $table->string('Phone1');
            $table->string('Phone2');
            $table->string('Whatsapp');
            $table->string('Instagram');
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
