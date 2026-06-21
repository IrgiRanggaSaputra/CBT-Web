const state = {
    token: localStorage.getItem('cbt_token'),
    type: localStorage.getItem('cbt_type'),
    user: JSON.parse(localStorage.getItem('cbt_user') || 'null'),
    stats: null,
    categories: [],
    participants: [],
    questions: [],
    schedules: [],
    reports: [],
    currentView: 'overview',
    monitoringInterval: null,
    selectedCategoryId: '',
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
};

// Admin Modals
const modalParticipants = document.getElementById('modal-participants');
const btnCloseParticipantsModal = document.getElementById('btn-close-participants-modal');
const modalTargetScheduleName = document.getElementById('modal-target-schedule-name');
const modalSelectParticipant = document.getElementById('modal-select-participant');
const modalAddParticipantForm = document.getElementById('modal-add-participant-form');
const modalAssignedParticipantsList = document.getElementById('modal-assigned-participants-list');

const modalImportSoal = document.getElementById('modal-import-soal');
const btnCloseImportSoalModal = document.getElementById('btn-close-import-soal-modal');
const modalImportSoalForm = document.getElementById('modal-import-soal-form');
const modalImportFileInput = document.getElementById('modal-import-file-input');
const importFileNameLabel = document.getElementById('import-file-name-label');

const modalImportPeserta = document.getElementById('modal-import-peserta');
const btnCloseImportPesertaModal = document.getElementById('btn-close-import-peserta-modal');
const modalImportPesertaForm = document.getElementById('modal-import-peserta-form');
const modalImportPesertaFileInput = document.getElementById('modal-import-peserta-file-input');
const importPesertaFileNameLabel = document.getElementById('import-peserta-file-name-label');

let currentScheduleId = null;
const apiBase = document.body.dataset.apiBase || '/api';

const adminViews = {
    overview: 'Dashboard CBT',
    participants: 'Manajemen Peserta',
    categories: 'Kategori Soal',
    'question-bank': 'Bank Soal',
    schedule: 'Jadwal Tes',
    monitoring: 'Monitoring',
    reports: 'Laporan',
};

// Session Validation
if (!state.token || state.type !== 'admin') {
    clearSession();
    window.location.href = '/';
}

function clearSession() {
    state.token = null;
    state.type = null;
    state.user = null;
    state.stats = null;
    localStorage.removeItem('cbt_token');
    localStorage.removeItem('cbt_type');
    localStorage.removeItem('cbt_user');
}

function userName() {
    return state.user?.nama_lengkap || state.user?.username || 'Admin';
}

function escapeHtml(value) {
    return String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}

function dateInputValue(value) {
    if (!value) return '';
    const date = new Date(value);
    if (Number.isNaN(date.getTime())) {
        return String(value).slice(0, 16);
    }
    const offsetDate = new Date(date.getTime() - date.getTimezoneOffset() * 60000);
    return offsetDate.toISOString().slice(0, 16);
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
    if (state.monitoringInterval) {
        clearInterval(state.monitoringInterval);
    }
    if (state.token) {
        await apiRequest('/logout', { method: 'POST' }).catch(() => {});
    }
    clearSession();
    window.location.href = '/';
}

async function loadStats() {
    state.stats = await apiRequest('/admin/stats');
    return state.stats;
}

function metricsForRole() {
    const stats = state.stats || {};
    return [
        ['Peserta', stats.participants ?? '0'],
        ['Soal', stats.questions ?? '0'],
        ['Jadwal Aktif', stats.active_schedules ?? '0'],
        ['Sedang Tes', stats.running_tests ?? '0'],
        ['Lulus', stats.passed_tests ?? '0'],
        ['Tidak Lulus', stats.failed_tests ?? '0'],
    ];
}

function renderMetrics() {
    els.metricGrid.innerHTML = metricsForRole()
        .map(([label, value]) => `<article class="metric-card"><span>${label}</span><strong>${escapeHtml(value)}</strong></article>`)
        .join('');
}

function tasksForRole() {
    return [
        ['Peserta', 'Tambah akun peserta dan aktifkan status sebelum ujian.'],
        ['Kategori dan soal', 'Kelompokkan soal agar jadwal tes mudah disusun.'],
        ['Jadwal', 'Pastikan periode, durasi, dan passing grade sudah benar.'],
        ['Laporan', 'Pantau hasil pengerjaan dari menu laporan.'],
    ];
}

function renderTaskList() {
    els.taskList.innerHTML = tasksForRole()
        .map(([title, detail]) => `<li><strong>${escapeHtml(title)}</strong><span>${escapeHtml(detail)}</span></li>`)
        .join('');
}

function categoryOptions(selectedId = '') {
    const selected = String(selectedId || '');
    return [
        '<option value="">Tanpa kategori</option>',
        ...state.categories.map((category) => {
            const id = String(category.id_kategori);
            return `<option value="${id}" ${id === selected ? 'selected' : ''}>${escapeHtml(category.nama_kategori)}</option>`;
        }),
    ].join('');
}

