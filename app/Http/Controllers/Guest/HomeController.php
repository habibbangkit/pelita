<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Models\Assessment;
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
            'description' => match ($level->name) {
                'Aware' => 'Tahap awal dalam mengenal dan memahami teknologi AI.',
                'Adopter' => 'Sudah mulai memahami dan menggunakan AI untuk membantu berbagai aktivitas.',
                'Transformer' => 'Memiliki pemahaman dan kemampuan yang baik dalam memanfaatkan AI secara produktif dan bertanggung jawab.',
                default => 'Tahap perkembangan dalam memahami dan memanfaatkan teknologi AI.',
            },
        ]);

        return view('guest.home', compact('assessment', 'privacySettings', 'maturityLevels'));
    }
}
