const state = {
    token: localStorage.getItem('cbt_token'),
    type: localStorage.getItem('cbt_type'),
    user: JSON.parse(localStorage.getItem('cbt_user') || 'null'),
    pesertaDashboard: null,
    pesertaTests: [],
    pesertaHistory: [],
    currentView: 'overview',
    
    // Exam state
    examTestId: null,
    examQuestions: [],
    examCurrentIndex: 0,
    examTimerInterval: null,
    examFlagged: {},
};

const els = {
    appShell: document.getElementById('app-shell'),
    dashboardView: document.getElementById('dashboard-view'),
    metricGrid: document.getElementById('metric-grid'),
    workTitle: document.getElementById('work-title'),
    workContent: document.getElementById('work-content'),
    roleBadge: document.getElementById('role-badge'),
    taskList: document.getElementById('task-list'),
    pageTitle: document.getElementById('page-title'),
    navItems: [...document.querySelectorAll('.nav-item')],
    logoutButton: document.getElementById('logout-button'),
    sessionChip: document.getElementById('session-chip'),
    
    // Exam View Elements
    examView: document.getElementById('exam-view'),
    examCategoryBadge: document.getElementById('exam-category-badge'),
    examQuestionTitle: document.getElementById('exam-question-title'),
    examTimerVal: document.getElementById('exam-timer-val'),
    questionText: document.getElementById('question-text'),
    questionImageContainer: document.getElementById('question-image-container'),
    questionImage: document.getElementById('question-image'),
    optionsList: document.getElementById('options-list'),
    btnPrevQuestion: document.getElementById('btn-prev-question'),
    btnFlagQuestion: document.getElementById('btn-flag-question'),
    btnNextQuestion: document.getElementById('btn-next-question'),
    examProgressText: document.getElementById('exam-progress-text'),
    questionGrid: document.getElementById('question-grid'),
    btnFinishExam: document.getElementById('btn-finish-exam'),
};

const apiBase = document.body.dataset.apiBase || '/api';

const pesertaViews = {
    overview: 'Dashboard CBT',
    'my-test': 'Tes Saya',
    history: 'Riwayat Tes',
};

// Session Validation
if (!state.token || state.type !== 'peserta') {
    clearSession();
    window.location.href = '/';
}

function clearSession() {
    state.token = null;
    state.type = null;
    state.user = null;
    localStorage.removeItem('cbt_token');
    localStorage.removeItem('cbt_type');
    localStorage.removeItem('cbt_user');
}

function userName() {
    return state.user?.nama_lengkap || state.user?.nomor_peserta || 'Peserta';
}

function escapeHtml(value) {
    return String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}

function displayDate(value) {
    if (!value) return '-';
    const date = new Date(value);
    return Number.isNaN(date.getTime())
        ? value
        : date.toLocaleString('id-ID', { dateStyle: 'medium', timeStyle: 'short' });
}

async function apiRequest(path, options = {}) {
    const response = await fetch(`${apiBase}${path}`, {
        ...options,
        headers: {
            'Accept': 'application/json',
            ...(options.body ? { 'Content-Type': 'application/json' } : {}),
            ...(state.token ? { 'Authorization': `Bearer ${state.token}` } : {}),
            ...(options.headers || {}),
        },
    });

    const payload = await response.json().catch(() => ({}));
    if (!response.ok) {
        const errors = payload.errors ? Object.values(payload.errors).flat().join(' ') : '';
        throw new Error(errors || payload.message || 'Permintaan gagal diproses.');
    }

    return payload;
}

function setLoading(message = 'Memuat data...') {
    els.workContent.innerHTML = `<div class="empty-state">${escapeHtml(message)}</div>`;
}

function showError(error) {
    els.workContent.innerHTML = `<div class="empty-state danger">${escapeHtml(error.message)}</div>`;
}

async function logout() {
    if (state.examTimerInterval) {
        clearInterval(state.examTimerInterval);
    }
    if (state.token) {
        await apiRequest('/logout', { method: 'POST' }).catch(() => {});
    }
    clearSession();
    disableAntiCheating();
    window.location.href = '/';
}

async function loadPesertaDashboard() {
    state.pesertaDashboard = await apiRequest('/peserta/dashboard');
    return state.pesertaDashboard;
}

async function loadPesertaTests() {
    state.pesertaTests = await apiRequest('/peserta/tests');
    return state.pesertaTests;
}

async function loadPesertaHistory() {
    state.pesertaHistory = await apiRequest('/peserta/history');
    return state.pesertaHistory;
}

