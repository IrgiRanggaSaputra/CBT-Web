# Production Deployment Checklist

## Pre-Deployment Setup

### 1. Database Preparation
- [ ] Eksport database lokal dengan: `mysqldump -u root cbt_lpk > database.sql`
- [ ] Upload schema ke MySQL server yang akan digunakan di Railway
- [ ] Test koneksi database dari Railway environment

### 2. Firebase Setup (jika digunakan)
- [ ] Siapkan `firebase-key.json` file
- Pilih salah satu metode:
  - **Metode 1: File Base64** (Recommended)
    ```bash
    cat config/firebase-key.json | base64 > firebase.b64
    # Copy hasil ke Railway environment variable FIREBASE_KEY_JSON
    ```
  - **Metode 2: Environment Variable**
    - Set `FIREBASE_KEY_JSON` di Railway dengan content firebase-key.json dalam format JSON string

### 3. Environment Variables di Railway
Set di Railway Dashboard:
```
DB_HOST = [railway-mysql-host]
DB_USER = [database-username]
DB_PASS = [database-password]
DB_NAME = cbt_lpk
FIREBASE_KEY_JSON = [base64-encoded-firebase-key]
```

### 4. Local Testing dengan Docker
Sebelum deploy ke Railway, test lokal:
```bash
# Copy .env.example ke .env dan isi database lokal
cp .env.example .env

# Run docker-compose
docker-compose up -d

# Test aplikasi
# http://localhost:8080

# Lihat logs
docker-compose logs -f web

# Stop
docker-compose down
```

### 5. Railway Deployment Steps
1. Connect GitHub repository ke Railway
2. Railway akan auto-detect Dockerfile
3. Set all environment variables di Railway dashboard
4. Deploy dengan klik "Deploy"
5. Monitor logs di Railway dashboard

### 6. Post-Deployment
- [ ] Test login peserta dan admin
- [ ] Test upload file (peserta import, soal import)
- [ ] Test API endpoints
- [ ] Setup custom domain (opsional)
- [ ] Setup SSL/HTTPS (Railway support built-in)

## Common Issues & Solutions

### Database Connection Error
- Pastikan `DB_HOST`, `DB_USER`, `DB_PASS`, `DB_NAME` sudah benar di Railway
- Railway MySQL biasanya tidak perlu port 3306 di connection string

### Firebase Not Working
- Verify `FIREBASE_KEY_JSON` format (harus valid JSON)
- Check firebase-key.json permissions di Railway

### Uploads Directory Issues
- Uploads directory hanya persistent selama container restart
- Untuk production, gunakan cloud storage (S3, Google Cloud Storage, dll)
- Atau setup persistent volume di Railway (paid feature)

### 502 Bad Gateway
- Check Apache error logs di Railway
- Verify database connection
- Check memory usage

## Next Steps for Production
1. Setup backup strategy untuk database
2. Setup monitoring & alerting
3. Setup custom domain
4. Setup email service untuk notifikasi
5. Consider using cloud storage untuk file uploads
6. Setup CDN untuk static assets