function renderAdminOverview() {
    const stats = state.stats || {};
    els.workTitle.textContent = 'Ringkasan Operasional';
    els.workContent.innerHTML = `
        <div class="module-grid">
            <article class="module-card"><strong>Peserta aktif</strong><p>${escapeHtml(stats.active_participants ?? 0)} dari ${escapeHtml(stats.participants ?? 0)} peserta siap mengikuti ujian.</p></article>
            <article class="module-card"><strong>Bank soal</strong><p>${escapeHtml(stats.questions ?? 0)} soal tersedia dalam ${escapeHtml(stats.categories ?? 0)} kategori.</p></article>
            <article class="module-card"><strong>Jadwal tes</strong><p>${escapeHtml(stats.active_schedules ?? 0)} jadwal aktif dari ${escapeHtml(stats.schedules ?? 0)} total jadwal.</p></article>
            <article class="module-card"><strong>Hasil tes</strong><p>${escapeHtml(stats.finished_tests ?? 0)} pengerjaan selesai. <span class="text-pass">${escapeHtml(stats.passed_tests ?? 0)} lulus</span>, <span class="text-fail">${escapeHtml(stats.failed_tests ?? 0)} tidak lulus</span>.</p></article>
        </div>`;
}

function actionButtons(id) {
    return `
        <div class="row-actions">
            <button class="icon-button" type="button" data-action="edit" data-id="${escapeHtml(id)}" title="Edit">Edit</button>
            <button class="icon-button danger" type="button" data-action="delete" data-id="${escapeHtml(id)}" title="Hapus">Hapus</button>
        </div>`;
}

async function loadParticipants() {
    state.participants = await apiRequest('/admin/participants');
}

function renderParticipants(editId = '') {
    const item = state.participants.find((row) => String(row.id_peserta) === String(editId)) || {};
    const isEdit = Boolean(item.id_peserta);

    els.workTitle.textContent = 'Data Peserta';
    els.workContent.innerHTML = `
        <form class="admin-form" data-form="participant" data-id="${escapeHtml(item.id_peserta || '')}">
            <div class="form-grid">
                <label><span>Nomor peserta</span><input name="nomor_peserta" required maxlength="20" value="${escapeHtml(item.nomor_peserta)}"></label>
                <label><span>Nama lengkap</span><input name="nama_lengkap" required value="${escapeHtml(item.nama_lengkap)}"></label>
                <label><span>Email</span><input name="email" type="email" value="${escapeHtml(item.email)}"></label>
                <label><span>Password ${isEdit ? '(kosongkan jika tetap)' : ''}</span><input name="password" type="password" ${isEdit ? '' : 'required'} minlength="6"></label>
                <label><span>Jenis kelamin</span><select name="jenis_kelamin">
                    <option value="">-</option>
                    <option value="L" ${item.jenis_kelamin === 'L' ? 'selected' : ''}>Laki-laki</option>
                    <option value="P" ${item.jenis_kelamin === 'P' ? 'selected' : ''}>Perempuan</option>
                </select></label>
                <label><span>Status</span><select name="status">
                    <option value="aktif" ${item.status !== 'nonaktif' ? 'selected' : ''}>Aktif</option>
                    <option value="nonaktif" ${item.status === 'nonaktif' ? 'selected' : ''}>Nonaktif</option>
                </select></label>
                <label><span>Telepon</span><input name="telepon" maxlength="15" value="${escapeHtml(item.telepon)}"></label>
                <label><span>Tanggal lahir</span><input name="tanggal_lahir" type="date" value="${escapeHtml(item.tanggal_lahir ? String(item.tanggal_lahir).slice(0, 10) : '')}"></label>
                <label class="span-2"><span>Alamat</span><textarea name="alamat">${escapeHtml(item.alamat)}</textarea></label>
            </div>
            <div class="form-actions">
                <button class="primary-button" type="submit">${isEdit ? 'Simpan Perubahan' : 'Tambah Peserta'}</button>
                ${isEdit ? '<button class="ghost-button" type="button" data-action="cancel">Batal</button>' : '<button class="ghost-button" type="button" id="btn-trigger-import-peserta">Import Excel/CSV</button>'}
            </div>
        </form>
        <div class="data-table">
            ${state.participants.map((row) => `
                <div class="data-row">
                    <div><strong>${escapeHtml(row.nama_lengkap)}</strong><span>${escapeHtml(row.nomor_peserta)} - ${escapeHtml(row.email || '-')}</span></div>
                    <span class="status-pill ${row.status === 'aktif' ? 'ok' : ''}">${escapeHtml(row.status)}</span>
                    ${actionButtons(row.id_peserta)}
                </div>`).join('') || '<div class="empty-state">Belum ada peserta.</div>'}
        </div>`;
}

async function loadCategories() {
    state.categories = await apiRequest('/admin/categories');
}

