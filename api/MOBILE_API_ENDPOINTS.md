# CBT LPK Mobile API - Documentation

API endpoints untuk aplikasi mobile Flutter peserta CBT LPK.

## 📌 Base Configuration

**Base URL**: `https://yourdomain.com/api/`

**Content-Type**: `application/json`

**Authentication**: Token-based (included in Authorization header)

```
Header: Authorization: {token}
```

---

## 🔐 Authentication Endpoints

### 1. Login
**Endpoint**: `POST /mobile_auth.php?action=login`

**Request Body**:
```json
{
  "nomor_peserta": "P001",
  "password": "password123"
}
```

**Response (200)**:
```json
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
    "telepon": "08123456789",
    "alamat": "Jl. Contoh No. 1",
    "token": "base64_encoded_token"
  }
}
```

**Response Error (401)**:
```json
{
  "status": "error",
  "message": "Nomor peserta atau password salah",
  "error_code": "AUTH_FAILED"
}
```

---

### 2. Logout
**Endpoint**: `POST /mobile_auth.php?action=logout`

**Headers**: 
```
Authorization: {token}
```

**Response (200)**:
```json
{
  "status": "success",
  "message": "Logout berhasil"
}
```

---

### 3. Verify Token
**Endpoint**: `POST /mobile_auth.php?action=verify-token`

**Headers**: 
```
Authorization: {token}
```

**Response (200)**:
```json
{
  "status": "success",
  "message": "Token valid",
  "data": {
    "valid": true,
    "peserta_id": 1
  }
}
```

---

## 👤 Peserta Profile Endpoints

### 4. Get Profile
**Endpoint**: `GET /mobile_peserta.php?action=get`

**Headers**: 
```
Authorization: {token}
```

**Response (200)**:
```json
{
  "status": "success",
  "message": "Data profil berhasil diambil",
  "data": {
    "id_peserta": 1,
    "nomor_peserta": "P001",
    "nama_lengkap": "John Doe",
    "email": "john@example.com",
    "jenis_kelamin": "L",
    "tanggal_lahir": "1990-01-15",
    "telepon": "08123456789",
    "alamat": "Jl. Contoh No. 1",
    "status": "aktif"
  }
}
```

---

### 5. Update Profile
**Endpoint**: `PUT /mobile_peserta.php?action=update`

**Headers**: 
```
Authorization: {token}
Content-Type: application/json
```

**Request Body**:
```json
{
  "nama_lengkap": "John Doe",
  "email": "john.doe@example.com",
  "telepon": "08123456789",
  "alamat": "Jl. Contoh No. 2"
}
```

**Response (200)**:
```json
{
  "status": "success",
  "message": "Profil berhasil diperbarui"
}
```

---

### 6. Change Password
**Endpoint**: `PUT /mobile_peserta.php?action=change-password`

**Headers**: 
```
Authorization: {token}
Content-Type: application/json
```

**Request Body**:
```json
{
  "password_lama": "old_password",
  "password_baru": "new_password",
  "konfirmasi_password": "new_password"
}
```

**Response (200)**:
```json
{
  "status": "success",
  "message": "Password berhasil diubah"
}
```

---

## 📊 Dashboard Endpoint

### 7. Get Dashboard
**Endpoint**: `GET /mobile_dashboard.php`

**Headers**: 
```
Authorization: {token}
```

