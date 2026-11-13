<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AssessmentAttempt;
use App\Models\MaturityLevel;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $completed = AssessmentAttempt::query()
            ->where('status', 'completed')
            ->whereNotNull('total_score');
        $summary = (clone $completed)
            ->join('participants', 'participants.id', '=', 'assessment_attempts.participant_id')
            ->selectRaw('COUNT(*) as total_attempts, COUNT(DISTINCT participants.email) as total_participants, AVG(total_score) as average_score')
            ->first();

        $counts = (clone $completed)
            ->select('maturity_level', DB::raw('COUNT(*) as aggregate'))
            ->groupBy('maturity_level')
            ->pluck('aggregate', 'maturity_level');

        $levels = MaturityLevel::query()
            ->where('is_active', true)
            ->orderBy('position')
            ->get()
            ->map(fn (MaturityLevel $level) => [
                'name' => $level->name,
                'count' => (int) $counts->get($level->name, 0),
            ]);

        $latestAttempts = (clone $completed)
            ->with('participant')
            ->latest('completed_at')
            ->limit(8)
            ->get();

        return view('admin.dashboard', [
            'totalParticipants' => (int) ($summary->total_participants ?? 0),
            'totalAttempts' => (int) ($summary->total_attempts ?? 0),
            'averageScore' => $summary->average_score !== null
                ? number_format((float) $summary->average_score, 2)
                : null,
            'levels' => $levels,
            'latestAttempts' => $latestAttempts,
        ]);
    }
}