function renderCategories(editId = '') {
    const item = state.categories.find((row) => String(row.id_kategori) === String(editId)) || {};
    const isEdit = Boolean(item.id_kategori);

    els.workTitle.textContent = 'Kategori Soal';
    els.workContent.innerHTML = `
        <form class="admin-form" data-form="category" data-id="${escapeHtml(item.id_kategori || '')}">
            <div class="form-grid">
                <label><span>Nama kategori</span><input name="nama_kategori" required value="${escapeHtml(item.nama_kategori)}"></label>
                <label class="span-2"><span>Deskripsi</span><textarea name="deskripsi">${escapeHtml(item.deskripsi)}</textarea></label>
            </div>
            <div class="form-actions">
                <button class="primary-button" type="submit">${isEdit ? 'Simpan Perubahan' : 'Tambah Kategori'}</button>
                ${isEdit ? '<button class="ghost-button" type="button" data-action="cancel">Batal</button>' : ''}
            </div>
        </form>
        <div class="data-table">
            ${state.categories.map((row) => `
                <div class="data-row">
                    <div><strong>${escapeHtml(row.nama_kategori)}</strong><span>${escapeHtml(row.deskripsi || '-')}</span></div>
                    <span>${escapeHtml(row.bank_soals_count ?? 0)} soal</span>
                    ${actionButtons(row.id_kategori)}
                </div>`).join('') || '<div class="empty-state">Belum ada kategori.</div>'}
        </div>`;
}

async function loadQuestions() {
    await loadCategories();
    state.questions = await apiRequest('/admin/questions');
}

function renderQuestions(editId = '') {
    const item = state.questions.find((row) => String(row.id_soal) === String(editId)) || {};
    const isEdit = Boolean(item.id_soal);

    // Determine category options for the filter dropdown
    const filterOptions = [
        '<option value="">-- Pilih Kategori Soal --</option>',
        ...state.categories.map((c) => {
            const id = String(c.id_kategori);
            return `<option value="${id}" ${String(state.selectedCategoryId || '') === id ? 'selected' : ''}>${escapeHtml(c.nama_kategori)}</option>`;
        })
    ].join('');

    els.workTitle.textContent = 'Bank Soal';

    // Filter questions if a category is selected
    const filteredQuestions = state.selectedCategoryId 
        ? state.questions.filter(q => String(q.id_kategori) === String(state.selectedCategoryId))
        : [];

    let formHtml = '';

    if (!state.selectedCategoryId) {
        formHtml = `
            <div class="empty-state" style="margin-bottom: 24px;">
                Silakan pilih kategori soal terlebih dahulu untuk mengelola dan menambahkan soal.
            </div>`;
    } else {
        // Pre-select the filtered category and lock it
        const catId = isEdit ? item.id_kategori : state.selectedCategoryId;
        formHtml = `
            <form class="admin-form" data-form="question" data-id="${escapeHtml(item.id_soal || '')}">
                <div class="form-grid">
                    <label class="span-2"><span>Pertanyaan</span><textarea name="pertanyaan" required>${escapeHtml(item.pertanyaan)}</textarea></label>
                    <label><span>Kategori</span>
                        <select name="id_kategori" required>
                            ${state.categories.filter(c => String(c.id_kategori) === String(catId)).map(c => `<option value="${c.id_kategori}" selected>${escapeHtml(c.nama_kategori)}</option>`).join('')}
                        </select>
                    </label>
                    <label><span>Jawaban benar</span><select name="jawaban_benar" required>${['A', 'B', 'C', 'D', 'E'].map((opt) => `<option value="${opt}" ${item.jawaban_benar === opt ? 'selected' : ''}>${opt}</option>`).join('')}</select></label>
                    <label><span>Pilihan A</span><input name="pilihan_a" required value="${escapeHtml(item.pilihan_a)}"></label>
                    <label><span>Pilihan B</span><input name="pilihan_b" required value="${escapeHtml(item.pilihan_b)}"></label>
                    <label><span>Pilihan C</span><input name="pilihan_c" required value="${escapeHtml(item.pilihan_c)}"></label>
                    <label><span>Pilihan D</span><input name="pilihan_d" required value="${escapeHtml(item.pilihan_d)}"></label>
                    <label><span>Pilihan E</span><input name="pilihan_e" value="${escapeHtml(item.pilihan_e)}"></label>
                    <label><span>Bobot</span><input name="bobot" type="number" min="1" max="100" required value="${escapeHtml(item.bobot || 1)}"></label>
                </div>
                <div class="form-actions">
                    <button class="primary-button" type="submit">${isEdit ? 'Simpan Perubahan' : 'Tambah Soal'}</button>
                    ${isEdit ? '<button class="ghost-button" type="button" data-action="cancel">Batal</button>' : '<button class="ghost-button" type="button" id="btn-trigger-import-soal">Import Excel/CSV</button>'}
                </div>
            </form>`;
    }

    els.workContent.innerHTML = `
        <div style="margin-bottom: 24px; display: flex; gap: 16px; align-items: center; background: var(--surface-soft); padding: 16px; border-radius: var(--border-radius); border: 1px solid var(--line);">
            <label style="font-weight: 700; font-size: 14px; display: flex; align-items: center; gap: 10px; width: 100%; margin: 0;">
                <span>Kategori Soal:</span>
                <select id="filter-question-category" style="width: auto; min-width: 200px; height: 38px; padding: 0 10px; border-radius: 6px; background: var(--surface); border: 1px solid var(--line); color: var(--ink); outline: none;">
                    ${filterOptions}
                </select>
            </label>
        </div>
        
        ${formHtml}
        
        ${state.selectedCategoryId ? `
        <div class="data-table">
            ${filteredQuestions.map((row) => `
                <div class="data-row">
                    <div><strong>${escapeHtml(row.pertanyaan)}</strong><span>${escapeHtml(row.kategori?.nama_kategori || 'Tanpa kategori')} - Jawaban ${escapeHtml(row.jawaban_benar)} - Bobot ${escapeHtml(row.bobot)}</span></div>
                    ${actionButtons(row.id_soal)}
                </div>`).join('') || '<div class="empty-state">Belum ada soal untuk kategori ini.</div>'}
        </div>` : ''}
    `;

    const filterSelect = document.getElementById('filter-question-category');
    if (filterSelect) {
        filterSelect.addEventListener('change', (e) => {
            state.selectedCategoryId = e.target.value;
            renderQuestions();
        });
    }
}

