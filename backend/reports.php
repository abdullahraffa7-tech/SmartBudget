<?php
// ============================================================
// API: Reports - Monthly & Annual Summary
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
$action = $_GET['action'] ?? 'monthly';

switch ($action) {
    case 'monthly':  getMonthlyReport($userId); break;
    case 'annual':   getAnnualReport($userId);  break;
    case 'export':   getExportReport($userId);  break;
    default:
        jsonResponse(false, 'Aksi tidak ditemukan.', null, 404);
}

// -------- Laporan Bulanan --------
function getMonthlyReport(int $userId): void {
    $year = (int)($_GET['year'] ?? date('Y'));
    jsonResponse(true, 'OK', ['year' => $year, 'monthly' => buildMonthlyReport($userId, $year)]);
}

function buildMonthlyReport(int $userId, int $year): array {
    $db   = getDB();

    $stmt = $db->prepare("
        SELECT
          MONTH(transaction_date) AS month,
          MONTHNAME(transaction_date) AS month_name,
          SUM(CASE WHEN type='income'  THEN amount ELSE 0 END) AS income,
          SUM(CASE WHEN type='expense' THEN amount ELSE 0 END) AS expense,
          COUNT(*) AS total_transactions
        FROM transactions
        WHERE user_id = ? AND YEAR(transaction_date) = ?
        GROUP BY MONTH(transaction_date), MONTHNAME(transaction_date)
        ORDER BY month
    ");
    $stmt->execute([$userId, $year]);
    $rows = $stmt->fetchAll();

    $result = [];
    foreach ($rows as $r) {
        $income  = (float)$r['income'];
        $expense = (float)$r['expense'];
        $result[] = [
            'month'              => (int)$r['month'],
            'month_name'         => $r['month_name'],
            'income'             => $income,
            'expense'            => $expense,
            'balance'            => $income - $expense,
            'total_transactions' => (int)$r['total_transactions'],
        ];
    }

    return $result;
}

// -------- Laporan Tahunan --------
function getAnnualReport(int $userId): void {
    jsonResponse(true, 'OK', ['annual' => buildAnnualReport($userId)]);
}

function buildAnnualReport(int $userId): array {
    $db = getDB();

    $stmt = $db->prepare("
        SELECT
          YEAR(transaction_date) AS year,
          SUM(CASE WHEN type='income'  THEN amount ELSE 0 END) AS income,
          SUM(CASE WHEN type='expense' THEN amount ELSE 0 END) AS expense,
          COUNT(*) AS total_transactions
        FROM transactions
        WHERE user_id = ?
        GROUP BY YEAR(transaction_date)
        ORDER BY year DESC
    ");
    $stmt->execute([$userId]);
    $rows = $stmt->fetchAll();

    $result = [];
    foreach ($rows as $r) {
        $income  = (float)$r['income'];
        $expense = (float)$r['expense'];
        $result[] = [
            'year'               => (int)$r['year'],
            'income'             => $income,
            'expense'            => $expense,
            'balance'            => $income - $expense,
            'total_transactions' => (int)$r['total_transactions'],
        ];
    }

    return $result;
}

// -------- Data Lengkap Untuk Export PDF --------
function getExportReport(int $userId): void {
    $db   = getDB();
    $year = (int)($_GET['year'] ?? date('Y'));

    $userStmt = $db->prepare("SELECT name, email FROM users WHERE id = ?");
    $userStmt->execute([$userId]);
    $user = $userStmt->fetch() ?: ['name' => 'User', 'email' => ''];

    $summaryStmt = $db->prepare("
        SELECT
          COALESCE(SUM(CASE WHEN type='income' THEN amount ELSE 0 END), 0) AS income,
          COALESCE(SUM(CASE WHEN type='expense' THEN amount ELSE 0 END), 0) AS expense,
          COUNT(*) AS total_transactions
        FROM transactions
        WHERE user_id = ? AND YEAR(transaction_date) = ?
    ");
    $summaryStmt->execute([$userId, $year]);
    $summary = $summaryStmt->fetch() ?: ['income' => 0, 'expense' => 0, 'total_transactions' => 0];
    $income  = (float)$summary['income'];
    $expense = (float)$summary['expense'];

    $categoryStmt = $db->prepare("
        SELECT t.type, c.name AS category_name, c.color AS category_color,
               SUM(t.amount) AS total, COUNT(*) AS total_transactions
        FROM transactions t
        JOIN categories c ON t.category_id = c.id
        WHERE t.user_id = ? AND YEAR(t.transaction_date) = ?
        GROUP BY t.type, c.id, c.name, c.color
        ORDER BY t.type ASC, total DESC
    ");
    $categoryStmt->execute([$userId, $year]);

    $transactionStmt = $db->prepare("
        SELECT t.type, t.amount, t.description, t.transaction_date,
               c.name AS category_name
        FROM transactions t
        JOIN categories c ON t.category_id = c.id
        WHERE t.user_id = ? AND YEAR(t.transaction_date) = ?
        ORDER BY t.transaction_date ASC, t.created_at ASC
    ");
    $transactionStmt->execute([$userId, $year]);

    jsonResponse(true, 'OK', [
        'user' => [
            'name'  => $user['name'],
            'email' => $user['email'],
        ],
        'year' => $year,
        'generated_at' => date('Y-m-d H:i:s'),
        'summary' => [
            'income' => $income,
            'expense' => $expense,
            'balance' => $income - $expense,
            'total_transactions' => (int)$summary['total_transactions'],
        ],
        'monthly' => buildMonthlyReport($userId, $year),
        'annual' => buildAnnualReport($userId),
        'categories' => array_map(function ($row) {
            return [
                'type' => $row['type'],
                'category_name' => $row['category_name'],
                'category_color' => $row['category_color'],
                'total' => (float)$row['total'],
                'total_transactions' => (int)$row['total_transactions'],
            ];
        }, $categoryStmt->fetchAll()),
        'transactions' => array_map(function ($row) {
            return [
                'type' => $row['type'],
                'amount' => (float)$row['amount'],
                'description' => $row['description'],
                'transaction_date' => $row['transaction_date'],
                'category_name' => $row['category_name'],
            ];
        }, $transactionStmt->fetchAll()),
    ]);
}
