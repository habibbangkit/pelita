<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assessment_attempts', function (Blueprint $table) {
            $table->string('result_email_status')->nullable()->after('recommendation_snapshot');
            $table->timestamp('result_email_sent_at')->nullable()->after('result_email_status');
        });
    }

    public function down(): void
    {
        Schema::table('assessment_attempts', function (Blueprint $table) {
            $table->dropColumn(['result_email_status', 'result_email_sent_at']);
        });
    }
};