async function loadSchedules() {
    await loadCategories();
    state.schedules = await apiRequest('/admin/schedules');
}

function renderSchedules(editId = '') {
    const item = state.schedules.find((row) => String(row.id_jadwal) === String(editId)) || {};
    const isEdit = Boolean(item.id_jadwal);

    els.workTitle.textContent = 'Jadwal Tes';
    els.workContent.innerHTML = `
        <form class="admin-form" data-form="schedule" data-id="${escapeHtml(item.id_jadwal || '')}">
            <div class="form-grid">
                <label><span>Nama tes</span><input name="nama_tes" required value="${escapeHtml(item.nama_tes)}"></label>
                <label><span>Kategori</span><select name="id_kategori">${categoryOptions(item.id_kategori)}</select></label>
                <label><span>Mulai</span><input name="tanggal_mulai" type="datetime-local" required value="${escapeHtml(dateInputValue(item.tanggal_mulai))}"></label>
                <label><span>Selesai</span><input name="tanggal_selesai" type="datetime-local" required value="${escapeHtml(dateInputValue(item.tanggal_selesai))}"></label>
                <label><span>Durasi menit</span><input name="durasi" type="number" min="1" required value="${escapeHtml(item.durasi || 90)}"></label>
                <label><span>Jumlah soal</span><input name="jumlah_soal" type="number" min="1" required value="${escapeHtml(item.jumlah_soal || 10)}"></label>
                <label><span>Passing grade</span><input name="passing_grade" type="number" min="0" max="100" step="0.01" required value="${escapeHtml(item.passing_grade || 70)}"></label>
                <label><span>Status</span><select name="status">
                    ${['draft', 'aktif', 'selesai'].map((opt) => `<option value="${opt}" ${item.status === opt ? 'selected' : ''}>${opt}</option>`).join('')}
                </select></label>
                <label class="span-2"><span>Instruksi</span><textarea name="instruksi">${escapeHtml(item.instruksi)}</textarea></label>
            </div>
            <div class="form-actions">
                <button class="primary-button" type="submit">${isEdit ? 'Simpan Perubahan' : 'Tambah Jadwal'}</button>
                ${isEdit ? '<button class="ghost-button" type="button" data-action="cancel">Batal</button>' : ''}
            </div>
        </form>
        <div class="data-table">
            ${state.schedules.map((row) => `
                <div class="data-row">
                    <div><strong>${escapeHtml(row.nama_tes)}</strong><span>${escapeHtml(row.kategori?.nama_kategori || 'Tanpa kategori')} - ${displayDate(row.tanggal_mulai)} - ${displayDate(row.tanggal_selesai)}</span></div>
                    <span class="status-pill ${row.status === 'aktif' ? 'ok' : ''}">${escapeHtml(row.status)}</span>
                    <div class="row-actions">
                        <button class="icon-button btn-trigger-participants" data-id="${escapeHtml(row.id_jadwal)}" data-name="${escapeHtml(row.nama_tes)}" type="button">👥 Peserta</button>
                        <button class="icon-button" type="button" data-action="edit" data-id="${escapeHtml(row.id_jadwal)}" title="Edit">Edit</button>
                        <button class="icon-button danger" type="button" data-action="delete" data-id="${escapeHtml(row.id_jadwal)}" title="Hapus">Hapus</button>
                    </div>
                </div>`).join('') || '<div class="empty-state">Belum ada jadwal.</div>'}
        </div>`;
}

