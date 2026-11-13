<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Recommendation extends Model
{
    protected $fillable = [
        'dimension_id',
        'minimum_score',
        'maximum_score',
        'title',
        'description',
        'learning_url',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'minimum_score' => 'decimal:2',
            'maximum_score' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function dimension(): BelongsTo
    {
        return $this->belongsTo(Dimension::class);
    }
}
