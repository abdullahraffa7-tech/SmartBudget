<?php
// ============================================================
// API: Dashboard - Summary, Chart Data, Recent Transactions
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
$action = $_GET['action'] ?? 'summary';

switch ($action) {
    case 'summary':       getDashboardSummary($userId); break;
    case 'monthly_chart': getMonthlyChart($userId);     break;
    case 'category_chart':getCategoryChart($userId);    break;
    case 'recent':        getRecentTransactions($userId);break;
    default:
        jsonResponse(false, 'Aksi tidak ditemukan.', null, 404);
}

// -------- Summary: Total income, expense, balance, count --------
function getDashboardSummary(int $userId): void {
    $db   = getDB();
    $year = (int)($_GET['year'] ?? date('Y'));

    $stmt = $db->prepare("
        SELECT
          SUM(CASE WHEN type='income'  THEN amount ELSE 0 END) AS total_income,
          SUM(CASE WHEN type='expense' THEN amount ELSE 0 END) AS total_expense,
          COUNT(*) AS total_transactions
        FROM transactions
        WHERE user_id = ? AND YEAR(transaction_date) = ?
    ");
    $stmt->execute([$userId, $year]);
    $row = $stmt->fetch();

    $income  = (float)($row['total_income']  ?? 0);
    $expense = (float)($row['total_expense'] ?? 0);

    jsonResponse(true, 'OK', [
        'total_income'       => $income,
        'total_expense'      => $expense,
        'balance'            => $income - $expense,
        'total_transactions' => (int)($row['total_transactions'] ?? 0),
        'year'               => $year,
    ]);
}

// -------- Monthly Chart (12 bulan) --------
function getMonthlyChart(int $userId): void {
    $db   = getDB();
    $year = (int)($_GET['year'] ?? date('Y'));

    $stmt = $db->prepare("
        SELECT
          MONTH(transaction_date) AS month,
          SUM(CASE WHEN type='income'  THEN amount ELSE 0 END) AS income,
          SUM(CASE WHEN type='expense' THEN amount ELSE 0 END) AS expense
        FROM transactions
        WHERE user_id = ? AND YEAR(transaction_date) = ?
        GROUP BY MONTH(transaction_date)
        ORDER BY month
    ");
    $stmt->execute([$userId, $year]);
    $rows = $stmt->fetchAll();

    // Build 12-month array
    $data = array_fill(1, 12, ['income' => 0, 'expense' => 0]);
    foreach ($rows as $r) {
        $data[(int)$r['month']] = [
            'income'  => (float)$r['income'],
            'expense' => (float)$r['expense'],
        ];
    }

    $labels  = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
    $income  = [];
    $expense = [];
    for ($m = 1; $m <= 12; $m++) {
        $income[]  = $data[$m]['income'];
        $expense[] = $data[$m]['expense'];
    }

    jsonResponse(true, 'OK', [
        'labels'  => $labels,
        'income'  => $income,
        'expense' => $expense,
    ]);
}

// -------- Category Chart (donut) --------
function getCategoryChart(int $userId): void {
    $db   = getDB();
    $year = (int)($_GET['year']  ?? date('Y'));
    $type = $_GET['type'] === 'income' ? 'income' : 'expense';

    $stmt = $db->prepare("
        SELECT c.name, c.color, SUM(t.amount) AS total
        FROM transactions t
        JOIN categories c ON t.category_id = c.id
        WHERE t.user_id = ? AND t.type = ? AND YEAR(t.transaction_date) = ?
        GROUP BY c.id, c.name, c.color
        ORDER BY total DESC
        LIMIT 8
    ");
    $stmt->execute([$userId, $type, $year]);
    $rows = $stmt->fetchAll();

    $labels = [];
    $values = [];
    $colors = [];
    foreach ($rows as $r) {
        $labels[] = $r['name'];
        $values[] = (float)$r['total'];
        $colors[] = $r['color'];
    }

    jsonResponse(true, 'OK', [
        'labels' => $labels,
        'values' => $values,
        'colors' => $colors,
    ]);
}

// -------- Recent Transactions --------
function getRecentTransactions(int $userId): void {
    $db   = getDB();
    $limit = (int)($_GET['limit'] ?? 7);

    $stmt = $db->prepare("
        SELECT t.id, t.type, t.amount, t.description, t.transaction_date,
               c.name AS category_name, c.icon AS category_icon, c.color AS category_color
        FROM transactions t
        JOIN categories c ON t.category_id = c.id
        WHERE t.user_id = ?
        ORDER BY t.transaction_date DESC, t.created_at DESC
        LIMIT ?
    ");
    $stmt->execute([$userId, $limit]);

    jsonResponse(true, 'OK', $stmt->fetchAll());
}