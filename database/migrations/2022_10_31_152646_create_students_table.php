<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStudentsTable extends Migration
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
            $table->string('nama_panjang');
            $table->string('nama_panggilan');
            $table->date('tanggal_lahir');
            $table->unsignedBigInteger('class_id');
            $table->date('tanggal_daftar');
            $table->unsignedBigInteger('bank_id');
            $table->string('nama_bank_pengirim');
            $table->string('nama_orang_tua');
            $table->string('alamat');
            $table->string('kota');
            $table->string('kode_pos');
            $table->string('no_telp1');
            $table->string('no_telp2');
            $table->string('whatsapp');
            $table->string('instagram');
            $table->string('email');
            $table->string('status');

            $table->foreign('class_id')->references('id')->on('kelas')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('bank_id')->references('id')->on('banks')->onUpdate('cascade')->onDelete('cascade');

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
}
