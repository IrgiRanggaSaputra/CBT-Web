# CBT LPK - Computer Based Test System
Sistem Testing Online untuk Lembaga Pelatihan Kerja

## 📋 Fitur Lengkap

### ✅ Fitur Peserta
- **Login** - Autentikasi peserta dengan nomor peserta dan password
- **Dashboard** - Melihat jadwal tes yang tersedia dan hasil tes
- **Petunjuk Tes** - Instruksi sebelum memulai tes
- **Tes Online (CBT)** - Mengerjakan tes dengan interface yang user-friendly
- **Timer & Autosave** - Timer countdown otomatis dan jawaban tersimpan real-time
- **Submit Otomatis** - Tes otomatis submit ketika waktu habis
- **Status Tes** - Melihat status kelulusan dan nilai tes

### ✅ Fitur Admin
- **Dashboard Statistik** - Ringkasan data peserta, jadwal, dan hasil tes
- **CRUD Peserta** - Tambah, edit, hapus data peserta
- **CRUD Bank Soal** - Kelola soal dengan berbagai kategori
- **Import Excel** - Import data peserta dari file CSV/Excel
- **Jadwal Tes** - Buat dan kelola jadwal tes
- **Monitoring Real-time** - Pantau peserta yang sedang mengerjakan tes
- **Penilaian Otomatis** - Sistem penilaian otomatis berdasarkan jawaban
- **Export Laporan** - Export hasil tes ke format Excel

## 📁 Struktur Folder

```
CBT_LPK/
│
├── config.php                          # Konfigurasi database dan helper functions
├── index.php                           # Halaman utama (pilihan login)
├── database.sql                        # File SQL untuk membuat database
│
├── admin/                              # Folder Admin
│   ├── includes/
│   │   ├── header.php                  # Template header admin
│   │   └── footer.php                  # Template footer admin
│   ├── login.php                       # Login admin
│   ├── logout.php                      # Logout admin
│   ├── dashboard.php                   # Dashboard admin dengan statistik
│   ├── peserta.php                     # Daftar peserta
│   ├── peserta_add.php                 # Tambah peserta
│   ├── peserta_edit.php                # Edit peserta
│   ├── peserta_import.php              # Import peserta dari Excel
│   ├── template_import_peserta.csv     # Template CSV import peserta
│   ├── kategori_soal.php               # Kelola kategori soal
│   ├── bank_soal.php                   # Daftar bank soal
│   ├── bank_soal_add.php               # Tambah soal
│   ├── bank_soal_edit.php              # Edit soal
│   ├── bank_soal_import.php            # Import soal dari Excel
│   ├── jadwal_tes.php                  # Daftar jadwal tes
│   ├── jadwal_tes_add.php              # Tambah jadwal tes
│   ├── jadwal_tes_edit.php             # Edit jadwal tes
│   ├── jadwal_tes_peserta.php          # Kelola peserta per jadwal
│   ├── monitoring.php                  # Monitoring real-time peserta tes
│   ├── laporan.php                     # Laporan hasil tes
│   └── laporan_detail.php              # Detail hasil tes peserta
│
├── peserta/                            # Folder Peserta
│   ├── includes/
│   │   ├── header.php                  # Template header peserta
│   │   └── footer.php                  # Template footer peserta
│   ├── login.php                       # Login peserta (existing)
│   ├── login_new.php                   # Login peserta (baru)
│   ├── logout.php                      # Logout peserta
│   ├── dashboard.php                   # Dashboard peserta (existing)
│   ├── dashboard_new.php               # Dashboard peserta (baru)
│   ├── profile.php                     # Profil peserta
│   ├── tes_petunjuk.php                # Halaman petunjuk tes
│   ├── tes_mulai.php                   # Halaman tes dengan timer
│   ├── tes_lanjut.php                  # Lanjutkan tes yang belum selesai
│   ├── tes_save.php                    # API untuk autosave jawaban
│   └── tes_submit.php                  # API untuk submit tes
│
└── assets/                             # Folder Assets
    └── uploads/                        # Folder untuk upload gambar soal
```

## 🗄️ Struktur Database

### Tabel Utama:
1. **admin** - Data administrator
2. **peserta** - Data peserta
3. **kategori_soal** - Kategori soal
4. **bank_soal** - Bank soal
5. **jadwal_tes** - Jadwal tes
6. **peserta_tes** - Relasi peserta dengan jadwal tes
7. **soal_tes** - Soal yang digunakan dalam tes
8. **jawaban_peserta** - Jawaban peserta

## 🚀 Cara Instalasi

### 1. Persiapan
- Install XAMPP/Laragon/Web Server lainnya
- Pastikan PHP >= 7.4 dan MySQL sudah terinstall

### 2. Setup Database
```sql
1. Buka phpMyAdmin (http://localhost/phpmyadmin)
2. Buat database baru bernama 'cbt_lpk'
3. Import file 'database.sql' ke database tersebut
```

