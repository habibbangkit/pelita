@extends('layouts.app')

@section('title', 'Pengaturan Privasi')
@section('topbar_eyebrow', 'Konfigurasi')
@section('topbar_title', 'Privasi dan retensi data')

@section('content')
    <section class="card">
        <p class="eyebrow">Perlindungan data peserta</p>
        <h1 style="font-size: 2rem">Pengaturan privasi</h1>
        <p class="muted">Identitas peserta akan dianonimkan setelah masa retensi dihitung dari asesmen terakhir yang selesai. Skor dan jawaban tetap disimpan tanpa nama dan email. Peserta yang masih memiliki asesmen berjalan tidak dianonimkan.</p>

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

        @if ($privacySettings?->isConfigured())
            <div class="notice" role="status">
                Kebijakan aktif: identitas dianonimkan setelah {{ $privacySettings->retention_days }} hari.
                Kontak: {{ $privacySettings->contact_name }} ({{ $privacySettings->contact_email }}).
            </div>
        @else
            <div class="error" role="status">Pengaturan ini wajib dilengkapi sebelum asesmen dapat dipublikasikan.</div>
        @endif

        <form method="POST" action="{{ route('admin.privacy-settings.update') }}">
            @csrf
            @method('PUT')
            <div class="grid">
                <div class="field">
                    <label for="retention_days">Masa retensi identitas peserta (hari)</label>
                    <input id="retention_days" name="retention_days" type="number" min="1" max="36500" step="1" value="{{ old('retention_days', $privacySettings?->retention_days) }}" required>
                    <small class="muted">Penghitung dimulai setelah asesmen terakhir peserta selesai. Tidak ada nilai bawaan.</small>
                </div>
                <div class="field">
                    <label for="contact_name">Nama/unit pengelola data</label>
                    <input id="contact_name" name="contact_name" type="text" maxlength="120" value="{{ old('contact_name', $privacySettings?->contact_name) }}" required>
                </div>
                <div class="field">
                    <label for="contact_email">Email kontak privasi</label>
                    <input id="contact_email" name="contact_email" type="email" maxlength="255" value="{{ old('contact_email', $privacySettings?->contact_email) }}" required>
                </div>
            </div>
            <button class="button" type="submit">Simpan kebijakan privasi</button>
        </form>
    </section>
@endsection
