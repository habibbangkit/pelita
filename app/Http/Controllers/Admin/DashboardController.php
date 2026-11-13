<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AdminAssessmentSummaryService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(AdminAssessmentSummaryService $summaryService): View
    {
        return view('admin.dashboard', $summaryService->data());
    }

    public function export(AdminAssessmentSummaryService $summaryService): Response
    {
        return Pdf::loadView('admin.reports.dashboard-pdf', $summaryService->data())
            ->setPaper('a4', 'landscape')
            ->download('pelita-rekapitulasi-'.now()->format('Ymd-His').'.pdf');
    }
}
