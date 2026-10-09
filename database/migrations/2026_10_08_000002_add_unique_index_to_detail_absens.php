<?php

use App\Support\DuplicateGuard;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DuplicateGuard::assertNone('detail_absens', ['header_absen_id', 'student_id']);
        Schema::table('detail_absens', fn (Blueprint $t) => $t->unique(['header_absen_id', 'student_id']));
    }

    public function down(): void
    {
        Schema::table('detail_absens', fn (Blueprint $t) => $t->dropUnique(['header_absen_id', 'student_id']));
    }
};