async function loadReports() {
    state.reports = await apiRequest('/admin/reports');
}

function renderReports() {
    els.workTitle.textContent = 'Laporan Hasil Tes';
    if (!state.reports || state.reports.length === 0) {
        els.workContent.innerHTML = '<div class="empty-state">Belum ada data pengerjaan tes.</div>';
        return;
    }

    els.workContent.innerHTML = `
        <div class="reports-table-wrapper">
            <table class="reports-table">
                <thead>
                    <tr>
                        <th>Peserta</th>
                        <th>Jadwal Tes</th>
                        <th>Status Tes</th>
                        <th>Nilai</th>
                        <th>Kelulusan</th>
                        <th>Dijawab</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    ${state.reports.map((row) => {
                        // Status Tes Badge
                        let statusTesBadge = '';
                        if (row.status_tes === 'selesai') {
                            statusTesBadge = '<span class="badge badge-success">Selesai</span>';
                        } else if (row.status_tes === 'sedang_tes') {
                            statusTesBadge = '<span class="badge badge-info">Sedang Ujian</span>';
                        } else {
                            statusTesBadge = `<span class="badge badge-warning">${escapeHtml(row.status_tes || '-')}</span>`;
                        }

                        // Kelulusan Badge
                        let kelulusanBadge = '-';
                        if (row.status_kelulusan) {
                            const statusUpper = row.status_kelulusan.toUpperCase();
                            if (row.status_kelulusan === 'lulus') {
                                kelulusanBadge = `<span class="badge badge-success">${statusUpper}</span>`;
                            } else if (row.status_kelulusan === 'tidak_lulus' || row.status_kelulusan === 'tidak lulus') {
                                kelulusanBadge = `<span class="badge badge-danger">TIDAK LULUS</span>`;
                            } else {
                                kelulusanBadge = `<span class="badge badge-warning">${statusUpper}</span>`;
                            }
                        }

                        const actionHtml = `
                            <div class="row-actions">
                                <button class="icon-button btn-export-csv" data-id="${row.id_peserta_tes}" type="button">📊 Excel</button>
                                <a class="icon-button" href="/admin/reports/${row.id_peserta_tes}/print" target="_blank" style="text-decoration: none; display: inline-flex; align-items: center; justify-content: center; height: 34px; padding: 0 12px;">📄 Rapor</a>
                            </div>`;

                        return `
                        <tr>
                            <td>
                                <strong style="display: block; font-size: 14px; color: var(--ink);">${escapeHtml(row.peserta?.nama_lengkap || '-')}</strong>
                                <span style="display: block; font-size: 12px; color: var(--muted);">No: ${escapeHtml(row.peserta?.nomor_peserta || '-')}</span>
                            </td>
                            <td>
                                <span style="font-weight: 500; color: var(--ink);">${escapeHtml(row.jadwal?.nama_tes || '-')}</span>
                            </td>
                            <td>${statusTesBadge}</td>
                            <td><strong style="color: var(--ink);">${escapeHtml(row.nilai ?? '-')}</strong></td>
                            <td>${kelulusanBadge}</td>
                            <td>
                                <strong style="color: var(--ink);">${escapeHtml(row.answered_count ?? '-')}</strong>
                                <span style="color: var(--muted); font-size: 12px;">/ ${escapeHtml(row.total_questions ?? '-')} Soal</span>
                            </td>
                            <td>${actionHtml}</td>
                        </tr>`;
                    }).join('')}
                </tbody>
            </table>
        </div>`;
}

function renderMonitoring(data) {
    els.workTitle.textContent = 'Monitoring Peserta Ujian';

    if (!data || data.length === 0) {
        els.workContent.innerHTML = '<div class="empty-state">Tidak ada peserta yang sedang mengerjakan ujian saat ini.</div>';
        return;
    }

    els.workContent.innerHTML = `
        <div class="monitoring-info-bar">
            <span>🟢 ${data.length} peserta sedang mengerjakan ujian</span>
            <button class="ghost-button" type="button" id="btn-refresh-monitoring">🔄 Refresh</button>
        </div>
        <div class="data-table">
            ${data.map((row) => {
                const progress = row.total_questions > 0 
                    ? Math.round((row.answered_count / row.total_questions) * 100) 
                    : 0;
                const remainMin = Math.floor(row.remaining_seconds / 60);
                const remainSec = row.remaining_seconds % 60;
                const timeStr = `${String(remainMin).padStart(2, '0')}:${String(remainSec).padStart(2, '0')}`;
                const timeClass = row.remaining_seconds < 60 ? 'time-critical' : (row.remaining_seconds < 300 ? 'time-warn' : '');
                
                return `
                <div class="data-row monitoring-row">
                    <div>
                        <strong>${escapeHtml(row.peserta?.nama_lengkap || '-')}</strong>
                        <span>${escapeHtml(row.peserta?.nomor_peserta || '-')} - ${escapeHtml(row.jadwal?.nama_tes || '-')}</span>
                    </div>
                    <div class="monitoring-progress">
                        <div class="progress-bar-container">
                            <div class="progress-bar-fill" style="width: ${progress}%"></div>
                        </div>
                        <span class="progress-label">${escapeHtml(row.answered_count)}/${escapeHtml(row.total_questions)} soal</span>
                    </div>
                    <span class="monitoring-timer ${timeClass}">${timeStr}</span>
                </div>`;
            }).join('')}
        </div>`;

    const refreshBtn = document.getElementById('btn-refresh-monitoring');
    if (refreshBtn) {
        refreshBtn.addEventListener('click', async () => {
            try {
                const data = await apiRequest('/admin/monitoring');
                renderMonitoring(data);
            } catch (e) {
                showError(e);
            }
        });
    }
}

