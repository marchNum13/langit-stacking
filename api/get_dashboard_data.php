<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Memuat semua kelas yang dibutuhkan
require_once '../database/connMySQL.php';
require_once '../database/UserTableClass.php';
require_once '../database/StakeTableClass.php';
require_once '../database/BalanceTableClass.php';

// Fungsi untuk mengirim respons error
function send_error($message) {
    echo json_encode(['status' => 'error', 'message' => $message]);
    exit;
}

// Cek apakah pengguna sudah login (wallet address tersimpan di session)
if (!isset($_SESSION['wallet_address'])) {
    send_error('User not authenticated. Please connect your wallet.');
}

$walletAddress = $_SESSION['wallet_address'];

try {
    // Inisialisasi semua kelas tabel
    $userTable = new UserTableClass();
    $stakeTable = new StakeTableClass();
    $balanceTable = new BalanceTableClass();

    // 1. Dapatkan data pengguna
    $user = $userTable->getUserByWalletAddress($walletAddress);
    if (!$user) {
        session_destroy(); // Hancurkan session jika data user tidak valid
        send_error('User data not found.');
    }
    $userId = $user['id'];

    // 2. Dapatkan data saldo bonus
    $balances = $balanceTable->getUserBalance($userId);
    if (!$balances) {
        // Buat entri jika tidak ada, sebagai fallback
        $balanceTable->createBalanceEntry($userId);
        $balances = $balanceTable->getUserBalance($userId);
    }
    
    // 3. Hitung Total Staking Aktif
    $totalActiveStake = $stakeTable->getUserTotalStakesUSDTByStatus($userId, 'active');

    // 4. Hitung Total Pendapatan (dari tabel balances)
    $totalEarnings = (float)$balances['staking_roi'] + (float)$balances['matching_bonus'] + (float)$balances['royalty_bonus'];

    // 5. Hitung Omset Jaringan (ini adalah fungsi yang kompleks, kita buat placeholder dulu)
    // NOTE: Logika ini akan kita implementasikan secara detail nanti
    // $networkTurnover = 0.00; // Placeholder

    $networkStats = $userTable->getNetworkStats($userId);
    $networkTurnover = 0;
    if (!empty($networkStats['all_member_ids'])) {
        $networkTurnover = $stakeTable->getTotalStakesForUserList($networkStats['all_member_ids']);
    }

    // 6. Hitung Siklus Profit Staking
    $totalWithdrawn = $stakeTable->getUserStakesByStatus($userId, 'active')[0]['total_withdrawn_usdt'];
    $totalStakeValue = $totalActiveStake;

    $profitCycleMax = $totalStakeValue > 0 ? $totalStakeValue * 5 : 0;
    $profitCyclePercentage = $profitCycleMax > 0 ? ($totalWithdrawn / $profitCycleMax) * 100 : 0;

    // Menyiapkan data untuk dikirim sebagai JSON
    $dashboardData = [
        'wallet_address' => $walletAddress,
        'user_grade' => $user['grade'] ?? 'N/A',
        'total_active_stake' => number_format($totalActiveStake, 4, '.', ','),
        'total_earnings' => number_format($totalEarnings, 4, '.', ','),
        'network_turnover' => number_format($networkTurnover, 4, '.', ','),
        'earnings_breakdown' => [
            'staking_roi' => number_format((float)$balances['staking_roi'], 4, '.', ','),
            'matching_bonus' => number_format((float)$balances['matching_bonus'], 4, '.', ','),
            'royalty_bonus' => number_format((float)$balances['royalty_bonus'], 4, '.', ',')
        ],
        'profit_cycle' => [
            'achieved' => number_format($totalWithdrawn, 2, '.', ','),
            'max' => number_format($profitCycleMax, 2, '.', ','),
            'percentage' => round($profitCyclePercentage)
        ]
    ];

    echo json_encode(['status' => 'success', 'data' => $dashboardData]);

} catch (Exception $e) {
    error_log("API get_dashboard_data error: " . $e->getMessage());
    send_error('An internal server error occurred while fetching data.');
}
?>

