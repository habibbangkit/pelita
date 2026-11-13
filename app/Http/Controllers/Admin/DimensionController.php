<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Dimension;
use App\Services\AdminActivityLogger;
use App\Services\AssessmentMutationGuard;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DimensionController extends Controller
{
    public function index(): View
    {
        return view('admin.dimensions.index', [
            'dimensions' => Dimension::query()
                ->withCount(['questions as active_questions_count' => fn ($query) => $query->where('is_active', true)])
                ->orderBy('id')
                ->get(),
        ]);
    }

    public function store(
        Request $request,
        AssessmentMutationGuard $guard,
        AdminActivityLogger $activityLogger
    ): RedirectResponse {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'slug' => ['required', 'alpha_dash', 'max:120', 'unique:dimensions,slug'],
            'description' => ['nullable', 'string', 'max:2000'],
        ]);
        $guard->assertNoInProgressAttemptsAnywhere();

        DB::transaction(function () use ($data, $activityLogger): void {
            $dimension = Dimension::create([
                ...$data,
                'weight' => 0,
                'is_active' => false,
            ]);
            $activityLogger->record('dimension.created', $dimension);
        });

        return redirect()->route('admin.dimensions.index')->with('status', 'Dimensi baru ditambahkan dalam keadaan nonaktif.');
    }

    public function edit(Dimension $dimension): View
    {
        return view('admin.dimensions.edit', compact('dimension'));
    }

    public function update(
        Request $request,
        Dimension $dimension,
        AssessmentMutationGuard $guard,
        AdminActivityLogger $activityLogger
    ): RedirectResponse {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'slug' => ['required', 'alpha_dash', 'max:120', Rule::unique('dimensions', 'slug')->ignore($dimension->id)],
            'description' => ['nullable', 'string', 'max:2000'],
        ]);
        $guard->assertNoInProgressAttemptsAnywhere();
        DB::transaction(function () use ($dimension, $data, $activityLogger): void {
            $dimension->update($data);
            $activityLogger->record('dimension.updated', $dimension);
        });

        return redirect()->route('admin.dimensions.index')->with('status', 'Dimensi berhasil diperbarui.');
    }

    public function archive(
        Dimension $dimension,
        AssessmentMutationGuard $guard,
        AdminActivityLogger $activityLogger
    ): RedirectResponse {
        $guard->assertNoInProgressAttemptsAnywhere();

        if ($dimension->questions()->where('is_active', true)->exists()) {
            return back()->withErrors([
                'dimension' => 'Arsipkan pertanyaan aktif yang menggunakan dimensi ini terlebih dahulu.',
            ]);
        }

        if ($dimension->is_active && (float) $dimension->weight > 0) {
            return back()->withErrors([
                'dimension' => 'Atur dimensi menjadi nonaktif dan sesuaikan bobot hingga 100% sebelum mengarsipkannya.',
            ]);
        }

        DB::transaction(function () use ($dimension, $activityLogger): void {
            $dimension->update(['is_active' => false]);
            $activityLogger->record('dimension.archived', $dimension);
        });

        return redirect()->route('admin.dimensions.index')->with('status', 'Dimensi berhasil diarsipkan.');
    }
}
