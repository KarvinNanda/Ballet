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
        Schema::create('header_absens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schedule_id')->references('id')->on('schedules')->onUpdate('cascade')->onDelete('cascade');
            $table->string('is_report');
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
        Schema::dropIfExists('header_absens');
    }
};
