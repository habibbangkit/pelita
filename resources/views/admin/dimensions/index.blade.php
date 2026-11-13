@extends('layouts.app')

@section('title', 'Manajemen Dimensi')

@section('content')
    <section class="card management-page">
        <div class="page-heading">
            <div>
                <p class="eyebrow">Manajemen instrumen</p>
                <h1>Dimensi penilaian</h1>
                <p class="muted">Kelola dimensi yang digunakan untuk mengelompokkan dan menilai kompetensi peserta.</p>
            </div>
        </div>
        <div class="notice">Bobot dan status aktif diatur pada halaman Pengaturan Penilaian.</div>

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

        <p class="table-scroll-hint">Geser tabel ke samping untuk melihat kolom lainnya.</p>
        <div class="table-wrap management-table-wrap">
            <table class="management-table dimension-table">
                <thead><tr><th>Nama</th><th>Slug</th><th>Bobot</th><th>Status</th><th>Pertanyaan aktif</th><th><span class="sr-only">Tindakan</span></th></tr></thead>
                <tbody>
                    @foreach ($dimensions as $dimension)
                        <tr>
                            <td><strong>{{ $dimension->name }}</strong></td>
                            <td class="dimension-slug">{{ $dimension->slug }}</td>
                            <td class="dimension-weight">{{ number_format((float) $dimension->weight, 2) }}%</td>
                            <td><span class="status-tag {{ $dimension->is_active ? '' : 'status-tag-inactive' }}">{{ $dimension->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
                            <td>{{ $dimension->active_questions_count }}</td>
                            <td>
                                <div class="table-actions">
                                <a class="button button-secondary button-compact" href="{{ route('admin.dimensions.edit', $dimension) }}">Ubah</a>
                                @if ($dimension->is_active || $dimension->active_questions_count === 0)
                                    <form method="POST" action="{{ route('admin.dimensions.archive', $dimension) }}" onsubmit="return confirm('Nonaktifkan/arsipkan dimensi ini?')">
                                        @csrf
                                        @method('PATCH')
                                        <button class="button button-secondary button-compact" type="submit">Arsipkan</button>
                                    </form>
                                @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <section class="dimension-create">
            <h2>Tambah dimensi</h2>
            <p class="muted">Dimensi baru akan dibuat nonaktif hingga bobot dan statusnya dikonfigurasi.</p>
            <form method="POST" action="{{ route('admin.dimensions.store') }}">
            @csrf
            <div class="grid">
                <div class="field"><label for="name">Nama</label><input id="name" name="name" value="{{ old('name') }}" required maxlength="120"></div>
                <div class="field"><label for="slug">Slug</label><input id="slug" name="slug" value="{{ old('slug') }}" required maxlength="120"></div>
            </div>
            <div class="field"><label for="description">Deskripsi</label><textarea id="description" name="description" rows="2" maxlength="2000">{{ old('description') }}</textarea></div>
            <button class="button" type="submit">Tambah dimensi</button>
            </form>
        </section>
    </section>
@endsection
