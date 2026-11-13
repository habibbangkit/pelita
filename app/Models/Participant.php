<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Participant extends Model
{
    protected $fillable = [
        'name',
        'email',
        'is_anonymized',
        'anonymized_at',
    ];

    protected function casts(): array
    {
        return [
            'is_anonymized' => 'boolean',
            'anonymized_at' => 'datetime',
        ];
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(AssessmentAttempt::class);
    }
}
