@extends('layouts.app')

@section('title', 'PELITA - Pemetaan Literasi dan Kesiapan AI')
@section('topbar_eyebrow', 'Beranda peserta')
@section('topbar_title', 'Pemetaan Literasi dan Kesiapan AI')

@section('content')
    <div class="landing-page">
        <div class="landing-grid">
            <section class="landing-panel landing-hero" id="beranda" aria-labelledby="welcome-heading">
                <div class="landing-hero-copy">
                    <span class="landing-kicker"><span></span> Pemetaan Literasi dan Kesiapan Teknologi Artificial Intelligence</span>
                    <h1 id="welcome-heading">Kenali Potensimu,<br><span>Ukur Kesiapanmu.</span></h1>
                    <p>Kenali kemampuanmu dan siapkan diri menghadapi era Artificial Intelligence melalui asesmen yang mudah, informatif, dan terukur.</p>
                    <div class="landing-actions">
                        @if ($assessment && $privacySettings?->isConfigured())
                            <a class="landing-button landing-button-primary" href="#mulai-asesmen">Mulai Asesmen <span aria-hidden="true">→</span></a>
                            <a class="landing-button landing-button-secondary" href="#mengenal-pelita">Pelajari Lebih Lanjut</a>
                        @else
                            <a class="landing-button landing-button-primary" href="#mengenal-pelita">Pelajari PELITA <span aria-hidden="true">→</span></a>
                            <a class="landing-button landing-button-secondary" href="#dimensi-penilaian">Lihat Indikator</a>
                        @endif
                    </div>
                </div>
                <div class="landing-robot" aria-hidden="true">
                    <svg viewBox="0 0 430 330" role="presentation">
                        <defs>
                            <linearGradient id="robot-shell" x1="0" x2="1" y1="0" y2="1">
                                <stop offset="0" stop-color="#fff"/>
                                <stop offset="1" stop-color="#e4dcff"/>
                            </linearGradient>
                            <linearGradient id="robot-accent" x1="0" x2="1" y1="0" y2="1">
                                <stop offset="0" stop-color="#9272ed"/>
                                <stop offset="1" stop-color="#5636c5"/>
                            </linearGradient>
                        </defs>
                        <ellipse cx="225" cy="292" rx="145" ry="17" fill="#6e53c8" opacity=".12"/>
                        <path d="M92 256c-18-11-26-29-19-47l24-61 31 12-24 64 33 32-19 22-26-22Z" fill="url(#robot-shell)" stroke="#d7cef7" stroke-width="4"/>
                        <path d="m308 255 20-30-26-63 31-13 29 69c5 12 1 25-8 34l-22 23-22-20Z" fill="url(#robot-shell)" stroke="#d7cef7" stroke-width="4"/>
                        <rect x="144" y="142" width="147" height="132" rx="43" fill="url(#robot-shell)" stroke="#d7cef7" stroke-width="4"/>
                        <rect x="174" y="180" width="87" height="54" rx="20" fill="#30235e"/>
                        <circle cx="199" cy="206" r="8" fill="#b9a7ff"/>
                        <circle cx="236" cy="206" r="8" fill="#b9a7ff"/>
                        <path d="M207 220c7 7 15 7 22 0" fill="none" stroke="#fff" stroke-linecap="round" stroke-width="3"/>
                        <rect x="194" y="117" width="49" height="28" rx="12" fill="url(#robot-accent)"/>
                        <path d="M218 116V94" stroke="#7655dc" stroke-linecap="round" stroke-width="7"/>
                        <circle cx="218" cy="86" r="12" fill="#f3b84b"/>
                        <path d="M156 167c-25-16-37-4-38 10M280 167c25-16 37-4 38 10" fill="none" stroke="#bcaaf2" stroke-linecap="round" stroke-width="8"/>
                        <rect x="186" y="258" width="27" height="42" rx="12" fill="#8e72e5"/>
                        <rect x="229" y="258" width="27" height="42" rx="12" fill="#8e72e5"/>
                        <path d="M76 104h47m-24-24v48M337 91h34m-17-17v34" stroke="#aa94ef" stroke-linecap="round" stroke-width="5"/>
                        <circle cx="89" cy="220" r="8" fill="#f3b84b"/>
                        <circle cx="355" cy="153" r="6" fill="#9577e4"/>
                        <path d="m319 75 7 14 15 2-11 10 3 15-14-7-13 7 2-15-11-10 15-2 7-14Z" fill="#f3b84b"/>
                    </svg>
                </div>
                <div class="landing-hero-badge"><span aria-hidden="true">✦</span> Kenali diri. Siap menghadapi AI.</div>
            </section>

            <section class="landing-panel landing-about" id="mengenal-pelita" aria-labelledby="about-heading">
                <p class="landing-eyebrow">Tentang PELITA</p>
                <h2 id="about-heading">Apa itu PELITA?</h2>
                <p>PELITA adalah platform asesmen digital untuk memetakan literasi, keterampilan, dan kesiapan individu dalam memanfaatkan teknologi Artificial Intelligence secara efektif, aman, dan bertanggung jawab.</p>
                <p>Kenali kemampuan yang sudah dimiliki, temukan aspek yang perlu dikembangkan, lalu dapatkan rekomendasi sesuai hasil asesmen.</p>

                <div class="landing-feature-list" id="fitur-pelita">
                    <div><span class="landing-feature-icon" aria-hidden="true">✓</span><span><strong>Asesmen terstruktur</strong><small>Pertanyaan disusun berdasarkan empat dimensi.</small></span></div>
                    <div><span class="landing-feature-icon" aria-hidden="true">◎</span><span><strong>Hasil dan rekomendasi</strong><small>Skor, tingkat kematangan, dan saran pengembangan.</small></span></div>
                    <div><span class="landing-feature-icon" aria-hidden="true">↓</span><span><strong>Laporan yang mudah dibawa</strong><small>Unduh hasil asesmen dalam format PDF.</small></span></div>
                </div>
                <a class="landing-text-link" href="#dimensi-penilaian">Lihat indikator penilaian <span aria-hidden="true">→</span></a>
            </section>

            <section class="landing-panel landing-dimensions" id="dimensi-penilaian" aria-labelledby="dimensions-heading">
                <div class="landing-section-heading">
                    <div>
                        <p class="landing-eyebrow">Empat aspek utama</p>
                        <h2 id="dimensions-heading">Apa yang Diukur?</h2>
                    </div>
                    <span class="landing-section-mark" aria-hidden="true">◈</span>
                </div>
                <div class="landing-dimension-grid">
                    <article class="landing-dimension-card">
                        <span class="landing-dimension-number">01</span>
                        <h3>Literasi Dasar AI</h3>
                        <p>Pemahaman konsep, fungsi, manfaat, dan cara kerja dasar AI.</p>
                    </article>
                    <article class="landing-dimension-card">
                        <span class="landing-dimension-number">02</span>
                        <h3>Keterampilan Praktis AI</h3>
                        <p>Kemampuan menggunakan teknologi AI untuk membantu aktivitas sehari-hari.</p>
                    </article>
                    <article class="landing-dimension-card">
                        <span class="landing-dimension-number">03</span>
                        <h3>Etika dan Keamanan AI</h3>
                        <p>Kesadaran tentang privasi, keamanan data, bias, dan penggunaan AI yang bertanggung jawab.</p>
                    </article>
                    <article class="landing-dimension-card">
                        <span class="landing-dimension-number">04</span>
                        <h3>Kesiapan Infrastruktur</h3>
                        <p>Kesiapan perangkat, akses internet, lingkungan, dan kemampuan beradaptasi.</p>
                    </article>
                </div>
                <div class="landing-level-strip" aria-label="Tingkat kematangan AI">
                    @foreach ($maturityLevels as $level)
                        <span><strong>{{ $level['name'] }}</strong><small>{{ $level['range'] }}</small></span>
                    @endforeach
                </div>
            </section>

            <section class="landing-panel landing-process" id="cara-kerja" aria-labelledby="process-heading">
                <p class="landing-eyebrow">Mudah dan informatif</p>
                <h2 id="process-heading">Alur Asesmen PELITA</h2>
                <div class="landing-process-list">
                    <article><span>01</span><div><h3>Isi identitas</h3><p>Masukkan nama dan email untuk memulai asesmen.</p></div></article>
                    <article><span>02</span><div><h3>Kerjakan asesmen</h3><p>Jawab sesuai pengetahuan dan pengalamanmu.</p></div></article>
                    <article><span>03</span><div><h3>Lihat hasil</h3><p>Ketahui skor dan tingkat kematangan AI-mu.</p></div></article>
                    <article><span>04</span><div><h3>Dapatkan rekomendasi</h3><p>Pelajari saran untuk meningkatkan kesiapanmu.</p></div></article>
                </div>
                <div class="landing-process-callout">
                    @if ($assessment && $privacySettings?->isConfigured())
                        <span><strong>Siap mulai?</strong><small>Asesmen dapat dikerjakan tanpa membuat akun.</small></span>
                        <a href="#mulai-asesmen">Mulai <span aria-hidden="true">→</span></a>
                    @else
                        <span><strong>Kenali dulu PELITA</strong><small>Asesmen belum dapat dimulai saat ini.</small></span>
                        <span class="landing-process-unavailable">Sementara ditutup</span>
                    @endif
                </div>
                <a class="landing-text-link landing-faq-link" href="#faq">Punya pertanyaan? Baca FAQ <span aria-hidden="true">→</span></a>
            </section>
        </div>

        <section class="landing-faq" id="faq" aria-labelledby="faq-heading">
            <div>
                <p class="landing-eyebrow">Bantuan</p>
                <h2 id="faq-heading">Pertanyaan yang Sering Diajukan</h2>
            </div>
            <div class="landing-faq-list">
                <details>
                    <summary>Apakah asesmen PELITA berbayar dan perlu akun?</summary>
                    <p>Asesmen dapat diikuti secara gratis tanpa membuat akun. Peserta cukup memasukkan nama dan email.</p>
                </details>
                <details>
                    <summary>Berapa lama asesmen berlangsung?</summary>
                    <p>Waktu pengerjaan ditampilkan sebelum asesmen dimulai dan bergantung pada instrumen yang dipublikasikan.</p>
                </details>
                <details>
                    <summary>Apakah hasil asesmen merupakan sertifikat kompetensi?</summary>
                    <p>Tidak. Hasil PELITA adalah gambaran literasi dan kesiapan berdasarkan instrumen yang digunakan, bukan sertifikasi kompetensi profesional.</p>
                </details>
                <details id="kebijakan-privasi">
                    <summary>Bagaimana data peserta digunakan dan dilindungi?</summary>
                    @if ($privacySettings?->isConfigured())
                        <p>Nama dan email dianonimkan setelah {{ $privacySettings->retention_days }} hari sejak asesmen terakhir selesai. Skor dan jawaban tetap disimpan tanpa identitas. Kontak privasi: {{ $privacySettings->contact_name }}, <a href="mailto:{{ $privacySettings->contact_email }}">{{ $privacySettings->contact_email }}</a>.</p>
                    @else
                        <p>Data identitas dianonimkan sesuai masa retensi yang ditetapkan administrator, sedangkan skor dan jawaban disimpan tanpa identitas.</p>
                    @endif
                </details>
            </div>
        </section>

        <section class="landing-start" id="mulai-asesmen" aria-labelledby="start-heading">
            <div class="landing-start-copy">
                <p class="landing-eyebrow">Langkah berikutnya</p>
                <h2 id="start-heading">Siap Mengukur Kesiapanmu Menghadapi Era AI?</h2>
                <p>Mulai kenali kemampuanmu dan temukan peluang untuk berkembang bersama teknologi Artificial Intelligence.</p>
                <div class="landing-start-points"><span>✓ Hasil langsung tersedia</span><span>✓ Laporan PDF</span><span>✓ Rekomendasi personal</span></div>
            </div>
            <div class="landing-start-form">
                @if ($assessment && $privacySettings?->isConfigured())
                    <span class="landing-availability"><span></span> Asesmen tersedia</span>
                    <h3>{{ $assessment->title }}</h3>
                    <p>{{ $assessment->description ?: 'Isi identitas Anda untuk memulai asesmen.' }}</p>
                    <p class="landing-form-meta">{{ $assessment->questions_count }} pertanyaan · sekitar {{ $assessment->duration_minutes }} menit</p>
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
                        <p class="landing-privacy-note">Laporan PDF akan dikirim ke alamat email yang Anda daftarkan. Data identitas dianonimkan setelah {{ $privacySettings->retention_days }} hari.</p>
                        <button class="button button-wide" type="submit">Mulai asesmen sekarang <span aria-hidden="true">→</span></button>
                    </form>
                @elseif ($assessment)
                    <span class="landing-availability is-pending"><span></span> Sementara ditutup</span>
                    <h3>Asesmen belum dapat dimulai</h3>
                    <p>Pengaturan retensi data dan kontak privasi belum dilengkapi oleh administrator.</p>
                @else
                    <span class="landing-availability is-pending"><span></span> Sedang disiapkan</span>
                    <h3>Asesmen belum tersedia</h3>
                    <p>Instrumen asesmen sedang disiapkan. Silakan kembali lagi nanti.</p>
                @endif
            </div>
        </section>
    </div>
@endsection
