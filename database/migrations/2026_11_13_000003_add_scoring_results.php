<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assessment_attempts', function (Blueprint $table) {
            $table->decimal('total_score', 5, 2)->nullable()->after('completed_at');
            $table->string('maturity_level')->nullable()->after('total_score');
            $table->json('scoring_snapshot')->nullable()->after('maturity_level');
            $table->json('recommendation_snapshot')->nullable()->after('scoring_snapshot');
        });

        Schema::table('answers', function (Blueprint $table) {
            $table->decimal('score', 5, 2)->nullable()->after('question_option_id');
        });

        Schema::create('attempt_dimension_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attempt_id')->constrained('assessment_attempts')->cascadeOnDelete();
            $table->foreignId('dimension_id')->constrained()->restrictOnDelete();
            $table->string('dimension_name_snapshot');
            $table->decimal('score', 5, 2);
            $table->decimal('weight_snapshot', 5, 2);
            $table->timestamps();
            $table->unique(['attempt_id', 'dimension_id']);
        });

        Schema::create('recommendations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dimension_id')->constrained()->restrictOnDelete();
            $table->decimal('minimum_score', 5, 2);
            $table->decimal('maximum_score', 5, 2);
            $table->string('title');
            $table->text('description');
            $table->string('learning_url')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(
                ['dimension_id', 'is_active', 'minimum_score', 'maximum_score'],
                'recommendations_score_lookup_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recommendations');
        Schema::dropIfExists('attempt_dimension_scores');

        Schema::table('answers', function (Blueprint $table) {
            $table->dropColumn('score');
        });

        Schema::table('assessment_attempts', function (Blueprint $table) {
            $table->dropColumn([
                'total_score',
                'maturity_level',
                'scoring_snapshot',
                'recommendation_snapshot',
            ]);
        });
    }
};
