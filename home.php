<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Langit Staking+</title>

    <?php include "seo.php" ?>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Google Fonts (Space Grotesk) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="page-app">

    <!-- Preloader -->
    <div class="preloader show"> <!-- Tampilkan secara default, JS akan menyembunyikan -->
        <img src="assets/images/logo-langit.png" alt="Loading..." class="loader-logo">
    </div>

    <div class="container">
        <!-- Header -->
        <header class="d-flex justify-content-between align-items-center header">
            <!-- PENAMBAHAN ID -->
            <div id="greetingWallet" class="greeting-wallet">Loading...</div>
            <a href="#" id="disconnectBtn" class="disconnect-btn">
                <i class="fas fa-sign-out-alt"></i>
            </a>
        </header>

        <!-- Main Content -->
        <main>
            <!-- Kartu Statistik Utama -->
            <section class="mb-4">
                <div class="row g-3">
                    <div class="col-6">
                        <div class="stat-card">
                            <div class="stat-title">Total Active Stake</div>
                            <!-- PENAMBAHAN ID -->
                            <div id="totalActiveStake" class="stat-value">$ 0.00</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="stat-card">
                            <div class="stat-title">Total Earnings</div>
                            <!-- PENAMBAHAN ID -->
                            <div id="totalEarnings" class="stat-value">$ 0.00</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="stat-card">
                            <div class="stat-title">Your Grade</div>
                            <!-- PENAMBAHAN ID -->
                            <div id="userGrade" class="stat-value"><i class="fas fa-star"></i> Grade N/A</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="stat-card">
                            <div class="stat-title">Network Turnover</div>
                            <!-- PENAMBAHAN ID -->
                            <div id="networkTurnover" class="stat-value">$ 0.00</div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Tombol Aksi Utama (CTA) -->
            <section class="d-grid gap-2 mb-4">
                <a href="stake.php" class="btn cta-button"><i class="fas fa-rocket me-2"></i> Stake Now!</a>
            </section>

            <!-- Ringkasan Pendapatan -->
            <section class="mb-4">
                <div class="summary-card">
                    <h2 class="section-title">Your Earnings Breakdown</h2>
                    <div class="summary-item d-flex justify-content-between align-items-center">
                        <span class="item-label">Staking Reward</span>
                        <!-- PENAMBAHAN ID -->
                        <span id="stakingRoi" class="item-value">$ 0.00</span>
                    </div>
                    <div class="summary-item d-flex justify-content-between align-items-center">
                        <span class="item-label">Matching Reward</span>
                        <!-- PENAMBAHAN ID -->
                        <span id="matchingBonus" class="item-value">$ 0.00</span>
                    </div>
                    <div class="summary-item d-flex justify-content-between align-items-center">
                        <span class="item-label">Royalty Reward</span>
                        <!-- PENAMBAHAN ID -->
                        <span id="royaltyBonus" class="item-value">$ 0.00</span>
                    </div>
                </div>
            </section>
            
            <!-- Progress Bar Batas Penarikan -->
            <section class="mb-4">
                <div class="summary-card">
                    <h2 class="section-title">Staking Profit Cycle</h2>
                    <div class="d-flex justify-content-between align-items-center mb-2 text-secondary small">
                        <!-- PENAMBAHAN ID -->
                        <span id="profitCycleAchieved">Achieved: $0.00</span>
                        <span id="profitCycleMax">Max: $0.00 (500%)</span>
                    </div>
                    <div class="progress-bar-custom">
                        <!-- PENAMBAHAN ID -->
                        <div id="profitCycleProgress" class="progress-fill" style="width: 0%;"></div>
                    </div>
                </div>
            </section>

            <section id="vestingClaimsContainer" class="mb-4">
                <!-- Konten akan diisi oleh dashboard.js -->
            </section>
        </main>
    </div>

    <!-- Pop-up Notifikasi -->
    <div id="customAlert" class="custom-alert-overlay">
        <div class="custom-alert-popup">
            <div id="alertIcon" class="alert-icon"></div>
            <h3 id="alertTitle" class="alert-title"></h3>
            <p id="alertMessage" class="alert-message"></p>
            <button id="alertCloseBtn" class="btn alert-close-btn">Close</button>
        </div>
    </div>


    <!-- Panggil nav.php -->
    <?php include "nav.php"; ?>

    <!-- JS Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/ethers/5.7.2/ethers.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/ethers@5.7.2/dist/ethers.umd.min.js" type="application/javascript"></script>

    <!-- Panggil file JS kustom kita -->
    <script src="assets/js/nav_handler.js"></script>
    <script src="assets/js/dashboard.js?v=<?php echo filemtime('assets/js/dashboard.js'); ?>"></script>

</body>
</html>

