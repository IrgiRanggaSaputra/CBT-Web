# Firebase Integration untuk Mobile API

Dokumentasi integrasi Firebase untuk API Mobile CBT LPK.

## 📋 Setup Database

Kolom `firebase_uid` sudah ditambahkan ke tabel `peserta`:

```sql
ALTER TABLE peserta ADD COLUMN firebase_uid VARCHAR(255) UNIQUE;
```

## 🔐 Cara Kerja Autentikasi

### Flow:
1. **Flutter App** login dengan Firebase
2. Dapat **Firebase UID** dari Firebase SDK
3. Pass Firebase UID ke API
4. API convert Firebase UID → peserta_id
5. API return data sesuai peserta_id

## 🚀 Menggunakan API

### Option 1: Query Parameter (Recommended)
```
GET /api/mobile_dashboard.php?firebase_uid=abc123def456
GET /api/mobile_test.php?action=list&firebase_uid=abc123def456
GET /api/mobile_hasil.php?action=history&firebase_uid=abc123def456
```

### Option 2: Authorization Header (Lebih Clean)
```
Header: Authorization: Bearer abc123def456
GET /api/mobile_dashboard.php
GET /api/mobile_test.php?action=list
GET /api/mobile_hasil.php?action=history
```

## 📱 Flutter App Integration

### Dapatkan Firebase UID di Flutter:
```dart
import 'package:firebase_auth/firebase_auth.dart';

final User? user = FirebaseAuth.instance.currentUser;
final String? firebaseUid = user?.uid;

// Pass ke API
final response = await dio.get(
  '/mobile_dashboard.php',
  queryParameters: {
    'firebase_uid': firebaseUid,
  },
);
```

### Atau Gunakan Interceptor:
```dart
_dio.interceptors.add(
  InterceptorsWrapper(
    onRequest: (options, handler) {
      final User? user = FirebaseAuth.instance.currentUser;
      if (user != null) {
        options.headers['Authorization'] = 'Bearer ${user.uid}';
      }
      return handler.next(options);
    },
  ),
);
```

## 🔗 Link Firebase UID ke Peserta

Gunakan endpoint `link_firebase.php` untuk link Firebase UID:

```
POST /api/link_firebase.php
Content-Type: application/json

{
  "firebase_uid": "abc123def456",
  "nomor_peserta": "P001"
}
```

Response:
```json
{
  "status": "success",
  "message": "Firebase UID berhasil terhubung"
}
```

## ✅ API Endpoints dengan Firebase UID

| Endpoint | Method | Firebase UID | Deskripsi |
|----------|--------|--------------|-----------|
| `/api/mobile_dashboard.php` | GET | Required | Dashboard peserta |
| `/api/mobile_test.php?action=list` | GET | Required | List jadwal tes |
| `/api/mobile_test.php?action=detail` | GET | Required | Detail tes |
| `/api/mobile_test.php?action=start` | POST | Required | Mulai tes |
| `/api/mobile_test.php?action=questions` | GET | Required | List soal |
| `/api/mobile_jawaban.php?action=save` | POST | Required | Simpan jawaban |
| `/api/mobile_jawaban.php?action=save-batch` | POST | Required | Batch simpan jawaban |
| `/api/mobile_jawaban.php?action=submit` | POST | Required | Submit tes |
| `/api/mobile_hasil.php?action=get` | GET | Required | Hasil tes |
| `/api/mobile_hasil.php?action=detail` | GET | Required | Detail hasil |
| `/api/mobile_hasil.php?action=history` | GET | Required | Riwayat tes |
| `/api/mobile_peserta.php?action=get` | GET | Required | Profile peserta |
| `/api/mobile_peserta.php?action=update` | PUT | Required | Update profile |
| `/api/mobile_peserta.php?action=change-password` | PUT | Required | Change password |

## 🔧 Troubleshooting

### Firebase UID tidak terdaftar
```json
{
  "status": "error",
  "message": "Firebase UID tidak terdaftar",
  "error_code": "NOT_FOUND"
}
```

**Solusi**: Link Firebase UID terlebih dahulu menggunakan endpoint `link_firebase.php`

### Parameter firebase_uid tidak ditemukan
```json
{
  "status": "error",
  "message": "Parameter firebase_uid harus diisi",
  "error_code": "VALIDATION_ERROR"
}
```

**Solusi**: Pastikan Firebase UID di-pass di query parameter atau Authorization header

## 📝 Notes

- Firebase UID harus unique di database
- Satu peserta hanya bisa link satu Firebase UID
- Jika sudah link, tidak bisa diubah ke Firebase UID lain
- Untuk development, bisa manually update `firebase_uid` di database
