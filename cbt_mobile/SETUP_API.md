# Setup API Mobile App - CBT LPK

## Konfigurasi Base URL

Edit file `lib/core/constants.dart` dan ubah `baseUrl` sesuai environment:

```dart
class AppConstants {
  // Development dengan Android Emulator
  static const String baseUrl = "http://10.0.2.2/CBT_LPK_hosting/api";
  
  // Development dengan iOS Simulator
  // static const String baseUrl = "http://localhost/CBT_LPK_hosting/api";
  
  // Development dengan Device Fisik (ganti dengan IP komputer)
  // static const String baseUrl = "http://192.168.1.xxx/CBT_LPK_hosting/api";
  
  // Production
  // static const String baseUrl = "https://your-domain.com/api";
}
```

## API Endpoints yang Digunakan

### 1. Authentication (`mobile_auth.php`)
- `POST ?action=login` - Login dengan nomor peserta & password
- `POST ?action=logout` - Logout
- `POST ?action=verify-token` - Verifikasi token

### 2. Dashboard (`mobile_dashboard.php`)
- `GET ?firebase_uid=xxx` - Get dashboard data

### 3. Profile/Peserta (`mobile_peserta.php`)
- `GET ?action=get&firebase_uid=xxx` - Get profile
- `PUT ?action=update` - Update profile
- `PUT ?action=change-password` - Ganti password

### 4. Tests (`mobile_test.php`)
- `GET ?action=list&firebase_uid=xxx` - Daftar ujian
- `GET ?action=detail&firebase_uid=xxx&jadwal_id=xxx` - Detail ujian
- `POST ?action=start` - Mulai ujian
- `GET ?action=questions&firebase_uid=xxx&jadwal_id=xxx` - Get soal

### 5. Jawaban (`mobile_jawaban.php`)
- `POST ?action=save` - Simpan jawaban tunggal
- `POST ?action=save-batch` - Simpan jawaban batch
- `POST ?action=submit` - Submit/selesaikan ujian

### 6. Hasil (`mobile_hasil.php`)
- `GET ?action=get&firebase_uid=xxx&jadwal_id=xxx` - Get hasil
- `GET ?action=detail&firebase_uid=xxx&jadwal_id=xxx` - Detail hasil
- `GET ?action=history&firebase_uid=xxx` - Riwayat ujian

### 7. Firebase Link (`link_firebase.php`)
- `POST` - Link Firebase UID ke akun peserta

## Flow Autentikasi

1. User memasukkan nomor peserta + password
2. App login ke backend PHP (`mobile_auth.php?action=login`)
3. Jika sukses, backend mengembalikan token + data peserta
4. App login/register ke Firebase Auth (email: `{nomor_peserta}@cbt-lpk.local`)
5. App menyimpan token + Firebase UID ke local storage
6. App memanggil `link_firebase.php` untuk linking UID ke backend

## Local Storage (SharedPreferences)

Data yang disimpan:
- `user_id` - ID peserta
- `auth_token` - Token autentikasi
- `firebase_uid` - Firebase UID
- `user_data` - Data peserta (JSON)

## Testing dengan Postman

Import collection dari `api/MOBILE_API_ENDPOINTS.md` atau test manual:

### Login
```
POST http://localhost/CBT_LPK_hosting/api/mobile_auth.php?action=login
Content-Type: application/json

{
  "nomor_peserta": "PST001",
  "password": "password123"
}
```

### Get Dashboard
```
GET http://localhost/CBT_LPK_hosting/api/mobile_dashboard.php?firebase_uid=xxx
```

### Get Test List
```
GET http://localhost/CBT_LPK_hosting/api/mobile_test.php?action=list&firebase_uid=xxx
```

## Troubleshooting

### Error: Connection refused
- Pastikan server PHP (Laragon/XAMPP) running
- Pastikan base URL sesuai dengan environment
- Untuk emulator Android, gunakan `10.0.2.2` bukan `localhost`

### Error: CORS
- Backend sudah include CORS headers di `mobile_config.php`
- Pastikan tidak ada error PHP sebelum headers dikirim

### Error: Token invalid
- Token expired setelah beberapa waktu
- User perlu login ulang
- Cek apakah LocalService.init() dipanggil di main.dart

### Error: Firebase Auth
- Pastikan Firebase project sudah dikonfigurasi
- Cek `firebase_options.dart` sudah benar
- Enable Email/Password authentication di Firebase Console
