@extends('layouts.app')

@section('title', 'Pengaturan Penilaian')

@section('content')
    <section class="card">
        <p class="eyebrow">Konfigurasi</p>
        <h1 style="font-size: 2rem">Pengaturan penilaian</h1>
        <p class="muted">Total bobot dimensi aktif harus 100%. Rentang level harus berurutan dari 0 sampai 100. Pengaturan terkunci selama peserta sedang mengerjakan asesmen.</p>

        @if (session('status'))
            <div class="notice" role="status">{{ session('status') }}</div>
        @endif
        @if ($errors->any())
            <div class="error" role="alert">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('admin.scoring-settings.update') }}">
            @csrf
            @method('PUT')
            <h2>Dimensi dan bobot</h2>
            @foreach ($dimensions as $dimension)
                <div class="grid" style="align-items: end; padding: 12px 0; border-top: 1px solid #e3eaf0">
                    <div>
                        <strong>{{ $dimension->name }}</strong>
                        <input type="hidden" name="dimensions[{{ $dimension->id }}][is_active]" value="0">
                        <label><input type="checkbox" name="dimensions[{{ $dimension->id }}][is_active]" value="1" @checked((bool) old("dimensions.{$dimension->id}.is_active", $dimension->is_active))> Aktif</label>
                    </div>
                    <div class="field">
                        <label for="dimension-{{ $dimension->id }}">Bobot (%)</label>
                        <input id="dimension-{{ $dimension->id }}" type="number" name="dimensions[{{ $dimension->id }}][weight]" value="{{ old("dimensions.{$dimension->id}.weight", $dimension->weight) }}" min="0" max="100" step="0.01" required>
                    </div>
                </div>
            @endforeach

            <h2 style="margin-top: 28px">Rentang tingkat kematangan</h2>
            @foreach ($levels as $level)
                <div class="grid" style="align-items: end; padding: 12px 0; border-top: 1px solid #e3eaf0">
                    <strong>{{ $level->name }}</strong>
                    <div class="grid">
                        <div class="field">
                            <label for="level-{{ $level->id }}-min">Skor minimum</label>
                            <input id="level-{{ $level->id }}-min" type="number" name="levels[{{ $level->id }}][minimum_score]" value="{{ old("levels.{$level->id}.minimum_score", $level->minimum_score) }}" min="0" max="100" step="0.01" required>
                        </div>
                        <div class="field">
                            <label for="level-{{ $level->id }}-max">Skor maksimum</label>
                            <input id="level-{{ $level->id }}-max" type="number" name="levels[{{ $level->id }}][maximum_score]" value="{{ old("levels.{$level->id}.maximum_score", $level->maximum_score) }}" min="0" max="100" step="0.01" required>
                        </div>
                    </div>
                </div>
            @endforeach

            <button class="button" type="submit">Simpan pengaturan</button>
        </form>
    </section>
@endsection
