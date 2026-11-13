<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PrivacySetting;
use App\Services\AdminActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PrivacySettingsController extends Controller
{
    public function index(): View
    {
        return view('admin.privacy-settings', [
            'privacySettings' => PrivacySetting::query()->find(1),
        ]);
    }

    public function update(Request $request, AdminActivityLogger $activityLogger): RedirectResponse
    {
        $data = $request->validate([
            'retention_days' => ['required', 'integer', 'between:1,36500'],
            'contact_name' => ['required', 'string', 'max:120'],
            'contact_email' => ['required', 'email', 'max:255'],
        ]);
        $data['contact_email'] = strtolower(trim($data['contact_email']));

        DB::transaction(function () use ($data, $activityLogger): void {
            $settings = PrivacySetting::query()->updateOrCreate(['id' => 1], $data);
            $activityLogger->record('privacy.updated', $settings);
        });

        return redirect()
            ->route('admin.privacy-settings.index')
            ->with('status', 'Pengaturan privasi dan retensi berhasil disimpan.');
    }
}
