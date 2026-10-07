<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
| Rule content holds rich text (accordion markup); varchar(255) cut it off silently
| while MySQL ran without strict mode. Widening keeps every existing value.
*/
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rules', function (Blueprint $table) {
            $table->text('content')->nullable()->change();
        });
    }

    public function down(): void
    {
        // Narrowing would fail (strict mode) or cut text, so content longer than 255 is not restored.
        Schema::table('rules', function (Blueprint $table) {
            $table->string('content')->nullable()->change();
        });
    }
};