function metricsForRole() {
    const stats = state.pesertaDashboard?.stats || {};
    return [
        ['Total Tes', stats.total_tests ?? '0'],
        ['Tes Tersedia', stats.available_tests ?? '0'],
        ['Sedang Tes', stats.running_tests ?? '0'],
        ['Riwayat', stats.completed_tests ?? '0'],
    ];
}

function renderMetrics() {
    els.metricGrid.innerHTML = metricsForRole()
        .map(([label, value]) => `<article class="metric-card"><span>${label}</span><strong>${escapeHtml(value)}</strong></article>`)
        .join('');
}

function tasksForRole() {
    return [
        ['Cek Tes Saya', 'Lihat daftar ujian yang tersedia untuk akun Anda.'],
        ['Periksa jadwal', 'Pastikan tanggal, durasi, dan instruksi sudah dibaca.'],
        ['Ikuti ujian', 'Mulai tes hanya saat jadwal sudah aktif.'],
        ['Riwayat Tes', 'Lihat status pengerjaan tanpa informasi nilai.'],
    ];
}

function renderTaskList() {
    els.taskList.innerHTML = tasksForRole()
        .map(([title, detail]) => `<li><strong>${escapeHtml(title)}</strong><span>${escapeHtml(detail)}</span></li>`)
        .join('');
}

function testTimeRange(test) {
    return `${displayDate(test.jadwal?.tanggal_mulai)} - ${displayDate(test.jadwal?.tanggal_selesai)}`;
}

function renderPesertaDashboard() {
    const profile = state.pesertaDashboard?.profile || state.user || {};
    const upcoming = state.pesertaDashboard?.upcoming_tests || [];

    els.workTitle.textContent = 'Dashboard Peserta';
    els.workContent.innerHTML = `
        <div class="module-grid">
            <article class="module-card"><strong>Nomor peserta</strong><p>${escapeHtml(profile.nomor_peserta || '-')}</p></article>
            <article class="module-card"><strong>Nama peserta</strong><p>${escapeHtml(profile.nama_lengkap || '-')}</p></article>
            <article class="module-card"><strong>Status akun</strong><p>${escapeHtml(profile.status || '-')}</p></article>
            <article class="module-card"><strong>Catatan</strong><p>Peserta tidak dapat melihat nilai atau status kelulusan dari halaman ini.</p></article>
        </div>
        <h3 class="section-title">Tes Tersedia</h3>
        ${renderPesertaTestRows(upcoming, 'Belum ada tes yang tersedia.')}`;
}

function renderPesertaTests() {
    els.workTitle.textContent = 'Tes Saya';
    els.workContent.innerHTML = renderPesertaTestRows(state.pesertaTests, 'Belum ada tes aktif atau belum mulai.');
}

function renderPesertaHistory() {
    els.workTitle.textContent = 'Riwayat Tes';
    els.workContent.innerHTML = `
        <div class="empty-state">Riwayat hanya menampilkan jadwal dan status pengerjaan. Nilai dan kelulusan disembunyikan untuk peserta.</div>
        ${renderPesertaTestRows(state.pesertaHistory, 'Belum ada riwayat tes.')}`;
}

function renderPesertaTestRows(rows, emptyMessage) {
    return `
        <div class="data-table">
            ${rows.map((test) => {
                let actionHtml = '';
                if (test.status_tes === 'belum_mulai') {
                    actionHtml = `<button class="action-btn-exam start-test-btn" data-id="${test.id_peserta_tes}" type="button">Mulai Ujian</button>`;
                } else if (test.status_tes === 'sedang_tes') {
                    actionHtml = `<button class="action-btn-exam continue-test-btn" data-id="${test.id_peserta_tes}" type="button">Lanjutkan</button>`;
                } else {
                    actionHtml = `<span class="status-pill ok">Selesai</span>`;
                }

                return `
                <div class="data-row peserta-row">
                    <div>
                        <strong>${escapeHtml(test.jadwal?.nama_tes || '-')}</strong>
                        <span>${escapeHtml(test.jadwal?.kategori || 'Tanpa kategori')} - ${testTimeRange(test)}</span>
                    </div>
                    <span>${escapeHtml(test.jadwal?.durasi || '-')} menit</span>
                    <span>${escapeHtml(test.jadwal?.jumlah_soal || '-')} soal</span>
                    ${actionHtml}
                </div>
                ${test.jadwal?.instruksi ? `<div class="instruction-row">${escapeHtml(test.jadwal.instruksi)}</div>` : ''}`;
            }).join('') || `<div class="empty-state">${escapeHtml(emptyMessage)}</div>`}
        </div>`;
}

