<?php
// ============================================================
// Gemini helper murni PHP (cURL), tanpa shell_exec / Python
// Bisa dipakai sebagai library dan tetap bisa dites langsung via POST.
// ============================================================

require_once __DIR__ . '/config.php';

function geminiGenerateContent(array $contents, string $model, ?string $systemInstruction = null, array $generationConfig = []): array {
    if (!defined('GEMINI_API_KEY') || GEMINI_API_KEY === '') {
        throw new RuntimeException('GEMINI_API_KEY belum dikonfigurasi.');
    }

    if (!function_exists('curl_init')) {
        throw new RuntimeException('Ekstensi PHP cURL belum aktif di server.');
    }

    $payload = [
        'contents' => $contents,
    ];

    if ($systemInstruction !== null && trim($systemInstruction) !== '') {
        $payload['systemInstruction'] = [
            'parts' => [
                ['text' => $systemInstruction],
            ],
        ];
    }

    if ($generationConfig) {
        $payload['generationConfig'] = $generationConfig;
    }

    $url = 'https://generativelanguage.googleapis.com/v1beta/models/'
        . rawurlencode($model)
        . ':generateContent?key='
        . urlencode(GEMINI_API_KEY);

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE),
        CURLOPT_CONNECTTIMEOUT => 15,
        CURLOPT_TIMEOUT => 60,
    ]);

    $response = curl_exec($ch);
    $curlError = curl_error($ch);
    $httpCode = (int)curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    curl_close($ch);

    if ($response === false) {
        throw new RuntimeException('Gagal menghubungi Gemini: ' . $curlError);
    }

    $decoded = json_decode($response, true);
    if (!is_array($decoded)) {
        throw new RuntimeException('Respons Gemini bukan JSON valid.');
    }

    if ($httpCode < 200 || $httpCode >= 300) {
        $message = $decoded['error']['message'] ?? ('HTTP ' . $httpCode);
        throw new RuntimeException('Gemini error: ' . $message);
    }

    return $decoded;
}

function geminiTextFromResponse(array $response): string {
    $parts = $response['candidates'][0]['content']['parts'] ?? [];
    $text = '';

    foreach ($parts as $part) {
        if (isset($part['text'])) {
            $text .= $part['text'];
        }
    }

    return trim($text);
}

function geminiCleanJsonText(string $text): string {
    $text = trim($text);
    $text = preg_replace('/^```(?:json)?\s*/i', '', $text);
    $text = preg_replace('/\s*```$/', '', $text);

    $start = strpos($text, '{');
    $end = strrpos($text, '}');
    if ($start !== false && $end !== false && $end >= $start) {
        $text = substr($text, $start, $end - $start + 1);
    }

    return trim($text);
}

function geminiDecodeJsonObject(string $text): array {
    $cleaned = geminiCleanJsonText($text);
    $decoded = json_decode($cleaned, true);

    if (!is_array($decoded)) {
        throw new RuntimeException('AI tidak mengembalikan JSON valid: ' . $text);
    }

    return $decoded;
}

function geminiMimeType(string $path): string {
    $mime = '';

    if (function_exists('finfo_open')) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if ($finfo) {
            $mime = (string)finfo_file($finfo, $path);
            finfo_close($finfo);
        }
    }

    if ($mime === '') {
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        switch ($ext) {
            case 'png':
                $mime = 'image/png';
                break;
            case 'webp':
                $mime = 'image/webp';
                break;
            default:
                $mime = 'image/jpeg';
                break;
        }
    }

    return $mime;
}

