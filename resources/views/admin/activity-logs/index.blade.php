@extends('layouts.app')

@section('title', 'Aktivitas Admin')
@section('topbar_eyebrow', 'Administrasi')
@section('topbar_title', 'Riwayat aktivitas admin')

@section('content')
    <section class="card">
        <p class="eyebrow">Jejak audit</p>
        <h1 style="font-size: 2rem">Aktivitas administrator</h1>
        <p class="muted">Riwayat tindakan admin untuk membantu penelusuran perubahan. Data identitas peserta tidak dicatat pada log ini.</p>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Waktu</th>
                        <th>Administrator</th>
                        <th>Tindakan</th>
                        <th>Objek</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($logs as $log)
                        <tr>
                            <td>{{ $log->created_at->format('d M Y, H:i:s') }}</td>
                            <td>{{ $log->user?->name ?? 'Akun admin dihapus' }}</td>
                            <td>{{ $actionLabels[$log->action] ?? $log->action }}</td>
                            <td>
                                @if ($log->subject_type)
                                    {{ $log->subject_type }}{{ $log->subject_id ? ' #'.$log->subject_id : '' }}
                                @else
                                    —
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">Belum ada aktivitas admin yang tercatat.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $logs->links() }}
    </section>
@endsection