async function setActiveView(name) {
    state.currentView = name;
    const title = pesertaViews[name] || 'Dashboard CBT';
    els.pageTitle.textContent = title;
    els.navItems.forEach((item) => item.classList.toggle('active', item.dataset.view === name));

    try {
        setLoading();
        if (name === 'my-test') {
            await loadPesertaTests();
            renderPesertaTests();
        } else if (name === 'history') {
            await loadPesertaHistory();
            renderPesertaHistory();
        } else {
            await loadPesertaDashboard();
            renderMetrics();
            renderPesertaDashboard();
        }
    } catch (error) {
        showError(error);
    }
}

async function renderApp() {
    els.sessionChip.textContent = `${userName()} (Peserta)`;
    els.roleBadge.textContent = 'Peserta';
    renderTaskList();
    await loadPesertaDashboard().catch(() => {});
    renderMetrics();
    await setActiveView('overview');
}

// ==========================================
// EXAM CLIENT LOGIC
// ==========================================
async function startExamFlow(idPesertaTes, isContinue = false) {
    const message = isContinue 
        ? 'Lanjutkan pengerjaan ujian?' 
        : 'Apakah Anda yakin ingin memulai ujian ini? Waktu akan mulai berjalan sejak Anda klik OK.';
        
    if (!window.confirm(message)) return;

    try {
        setLoading('Menyiapkan ujian...');
        const response = await apiRequest(`/peserta/tests/${idPesertaTes}/start`, { method: 'POST' });
        
        state.examTestId = idPesertaTes;
        state.examFlagged = {};
        
        els.appShell.classList.add('exam-mode');
        els.dashboardView.hidden = true;
        els.examView.hidden = false;

        enableAntiCheating();
        await loadExamQuestions(idPesertaTes);
    } catch (error) {
        window.alert(error.message);
        renderApp();
    }
}

async function loadExamQuestions(idPesertaTes) {
    try {
        const response = await apiRequest(`/peserta/tests/${idPesertaTes}/questions`);
        state.examQuestions = response.questions || [];
        state.examCurrentIndex = 0;
        els.examCategoryBadge.textContent = 'UJIAN CBT';
        
        renderQuestionGrid();
        renderQuestion(0);
        startCountdown(response.remaining_seconds);
    } catch (error) {
        window.alert('Gagal mengambil soal ujian: ' + error.message);
        exitExamFlow();
    }
}

function renderQuestionGrid() {
    els.questionGrid.innerHTML = state.examQuestions.map((q, idx) => {
        const num = idx + 1;
        const isAnswered = q.jawaban_anda !== null && q.jawaban_anda !== undefined;
        const isFlagged = state.examFlagged[idx] === true;
        
        let statusClass = '';
        if (isFlagged) {
            statusClass = 'flagged';
        } else if (isAnswered) {
            statusClass = 'done';
        }
        
        return `<button class="grid-num-btn ${statusClass}" data-index="${idx}" type="button" id="grid-q-${idx}">${num}</button>`;
    }).join('');
    
    els.questionGrid.querySelectorAll('.grid-num-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const index = parseInt(btn.dataset.index, 10);
            renderQuestion(index);
        });
    });
    
    updateProgressText();
}

function updateProgressText() {
    const answeredCount = state.examQuestions.filter(q => q.jawaban_anda !== null && q.jawaban_anda !== undefined).length;
    els.examProgressText.textContent = `${answeredCount} / ${state.examQuestions.length} Terjawab`;
}

