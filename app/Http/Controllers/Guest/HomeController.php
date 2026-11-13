<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Models\Dimension;
use App\Models\MaturityLevel;
use App\Models\PrivacySetting;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $assessment = Assessment::query()
            ->where('status', 'published')
            ->withCount(['questions' => fn ($query) => $query->where('is_active', true)])
            ->whereHas('questions', fn ($query) => $query->where('is_active', true))
            ->whereDoesntHave('questions', fn ($query) => $query
                ->where('is_active', true)
                ->whereDoesntHave('options', fn ($options) => $options->where('is_active', true)))
            ->latest('published_at')
            ->first();

        $privacySettings = PrivacySetting::query()->find(1);
        $dimensionWeights = [
            'literasi-dasar' => 25.0,
            'keterampilan-praktis' => 35.0,
            'etika-keamanan' => 20.0,
            'kesiapan-infrastruktur' => 20.0,
        ];
        $dimensionWeights = array_merge(
            $dimensionWeights,
            Dimension::query()
                ->where('is_active', true)
                ->get()
                ->mapWithKeys(fn (Dimension $dimension) => [$dimension->slug => (float) $dimension->weight])
                ->all()
        );
        $levels = MaturityLevel::query()
            ->where('is_active', true)
            ->orderBy('position')
            ->get();

        if ($levels->isEmpty()) {
            $levels = collect(config('pelita.maturity_levels'))
                ->map(fn (array $level) => new MaturityLevel([
                    'name' => $level['name'],
                    'minimum_score' => $level['min_score'],
                    'maximum_score' => $level['max_score'],
                ]));
        }

        $maturityLevels = $levels->map(fn (MaturityLevel $level) => [
            'name' => $level->name,
            'range' => (int) ceil((float) $level->minimum_score).'–'.(int) floor((float) $level->maximum_score),
            'stage' => match ($level->name) {
                'Aware' => 'Tahap Pengenalan',
                'Adopter' => 'Tahap Penerapan',
                'Transformer' => 'Tahap Optimalisasi',
                default => 'Tahap Perkembangan',
            },
            'description' => match ($level->name) {
                'Aware' => 'Peserta berada pada tahap awal dalam memahami teknologi Artificial Intelligence. Pemahaman dan keterampilan penggunaan AI masih perlu dikembangkan.',
                'Adopter' => 'Peserta telah memiliki pemahaman dasar dan mulai memanfaatkan teknologi AI untuk membantu berbagai aktivitas.',
                'Transformer' => 'Peserta memiliki tingkat literasi dan kesiapan AI yang baik serta mampu memanfaatkan teknologi tersebut secara lebih efektif dan bertanggung jawab.',
                default => 'Tingkat pemahaman dan kesiapan peserta berdasarkan hasil asesmen.',
            },
            'focus' => match ($level->name) {
                'Aware' => [
                    'Mengenal konsep dasar AI.',
                    'Memahami manfaat dan fungsi AI.',
                    'Mulai mencoba berbagai teknologi AI.',
                    'Mempelajari prinsip penggunaan AI yang aman.',
                ],
                'Adopter' => [
                    'Meningkatkan keterampilan penggunaan AI.',
                    'Mengembangkan kemampuan membuat prompt.',
                    'Memanfaatkan AI untuk meningkatkan produktivitas.',
                    'Memahami etika dan keamanan penggunaan AI.',
                ],
                'Transformer' => [
                    'Mengoptimalkan penggunaan AI.',
                    'Mengintegrasikan AI dalam aktivitas produktif.',
                    'Mengevaluasi hasil yang diberikan AI.',
                    'Mengembangkan inovasi berbasis teknologi AI.',
                ],
                default => [
                    'Tinjau rekomendasi berdasarkan setiap dimensi.',
                    'Terus kembangkan literasi dan kesiapan AI.',
                ],
            },
        ]);

        return view('guest.home', compact('assessment', 'privacySettings', 'dimensionWeights', 'maturityLevels'));
    }
}
