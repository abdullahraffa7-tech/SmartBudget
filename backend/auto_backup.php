<?php
// =======================
// KONFIGURASI
// =======================
$db_host = "localhost";
$db_user = "root";
$db_pass = ""; // isi kalau ada password
$db_name = "finance_app";

$backupDir = "C:\\backup\\"; // pastikan folder ini ada
$mysqldump = "C:\\xampp\\mysql\\bin\\mysqldump";

// =======================
// SECURITY (opsional tapi disarankan)
// =======================
$secret_key = ""; // bebas
if (php_sapi_name() !== 'cli') {
    if (!isset($_GET['key']) || $_GET['key'] !== "") {
        die("Akses ditolak!");
    }
}

// =======================
// BUAT NAMA FILE
// =======================
$date = date("Y-m-d_H-i-s");
$filename = "backup_" . $date . ".sql";
$filepath = $backupDir . $filename;

// =======================
// PROSES BACKUP
// =======================
$command = "\"$mysqldump.exe\" --host=$db_host --user=$db_user --password=$db_pass $db_name > \"$filepath\"";
system($command, $output);

// =======================
// CEK HASIL BACKUP
// =======================
if (file_exists($filepath)) {
    echo "✅ Backup berhasil: $filename<br>";
} else {
    echo "❌ Backup gagal!<br>";
}

// =======================
// AUTO DELETE (hapus > 3 bulan)
// =======================
$files = glob($backupDir . "*.sql");

foreach ($files as $file) {
    if (is_file($file)) {
        if (time() - filemtime($file) > (90 * 24 * 60 * 60)) {
            unlink($file);
            echo "🗑️ Hapus file lama: " . basename($file) . "<br>";
        }
    }
}

echo "✅ Proses selesai!";
?>