function collectForm(form) {
    const data = Object.fromEntries(new FormData(form).entries());
    Object.keys(data).forEach((key) => {
        if (data[key] === '') {
            data[key] = null;
        }
    });
    return data;
}

async function handleAdminSubmit(form) {
    const type = form.dataset.form;
    const id = form.dataset.id;
    const data = collectForm(form);
    const config = {
        participant: ['/admin/participants', loadParticipants, renderParticipants],
        category: ['/admin/categories', loadCategories, renderCategories],
        question: ['/admin/questions', loadQuestions, renderQuestions],
        schedule: ['/admin/schedules', loadSchedules, renderSchedules],
    }[type];

    if (!config) return;

    const [path, loader, renderer] = config;
    await apiRequest(id ? `${path}/${id}` : path, {
        method: id ? 'PUT' : 'POST',
        body: JSON.stringify(data),
    });

    await loader();
    await loadStats().catch(() => {});
    renderMetrics();
    renderer();
}

async function handleAdminDelete(type, id) {
    const config = {
        participants: ['/admin/participants', loadParticipants, renderParticipants],
        categories: ['/admin/categories', loadCategories, renderCategories],
        'question-bank': ['/admin/questions', loadQuestions, renderQuestions],
        schedule: ['/admin/schedules', loadSchedules, renderSchedules],
    }[type] || ['/admin/categories', loadCategories, renderCategories];

    if (!window.confirm('Hapus data ini?')) return;

    const [path, loader, renderer] = config;
    await apiRequest(`${path}/${id}`, { method: 'DELETE' });
    await loader();
    await loadStats().catch(() => {});
    renderMetrics();
    renderer();
}

async function setActiveView(name) {
    state.currentView = name;
    const title = adminViews[name] || 'Dashboard CBT';
    els.pageTitle.textContent = title;
    els.navItems.forEach((item) => item.classList.toggle('active', item.dataset.view === name));

    if (name !== 'monitoring' && state.monitoringInterval) {
        clearInterval(state.monitoringInterval);
        state.monitoringInterval = null;
    }

    try {
        setLoading();
        if (name === 'overview') {
            await loadStats();
            renderMetrics();
            renderAdminOverview();
        } else if (name === 'participants') {
            await loadParticipants();
            renderParticipants();
        } else if (name === 'categories') {
            await loadCategories();
            renderCategories();
        } else if (name === 'question-bank') {
            await loadQuestions();
            renderQuestions();
        } else if (name === 'schedule') {
            await loadSchedules();
            renderSchedules();
        } else if (name === 'monitoring') {
            const data = await apiRequest('/admin/monitoring');
            renderMonitoring(data);
            state.monitoringInterval = setInterval(async () => {
                try {
                    const freshData = await apiRequest('/admin/monitoring');
                    if (state.currentView === 'monitoring') {
                        renderMonitoring(freshData);
                    }
                } catch (e) {}
            }, 15000);
        } else if (name === 'reports') {
            await loadReports();
            renderReports();
        }
    } catch (error) {
        showError(error);
    }
}

async function renderApp() {
    els.sessionChip.textContent = `${userName()} (Admin)`;
    els.roleBadge.textContent = 'Admin';
    renderTaskList();
    await loadStats().catch(() => {});
    renderMetrics();
    await setActiveView('overview');
}

// Modal Closers
if (btnCloseParticipantsModal) {
    btnCloseParticipantsModal.addEventListener('click', () => {
        modalParticipants.hidden = true;
    });
}
if (btnCloseImportSoalModal) {
    btnCloseImportSoalModal.addEventListener('click', () => {
        modalImportSoal.hidden = true;
        modalImportFileInput.value = '';
        importFileNameLabel.textContent = '';
    });
}
if (btnCloseImportPesertaModal) {
    btnCloseImportPesertaModal.addEventListener('click', () => {
        modalImportPeserta.hidden = true;
        modalImportPesertaFileInput.value = '';
        importPesertaFileNameLabel.textContent = '';
    });
}

