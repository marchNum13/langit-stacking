<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Memuat semua kelas yang dibutuhkan
require_once '../database/connMySQL.php';
require_once '../database/UserTableClass.php';
require_once '../database/StakeTableClass.php';

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

    // Dapatkan data pengguna saat ini
    $currentUser = $userTable->getUserByWalletAddress($walletAddress);
    if (!$currentUser) {
        send_error('Current user not found.');
    }
    $userId = $currentUser['id'];

    // 1. Dapatkan jumlah downline langsung
    $directDownlineCount = $userTable->getDirectDownlineCount($walletAddress);

    // 2. Dapatkan daftar downline langsung (kita butuh data lengkapnya)
    $directDownlines = $userTable->getDirectDownlinesData($walletAddress);

    // 3. Hitung omset untuk setiap downline langsung
    $downlinesWithTurnover = [];
    foreach ($directDownlines as $downline) {
        $downlineTurnover = $stakeTable->getUserTotalStakesUSDT($downline['id']);
        $downlinesWithTurnover[] = [
            'wallet_address' => $downline['wallet_address'],
            'turnover' => $downlineTurnover
        ];
    }
    
    // 4. Hitung Total Omset Jaringan & Total Anggota Tim (Logika Kompleks)
    // NOTE: Ini menggunakan query rekursif yang mungkin berat untuk DB besar. Caching sangat direkomendasikan.
    $networkStats = $userTable->getNetworkStats($userId);
    $totalTeamMembers = $networkStats['total_members'];
    $totalNetworkTurnover = 0;
    
    if (!empty($networkStats['all_member_ids'])) {
        $totalNetworkTurnover = $stakeTable->getTotalStakesForUserList($networkStats['all_member_ids']);
    }

    // Menyiapkan data untuk dikirim
    $response = [
        'status' => 'success',
        'data' => [
            'wallet_address' => $walletAddress,
            'current_grade' => $currentUser['grade'] ?? 'N/A',
            'referral_link' => 'https://langit.plus/index?ref=' . $walletAddress, // Contoh URL
            'network_turnover' => number_format($totalNetworkTurnover, 2, '.', ','),
            'direct_downline_count' => $directDownlineCount,
            'total_team_members' => $totalTeamMembers,
            'direct_downlines_list' => $downlinesWithTurnover
        ]
    ];

    echo json_encode($response);

} catch (Exception $e) {
    error_log("API get_network_data error: " . $e->getMessage());
    send_error('An internal server error occurred.');
}
?>