**Response (200)**:
```json
{
  "status": "success",
  "message": "Dashboard data berhasil diambil",
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

## 📝 Test Management Endpoints

### 8. Get Test List
**Endpoint**: `GET /mobile_test.php?action=list&status=&search=&sort=date`

**Headers**: 
```
Authorization: {token}
```

**Query Parameters**:
- `status`: `belum_mulai`, `sedang_tes`, `selesai` (optional)
- `search`: Cari berdasarkan nama tes (optional)
- `sort`: `date` atau `newest` (optional)

**Response (200)**:
```json
{
  "status": "success",
  "message": "Daftar tes berhasil diambil",
  "data": [
    {
      "id_jadwal": 1,
      "nama_tes": "Tes Matematika",
      "kategori": "Matematika",
      "tanggal_mulai": "2025-01-20 09:00:00",
      "tanggal_selesai": "2025-01-20 11:00:00",
      "durasi": 120,
      "jumlah_soal": 50,
      "passing_grade": 70,
      "status": "belum_mulai",
      "nilai": null,
      "status_kelulusan": null
    }
  ]
}
```

---

### 9. Get Test Detail
**Endpoint**: `GET /mobile_test.php?action=detail&id_jadwal=1`

**Headers**: 
```
Authorization: {token}
```

**Query Parameters**:
- `id_jadwal`: ID jadwal tes (required)

**Response (200)**:
```json
{
  "status": "success",
  "message": "Detail tes berhasil diambil",
  "data": {
    "id_jadwal": 1,
    "nama_tes": "Tes Matematika",
    "kategori": "Matematika",
    "tanggal_mulai": "2025-01-20 09:00:00",
    "tanggal_selesai": "2025-01-20 11:00:00",
    "durasi": 120,
    "jumlah_soal": 50,
    "passing_grade": 70,
    "instruksi": "Baca soal dengan teliti dan jawab dengan benar"
  }
}
```

---

### 10. Start Test
**Endpoint**: `POST /mobile_test.php?action=start`

**Headers**: 
```
Authorization: {token}
Content-Type: application/json
```

**Request Body**:
```json
{
  "id_jadwal": 1
}
```

**Response (201)**:
```json
{
  "status": "success",
  "message": "Tes dimulai",
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

---

### 11. Get All Questions
**Endpoint**: `GET /mobile_test.php?action=questions&id_peserta_tes=1`

**Headers**: 
```
Authorization: {token}
```

**Query Parameters**:
- `id_peserta_tes`: ID peserta tes (required)

**Response (200)**:
```json
{
  "status": "success",
  "message": "Soal berhasil diambil",
  "data": {
    "id_peserta_tes": 5,
    "total_soal": 50,
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
    ],
    "jawaban_tersimpan": {
      "1": "C",
      "3": "A",
      "5": "B"
    }
  }
}
```

---

## ✍️ Answer & Submission Endpoints

### 12. Save Single Answer
**Endpoint**: `POST /mobile_jawaban.php?action=save`

**Headers**: 
```
Authorization: {token}
Content-Type: application/json
```

**Request Body**:
```json
{
  "id_peserta_tes": 5,
  "id_soal_tes": 1,
  "jawaban": "C"
}
```

**Response (201)**:
```json
{
  "status": "success",
  "message": "Jawaban tersimpan",
  "data": {
    "id_jawaban": 100,
    "timestamp": "2025-01-20 09:05:30"
  }
}
```

---

### 13. Save Batch Answers
**Endpoint**: `POST /mobile_jawaban.php?action=save-batch`

**Headers**: 
```
Authorization: {token}
Content-Type: application/json
```

**Request Body**:
```json
{
  "id_peserta_tes": 5,
  "jawaban": [
    {"id_soal_tes": 1, "jawaban": "C"},
    {"id_soal_tes": 2, "jawaban": "A"},
    {"id_soal_tes": 3, "jawaban": "B"}
  ]
}
```

**Response (200)**:
```json
{
  "status": "success",
  "message": "Jawaban berhasil disimpan"
}
```

---

### 14. Submit Test
**Endpoint**: `POST /mobile_jawaban.php?action=submit`

**Headers**: 
```
Authorization: {token}
Content-Type: application/json
```

**Request Body**:
```json
{
  "id_peserta_tes": 5
}
```

**Response (200)**:
```json
{
  "status": "success",
  "message": "Tes berhasil disubmit",
  "data": {
    "id_peserta_tes": 5,
    "waktu_selesai": "2025-01-20 11:05:30",
    "waktu_pengerjaan": 120,
    "total_soal": 50,
    "soal_terjawab": 48,
    "soal_kosong": 2,
    "nilai_sementara": 96,
    "status": "selesai"
  }
}
```

---

## 📊 Results Endpoints

### 15. Get Test Result
**Endpoint**: `GET /mobile_hasil.php?action=get&id_peserta_tes=1`

**Headers**: 
```
Authorization: {token}
```

**Query Parameters**:
- `id_peserta_tes`: ID peserta tes (required)

**Response (200)**:
```json
{
  "status": "success",
  "message": "Hasil tes berhasil diambil",
  "data": {
    "id_peserta_tes": 5,
    "nama_tes": "Tes Matematika",
    "tanggal_mulai": "2025-01-20 09:00:00",
    "tanggal_selesai": "2025-01-20 11:05:30",
    "waktu_pengerjaan": 125,
    "total_soal": 50,
    "soal_benar": 48,
    "soal_salah": 2,
    "soal_kosong": 0,
    "nilai": 96,
    "passing_grade": 70,
    "status_kelulusan": "lulus"
  }
}
```

---

### 16. Get Test Result Detail
**Endpoint**: `GET /mobile_hasil.php?action=detail&id_peserta_tes=1`

**Headers**: 
```
Authorization: {token}
```

**Query Parameters**:
- `id_peserta_tes`: ID peserta tes (required)

**Response (200)**:
```json
{
  "status": "success",
  "message": "Detail hasil tes berhasil diambil",
  "data": {
    "id_peserta_tes": 5,
    "nama_tes": "Tes Matematika",
    "tanggal_mulai": "2025-01-20 09:00:00",
    "tanggal_selesai": "2025-01-20 11:05:30",
    "waktu_pengerjaan": 125,
    "total_soal": 50,
    "soal_benar": 48,
    "soal_salah": 2,
    "soal_kosong": 0,
    "nilai": 96,
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
        "pertanyaan": "Berapa hasil dari 3 + 4?",
        "jawaban_peserta": "A",
        "jawaban_benar": "B",
        "status": "salah"
      }
    ]
  }
}
```

---

### 17. Get Test History
**Endpoint**: `GET /mobile_hasil.php?action=history&search=&filter=all&sort=date`

**Headers**: 
```
Authorization: {token}
```

**Query Parameters**:
- `search`: Cari berdasarkan nama tes (optional)
- `filter`: `all`, `lulus`, `gagal`, `belum_dinilai` (optional)
- `sort`: `date` (optional)

**Response (200)**:
```json
{
  "status": "success",
  "message": "Riwayat tes berhasil diambil",
  "data": [
    {
      "id_peserta_tes": 5,
      "nama_tes": "Tes Matematika",
      "tanggal_mulai": "2025-01-20 09:00:00",
      "tanggal_selesai": "2025-01-20 11:05:30",
      "nilai": 96,
      "status_kelulusan": "lulus",
      "status": "selesai"
    },
    {
      "id_peserta_tes": 1,
      "nama_tes": "Tes Dasar",
      "tanggal_mulai": "2025-01-10 10:00:00",
      "tanggal_selesai": "2025-01-10 11:30:00",
      "nilai": 85,
      "status_kelulusan": "lulus",
      "status": "selesai"
    }
  ]
}
```

---

## 🔄 Error Responses

### 400 - Bad Request
```json
{
  "status": "error",
  "message": "Field 'nomor_peserta' harus diisi",
  "error_code": "VALIDATION_ERROR"
}
```

### 401 - Unauthorized
```json
{
  "status": "error",
  "message": "Token tidak valid",
  "error_code": "INVALID_TOKEN"
}
```

### 404 - Not Found
```json
{
  "status": "error",
  "message": "Tes tidak ditemukan",
  "error_code": "NOT_FOUND"
}
```

### 500 - Server Error
```json
{
  "status": "error",
  "message": "Terjadi kesalahan server",
  "error_code": "SERVER_ERROR"
}
```

---

## 🔐 Authentication

Semua endpoint (kecuali login) memerlukan token yang didapat dari login endpoint.

Token harus dikirim di header:
```
Authorization: {token}
```

Token berlaku selama 24 jam.

---

## 📝 Notes

- Semua request harus menggunakan HTTPS
- Format tanggal: `YYYY-MM-DD HH:MM:SS`
- Response JSON selalu include `status` dan `message`
- Error responses include `error_code` untuk handling lebih baik
- Rate limiting dapat diimplementasikan untuk mencegah abuse

---

**Last Updated**: January 2025
**API Version**: 1.0
