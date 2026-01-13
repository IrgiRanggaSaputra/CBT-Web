# 📐 PROJECT STRUCTURE DOCUMENTATION

## Gambaran Umum Project

Ini adalah aplikasi CBT (Computer-Based Testing) terintegrasi dengan:
- **Backend Web**: PHP Native dengan MySQL database
- **Frontend Web**: PHP (Admin Panel + Peserta Portal)
- **Mobile App**: Flutter (iOS + Android)
- **Backend API**: REST API untuk mobile integration

---

## 📁 ROOT DIRECTORY STRUCTURE

```
CBT_LPK_hosting/                    # Root project folder
│
├── 📄 CONFIGURATION FILES
│   ├── config.php                  # Main config (DB, BASE_URL, helpers)
│   ├── composer.json               # PHP dependencies (Firebase SDK)
│   ├── composer.lock               # Locked dependencies
│   ├── .env.example                # Environment variables template
│   ├── .env (not in repo)          # Production env vars
│   ├── .gitignore                  # Git ignore rules
│   └── .htaccess                   # Apache rewrite rules
│
├── 🐳 DEPLOYMENT FILES
│   ├── Dockerfile                  # Docker image config
│   ├── docker-compose.yml          # Multi-container setup
│   ├── docker-entrypoint.sh        # Container startup script
│   ├── Procfile                    # Heroku/Railway deployment
│   ├── Railway_DEPLOYMENT.md       # Railway setup guide
│   └── start.sh                    # Local startup script
│
├── 🗄️ DATABASE
│   ├── database.sql                # Database schema & initial data
│   └── sample_data.sql             # Sample test data
│
├── 📖 DOCUMENTATION
│   ├── README.md                   # Main documentation
│   ├── CHANGELOG.md                # Version history
│   ├── QUICKSTART.md               # Quick start guide
│   ├── PRODUCTION_CHECKLIST.md     # Pre-production checklist
│   ├── STRUCTURE.txt               # Old structure info
│   ├── SELESAI.txt                 # Completion notes
│   └── MOBILE_RESPONSIVE_UPDATE.md # Mobile UI updates
│
├── 🔐 SECURITY
│   ├── hash.php                    # Password hashing utility
│   └── config/firebase-key.sample.json  # Firebase config template
│
├── 🌐 WEB APPLICATION
│   ├── index.php                   # Landing page
│   ├── login.php                   # Login page (unified)
│   │
│   ├── admin/                      # ADMIN PANEL (Protected)
│   │   ├── dashboard.php           # Admin dashboard
│   │   ├── bank_soal.php           # View test questions
│   │   ├── bank_soal_add.php       # Add new question
│   │   ├── bank_soal_edit.php      # Edit question
│   │   ├── bank_soal_import.php    # Bulk import questions
│   │   ├── kategori_soal.php       # Question categories
│   │   ├── jadwal_tes.php          # Test schedule list
│   │   ├── jadwal_tes_add.php      # Create new test
│   │   ├── jadwal_tes_edit.php     # Edit test schedule
│   │   ├── jadwal_tes_peserta.php  # Participants per test
│   │   ├── peserta.php             # Participants list
│   │   ├── peserta_add.php         # Add new participant
│   │   ├── peserta_edit.php        # Edit participant
│   │   ├── peserta_import.php      # Bulk import participants
│   │   ├── laporan.php             # Test results report
│   │   ├── laporan_detail.php      # Detailed results
│   │   ├── laporan_export_detail.php # Export detailed results
│   │   ├── laporan_print_detail.php  # Print detailed results
│   │   ├── monitoring.php          # Real-time test monitoring
│   │   ├── logout.php              # Logout handler
│   │   ├── template_import_peserta.csv  # Import template
│   │   ├── template_import_soal.csv     # Question import template
│   │   ├── CARA_IMPORT_SOAL_DENGAN_GAMBAR.md # Image import guide
│   │   └── includes/
│   │       ├── header.php          # Admin page header
│   │       └── footer.php          # Admin page footer
│   │
│   ├── peserta/                    # PESERTA PORTAL (Protected)
│   │   ├── dashboard.php           # Peserta dashboard
│   │   ├── profile.php             # User profile
│   │   ├── logout.php              # Peserta logout
│   │   ├── tes_mulai.php           # Start test page
│   │   ├── tes_petunjuk.php        # Test instructions
│   │   ├── tes_save.php            # Save answer (AJAX)
│   │   ├── tes_submit.php          # Submit test
│   │   └── includes/
│   │       └── (shared templates)
│   │
│   └── api/                        # REST API (For mobile app)
│       ├── _helpers.php            # API utility functions
│       ├── config_api.php          # API specific config
│       ├── helpers_api.php         # API helpers
│       ├── auth_peserta.php        # Peserta authentication
│       ├── mobile_auth.php         # Mobile auth endpoint
│       ├── mobile_config.php       # Mobile config
│       ├── mobile_test.php         # Test endpoint
│       ├── mobile_dashboard.php    # Dashboard endpoint
│       ├── mobile_hasil.php        # Results endpoint
│       ├── mobile_jawaban.php      # Answers endpoint
│       ├── mobile_peserta.php      # Peserta data endpoint
│       ├── get.php                 # GET endpoint handler
│       ├── create.php              # POST endpoint handler
│       ├── delete.php              # DELETE endpoint handler
│       ├── put.php                 # PUT endpoint handler
│       ├── resources.php           # Resource definitions
│       ├── link_firebase.php       # Firebase integration
│       ├── FIREBASE_INTEGRATION.md # Firebase setup guide
│       ├── MOBILE_API_ENDPOINTS.md # Mobile API documentation
│       │
│       ├── jawaban/
│       │   └── save.php            # Save jawaban (answers)
│       ├── peserta/
│       │   ├── dashboard.php       # Peserta dashboard API
│       │   ├── logout.php          # Peserta logout API
│       │   └── profile.php         # Peserta profile API
│       └── tes/
│           ├── detail.php          # Get test detail
│           ├── start.php           # Start test
│           └── submit.php          # Submit test
│
├── 📦 ASSETS (Static files)
│   ├── css/
│   │   ├── admin.css               # Admin panel styles
│   │   ├── auth.css                # Login/auth styles
│   │   ├── landing.css             # Landing page styles
│   │   └── peserta.css             # Peserta portal styles
│   ├── image/                      # Images folder
│   └── uploads/                    # User uploads (questions with images)
│       └── 1766106245_template_...
│       └── (uploaded files)
│
├── 🎲 DEPENDENCIES
│   └── vendor/                     # Composer packages
│       ├── autoload.php            # Composer autoloader
│       ├── firebase/               # Firebase PHP SDK
│       ├── kreait/                 # Firebase Admin SDK
│       ├── google/                 # Google API Client
│       ├── guzzlehttp/             # HTTP Client
│       └── (other packages)
│
├── 🔧 OPTIONAL DIRECTORIES
│   ├── backend/                    # Backend dev folder (empty)
│   ├── frontend/                   # Frontend dev folder (empty)
│   ├── .idea/                      # IDE config
│   └── .git/                       # Git repository
│
└── 📱 MOBILE APP (Flutter)
    └── cbt_mobile/                 # See detailed structure below
```

