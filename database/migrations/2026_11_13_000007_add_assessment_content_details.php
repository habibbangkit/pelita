<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assessments', function (Blueprint $table) {
            $table->unsignedSmallInteger('duration_minutes')->nullable();
        });

        Schema::table('questions', function (Blueprint $table) {
            $table->text('explanation')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->dropColumn('explanation');
        });

        Schema::table('assessments', function (Blueprint $table) {
            $table->dropColumn('duration_minutes');
        });
    }
};
