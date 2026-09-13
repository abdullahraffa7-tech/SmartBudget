<?php
// ============================================================
// API: Upload struk + OCR Gemini via PHP cURL
// Tidak memakai shell_exec / Python.
// ============================================================

require_once __DIR__ . '/auth_helper.php';
require_once __DIR__ . '/gemini.php';

header('Content-Type: application/json; charset=utf-8');
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if ($origin) {
    header('Access-Control-Allow-Origin: ' . $origin);
    header('Vary: Origin');
} else {
    header('Access-Control-Allow-Origin: *');
}
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Credentials: true');

requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'status' => 'error',
        'message' => 'Method tidak valid',
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$file = $_FILES['image'] ?? null;

if (!$file || !is_uploaded_file($file['tmp_name'])) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'File gambar tidak ditemukan',
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'Upload gagal. Kode error: ' . (int)$file['error'],
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$allowedMime = ['image/jpeg', 'image/png', 'image/webp'];
$mimeType = geminiMimeType($file['tmp_name']);

if (!in_array($mimeType, $allowedMime, true)) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'Format gambar harus JPG, PNG, atau WEBP.',
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$uploadDir = realpath(__DIR__ . '/../uploads');
if ($uploadDir === false) {
    $targetDir = __DIR__ . '/../uploads';
    if (!mkdir($targetDir, 0775, true) && !is_dir($targetDir)) {
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => 'Folder upload gagal dibuat',
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    $uploadDir = realpath($targetDir);
}

$extension = imageExtensionFromMime($mimeType);
$safeName = preg_replace('/[^A-Za-z0-9._-]/', '_', pathinfo($file['name'], PATHINFO_FILENAME));
$fileName = time() . '_' . bin2hex(random_bytes(4)) . '_' . $safeName . '.' . $extension;
$serverPath = $uploadDir . DIRECTORY_SEPARATOR . $fileName;
$publicPath = 'uploads/' . $fileName;

if (!move_uploaded_file($file['tmp_name'], $serverPath)) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Upload gagal',
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $data = geminiAnalyzeReceipt($serverPath, $mimeType);

    if (($data['total'] ?? 0) <= 0) {
        throw new RuntimeException('Total pembayaran tidak terbaca dari gambar.');
    }

    echo json_encode([
        'status' => 'success',
        'data' => $data,
        'image_path' => $publicPath,
    ], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'AI gagal membaca struk: ' . $e->getMessage(),
    ], JSON_UNESCAPED_UNICODE);
}

function imageExtensionFromMime(string $mimeType): string {
    switch ($mimeType) {
        case 'image/png':
            return 'png';
        case 'image/webp':
            return 'webp';
        default:
            return 'jpg';
    }
}
