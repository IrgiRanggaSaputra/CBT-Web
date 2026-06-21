# KIYORAKA CBT - Computer Based Test Portal

KIYORAKA CBT adalah platform ujian berbasis komputer (Computer Based Test) premium yang dirancang untuk menyelenggarakan ujian online secara efisien, aman, dan dinamis. Sistem ini memisahkan peran Administrator dan Peserta secara cerdas dengan antarmuka modern, responsif, dan estetika premium yang konsisten.

---

## 🚀 Fitur Utama

### 1. Sistem Login Cerdas & Terpadu
- **Pendeteksi Peran Otomatis (Dynamic Role Detection):** Menggunakan satu formulir masuk terpadu. Sistem mendeteksi secara real-time apakah pengguna adalah Administrator (berdasarkan username/email) atau Peserta (berdasarkan Nomor Peserta) saat mengetik, lalu menyesuaikan badge tampilan secara dinamis.
- **Desain Modern:** Memuat logo instansi "Kiyoraka" dengan tata letak minimalis dan mewah.

### 2. Dashboard Administrator (Panel Kontrol)
- **Ringkasan Operasional (Dashboard Summary):** Statistik cepat mengenai jumlah peserta aktif, total bank soal, jadwal aktif, serta statistik kelulusan (lulus/tidak lulus).
- **Manajemen Peserta (Participant Management):**
  - CRUD Akun Peserta Ujian.
  - Fitur Impor Peserta secara massal menggunakan file CSV/Excel dengan template yang disediakan.
- **Kategori Soal:** Pengelompokan soal secara modular untuk memudahkan pengelolaan bank soal.
- **Bank Soal (Question Bank):**
  - Pembuatan soal pilihan ganda (A-E) lengkap dengan gambar pendukung, bobot nilai, dan filter kategori.
  - Impor soal massal menggunakan file CSV/Excel.
  - **Filter Sebelum Penulisan Soal:** Mencegah kesalahan dengan memfilter kategori terlebih dahulu sebelum admin dapat menambahkan soal baru.
- **Jadwal Tes (Exam Scheduling):**
  - Pengaturan durasi ujian, batas kelulusan (passing grade), jumlah soal acak yang diambil, serta rentang tanggal aktif.
  - **Daftarkan Semua Peserta (Bulk Enroll):** Fitur sekali klik untuk mendaftarkan seluruh peserta aktif ke dalam jadwal ujian tertentu secara instan.
- **Monitoring Peserta Real-time:**
  - Panel pemantauan langsung untuk melihat peserta yang sedang mengerjakan ujian.
  - Dilengkapi dengan *progress bar* pengerjaan soal dan sisa waktu pengerjaan masing-masing peserta.
- **Laporan & Rapor Hasil Ujian:**
  - Tampilan tabel premium hasil tes yang rapi dengan indikator kelulusan berbasis badge berwarna.
  - Ekspor hasil laporan ke format Excel (CSV).
  - Cetak lembar rapor hasil ujian per peserta dalam format cetak/PDF standar.

### 3. Dashboard Peserta & Modul Ujian
- **Ujian Tersedia (My Tests):** Menampilkan daftar ujian yang sedang aktif dan siap untuk diikuti oleh peserta.
- **Antarmuka Ujian Interaktif:**
  - Tata letak ujian satu per satu dengan panel navigasi nomor soal.
  - Penanda soal ragu-ragu (*flagged question*).
  - Status pengerjaan real-time.
- **Pengacakan Soal Menggunakan Fisher-Yates (Fisher-Yates Shuffle):**
  - Soal dan pilihan jawaban diacak secara dinamis untuk setiap peserta menggunakan seed unik yang terikat pada sesi tes peserta. Hal ini menjamin urutan tetap konsisten bagi peserta saat halaman dimuat ulang (refresh), namun sepenuhnya berbeda antar peserta.
- **Fitur Keamanan Ujian (Anti-Cheat System):**
  - **Deteksi Kehilangan Fokus (Focus Loss Monitoring):** Sistem mencatat dan memberikan peringatan keras jika peserta berpindah tab, membuka jendela lain, atau meminimalkan browser.
  - **Pembatasan Pintasan DevTools:** Memblokir tombol `F12`, `Ctrl+Shift+I` (Inspect Element), `Ctrl+U` (View Source), serta klik kanan.
  - **Pencegahan Tombol Kembali (Back Button Block):** Menghindari keluarnya peserta dari halaman ujian secara tidak sengaja melalui tombol navigasi browser.
  - **Autosubmit Ujian:** Ujian akan otomatis terkirim dan tersimpan saat waktu pengerjaan habis.

---

## 🛠️ Teknologi yang Digunakan

- **Backend:** Laravel Framework (PHP >= 8.x)
- **Frontend:** Vanilla JavaScript & HTML5 (Tanpa library/framework JS berat)
- **Styling:** Vanilla CSS (Aesthetically rich, responsive grid, custom glassmorphism)
- **Database:** MySQL
- **Timezone:** `Asia/Jakarta` (WIB) — menjamin keselarasan waktu server Laravel dengan database lokal dalam penjadwalan ujian.

---

## ⚙️ Persyaratan Sistem

- PHP >= 8.1
- Composer
- MySQL >= 5.7 atau MariaDB >= 10.3
- Web Server (Apache / Nginx / Artisan Development Server)

---

## 🔧 Panduan Instalasi & Penggunaan

1. **Clone Repositori**
   ```bash
   git clone https://github.com/username/CBT-Web.git
   cd CBT-Web
   ```

2. **Instalasi Dependensi PHP (Composer)**
   ```bash
   composer install
   ```

3. **Konfigurasi Environment**
   Salin file `.env.example` menjadi `.env`:
   ```bash
   cp .env.example .env
   ```
   *Sesuaikan pengaturan koneksi database Anda di dalam file `.env` (misalnya `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`).*

4. **Generate Aplikasi Key**
   ```bash
   php artisan key:generate
   ```

5. **Migrasi Database & Seeding Data**
   Jalankan perintah berikut untuk membuat tabel dan mengisi data awal (seperti akun administrator default):
   ```bash
   php artisan migrate --seed
   ```

6. **Jalankan Aplikasi**
   Jalankan server pengembangan Laravel lokal:
   ```bash
   php artisan serve
   ```
   Buka peramban (browser) dan akses `http://127.0.0.1:8000`.

---

## 🧪 Pengujian Sistem

Untuk menjalankan suite pengujian otomatis (automated unit/feature testing):
```bash
php artisan test
```
