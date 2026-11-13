<?php

namespace Database\Seeders;

use App\Models\PrivacySetting;
use Illuminate\Database\Seeder;

class PrivacySettingsSeeder extends Seeder
{
    public function run(): void
    {
        PrivacySetting::query()->firstOrCreate(
            ['id' => 1],
            [
                'contact_name' => 'admin',
                'contact_email' => 'admin@gmail.com',
                'retention_days' => null,
            ]
        );
    }
}
