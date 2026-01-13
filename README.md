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

---

# 📱 PERSYARATAN IMPLEMENTASI APLIKASI MOBILE FLUTTER - PESERTA

## I. OVERVIEW

Aplikasi mobile Flutter untuk peserta adalah komplemen dari sistem CBT LPK web. Aplikasi ini memungkinkan peserta mengakses platform testing dari smartphone dengan pengalaman yang optimal, responsif, dan intuitif.

**Tujuan**: Memberikan akses mudah kepada peserta untuk login, melihat jadwal tes, membaca petunjuk, dan mengerjakan soal tes langsung dari perangkat mobile.

---

## II. REQUIREMENT FUNGSIONAL - FITUR PESERTA

### A. Authentication & Authorization

#### 1. **Login Screen** ✓
- **Input Fields**:
  - Nomor Peserta (text input)
  - Password (password input dengan toggle visibility)
  - Remember Me (checkbox - opsional)
  
- **Validasi**:
  - Email/nomor peserta tidak boleh kosong
  - Password minimal 6 karakter
  - Format input validation client-side
  
- **Fungsi**:
  - Login dengan nomor peserta & password
  - Tampilkan error message jika login gagal
  - Loading indicator saat proses login
  - Remember me untuk auto-login (optional)
  - Forgot password link (opsional)

- **API Endpoint**:
  ```
  POST /api/auth_peserta.php
  
  Request Body:
  {
    "nomor_peserta": "P001",
    "password": "password123"
  }
  
  Response Success (200):
  {
    "status": "success",
    "message": "Login berhasil",
    "data": {
      "id_peserta": 1,
      "nomor_peserta": "P001",
      "nama_lengkap": "John Doe",
      "email": "john@example.com",
      "jenis_kelamin": "L",
      "tanggal_lahir": "1990-01-15",
      "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
      "firebase_uid": "firebase_uid_string"
    }
  }
  
  Response Error (401):
  {
    "status": "error",
    "message": "Nomor peserta atau password salah"
  }
  ```

#### 2. **Logout** ✓
- Tombol logout di menu/settings
- Hapus token dari local storage
- Redirect ke login screen
- Confirmation dialog sebelum logout

- **API Endpoint**:
  ```
  POST /api/peserta/logout.php
  
  Header:
  Authorization: {token}
  
  Response (200):
  {
    "status": "success",
    "message": "Logout berhasil"
  }
  ```

---

### B. Dashboard Peserta

#### 3. **Home/Dashboard Screen** ✓
Tampilkan:
- **Greeting**: "Assalamualaikum, [Nama Peserta]"
- **Status Peserta**: Aktif/Tidak Aktif
- **Statistik Cepat**:
  - Total tes tersedia
  - Total tes selesai
  - Rata-rata nilai
  - Total lulus/tidak lulus
  
- **Widget-Widget**:
  1. **Jadwal Tes Mendatang** (Upcoming Tests)
     - Tampilkan 3-5 jadwal tes yang akan datang
     - Info: nama tes, tanggal, waktu, durasi
     - Status: belum mulai, sedang berlangsung, selesai
     - Button "Mulai Tes" / "Lanjut Tes" / "Lihat Hasil"
  
  2. **Tes Yang Belum Selesai** (In Progress)
     - Tes yang sedang berlangsung atau ditunda
     - Progress indicator
     - Button "Lanjut Tes"
  
  3. **Riwayat Tes** (Test History)
     - Daftar tes yang sudah selesai
     - Info: nama tes, tanggal selesai, nilai, status kelulusan
     - Button "Lihat Detail"
  
  4. **Quick Actions**:
     - Profile Peserta
     - Pengaturan
     - Bantuan

- **API Endpoint**:
  ```
  GET /api/peserta/dashboard.php
  
  Header:
  Authorization: {token}
  
  Response (200):
  {
    "status": "success",
    "data": {
      "peserta": {
        "id_peserta": 1,
        "nama_lengkap": "John Doe",
        "nomor_peserta": "P001",
        "email": "john@example.com"
      },
      "statistik": {
        "total_tes": 10,
        "tes_selesai": 7,
        "tes_lulus": 6,
        "tes_gagal": 1,
        "rata_rata_nilai": 78.5
      },
      "jadwal_mendatang": [
        {
          "id_jadwal": 1,
          "nama_tes": "Tes Matematika",
          "kategori": "Matematika",
          "tanggal_mulai": "2025-01-20 09:00:00",
          "tanggal_selesai": "2025-01-20 11:00:00",
          "durasi": 120,
          "jumlah_soal": 50,
          "passing_grade": 70,
          "status": "belum_mulai"
        }
      ],
      "tes_dalam_progress": [
        {
          "id_peserta_tes": 5,
          "id_jadwal": 1,
          "nama_tes": "Tes Bahasa Inggris",
          "waktu_mulai": "2025-01-15 10:00:00",
          "durasi": 120,
          "sisa_waktu": 45,
          "status_tes": "sedang_tes"
        }
      ],
      "riwayat_tes": [
        {
          "id_peserta_tes": 1,
          "nama_tes": "Tes Dasar",
          "tanggal_selesai": "2025-01-10 11:30:00",
          "nilai": 85,
          "status_kelulusan": "lulus"
        }
      ]
    }
  }
  ```

