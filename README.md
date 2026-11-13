# PELITA

PELITA adalah aplikasi Laravel 12 untuk memetakan literasi dan kesiapan terhadap teknologi Artificial Intelligence (AI). Peserta dapat mengikuti asesmen tanpa membuat akun, sedangkan administrator dapat mengelola instrumen, konfigurasi penilaian, dan hasil melalui area admin yang terlindungi.

## Persyaratan

- PHP 8.2 atau lebih baru dan Composer.
- MySQL/MariaDB untuk produksi; SQLite didukung untuk pengembangan lokal dan pengujian.
- Node.js/npm untuk mengompilasi aset frontend.
- Ekstensi PHP `pdo_sqlite` untuk menjalankan pengujian dengan SQLite.

## Menyiapkan aplikasi lokal

1. Salin `.env.example` menjadi `.env`, lalu atur koneksi database.
2. Atur `PELITA_ADMIN_NAME`, `PELITA_ADMIN_EMAIL`, dan `PELITA_ADMIN_PASSWORD` untuk membuat administrator awal. Jangan masukkan nilai tersebut ke kontrol versi.
3. Pasang dependensi, buat kunci aplikasi, migrasikan dan isi database, lalu kompilasi aset frontend:

   ```sh
   composer install
   php artisan key:generate
   php artisan migrate --seed
   npm install
   npm run build
   php artisan serve
   ```

4. Buka `/` untuk halaman peserta dan `/admin/login` untuk masuk sebagai administrator.

Seeder fondasi membuat empat dimensi awal, tiga tingkat kematangan, dan satu asesmen berstatus **draft**. Seeder tersebut tidak memuat butir soal yang telah disetujui karena PRD tidak menyediakan pertanyaan yang tervalidasi. Jika dijalankan kembali, seeder hanya membuat data fondasi yang belum tersedia dan tidak menimpa nilai penilaian yang sudah diatur administrator.

## Alur kerja administrator

1. Tinjau dan kelola dimensi melalui `/admin/dimensions`. Dimensi baru berstatus nonaktif dengan bobot nol.
2. Atur dimensi aktif beserta bobotnya, lalu tentukan rentang skor kematangan melalui `/admin/scoring-settings`. Jumlah bobot dimensi aktif harus 100%; rentang kematangan harus berurutan dari 0 sampai 100.
3. Buat dan kelola pertanyaan beserta opsi melalui `/admin/questions`. Pertanyaan pilihan ganda harus memiliki tepat satu opsi benar. Setiap opsi Likert memerlukan skor rubrik 0–100. Pertanyaan dapat diurutkan ulang, dinonaktifkan, atau diarsipkan; data tetap disimpan dan tidak dihapus.
4. Publikasikan asesmen draft dari halaman pengelolaan pertanyaan. Publikasi ditolak jika dimensi aktif tidak memiliki pertanyaan aktif, atau jika pertanyaan aktif memiliki opsi, tipe, atau bobot yang tidak valid. Hanya asesmen yang sudah dipublikasikan dan lengkap yang tersedia di halaman peserta.
5. Untuk mengubah pertanyaan, kembalikan asesmen ke status draft terlebih dahulu. Perubahan instrumen dan penilaian dibatasi selama masih ada peserta yang sedang mengerjakan asesmen. Dimensi yang digunakan oleh asesmen terpublikasi tidak dapat diaktifkan atau dinonaktifkan sebelum asesmen dikembalikan ke draft dan pertanyaannya disesuaikan.

Administrator dapat melihat ringkasan di `/admin/dashboard`, mencari dan memfilter asesmen selesai di `/admin/participants`, serta memeriksa rekaman hasil tiap peserta. Hasil yang telah difilter dapat diunduh sebagai PDF.

## Penilaian dan hasil historis

- Jawaban pilihan ganda mendapat skor 100 jika benar dan 0 jika salah. Jawaban Likert menggunakan `score_value` dari opsi yang dipilih.
- Skor pertanyaan dirata-ratakan berdasarkan bobot pertanyaan di dalam setiap dimensi. Skor akhir merupakan jumlah skor dimensi yang telah dikalikan bobot masing-masing.
- Rentang kematangan dan bobot dimensi dikelola di database. Seeder fondasi menyediakan nilai awal: Aware (0–39,99), Adopter (40–74,99), dan Transformer (75–100).
- Hasil asesmen yang selesai menyimpan skor, tingkat kematangan, snapshot bobot, teks pertanyaan dan jawaban, opsi yang dipilih, serta rekomendasi. Perubahan instrumen atau konfigurasi penilaian berikutnya tidak menghitung ulang hasil historis.
- Jika tidak ada rekomendasi yang sesuai dengan dimensi berskor terendah, PELITA menampilkan rekomendasi netral. Rekomendasi dapat disimpan di tabel `recommendations`.

## Deployment produksi

