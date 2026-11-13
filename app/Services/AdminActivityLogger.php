<?php

namespace App\Services;

use App\Models\AdminActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use LogicException;

class AdminActivityLogger
{
    public function record(string $action, ?Model $subject = null): AdminActivityLog
    {
        $user = Auth::user();
        if (! $user || ! $user->is_admin) {
            throw new LogicException('Admin activity logs require an authenticated administrator.');
        }

        return AdminActivityLog::query()->create([
            'user_id' => $user->getAuthIdentifier(),
            'action' => $action,
            'subject_type' => $subject ? class_basename($subject) : null,
            'subject_id' => $subject?->getKey(),
        ]);
    }
}
