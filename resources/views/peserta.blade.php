<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'CBT Web') }} - Ujian CBT</title>
    
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
            <a class="brand" href="/peserta" aria-label="CBT Web">
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
                <button class="nav-item" data-view="history" type="button">
                    <span class="nav-icon">🕒</span> Riwayat Tes
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
                        <span class="user-avatar" id="user-avatar">U</span>
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
                            <span class="panel-badge" id="role-badge">Peserta</span>
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

            <!-- EXAM VIEW (CBT Client) -->
            <section class="exam-layout" id="exam-view" hidden>
                
                <!-- Main Exam Content Area (Questions) -->
                <div class="exam-main">
                    <div class="exam-header-sticky">
                        <div class="exam-title-area">
                            <span class="exam-badge" id="exam-category-badge">Kategori</span>
                            <h2 id="exam-question-title">Soal No. 1</h2>
                        </div>
                        <div class="exam-timer-wrapper">
                            <span class="timer-icon">⏱️</span>
                            <div class="timer-digits">
                                <small>Sisa Waktu</small>
                                <strong id="exam-timer-val">00:00</strong>
                            </div>
                        </div>
                    </div>

                    <!-- Question Container -->
                    <div class="exam-question-card">
                        <!-- Image Container if question has drawing/diagram -->
                        <div class="question-image-container" id="question-image-container" hidden>
                            <img src="" id="question-image" alt="Diagram/Gambar Soal">
                        </div>

                        <!-- Question Text -->
                        <div class="question-text" id="question-text">
                            Memuat soal...
                        </div>

                        <!-- Options List -->
                        <div class="options-list" id="options-list">
                            <!-- Dynamic choices generated by JS -->
                        </div>
                    </div>

                    <!-- Question Control Footer -->
                    <div class="exam-footer">
                        <button class="control-btn prev" id="btn-prev-question" type="button">
                            <span>←</span> Soal Sebelumnya
                        </button>
                        <button class="control-btn flag" id="btn-flag-question" type="button">
                            <span class="flag-icon">🚩</span> Ragu-Ragu
                        </button>
                        <button class="control-btn next" id="btn-next-question" type="button">
                            Soal Selanjutnya <span>→</span>
                        </button>
                    </div>
                </div>

                <!-- Exam Right Sidebar (Navigation & Finish) -->
                <aside class="exam-sidebar">
                    <div class="exam-sidebar-header">
                        <h3>Navigasi Soal</h3>
                        <span class="progress-pill" id="exam-progress-text">0/0 Terjawab</span>
                    </div>
                    
                    <div class="question-grid" id="question-grid">
                        <!-- Number grid generated dynamically -->
                    </div>

                    <div class="exam-sidebar-footer">
                        <div class="legend-grid">
                            <div><span class="dot done"></span><span>Dijawab</span></div>
                            <div><span class="dot flagged"></span><span>Ragu-ragu</span></div>
                            <div><span class="dot active"></span><span>Aktif</span></div>
                            <div><span class="dot empty"></span><span>Belum</span></div>
                        </div>
                        <button class="finish-test-btn" id="btn-finish-exam" type="button">Submit & Selesai Ujian</button>
                    </div>
                </aside>
            </section>

        </main>
    </div>

    <script src="{{ asset('js/peserta.js') }}?v=1.0.2" defer></script>
</body>
</html>
