<?php

namespace App\Jobs;

use App\Mail\AssessmentResultMail;
use App\Models\AssessmentAttempt;
use App\Services\AssessmentReportService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use RuntimeException;
use Throwable;

class SendAssessmentResultEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public array $backoff = [30, 120, 600];

    public function __construct(public int $attemptId) {}

    public function handle(AssessmentReportService $reportService): void
    {
        $attempt = AssessmentAttempt::query()
            ->with(['participant', 'assessment', 'dimensionScores'])
            ->find($this->attemptId);

        if (! $attempt || $attempt->status !== 'completed' || $attempt->total_score === null) {
            return;
        }

        if ($attempt->result_email_status === 'sent' || $attempt->result_email_sent_at !== null) {
            return;
        }

        if ($attempt->participant->is_anonymized) {
            $attempt->update(['result_email_status' => 'skipped']);

            return;
        }

        if (in_array(config('mail.default'), ['log', 'array'], true)) {
            $attempt->update(['result_email_status' => 'not_configured']);
            logger()->warning('Assessment result email was not sent because the mailer is not configured for delivery.', [
                'attempt_id' => $attempt->id,
            ]);

            return;
        }

        if (! filter_var($attempt->participant->email, FILTER_VALIDATE_EMAIL)) {
            throw new RuntimeException('Alamat email peserta tidak valid untuk pengiriman hasil asesmen.');
        }

        $attempt->update(['result_email_status' => 'sending']);

        Mail::to($attempt->participant->email)->send(new AssessmentResultMail(
            $attempt,
            $reportService->pdf($attempt),
            $reportService->filename($attempt)
        ));

        $attempt->update([
            'result_email_status' => 'sent',
            'result_email_sent_at' => now(),
        ]);
    }

    public function failed(?Throwable $exception): void
    {
        AssessmentAttempt::query()
            ->whereKey($this->attemptId)
            ->whereNull('result_email_sent_at')
            ->update(['result_email_status' => 'failed']);

        if ($exception) {
            report($exception);
        }
    }
}
