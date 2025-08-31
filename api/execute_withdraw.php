<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') exit;

require_once '../database/connMySQL.php';
require_once '../database/UserTableClass.php';
require_once '../database/BalanceTableClass.php';
require_once '../database/StakeTableClass.php';
require_once '../database/TransactionTableClass.php';

function send_error($message) {
    echo json_encode(['status' => 'error', 'message' => $message]);
    exit;
}

if (!isset($_SESSION['wallet_address'])) send_error('User not authenticated.');
$walletAddress = $_SESSION['wallet_address'];

$data = json_decode(file_get_contents('php://input'), true);
$required = ['stake_id_onchain', 'amount_usdt', 'tx_hash', 'is_burn'];
foreach ($required as $field) {
    if (!isset($data[$field])) send_error("Missing required field: {$field}");
}

try {
    $userTable = new UserTableClass();
    $balanceTable = new BalanceTableClass();
    $stakeTable = new StakeTableClass();
    $transactionTable = new TransactionTableClass();

    $user = $userTable->getUserByWalletAddress($walletAddress);
    if (!$user) send_error('User not found.');
    $userId = $user['id'];

    $stake = $stakeTable->getStakeByIdOnchain($data['stake_id_onchain']);
    if (!$stake || $stake['user_id'] != $userId) send_error('Stake data mismatch.');

    // 1. Kurangi Saldo
    $balanceTable->deductBalancesAfterWithdrawal($userId, (float)$data['amount_usdt']);

    // 2. Tambah jumlah yang sudah ditarik
    $stakeTable->addWithdrawnAmount($data['stake_id_onchain'], (float)$data['amount_usdt']);

    // 3. Update status jika terjadi burn
    if ($data['is_burn']) {
        $stakeTable->updateStakeStatus($data['stake_id_onchain'], 'burned');
    }

    // 4. Catat transaksi
    $transactionData = [
        'user_id' => $userId,
        'related_stake_id' => $stake['id'],
        'type' => 'withdraw',
        'amount_usdt' => (float)$data['amount_usdt'],
        'tx_hash' => $data['tx_hash']
    ];
    $transactionTable->createTransaction($transactionData);

    echo json_encode(['status' => 'success', 'message' => 'Withdrawal recorded successfully.']);

} catch (Exception $e) {
    error_log("API execute_withdraw error: " . $e->getMessage());
    send_error('An internal server error occurred.');
}
?>