---

## 📱 MOBILE APP STRUCTURE (Flutter - cbt_mobile/)

```
cbt_mobile/                         # Flutter project root
│
├── 📄 PROJECT FILES
│   ├── pubspec.yaml                # Dependencies & project config
│   ├── pubspec.lock                # Locked dependency versions
│   ├── analysis_options.yaml       # Dart lint rules
│   ├── README.md                   # Flutter app README
│   ├── cbt_mobile.iml              # IDE module config
│   └── .metadata                   # Flutter metadata
│
├── 🔥 FIREBASE
│   ├── lib/firebase_options.dart   # Firebase configuration
│   └── android/app/google-services.json  # Android Firebase config
│
├── 💻 LIB - FLUTTER SOURCE CODE (Main Application)
│   ├── main.dart                   # App entry point
│   │   └── Routes initialization
│   │   └── Firebase initialization
│   │
│   ├── core/                       # Global utilities & constants
│   │   ├── routes.dart             # Named routes definition
│   │   │   ├── Route.login → LoginScreen
│   │   │   ├── Route.link → LinkAccountScreen
│   │   │   ├── Route.dashboard → DashboardScreen
│   │   │   ├── Route.tests → TestListScreen
│   │   │   ├── Route.instruction → InstructionScreen
│   │   │   ├── Route.test → TestScreen
│   │   │   └── Route.result → ResultScreen
│   │   └── constants.dart          # App constants
│   │       ├── pesertaUrl (Mock API)
│   │       ├── ujianUrl (Mock API)
│   │       └── jawabanUrl (Mock API)
│   │
│   ├── models/                     # Data models
│   │   ├── user_model.dart         # User data model
│   │   │   ├── id
│   │   │   ├── name
│   │   │   └── participantNumber
│   │   ├── test_model.dart         # Test data model
│   │   │   ├── id
│   │   │   └── title
│   │   └── question_model.dart     # Question data model
│   │
│   ├── services/                   # Business logic & API calls
│   │   ├── api_service.dart        # HTTP API calls
│   │   │   ├── getPesertaByNumber()
│   │   │   ├── getTests()
│   │   │   ├── getQuestions(testId)
│   │   │   ├── saveAnswer(data)
│   │   │   └── getResults()
│   │   ├── auth_service.dart       # Firebase Authentication
│   │   │   ├── login(email, password)
│   │   │   ├── register(email, password)
│   │   │   └── logout()
│   │   │   ⚠️ Development mode bypass!
│   │   └── local_service.dart      # In-memory local state
│   │       ├── userId
│   │       └── Helper methods
│   │
│   ├── provider/                   # State management layer
│   │   ├── auth_provider.dart      # Auth state
│   │   │   └── login(email, password)
│   │   └── test_provider.dart      # Test state
│   │
│   └── screens/                    # UI Screens
│       ├── auth/
│       │   ├── login_screen.dart   # Login screen
│       │   │   ├── Email input
│       │   │   ├── Password input
│       │   │   ├── Login button
│       │   │   └── Loading state
│       │   └── link_account_screen.dart  # Account linking
│       ├── dashboard/
│       │   └── dashboard_screen.dart    # User dashboard
│       └── test/
│           ├── test_list_screen.dart    # Available tests
│           ├── instruction_screen.dart  # Test instructions
│           ├── test_screen.dart         # Actual test/questions
│           └── result_screen.dart       # Test results
│
├── 🤖 ANDROID
│   ├── app/
│   │   ├── build.gradle.kts        # Android build config
│   │   ├── google-services.json    # Firebase config
│   │   └── src/main/
│   │       ├── AndroidManifest.xml
│   │       └── kotlin/java files
│   ├── gradle/
│   ├── .gradle/                    # Gradle cache
│   └── build/                      # Build output
│
├── 🍎 iOS
│   ├── Runner.xcodeproj/           # Xcode project
│   ├── Runner.xcworkspace/         # Workspace
│   ├── Podfile                     # CocoaPods
│   └── build/                      # Build output
│
├── 🌐 WEB
│   ├── index.html                  # Web entry point
│   ├── manifest.json               # PWA manifest
│   └── icons/                      # Web app icons
│
├── 🐧 LINUX
│   ├── CMakeLists.txt              # Linux build
│   └── runner/
│
├── 🖥️ WINDOWS
│   ├── CMakeLists.txt              # Windows build
│   └── runner/
│
├── 🍎 macOS
│   ├── Runner.xcodeproj/           # Xcode project
│   └── Podfile                     # CocoaPods
│
├── 🧪 TEST
│   └── widget_test.dart            # Widget tests (empty)
│
├── 🛠️ BUILD ARTIFACTS
│   ├── .dart_tool/                 # Dart tools cache
│   ├── build/                      # Build outputs
│   ├── .idea/                      # IDE config
│   └── .vscode/                    # VS Code config
│
└── 📋 CONFIG FILES
    ├── .gitignore
    ├── .flutter-plugins-dependencies
    └── .metadata
```

