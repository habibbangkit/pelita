<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminActivityLog;
use Illuminate\View\View;

class AdminActivityLogController extends Controller
{
    public function index(): View
    {
        $actionLabels = [
            'admin.login' => 'Masuk admin',
            'admin.logout' => 'Keluar admin',
            'question.created' => 'Menambahkan pertanyaan',
            'question.updated' => 'Memperbarui pertanyaan',
            'question.archived' => 'Mengarsipkan pertanyaan',
            'question.reordered' => 'Mengubah urutan pertanyaan',
            'assessment.published' => 'Mempublikasikan asesmen',
            'assessment.unpublished' => 'Membatalkan publikasi asesmen',
            'dimension.created' => 'Menambahkan dimensi',
            'dimension.updated' => 'Memperbarui dimensi',
            'dimension.archived' => 'Mengarsipkan dimensi',
            'privacy.updated' => 'Memperbarui kebijakan privasi',
            'scoring.updated' => 'Memperbarui pengaturan penilaian',
        ];

        return view('admin.activity-logs.index', [
            'logs' => AdminActivityLog::query()
                ->with('user:id,name')
                ->orderByDesc('id')
                ->paginate(50),
            'actionLabels' => $actionLabels,
        ]);
    }
}
