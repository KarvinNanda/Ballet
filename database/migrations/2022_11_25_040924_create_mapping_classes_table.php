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
        Schema::create('mapping_classes', function (Blueprint $table) {
            $table->foreignId('ClassId')->references('ClassId')->on('class_transactions')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('TeacherId')->references('TeacherId')->on('teachers')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('StudentId')->references('StudentId')->on('students')->onUpdate('cascade')->onDelete('cascade');
            $table->primary(['ClassId', 'StudentId','TeacherId']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mapping_classes');
    }
};
