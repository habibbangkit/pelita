<?php

namespace Database\Seeders;

use App\Models\Assessment;
use App\Models\Dimension;
use App\Models\Question;
use App\Models\Recommendation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class PelitaAssessmentContentSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(AssessmentFoundationSeeder::class);

        $dimensionDescriptions = [
            'literasi-dasar' => 'Mengukur pemahaman konsep dasar, cara kerja, manfaat, dan keterbatasan AI.',
            'keterampilan-praktis' => 'Mengukur kemampuan menggunakan AI, menyusun prompt, dan memanfaatkannya untuk pekerjaan.',
            'etika-keamanan' => 'Mengukur kesadaran terhadap privasi, keamanan data, bias, hak cipta, dan tanggung jawab penggunaan AI.',
            'kesiapan-infrastruktur' => 'Mengukur kesiapan perangkat, akses internet, kemauan belajar, serta dukungan lingkungan terhadap penggunaan AI.',
        ];

        foreach ($dimensionDescriptions as $slug => $description) {
            $dimension = Dimension::query()->where('slug', $slug)->firstOrFail();
            if (blank($dimension->description)) {
                $dimension->update(['description' => $description]);
            }
        }

        $assessment = Assessment::query()->where('title', 'PELITA - AI Literacy & Readiness Assessment')->first();
        $isInitialAssessment = false;
        if (! $assessment) {
            $assessment = Assessment::query()->where('title', 'Asesmen Kesiapan AI PELITA')->first();
            $isInitialAssessment = $assessment !== null;
        }

        if (! $assessment) {
            $assessment = Assessment::create([
                'title' => 'PELITA - AI Literacy & Readiness Assessment',
                'description' => 'Asesmen ini mengukur pemahaman, keterampilan penggunaan, kesadaran etika, dan kesiapan individu dalam mengadopsi teknologi AI. Hasilnya membantu menentukan tingkat kematangan literasi AI dan rekomendasi pembelajaran.',
                'duration_minutes' => 15,
            ]);
        }

        if ($assessment->status === 'published' && $assessment->questions()->count() < 20) {
            throw new RuntimeException('Asesmen terpublikasi belum lengkap; jadikan draft sebelum mengisi konten awal.');
        }

        if ($isInitialAssessment && $assessment->status !== 'published') {
            $assessment->update([
                'title' => 'PELITA - AI Literacy & Readiness Assessment',
                'description' => 'Asesmen ini mengukur pemahaman, keterampilan penggunaan, kesadaran etika, dan kesiapan individu dalam mengadopsi teknologi AI. Hasilnya membantu menentukan tingkat kematangan literasi AI dan rekomendasi pembelajaran.',
                'duration_minutes' => 15,
            ]);
        } elseif ($assessment->duration_minutes === null) {
            $assessment->update(['duration_minutes' => 15]);
        }

        $questions = $this->questions();
        DB::transaction(function () use ($assessment, $questions): void {
            foreach ($questions as $index => $data) {
                $question = $assessment->questions()->firstOrCreate(
                    ['position' => $index + 1],
                    [
                        'dimension_id' => Dimension::query()->where('slug', $data['dimension'])->value('id'),
                        'question_text' => $data['question'],
                        'explanation' => $data['explanation'],
                        'type' => $data['type'],
                        'weight' => 1,
                        'position' => $index + 1,
                        'is_required' => true,
                        'is_active' => true,
                    ]
                );

                foreach ($data['options'] as $optionIndex => $option) {
                    $question->options()->firstOrCreate(
                        ['position' => $optionIndex + 1],
                        [
                            'option_text' => $option['text'],
                            'score_value' => $option['score'] ?? null,
                            'is_correct' => $option['correct'] ?? null,
                            'is_active' => true,
                        ]
                    );
                }
            }

            foreach ([
                'literasi-dasar' => [
                    'Perkuat literasi dasar AI',
                    'Pelajari pengenalan AI, konsep dasar, cara kerja model AI, manfaat, dan keterbatasannya.',
                ],
                'keterampilan-praktis' => [
                    'Tingkatkan keterampilan praktis AI',
                    'Ikuti latihan penggunaan AI dan Prompt Engineering, lalu periksa hasilnya sebelum digunakan.',
                ],
                'etika-keamanan' => [
                    'Tingkatkan etika dan keamanan AI',
                    'Pelajari privasi data, bias AI, keamanan, hak cipta, dan penggunaan AI yang bertanggung jawab.',
                ],
                'kesiapan-infrastruktur' => [
                    'Tingkatkan kesiapan infrastruktur AI',
                    'Tinjau kesiapan perangkat, akses internet, dukungan lingkungan, dan kesempatan belajar AI.',
                ],
            ] as $slug => [$title, $description]) {
                Recommendation::query()->firstOrCreate(
                    [
                        'dimension_id' => Dimension::query()->where('slug', $slug)->value('id'),
                        'minimum_score' => 0,
                        'maximum_score' => 59.99,
                    ],
                    [
                        'title' => $title,
                        'description' => $description,
                        'learning_url' => null,
                        'is_active' => true,
                    ]
                );
            }
        });
    }

    private function questions(): array
    {
        $multipleChoice = static fn (string $dimension, string $question, string $explanation, array $options): array => [
            'dimension' => $dimension,
            'type' => 'multiple_choice',
            'question' => $question,
            'explanation' => $explanation,
            'options' => array_map(
                static fn (array $option): array => ['text' => $option[0], 'correct' => $option[1]],
                $options
            ),
        ];
        $likert = static fn (string $question, array $options): array => [
            'dimension' => 'kesiapan-infrastruktur',
            'type' => 'likert',
            'question' => $question,
            'explanation' => 'Tidak ada jawaban benar atau salah. Pilih kondisi yang paling sesuai dengan pengalaman Anda.',
            'options' => array_map(
                static fn (array $option): array => ['text' => $option[0], 'score' => $option[1]],
                $options
            ),
        ];

        return [
            $multipleChoice('literasi-dasar', 'Apa yang dimaksud dengan Artificial Intelligence (AI)?', 'AI adalah teknologi yang memungkinkan komputer melakukan tugas yang biasanya membutuhkan kemampuan seperti belajar, mengenali pola, atau memahami bahasa.', [
                ['Teknologi yang memungkinkan komputer melakukan tugas yang biasanya membutuhkan kecerdasan manusia.', true],
                ['Perangkat keras untuk menyimpan informasi.', false],
                ['Sistem operasi yang digunakan komputer.', false],
                ['Jaringan yang menghubungkan perangkat komputer.', false],
            ]),
            $multipleChoice('literasi-dasar', 'Manakah contoh penerapan Artificial Intelligence dalam kehidupan sehari-hari?', 'Sistem rekomendasi mempelajari pola penggunaan untuk menyarankan konten yang mungkin relevan bagi seseorang.', [
                ['Kabel pengisi daya ponsel.', false],
                ['Sistem rekomendasi video berdasarkan kebiasaan pengguna.', true],
                ['Flashdisk untuk menyimpan dokumen.', false],
                ['Keyboard komputer biasa.', false],
            ]),
            $multipleChoice('literasi-dasar', 'Apa yang dimaksud dengan Generative AI?', 'Generative AI menghasilkan konten baru, seperti teks, gambar, audio, atau kode, berdasarkan pola yang dipelajari dari data.', [
                ['Teknologi untuk mempercepat koneksi internet.', false],
                ['Sistem untuk menghapus seluruh data komputer.', false],
                ['Teknologi AI yang dapat menghasilkan konten baru seperti teks, gambar, dan suara.', true],
                ['Perangkat untuk mengoperasikan komputer.', false],
            ]),
            $multipleChoice('literasi-dasar', 'Mengapa hasil yang diberikan oleh AI perlu diperiksa kembali?', 'AI dapat menghasilkan jawaban yang terdengar meyakinkan tetapi keliru, tidak lengkap, atau tidak sesuai konteks.', [
                ['Karena AI tidak dapat menghasilkan teks.', false],
                ['Karena semua hasil AI selalu benar.', false],
                ['Karena AI hanya bekerja pada malam hari.', false],
                ['Karena AI dapat menghasilkan informasi yang keliru atau tidak akurat.', true],
            ]),
            $multipleChoice('literasi-dasar', 'Manakah pernyataan yang benar mengenai kemampuan AI?', 'AI dapat membantu pekerjaan, tetapi manusia tetap perlu menilai hasil, konteks, dan dampak penggunaannya.', [
                ['AI dapat membantu manusia menyelesaikan pekerjaan, tetapi hasilnya tetap memerlukan pemeriksaan manusia.', true],
                ['AI selalu memiliki jawaban yang benar.', false],
                ['AI dapat menggantikan seluruh pekerjaan manusia.', false],
                ['AI tidak membutuhkan data untuk bekerja.', false],
            ]),
            $multipleChoice('keterampilan-praktis', 'Apa yang dimaksud dengan Prompt Engineering?', 'Prompt Engineering adalah proses menyusun instruksi dan konteks agar keluaran AI lebih sesuai dengan kebutuhan.', [
                ['Teknik memperbaiki perangkat komputer.', false],
                ['Teknik menyusun instruksi yang tepat agar AI memberikan hasil sesuai kebutuhan.', true],
                ['Teknik membuat jaringan internet.', false],
                ['Teknik menghapus data AI.', false],
            ]),
            $multipleChoice('keterampilan-praktis', 'Anda ingin meminta AI membuat rangkuman sebuah materi perkuliahan. Instruksi mana yang paling efektif?', 'Instruksi yang menyebutkan tugas, panjang, sasaran pembaca, dan gaya bahasa memberi konteks yang lebih jelas.', [
                ['Tolong buatkan sesuatu.', false],
                ['Baca materi ini.', false],
                ['Rangkum materi berikut menjadi 5 poin utama menggunakan bahasa sederhana dan mudah dipahami mahasiswa.', true],
                ['Kerjakan semuanya.', false],
            ]),
            $multipleChoice('keterampilan-praktis', 'Jika hasil jawaban AI belum sesuai dengan kebutuhan, tindakan terbaik adalah …', 'Berikan konteks tambahan atau perjelas instruksi, lalu tinjau kembali hasil yang diperoleh.', [
                ['Menghentikan penggunaan AI selamanya.', false],
                ['Langsung menggunakan jawaban tersebut.', false],
                ['Menghapus seluruh dokumen.', false],
                ['Memperbaiki instruksi atau memberikan konteks tambahan kepada AI.', true],
            ]),
            $multipleChoice('keterampilan-praktis', 'Manakah kegiatan berikut yang dapat dibantu menggunakan teknologi AI?', 'AI dapat membantu pekerjaan berbasis informasi, tetapi bukan pengganti pengawasan dan keputusan manusia.', [
                ['Membuat rangkuman dokumen, mencari ide, dan membantu menyusun laporan.', true],
                ['Mengisi daya baterai ponsel secara otomatis tanpa perangkat tambahan.', false],
                ['Memperbaiki kerusakan fisik komputer secara langsung.', false],
                ['Menggantikan seluruh keputusan manusia tanpa pengawasan.', false],
            ]),
            $multipleChoice('keterampilan-praktis', 'Anda diminta membuat desain poster kegiatan menggunakan bantuan AI. Apa langkah yang paling tepat?', 'Tema, tujuan, ukuran, warna, dan elemen yang diinginkan membantu AI menghasilkan rancangan yang lebih relevan.', [
                ['Meminta AI membuat poster tanpa memberikan informasi apa pun.', false],
                ['Memberikan deskripsi tema, tujuan, ukuran, warna, dan elemen desain yang dibutuhkan.', true],
                ['Meminta AI membuat desain secara acak.', false],
                ['Menggunakan hasil pertama tanpa melakukan pemeriksaan.', false],
            ]),
            $multipleChoice('etika-keamanan', 'Apakah data pribadi seperti password dan informasi rekening boleh dimasukkan ke layanan AI publik?', 'Informasi autentikasi dan keuangan bersifat sensitif. Jangan memasukkannya ke layanan publik kecuali ada izin dan perlindungan yang jelas.', [
                ['Boleh karena AI selalu aman.', false],
                ['Boleh jika sedang terburu-buru.', false],
                ['Tidak boleh sembarangan karena berisiko terhadap keamanan dan privasi data.', true],
                ['Boleh jika menggunakan komputer pribadi.', false],
            ]),
            $multipleChoice('etika-keamanan', 'Apa yang dimaksud dengan bias pada Artificial Intelligence?', 'Bias dapat muncul ketika data atau proses pengembangan membuat keluaran AI cenderung tidak adil terhadap kelompok atau kondisi tertentu.', [
                ['Kesalahan warna pada tampilan aplikasi.', false],
                ['Kondisi ketika AI memberikan hasil yang cenderung tidak adil akibat bias dalam data atau proses pengembangannya.', true],
                ['Kerusakan perangkat komputer.', false],
                ['Gangguan koneksi internet.', false],
            ]),
            $multipleChoice('etika-keamanan', 'Anda mendapatkan informasi dari AI yang akan digunakan dalam laporan akademik. Apa yang harus dilakukan?', 'Informasi penting perlu diverifikasi menggunakan sumber yang kredibel dan dapat ditelusuri.', [
                ['Langsung menyalinnya tanpa pemeriksaan.', false],
                ['Menganggap seluruh informasi benar.', false],
                ['Menghapus sumber referensi.', false],
                ['Memeriksa kebenaran informasi menggunakan sumber yang dapat dipercaya.', true],
            ]),
            $multipleChoice('etika-keamanan', 'Apa yang perlu diperhatikan ketika menggunakan gambar atau tulisan yang dihasilkan AI untuk keperluan publikasi?', 'Periksa hak cipta, lisensi, dan syarat layanan serta pastikan penggunaan konten sesuai ketentuan yang berlaku.', [
                ['Hak cipta, lisensi, dan ketentuan penggunaan konten.', true],
                ['Hanya ukuran gambar.', false],
                ['Kecepatan proses pembuatan.', false],
                ['Jumlah warna yang digunakan.', false],
            ]),
            $multipleChoice('etika-keamanan', 'Manakah contoh penggunaan AI yang bertanggung jawab?', 'Pengguna tetap memeriksa hasil AI dan bertanggung jawab atas keputusan serta dampak pemakaiannya.', [
                ['Menggunakan AI untuk menyebarkan informasi palsu.', false],
                ['Menggunakan AI untuk meniru karya orang lain tanpa memperhatikan hak penggunaannya.', false],
                ['Menggunakan AI sebagai alat bantu dengan tetap memeriksa hasil dan bertanggung jawab atas penggunaannya.', true],
                ['Menggunakan AI untuk mengambil data pribadi tanpa izin.', false],
            ]),
            $likert('Apakah Anda memiliki perangkat yang memadai untuk mengakses dan menggunakan teknologi AI?', [
                ['Tidak memiliki perangkat yang memadai', 0],
                ['Memiliki perangkat tetapi sangat terbatas', 25],
                ['Memiliki perangkat dengan kemampuan cukup', 50],
                ['Memiliki perangkat yang memadai', 75],
                ['Memiliki perangkat yang sangat mendukung penggunaan AI', 100],
            ]),
            $likert('Bagaimana kondisi akses internet yang Anda gunakan untuk mengakses layanan AI?', [
                ['Tidak memiliki akses internet', 0],
                ['Internet sangat terbatas', 25],
                ['Internet cukup tetapi terkadang tidak stabil', 50],
                ['Internet stabil untuk kebutuhan sehari-hari', 75],
                ['Internet sangat stabil dan mendukung berbagai kebutuhan AI', 100],
            ]),
            $likert('Seberapa siap Anda mempelajari dan mencoba teknologi AI baru?', [
                ['Belum tertarik mempelajari AI', 0],
                ['Masih ragu untuk mencoba', 25],
                ['Tertarik tetapi belum banyak mencoba', 50],
                ['Siap mempelajari dan mencoba AI', 75],
                ['Sangat antusias dan aktif mempelajari perkembangan AI', 100],
            ]),
            $likert('Bagaimana dukungan lingkungan belajar atau tempat kerja Anda terhadap penggunaan teknologi AI?', [
                ['Tidak ada dukungan', 0],
                ['Dukungan masih sangat terbatas', 25],
                ['Mulai terdapat dukungan dan arahan', 50],
                ['Terdapat dukungan serta kesempatan menggunakan AI', 75],
                ['Terdapat fasilitas, kebijakan, dan pelatihan AI yang memadai', 100],
            ]),
            $likert('Bagaimana kesiapan Anda mengintegrasikan AI ke dalam aktivitas belajar atau pekerjaan sehari-hari?', [
                ['Belum siap menggunakan AI', 0],
                ['Membutuhkan banyak bimbingan', 25],
                ['Siap mencoba pada pekerjaan sederhana', 50],
                ['Siap menggunakan AI untuk membantu sebagian pekerjaan', 75],
                ['Siap mengintegrasikan AI secara konsisten dan bertanggung jawab', 100],
            ]),
        ];
    }
}
