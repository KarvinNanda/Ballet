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
            $table->foreignId('HeaderAbsenId')->references('HeaderAbsenId')->on('header_absens')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('ClassId')->references('ClassId')->on('class_transactions')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('StudentId')->references('StudentId')->on('students')->onDelete('cascade')->onUpdate('cascade');
            $table->primary(['ClassId','HeaderAbsenId']);
            $table->string('Description');
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
