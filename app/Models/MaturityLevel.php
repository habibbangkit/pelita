<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaturityLevel extends Model
{
    protected $fillable = [
        'name',
        'minimum_score',
        'maximum_score',
        'position',
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
}
