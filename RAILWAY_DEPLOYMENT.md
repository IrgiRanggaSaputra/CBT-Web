# Railway Deployment Guide

## Persyaratan:
1. Railway account (railway.app)
2. GitHub repository
3. Database eksternal (MySQL)

## Setup di Railway:

### 1. Database Setup
- Gunakan Railway MySQL service atau external database
- Configure environment variables di Railway:
  ```
  DB_HOST=your-database-host
  DB_USER=your-database-user
  DB_PASS=your-database-password
  DB_NAME=cbt_lpk
  ```

### 2. Deploy Project
1. Push code ke GitHub
2. Di Railway dashboard:
   - Create new project
   - Connect GitHub repository
   - Select "Docker" (akan detect Dockerfile)
   - Set environment variables untuk database
   - Railway akan auto-build dan deploy

### 3. Environment Variables yang diperlukan
```
DB_HOST = (host MySQL)
DB_USER = (user MySQL)
DB_PASS = (password MySQL)
DB_NAME = cbt_lpk
BASE_URL = https://your-railway-domain/
```

### 4. Update config.php
Ubah config.php untuk baca dari environment variables:
```php
define('DB_HOST', $_ENV['DB_HOST'] ?? 'localhost');
define('DB_USER', $_ENV['DB_USER'] ?? 'root');
define('DB_PASS', $_ENV['DB_PASS'] ?? '');
define('DB_NAME', $_ENV['DB_NAME'] ?? 'cbt_lpk');
define('BASE_URL', $_ENV['BASE_URL'] ?? 'http://localhost/CBT_LPK/');
```

## Catatan:
- Railway otomatis memberikan PORT variable
- Dockerfile sudah dikonfigurasi untuk Railway
- docker-compose.yml hanya untuk development lokal
- Untuk production, gunakan managed database service

## Troubleshooting:
- Cek logs di Railway dashboard
- Pastikan database variables sudah set
- Import database.sql via Railway database console atau buat init script
