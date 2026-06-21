<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Masuk ke portal CBT Web untuk memulai ujian online komputer atau mengelola sistem ujian bagi administrator.">
    <title>{{ config('app.name', 'CBT Web') }} - Masuk ke Sistem</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/images/kiyoraka.png') }}">
    
    <!-- Google Fonts for Premium Aesthetics -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="{{ asset('css/cbt.css') }}?v=1.0.2">
</head>
<body class="login-mode" data-api-base="{{ url('/api') }}">
    <div class="app-shell login-mode">
        <div class="login-layout-centered">
            <div class="login-card-box">
                <div class="login-card">
                    <!-- Brand / Logo -->
                    <div class="login-brand-logo">
                        <img src="{{ asset('assets/images/kiyoraka.png') }}" alt="Logo CBT" style="height: 50px; width: auto;">
                    </div>

                    
                    <div class="login-header" style="text-align: center;">
                        <h1 style="font-size: 26px; font-weight: 800; margin: 0 0 8px; font-family: 'Outfit', sans-serif;">CBT Web Portal</h1>
                        <p>Masuk untuk memulai ujian</p>
                    </div>

                    <!-- Form -->
                    <form id="login-form">
                        <div style="display: flex; flex-direction: column; gap: 20px;">
                            <div class="input-group">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2px;">
                                    <label id="identity-label" for="identity">Nomor Peserta</label>
                                    <span id="role-badge" class="panel-badge" style="display: none; padding: 2px 10px; font-size: 11px; height: auto;">Peserta</span>
                                </div>
                                <div class="input-wrapper">
                                    <span class="input-icon" id="identity-icon">👤</span>
                                    <input type="text" id="identity" name="identity" placeholder="Masukan Credential dengan benar" required autocomplete="username">
                                </div>
                                <span id="identity-hint" style="font-size: 11px; color: var(--muted); margin-top: -4px;">Sistem akan mendeteksi peran Anda secara otomatis</span>
                            </div>

                            <div class="input-group">
                                <label for="password">Kata Sandi</label>
                                <div class="input-wrapper">
                                    <span class="input-icon">🔒</span>
                                    <input type="password" id="password" name="password" placeholder="••••••••" required autocomplete="current-password">
                                </div>
                            </div>

                            <p class="form-message" id="form-message"></p>

                            <button type="submit" class="primary-button" id="login-button">login</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="{{ asset('js/login.js') }}?v=1.0.2" defer></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const identityInput = document.getElementById('identity');
            const identityIcon = document.getElementById('identity-icon');
            const roleBadge = document.getElementById('role-badge');

            if (identityInput) {
                const detectRole = () => {
                    const value = identityInput.value.trim();
                    if (!value) {
                        roleBadge.style.display = 'none';
                        identityIcon.textContent = '👤';
                        return;
                    }

                    // Check if it matches Nomor Peserta (starts with P/p followed by numbers)
                    if (/^[pP]\d+$/i.test(value)) {
                        roleBadge.textContent = '👤 Peserta';
                        roleBadge.style.display = 'inline-block';
                        roleBadge.style.background = 'var(--accent-glow)';
                        roleBadge.style.color = '#34d399';
                        roleBadge.style.borderColor = 'rgba(16, 185, 129, 0.3)';
                        identityIcon.textContent = '👤';
                    } else {
                        roleBadge.textContent = '🛡️ Administrator';
                        roleBadge.style.display = 'inline-block';
                        roleBadge.style.background = 'var(--primary-glow)';
                        roleBadge.style.color = '#a5b4fc';
                        roleBadge.style.borderColor = 'rgba(99, 102, 241, 0.3)';
                        identityIcon.textContent = '🛡️';
                    }
                };

                // Detect on page load (in case of autofill)
                setTimeout(detectRole, 100);
                // Detect on user input
                identityInput.addEventListener('input', detectRole);
            }
        });
    </script>
</body>
</html>
