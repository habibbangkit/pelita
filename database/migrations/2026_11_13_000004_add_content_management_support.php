<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('question_options', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('is_correct');
        });

        Schema::table('answers', function (Blueprint $table) {
            $table->text('question_text_snapshot')->nullable()->after('score');
            $table->string('dimension_name_snapshot')->nullable()->after('question_text_snapshot');
            $table->string('option_text_snapshot')->nullable()->after('dimension_name_snapshot');
        });

        Schema::create('maturity_levels', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->decimal('minimum_score', 5, 2);
            $table->decimal('maximum_score', 5, 2);
            $table->unsignedInteger('position');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique('position');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maturity_levels');

        Schema::table('answers', function (Blueprint $table) {
            $table->dropColumn([
                'question_text_snapshot',
                'dimension_name_snapshot',
                'option_text_snapshot',
            ]);
        });

        Schema::table('question_options', function (Blueprint $table) {
            $table->dropColumn('is_active');
        });
    }
};
