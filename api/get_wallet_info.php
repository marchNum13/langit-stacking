<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Memuat semua kelas yang dibutuhkan
require_once '../database/connMySQL.php';
require_once '../database/UserTableClass.php';
require_once '../database/BalanceTableClass.php';
require_once '../utils/MarketPriceFetcher.php';
// PENAMBAHAN: Diperlukan untuk mengecek stake aktif
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
    $balanceTable = new BalanceTableClass();
    $priceFetcher = new MarketPriceFetcher();
    $stakeTable = new StakeTableClass(); // Inisialisasi StakeTableClass

    $user = $userTable->getUserByWalletAddress($walletAddress);
    if (!$user) {
        send_error('User not found.');
    }
    $userId = $user['id'];

    // Dapatkan saldo bonus
    $balances = $balanceTable->getUserBalance($userId);
    $totalWithdrawable = (float)($balances['staking_roi'] ?? 0) +
                         (float)($balances['matching_bonus'] ?? 0) +
                         (float)($balances['royalty_bonus'] ?? 0);

    // Dapatkan harga pasar
    $langitPrice = $priceFetcher->getLangitPriceInUsdt();
    if ($langitPrice === null) {
        send_error('Could not fetch market price.');
    }

    // PENAMBAHAN: Cek apakah pengguna memiliki staking aktif
    $activeStake = $stakeTable->getUserStakesByStatus($userId, 'active');
    $hasActiveStake = !empty($activeStake);
    
    // PENAMBAHAN: Logika kelayakan penarikan
    $userGrade = $user['grade'];
    $isEligible = ($userGrade !== null && $userGrade >= 'A' && $hasActiveStake);

    $response_data = [
        'wallet_address' => $walletAddress,
        'user_grade' => $userGrade,
        'withdrawable_balance_usdt' => $totalWithdrawable,
        'langit_price_usdt' => $langitPrice,
        'is_eligible_for_withdraw' => $isEligible // Flag baru untuk frontend
    ];
    
    echo json_encode(['status' => 'success', 'data' => $response_data]);

} catch (Exception $e) {
    error_log("API get_wallet_info error: " . $e->getMessage());
    send_error('An internal server error occurred.');
}
?>