// File Updates
if (modalImportFileInput) {
    modalImportFileInput.addEventListener('change', (e) => {
        const file = e.target.files[0];
        if (file) {
            importFileNameLabel.textContent = `Berkas terpilih: ${file.name}`;
        }
    });
}
if (modalImportPesertaFileInput) {
    modalImportPesertaFileInput.addEventListener('change', (e) => {
        const file = e.target.files[0];
        if (file) {
            importPesertaFileNameLabel.textContent = `Berkas terpilih: ${file.name}`;
        }
    });
}

// Imports
if (modalImportSoalForm) {
    modalImportSoalForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const submitBtn = modalImportSoalForm.querySelector('button[type="submit"]');
        submitBtn.disabled = true;
        submitBtn.textContent = 'Mengimpor...';

        try {
            const formData = new FormData();
            formData.append('file', modalImportFileInput.files[0]);

            const response = await fetch(`${apiBase}/admin/questions/import`, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Authorization': `Bearer ${state.token}`
                },
                body: formData
            });

            const payload = await response.json();
            if (!response.ok) {
                throw new Error(payload.message || 'Gagal mengimpor berkas CSV.');
            }

            window.alert(payload.message || 'Soal berhasil diimpor!');
            modalImportSoal.hidden = true;
            modalImportFileInput.value = '';
            importFileNameLabel.textContent = '';
            await loadQuestions();
            renderQuestions();
        } catch (error) {
            window.alert(error.message);
        } finally {
            submitBtn.disabled = false;
            submitBtn.textContent = 'Mulai Import Soal';
        }
    });
}

if (modalImportPesertaForm) {
    modalImportPesertaForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const submitBtn = modalImportPesertaForm.querySelector('button[type="submit"]');
        submitBtn.disabled = true;
        submitBtn.textContent = 'Mengimpor...';

        try {
            const formData = new FormData();
            formData.append('file', modalImportPesertaFileInput.files[0]);

            const response = await fetch(`${apiBase}/admin/participants/import`, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Authorization': `Bearer ${state.token}`
                },
                body: formData
            });

            const payload = await response.json();
            if (!response.ok) {
                throw new Error(payload.message || 'Gagal mengimpor berkas CSV.');
            }

            window.alert(payload.message || 'Peserta berhasil diimpor!');
            modalImportPeserta.hidden = true;
            modalImportPesertaFileInput.value = '';
            importPesertaFileNameLabel.textContent = '';
            await loadParticipants();
            renderParticipants();
        } catch (error) {
            window.alert(error.message);
        } finally {
            submitBtn.disabled = false;
            submitBtn.textContent = 'Mulai Import Peserta';
        }
    });
}

// Participants Modal Loader
async function openParticipantsModal(scheduleId, scheduleName) {
    currentScheduleId = scheduleId;
    modalTargetScheduleName.textContent = scheduleName;
    modalParticipants.hidden = false;
    modalSelectParticipant.innerHTML = '<option value="">Memuat data...</option>';
    modalAssignedParticipantsList.innerHTML = '<tr><td colspan="4" class="center">Memuat data...</td></tr>';

    try {
        const response = await apiRequest(`/admin/schedules/${scheduleId}/participants`);
        
        if (response.available && response.available.length > 0) {
            modalSelectParticipant.innerHTML = '<option value="">Pilih peserta...</option>' + 
                response.available.map(p => `<option value="${p.id_peserta}">${escapeHtml(p.nama_lengkap)} (${escapeHtml(p.nomor_peserta)})</option>`).join('');
        } else {
            modalSelectParticipant.innerHTML = '<option value="">Tidak ada peserta aktif tersedia</option>';
        }

        if (response.assigned && response.assigned.length > 0) {
            modalAssignedParticipantsList.innerHTML = response.assigned.map(p => `
                <tr>
                    <td>${escapeHtml(p.nomor_peserta)}</td>
                    <td><strong>${escapeHtml(p.nama_lengkap)}</strong></td>
                    <td><span class="status-pill ${p.status_tes === 'selesai' || p.status_tes === 'sedang_tes' ? 'ok' : ''}">${escapeHtml(p.status_tes)}</span></td>
                    <td style="text-align: center;">
                        <button type="button" class="icon-button danger btn-delete-participant" data-id="${p.id_peserta}" ${p.status_tes !== 'belum_mulai' ? 'disabled' : ''}>Hapus</button>
                    </td>
                </tr>
            `).join('');

            modalAssignedParticipantsList.querySelectorAll('.btn-delete-participant').forEach(btn => {
                btn.addEventListener('click', async () => {
                    const pesertaId = btn.dataset.id;
                    if (window.confirm('Hapus peserta ini dari jadwal ujian?')) {
                        try {
                            await apiRequest(`/admin/schedules/${currentScheduleId}/participants/${pesertaId}`, { method: 'DELETE' });
                            openParticipantsModal(currentScheduleId, scheduleName);
                        } catch (err) {
                            window.alert(err.message);
                        }
                    }
                });
            });
        } else {
            modalAssignedParticipantsList.innerHTML = '<tr><td colspan="4" class="center">Belum ada peserta terdaftar.</td></tr>';
        }
    } catch (error) {
        window.alert('Gagal memuat peserta: ' + error.message);
        modalParticipants.hidden = true;
    }
}

