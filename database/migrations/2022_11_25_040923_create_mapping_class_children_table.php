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
        Schema::create('mapping_class_children', function (Blueprint $table) {
            $table->foreignId('class_id')->references('id')->on('class_transactions')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('student_id')->references('id')->on('students')->onUpdate('cascade')->onDelete('cascade');
            $table->primary(['class_id', 'student_id']);
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
        Schema::dropIfExists('mapping_class_children');
    }
};
