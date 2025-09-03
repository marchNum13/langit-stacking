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

    $currentUser = $userTable->getUserByWalletAddress($walletAddress);
    if (!$currentUser) {
        send_error('Current user not found.');
    }
    $userId = $currentUser['id'];

    // 1. Dapatkan statistik dasar jaringan
    $directDownlineCount = $userTable->getDirectDownlineCount($walletAddress);
    $networkStats = $userTable->getNetworkStats($userId);
    $totalTeamMembers = $networkStats['total_members'];

    // 2. Hitung Total Omset Jaringan
    $totalNetworkTurnover = 0;
    if (!empty($networkStats['all_member_ids'])) {
        $totalNetworkTurnover = $stakeTable->getTotalStakesForUserList($networkStats['all_member_ids']);
    }

    // 3. Dapatkan daftar downline langsung dan omset mereka
    $directDownlines = $userTable->getDirectDownlinesData($walletAddress);
    $downlinesWithTurnover = [];
    foreach ($directDownlines as $downline) {
        $downlineTurnover = $stakeTable->getUserTotalStakesUSDT($downline['id']);
        $downlinesWithTurnover[] = [
            'wallet_address' => $downline['wallet_address'],
            'turnover' => number_format($downlineTurnover, 2, '.', ',')
        ];
    }
    
    // 4. PENAMBAHAN: Logika untuk "Journey to Grade"
    $gradeRequirements = [
        'A' => 50, 'B' => 150, 'C' => 500, 'D' => 1500,
        'E' => 5000, 'F' => 20000, 'G' => 100000, 'H' => 500000,
    ];
    $gradesOrder = array_keys($gradeRequirements);
    $currentGrade = $currentUser['grade'];
    $nextGrade = 'H'; // Default jika sudah di puncak
    $nextGradeTarget = $gradeRequirements['H'];

    if ($currentGrade === null) {
        $nextGrade = 'A';
        $nextGradeTarget = $gradeRequirements['A'];
    } else {
        $currentIndex = array_search($currentGrade, $gradesOrder);
        if ($currentIndex !== false && $currentIndex < count($gradesOrder) - 1) {
            $nextGrade = $gradesOrder[$currentIndex + 1];
            $nextGradeTarget = $gradeRequirements[$nextGrade];
        }
    }

    // Menyiapkan data untuk dikirim
    $response = [
        'status' => 'success',
        'data' => [
            'wallet_address' => $walletAddress,
            'referral_link' => 'https://langitstaking.com/index?ref=' . $walletAddress,
            'direct_downline_count' => $directDownlineCount,
            'total_team_members' => $totalTeamMembers,
            'direct_downlines_list' => $downlinesWithTurnover,
            'grade_journey' => [
                'current_turnover' => $totalNetworkTurnover,
                'target_turnover' => $nextGradeTarget,
                'next_grade' => $nextGrade
            ]
        ]
    ];

    echo json_encode($response);

} catch (Exception $e) {
    error_log("API get_network_data error: " . $e->getMessage());
    send_error('An internal server error occurred.');
}
?>

