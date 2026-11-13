<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrivacySetting extends Model
{
    protected $fillable = [
        'retention_days',
        'contact_name',
        'contact_email',
    ];

    protected function casts(): array
    {
        return [
            'retention_days' => 'integer',
        ];
    }

    public function isConfigured(): bool
    {
        return $this->retention_days !== null
            && filled($this->contact_name)
            && filled($this->contact_email);
    }
}