function renderQuestion(index) {
    if (index < 0 || index >= state.examQuestions.length) return;
    
    els.questionGrid.querySelectorAll('.grid-num-btn').forEach(btn => {
        btn.classList.remove('active');
        const btnIndex = parseInt(btn.dataset.index, 10);
        if (btnIndex === index) {
            btn.classList.add('active');
        }
    });
    
    state.examCurrentIndex = index;
    const q = state.examQuestions[index];
    
    els.examQuestionTitle.textContent = `Soal No. ${index + 1}`;
    els.questionText.textContent = q.pertanyaan;
    
    if (q.gambar) {
        els.questionImage.src = q.gambar;
        els.questionImageContainer.hidden = false;
    } else {
        els.questionImageContainer.hidden = true;
    }
    
    const choices = [
        ['A', q.pilihan_a],
        ['B', q.pilihan_b],
        ['C', q.pilihan_c],
        ['D', q.pilihan_d],
        ['E', q.pilihan_e],
    ].filter(item => item[1] !== null && item[1] !== undefined && item[1] !== '');
    
    els.optionsList.innerHTML = choices.map(([key, val]) => {
        const isSelected = q.jawaban_anda === key;
        return `
            <label class="option-card ${isSelected ? 'selected' : ''}" data-key="${key}">
                <input type="radio" name="exam_choice" value="${key}" ${isSelected ? 'checked' : ''} style="display:none;">
                <span class="option-key">${key}</span>
                <span class="option-val">${escapeHtml(val)}</span>
            </label>
        `;
    }).join('');
    
    els.optionsList.querySelectorAll('.option-card').forEach(card => {
        card.addEventListener('click', () => {
            const key = card.dataset.key;
            els.optionsList.querySelectorAll('.option-card').forEach(c => c.classList.remove('selected'));
            card.classList.add('selected');
            saveExamAnswer(q.id_soal, key);
        });
    });
    
    els.btnPrevQuestion.disabled = (index === 0);
    els.btnNextQuestion.disabled = (index === state.examQuestions.length - 1);
    
    if (state.examFlagged[index]) {
        els.btnFlagQuestion.classList.add('active');
    } else {
        els.btnFlagQuestion.classList.remove('active');
    }
}

async function saveExamAnswer(idSoal, jawaban) {
    const idx = state.examCurrentIndex;
    state.examQuestions[idx].jawaban_anda = jawaban;
    
    const gridBtn = document.getElementById(`grid-q-${idx}`);
    if (gridBtn && !state.examFlagged[idx]) {
        gridBtn.className = 'grid-num-btn done active';
    }
    updateProgressText();
    
    try {
        await apiRequest(`/peserta/tests/${state.examTestId}/answer`, {
            method: 'POST',
            body: JSON.stringify({ id_soal: idSoal, jawaban: jawaban }),
        });
    } catch (error) {
        console.error('Gagal menyimpan jawaban: ', error);
    }
}

function toggleExamFlag() {
    const idx = state.examCurrentIndex;
    const isFlagged = !state.examFlagged[idx];
    state.examFlagged[idx] = isFlagged;
    
    const gridBtn = document.getElementById(`grid-q-${idx}`);
    const isAnswered = state.examQuestions[idx].jawaban_anda !== null && state.examQuestions[idx].jawaban_anda !== undefined;
    
    if (gridBtn) {
        gridBtn.className = 'grid-num-btn active';
        if (isFlagged) {
            gridBtn.classList.add('flagged');
        } else if (isAnswered) {
            gridBtn.classList.add('done');
        }
    }
    
    if (isFlagged) {
        els.btnFlagQuestion.classList.add('active');
    } else {
        els.btnFlagQuestion.classList.remove('active');
    }
}

function startCountdown(seconds) {
    if (state.examTimerInterval) {
        clearInterval(state.examTimerInterval);
    }
    
    function updateVal(sec) {
        if (sec <= 0) {
            clearInterval(state.examTimerInterval);
            els.examTimerVal.textContent = '00:00';
            els.examTimerVal.classList.add('expired');
            autoFinishExam();
            return;
        }
        
        const h = Math.floor(sec / 3600);
        const m = Math.floor((sec % 3600) / 60);
        const s = sec % 60;
        const pad = n => String(n).padStart(2, '0');
        
        if (h > 0) {
            els.examTimerVal.textContent = `${pad(h)}:${pad(m)}:${pad(s)}`;
        } else {
            els.examTimerVal.textContent = `${pad(m)}:${pad(s)}`;
        }
        
        if (sec < 60) {
            els.examTimerVal.classList.add('pulse-warn');
        } else {
            els.examTimerVal.classList.remove('pulse-warn');
        }
    }
    
    let currentSec = seconds;
    updateVal(currentSec);
    state.examTimerInterval = setInterval(() => {
        currentSec--;
        updateVal(currentSec);
    }, 1000);
}

async function autoFinishExam() {
    window.alert('Waktu ujian telah habis! Jawaban Anda akan otomatis dikirimkan.');
    await submitFinishExam(true);
}

