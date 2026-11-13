<?php

use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AdminActivityLogController;
use App\Http\Controllers\Admin\AssessmentManagementController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DimensionController;
use App\Http\Controllers\Admin\ParticipantController;
use App\Http\Controllers\Admin\PrivacySettingsController;
use App\Http\Controllers\Admin\QuestionController;
use App\Http\Controllers\Admin\ScoringSettingsController;
use App\Http\Controllers\Guest\AssessmentController;
use App\Http\Controllers\Guest\HomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::post('/assessment/start', [AssessmentController::class, 'start'])
    ->middleware('throttle:10,1')
    ->name('assessment.start');
Route::get('/assessment/{attempt:uuid}', [AssessmentController::class, 'show'])
    ->name('assessment.show');
Route::post('/assessment/{attempt:uuid}/answer', [AssessmentController::class, 'saveAnswer'])
    ->middleware('throttle:60,1')
    ->name('assessment.answer');
Route::get('/assessment/{attempt:uuid}/complete', [AssessmentController::class, 'complete'])
    ->name('assessment.complete');
Route::get('/assessment/{attempt:uuid}/download', [AssessmentController::class, 'download'])
    ->name('assessment.download');

Route::get('/admin/login', [AdminAuthController::class, 'showLogin'])->name('admin.login');
Route::post('/admin/login', [AdminAuthController::class, 'login'])
    ->middleware('throttle:5,1')
    ->name('admin.login.submit');
Route::post('/admin/logout', [AdminAuthController::class, 'logout'])
    ->middleware(['auth', 'admin'])
    ->name('admin.logout');

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/activity-logs', [AdminActivityLogController::class, 'index'])->name('activity-logs.index');
    Route::get('/participants', [ParticipantController::class, 'index'])->name('participants.index');
    Route::get('/participants/export', [ParticipantController::class, 'export'])->name('participants.export');
    Route::get('/participants/{attempt:uuid}', [ParticipantController::class, 'show'])->name('participants.show');

    Route::get('/questions', [QuestionController::class, 'index'])->name('questions.index');
    Route::get('/questions/create', [QuestionController::class, 'create'])->name('questions.create');
    Route::post('/questions', [QuestionController::class, 'store'])->name('questions.store');
    Route::get('/questions/{question}/edit', [QuestionController::class, 'edit'])->name('questions.edit');
    Route::put('/questions/{question}', [QuestionController::class, 'update'])->name('questions.update');
    Route::patch('/questions/{question}/archive', [QuestionController::class, 'archive'])->name('questions.archive');
    Route::patch('/questions/{question}/move', [QuestionController::class, 'move'])->name('questions.move');

    Route::post('/assessments/{assessment}/publish', [AssessmentManagementController::class, 'publish'])
        ->name('assessments.publish');
    Route::patch('/assessments/{assessment}/unpublish', [AssessmentManagementController::class, 'unpublish'])
        ->name('assessments.unpublish');

    Route::get('/dimensions', [DimensionController::class, 'index'])->name('dimensions.index');
    Route::post('/dimensions', [DimensionController::class, 'store'])->name('dimensions.store');
    Route::get('/dimensions/{dimension}/edit', [DimensionController::class, 'edit'])->name('dimensions.edit');
    Route::put('/dimensions/{dimension}', [DimensionController::class, 'update'])->name('dimensions.update');
    Route::patch('/dimensions/{dimension}/archive', [DimensionController::class, 'archive'])->name('dimensions.archive');

    Route::get('/scoring-settings', [ScoringSettingsController::class, 'index'])->name('scoring-settings.index');
    Route::put('/scoring-settings', [ScoringSettingsController::class, 'update'])->name('scoring-settings.update');
    Route::get('/privacy-settings', [PrivacySettingsController::class, 'index'])->name('privacy-settings.index');
    Route::put('/privacy-settings', [PrivacySettingsController::class, 'update'])->name('privacy-settings.update');
});
