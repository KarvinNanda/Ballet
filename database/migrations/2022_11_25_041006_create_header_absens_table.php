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
            $table->id('HeaderAbsenId');
            $table->foreignId('ScheduleId')->references('ScheduleId')->on('schedules')->onUpdate('cascade')->onDelete('cascade');
            $table->string('is_report');
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
