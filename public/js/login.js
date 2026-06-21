const state = {
    token: localStorage.getItem('cbt_token'),
    type: localStorage.getItem('cbt_type'),
    user: JSON.parse(localStorage.getItem('cbt_user') || 'null'),
};

const els = {
    loginForm: document.getElementById('login-form'),
    loginButton: document.getElementById('login-button'),
    formMessage: document.getElementById('form-message'),
};

const apiBase = document.body.dataset.apiBase || '/api';

function persistSession(payload) {
    state.token = payload.token;
    state.type = payload.type;
    state.user = payload.user;
    localStorage.setItem('cbt_token', payload.token);
    localStorage.setItem('cbt_type', payload.type);
    localStorage.setItem('cbt_user', JSON.stringify(payload.user));
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

async function login(identity, password) {
    return apiRequest('/login', {
        method: 'POST',
        body: JSON.stringify({ identity, password }),
    });
}

// Redirect immediately if already logged in
if (state.token && state.user) {
    window.location.href = state.type === 'admin' ? '/admin' : '/peserta';
} else {
    // Attach submit listener
    if (els.loginForm) {
        els.loginForm.addEventListener('submit', async (event) => {
            event.preventDefault();
            els.formMessage.textContent = '';
            els.loginButton.disabled = true;
            els.loginButton.textContent = 'Memproses...';

            try {
                const form = new FormData(els.loginForm);
                const payload = await login(form.get('identity'), form.get('password'));
                persistSession(payload);
                els.loginForm.reset();
                window.location.href = payload.type === 'admin' ? '/admin' : '/peserta';
            } catch (error) {
                els.formMessage.textContent = error.message;
            } finally {
                els.loginButton.disabled = false;
                els.loginButton.textContent = 'Masuk ke Sistem';
            }
        });
    }
}
