<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') exit;

require_once '../database/connMySQL.php';
require_once '../database/UserTableClass.php';
require_once '../database/StakeTableClass.php';
require_once '../database/TransactionTableClass.php';

function send_error($message) {
    echo json_encode(['status' => 'error', 'message' => $message]);
    exit;
}

if (!isset($_SESSION['wallet_address'])) send_error('User not authenticated.');
$walletAddress = $_SESSION['wallet_address'];

$data = json_decode(file_get_contents('php://input'), true);
$required = ['stake_id_onchain', 'tx_hash', 'amount_langit_claimed', 'is_complete'];
foreach ($required as $field) {
    if (!isset($data[$field])) {
        send_error("Missing required field: {$field}");
    }
}

try {
    $userTable = new UserTableClass();
    $stakeTable = new StakeTableClass();
    $transactionTable = new TransactionTableClass();

    $user = $userTable->getUserByWalletAddress($walletAddress);
    if (!$user) send_error('User not found.');
    $userId = $user['id'];

    $stake = $stakeTable->getStakeByIdOnchain($data['stake_id_onchain']);
    if (!$stake || $stake['user_id'] != $userId) send_error('Stake data mismatch.');

    // 1. Catat transaksi klaim vesting
    $transactionTable->createTransaction([
        'user_id' => $userId,
        'related_stake_id' => $stake['id'],
        'type' => 'claim_vesting',
        'amount_langit' => (float)$data['amount_langit_claimed'],
        'tx_hash' => $data['tx_hash']
    ]);

    // 2. Jika vesting sudah selesai, update status di database
    if ($data['is_complete'] === true) {
        $stakeTable->updateStakeStatus($data['stake_id_onchain'], 'vesting_complete');
    }

    echo json_encode(['status' => 'success', 'message' => 'Vesting claim recorded successfully.']);

} catch (Exception $e) {
    error_log("API execute_vesting_claim error: " . $e->getMessage());
    send_error('An internal server error occurred.');
}
?>
