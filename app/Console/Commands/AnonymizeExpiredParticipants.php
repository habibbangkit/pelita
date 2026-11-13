<?php

namespace App\Console\Commands;

use App\Models\Participant;
use App\Models\PrivacySetting;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class AnonymizeExpiredParticipants extends Command
{
    protected $signature = 'pelita:anonymize-expired-participants';

    protected $description = 'Anonymize participant identities after the configured retention period';

    public function handle(): int
    {
        $settings = PrivacySetting::query()->find(1);
        if (! $settings?->isConfigured()) {
            $this->error('Pengaturan retensi dan kontak privasi harus disimpan terlebih dahulu.');

            return self::FAILURE;
        }

        $cutoff = now()->subDays($settings->retention_days);
        $anonymizedCount = 0;

        Participant::query()
            ->where('is_anonymized', false)
            ->whereHas('attempts', fn (Builder $query) => $query
                ->where('status', 'completed')
                ->whereNotNull('completed_at')
                ->where('completed_at', '<=', $cutoff))
            ->whereDoesntHave('attempts', fn (Builder $query) => $query->where(function (Builder $attemptQuery) use ($cutoff) {
                $attemptQuery
                    ->where('status', 'in_progress')
                    ->orWhereNull('completed_at')
                    ->orWhere('completed_at', '>', $cutoff);
            }))
            ->orderBy('id')
            ->chunkById(100, function ($participants) use (&$anonymizedCount): void {
                foreach ($participants as $participant) {
                    DB::transaction(function () use ($participant, &$anonymizedCount): void {
                        $participant->update([
                            'name' => 'Peserta anonim #'.$participant->id,
                            'email' => 'peserta-'.$participant->id.'@anonim.invalid',
                            'is_anonymized' => true,
                            'anonymized_at' => now(),
                        ]);
                        $anonymizedCount++;
                    });
                }
            });

        $this->info("Identitas peserta dianonimkan: {$anonymizedCount}.");

        return self::SUCCESS;
    }
}
