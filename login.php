<?php
require_once __DIR__ . '/config.php';

if (isset($_SESSION['admin_id'])) {
    redirect('admin/dashboard.php');
}
if (isset($_SESSION['peserta_id'])) {
    redirect('peserta/dashboard.php');
}

$error = '';

// Ensure login attempts table exists
mysqli_query($conn, "CREATE TABLE IF NOT EXISTS login_attempts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    identity VARCHAR(100) NOT NULL UNIQUE,
    attempts INT NOT NULL DEFAULT 0,
    last_attempt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    locked_until DATETIME DEFAULT NULL
)");

function get_lock_info($conn, $identity) {
    $info = ['attempts' => 0, 'locked_until' => null];
    if ($stmt = mysqli_prepare($conn, 'SELECT attempts, locked_until FROM login_attempts WHERE identity = ?')) {
        mysqli_stmt_bind_param($stmt, 's', $identity);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        if ($row = mysqli_fetch_assoc($res)) {
            $info['attempts'] = (int)$row['attempts'];
            $info['locked_until'] = $row['locked_until'];
        }
        mysqli_stmt_close($stmt);
    }
    return $info;
}

function record_failed_attempt($conn, $identity) {
    $info = get_lock_info($conn, $identity);
    $attempts = $info['attempts'] + 1;
    if ($attempts >= MAX_LOGIN_ATTEMPTS) {
        // lock account for LOCKOUT_MINUTES, reset attempts to 0
        $lockedUntil = date('Y-m-d H:i:s', time() + (LOCKOUT_MINUTES * 60));
        $stmt = mysqli_prepare($conn, 'INSERT INTO login_attempts (identity, attempts, locked_until) VALUES (?, 0, ?) ON DUPLICATE KEY UPDATE attempts = 0, locked_until = VALUES(locked_until)');
        mysqli_stmt_bind_param($stmt, 'ss', $identity, $lockedUntil);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    } else {
        $stmt = mysqli_prepare($conn, 'INSERT INTO login_attempts (identity, attempts) VALUES (?, ?) ON DUPLICATE KEY UPDATE attempts = VALUES(attempts)');
        mysqli_stmt_bind_param($stmt, 'si', $identity, $attempts);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
}

function clear_attempts($conn, $identity) {
    if ($stmt = mysqli_prepare($conn, 'DELETE FROM login_attempts WHERE identity = ?')) {
        mysqli_stmt_bind_param($stmt, 's', $identity);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identity = trim($_POST['identity'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($identity === '' || $password === '') {
        $error = 'Mohon isi username/nomor peserta dan password.';
    } else {
        // Check lock status
        $lockInfo = get_lock_info($conn, $identity);
        if (!empty($lockInfo['locked_until'])) {
            $lockedUntilTs = strtotime($lockInfo['locked_until']);
            $nowTs = time();
            if ($lockedUntilTs !== false && $lockedUntilTs > $nowTs) {
                $remaining = ceil(($lockedUntilTs - $nowTs) / 60);
                $error = 'Terlalu banyak percobaan login. Coba lagi dalam ' . $remaining . ' menit.';
            }
        }

        if ($error === '') {
        // Cek Admin terlebih dahulu (username)
        if ($stmt = mysqli_prepare($conn, 'SELECT id_admin, username, password, nama_lengkap FROM admin WHERE username = ? LIMIT 1')) {
            mysqli_stmt_bind_param($stmt, 's', $identity);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            if ($row = mysqli_fetch_assoc($result)) {
                if (password_verify($password, $row['password'])) {
                    $_SESSION['admin_id'] = $row['id_admin'];
                    $_SESSION['admin_nama'] = $row['nama_lengkap'];
                    $_SESSION['admin_username'] = $row['username'];
                    clear_attempts($conn, $identity);
                    redirect('admin/dashboard.php');
                }
            }
            mysqli_stmt_close($stmt);
        }

        // Jika bukan admin atau password admin salah, cek peserta (nomor_peserta)
        if ($stmt = mysqli_prepare($conn, "SELECT id_peserta, nomor_peserta, nama_lengkap, password FROM peserta WHERE nomor_peserta = ? AND status = 'aktif' LIMIT 1")) {
            mysqli_stmt_bind_param($stmt, 's', $identity);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            if ($row = mysqli_fetch_assoc($result)) {
                if (password_verify($password, $row['password'])) {
                    $_SESSION['peserta_id'] = $row['id_peserta'];
                    $_SESSION['peserta_nama'] = $row['nama_lengkap'];
                    $_SESSION['peserta_nomor'] = $row['nomor_peserta'];
                    clear_attempts($conn, $identity);
                    redirect('peserta/dashboard.php');
                }
            }
            mysqli_stmt_close($stmt);
        }

        // Jika sampai di sini belum redirect, berarti gagal
        record_failed_attempt($conn, $identity);
        // Re-check lock status to inform user
        $lockInfo = get_lock_info($conn, $identity);
        if (!empty($lockInfo['locked_until'])) {
            $lockedUntilTs = strtotime($lockInfo['locked_until']);
            $nowTs = time();
            if ($lockedUntilTs !== false && $lockedUntilTs > $nowTs) {
                $remaining = ceil(($lockedUntilTs - $nowTs) / 60);
                $error = 'Terlalu banyak percobaan login. Akun dikunci sementara. Coba lagi dalam ' . $remaining . ' menit.';
            } else {
                $error = 'Akun tidak ditemukan atau password salah.';
            }
        } else {
            $error = 'Akun tidak ditemukan atau password salah.';
        }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="assets/image/kiyoraka.png">
    <title>Login - CBT KIYORAKA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/auth.css">
    <style>
        .hint { font-size: .9rem; color: #6c757d; }
    </style>
    </head>
<body>
    <div class="login-box">
            <div class="login-header">
                <i class="bi bi-box-arrow-in-right"></i>
                <h2 class="mt-3">Login</h2>
            </div>

            <?php if($error): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle-fill"></i> <?php echo htmlspecialchars($error); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="mb-3">
                    <label class="form-label">Nomor Peserta</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-person-fill"></i></span>
                        <input type="text" class="form-control" name="identity" placeholder="Masukkan nomor peserta" required>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label">Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                        <input type="password" class="form-control" name="password" placeholder="Masukkan password" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary btn-login">
                    <i class="bi bi-box-arrow-in-right"></i> Login
                </button>
            </form>

            <div class="text-center mt-4">
                <a href="index.php" class="text-decoration-none"><i class="bi bi-arrow-left"></i> Kembali</a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    </body>
</html>
