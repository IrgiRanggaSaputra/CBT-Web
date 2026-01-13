# Firebase Setup Guide untuk CBT LPK Mobile

## Status Saat Ini
- Firebase Project: `cbt-lpk`
- Android App ID: `1:1041673028858:android:1c3a5b1fd2ae40dc19f543`
- Firebase sudah terkonfigurasi di `firebase_options.dart`

## Langkah-langkah Konfigurasi

### 1. Buka Firebase Console
Buka https://console.firebase.google.com dan pilih project `cbt-lpk`

### 2. Enable Email/Password Authentication
1. Di sidebar kiri, klik **Build** > **Authentication**
2. Klik tab **Sign-in method**
3. Klik **Email/Password**
4. Enable **Email/Password** (bukan Email link)
5. Klik **Save**

### 3. Verifikasi Konfigurasi Android
1. Di sidebar, klik **Project Settings** (⚙️)
2. Scroll ke **Your apps** > Android app
3. Pastikan **Package name** = `com.example.cbt_kiyoraka`
4. Download `google-services.json` terbaru jika perlu
5. Copy ke `android/app/google-services.json`

### 4. SHA-1 Certificate (Opsional tapi disarankan)
```bash
# Di terminal, jalankan:
cd android
./gradlew signingReport
```
Copy SHA-1 dari `debug` variant dan tambahkan ke Firebase Console.

## Cara Kerja Firebase di App

### Flow Login:
1. User login dengan `nomor_peserta` dan `password`
2. Backend validasi credential → return `peserta_id` dan `token`
3. App convert ke email: `{nomor_peserta}@cbt-lpk.local`
4. App login/register ke Firebase dengan email tersebut
5. Firebase UID disimpan ke backend via `link_firebase.php`

### Fallback Jika Firebase Gagal:
- App tetap bisa berfungsi tanpa Firebase
- Menggunakan `local_{peserta_id}` sebagai fallback UID
- Semua API request menggunakan `peserta_id` langsung

## Troubleshooting

### Error: "invalid-credential"
- Firebase Authentication belum di-enable
- Atau user belum terdaftar di Firebase

### Error: "user-not-found"
- Normal jika user baru pertama kali login
- App akan otomatis register user ke Firebase

### Error: "email-already-in-use"
- Email sudah terdaftar, coba login saja

## Testing

### Test dengan user baru:
1. Buat peserta baru di admin panel
2. Login di mobile app
3. Cek di Firebase Console > Authentication > Users
4. User baru seharusnya muncul dengan email `{nomor_peserta}@cbt-lpk.local`

### Test API link_firebase:
```bash
curl -X POST https://cbtkiyoraka.web.id/api/link_firebase.php \
  -H "Content-Type: application/json" \
  -d '{"firebase_uid":"test_uid_123","peserta_id":"27"}'
```

## File Konfigurasi
- `lib/firebase_options.dart` - Firebase config untuk Flutter
- `android/app/google-services.json` - Firebase config untuk Android
- `api/link_firebase.php` - Backend API untuk linking Firebase UID
- `api/mobile_config.php` - Helper function `getPesertaIdFromFirebaseUID()`