function geminiAnalyzeReceipt(string $imagePath, ?string $mimeType = null): array {
    if (!is_file($imagePath)) {
        throw new RuntimeException('File gambar tidak ditemukan.');
    }

    $imageData = file_get_contents($imagePath);
    if ($imageData === false) {
        throw new RuntimeException('Gagal membaca file gambar.');
    }

    $prompt = <<<'PROMPT'
Anda adalah AI OCR untuk struk belanja, nota, atau mutasi pembayaran.

Ambil hanya data non-sensitif:
- total pembayaran
- tanggal transaksi jika terlihat
- nama item dan harga item jika terlihat

Aturan:
- Jangan ambil nomor rekening, nomor kartu, nomor transaksi, QR, barcode, alamat lengkap, atau data pribadi.
- Total harus angka Rupiah tanpa titik/koma.
- Jika tanggal tidak ada, gunakan null.
- Jika item tidak ada, gunakan array kosong.

Balas hanya JSON valid:
{
  "items": [{"name": "nama item", "price": 1000}],
  "total": 1000,
  "date": "YYYY-MM-DD atau null"
}
PROMPT;

    $response = geminiGenerateContent(
        [[
            'parts' => [
                ['text' => $prompt],
                [
                    'inlineData' => [
                        'mimeType' => $mimeType ?: geminiMimeType($imagePath),
                        'data' => base64_encode($imageData),
                    ],
                ],
            ],
        ]],
        GEMINI_VISION_MODEL,
        null,
        [
            'temperature' => 0.1,
            'responseMimeType' => 'application/json',
        ]
    );

    $data = geminiDecodeJsonObject(geminiTextFromResponse($response));

    return [
        'items' => array_values(array_filter($data['items'] ?? [], 'is_array')),
        'total' => isset($data['total']) ? (float)$data['total'] : 0,
        'date' => normalizeGeminiDate($data['date'] ?? null),
    ];
}

function normalizeGeminiDate($date): ?string {
    if ($date === null || $date === '' || strtolower((string)$date) === 'null') {
        return null;
    }

    $date = trim((string)$date);
    $dt = DateTime::createFromFormat('Y-m-d', $date);

    return $dt ? $dt->format('Y-m-d') : null;
}

function geminiChatReply(string $message, array $history, array $financialContext): string {
    $systemInstruction = <<<'SYSTEM'
Anda adalah chatbot keuangan pribadi untuk pengguna Indonesia.
Jawab dengan Bahasa Indonesia yang ramah, singkat, jelas, dan praktis.
Gunakan data aplikasi hanya untuk membantu analisis keuangan pengguna.
Jangan mengaku sebagai penasihat keuangan bersertifikat.
Jangan memberi janji keuntungan investasi.
Untuk investasi, pajak, hukum, atau produk keuangan kompleks, sarankan konsultasi dengan profesional.
SYSTEM;

    $prompt = "Data keuangan user dari aplikasi:\n"
        . json_encode($financialContext, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
        . "\n\nRiwayat chat terakhir:\n"
        . formatGeminiChatHistory($history)
        . "\n\nPertanyaan user:\n"
        . $message
        . "\n\nJawab berdasarkan data tersebut. Jika data belum cukup, sebutkan asumsi singkat dan ajukan pertanyaan lanjutan yang spesifik.";

    $response = geminiGenerateContent(
        [[
            'parts' => [
                ['text' => $prompt],
            ],
        ]],
        GEMINI_TEXT_MODEL,
        $systemInstruction,
        [
            'temperature' => 0.4,
        ]
    );

    $reply = geminiTextFromResponse($response);

    return $reply !== '' ? $reply : 'Maaf, saya belum bisa membuat jawaban. Coba ulangi dengan kalimat yang lebih jelas.';
}

function formatGeminiChatHistory(array $history): string {
    if (!$history) {
        return 'Belum ada riwayat percakapan.';
    }

    $lines = [];
    foreach (array_slice($history, -10) as $item) {
        $role = (($item['role'] ?? '') === 'bot') ? 'Bot' : 'User';
        $text = trim((string)($item['text'] ?? ''));
        if ($text !== '') {
            $lines[] = $role . ': ' . $text;
        }
    }

    return $lines ? implode("\n", $lines) : 'Belum ada riwayat percakapan.';
}

function geminiDirectUploadEndpoint(): void {
    header('Content-Type: application/json; charset=utf-8');

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['status' => 'error', 'message' => 'Method tidak valid'], JSON_UNESCAPED_UNICODE);
        return;
    }

    $file = $_FILES['gambar'] ?? $_FILES['image'] ?? null;
    if (!$file || !is_uploaded_file($file['tmp_name'])) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Gambar tidak ditemukan'], JSON_UNESCAPED_UNICODE);
        return;
    }

    try {
        echo json_encode(geminiAnalyzeReceipt($file['tmp_name'], $file['type'] ?? null), JSON_UNESCAPED_UNICODE);
    } catch (Throwable $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
    }
}

if (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    geminiDirectUploadEndpoint();
}
