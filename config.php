<?php
// Load environment variables from .env file if exists
if (file_exists(__DIR__ . '/.env')) {
    $env_lines = file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($env_lines as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            [$key, $value] = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            if (!isset($_ENV[$key])) {
                $_ENV[$key] = $value;
            }
        }
    }
}

// Konfigurasi Database - Baca dari environment variables atau gunakan default
define('DB_HOST', $_ENV['DB_HOST'] ?? $_SERVER['DB_HOST'] ?? 'localhost');
define('DB_USER', $_ENV['DB_USER'] ?? $_SERVER['DB_USER'] ?? 'root');
define('DB_PASS', $_ENV['DB_PASS'] ?? $_SERVER['DB_PASS'] ?? '');
define('DB_NAME', $_ENV['DB_NAME'] ?? $_SERVER['DB_NAME'] ?? 'cbt_lpk');

// Koneksi Database
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

// Set timezone
date_default_timezone_set('Asia/Jakarta');

// Session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Base URL - Baca dari environment variables atau auto-generate


$base_url = $_ENV['BASE_URL'] ?? $_SERVER['BASE_URL'] ?? null;
if (!$base_url) {
    // Paksa selalu http untuk lingkungan lokal/development
    $protocol = 'http://';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    
    // Metode paling reliable: gunakan SCRIPT_NAME dari browser
    // SCRIPT_NAME adalah relative path yang dipassing browser (e.g., /CBT-Web/login.php)
    $script_name = $_SERVER['SCRIPT_NAME'] ?? '';
    
    // Ambil directory dari SCRIPT_NAME, bukan dari filesystem __DIR__
    // dirname('/CBT-Web/login.php') = '/CBT-Web'
    $relative_path = rtrim(str_replace('\\', '/', dirname($script_name)), '/');
    
    // Jika relative_path kosong atau '/', gunakan root
    if ($relative_path === '' || $relative_path === '/') {
        $relative_path = '';
    }
    
    // Build final BASE_URL
    $base_url = $protocol . $host . $relative_path . '/';
}
define('BASE_URL', $base_url);

// Login security settings
define('MAX_LOGIN_ATTEMPTS', 3);
define('LOCKOUT_MINUTES', 5); // pending/lock duration after too many attempts

// Helper Functions
function redirect($url) {
    // Normalize URL
    $url = trim($url);
    
    // Debug log
    error_log("Redirect called with URL: " . $url);
    error_log("BASE_URL: " . BASE_URL);
    
    // Jika $url sudah mengandung http/https, redirect langsung
    if (preg_match('/^https?:\/\//i', $url)) {
        header("Location: $url");
        exit;
    }
    
    // Handle relative path: ../login.php, login.php, /login.php
    $url = ltrim($url, '/');
    
    // Ganti ../ dengan empty untuk handle parent directory
    $url = str_replace('../', '', $url);
    
    // Build final redirect URL
    $final_url = rtrim(BASE_URL, '/') . '/' . $url;
    
    error_log("Final redirect URL: " . $final_url);
    
    // Make sure no output was sent
    if (headers_sent($file, $line)) {
        error_log("ERROR: Headers already sent in $file on line $line");
        echo "<script>window.location.href = '" . addslashes($final_url) . "';</script>";
        exit;
    }
    
    header("Location: " . $final_url);
    exit;
}
   
function alert($message, $type = 'success') {
    $_SESSION['alert'] = [
        'message' => $message,
        'type' => $type
    ];
}

function show_alert() {
    if (isset($_SESSION['alert'])) {
        $alert = $_SESSION['alert'];
        echo "<div class='alert alert-{$alert['type']} alert-dismissible fade show' role='alert'>
                {$alert['message']}
                <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
              </div>";
        unset($_SESSION['alert']);
    }
}

function check_login_admin() {
    if (!isset($_SESSION['admin_id'])) {
        redirect('login.php');
    }
}

function check_login_peserta() {
    if (!isset($_SESSION['peserta_id'])) {
        redirect('login.php');
    }
}

function format_tanggal($tanggal) {
    $bulan = [
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    $split = explode('-', date('Y-n-j', strtotime($tanggal)));
    return $split[2] . ' ' . $bulan[(int)$split[1]] . ' ' . $split[0];
}

function time_elapsed($datetime) {
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);
    
    if ($diff->y > 0) return $diff->y . ' tahun lalu';
    if ($diff->m > 0) return $diff->m . ' bulan lalu';
    if ($diff->d > 0) return $diff->d . ' hari lalu';
    if ($diff->h > 0) return $diff->h . ' jam lalu';
    if ($diff->i > 0) return $diff->i . ' menit lalu';
    return 'Baru saja';
}
?>