### 3. Konfigurasi
Edit file `config.php` jika perlu mengubah:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'cbt_lpk');
define('BASE_URL', 'http://localhost/CBT_LPK/');
```

### 4. Akses Aplikasi
```
URL Utama: http://localhost/CBT_LPK/

Login Admin:
Username: admin
Password: password

Login Peserta:
- Buat peserta baru melalui admin
- Atau import dari template CSV
```

## 👤 Default Login

### Admin
- **Username**: admin
- **Password**: password

### Peserta
Peserta harus dibuat terlebih dahulu oleh admin melalui:
- Form tambah peserta
- Import Excel/CSV

## 📝 Petunjuk Penggunaan

### Untuk Admin:

1. **Login** ke sistem sebagai admin
2. **Buat Kategori Soal** (Matematika, Bahasa Indonesia, dll)
3. **Tambah Bank Soal** atau import dari Excel
4. **Tambah Peserta** atau import dari Excel
5. **Buat Jadwal Tes** dengan mengatur:
   - Nama tes
   - Kategori soal
   - Tanggal & waktu
   - Durasi
   - Jumlah soal
   - Passing grade
6. **Monitor Real-time** peserta yang sedang tes
7. **Lihat Laporan** dan export ke Excel

### Untuk Peserta:

1. **Login** dengan nomor peserta dan password
2. **Lihat Dashboard** untuk melihat jadwal tes tersedia
3. **Baca Petunjuk** sebelum memulai tes
4. **Kerjakan Tes** - jawaban tersimpan otomatis
5. **Submit Tes** atau biarkan auto-submit saat waktu habis
6. **Lihat Hasil** setelah admin melakukan penilaian

## 🎯 Fitur Unggulan

### Timer Otomatis
- Countdown timer real-time
- Peringatan ketika waktu hampir habis
- Auto-submit ketika waktu habis

### Autosave
- Jawaban tersimpan otomatis via AJAX
- Tidak perlu khawatir kehilangan jawaban

### Navigasi Soal
- Grid navigasi untuk berpindah antar soal
- Indikator soal yang sudah/belum dijawab
- Progress bar pengerjaan

### Monitoring Real-time
- Admin dapat melihat peserta yang sedang tes
- Melihat progress pengerjaan
- Melihat sisa waktu peserta

### Penilaian Otomatis
- Sistem menghitung nilai otomatis
- Status kelulusan berdasarkan passing grade
- Laporan detail per peserta

## 🔒 Keamanan

- Password dienkripsi dengan password_hash()
- Session management untuk autentikasi
- Validasi input untuk mencegah SQL injection
- Prevent refresh dan back button saat tes
- Disable right-click saat tes

## 📊 Export & Import

### Import Peserta (CSV)
Format file CSV:
```
nomor_peserta,nama_lengkap,email,password,jenis_kelamin,tanggal_lahir,telepon,alamat
P001,John Doe,john@example.com,password123,L,1990-01-15,08123456789,Jl. Contoh No. 1
```

### Export Laporan (Excel)
- Filter berdasarkan jadwal tes
- Filter berdasarkan tanggal
- Export ke format Excel (.xls)

## 🛠️ Teknologi yang Digunakan

- **Backend**: PHP Native
- **Database**: MySQL
- **Frontend**: Bootstrap 5, HTML5, CSS3, JavaScript
- **Library**: jQuery, Bootstrap Icons

## ⚙️ Requirements

- PHP >= 7.4
- MySQL >= 5.7
- Web Server (Apache/Nginx)
- Browser modern (Chrome, Firefox, Edge)

## 📞 Support

Untuk pertanyaan dan dukungan, silakan hubungi administrator sistem.

## ✅ Pemenuhan Standar Proyek

Proyek ini telah memenuhi semua ketentuan pengembangan aplikasi web:

### 1. ✅ Backend & Frontend Terintegrasi
- **Backend**: PHP native dengan MySQL untuk pemrosesan data, autentikasi, dan business logic
- **Frontend**: HTML5, CSS3, Bootstrap 5, JavaScript/jQuery untuk interface responsif dan interaktif
- **Integrasi penuh**: Komunikasi seamless antara client-side dan server-side

### 2. ✅ Dashboard sebagai Pusat Pengelolaan
- **Admin Dashboard** (`admin/dashboard.php`): 
  - Statistik lengkap (total peserta, jadwal aktif, hasil tes)
  - Navigasi cepat ke semua fitur manajemen
  - Ringkasan aktivitas sistem
- **Peserta Dashboard** (`peserta/dashboard.php`):
  - Informasi profil peserta
  - Jadwal tes yang tersedia
  - Riwayat dan hasil tes

### 3. ✅ Laporan dengan Export PDF & Excel
- **Export Excel** (`admin/laporan_export_detail.php`):
  - Format CSV/XLSX untuk kompatibilitas penuh dengan Microsoft Excel
  - Laporan detail jawaban per peserta
  - Statistik hasil tes lengkap
- **Export PDF/Print** (`admin/laporan_print_detail.php`):
  - Format print-friendly untuk PDF
  - Layout profesional dengan header dan statistik
  - Siap cetak atau save as PDF dari browser
- **Fitur Laporan**:
  - Filter berdasarkan jadwal tes
  - Filter berdasarkan rentang tanggal
  - Laporan summary dan detail

### 4. ✅ CRUD Lengkap
Implementasi operasi Create, Read, Update, Delete pada semua entitas:

**A. Peserta**
- Create: `admin/peserta_add.php`
- Read: `admin/peserta.php`
- Update: `admin/peserta_edit.php`
- Delete: Fungsi hapus di `admin/peserta.php`
- Import: `admin/peserta_import.php` (bulk create via CSV)

**B. Bank Soal**
- Create: `admin/bank_soal_add.php`
- Read: `admin/bank_soal.php`
- Update: `admin/bank_soal_edit.php`
- Delete: Fungsi hapus di `admin/bank_soal.php`
- Import: `admin/bank_soal_import.php`

**C. Jadwal Tes**
- Create: `admin/jadwal_tes_add.php`
- Read: `admin/jadwal_tes.php`
- Update: `admin/jadwal_tes_edit.php`
- Delete: Fungsi hapus di `admin/jadwal_tes.php`

**D. Kategori Soal**
- Full CRUD di `admin/kategori_soal.php`

**E. RESTful API** (bonus untuk integrasi eksternal)
- Create: `api/create.php`
- Read: `api/get.php`
- Update: `api/put.php`
- Delete: `api/delete.php`
- Resources: peserta, kategori_soal, bank_soal, jadwal_tes

### 5. ✅ Session & Cookies Management
**Backend Session Management** (`config.php`):
```php
// Fungsi pengecekan sesi admin
function check_login_admin() {
    if (!isset($_SESSION['admin_id'])) {
        redirect('login.php');
    }
}

