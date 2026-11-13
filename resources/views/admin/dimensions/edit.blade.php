@extends('layouts.app')

@section('title', 'Ubah Dimensi')

@section('content')
    <section class="card narrow">
        <p class="eyebrow">Manajemen instrumen</p>
        <h1 style="font-size: 2rem">Ubah dimensi</h1>
        @if ($errors->any())
            <div class="error" role="alert">@foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>
        @endif
        <form method="POST" action="{{ route('admin.dimensions.update', $dimension) }}">
            @csrf
            @method('PUT')
            <div class="field"><label for="name">Nama</label><input id="name" name="name" value="{{ old('name', $dimension->name) }}" maxlength="120" required></div>
            <div class="field"><label for="slug">Slug</label><input id="slug" name="slug" value="{{ old('slug', $dimension->slug) }}" maxlength="120" required></div>
            <div class="field"><label for="description">Deskripsi</label><textarea id="description" name="description" rows="4" maxlength="2000" style="width:100%;border:1px solid #cbd5df;border-radius:9px;padding:12px;font:inherit">{{ old('description', $dimension->description) }}</textarea></div>
            <div class="actions">
                <a class="button button-secondary" href="{{ route('admin.dimensions.index') }}">Batal</a>
                <button class="button" type="submit">Simpan</button>
            </div>
        </form>
    </section>
@endsection