---

## 🔄 DATA FLOW DIAGRAMS

### Web App - Admin Flow
```
┌─────────────┐
│   Admin     │
│   Portal    │
│  login.php  │
└──────┬──────┘
       │ POST /login
       ▼
┌─────────────────────┐
│  Auth Check         │ Check session
│  (config.php)       │ Set $_SESSION
└──────┬──────────────┘
       │ Authenticated
       ▼
┌─────────────────────────────────┐
│   Admin Dashboard               │
│   /admin/dashboard.php          │
│   - Manage questions            │
│   - Manage test schedules       │
│   - View participants           │
│   - View reports                │
└─────────────────────────────────┘
```

### Web App - Peserta Flow
```
┌──────────────┐
│   Peserta    │
│   login.php  │
└───────┬──────┘
        │ POST /login
        ▼
┌──────────────────────┐
│   Auth Check         │ Check session
│   (config.php)       │ Set $_SESSION
└───────┬──────────────┘
        │ Authenticated
        ▼
┌────────────────────────────────────┐
│   Peserta Portal                   │
│   /peserta/dashboard.php           │
│   - View available tests           │
│   - Take test (tes_mulai.php)      │
│   - View results                   │
└────────────────────────────────────┘
```

### Mobile App - Auth Flow
```
┌──────────────────────┐
│   Login Screen       │
│ Email + Password     │
└──────┬───────────────┘
       │ login()
       ▼
┌────────────────────────────┐
│   AuthService              │
│   Firebase Auth            │
│   (isDevelopmentMode flag) │
└──────┬─────────────────────┘
       │ User or Mock User
       ▼
┌────────────────────────────┐
│   LinkAccountScreen        │ Link to peserta number
│   via API                  │
└──────┬─────────────────────┘
       │ Save local user data
       ▼
┌────────────────────────────┐
│   DashboardScreen          │
│   - View available tests   │
│   - Start test             │
│   - View results           │
└────────────────────────────┘
```

