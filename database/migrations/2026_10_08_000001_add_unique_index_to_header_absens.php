<?php

use App\Support\DuplicateGuard;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DuplicateGuard::assertNone('header_absens', ['schedules_id']);
        Schema::table('header_absens', fn (Blueprint $t) => $t->unique('schedules_id'));
    }

    public function down(): void
    {
        Schema::table('header_absens', fn (Blueprint $t) => $t->dropUnique(['schedules_id']));
    }
};
