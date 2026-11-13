<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttemptDimensionScore extends Model
{
    protected $fillable = [
        'attempt_id',
        'dimension_id',
        'dimension_name_snapshot',
        'score',
        'weight_snapshot',
    ];

    protected function casts(): array
    {
        return [
            'score' => 'decimal:2',
            'weight_snapshot' => 'decimal:2',
        ];
    }

    public function attempt(): BelongsTo
    {
        return $this->belongsTo(AssessmentAttempt::class, 'attempt_id');
    }

    public function dimension(): BelongsTo
    {
        return $this->belongsTo(Dimension::class);
    }
}