async function submitFinishExam(forced = false) {
    if (!forced) {
        const answeredCount = state.examQuestions.filter(q => q.jawaban_anda !== null && q.jawaban_anda !== undefined).length;
        const total = state.examQuestions.length;
        
        let confirmMsg = `Apakah Anda yakin ingin mengakhiri ujian ini?\n\nTerjawab: ${answeredCount} dari ${total} soal.`;
        if (answeredCount < total) {
            confirmMsg += `\n\nPERINGATAN: Ada ${total - answeredCount} soal yang belum Anda jawab!`;
        }
        if (!window.confirm(confirmMsg)) return;
    }
    
    if (state.examTimerInterval) {
        clearInterval(state.examTimerInterval);
    }
    
    try {
        setLoading('Mengirimkan hasil ujian...');
        const response = await apiRequest(`/peserta/tests/${state.examTestId}/finish`, { method: 'POST' });
        window.alert(`Ujian Selesai!\nNilai Anda: ${response.nilai}\nStatus Kelulusan: ${response.status_kelulusan.toUpperCase()}`);
    } catch (error) {
        window.alert('Gagal menyelesaikan ujian: ' + error.message);
    } finally {
        exitExamFlow();
    }
}

function exitExamFlow() {
    if (state.examTimerInterval) {
        clearInterval(state.examTimerInterval);
    }
    state.examTestId = null;
    state.examQuestions = [];
    els.appShell.classList.remove('exam-mode');
    els.examView.hidden = true;
    disableAntiCheating();
    renderApp();
}

// ==========================================
// ANTI-CHEATING MEASURES
// ==========================================
let antiCheatingActive = false;
let blurViolationsCount = 0;

function handleBeforeUnload(e) {
    if (state.examTestId) {
        e.preventDefault();
        e.returnValue = 'Anda sedang mengerjakan ujian. Yakin ingin meninggalkan halaman?';
        return e.returnValue;
    }
}

function handleVisibilityChange() {
    if (state.examTestId && document.hidden) {
        console.warn('[Anti-Cheat] Tab switch detected.');
    }
}

function handleWindowBlur() {
    if (state.examTestId) {
        blurViolationsCount++;
        window.alert(`[PERINGATAN KECURANGAN #${blurViolationsCount}]\nAnda dideteksi meninggalkan layar ujian! Harap fokus pada halaman ujian Anda.`);
    }
}

function handleContextMenu(e) {
    if (state.examTestId) e.preventDefault();
}

function handleKeyDown(e) {
    if (!state.examTestId) return;
    if (
        e.key === 'F12' ||
        (e.ctrlKey && e.shiftKey && e.key === 'I') ||
        (e.ctrlKey && e.key === 'u') ||
        (e.ctrlKey && e.key === 'c') ||
        (e.ctrlKey && e.key === 'v')
    ) {
        e.preventDefault();
        return false;
    }
}

function enableAntiCheating() {
    if (antiCheatingActive) return;
    antiCheatingActive = true;
    blurViolationsCount = 0;
    
    window.addEventListener('beforeunload', handleBeforeUnload);
    document.addEventListener('visibilitychange', handleVisibilityChange);
    window.addEventListener('blur', handleWindowBlur);
    document.addEventListener('contextmenu', handleContextMenu);
    document.addEventListener('keydown', handleKeyDown);
    
    history.pushState(null, '', window.location.href);
    window.addEventListener('popstate', handlePopState);
}

function handlePopState() {
    if (state.examTestId) {
        history.pushState(null, '', window.location.href);
    }
}

function disableAntiCheating() {
    if (!antiCheatingActive) return;
    antiCheatingActive = false;
    
    window.removeEventListener('beforeunload', handleBeforeUnload);
    document.removeEventListener('visibilitychange', handleVisibilityChange);
    window.removeEventListener('blur', handleWindowBlur);
    document.removeEventListener('contextmenu', handleContextMenu);
    document.removeEventListener('keydown', handleKeyDown);
    window.removeEventListener('popstate', handlePopState);
}

// Global Triggers in content
els.workContent.addEventListener('click', async (event) => {
    const examBtn = event.target.closest('.start-test-btn, .continue-test-btn');
    if (examBtn) {
        const id = examBtn.dataset.id;
        const isContinue = examBtn.classList.contains('continue-test-btn');
        await startExamFlow(id, isContinue);
    }
});

// Navigation Click listeners
els.navItems.forEach((item) => {
    item.addEventListener('click', () => setActiveView(item.dataset.view));
});

// Logout listener
els.logoutButton.addEventListener('click', logout);

// Exam Control Buttons
els.btnPrevQuestion.addEventListener('click', () => {
    if (state.examCurrentIndex > 0) renderQuestion(state.examCurrentIndex - 1);
});
els.btnNextQuestion.addEventListener('click', () => {
    if (state.examCurrentIndex < state.examQuestions.length - 1) renderQuestion(state.examCurrentIndex + 1);
});
els.btnFlagQuestion.addEventListener('click', toggleExamFlag);
els.btnFinishExam.addEventListener('click', () => submitFinishExam(false));

// Run App on Load
renderApp();
