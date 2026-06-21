<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'CBT Web') }} - Admin Dashboard</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/images/kiyoraka.png') }}">
    
    <!-- Google Fonts for Premium Aesthetics -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="{{ asset('css/cbt.css') }}?v=1.0.2">
</head>
<body data-api-base="{{ url('/api') }}">
    <div class="app-shell" id="app-shell">
        
        <!-- Sidebar Navigation -->
        <aside class="sidebar" id="app-sidebar" aria-label="Navigasi utama">
            <a class="brand" href="/admin" aria-label="CBT Web">
                <img src="{{ asset('assets/images/kiyoraka.png') }}" alt="Logo CBT" style="height: 50px; width: auto;">
                <span>
                    <strong>KIYORAKA</strong>
                    <small>Computer Based Test</small>
                </span>  
            </a>

            <nav class="nav-list" aria-label="Modul aplikasi">
                <button class="nav-item active" data-view="overview" type="button">
                    <span class="nav-icon">📊</span> Dashboard
                </button>
                <button class="nav-item" data-view="participants" type="button">
                    <span class="nav-icon">👥</span> Peserta
                </button>
                <button class="nav-item" data-view="categories" type="button">
                    <span class="nav-icon">📁</span> Kategori Soal
                </button>
                <button class="nav-item" data-view="question-bank" type="button">
                    <span class="nav-icon">📝</span> Bank Soal
                </button>
                <button class="nav-item" data-view="schedule" type="button">
                    <span class="nav-icon">📅</span> Jadwal Tes
                </button>
                <button class="nav-item" data-view="monitoring" type="button">
                    <span class="nav-icon">🖥️</span> Monitoring
                </button>
                <button class="nav-item" data-view="reports" type="button">
                    <span class="nav-icon">📈</span> Laporan
                </button>
            </nav> 

            <div class="sidebar-note">
                <div class="status-dot-pulse"></div>
                <div>
                    <strong>Sistem Aktif</strong>
                    <small>Koneksi Terjamin</small>
                </div>
            </div>
        </aside> 

        <!-- Main Workspace -->
        <main class="main-panel" id="main-panel">
            
            <!-- Topbar -->
            <header class="topbar" id="app-topbar">
                <div>
                    <p class="eyebrow">Sistem Ujian Online</p>
                    <h1 id="page-title">Dashboard CBT</h1>
                </div>
                <div class="topbar-actions">
                    <div class="user-profile-chip">
                        <span class="user-avatar" id="user-avatar">A</span>
                        <div class="user-info">
                            <span class="session-chip" id="session-chip">Memuat...</span>
                        </div>
                    </div>
                    <button class="logout-btn" id="logout-button" type="button">Keluar</button>
                </div>
            </header>

            <!-- DASHBOARD VIEW -->
            <section class="dashboard" id="dashboard-view">
                <div class="metric-grid" id="metric-grid"></div>

                <div class="content-grid">
                    <section class="work-panel">
                        <div class="panel-heading">
                            <div>
                                <p class="eyebrow">Modul Kerja</p>
                                <h2 id="work-title">Ringkasan</h2>
                            </div>
                            <span class="panel-badge" id="role-badge">Admin</span>
                        </div>
                        <div id="work-content"></div>
                    </section>

                    <aside class="activity-panel">
                        <div class="panel-heading compact">
                            <div>
                                <p class="eyebrow">Panduan</p>
                                <h2>Langkah Berikutnya</h2>
                            </div>
                        </div>
                        <ol class="task-list" id="task-list"></ol>
                    </aside>
                </div>
            </section>

        </main>
    </div>

    <!-- MODAL POPUP: KELOLA PESERTA JADWAL -->
    <div class="modal-overlay" id="modal-participants" hidden>
        <div class="modal-card">
            <div class="modal-header">
                <h3>Kelola Peserta Ujian</h3>
                <button class="modal-close-btn" id="btn-close-participants-modal" type="button">×</button>
            </div>
            <div class="modal-body">
                <div class="modal-form-section">
                    <p class="modal-sub-label">Daftarkan Peserta Ujian ke Jadwal: <strong id="modal-target-schedule-name">Nama Ujian</strong></p>
                    <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                        <form id="modal-add-participant-form" class="modal-inline-form" style="flex: 1; min-width: 250px; margin: 0;">
                            <div class="modal-form-wrapper">
                                <select id="modal-select-participant" class="modal-select" required>
                                    <option value="">Pilih peserta...</option>
                                </select>
                                <button type="submit" class="modal-submit-btn">Tambah Peserta</button>
                            </div>
                        </form>
                        <button type="button" id="btn-register-all-participants" class="modal-submit-btn" style="background: var(--accent); white-space: nowrap; height: 38px;">👥 Daftarkan Semua Peserta</button>
                    </div>
                </div>
                
                <div class="modal-list-section">
                    <h4>Daftar Peserta Terdaftar</h4>
                    <div class="modal-list-table-wrapper">
                        <table class="modal-list-table">
                            <thead>
                                <tr>
                                    <th>No Peserta</th>
                                    <th>Nama Lengkap</th>
                                    <th>Status Ujian</th>
                                    <th style="width: 80px; text-align: center;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="modal-assigned-participants-list">
                                <!-- Dynamic Rows -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL POPUP: IMPORT CSV SOAL -->
    <div class="modal-overlay" id="modal-import-soal" hidden>
        <div class="modal-card">
            <div class="modal-header">
                <h3>Import Soal (Excel / CSV)</h3>
                <button class="modal-close-btn" id="btn-close-import-soal-modal" type="button">×</button>
            </div>
            <div class="modal-body">
                <div class="import-template-info">
                    <p>Format berkas harus berupa **CSV** dengan pemisah koma (,) atau titik koma (;) serta memiliki susunan kolom header:</p>
                    <div class="csv-header-code">
                        <code>id_kategori,pertanyaan,pilihan_a,pilihan_b,pilihan_c,pilihan_d,pilihan_e,jawaban_benar,bobot</code>
                    </div>
                    <a class="csv-template-btn" href="data:text/csv;charset=utf-8,id_kategori,pertanyaan,pilihan_a,pilihan_b,pilihan_c,pilihan_d,pilihan_e,jawaban_benar,bobot%0A1,Manakah%20yang%20merupakan%20sistem%20operasi?,Linux,Vite,HTML,MySQL,,A,20" download="template_import_soal.csv">📥 Unduh Template Soal (.csv)</a>
                </div>
                
                <form id="modal-import-soal-form" class="modal-import-form">
                    <div class="file-drop-area">
                        <span class="file-drop-icon">📂</span>
                        <span class="file-drop-label">Pilih berkas CSV dari komputer Anda</span>
                        <input type="file" id="modal-import-file-input" name="file" accept=".csv,.txt" required>
                    </div>
                    <p class="import-file-name" id="import-file-name-label"></p>
                    <button type="submit" class="modal-import-submit-btn">Mulai Import Soal</button>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL POPUP: IMPORT PESERTA CSV -->
    <div class="modal-overlay" id="modal-import-peserta" hidden>
        <div class="modal-card">
            <div class="modal-header">
                <h3>Import Peserta (Excel / CSV)</h3>
                <button class="modal-close-btn" id="btn-close-import-peserta-modal" type="button">×</button>
            </div>
            <div class="modal-body">
                <div class="import-template-info">
                    <p>Format berkas harus berupa **CSV** dengan pemisah koma (,) atau titik koma (;) serta memiliki susunan kolom header:</p>
                    <div class="csv-header-code">
                        <code>nomor_peserta,nama_lengkap,email,password,jenis_kelamin,status</code>
                    </div>
                    <a class="csv-template-btn" href="data:text/csv;charset=utf-8,nomor_peserta,nama_lengkap,email,password,jenis_kelamin,status%0AP001,Ahmad Farhan,ahmad@email.com,password123,L,aktif%0AP002,Siti Nurhaliza,siti@email.com,password456,P,aktif" download="template_import_peserta.csv">📥 Unduh Template Peserta (.csv)</a>
                </div>
                
                <form id="modal-import-peserta-form" class="modal-import-form">
                    <div class="file-drop-area">
                        <span class="file-drop-icon">📂</span>
                        <span class="file-drop-label">Pilih berkas CSV dari komputer Anda</span>
                        <input type="file" id="modal-import-peserta-file-input" name="file" accept=".csv,.txt" required>
                    </div>
                    <p class="import-file-name" id="import-peserta-file-name-label"></p>
                    <button type="submit" class="modal-import-submit-btn">Mulai Import Peserta</button>
                </form>
            </div>
        </div>
    </div>

    <script src="{{ asset('js/admin.js') }}?v=1.0.2" defer></script>
</body>
</html>