---

### C. Profile Management

#### 4. **Profile Screen** ✓
Tampilkan informasi peserta:
- Profile picture (avatar)
- Nama lengkap
- Nomor peserta
- Email
- Jenis kelamin
- Tanggal lahir
- Nomor telepon
- Alamat
- Status (Aktif/Tidak Aktif)

**Fungsionalitas**:
- Edit Profile (edit nama, email, telepon, alamat)
- Change Password
- Logout button

- **API Endpoint - Get Profile**:
  ```
  GET /api/peserta/profile.php
  
  Header:
  Authorization: {token}
  
  Response (200):
  {
    "status": "success",
    "data": {
      "id_peserta": 1,
      "nomor_peserta": "P001",
      "nama_lengkap": "John Doe",
      "email": "john@example.com",
      "jenis_kelamin": "L",
      "tanggal_lahir": "1990-01-15",
      "telepon": "08123456789",
      "alamat": "Jl. Contoh No. 1",
      "status": "aktif",
      "avatar_url": "https://api.example.com/uploads/avatar/P001.jpg"
    }
  }
  ```

- **API Endpoint - Update Profile**:
  ```
  PUT /api/peserta/profile.php
  
  Header:
  Authorization: {token}
  Content-Type: application/json
  
  Request Body:
  {
    "nama_lengkap": "John Doe",
    "email": "john.doe@example.com",
    "telepon": "08123456789",
    "alamat": "Jl. Contoh No. 2"
  }
  
  Response (200):
  {
    "status": "success",
    "message": "Profil berhasil diperbarui"
  }
  ```

- **API Endpoint - Change Password**:
  ```
  PUT /api/peserta/change_password.php
  
  Header:
  Authorization: {token}
  Content-Type: application/json
  
  Request Body:
  {
    "password_lama": "old_password",
    "password_baru": "new_password",
    "konfirmasi_password": "new_password"
  }
  
  Response (200):
  {
    "status": "success",
    "message": "Password berhasil diubah"
  }
  
  Response Error (400):
  {
    "status": "error",
    "message": "Password lama tidak sesuai"
  }
  ```

---

### D. Jadwal Tes & Petunjuk

#### 5. **Schedule/Jadwal Tes Screen** ✓
Tampilkan daftar lengkap jadwal tes dengan filter:

**List Jadwal Tes**:
- Nama tes
- Kategori
- Tanggal & waktu mulai
- Durasi
- Jumlah soal
- Status (belum mulai, sedang berlangsung, selesai)
- Passing grade
- Tombol aksi (Buka, Lanjut, Lihat Hasil)

**Filter & Search**:
- Filter berdasarkan status (belum mulai, aktif, selesai)
- Search berdasarkan nama tes
- Sort berdasarkan tanggal

- **API Endpoint - List Jadwal Tes**:
  ```
  GET /api/peserta/tes/list.php?status=belum_mulai&search=&sort=date
  
  Header:
  Authorization: {token}
  
  Response (200):
  {
    "status": "success",
    "data": [
      {
        "id_jadwal": 1,
        "id_peserta_tes": 5,
        "nama_tes": "Tes Matematika",
        "kategori": "Matematika",
        "tanggal_mulai": "2025-01-20 09:00:00",
        "tanggal_selesai": "2025-01-20 11:00:00",
        "durasi": 120,
        "jumlah_soal": 50,
        "passing_grade": 70,
        "instruksi": "Baca dengan teliti dan jawab dengan benar",
        "status": "belum_mulai",
        "nilai": null,
        "status_kelulusan": null
      }
    ]
  }
  ```

#### 6. **Instruction/Petunjuk Tes Screen** ✓
Sebelum peserta memulai tes, tampilkan:
- **Informasi Tes**:
  - Nama tes
  - Durasi (menit)
  - Jumlah soal
  - Passing grade
  
- **Petunjuk Tes** (dari field instruksi):
  - Aturan pengerjaan
  - Waktu pengerjaan
  - Sistem penilaian
  - Larangan/batasan
  