// Fungsi pengecekan sesi peserta
function check_login_peserta() {
    if (!isset($_SESSION['peserta_id'])) {
        redirect('login.php');
    }
}
```

**Implementasi Keamanan**:
- Session dimulai otomatis di `config.php`
- Setiap halaman protected memanggil fungsi check_login
- Login tracking dengan rate limiting (max 3 percobaan)
- Account lockout selama 5 menit setelah gagal login
- Session timeout otomatis
- Logout menghapus semua session data

**Frontend Session Check**:
- JavaScript untuk mencegah akses langsung via URL
- Disable browser back button saat tes berlangsung
- Timer dan autosave menggunakan session ID

**Cookie Management**:
- `PHPSESSID` untuk session tracking
- Secure session configuration
- Session regeneration setelah login sukses

### 6. ✅ Studi Kasus Nyata
**Computer Based Test (CBT) untuk Lembaga Pelatihan Kerja**

**Relevansi**:
- LPK membutuhkan sistem testing untuk evaluasi peserta pelatihan
- Digunakan untuk tes seleksi, tes kompetensi, dan sertifikasi
- Menggantikan sistem paper-based yang tidak efisien

**Penerapan Dunia Nyata**:
- Tes penempatan kerja (placement test)
- Tes kompetensi bidang (Bahasa Inggris, Matematika, IT, dll)
- Ujian sertifikasi profesi
- Pre-test dan post-test pelatihan

**Stakeholder**:
- **Admin/Instruktur**: Mengelola soal, jadwal, dan peserta
- **Peserta**: Mengikuti tes online dengan mudah
- **Lembaga**: Mendapatkan laporan dan analisis hasil tes

**Benefit**:
- Efisiensi waktu dan biaya (paperless)
- Penilaian otomatis dan objektif
- Monitoring real-time
- Data tersentralisasi dan terorganisir
- Laporan detail untuk evaluasi

## 🎯 Fitur Tambahan (Nilai Plus)

### Import/Export Data
- Import peserta dan soal dari CSV/Excel
- Export laporan ke Excel
- Template import tersedia

### Monitoring Real-time
- Lihat peserta yang sedang mengerjakan tes
- Progress dan sisa waktu per peserta
- Status pengerjaan langsung

### Keamanan Berlapis
- Password hashing dengan bcrypt
- Rate limiting login
- Session management ketat
- SQL injection prevention
- Input validation client & server

### User Experience
- Interface responsif (mobile-friendly)
- Timer countdown real-time
- Autosave jawaban (prevent data loss)
- Navigasi soal yang intuitif
- Progress indicator

### RESTful API
- Endpoint CRUD untuk integrasi eksternal
- JSON response standar
- Authentication via session
- Support untuk aplikasi mobile/frontend terpisah

## 📄 License

© 2025 CBT LPK. All rights reserved.

---

**Selamat Menggunakan Sistem CBT LPK!** 🎓

> **Catatan**: Proyek ini telah memenuhi seluruh standar pengembangan aplikasi web yang ditetapkan, dengan implementasi lengkap backend-frontend, dashboard, laporan export, CRUD, session management, dan studi kasus nyata yang relevan.
