<?php

use App\Support\DuplicateGuard;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DuplicateGuard::assertNone('rekenings', ['bank_rek']);
        Schema::table('rekenings', fn (Blueprint $t) => $t->unique('bank_rek'));
    }

    public function down(): void
    {
        Schema::table('rekenings', fn (Blueprint $t) => $t->dropUnique(['bank_rek']));
    }
};
