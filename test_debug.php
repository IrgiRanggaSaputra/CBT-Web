<?php
// Load environment variables dari .env
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

echo "=== DEBUG BASE_URL CALCULATION ===<br><br>";
echo "1. Environment Variables from .env:<br>";
echo "   DB_HOST: " . ($_ENV['DB_HOST'] ?? 'NOT SET') . "<br>";
echo "   BASE_URL: " . ($_ENV['BASE_URL'] ?? 'NOT SET') . "<br><br>";

echo "2. Server Variables:<br>";
echo "   SCRIPT_NAME: " . $_SERVER['SCRIPT_NAME'] . "<br>";
echo "   HTTP_HOST: " . $_SERVER['HTTP_HOST'] . "<br>";
echo "   HTTPS: " . ($_SERVER['HTTPS'] ?? 'not set') . "<br>";
echo "   HTTP_X_FORWARDED_PROTO: " . ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? 'not set') . "<br><br>";

echo "3. Calculated BASE_URL:<br>";
$base_url = $_ENV['BASE_URL'] ?? null;
if (!$base_url) {
    $is_production = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || 
                     (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
    $protocol = $is_production ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    
    $script_name = $_SERVER['SCRIPT_NAME'] ?? '';
    $path_parts = explode('/', trim($script_name, '/'));
    $base_path = '';
    
    foreach ($path_parts as $i => $part) {
        if (in_array($part, ['admin', 'peserta', 'api'])) {
            $base_path = '/' . implode('/', array_slice($path_parts, 0, $i));
            break;
        }
    }
    
    if (empty($base_path) || $base_path === '/') {
        $base_path = '';
    }
    
    $base_url = $protocol . $host . $base_path . '/';
}

echo "   Final BASE_URL: " . $base_url . "<br><br>";
echo "Status: " . ($base_url ? "✓ OK" : "✗ ERROR");
?>
