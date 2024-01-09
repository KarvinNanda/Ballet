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
            $table->string('nis')->nullable();
            $table->string('LongName')->nullable();
            $table->string('ShortName')->nullable();
            $table->date('Dob')->nullable();
            $table->date('EnrollDate')->nullable();
            $table->string('bank_rek')->nullable();
            $table->string('nama_orang_tua')->nullable();
            $table->string('Address')->nullable();
            $table->string('City')->nullable();
            $table->string('kode_pos')->nullable();
            $table->string('Phone1')->nullable();
            $table->string('Phone2')->nullable();
            $table->string('Whatsapp')->nullable();
            $table->string('Instagram')->nullable();
            $table->string('Line')->nullable(true);
            $table->string('Email')->nullable();
            $table->string('Status')->nullable();
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
