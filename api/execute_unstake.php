<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit;
}

// Memuat kelas-kelas yang dibutuhkan
require_once '../database/connMySQL.php';
require_once '../database/UserTableClass.php';
require_once '../database/StakeTableClass.php';
require_once '../database/TransactionTableClass.php';

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
if (!isset($data['stake_id_onchain']) || empty($data['stake_id_onchain'])) {
    send_error("Missing required field: stake_id_onchain");
}
if (!isset($data['tx_hash']) || empty($data['tx_hash'])) {
    send_error("Missing required field: tx_hash");
}

try {
    // Inisialisasi kelas-kelas
    $userTable = new UserTableClass();
    $stakeTable = new StakeTableClass();
    $transactionTable = new TransactionTableClass();

    $user = $userTable->getUserByWalletAddress($walletAddress);
    if (!$user) {
        send_error('User not found.');
    }
    $userId = $user['id'];
    
    // 1. Dapatkan data staking yang akan di-unstake
    $stake = $stakeTable->getStakeByIdOnchain($data['stake_id_onchain']);
    if (!$stake || $stake['user_id'] != $userId) {
        send_error('Stake data not found or you are not the owner.');
    }

    // 2. Validasi status dan plan
    if ($stake['status'] !== 'active') {
        send_error('Only active stakes can be unstaked.');
    }

    // Cek periode lock untuk plan berjangka
    if ($stake['plan'] === '6_months' || $stake['plan'] === '12_months') {
        if (new DateTime() < new DateTime($stake['expires_at'])) {
            send_error('This stake is still in its lock-in period.');
        }
    }

    // 3. Update status stake di database menjadi 'vesting'
    $updateSuccess = $stakeTable->updateStakeStatus($data['stake_id_onchain'], 'vesting');
    if (!$updateSuccess) {
        send_error('Failed to update stake status in the database.');
    }
    
    // 4. Catat transaksi unstake (opsional, tapi baik untuk riwayat)
    $transactionData = [
        'user_id' => $userId,
        'related_stake_id' => $stake['id'],
        'type' => 'unstake', // Tambahkan 'unstake' ke ENUM jika belum ada
        'tx_hash' => $data['tx_hash']
    ];
    // $transactionTable->createTransaction($transactionData); // Aktifkan jika ingin mencatat

    echo json_encode(['status' => 'success', 'message' => 'Unstake successful. Your tokens will be available for vesting claims.']);

} catch (Exception $e) {
    error_log("API execute_unstake error: " . $e->getMessage());
    send_error('An internal server error occurred during the unstake process.');
}
?>
