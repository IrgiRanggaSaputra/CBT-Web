# 🚀 Quick Start Guide - CBT LPK

## Langkah Cepat Instalasi (5 Menit)

### 1️⃣ Setup Database (2 menit)
```bash
1. Buka phpMyAdmin → http://localhost/phpmyadmin
2. Klik "New" untuk buat database baru
3. Nama database: cbt_lpk
4. Klik "Import" → Pilih file "database.sql"
5. Klik "Go" → Selesai!
```

### 2️⃣ Test Koneksi (1 menit)
```bash
1. Buka browser: http://localhost/CBT_LPK/
2. Jika muncul halaman pilihan login → Berhasil!
3. Jika error, cek config.php
```

### 3️⃣ Login Admin (1 menit)
```bash
1. Klik "Administrator"
2. Username: admin
3. Password: password
4. Klik Login
```

### 4️⃣ Setup Awal (1 menit)
```bash
Di Dashboard Admin:
1. Tambah Kategori Soal (contoh: Matematika)
2. Tambah Soal (minimal 5 soal)
3. Tambah Peserta (contoh: P001)
4. Buat Jadwal Tes
```

## 🎯 Testing Sistem

### Test sebagai Admin:
1. ✅ Login admin berhasil
2. ✅ Dashboard menampilkan statistik
3. ✅ Bisa tambah peserta
4. ✅ Bisa tambah soal
5. ✅ Bisa buat jadwal tes

### Test sebagai Peserta:
1. ✅ Login peserta berhasil
2. ✅ Lihat jadwal tes tersedia
3. ✅ Mulai tes → Timer berjalan
4. ✅ Jawab soal → Autosave bekerja
5. ✅ Submit tes → Nilai muncul

## 🔧 Troubleshooting

### Error: "Connection failed"
**Solusi:**
- Cek MySQL service berjalan
- Cek username/password di config.php
- Cek nama database sudah benar

### Error: "Table doesn't exist"
**Solusi:**
- Import ulang database.sql
- Pastikan database bernama 'cbt_lpk'

### Halaman blank/error 500
**Solusi:**
- Cek PHP error di: `xampp/php/php.ini`
- Enable `display_errors = On`
- Restart Apache

### Timer tidak berjalan
**Solusi:**
- Pastikan JavaScript enabled di browser
- Buka console browser (F12) untuk lihat error
- Pastikan jQuery ter-load

## 📝 Checklist Sebelum Production

- [ ] Ganti password admin default
- [ ] Set `display_errors = Off` di php.ini
- [ ] Backup database secara berkala
- [ ] Test di berbagai browser
- [ ] Test dengan koneksi internet lambat
- [ ] Dokumentasikan user manual
- [ ] Training untuk admin dan peserta

## 🎓 Demo Data (Optional)

Untuk testing cepat, gunakan data sample:

**Peserta Demo:**
- Nomor: P001
- Password: demo123

**Soal Demo:**
Import file: `demo_soal.csv` (buat sendiri)

## 📞 Bantuan

Jika masih ada masalah:
1. Cek file README.md untuk dokumentasi lengkap
2. Periksa log error di `xampp/apache/logs/error.log`
3. Test koneksi database dengan script test sederhana

## ✨ Tips & Tricks

1. **Auto-refresh Monitoring:**
   Halaman monitoring refresh otomatis setiap 30 detik

2. **Shortcut Keyboard:**
   - Peserta: Arrow keys untuk navigasi soal (bisa dikembangkan)

3. **Best Practice:**
   - Buat backup sebelum update data besar
   - Test jadwal tes di draft mode dulu
   - Monitor peserta saat tes berlangsung

---

**Selamat! Sistem CBT siap digunakan!** 🎉
