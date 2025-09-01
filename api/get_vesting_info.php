<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Memuat kelas-kelas yang dibutuhkan
require_once '../database/connMySQL.php';
require_once '../database/UserTableClass.php';
require_once '../database/StakeTableClass.php';

function send_error($message) {
    echo json_encode(['status' => 'error', 'message' => $message]);
    exit;
}

if (!isset($_SESSION['wallet_address'])) {
    send_error('User not authenticated.');
}
$walletAddress = $_SESSION['wallet_address'];

try {
    $userTable = new UserTableClass();
    $stakeTable = new StakeTableClass();

    $user = $userTable->getUserByWalletAddress($walletAddress);
    if (!$user) {
        send_error('User not found.');
    }
    $userId = $user['id'];

    // Ambil semua stake yang statusnya 'vesting'
    $vestingStakes = $stakeTable->getUserStakesByStatus($userId, 'vesting');
    
    echo json_encode(['status' => 'success', 'data' => $vestingStakes]);

} catch (Exception $e) {
    error_log("API get_vesting_info error: " . $e->getMessage());
    send_error('An internal server error occurred.');
}
?>
