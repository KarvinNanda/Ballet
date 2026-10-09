<?php

use App\Support\DuplicateGuard;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DuplicateGuard::assertNone('mapping_class_children', ['class_id', 'student_id']);
        Schema::table('mapping_class_children', fn (Blueprint $t) => $t->unique(['class_id', 'student_id']));
    }

    public function down(): void
    {
        Schema::table('mapping_class_children', fn (Blueprint $t) => $t->dropUnique(['class_id', 'student_id']));
    }
};
