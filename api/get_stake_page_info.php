<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Memuat semua kelas yang dibutuhkan
require_once '../database/connMySQL.php';
require_once '../database/UserTableClass.php';
require_once '../database/StakeTableClass.php';
require_once '../utils/MarketPriceFetcher.php'; // Untuk mengambil harga

// Fungsi untuk mengirim respons error
function send_error($message) {
    echo json_encode(['status' => 'error', 'message' => $message]);
    exit;
}

// Cek autentikasi pengguna
if (!isset($_SESSION['wallet_address'])) {
    send_error('User not authenticated.');
}

$walletAddress = $_SESSION['wallet_address'];

try {
    $userTable = new UserTableClass();
    $stakeTable = new StakeTableClass();
    $priceFetcher = new MarketPriceFetcher();

    $user = $userTable->getUserByWalletAddress($walletAddress);
    if (!$user) {
        send_error('User not found.');
    }
    $userId = $user['id'];

    // Cek apakah user sudah memiliki staking aktif
    $activeStake = $stakeTable->getUserStakesByStatus($userId, 'active');

    $langitPrice = $priceFetcher->getLangitPriceInUsdt();
    if ($langitPrice === null) {
        send_error('Failed to fetch LANGIT market price.');
    }

    $response_data = [
        'wallet_address' => $walletAddress,
        'langit_price_usdt' => $langitPrice,
        'has_active_stake' => !empty($activeStake)
    ];

    // Jika ada staking aktif, sertakan detailnya
    if (!empty($activeStake)) {
        // Karena hanya ada satu stake aktif, kita ambil yang pertama
        $stakeDetails = $activeStake[0];
        
        $profitCyclePercentage = 0;
        if ((float)$stakeDetails['max_profit_usdt'] > 0) {
            $profitCyclePercentage = ((float)$stakeDetails['total_withdrawn_usdt'] / (float)$stakeDetails['max_profit_usdt']) * 100;
        }

        $response_data['active_stake_details'] = [
            'stake_id_onchain' => $stakeDetails['stake_id_onchain'],
            'plan' => ucwords(str_replace('_', ' ', $stakeDetails['plan'])),
            'amount_langit' => number_format((float)$stakeDetails['amount_langit'], 2, '.', ','),
            'amount_usdt_initial' => number_format((float)$stakeDetails['amount_usdt_initial'], 4, '.', ','),
            'start_date' => date("M d, Y", strtotime($stakeDetails['created_at'])),
            'expires_at' => $stakeDetails['expires_at'] ? date("M d, Y", strtotime($stakeDetails['expires_at'])) : null,
            'profit_cycle' => [
                'achieved' => number_format((float)$stakeDetails['total_withdrawn_usdt'], 4, '.', ','),
                'max' => number_format((float)$stakeDetails['max_profit_usdt'], 2, '.', ','),
                'percentage' => round($profitCyclePercentage)
            ]
        ];
    }
    
    echo json_encode(['status' => 'success', 'data' => $response_data]);

} catch (Exception $e) {
    error_log("API get_stake_page_info error: " . $e->getMessage());
    send_error('An internal server error occurred.');
}
?>