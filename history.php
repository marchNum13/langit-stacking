<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>History - Langit Stacking+</title>

    <?php include "seo.php"; ?>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="page-app">

    <div class="preloader show"> <!-- Tampilkan preloader secara default -->
        <img src="assets/images/logo-langit.png" alt="Loading..." class="loader-logo">
    </div>

    <div class="container">
        <!-- Header (Akan diisi oleh JS nanti) -->
        <header class="d-flex justify-content-between align-items-center header">
            <div id="greetingWallet" class="greeting-wallet">Loading...</div>
            <a href="#" class="disconnect-btn"><i class="fas fa-sign-out-alt"></i></a>
        </header>

        <h1 class="page-title">Transaction History</h1>

        <main>
            <!-- Filter Tabs dengan data-filter -->
            <section class="history-tabs">
                <button class="btn history-tab-item active" data-filter="all">All</button>
                <button class="btn history-tab-item" data-filter="stake">Staking</button>
                <button class="btn history-tab-item" data-filter="bonus">Bonuses</button>
                <button class="btn history-tab-item" data-filter="withdraw">Withdrawals</button>
                <button class="btn history-tab-item" data-filter="claim_vesting">Vesting</button>
            </section>

            <!-- Kontainer untuk daftar transaksi (akan diisi oleh JS) -->
            <section class="summary-card">
                <div id="transactionList" class="transaction-list">
                    <!-- Konten dinamis akan dimuat di sini -->
                </div>
            </section>
            
            <!-- Paginasi -->
            <section class="pagination-container">
                <button id="prevPageBtn" class="btn pagination-btn" disabled><i class="fas fa-chevron-left"></i></button>
                <div id="paginationInfo" class="pagination-info">Page 1 of 1</div>
                <button id="nextPageBtn" class="btn pagination-btn" disabled><i class="fas fa-chevron-right"></i></button>
            </section>
        </main>
    </div>

    <!-- Panggil nav.php -->
    <?php include "nav.php"; ?>

    <!-- Panggil file JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/history.js?v=<?php echo filemtime('assets/js/history.js'); ?>"></script>
    <script src="assets/js/nav_handler.js?v=<?php echo filemtime('assets/js/nav_handler.js'); ?>"></script>

</body>
</html>