Gunakan HTTPS dan server MySQL/MariaDB yang didukung. Sebelum deployment, siapkan database dan konfigurasi aplikasi, termasuk kata sandi administrator yang kuat serta `APP_KEY` yang tetap. Atur setidaknya:

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.example
SESSION_SECURE_COOKIE=true
```

Atur kredensial database dan nilai `PELITA_ADMIN_*` melalui konfigurasi rahasia/lingkungan hosting, bukan di kontrol versi. Konfigurasikan server web agar menyajikan folder `public` Laravel, aktifkan HTTPS, dan pastikan proses PHP dapat menulis ke `storage` serta `bootstrap/cache`. Jika TLS berakhir di proxy perantara (reverse proxy), atur trusted proxy Laravel agar deteksi koneksi aman dan HSTS berfungsi dengan benar.

Untuk setiap rilis, kompilasi aset dan pasang dependensi produksi, lalu jalankan migrasi serta perbarui cache konfigurasi ketika aplikasi berada dalam mode pemeliharaan:

```sh
npm install
npm run build
composer install --no-dev --optimize-autoloader
php artisan down
php artisan migrate --force
php artisan optimize
php artisan up
```

Pada deployment pertama, buat `APP_KEY` satu kali sebelum aplikasi menerima trafik dan jalankan seeder dengan konfigurasi lingkungan produksi untuk membuat administrator awal. Jangan membuat ulang kunci saat rilis rutin. Setelah deployment, verifikasi `/up`, login administrator, dan satu asesmen uji hingga selesai.

### Pengaturan email dan antrean di InfinityFree

InfinityFree tidak menyediakan worker antrean Laravel yang berjalan terus-menerus. Atur `QUEUE_CONNECTION=sync` di lingkungan hosting agar job email hasil dijalankan saat peserta mengirim asesmen. Jangan mengandalkan `php artisan queue:work` di hosting tersebut. Hasil asesmen disimpan sebelum pengiriman email; kegagalan email tidak membatalkan hasil dan status pengiriman dicatat sebagai gagal.

Gunakan penyedia SMTP eksternal beserta kredensial yang diterbitkannya. Untuk InfinityFree, gunakan host SMTP penyedia dengan port `587` dan STARTTLS, misalnya:

```dotenv
QUEUE_CONNECTION=sync
MAIL_MAILER=smtp
MAIL_SCHEME=smtp
MAIL_HOST=your-provider-smtp-host
MAIL_PORT=587
MAIL_USERNAME=your-smtp-username
MAIL_PASSWORD=your-new-smtp-password
MAIL_FROM_ADDRESS=verified-sender@example.com
MAIL_FROM_NAME="${APP_NAME}"
```

Ganti nilai contoh hanya di lingkungan hosting; jangan pernah melakukan commit kredensial SMTP. `MAIL_MAILER=log` hanya menulis pesan ke log aplikasi dan tidak mengirim email. `127.0.0.1` bukan layanan SMTP eksternal. Setelah mengubah konfigurasi lingkungan, bersihkan cache konfigurasi Laravel menggunakan cara deployment yang didukung hosting. Uji pengiriman dengan asesmen baru; hasil lama tidak dikirim ulang secara otomatis.

## Pencadangan dan pemulihan database

Buat cadangan database sebelum setiap migrasi skema dan secara berkala sesuai kebijakan pemilik data. Simpan cadangan dalam keadaan terenkripsi, dengan akses terbatas, dan terpisah dari host aplikasi. Untuk MySQL/MariaDB, gunakan alat klien database dan masukkan kata sandi saat diminta:

```sh
mysqldump --single-transaction \
  --host="$DB_HOST" --port="$DB_PORT" --user="$DB_USERNAME" --password \
  "$DB_DATABASE" > pelita-backup.sql
```

Pulihkan cadangan secara berkala ke database terisolasi, lalu verifikasi data dan skemanya sebelum mengandalkan cadangan tersebut:

```sh
mysql --host="$DB_HOST" --port="$DB_PORT" --user="$DB_USERNAME" --password \
  pelita_restore < pelita-backup.sql
```

Arahkan salinan aplikasi di lingkungan uji ke database hasil pemulihan dan verifikasi dengan `php artisan migrate:status`. Setelah itu, masuk ke aplikasi dan periksa beberapa hasil asesmen. Jangan menguji pemulihan terhadap database produksi.

Deployment produksi, pencadangan terjadwal, dan simulasi pemulihan belum dijalankan dari workspace ini karena memerlukan server dan database target yang telah disiapkan.

## Akses dan pengelolaan data

- Peserta tidak perlu membuat akun. Setiap asesmen menggunakan URL UUID acak; perlakukan URL tersebut sebagai tautan akses rahasia dan jangan membagikannya.
- Rute admin memerlukan akun administrator. Alur peserta publik menampilkan pemberitahuan singkat tentang penggunaan data.
- Masa retensi dan penghapusan data harus ditetapkan oleh pemilik data. Tetapkan kebijakan operasional retensi dan pencadangan sebelum mengumpulkan data peserta produksi.

## Pengujian

```sh
php artisan test
```

Pengujian menggunakan database SQLite dalam memori dan memerlukan ekstensi `pdo_sqlite`.