- **Syarat & Ketentuan**:
  - Checkbox "Saya telah membaca petunjuk"
  - Checkbox "Saya siap untuk memulai tes"
  
- **Tombol Aksi**:
  - Button "Kembali"
  - Button "Mulai Tes" (enabled setelah kedua checkbox dicentang)

- **API Endpoint**:
  ```
  GET /api/peserta/tes/detail.php?id_jadwal={id_jadwal}
  
  Header:
  Authorization: {token}
  
  Response (200):
  {
    "status": "success",
    "data": {
      "id_jadwal": 1,
      "nama_tes": "Tes Matematika",
      "durasi": 120,
      "jumlah_soal": 50,
      "passing_grade": 70,
      "instruksi": "1. Baca soal dengan teliti\n2. Pilih salah satu jawaban\n3. Waktu pengerjaan 120 menit\n...",
      "kategori": "Matematika"
    }
  }
  ```

---

### E. Test Interface (CBT)

#### 7. **Test Screen - Main Interface** ✓
Interface utama untuk mengerjakan tes dengan fitur lengkap:

**Layout Components**:

1. **Top Header Bar**:
   - Nama tes (left)
   - Timer countdown (center) - format MM:SS
     - Color: Normal (white), Warning (orange) saat <= 10 menit
     - Color: Critical (red) saat <= 5 menit
   - Tombol info/bantuan (right)

2. **Main Content Area**:
   - Soal nomor X dari Y
   - Pertanyaan/soal
   - Gambar soal (jika ada)
   - Pilihan jawaban (A, B, C, D, E) dengan radio button
   - Status: saved/unsaved indicator

3. **Left Sidebar - Question Navigator**:
   - Grid navigasi soal (5x5 atau 5x10 grid)
   - Indikator soal:
     - Kosong (belum dijawab)
     - Berwarna (sudah dijawab)
     - Highlight (soal saat ini)
   - Scroll untuk soal lebih dari 50
   - Click untuk pindah ke soal tersebut

4. **Bottom Navigation**:
   - Button "Previous" (prev soal)
   - Button "Next" (next soal)
   - Button "Submit Tes" (dengan confirmation dialog)
   - Button "Clear Answer" (hapus jawaban saat ini)

**Fitur Keamanan**:
- Prevent minimize/background app (show warning)
- Disable screenshot & screen recording
- Prevent copy-paste
- Disable back button (confirm before exit)
- Auto-submit saat waktu habis
- Auto-lock tes saat time's up

- **API Endpoint - Start Test**:
  ```
  POST /api/peserta/tes/start.php
  
  Header:
  Authorization: {token}
  Content-Type: application/json
  
  Request Body:
  {
    "id_jadwal": 1
  }
  
  Response (200):
  {
    "status": "success",
    "data": {
      "id_peserta_tes": 5,
      "waktu_mulai": "2025-01-20 09:00:00",
      "durasi": 120,
      "soal": [
        {
          "id_soal_tes": 1,
          "nomor_urut": 1,
          "id_soal": 10,
          "pertanyaan": "Berapa hasil dari 2 + 2?",
          "pilihan_a": "2",
          "pilihan_b": "3",
          "pilihan_c": "4",
          "pilihan_d": "5",
          "pilihan_e": null,
          "gambar": null,
          "bobot": 1
        }
      ]
    }
  }
  ```

- **API Endpoint - Get All Questions**:
  ```
  GET /api/peserta/tes/questions.php?id_peserta_tes={id_peserta_tes}
  
  Header:
  Authorization: {token}
  
  Response (200):
  {
    "status": "success",
    "data": {
      "id_peserta_tes": 5,
      "total_soal": 50,
      "soal": [
        {
          "id_soal_tes": 1,
          "nomor_urut": 1,
          "id_soal": 10,
          "pertanyaan": "...",
          "pilihan_a": "...",
          "pilihan_b": "...",
          "pilihan_c": "...",
          "pilihan_d": "...",
          "pilihan_e": null,
          "gambar": "https://api.example.com/uploads/soal/img_001.jpg",
          "bobot": 1
        }
      ],
      "jawaban_tersimpan": {
        "1": "C",
        "3": "A",
        "5": "B"
      }
    }
  }
  ```

#### 8. **Autosave Answer** ✓
Menyimpan jawaban secara otomatis setiap kali peserta mengubah pilihan:

- **Trigger**: Setiap kali radio button pilihan jawaban berubah
- **Interval**: Immediate atau max 2 detik delay (untuk debouncing)
- **Offline Support**: Queue jawaban jika offline, kirim saat online
- **Feedback**: Visual indicator "Saving..." → "Saved" atau ✓

