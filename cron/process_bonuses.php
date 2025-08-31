<?php
// Atur agar skrip berjalan tanpa batas waktu (penting untuk cron job)
set_time_limit(0);
// Tampilkan semua error untuk debugging dari command line
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Atur zona waktu ke UTC agar konsisten dengan logika "sekali sehari"
date_default_timezone_set('UTC');

// Path relatif dari file cron ke direktori utama
$baseDir = __DIR__ . '/../';

// Memuat semua kelas yang dibutuhkan
require_once $baseDir . 'database/connMySQL.php';
require_once $baseDir . 'database/UserTableClass.php';
require_once $baseDir . 'database/StakeTableClass.php';
require_once $baseDir . 'database/BalanceTableClass.php';
require_once $baseDir . 'database/TransactionTableClass.php';
require_once $baseDir . 'utils/BonusManager.php';

echo "============================================\n";
echo "Memulai Cron Job Distribusi Bonus pada " . date('Y-m-d H:i:s') . "\n";
echo "============================================\n";

try {
    // Inisialisasi semua kelas
    $userTable = new UserTableClass();
    $stakeTable = new StakeTableClass();
    $balanceTable = new BalanceTableClass();
    $transactionTable = new TransactionTableClass();

    // Inisialisasi dan jalankan Bonus Manager
    $bonusManager = new BonusManager($userTable, $stakeTable, $balanceTable, $transactionTable);
    $bonusManager->processHourlyBonuses();

} catch (Exception $e) {
    // Catat error jika terjadi
    $errorMessage = "Error pada cron job: " . $e->getMessage() . " di file " . $e->getFile() . " baris " . $e->getLine() . "\n";
    echo $errorMessage;
    // Anda juga bisa mengirim notifikasi email atau mencatat ke file log khusus di sini
    file_put_contents($baseDir . 'cron/cron_error.log', date('Y-m-d H:i:s') . " - " . $errorMessage, FILE_APPEND);
}

echo "============================================\n";
echo "Cron Job Selesai pada " . date('Y-m-d H:i:s') . "\n";
echo "============================================\n";
?>
