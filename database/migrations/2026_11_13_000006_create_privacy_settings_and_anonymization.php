<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('participants', function (Blueprint $table) {
            $table->boolean('is_anonymized')->default(false);
            $table->timestamp('anonymized_at')->nullable();
        });

        Schema::create('privacy_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('retention_days')->nullable();
            $table->string('contact_name')->nullable();
            $table->string('contact_email')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('privacy_settings');

        Schema::table('participants', function (Blueprint $table) {
            $table->dropColumn(['is_anonymized', 'anonymized_at']);
        });
    }
};