- **API Endpoint - Save Answer**:
  ```
  POST /api/peserta/jawaban/save.php
  
  Header:
  Authorization: {token}
  Content-Type: application/json
  
  Request Body:
  {
    "id_peserta_tes": 5,
    "id_soal_tes": 1,
    "jawaban": "C"
  }
  
  Response (200):
  {
    "status": "success",
    "message": "Jawaban tersimpan",
    "data": {
      "id_jawaban": 100,
      "timestamp": "2025-01-20 09:05:30"
    }
  }
  ```

#### 9. **Submit Test** ✓
Proses submit dan penyelesaian tes:

- **Confirmation Dialog**:
  - "Apakah Anda yakin ingin menyelesaikan tes?"
  - Tampilkan ringkasan: total soal, soal terjawab, soal tidak terjawab
  - Button: "Batal" atau "Lanjutkan Submit"

- **Submit Process**:
  1. Lock interface (disable semua input)
  2. Send final answers ke server
  3. Show loading indicator
  4. Get scoring result
  5. Show completion page

- **API Endpoint - Submit Test**:
  ```
  POST /api/peserta/tes/submit.php
  
  Header:
  Authorization: {token}
  Content-Type: application/json
  
  Request Body:
  {
    "id_peserta_tes": 5,
    "jawaban": {
      "1": "C",
      "2": "A",
      "3": "B",
      "...": "..."
    }
  }
  
  Response (200):
  {
    "status": "success",
    "message": "Tes berhasil disubmit",
    "data": {
      "id_peserta_tes": 5,
      "waktu_selesai": "2025-01-20 11:05:30",
      "waktu_pengerjaan": "120 menit",
      "total_soal": 50,
      "soal_terjawab": 48,
      "soal_kosong": 2,
      "nilai_sementara": "Diproses...",
      "status": "selesai"
    }
  }
  ```

#### 10. **Test Completion Screen** ✓
Layar yang ditampilkan setelah submit tes:

- Pesan "Terima kasih telah mengerjakan tes"
- Informasi yang ditampilkan:
  - Nama tes
  - Waktu pengerjaan
  - Total soal vs soal terjawab
  - Waktu submit
  - Status "Menunggu penilaian..."
  
- Tombol:
  - "Kembali ke Dashboard"
  - "Lihat Jadwal Tes Lainnya"

---

### F. Test Results & History

#### 11. **Test Results Screen** ✓
Menampilkan hasil tes setelah dinilai oleh system:

**Informasi Hasil**:
- Nama tes
- Tanggal pengerjaan
- Waktu pengerjaan (total menit)
- Nilai akhir (angka dan grade jika ada)
- Status kelulusan (LULUS/TIDAK LULUS)
- Passing grade
- Total soal
- Soal benar
- Soal salah
- Soal kosong

**Visualisasi**:
- Progress/pie chart untuk persentase jawaban (benar/salah/kosong)
- Color indicator: Lulus (hijau), Tidak lulus (merah)

**Detail Jawaban** (Optional):
- Button "Lihat Detail Jawaban"
- Tampilkan list semua soal dengan jawaban peserta vs jawaban benar
- Indikator benar/salah dengan warna

- **API Endpoint - Get Test Result**:
  ```
  GET /api/peserta/tes/result.php?id_peserta_tes={id_peserta_tes}
  
  Header:
  Authorization: {token}
  
  Response (200):
  {
    "status": "success",
    "data": {
      "id_peserta_tes": 5,
      "nama_tes": "Tes Matematika",
      "tanggal_mulai": "2025-01-20 09:00:00",
      "tanggal_selesai": "2025-01-20 10:05:30",
      "waktu_pengerjaan": 65,
      "total_soal": 50,
      "soal_benar": 42,
      "soal_salah": 6,
      "soal_kosong": 2,
      "nilai": 84,
      "passing_grade": 70,
      "status_kelulusan": "lulus",
      "detail": [
        {
          "nomor": 1,
          "pertanyaan": "Berapa hasil dari 2 + 2?",
          "jawaban_peserta": "C",
          "jawaban_benar": "C",
          "status": "benar"
        },
        {
          "nomor": 2,
          "pertanyaan": "...",
          "jawaban_peserta": "A",
          "jawaban_benar": "B",
          "status": "salah"
        }
      ]
    }
  }
  ```

#### 12. **Test History Screen** ✓
Menampilkan daftar riwayat semua tes yang sudah dikerjakan:

- List tes dengan informasi:
  - Nama tes
  - Tanggal pengerjaan
  - Nilai
  - Status kelulusan
  - Status ("Selesai" / "Belum Dinilai")

