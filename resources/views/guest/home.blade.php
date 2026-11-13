@extends('layouts.app')

@section('title', 'PELITA - Pemetaan Literasi dan Kesiapan AI')
@section('topbar_eyebrow', 'Beranda')
@section('topbar_title', 'Pemetaan Literasi dan Kesiapan AI')

@section('content')
    <section class="home-dashboard" aria-labelledby="welcome-heading">
        <div class="hero-panel home-hero">
            <div class="hero-glow hero-glow-one"></div>
            <div class="hero-glow hero-glow-two"></div>
            <div class="hero-copy">
                <span class="hero-kicker"><span class="hero-kicker-dot"></span> Pemetaan Literasi dan Kesiapan Teknologi Artificial Intelligence</span>
                <h1 id="welcome-heading">PELITA</h1>
                <h2>Kenali Kemampuanmu, Siap Hadapi Era Artificial Intelligence.</h2>
                <p>PELITA merupakan platform asesmen digital yang dirancang untuk mengukur tingkat literasi, keterampilan, serta kesiapan individu dalam memanfaatkan teknologi Artificial Intelligence (AI) secara efektif, aman, dan bertanggung jawab.</p>

                <div class="home-hero-actions">
                    <a class="button home-hero-primary" href="#mulai-asesmen">Mulai Asesmen</a>
                    <a class="button button-secondary home-hero-secondary" href="#mengenal-pelita">Pelajari Lebih Lanjut</a>
                </div>
            </div>

            <div class="hero-art" aria-hidden="true">
                <div class="art-orbit orbit-one"></div>
                <div class="art-orbit orbit-two"></div>
                <div class="art-core">
                    <svg viewBox="0 0 180 180">
                        <path d="M90 28c-25-20-55-1-51 26-21 11-18 40-2 48-5 25 18 42 39 31 8 21 29 21 37 0 21 11 44-6 39-31 16-8 19-37-2-48 4-27-26-46-51-26Z"/>
                        <path d="M89 36v103M72 42c-11 13-9 25 3 33-10 10-10 23 0 31M107 42c11 13 9 25-3 33 10 10 10 23 0 31M52 72l23 3m52-3-23 3M43 99l29-5m65 5-29-5"/>
                        <circle cx="90" cy="89" r="9"/>
                        <circle cx="62" cy="71" r="4"/><circle cx="118" cy="71" r="4"/>
                        <circle cx="62" cy="104" r="4"/><circle cx="118" cy="104" r="4"/>
                    </svg>
                </div>
                <div class="art-platform"><span></span><span></span><span></span></div>
                <span class="art-spark spark-one"></span>
                <span class="art-spark spark-two"></span>
            </div>
        </div>

        <section class="card home-info" id="mengenal-pelita" aria-labelledby="about-heading">
            <div class="home-info-heading">
                <p class="eyebrow">Tentang platform</p>
                <h2 id="about-heading">Mengenal PELITA</h2>
                <p class="muted">Perkembangan teknologi Artificial Intelligence membawa perubahan besar dalam dunia pendidikan, pekerjaan, dan kehidupan sehari-hari.</p>
                <p class="muted">PELITA hadir sebagai sarana untuk mengetahui sejauh mana kemampuan dan kesiapan seseorang dalam menggunakan teknologi AI. Melalui asesmen yang mudah diikuti, peserta dapat mengetahui tingkat pemahaman serta aspek yang perlu ditingkatkan.</p>
            </div>
        </section>

        <section class="card home-info" aria-labelledby="dimensions-heading">
            <div class="home-info-heading">
                <p class="eyebrow">Empat aspek utama</p>
                <h2 id="dimensions-heading">Apa yang Diukur PELITA?</h2>
                <p class="muted">Asesmen memetakan literasi dan kesiapan AI melalui empat dimensi berikut.</p>
            </div>
            <div class="home-info-grid">
                <article class="home-info-item">
                    <span class="home-info-icon">01</span>
                    <h3>Literasi Dasar AI</h3>
                    <p>Mengukur pemahaman mengenai konsep, fungsi, manfaat, dan cara kerja dasar Artificial Intelligence.</p>
                </article>
                <article class="home-info-item">
                    <span class="home-info-icon">02</span>
                    <h3>Keterampilan Praktis AI</h3>
                    <p>Mengukur kemampuan menggunakan berbagai teknologi AI untuk membantu menyelesaikan tugas dan aktivitas sehari-hari.</p>
                </article>
                <article class="home-info-item">
                    <span class="home-info-icon">03</span>
                    <h3>Etika dan Keamanan AI</h3>
                    <p>Mengukur pemahaman tentang privasi, keamanan data, bias, serta penggunaan AI secara bertanggung jawab.</p>
                </article>
                <article class="home-info-item">
                    <span class="home-info-icon">04</span>
                    <h3>Kesiapan Infrastruktur</h3>
                    <p>Mengukur kesiapan perangkat, akses internet, lingkungan, dan kemampuan beradaptasi dengan teknologi AI.</p>
                </article>
            </div>
        </section>

        <section class="card home-info" aria-labelledby="process-heading">
            <div class="home-info-heading">
                <p class="eyebrow">Proses asesmen</p>
                <h2 id="process-heading">Bagaimana Cara Kerjanya?</h2>
            </div>
            <div class="home-info-grid">
                <article class="home-info-item">
                    <span class="home-info-icon">01</span>
                    <h3>Isi Identitas</h3>
                    <p>Masukkan nama dan email untuk memulai asesmen.</p>
                </article>
                <article class="home-info-item">
                    <span class="home-info-icon">02</span>
                    <h3>Kerjakan Asesmen</h3>
                    <p>Jawab pertanyaan sesuai pengetahuan dan pengalaman yang dimiliki.</p>
                </article>
                <article class="home-info-item">
                    <span class="home-info-icon">03</span>
                    <h3>Lihat Hasil</h3>
                    <p>Dapatkan hasil pengukuran dan tingkat kematangan penggunaan AI.</p>
                </article>
                <article class="home-info-item">
                    <span class="home-info-icon">04</span>
                    <h3>Dapatkan Rekomendasi</h3>
                    <p>Pelajari rekomendasi untuk meningkatkan kemampuan dan kesiapan menggunakan AI.</p>
                </article>
            </div>
        </section>

        <section class="card home-info" aria-labelledby="levels-heading">
            <div class="home-info-heading">
                <p class="eyebrow">Interpretasi hasil</p>
                <h2 id="levels-heading">Tingkat Kematangan AI</h2>
                <p class="muted">Hasil asesmen dikelompokkan berdasarkan tingkat kematangan berikut.</p>
            </div>
            <div class="home-level-grid">
                @foreach ($maturityLevels as $level)
                    <article class="home-level-card">
                        <span class="home-level-range">{{ $level['range'] }}</span>
                        <h3>{{ $level['name'] }}</h3>
                        <p>{{ $level['description'] }}</p>
                    </article>
                @endforeach
            </div>
        </section>

        <section class="card home-info" aria-labelledby="benefits-heading">
            <div class="home-info-heading">
                <p class="eyebrow">Manfaat asesmen</p>
                <h2 id="benefits-heading">Mengapa Mengikuti PELITA?</h2>
            </div>
            <ul class="home-benefit-list">
                <li>Mengetahui tingkat literasi dan kesiapan penggunaan AI.</li>
                <li>Mengenali kemampuan yang sudah dimiliki.</li>
                <li>Mengetahui aspek yang masih perlu ditingkatkan.</li>
                <li>Mendapatkan rekomendasi pembelajaran sesuai hasil asesmen.</li>
                <li>Mempersiapkan diri menghadapi perkembangan teknologi digital.</li>
            </ul>
        </section>

        <section class="home-final-cta" aria-labelledby="final-cta-heading">
            <div>
                <p class="eyebrow">Langkah berikutnya</p>
                <h2 id="final-cta-heading">Siap Mengukur Kesiapanmu Menghadapi Era AI?</h2>
                <p>Mulai kenali kemampuanmu dan temukan peluang untuk berkembang bersama teknologi Artificial Intelligence.</p>
            </div>
            <a class="button" href="#mulai-asesmen">Mulai Asesmen Sekarang <span aria-hidden="true">→</span></a>
        </section>

        <section class="card welcome-card" id="mulai-asesmen" aria-labelledby="start-heading">
            @if ($assessment && $privacySettings?->isConfigured())
                <div class="welcome-card-top">
                    <span class="welcome-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3 3.5 7.5 12 12l8.5-4.5L12 3Z"/><path d="M5 10v5l7 4 7-4v-5M20.5 8v6"/></svg></span>
                    <span class="status-pill"><span></span> Asesmen tersedia</span>
                </div>
                <p class="eyebrow">Langkah pertama</p>
                <h2 id="start-heading">{{ $assessment->title }}</h2>
                <p class="muted">{{ $assessment->description ?: 'Isi identitas Anda untuk memulai asesmen. Anda tidak perlu membuat akun.' }}</p>
                <p class="muted">{{ $assessment->questions_count }} pertanyaan · Estimasi sekitar {{ $assessment->duration_minutes }} menit.</p>

                @if ($errors->any())
                    <div class="error" role="alert">
                        @foreach ($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                @endif

                <form method="POST" action="{{ route('assessment.start') }}">
                    @csrf
                    <div class="field">
                        <label for="name">Nama lengkap</label>
                        <input id="name" name="name" type="text" maxlength="80" autocomplete="name" placeholder="Masukkan nama lengkap" value="{{ old('name') }}" required>
                    </div>
                    <div class="field">
                        <label for="email">Alamat email</label>
                        <input id="email" name="email" type="email" maxlength="255" autocomplete="email" placeholder="nama@email.com" value="{{ old('email') }}" required>
                    </div>
                    <div class="privacy-note">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="4" y="10" width="16" height="11" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/></svg>
                        <span>Laporan PDF akan dikirim ke alamat email yang Anda daftarkan. Nama dan email dianonimkan setelah {{ $privacySettings->retention_days }} hari sejak asesmen terakhir selesai; skor dan jawaban tetap disimpan tanpa identitas. Kontak privasi: {{ $privacySettings->contact_name }}, <a href="mailto:{{ $privacySettings->contact_email }}">{{ $privacySettings->contact_email }}</a>.</span>
                    </div>
                    <button class="button button-wide" type="submit">Mulai asesmen <span aria-hidden="true">→</span></button>
                </form>
                <p class="welcome-footnote">Tidak perlu membuat akun · Jawaban tersimpan saat Anda melanjutkan</p>
            @elseif ($assessment)
                <div class="welcome-card-top">
                    <span class="welcome-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3 3.5 7.5 12 12l8.5-4.5L12 3Z"/><path d="M5 10v5l7 4 7-4v-5M20.5 8v6"/></svg></span>
                    <span class="status-pill status-pill-pending"><span></span> Sementara ditutup</span>
                </div>
                <p class="eyebrow">Pemberitahuan privasi</p>
                <h2 id="start-heading">Asesmen belum dapat dimulai</h2>
                <p class="muted">Pengaturan retensi data dan kontak privasi belum dilengkapi oleh administrator.</p>
            @else
                <div class="welcome-card-top">
                    <span class="welcome-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3 3.5 7.5 12 12l8.5-4.5L12 3Z"/><path d="M5 10v5l7 4 7-4v-5M20.5 8v6"/></svg></span>
                    <span class="status-pill status-pill-pending"><span></span> Sedang disiapkan</span>
                </div>
                <p class="eyebrow">Asesmen PELITA</p>
                <h2 id="start-heading">Asesmen belum tersedia</h2>
                <p class="muted">Instrumen asesmen sedang disiapkan. Silakan kembali lagi nanti.</p>
                <div class="empty-state-art" aria-hidden="true">
                    <span class="empty-state-circle"></span>
                    <svg viewBox="0 0 64 64"><path d="M12 18h40v30H12zM20 12h24M24 25h16M24 32h16M24 39h9"/></svg>
                </div>
                <p class="welcome-footnote">Informasi asesmen akan tersedia di halaman ini setelah dipublikasikan.</p>
            @endif
        </section>
    </section>
@endsection
