<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ParticipantFilterRequest;
use App\Models\AssessmentAttempt;
use App\Models\AttemptDimensionScore;
use App\Models\MaturityLevel;
use App\Services\AssessmentReportService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ParticipantController extends Controller
{
    public function index(ParticipantFilterRequest $request): View
    {
        $attempts = $this->filteredAttempts($request->validated())
            ->with('participant', 'assessment')
            ->paginate(15)
            ->withQueryString();

        return view('admin.participants.index', [
            'attempts' => $attempts,
            'levels' => MaturityLevel::query()->where('is_active', true)->orderBy('position')->get(),
        ]);
    }

    public function show(AssessmentAttempt $attempt, AssessmentReportService $reportService): View
    {
        abort_unless($attempt->status === 'completed' && $attempt->total_score !== null, 404);

        $attempt->load([
            'participant',
            'assessment',
            'dimensionScores',
            'answers.question.dimension',
            'answers.option',
        ]);
        $history = $attempt->participant->attempts()
            ->where('status', 'completed')
            ->whereNotNull('total_score')
            ->with('assessment:id,title')
            ->orderByDesc('completed_at')
            ->orderByDesc('id')
            ->get();
        $questionSnapshots = collect($attempt->scoring_snapshot['questions'] ?? [])
            ->keyBy('question_id');
        $reportData = $reportService->data($attempt);
        $emailStatus = match ($attempt->result_email_status) {
            'pending', 'sending' => 'Laporan sedang disiapkan untuk dikirim.',
            'sent' => 'Laporan terkirim pada '.$attempt->result_email_sent_at?->format('d M Y, H:i').'.',
            'failed' => 'Pengiriman gagal. Periksa konfigurasi email dan antrean.',
            'skipped' => 'Laporan tidak dikirim karena data peserta telah dianonimkan.',
            'not_configured' => 'Pengiriman belum aktif. Konfigurasikan mailer SMTP.',
            default => 'Belum ada catatan pengiriman email untuk pengerjaan ini.',
        };
        $emailStatusClass = match ($attempt->result_email_status) {
            'sent' => '',
            'failed', 'not_configured' => 'result-status-needs-work',
            default => 'status-tag-inactive',
        };

        return view('admin.participants.show', [
            'attempt' => $attempt,
            'history' => $history,
            'questionSnapshots' => $questionSnapshots,
            'summary' => $reportData['summary'],
            'recommendations' => $reportData['recommendations'],
            'emailStatus' => $emailStatus,
            'emailStatusClass' => $emailStatusClass,
        ]);
    }

    public function export(ParticipantFilterRequest $request): StreamedResponse
    {
        $query = $this->filteredAttempts($request->validated())
            ->with('participant', 'assessment', 'dimensionScores');
        $dimensions = AttemptDimensionScore::query()
            ->whereHas('attempt', fn (Builder $attemptQuery) => $attemptQuery
                ->where('status', 'completed')
                ->whereNotNull('total_score'))
            ->select('dimension_name_snapshot')
            ->distinct()
            ->orderBy('dimension_name_snapshot')
            ->pluck('dimension_name_snapshot');

        return response()->streamDownload(function () use ($query, $dimensions): void {
            $output = fopen('php://output', 'w');
            if ($output === false) {
                throw new RuntimeException('Tidak dapat membuka stream ekspor CSV.');
            }

            fwrite($output, "\xEF\xBB\xBF");
            $headers = ['ID Pengerjaan', 'Nama', 'Email', 'Asesmen', 'Waktu Selesai', 'Skor Akhir', 'Level'];
            foreach ($dimensions as $dimensionName) {
                $headers[] = 'Skor '.$dimensionName;
            }
            fputcsv($output, array_map($this->safeCsvValue(...), $headers), ',', '"', '');

            $query->reorder()->orderBy('id')->chunkById(500, function ($attempts) use ($output, $dimensions): void {
                foreach ($attempts as $attempt) {
                    $scores = $attempt->dimensionScores->keyBy('dimension_name_snapshot');
                    $row = [
                        $attempt->uuid,
                        $attempt->participant->name,
                        $attempt->participant->email,
                        $attempt->assessment->title,
                        $attempt->completed_at?->format('d/m/Y H:i'),
                        $attempt->total_score,
                        $attempt->maturity_level,
                    ];

                    foreach ($dimensions as $dimensionName) {
                        $row[] = $scores->get($dimensionName)?->score ?? '';
                    }

                    fputcsv($output, array_map($this->safeCsvValue(...), $row), ',', '"', '');
                }
            });

            fclose($output);
        }, 'pelita-hasil-'.now()->format('Ymd-His').'.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function filteredAttempts(array $filters): Builder
    {
        return AssessmentAttempt::query()
            ->where('status', 'completed')
            ->whereNotNull('total_score')
            ->when($filters['search'] ?? null, function (Builder $query, string $search): void {
                $query->whereHas('participant', fn (Builder $participantQuery) => $participantQuery
                    ->where('name', 'like', '%'.$search.'%')
                    ->orWhere('email', 'like', '%'.$search.'%'));
            })
            ->when($filters['level'] ?? null, fn (Builder $query, string $level) => $query
                ->where('maturity_level', $level))
            ->when($filters['date_from'] ?? null, fn (Builder $query, string $date) => $query
                ->whereDate('completed_at', '>=', $date))
            ->when($filters['date_to'] ?? null, fn (Builder $query, string $date) => $query
                ->whereDate('completed_at', '<=', $date))
            ->orderByDesc('completed_at')
            ->orderByDesc('id');
    }

    private function safeCsvValue(mixed $value): string|int|float
    {
        if (! is_string($value)) {
            return $value ?? '';
        }

        if (preg_match('/^[\x00-\x20]*[=+\-@]/', $value) === 1) {
            return "'".$value;
        }

        return $value;
    }
}
