@extends('layouts.app')

@section('title', 'Data Peserta')

@section('content')
    <section class="card">
        <p class="eyebrow">Administrator</p>
        <h1 style="font-size: 2rem">Data asesmen peserta</h1>
        <p class="muted">Pencarian menampilkan pengerjaan asesmen yang telah selesai.</p>

        <form method="GET" action="{{ route('admin.participants.index') }}">
            <div class="grid">
                <div class="field">
                    <label for="search">Nama atau email</label>
                    <input id="search" name="search" type="text" value="{{ request('search') }}" maxlength="100">
                </div>
                <div class="field">
                    <label for="level">Tingkat kematangan</label>
                    <select id="level" name="level" style="width: 100%; min-height: 46px; border: 1px solid #cbd5df; border-radius: 9px; padding: 10px 12px; font: inherit">
                        <option value="">Semua level</option>
                        @foreach ($levels as $level)
                            <option value="{{ $level->name }}" @selected(request('level') === $level->name)>{{ $level->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label for="date_from">Selesai dari</label>
                    <input id="date_from" name="date_from" type="date" value="{{ request('date_from') }}">
                </div>
                <div class="field">
                    <label for="date_to">Selesai sampai</label>
                    <input id="date_to" name="date_to" type="date" value="{{ request('date_to') }}">
                </div>
            </div>
            @if ($errors->any())
                <div class="error" role="alert">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif
            <div class="actions">
                <button class="button" type="submit">Terapkan filter</button>
                <a class="button button-secondary" href="{{ route('admin.participants.export', request()->query()) }}">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3v12m0 0 4-4m-4 4-4-4M5 17v3h14v-3"/></svg>
                    Unduh PDF
                </a>
            </div>
        </form>

        <div style="overflow-x: auto; margin-top: 24px">
            <table style="width: 100%; border-collapse: collapse; text-align: left">
                <thead>
                    <tr>
                        <th style="padding: 10px">Peserta</th>
                        <th style="padding: 10px">Email</th>
                        <th style="padding: 10px">Selesai</th>
                        <th style="padding: 10px">Skor</th>
                        <th style="padding: 10px">Level</th>
                        <th style="padding: 10px"><span class="sr-only">Detail</span></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($attempts as $attempt)
                        <tr>
                            <td style="padding: 10px">{{ $attempt->participant->name }}</td>
                            <td style="padding: 10px">{{ $attempt->participant->email }}</td>
                            <td style="padding: 10px">{{ $attempt->completed_at->format('d M Y H:i') }}</td>
                            <td style="padding: 10px">{{ number_format((float) $attempt->total_score, 2) }}</td>
                            <td style="padding: 10px">{{ $attempt->maturity_level }}</td>
                            <td style="padding: 10px"><a href="{{ route('admin.participants.show', $attempt) }}">Detail</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" style="padding: 18px" class="muted">Tidak ada hasil yang sesuai.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div style="margin-top: 20px">{{ $attempts->links() }}</div>
    </section>
@endsection
