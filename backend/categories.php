<?php
// ============================================================
// API: Categories
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
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Credentials: true');

requireLogin();

$userId = getCurrentUserId();
$type   = $_GET['type'] ?? '';

$db = getDB();

// Kurung wajib di kondisi OR agar AND type = ? tidak salah prioritas
if ($type === 'income' || $type === 'expense') {
    $sql    = "SELECT id, name, type, icon, color
               FROM categories
               WHERE (user_id = ? OR user_id IS NULL)
                 AND type = ?
               ORDER BY name";
    $params = [$userId, $type];
} else {
    $sql    = "SELECT id, name, type, icon, color
               FROM categories
               WHERE (user_id = ? OR user_id IS NULL)
               ORDER BY type, name";
    $params = [$userId];
}

$stmt = $db->prepare($sql);
$stmt->execute($params);

jsonResponse(true, 'OK', $stmt->fetchAll());