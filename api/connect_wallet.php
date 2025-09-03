<?php
// PENAMBAHAN: Mulai session di awal skrip
session_start();

// Mengatur header untuk memastikan output adalah JSON
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // Izinkan akses dari mana saja (untuk pengembangan)
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight request for CORS
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit;
}

// Memuat kelas-kelas yang dibutuhkan
require_once '../database/connMySQL.php';
require_once '../database/UserTableClass.php';
require_once '../database/BalanceTableClass.php';

// Fungsi untuk mengirim respons error dan menghentikan skrip
function send_error($message) {
    echo json_encode(['status' => 'error', 'message' => $message]);
    exit;
}

// 1. Memvalidasi Metode Request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_error('Invalid request method. Only POST is accepted.');
}

// 2. Mengambil dan Mendecode Data JSON dari Body Request
$data = json_decode(file_get_contents('php://input'), true);

// 3. Memvalidasi Input yang Diterima
if (!isset($data['wallet_address']) || empty($data['wallet_address'])) {
    send_error('Wallet address is required.');
}

// Validasi sederhana untuk format alamat wallet (diawali 0x dan panjang 42 karakter)
if (!preg_match('/^0x[a-fA-F0-9]{40}$/', $data['wallet_address'])) {
    send_error('Invalid wallet address format.');
}

$walletAddress = strtolower($data['wallet_address']); // Standarisasi ke huruf kecil
$uplineAddress = "0x87e58959fa8b3343ad7eab346b0dea7ad0506c99";

if (isset($data['upline_address']) && !empty($data['upline_address'])) {
    if (!preg_match('/^0x[a-fA-F0-9]{40}$/', $data['upline_address'])) {
        send_error('Invalid upline address format.');
    }
    // Pastikan user tidak menggunakan alamatnya sendiri sebagai upline
    if (strtolower($data['upline_address']) === $walletAddress) {
        send_error('Cannot use your own address as an upline.');
    }
    $uplineAddress = strtolower($data['upline_address']);
}

// 4. Memproses Logika Utama
try {
    $userTable = new UserTableClass();

    // Cek apakah upline yang diberikan benar-benar ada di database, jika ada
    if ($uplineAddress !== null) {
        $uplineUser = $userTable->getUserByWalletAddress($uplineAddress);
        if (!$uplineUser) {
            // Jika upline tidak ditemukan, anggap tidak ada upline untuk menghindari error
            $uplineAddress = "0x87e58959fa8b3343ad7eab346b0dea7ad0506c99"; 
        }
    }
    
    // Cari atau buat pengguna baru
    $result = $userTable->findOrCreateUser($walletAddress, $uplineAddress);
    $userData = $result['user'];
    $isNewUser = $result['is_new'];

    // PENAMBAHAN: Simpan wallet address ke dalam session setelah berhasil
    $_SESSION['wallet_address'] = $walletAddress;

    // Jika pengguna baru berhasil dibuat
    if ($isNewUser) {
        $newUserId = $userData['id'];

        // Buat entri balance untuk pengguna baru
        $balanceTable = new BalanceTableClass();
        $balanceTable->createBalanceEntry($newUserId);

        // Kirim respons dengan data pengguna baru
        echo json_encode(['status' => 'success', 'message' => 'User registered successfully.', 'data' => $userData]);

    } else {
        // Jika pengguna sudah ada, kirim data pengguna yang ada
        echo json_encode(['status' => 'success', 'message' => 'User logged in successfully.', 'data' => $userData]);
    }

} catch (Exception $e) {
    // Tangani error database atau error lainnya
    error_log("API connect_wallet error: " . $e->getMessage());
    send_error('An internal server error occurred.');
}
?>

