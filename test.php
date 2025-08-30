<?php
// Mengatur header untuk menampilkan output sebagai teks biasa agar mudah dibaca
header('Content-Type: text/plain');

// Sertakan kelas MarketPriceFetcher
require_once __DIR__ . '/utils/MarketPriceFetcher.php';

echo "Memulai Tes Pengambilan Harga Pasar...\n\n";

// Buat instance dari kelas
$priceFetcher = new MarketPriceFetcher();

// Panggil metode untuk mendapatkan harga
$price = $priceFetcher->getLangitPriceInUsdt();

// Periksa dan tampilkan hasilnya
if ($price !== null) {
    echo "SUKSES!\n";
    echo "Harga LANGIT saat ini adalah: $" . $price . " USDT\n";
    echo "\nCatatan: Jika Anda menjalankan ini beberapa kali dalam 5 menit, harga mungkin akan sama karena diambil dari cache.\n";
} else {
    echo "GAGAL!\n";
    echo "Tidak dapat mengambil harga pasar. Silakan periksa log error server (error_log) untuk detail lebih lanjut.\n";
    echo "Kemungkinan penyebab: \n";
    echo "1. Alamat pair di DexScreener salah.\n";
    echo "2. Tidak ada koneksi internet dari server.\n";
    echo "3. API DexScreener sedang tidak aktif atau memblokir request.\n";
    echo "4. Folder 'cache' tidak dapat dibuat atau ditulis (permission issue).\n";
}

?>
