<?php
// ============================================================
// Konfigurasi Database
// Sesuaikan dengan setting XAMPP Anda
// ============================================================

define('DB_HOST',   'localhost');
define('DB_USER',   '');
define('DB_PASS',   '');
define('DB_NAME',   '');
define('DB_CHARSET','utf8mb4');

// ============================================================
// Konfigurasi Gemini AI
// Urutan baca key:
// 1. Taruh file di tempat yang di inginkan
// 2. Contoh File yang sudah di berada di tempat amang: /home/rahasia/rahasia1/gemini_keys.php
// 3. Kosong, agar aplikasi memberi error jelas jika belum dikonfigurasi
// ============================================================
if (is_file('/home/smartbud/rahasia/gemini_keys.php')) {
    require_once '/home/smartbud/rahasia/gemini_keys.php';
}

if (!defined('GEMINI_CHAT_API_KEY')) {
    define('GEMINI_CHAT_API_KEY', getenv('GEMINI_CHAT_API_KEY') ?: '');
}

if (!defined('GEMINI_VISION_API_KEY')) {
    define('GEMINI_VISION_API_KEY', getenv('GEMINI_VISION_API_KEY') ?: '');
}

if (!defined('GEMINI_API_KEY')) {
    define('GEMINI_API_KEY', getenv('GEMINI_API_KEY') ?: GEMINI_CHAT_API_KEY);
}

define('GEMINI_TEXT_MODEL', getenv('GEMINI_TEXT_MODEL') ?: 'gemini-2.5-flash');
define('GEMINI_VISION_MODEL', getenv('GEMINI_VISION_MODEL') ?: 'gemini-2.5-flash-lite');

// ============================================================
// Koneksi PDO (lebih aman dari MySQLi)
// ============================================================
function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            http_response_code(500);
            die(json_encode([
                'success' => false,
                'message' => 'Koneksi database gagal: ' . $e->getMessage()
            ]));
        }
    }
    return $pdo;
}
