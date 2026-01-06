# Cara Import Soal dengan Gambar menggunakan CSV

## Langkah-langkah:

### 1. Persiapkan File Gambar
- Upload semua file gambar soal ke folder: `assets/uploads/`
- Gunakan nama file yang jelas, contoh: 
  - `soal_matematika_1.jpg`
  - `soal_ipa_2.png`
  - `diagram_fisika.jpg`
- Format yang didukung: JPG, JPEG, PNG, GIF

### 2. Persiapkan File CSV
- Download template CSV dari halaman import
- Buka dengan Excel atau text editor
- Format kolom (10 kolom):
  1. **id_kategori** - ID kategori soal (angka)
  2. **pertanyaan** - Teks pertanyaan
  3. **pilihan_a** - Pilihan A
  4. **pilihan_b** - Pilihan B
  5. **pilihan_c** - Pilihan C
  6. **pilihan_d** - Pilihan D
  7. **pilihan_e** - Pilihan E (boleh kosong)
  8. **jawaban_benar** - Jawaban (A/B/C/D/E)
  9. **bobot** - Bobot nilai (angka)
  10. **gambar** - Nama file gambar (boleh kosong)

### 3. Isi Data CSV

#### Contoh 1: Soal TANPA gambar
```csv
1,"Apa kepanjangan HTML?","HyperText Markup Language","High Text","HTML Text","Hyper Language","",A,1,
```

#### Contoh 2: Soal DENGAN gambar
```csv
2,"Perhatikan gambar diagram di bawah. Berapakah nilai X?","10","20","30","40","",C,1,diagram_1.jpg
```

**Penting:**
- Kolom gambar diisi dengan nama file saja (bukan path lengkap)
- Jika tidak ada gambar, kosongkan kolom tersebut (tetap ada koma)
- File gambar HARUS sudah ada di folder `assets/uploads/`

### 4. Upload File CSV
- Buka menu **Bank Soal** → **Import Excel**
- Pilih file CSV yang sudah disiapkan
- Klik **Upload & Import**
- Sistem akan memvalidasi:
  - Format data
  - Keberadaan file gambar
  - ID kategori
  - Jawaban benar

### 5. Cek Hasil Import
- Lihat laporan berhasil/gagal
- Jika ada error, perbaiki baris yang bermasalah
- Upload ulang file CSV yang sudah diperbaiki

## Tips:

✅ **DO:**
- Upload gambar terlebih dahulu sebelum import CSV
- Gunakan nama file gambar yang sederhana (tanpa spasi atau karakter khusus)
- Test dengan beberapa baris data dulu sebelum import massal
- Simpan backup file CSV sebelum import

❌ **DON'T:**
- Jangan isi kolom gambar dengan path lengkap (`C:\Users\...`)
- Jangan gunakan nama file yang belum di-upload
- Jangan lupa koma di akhir kolom gambar jika kosong
- Jangan upload gambar berukuran terlalu besar (max 2MB per file)

## Contoh File CSV Lengkap:

```csv
id_kategori,pertanyaan,pilihan_a,pilihan_b,pilihan_c,pilihan_d,pilihan_e,jawaban_benar,bobot,gambar
1,"Soal tanpa gambar","Jawaban A","Jawaban B","Jawaban C","Jawaban D","",A,1,
2,"Soal dengan gambar","Pilihan A","Pilihan B","Pilihan C","Pilihan D","Pilihan E",B,2,soal_2.jpg
3,"Perhatikan diagram","10","20","30","40","",C,1,diagram_3.png
```

## Troubleshooting:

**Error: "File gambar tidak ditemukan"**
- Pastikan file gambar sudah di-upload ke `assets/uploads/`
- Cek nama file di CSV sesuai dengan nama file di folder
- Nama file case-sensitive (huruf besar/kecil harus sama)

**Error: "Data tidak lengkap"**
- Pastikan ada 10 kolom di setiap baris
- Jangan lupa koma pemisah antar kolom
- Kolom opsional (pilihan_e dan gambar) boleh kosong tapi koma harus ada

**Gambar tidak tampil setelah import**
- Cek file permission folder `assets/uploads/`
- Pastikan format gambar didukung (JPG, PNG, GIF)
- Cek ukuran file tidak terlalu besar