if (modalAddParticipantForm) {
    modalAddParticipantForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const pesertaId = modalSelectParticipant.value;
        if (!pesertaId || !currentScheduleId) return;

        const submitBtn = modalAddParticipantForm.querySelector('button[type="submit"]');
        submitBtn.disabled = true;

        try {
            await apiRequest(`/admin/schedules/${currentScheduleId}/participants`, {
                method: 'POST',
                body: JSON.stringify({ id_peserta: pesertaId })
            });
            openParticipantsModal(currentScheduleId, modalTargetScheduleName.textContent);
        } catch (error) {
            window.alert(error.message);
        } finally {
            submitBtn.disabled = false;
        }
    });
}

const btnRegisterAllParticipants = document.getElementById('btn-register-all-participants');
if (btnRegisterAllParticipants) {
    btnRegisterAllParticipants.addEventListener('click', async () => {
        if (!currentScheduleId) return;
        if (!window.confirm('Daftarkan semua peserta aktif ke jadwal ujian ini?')) return;

        btnRegisterAllParticipants.disabled = true;
        btnRegisterAllParticipants.textContent = 'Mendaftarkan...';

        try {
            const res = await apiRequest(`/admin/schedules/${currentScheduleId}/participants/all`, {
                method: 'POST'
            });
            window.alert(res.message);
            openParticipantsModal(currentScheduleId, modalTargetScheduleName.textContent);
        } catch (error) {
            window.alert(error.message);
        } finally {
            btnRegisterAllParticipants.disabled = false;
            btnRegisterAllParticipants.textContent = '👥 Daftarkan Semua Peserta';
        }
    });
}

async function downloadReportCsv(idPesertaTes) {
    try {
        const response = await fetch(`${apiBase}/admin/reports/${idPesertaTes}/export-csv`, {
            headers: { 'Authorization': `Bearer ${state.token}` }
        });
        if (!response.ok) throw new Error('Gagal mengunduh berkas CSV.');
        const blob = await response.blob();
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `rapor_cbt_${idPesertaTes}.csv`;
        document.body.appendChild(a);
        a.click();
        a.remove();
        window.URL.revokeObjectURL(url);
    } catch (e) {
        window.alert(e.message);
    }
}

// Global Triggers in content
els.workContent.addEventListener('click', async (event) => {
    const parBtn = event.target.closest('.btn-trigger-participants');
    if (parBtn) {
        openParticipantsModal(parBtn.dataset.id, parBtn.dataset.name);
        return;
    }

    const impBtn = event.target.closest('#btn-trigger-import-soal');
    if (impBtn) {
        modalImportSoal.hidden = false;
        return;
    }

    const impPesertaBtn = event.target.closest('#btn-trigger-import-peserta');
    if (impPesertaBtn) {
        modalImportPeserta.hidden = false;
        return;
    }

    const expBtn = event.target.closest('.btn-export-csv');
    if (expBtn) {
        downloadReportCsv(expBtn.dataset.id);
        return;
    }
});

// Admin Global submit
els.workContent.addEventListener('submit', async (event) => {
    const form = event.target.closest('.admin-form');
    if (!form) return;
    event.preventDefault();
    const button = form.querySelector('button[type="submit"]');
    button.disabled = true;
    button.textContent = 'Menyimpan...';

    try {
        await handleAdminSubmit(form);
    } catch (error) {
        window.alert(error.message);
        button.disabled = false;
        button.textContent = form.dataset.id ? 'Simpan Perubahan' : 'Tambah Data';
    }
});

// Admin Global action button clicks
els.workContent.addEventListener('click', async (event) => {
    const button = event.target.closest('button[data-action]');
    if (!button) return;
    const action = button.dataset.action;
    const id = button.dataset.id;

    if (action === 'cancel') {
        await setActiveView(state.currentView);
        return;
    }
    if (action === 'edit') {
        if (state.currentView === 'participants') renderParticipants(id);
        if (state.currentView === 'categories') renderCategories(id);
        if (state.currentView === 'question-bank') renderQuestions(id);
        if (state.currentView === 'schedule') renderSchedules(id);
    }
    if (action === 'delete') {
        try {
            await handleAdminDelete(state.currentView, id);
        } catch (error) {
            window.alert(error.message);
        }
    }
});

// Navigation Click listeners
els.navItems.forEach((item) => {
    item.addEventListener('click', () => setActiveView(item.dataset.view));
});

// Logout listener
els.logoutButton.addEventListener('click', logout);

// Run App on Load
renderApp();
