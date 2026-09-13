<?php
// ============================================================
// API: Chatbot Keuangan
// Menghubungkan web PHP langsung ke Gemini via cURL.
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
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Credentials: true');

requireLogin();

$userId = getCurrentUserId();
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? 'send';

switch ("$method:$action") {
    case 'GET:history':
        getChatHistory($userId);
        break;
    case 'POST:send':
        sendMessage($userId);
        break;
    case 'DELETE:clear':
        clearChatHistory($userId);
        break;
    default:
        jsonResponse(false, 'Endpoint chatbot tidak ditemukan.', null, 404);
}

function ensureChatbotTable(PDO $db): void {
    $db->exec("
        CREATE TABLE IF NOT EXISTS chatbot_messages (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            role ENUM('user','bot') NOT NULL,
            message TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_chatbot_user_created (user_id, created_at),
            CONSTRAINT fk_chatbot_user
                FOREIGN KEY (user_id) REFERENCES users(id)
                ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
}

function getChatHistory(int $userId): void {
    $db = getDB();
    ensureChatbotTable($db);

    $stmt = $db->prepare("
        SELECT role, message, created_at
        FROM chatbot_messages
        WHERE user_id = ?
        ORDER BY created_at ASC, id ASC
        LIMIT 80
    ");
    $stmt->execute([$userId]);

    jsonResponse(true, 'OK', ['messages' => $stmt->fetchAll()]);
}

function clearChatHistory(int $userId): void {
    $db = getDB();
    ensureChatbotTable($db);

    $stmt = $db->prepare("DELETE FROM chatbot_messages WHERE user_id = ?");
    $stmt->execute([$userId]);

    jsonResponse(true, 'Riwayat chat berhasil dihapus.');
}

function sendMessage(int $userId): void {
    $body = json_decode(file_get_contents('php://input'), true);
    $message = trim($body['message'] ?? '');

    if ($message === '') {
        jsonResponse(false, 'Pesan tidak boleh kosong.', null, 400);
    }

    if (mb_strlen($message) > 1200) {
        jsonResponse(false, 'Pesan terlalu panjang. Maksimal 1200 karakter.', null, 400);
    }

    $db = getDB();
    ensureChatbotTable($db);

    $history = getRecentChatForPrompt($db, $userId);
    $financialContext = buildFinancialContext($db, $userId);

    try {
        $reply = geminiChatReply($message, $history, $financialContext);
    } catch (Throwable $e) {
        jsonResponse(false, 'AI gagal menjawab: ' . $e->getMessage(), null, 500);
    }

    saveChatMessage($db, $userId, 'user', $message);
    saveChatMessage($db, $userId, 'bot', $reply);

    jsonResponse(true, 'OK', [
        'reply' => $reply,
        'context' => [
            'total_income' => $financialContext['summary']['total_income'],
            'total_expense' => $financialContext['summary']['total_expense'],
            'balance' => $financialContext['summary']['balance'],
            'transaction_count' => $financialContext['summary']['transaction_count'],
        ],
    ]);
}

function getRecentChatForPrompt(PDO $db, int $userId): array {
    $stmt = $db->prepare("
        SELECT role, message
        FROM chatbot_messages
        WHERE user_id = ?
        ORDER BY created_at DESC, id DESC
        LIMIT 10
    ");
    $stmt->execute([$userId]);
    $rows = array_reverse($stmt->fetchAll());

    return array_map(function ($row) {
        return [
            'role' => $row['role'] === 'bot' ? 'bot' : 'user',
            'text' => $row['message'],
        ];
    }, $rows);
}

function saveChatMessage(PDO $db, int $userId, string $role, string $message): void {
    $stmt = $db->prepare("
        INSERT INTO chatbot_messages (user_id, role, message)
        VALUES (?, ?, ?)
    ");
    $stmt->execute([$userId, $role, $message]);
}

function buildFinancialContext(PDO $db, int $userId): array {
    $currentYear = (int)date('Y');
    $currentMonth = (int)date('m');

    $summaryStmt = $db->prepare("
        SELECT
            COALESCE(SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END), 0) AS total_income,
            COALESCE(SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END), 0) AS total_expense,
            COUNT(*) AS transaction_count
        FROM transactions
        WHERE user_id = ?
    ");
    $summaryStmt->execute([$userId]);
    $summary = $summaryStmt->fetch();

    $monthStmt = $db->prepare("
        SELECT
            COALESCE(SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END), 0) AS income,
            COALESCE(SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END), 0) AS expense,
            COUNT(*) AS transaction_count
        FROM transactions
        WHERE user_id = ?
          AND YEAR(transaction_date) = ?
          AND MONTH(transaction_date) = ?
    ");
    $monthStmt->execute([$userId, $currentYear, $currentMonth]);
    $currentMonthData = $monthStmt->fetch();

    $categoryStmt = $db->prepare("
        SELECT t.type, c.name AS category, SUM(t.amount) AS total, COUNT(*) AS transaction_count
        FROM transactions t
        JOIN categories c ON t.category_id = c.id
        WHERE t.user_id = ?
        GROUP BY t.type, c.name
        ORDER BY total DESC
        LIMIT 12
    ");
    $categoryStmt->execute([$userId]);

    $recentStmt = $db->prepare("
        SELECT t.type, t.amount, t.description, t.transaction_date, c.name AS category
        FROM transactions t
        JOIN categories c ON t.category_id = c.id
        WHERE t.user_id = ?
        ORDER BY t.transaction_date DESC, t.created_at DESC
        LIMIT 20
    ");
    $recentStmt->execute([$userId]);

    $monthlyStmt = $db->prepare("
        SELECT
            YEAR(transaction_date) AS year,
            MONTH(transaction_date) AS month,
            SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) AS income,
            SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) AS expense
        FROM transactions
        WHERE user_id = ?
        GROUP BY YEAR(transaction_date), MONTH(transaction_date)
        ORDER BY year DESC, month DESC
        LIMIT 12
    ");
    $monthlyStmt->execute([$userId]);

    $totalIncome = (float)$summary['total_income'];
    $totalExpense = (float)$summary['total_expense'];

    return [
        'generated_at' => date('Y-m-d H:i:s'),
        'summary' => [
            'total_income' => $totalIncome,
            'total_expense' => $totalExpense,
            'balance' => $totalIncome - $totalExpense,
            'transaction_count' => (int)$summary['transaction_count'],
        ],
        'current_month' => [
            'year' => $currentYear,
            'month' => $currentMonth,
            'income' => (float)$currentMonthData['income'],
            'expense' => (float)$currentMonthData['expense'],
            'balance' => (float)$currentMonthData['income'] - (float)$currentMonthData['expense'],
            'transaction_count' => (int)$currentMonthData['transaction_count'],
        ],
        'top_categories' => $categoryStmt->fetchAll(),
        'recent_transactions' => $recentStmt->fetchAll(),
        'monthly_trends' => $monthlyStmt->fetchAll(),
    ];
}
