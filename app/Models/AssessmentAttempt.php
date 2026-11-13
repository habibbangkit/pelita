<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AssessmentAttempt extends Model
{
    protected $fillable = [
        'uuid',
        'participant_id',
        'assessment_id',
        'status',
        'started_at',
        'completed_at',
        'total_score',
        'maturity_level',
        'scoring_snapshot',
        'recommendation_snapshot',
        'result_email_status',
        'result_email_sent_at',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'total_score' => 'decimal:2',
            'scoring_snapshot' => 'array',
            'recommendation_snapshot' => 'array',
            'result_email_sent_at' => 'datetime',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function participant(): BelongsTo
    {
        return $this->belongsTo(Participant::class);
    }

    public function assessment(): BelongsTo
    {
        return $this->belongsTo(Assessment::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(Answer::class, 'attempt_id');
    }

    public function dimensionScores(): HasMany
    {
        return $this->hasMany(AttemptDimensionScore::class, 'attempt_id');
    }
}
