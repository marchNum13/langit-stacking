<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Memuat kelas-kelas yang dibutuhkan
require_once '../database/connMySQL.php';
require_once '../database/UserTableClass.php';
require_once '../database/TransactionTableClass.php';

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

// Mengambil parameter dari query string (GET request)
$type = $_GET['type'] ?? 'all';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Validasi tipe filter untuk keamanan
$allowedTypes = ['all', 'stake', 'withdraw', 'claim_vesting', 'bonus'];
if (!in_array($type, $allowedTypes)) {
    $type = 'all'; // Default ke 'all' jika tipe tidak valid
}

try {
    $userTable = new UserTableClass();
    $transactionTable = new TransactionTableClass();

    // Dapatkan data pengguna untuk mendapatkan user_id
    $user = $userTable->getUserByWalletAddress($walletAddress);
    if (!$user) {
        send_error('User not found.');
    }
    $userId = $user['id'];
    
    // REVISI: Cukup teruskan parameter '$type' langsung ke dalam fungsi.
    // Logika filter 'bonus' sekarang ditangani di dalam TransactionTableClass.
    $transactions = $transactionTable->getUserTransactions($userId, $type, $limit, $offset);
    $totalTransactions = $transactionTable->countUserTransactions($userId, $type);
    
    $totalPages = ceil($totalTransactions / $limit);
    
    // Menyiapkan data untuk dikirim
    $response = [
        'status' => 'success',
        'wallet_address' => $walletAddress,
        'data' => $transactions,
        'pagination' => [
            'current_page' => $page,
            'total_pages' => $totalPages,
            'total_transactions' => $totalTransactions
        ]
    ];

    echo json_encode($response);

} catch (Exception $e) {
    error_log("API get_history error: " . $e->getMessage());
    send_error('An internal server error occurred.');
}
?>