### Mobile App - Test Taking Flow
```
┌───────────────────┐
│ Test List Screen  │
│ getTests()        │
└────────┬──────────┘
         │ POST /api/ujian
         ▼
┌───────────────────────────────────┐
│ Instruction Screen                │
│ - Show test rules & instructions  │
│ - Show time limit                 │
└────────┬────────────────────────┬─┘
         │ User confirms          │ Cancel
         ▼                        ▼
┌──────────────────────┐   Exit
│ Test Screen          │
│ getQuestions(id)     │
├──────────────────────┤
│ - Display Q&A        │
│ - Allow answer       │
│ - Save answer        │
│ - Timer              │
└────────┬─────────────┘
         │ submit()
         ▼
┌──────────────────────┐
│ Submit & Result      │
│ - Get score          │
│ - Show results       │
└──────────────────────┘
```

---

## 🗄️ DATABASE SCHEMA

Main Tables (from database.sql):
```
peserta
├── id (PK)
├── nama
├── nomor_peserta (Unique)
├── email
├── password
├── created_at
└── updated_at

soal (Questions)
├── id (PK)
├── jadwal_tes_id (FK)
├── kategori_id (FK)
├── pertanyaan
├── opsi_a, opsi_b, opsi_c, opsi_d, opsi_e
├── jawaban_benar
├── gambar (optional)
└── created_at

jadwal_tes (Test Schedules)
├── id (PK)
├── nama_tes
├── deskripsi
├── tanggal_mulai
├── tanggal_selesai
├── durasi_menit
├── created_by (admin_id)
└── created_at

jawaban (Answers)
├── id (PK)
├── peserta_id (FK)
├── soal_id (FK)
├── jawaban_dipilih
├── benar (boolean)
├── created_at

peserta_test_rel (Peserta-Test Relationship)
├── id (PK)
├── peserta_id (FK)
├── jadwal_tes_id (FK)
├── nilai (score)
├── status (mulai/selesai)
└── selesai_at
```

