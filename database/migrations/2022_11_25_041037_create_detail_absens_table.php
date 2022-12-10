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
        Schema::create('detail_absens', function (Blueprint $table) {
            $table->foreignId('header_absen_id')->references('id')->on('header_absens')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('student_id')->references('id')->on('students')->onDelete('cascade')->onUpdate('cascade');
            $table->primary(['header_absen_id']);
            $table->string('Description');
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
        Schema::dropIfExists('detail_absens');
    }
};