- Fitur:
  - Search berdasarkan nama tes
  - Filter berdasarkan status (lulus/tidak lulus/belum dinilai)
  - Sort berdasarkan tanggal (newest first)
  - Click item untuk lihat detail

- **API Endpoint - Get Test History**:
  ```
  GET /api/peserta/tes/history.php?search=&filter=all&sort=date
  
  Header:
  Authorization: {token}
  
  Response (200):
  {
    "status": "success",
    "data": [
      {
        "id_peserta_tes": 1,
        "nama_tes": "Tes Dasar",
        "tanggal_mulai": "2025-01-10 10:00:00",
        "tanggal_selesai": "2025-01-10 11:30:00",
        "nilai": 85,
        "status_kelulusan": "lulus",
        "status": "selesai"
      },
      {
        "id_peserta_tes": 5,
        "nama_tes": "Tes Matematika",
        "tanggal_mulai": "2025-01-20 09:00:00",
        "tanggal_selesai": "2025-01-20 10:05:30",
        "nilai": null,
        "status_kelulusan": null,
        "status": "belum_dinilai"
      }
    ]
  }
  ```

---

## III. REQUIREMENT NON-FUNGSIONAL

### A. Performance
- **Load Time**: < 3 detik untuk setiap screen
- **API Response**: < 2 detik untuk request standar
- **Battery**: Optimasi untuk penggunaan battery efficient
- **Network**: Bekerja dengan jaringan 3G+, offline caching untuk data tertentu

### B. Compatibility
- **Platform**: iOS (12+), Android (8+)
- **Device**: Support semua ukuran layar (phones)
- **Screen Resolution**: Minimum 480px width
- **Orientation**: Portrait (primary), Landscape (supported)

### C. Security
- **Authentication**: JWT token atau session-based
- **Encryption**: HTTPS untuk semua komunikasi
- **Data Protection**: Sensitive data encrypted (password, token)
- **Secure Storage**: Use FlutterSecureStorage untuk token
- **Input Validation**: Validate semua input client & server side
- **Prevent Cheating**:
  - Disable screenshot during test
  - Disable copy-paste
  - Prevent app switching (alt+tab)
  - Warn if app goes to background
  - Auto-logout on app close

### D. User Experience
- **Responsiveness**: Interface tetap responsif saat loading
- **Error Handling**: Clear error messages
- **Offline Support**: Queue data ketika offline
- **Accessibility**: Support untuk accessibility features
- **Internationalization**: Support Indonesian language (i18n)

### E. Reliability
- **Crash Handling**: Graceful error handling, prevent crashes
- **Data Integrity**: Validate data sebelum dan sesudah operasi
- **Session Management**: Auto-logout after 30 min inactivity
- **Retry Logic**: Retry failed API calls dengan exponential backoff
- **Backup**: Local backup untuk jawaban (prevent data loss)

---

## IV. TECHNOLOGY STACK

### Frontend (Mobile)
- **Framework**: Flutter (Latest stable version)
- **Language**: Dart 3.0+
- **State Management**: Provider / Riverpod / Bloc (pilih salah satu)
- **HTTP Client**: Dio / http
- **Storage**: SharedPreferences, FlutterSecureStorage
- **UI Components**: Material 3 / Cupertino
- **Chart Library**: fl_chart / charts_flutter (untuk visualisasi hasil)
- **Timer**: Timer / CountdownTimer plugin
- **Notification**: flutter_local_notifications

### Backend (API)
- **Language**: PHP 7.4+
- **Database**: MySQL 5.7+
- **API Pattern**: RESTful
- **Response Format**: JSON
- **Authentication**: JWT / Session-based
- **Database ORM**: PDO / MySQLi
- **Validation**: Server-side validation
- **Logging**: File logging untuk debugging

### Infrastructure
- **Hosting**: Same as web app (cPanel/Linux VPS)
- **API Base URL**: `https://yourdomain.com/api/`
- **SSL Certificate**: HTTPS mandatory
- **CORS**: Enabled untuk mobile app
- **Rate Limiting**: Implement untuk prevent abuse

---

## V. API ENDPOINTS LENGKAP

### Base URL
```
https://yourdomain.com/api/
```

### Authentication Endpoints

#### 1. Login
```
POST /auth_peserta.php
Body: {nomor_peserta, password}
Response: {status, message, data: {id_peserta, nama, token}}
```

#### 2. Logout
```
POST /peserta/logout.php
Header: Authorization: {token}
Response: {status, message}
```

#### 3. Verify Token
```
POST /auth/verify-token.php
Header: Authorization: {token}
Response: {status, valid: boolean, data: {peserta_info}}
```

---

### Peserta Endpoints

