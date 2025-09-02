<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit;
}

// Memuat semua kelas dan utilitas
require_once '../database/connMySQL.php';
require_once '../database/UserTableClass.php';
require_once '../database/StakeTableClass.php';
require_once '../database/TransactionTableClass.php';
require_once '../utils/MarketPriceFetcher.php';
// PENAMBAHAN: Memuat GradeManager
require_once '../utils/GradeManager.php';

// Fungsi untuk mengirim respons error
function send_error($message) {
    echo json_encode(['status' => 'error', 'message' => $message]);
    exit;
}

// Cek autentikasi
if (!isset($_SESSION['wallet_address'])) {
    send_error('User not authenticated.');
}
$walletAddress = $_SESSION['wallet_address'];

// Validasi metode request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_error('Invalid request method.');
}

// Ambil data dari body request
$data = json_decode(file_get_contents('php://input'), true);

// Validasi input
$required_fields = ['stake_id_onchain', 'plan', 'amount_langit', 'tx_hash'];
foreach ($required_fields as $field) {
    if (!isset($data[$field]) || empty($data[$field])) {
        send_error("Missing required field: {$field}");
    }
}

try {
    // Inisialisasi kelas-kelas
    $userTable = new UserTableClass();
    $stakeTable = new StakeTableClass();
    $transactionTable = new TransactionTableClass();
    $priceFetcher = new MarketPriceFetcher();

    // 1. Dapatkan data user
    $user = $userTable->getUserByWalletAddress($walletAddress);
    if (!$user) {
        send_error('User not found.');
    }
    $userId = $user['id'];
    
    // 2. Cek lagi, pastikan user belum punya stake aktif
    $activeStake = $stakeTable->getUserStakesByStatus($userId, 'active');
    if (!empty($activeStake)) {
        send_error('You already have an active stake.');
    }

    // 3. Ambil harga pasar TERBARU dari server (sumber kebenaran)
    $langitPrice = $priceFetcher->getLangitPriceInUsdt();
    if ($langitPrice === null) {
        send_error('Failed to fetch LANGIT market price for validation.');
    }
    
    // 4. Hitung nilai USDT dan max profit
    $amount_langit = (float)$data['amount_langit'];
    $amount_usdt_initial = $amount_langit * (float)$langitPrice;
    $max_profit_usdt = $amount_usdt_initial * 5;

    // 5. Siapkan data untuk disimpan ke tabel `stakes`
    $stakeData = [
        'user_id' => $userId,
        'stake_id_onchain' => $data['stake_id_onchain'],
        'plan' => $data['plan'],
        'amount_langit' => $data['amount_langit'],
        'amount_usdt_initial' => $amount_usdt_initial,
        'max_profit_usdt' => $max_profit_usdt
    ];

    // 6. Simpan data staking
    $newStakeId = $stakeTable->createStake($stakeData);
    if (!$newStakeId) {
        send_error('Failed to save staking data to the database.');
    }
    
    // 7. Catat transaksi
    $transactionData = [
        'user_id' => $userId,
        'related_stake_id' => $newStakeId,
        'type' => 'stake',
        'amount_usdt' => $amount_usdt_initial,
        'tx_hash' => $data['tx_hash']
    ];
    $transactionTable->createTransaction($transactionData);
    $gradeManager = new GradeManager($userTable, $stakeTable);
    $gradeManager->checkAndUpgradeSingleUser($user);
    
    // 8. REVISI: Implementasikan pemicu untuk pengecekan grade upline
    if (isset($user['upline_wallet']) && !empty($user['upline_wallet'])) {
        $gradeManager->checkAndUpgradeUplines($user['upline_wallet']);
    }

    echo json_encode(['status' => 'success', 'message' => 'Staking was successful and recorded.']);

} catch (Exception $e) {
    error_log("API execute_stake error: " . $e->getMessage());
    send_error('An internal server error occurred during staking process.');
}
?>