---

## 🔌 API ENDPOINTS

### Current Mock API (Deprecated - Should use backend)
```
peserta:  https://696135b2e7aa517cb7985d5c.mockapi.io/api/peserta
ujian:    https://69652809e8ce952ce1f4593a.mockapi.io/api/ujian
jawaban:  https://69652aa4e8ce952ce1f46127.mockapi.io/api/jawaban
```

### Backend API Endpoints (PHP)
```
/api/auth_peserta.php          - Peserta authentication
/api/mobile_auth.php           - Mobile auth endpoint
/api/mobile_dashboard.php      - Dashboard data
/api/mobile_test.php           - Test list & details
/api/mobile_jawaban.php        - Answer management
/api/tes/detail.php            - Test details
/api/tes/start.php             - Start test session
/api/tes/submit.php            - Submit test
/api/jawaban/save.php          - Save individual answer
/api/peserta/dashboard.php     - Peserta dashboard
/api/peserta/profile.php       - Peserta profile
```

---

## 🔐 Authentication & Session

### Web App
- **Type**: PHP Session + Firebase Auth (optional)
- **Storage**: Server-side session
- **Timeout**: Default PHP session timeout
- **Methods**: 
  - Check admin: `check_login_admin()` in config.php
  - Check peserta: `check_login_peserta()` in config.php

### Mobile App
- **Type**: Firebase Auth + Local storage (in-memory)
- **Issues**: 
  - ⚠️ Development bypass enabled
  - ⚠️ No persistent storage
  - ⚠️ No token refresh

---

## 🚀 DEPLOYMENT STRUCTURE

### Docker Setup
```
docker-compose.yml
├── PHP Web Service
│   ├── Port: 8000
│   ├── Volume: ./:/var/www/html
│   └── DB: mysql:latest
├── MySQL Database
│   ├── Port: 3306
│   ├── Volume: mysql_data
│   └── Database: cbt_lpk
└── phpMyAdmin (optional)
    └── Port: 8081
```

### Environment Configuration
```
.env file variables:
- DB_HOST
- DB_USER
- DB_PASS
- DB_NAME
- BASE_URL
- FIREBASE_API_KEY
- FIREBASE_PROJECT_ID
```

---

## 📊 PROJECT STATISTICS

| Metric | Value |
|--------|-------|
| Total PHP Files | ~40+ |
| Total Dart Files | ~20+ |
| Database Tables | ~6-8 |
| API Endpoints | ~15+ |
| Screens (Mobile) | 7 |
| Dependencies (PHP) | ~20 (Composer) |
| Dependencies (Flutter) | 4 |

---

## 🎯 KEY FILE PURPOSES

| File | Purpose | Status |
|------|---------|--------|
| config.php | DB config & helpers | ✅ Working |
| database.sql | Schema & sample data | ✅ Complete |
| main.dart | App entry point | ✅ Working |
| api_service.dart | API calls | ⚠️ Uses mock API |
| auth_service.dart | Firebase auth | ⚠️ Dev bypass |
| routes.dart | Screen navigation | ✅ Complete |
| models/ | Data structures | ⚠️ Minimal |
| admin/dashboard.php | Admin panel | ✅ Working |
| peserta/dashboard.php | Student portal | ✅ Working |
| api/ | REST endpoints | ⚠️ Incomplete |

---

**Last Updated:** 13 Januari 2026  
**Version:** 1.0