#### 4. Get Profile
```
GET /peserta/profile.php
Header: Authorization: {token}
Response: {status, data: {profile_details}}
```

#### 5. Update Profile
```
PUT /peserta/profile.php
Header: Authorization: {token}
Body: {nama_lengkap, email, telepon, alamat}
Response: {status, message}
```

#### 6. Change Password
```
PUT /peserta/change-password.php
Header: Authorization: {token}
Body: {password_lama, password_baru, konfirmasi_password}
Response: {status, message}
```

#### 7. Get Dashboard Data
```
GET /peserta/dashboard.php
Header: Authorization: {token}
Response: {status, data: {statistik, jadwal_mendatang, tes_in_progress, riwayat_tes}}
```

---

### Test Schedule Endpoints

#### 8. Get Available Tests
```
GET /peserta/tes/list.php?status=&search=&sort=
Header: Authorization: {token}
Response: {status, data: [jadwal_tes_array]}
```

#### 9. Get Test Details
```
GET /peserta/tes/detail.php?id_jadwal={id}
Header: Authorization: {token}
Response: {status, data: {nama_tes, durasi, jumlah_soal, instruksi, ...}}
```

---

### Test Execution Endpoints

#### 10. Start Test
```
POST /peserta/tes/start.php
Header: Authorization: {token}
Body: {id_jadwal}
Response: {status, data: {id_peserta_tes, waktu_mulai, durasi, soal: []}}
```

#### 11. Get All Questions
```
GET /peserta/tes/questions.php?id_peserta_tes={id}
Header: Authorization: {token}
Response: {status, data: {total_soal, soal: [], jawaban_tersimpan: {}}}
```

#### 12. Get Single Question
```
GET /peserta/tes/question.php?id_soal_tes={id}
Header: Authorization: {token}
Response: {status, data: {soal_details}}
```

#### 13. Save Answer
```
POST /peserta/jawaban/save.php
Header: Authorization: {token}
Body: {id_peserta_tes, id_soal_tes, jawaban}
Response: {status, message, data: {id_jawaban, timestamp}}
```

#### 14. Save Multiple Answers
```
POST /peserta/jawaban/save-batch.php
Header: Authorization: {token}
Body: {id_peserta_tes, jawaban: [{id_soal_tes, jawaban}, ...]}
Response: {status, message}
```

#### 15. Submit Test
```
POST /peserta/tes/submit.php
Header: Authorization: {token}
Body: {id_peserta_tes, jawaban: {id_soal: answer, ...}}
Response: {status, message, data: {nilai, status_kelulusan, ...}}
```

---

### Test Results Endpoints

#### 16. Get Test Result
```
GET /peserta/tes/result.php?id_peserta_tes={id}
Header: Authorization: {token}
Response: {status, data: {nilai, status_kelulusan, detail: []}}
```

#### 17. Get Test Result Detail (with answers)
```
GET /peserta/tes/result-detail.php?id_peserta_tes={id}
Header: Authorization: {token}
Response: {status, data: {nilai, total_soal, soal_benar, detail_jawaban: []}}
```

#### 18. Get Test History
```
GET /peserta/tes/history.php?search=&filter=&sort=
Header: Authorization: {token}
Response: {status, data: [{riwayat_tes}, ...]}
```

---

## VI. PROJECT STRUCTURE (FLUTTER)

```
lib/
├── main.dart                          # Entry point aplikasi
├── constants/
│   ├── api_config.dart               # Konfigurasi API
│   ├── colors.dart                   # Warna aplikasi
│   └── strings.dart                  # Text/string constants
├── models/
│   ├── peserta_model.dart            # Model peserta
│   ├── test_model.dart               # Model tes
│   ├── question_model.dart           # Model soal
│   ├── answer_model.dart             # Model jawaban
│   └── result_model.dart             # Model hasil tes
├── services/
│   ├── api_service.dart              # HTTP client & API calls
│   ├── auth_service.dart             # Authentication logic
│   ├── storage_service.dart          # Local storage (SharedPreferences)
│   ├── secure_storage_service.dart   # Secure storage (token, dll)
│   └── connectivity_service.dart     # Network connectivity check
├── providers/
│   ├── auth_provider.dart            # Auth state management
│   ├── peserta_provider.dart         # Peserta state management
│   ├── test_provider.dart            # Test state management
│   ├── answer_provider.dart          # Answer state management
│   └── timer_provider.dart           # Timer state management
├── screens/
│   ├── auth/
│   │   ├── login_screen.dart         # Login page
│   │   └── splash_screen.dart        # Splash page
│   ├── peserta/
│   │   ├── dashboard_screen.dart     # Dashboard peserta
│   │   ├── profile_screen.dart       # Profile peserta
│   │   ├── edit_profile_screen.dart  # Edit profile
│   │   ├── change_password_screen.dart
│   │   ├── test_schedule_screen.dart # Jadwal tes
│   │   ├── test_instruction_screen.dart # Petunjuk tes
│   │   ├── test_screen.dart          # Interface tes (main)
│   │   ├── test_completion_screen.dart # Tes selesai
│   │   ├── test_result_screen.dart   # Hasil tes
│   │   ├── test_history_screen.dart  # Riwayat tes
│   │   └── test_detail_screen.dart   # Detail hasil
│   └── error/
│       └── error_screen.dart         # Error page
├── widgets/
│   ├── custom_button.dart            # Custom button
│   ├── custom_text_field.dart        # Custom input
│   ├── loading_indicator.dart        # Loading
│   ├── error_snackbar.dart           # Snackbar
│   ├── question_navigator.dart       # Question grid
│   ├── test_timer.dart               # Timer widget
│   ├── progress_chart.dart           # Chart widget
│   └── custom_card.dart              # Card widget
├── utils/
│   ├── validators.dart               # Input validators
│   ├── date_formatter.dart           # Date formatting
│   ├── app_router.dart               # Navigation routing
│   └── logger.dart                   # Logging utility
└── pubspec.yaml                      # Dependencies

```

