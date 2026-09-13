<?php
// ============================================================
// API: Autentikasi (Login, Register, Logout, Check)
// ============================================================

require_once __DIR__ . '/auth_helper.php';

header('Content-Type: application/json; charset=utf-8');
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if ($origin) {
    header('Access-Control-Allow-Origin: ' . $origin);
    header('Vary: Origin');
} else {
    header('Access-Control-Allow-Origin: *');
}
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Credentials: true');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit;

startSecureSession();

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'register':
        handleRegister();
        break;
    case 'login':
        handleLogin();
        break;
    case 'firebase_session':
        handleFirebaseSession();
        break;
    case 'logout':
        handleLogout();
        break;
    case 'check':
        handleCheck();
        break;
    default:
        jsonResponse(false, 'Aksi tidak dikenali.', null, 400);
}

// -------- Register --------
function handleRegister(): void {
    $body = json_decode(file_get_contents('php://input'), true);
    $name  = trim($body['name']  ?? '');
    $email = trim($body['email'] ?? '');
    $pass  = $body['password']   ?? '';

    if (!$name || !$email || !$pass) {
        jsonResponse(false, 'Semua field wajib diisi.', null, 400);
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        jsonResponse(false, 'Format email tidak valid.', null, 400);
    }
    if (strlen($pass) < 6) {
        jsonResponse(false, 'Password minimal 6 karakter.', null, 400);
    }

    $db   = getDB();
    $stmt = $db->prepare('SELECT id FROM users WHERE email = ?');
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        jsonResponse(false, 'Email sudah terdaftar.', null, 409);
    }

    $hash = password_hash($pass, PASSWORD_BCRYPT, ['cost' => 12]);
    $ins  = $db->prepare('INSERT INTO users (name, email, password) VALUES (?, ?, ?)');
    $ins->execute([$name, $email, $hash]);

    jsonResponse(true, 'Registrasi berhasil! Silakan login.');
}

// -------- Login --------
function handleLogin(): void {
    $body  = json_decode(file_get_contents('php://input'), true);
    $email = trim($body['email']    ?? '');
    $pass  = $body['password']      ?? '';

    if (!$email || !$pass) {
        jsonResponse(false, 'Email dan password wajib diisi.', null, 400);
    }

    $db   = getDB();
    $stmt = $db->prepare('SELECT id, name, email, password FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($pass, $user['password'])) {
        jsonResponse(false, 'Email atau password salah.', null, 401);
    }

    session_regenerate_id(true);
    $_SESSION['user_id']   = $user['id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['user_email']= $user['email'];

    jsonResponse(true, 'Login berhasil!', [
        'name'  => $user['name'],
        'email' => $user['email'],
    ]);
}

// -------- Firebase Session Sync --------
function handleFirebaseSession(): void {
    $body = json_decode(file_get_contents('php://input'), true);
    $idToken = trim($body['idToken'] ?? '');

    if (!$idToken) {
        jsonResponse(false, 'Data Firebase tidak lengkap.', null, 400);
    }

    $firebaseUser = verifyFirebaseIdToken($idToken);
    if (!$firebaseUser) {
        jsonResponse(false, 'Token Firebase tidak valid.', null, 401);
    }

    $email = trim($firebaseUser['email'] ?? '');
    $name = trim($firebaseUser['displayName'] ?? '');

    if (!$email) {
        jsonResponse(false, 'Email Firebase tidak ditemukan.', null, 400);
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        jsonResponse(false, 'Format email Firebase tidak valid.', null, 400);
    }

    $db = getDB();
    $stmt = $db->prepare('SELECT id, name, email FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user) {
        $displayName = $name ?: explode('@', $email)[0];
        $randomPassword = password_hash(bin2hex(random_bytes(32)), PASSWORD_BCRYPT, ['cost' => 12]);
        $ins = $db->prepare('INSERT INTO users (name, email, password) VALUES (?, ?, ?)');
        $ins->execute([$displayName, $email, $randomPassword]);

        $user = [
            'id' => $db->lastInsertId(),
            'name' => $displayName,
            'email' => $email,
        ];
    }

    session_regenerate_id(true);
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['user_email'] = $user['email'];

    jsonResponse(true, 'Login Firebase berhasil!', [
        'name' => $user['name'],
        'email' => $user['email'],
    ]);
}

function verifyFirebaseIdToken(string $idToken): ?array {
    $apiKey = '//APIKEY';
    $url = '' . urlencode($apiKey);
    $payload = json_encode(['idToken' => $idToken]);

    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_TIMEOUT => 10,
        ]);
        $response = curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
    } else {
        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => "Content-Type: application/json\r\n",
                'content' => $payload,
                'timeout' => 10,
            ],
        ]);
        $response = @file_get_contents($url, false, $context);
        $status = $response === false ? 0 : 200;
    }

    if ($status < 200 || $status >= 300 || !$response) {
        return null;
    }

    $data = json_decode($response, true);
    return $data['users'][0] ?? null;
}

// -------- Logout --------
function handleLogout(): void {
    session_unset();
    session_destroy();
    jsonResponse(true, 'Logout berhasil.');
}

// -------- Check Session --------
function handleCheck(): void {
    if (isLoggedIn()) {
        jsonResponse(true, 'Authenticated', [
            'name'  => $_SESSION['user_name']  ?? '',
            'email' => $_SESSION['user_email'] ?? '',
        ]);
    } else {
        jsonResponse(false, 'Not authenticated', null, 401);
    }
}
