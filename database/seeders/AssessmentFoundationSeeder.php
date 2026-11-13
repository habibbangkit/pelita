<?php

namespace Database\Seeders;

use App\Models\Assessment;
use App\Models\Dimension;
use App\Models\MaturityLevel;
use Illuminate\Database\Seeder;

class AssessmentFoundationSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            ['name' => 'Literasi Dasar', 'slug' => 'literasi-dasar', 'weight' => 25],
            ['name' => 'Keterampilan Praktis', 'slug' => 'keterampilan-praktis', 'weight' => 35],
            ['name' => 'Etika & Keamanan', 'slug' => 'etika-keamanan', 'weight' => 20],
            ['name' => 'Kesiapan Infrastruktur', 'slug' => 'kesiapan-infrastruktur', 'weight' => 20],
        ] as $dimension) {
            Dimension::firstOrCreate(
                ['slug' => $dimension['slug']],
                [...$dimension, 'is_active' => true]
            );
        }

        $assessment = Assessment::query()
            ->where('title', 'PELITA - AI Literacy & Readiness Assessment')
            ->first();

        if (! $assessment) {
            $assessment = Assessment::query()
                ->where('title', 'Asesmen Kesiapan AI PELITA')
                ->first();
        }

        $description = 'Asesmen ini mengukur pemahaman, keterampilan penggunaan, kesadaran etika, dan kesiapan individu dalam mengadopsi teknologi AI. Hasilnya membantu menentukan tingkat kematangan literasi AI dan rekomendasi pembelajaran.';

        if (! $assessment) {
            Assessment::create([
                'title' => 'PELITA - AI Literacy & Readiness Assessment',
                'description' => $description,
                'status' => 'draft',
            ]);
        } elseif ($assessment->title === 'Asesmen Kesiapan AI PELITA' && $assessment->status !== 'published') {
            $assessment->update([
                'title' => 'PELITA - AI Literacy & Readiness Assessment',
                'description' => $description,
            ]);
        }

        foreach (config('pelita.maturity_levels') as $position => $level) {
            MaturityLevel::firstOrCreate(
                ['name' => $level['name']],
                [
                    'minimum_score' => $level['min_score'],
                    'maximum_score' => $level['max_score'],
                    'position' => $position + 1,
                    'is_active' => true,
                ]
            );
        }
    }
}