---

## VII. DATABASE SCHEMA EXTENSION

Schema database yang sudah ada di web app sudah cukup mendukung mobile app. Column tambahan yang recommended:

```sql
-- Add column untuk Firebase UID (untuk optional Firebase integration)
ALTER TABLE peserta ADD COLUMN firebase_uid VARCHAR(255) UNIQUE;

-- Add column untuk device token (untuk push notifications)
ALTER TABLE peserta ADD COLUMN device_token VARCHAR(255);
ALTER TABLE peserta ADD COLUMN device_type ENUM('ios', 'android', 'web');

-- Add column untuk tracking login terakhir
ALTER TABLE peserta ADD COLUMN last_login DATETIME;
ALTER TABLE peserta ADD COLUMN last_device VARCHAR(255);
```

---

## VIII. IMPLEMENTATION TIMELINE

### Phase 1: Setup & Foundation (Week 1-2)
- [ ] Setup Flutter project structure
- [ ] Create models & data classes
- [ ] Setup API service & HTTP client
- [ ] Implement local storage services
- [ ] Create navigation & routing

### Phase 2: Authentication (Week 2-3)
- [ ] Login screen UI
- [ ] Auth service & provider
- [ ] Secure token storage
- [ ] Logout functionality
- [ ] Token verification & refresh

### Phase 3: Dashboard & Profile (Week 3-4)
- [ ] Dashboard screen UI
- [ ] Profile screen UI
- [ ] Edit profile functionality
- [ ] Change password functionality
- [ ] Statistics display

### Phase 4: Test Schedule (Week 4-5)
- [ ] Test schedule list screen
- [ ] Test instruction screen
- [ ] Filter & search functionality
- [ ] Test detail API integration

### Phase 5: Test Interface (Week 5-8)
- [ ] Test screen UI/UX design
- [ ] Question navigator implementation
- [ ] Timer implementation
- [ ] Answer selection & validation
- [ ] Autosave functionality
- [ ] Question navigation

### Phase 6: Submit & Results (Week 8-9)
- [ ] Submit confirmation dialog
- [ ] Test submission logic
- [ ] Result display screen
- [ ] Result detail screen
- [ ] History screen

### Phase 7: Security & Offline (Week 9-10)
- [ ] Offline support & caching
- [ ] Data encryption
- [ ] Prevent cheating measures
- [ ] Error handling & retry logic
- [ ] Input validation

### Phase 8: Testing & QA (Week 10-12)
- [ ] Unit testing
- [ ] Integration testing
- [ ] UI/UX testing
- [ ] Performance testing
- [ ] Security testing
- [ ] Bug fixes

### Phase 9: Deployment (Week 12-13)
- [ ] Build APK for Android
- [ ] Build IPA for iOS
- [ ] App store configuration
- [ ] Production API setup
- [ ] Launch & monitoring

---

## IX. DEPENDENCIES (pubspec.yaml)

```yaml
dependencies:
  flutter:
    sdk: flutter
  
  # State Management
  provider: ^6.0.0
  
  # HTTP & API
  dio: ^5.0.0
  
  # Local Storage
  shared_preferences: ^2.0.0
  flutter_secure_storage: ^9.0.0
  
  # UI/UX
  google_fonts: ^6.0.0
  intl: ^0.18.0
  
  # Chart
  fl_chart: ^0.63.0
  
  # Timer
  flutter_timer_countdown: ^2.1.6
  
  # Notification
  flutter_local_notifications: ^15.0.0
  
  # Navigation
  go_router: ^10.0.0
  
  # Connectivity
  connectivity_plus: ^4.0.0
  
  # Logging
  logger: ^2.0.0

dev_dependencies:
  flutter_test:
    sdk: flutter
```

