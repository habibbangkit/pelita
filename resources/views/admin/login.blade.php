@extends('layouts.app')

@section('title', 'Login Admin')

@section('content')
    <section class="card narrow">
        <p class="eyebrow">Area administrator</p>
        <h1 style="font-size: 2rem">Masuk ke PELITA</h1>
        <p class="muted">Gunakan akun administrator yang telah disiapkan oleh pengelola sistem.</p>

        @if ($errors->any())
            <div class="error" role="alert">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('admin.login.submit') }}">
            @csrf
            <div class="field">
                <label for="email">Email</label>
                <input id="email" name="email" type="email" autocomplete="username" value="{{ old('email') }}" required autofocus>
            </div>
            <div class="field">
                <label for="password">Kata sandi</label>
                <input id="password" name="password" type="password" autocomplete="current-password" required>
            </div>
            <button class="button" type="submit">Masuk</button>
        </form>
    </section>
@endsection
