<?php
// ============================================================
// API: Transactions (CRUD + Filter + Search)
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
header('Access-Control-Allow-Methods: POST, GET, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Credentials: true');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit;

requireLogin();

$userId = getCurrentUserId();
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

switch ("$method:$action") {
    case 'GET:list':     getTransactions($userId);   break;
    case 'GET:get':      getTransaction($userId);    break;
    case 'POST:create':  createTransaction($userId); break;
    case 'PUT:update':   updateTransaction($userId); break;
    case 'DELETE:delete':deleteTransaction($userId); break;
    default:
        jsonResponse(false, 'Endpoint tidak ditemukan.', null, 404);
}

// -------- LIST dengan filter & search --------
function getTransactions(int $userId): void {
    $db = getDB();
    ensureTransactionImagePathColumn($db);

    $where  = ['t.user_id = ?'];
    $params = [$userId];

    $search = trim($_GET['search'] ?? '');
    $type   = $_GET['type']   ?? '';
    $month  = $_GET['month']  ?? '';
    $year   = $_GET['year']   ?? '';
    $limit  = (int)($_GET['limit'] ?? 50);
    $offset = (int)($_GET['offset'] ?? 0);

    if ($search) {
        $where[]  = 't.description LIKE ?';
        $params[] = "%$search%";
    }
    if ($type === 'income' || $type === 'expense') {
        $where[]  = 't.type = ?';
        $params[] = $type;
    }
    if ($month && $year) {
        $where[]  = 'MONTH(t.transaction_date) = ? AND YEAR(t.transaction_date) = ?';
        $params[] = (int)$month;
        $params[] = (int)$year;
    } elseif ($year) {
        $where[]  = 'YEAR(t.transaction_date) = ?';
        $params[] = (int)$year;
    }

    $whereStr = implode(' AND ', $where);

    // Count total
    $countStmt = $db->prepare("SELECT COUNT(*) FROM transactions t WHERE $whereStr");
    $countStmt->execute($params);
    $total = (int)$countStmt->fetchColumn();

    // Data
    $params[] = $limit;
    $params[] = $offset;
    $stmt = $db->prepare("
        SELECT t.id, t.type, t.amount, t.description, t.transaction_date, t.image_path,
               c.name AS category_name, c.icon AS category_icon, c.color AS category_color
        FROM transactions t
        JOIN categories c ON t.category_id = c.id
        WHERE $whereStr
        ORDER BY t.transaction_date DESC, t.created_at DESC
        LIMIT ? OFFSET ?
    ");
    $stmt->execute($params);
    $rows = $stmt->fetchAll();

    jsonResponse(true, 'OK', ['transactions' => $rows, 'total' => $total]);
}

// -------- GET SINGLE --------
function getTransaction(int $userId): void {
    $id = (int)($_GET['id'] ?? 0);
    if (!$id) jsonResponse(false, 'ID tidak valid.', null, 400);

    $db   = getDB();
    ensureTransactionImagePathColumn($db);
    $stmt = $db->prepare("
        SELECT t.id, t.type, t.amount, t.description, t.transaction_date, t.image_path,
               t.category_id, c.name AS category_name
        FROM transactions t
        JOIN categories c ON t.category_id = c.id
        WHERE t.id = ? AND t.user_id = ?
    ");
    $stmt->execute([$id, $userId]);
    $row = $stmt->fetch();

    if (!$row) jsonResponse(false, 'Transaksi tidak ditemukan.', null, 404);
    jsonResponse(true, 'OK', $row);
}

// -------- CREATE --------
function createTransaction(int $userId): void {
    $body = json_decode(file_get_contents('php://input'), true);

    $imagePath = isset($body['image_path']) && $body['image_path'] !== '' ? $body['image_path'] : null;

    $type        = $body['type']             ?? '';
    $amount      = floatval($body['amount']  ?? 0);
    $desc        = trim($body['description'] ?? '');
    $date        = $body['transaction_date'] ?? '';
    $categoryId  = (int)($body['category_id'] ?? 0);

    if (!in_array($type, ['income','expense'])) jsonResponse(false, 'Jenis transaksi tidak valid.', null, 400);
    if ($amount <= 0)   jsonResponse(false, 'Jumlah harus lebih dari 0.', null, 400);
    if (!$desc)         jsonResponse(false, 'Deskripsi wajib diisi.', null, 400);
    if (!$date)         jsonResponse(false, 'Tanggal wajib diisi.', null, 400);
    if (!$categoryId)   jsonResponse(false, 'Kategori wajib dipilih.', null, 400);

    // Validasi tanggal
    $d = DateTime::createFromFormat('Y-m-d', $date);
    if (!$d) jsonResponse(false, 'Format tanggal tidak valid.', null, 400);

    // Validasi kategori
    $db   = getDB();
    ensureTransactionImagePathColumn($db);
    $cStmt = $db->prepare("SELECT id FROM categories WHERE id = ? AND (user_id = ? OR user_id IS NULL) AND type = ?");
    $cStmt->execute([$categoryId, $userId, $type]);
    if (!$cStmt->fetch()) jsonResponse(false, 'Kategori tidak valid.', null, 400);

    $stmt = $db->prepare("
    INSERT INTO transactions 
    (user_id, category_id, type, amount, description, transaction_date, image_path)
    VALUES (?, ?, ?, ?, ?, ?, ?)
");

$stmt->execute([$userId, $categoryId, $type, $amount, sanitize($desc), $date, $imagePath]);

    jsonResponse(true, 'Transaksi berhasil ditambahkan!', ['id' => (int)$db->lastInsertId()]);
}

// -------- UPDATE --------
function updateTransaction(int $userId): void {
    $id   = (int)($_GET['id'] ?? 0);
    if (!$id) jsonResponse(false, 'ID tidak valid.', null, 400);

    $body = json_decode(file_get_contents('php://input'), true);

    $imagePath = isset($body['image_path']) && $body['image_path'] !== '' ? $body['image_path'] : null;

    $type       = $body['type']             ?? '';
    $amount     = floatval($body['amount']  ?? 0);
    $desc       = trim($body['description'] ?? '');
    $date       = $body['transaction_date'] ?? '';
    $categoryId = (int)($body['category_id'] ?? 0);

    if (!in_array($type, ['income','expense'])) jsonResponse(false, 'Jenis transaksi tidak valid.', null, 400);
    if ($amount <= 0)   jsonResponse(false, 'Jumlah harus lebih dari 0.', null, 400);
    if (!$desc)         jsonResponse(false, 'Deskripsi wajib diisi.', null, 400);
    if (!$date)         jsonResponse(false, 'Tanggal wajib diisi.', null, 400);
    if (!$categoryId)   jsonResponse(false, 'Kategori wajib dipilih.', null, 400);

    $db = getDB();
    ensureTransactionImagePathColumn($db);

    // Cek kepemilikan
    $check = $db->prepare("SELECT id FROM transactions WHERE id = ? AND user_id = ?");
    $check->execute([$id, $userId]);
    if (!$check->fetch()) jsonResponse(false, 'Transaksi tidak ditemukan.', null, 404);

    $stmt = $db->prepare("
    UPDATE transactions
    SET category_id = ?, type = ?, amount = ?, description = ?, transaction_date = ?, image_path = ?
    WHERE id = ? AND user_id = ?
");

$stmt->execute([$categoryId, $type, $amount, sanitize($desc), $date, $imagePath, $id, $userId]);

    jsonResponse(true, 'Transaksi berhasil diperbarui!');
}

// -------- DELETE --------
function deleteTransaction(int $userId): void {
    $id = (int)($_GET['id'] ?? 0);
    if (!$id) jsonResponse(false, 'ID tidak valid.', null, 400);

    $db   = getDB();
    $stmt = $db->prepare("DELETE FROM transactions WHERE id = ? AND user_id = ?");
    $stmt->execute([$id, $userId]);

    if ($stmt->rowCount() === 0) {
        jsonResponse(false, 'Transaksi tidak ditemukan.', null, 404);
    }

    jsonResponse(true, 'Transaksi berhasil dihapus!');
}

function ensureTransactionImagePathColumn(PDO $db): void {
    static $checked = false;
    if ($checked) return;

    $stmt = $db->prepare("
        SELECT COUNT(*)
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'transactions'
          AND COLUMN_NAME = 'image_path'
    ");
    $stmt->execute();

    if ((int)$stmt->fetchColumn() === 0) {
        $db->exec("ALTER TABLE transactions ADD COLUMN image_path VARCHAR(255) NULL AFTER transaction_date");
    }

    $checked = true;
}