---

## X. API RESPONSE FORMAT STANDARD

### Success Response (200)
```json
{
  "status": "success",
  "message": "Operation berhasil",
  "data": {
    // Actual data here
  }
}
```

### Error Response (400/401/403/500)
```json
{
  "status": "error",
  "message": "Deskripsi error",
  "error_code": "ERROR_CODE",
  "details": {
    // Tambahan detail error (optional)
  }
}
```

### List Response with Pagination (200)
```json
{
  "status": "success",
  "message": "Data retrieved",
  "data": [
    // Array of items
  ],
  "pagination": {
    "page": 1,
    "per_page": 20,
    "total": 100,
    "total_pages": 5
  }
}
```

---

## XI. ERROR HANDLING & VALIDATION

### Client-Side Validation
- Empty field check
- Email format validation
- Number format validation
- Password strength validation
- File size validation (untuk upload gambar)

### Server-Side Validation
- SQL injection prevention
- XSS prevention
- CSRF tokens
- Rate limiting
- Input sanitization

### Error Codes
```
200 - OK
201 - Created
400 - Bad Request
401 - Unauthorized
403 - Forbidden
404 - Not Found
500 - Internal Server Error
502 - Bad Gateway
503 - Service Unavailable
```

---

## XII. TESTING STRATEGY

### Unit Testing
```dart
// Example test
void main() {
  group('Auth Service Tests', () {
    test('Login dengan credentials valid', () async {
      final result = await authService.login('P001', 'password123');
      expect(result.status, 'success');
    });
  });
}
```

### Integration Testing
- Test API integration
- Test local storage
- Test navigation flow
- Test state management

### UI Testing
- Test screen rendering
- Test button interactions
- Test form validation
- Test error handling

### Performance Testing
- Load time < 3 seconds
- Memory usage < 150MB
- Battery consumption optimization

---

## XIII. SECURITY BEST PRACTICES

1. **Token Management**:
   - Store token di FlutterSecureStorage (bukan SharedPreferences)
   - Set token expiration
   - Implement token refresh
   - Clear token on logout

2. **API Communication**:
   - Use HTTPS only
   - Implement certificate pinning
   - Add request signing
   - Validate SSL certificates

3. **Data Protection**:
   - Encrypt sensitive data
   - Don't log sensitive information
   - Clear sensitive data from memory
   - Implement secure session timeout

4. **Prevent Cheating**:
   - Disable screenshot during test
   - Disable copy-paste
   - Detect app switching
   - Monitor for emulator/root
   - Validate answer source

5. **Input Security**:
   - Validate all inputs
   - Sanitize user data
   - Use parameterized queries
   - Implement rate limiting

---

## XIV. MONITORING & ANALYTICS

### Metrics to Track
- User engagement
- Test completion rate
- Average test duration
- Error rates
- API response time
- Crash reports

### Tools
- Google Analytics / Firebase Analytics
- Sentry untuk crash reporting
- Custom logging untuk analytics

---

## XV. DEPLOYMENT CHECKLIST

- [ ] API endpoints tested & working
- [ ] Database schema updated
- [ ] CORS configured correctly
- [ ] HTTPS enabled
- [ ] Rate limiting configured
- [ ] Error logging setup
- [ ] Monitoring setup
- [ ] Build APK/IPA
- [ ] Google Play Store submission
- [ ] Apple App Store submission
- [ ] Production monitoring active
- [ ] Backup & recovery plan

---

## XVI. SUPPORT & MAINTENANCE

- Bug fixes SLA: 24-48 jam
- Feature requests: Quarterly review
- Security updates: Immediate
- Performance optimization: Monthly review
- User documentation: Maintain & update
- API versioning: Maintain backward compatibility

---

**Estimasi Total Development**: 12-14 minggu (3 bulan)
**Tim yang Diperlukan**: 1-2 Flutter Developer, 1 Backend Developer, 1 QA Engineer

---

## 📄 License

© 2025 CBT LPK. All rights reserved.

---

**Selamat Menggunakan Sistem CBT LPK!** 🎓

> **Catatan**: Proyek ini telah memenuhi seluruh standar pengembangan aplikasi web yang ditetapkan, dengan implementasi lengkap backend-frontend, dashboard, laporan export, CRUD, session management, dan studi kasus nyata yang relevan.
