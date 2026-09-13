<?php
// ============================================================
// Helper: Autentikasi & Session
// ============================================================

require_once __DIR__ . '/config.php';

function startSecureSession(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_name('FINANCE_APP_SESSION');
        session_set_cookie_params([
            'lifetime' => 86400,
            'path'     => '/',
            'secure'   => true,  // set true jika pakai HTTPS
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        session_start();
    }
}

function isLoggedIn(): bool {
    startSecureSession();
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function requireLogin(): void {
    if (!isLoggedIn()) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Unauthorized. Silakan login terlebih dahulu.']);
        exit;
    }
}

function getCurrentUserId(): int {
    startSecureSession();
    return (int)($_SESSION['user_id'] ?? 0);
}

function jsonResponse(bool $success, string $message, $data = null, int $code = 200): void {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    $response = ['success' => $success, 'message' => $message];
    if ($data !== null) $response['data'] = $data;
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
}

function sanitize(string $input): string {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}