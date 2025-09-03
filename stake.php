<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stake - Langit Staking+</title>

    <?php include "seo.php" ?>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="page-app">

    <div class="preloader show">
        <img src="assets/images/logo-langit.png" alt="Loading..." class="loader-logo">
    </div>

    <div class="container">
        <header class="d-flex justify-content-between align-items-center header">
            <div id="greetingWallet" class="greeting-wallet">Loading...</div>
            <a href="#" id="disconnectBtn" class="disconnect-btn">
                <i class="fas fa-sign-out-alt"></i>
            </a>
        </header>

        <main>
            <!-- =========================================================== -->
            <!-- Tampilan #1: Form Untuk Staking Baru (Defaultnya terlihat)  -->
            <!-- =========================================================== -->
            <div id="newStakeView">
                <h1 class="page-title">Start Staking</h1>
                
                <section class="form-group-custom">
                    <label for="stakeAmount" class="form-label-custom">Amount in Langit</label>
                    <input type="number" class="form-control form-control-custom" id="stakeAmount" placeholder="0.00">
                    <div class="d-flex justify-content-between input-info">
                        <span id="langitBalance">Your Balance: 0.00 LANGIT</span>
                        <span id="usdtValue">≈ $ 0.00 USDT</span>
                    </div>
                     <div class="text-start input-info mt-1">
                        <span id="langitPrice">1 LANGIT ≈ $0.00 USDT</span>
                    </div>
                </section>

                <section class="form-group-custom plan-selection">
                    <label class="form-label-custom">Select Contract Plan</label>
                    <button class="btn plan-btn active" data-plan="flexible">
                        <div class="plan-title">Flexible</div>
                        <div class="plan-roi">Daily Reward: 0.3% - 1.5%</div>
                    </button>
                    <button class="btn plan-btn" data-plan="6_months">
                        <div class="plan-title">6 Months</div>
                        <div class="plan-roi">Daily Reward: 0.4% - 1.5%</div>
                    </button>
                    <button class="btn plan-btn" data-plan="12_months">
                        <div class="plan-title">12 Months</div>
                        <div class="plan-roi">Daily Reward: 0.5% - 1.5%</div>
                    </button>
                </section>

                <section class="confirmation-summary mb-4">
                     <div class="summary-item d-flex justify-content-between align-items-center">
                        <span class="item-label">Stake Amount</span>
                        <span id="summaryAmountLangit" class="item-value">0.00 LANGIT</span>
                    </div>
                     <div class="summary-item d-flex justify-content-between align-items-center">
                        <span class="item-label">Current Value</span>
                        <span id="summaryAmountUsdt" class="item-value">~ $0.00 USDT</span>
                    </div>
                     <div class="summary-item d-flex justify-content-between align-items-center">
                        <span class="item-label">Selected Plan</span>
                        <span id="summaryPlan" class="item-value">Flexible</span>
                    </div>
                </section>

                <section class="d-grid gap-2 mb-4">
                    <button id="stakeBtn" class="btn cta-button" disabled>Confirm Stake</button>
                </section>
            </div>

            <!-- =========================================================== -->
            <!-- Tampilan #2: Manajemen Stake Aktif (Defaultnya tersembunyi) -->
            <!-- =========================================================== -->
            <div id="manageStakeView" class="d-none">
                <h1 class="page-title">Manage Your Stake</h1>

                <section class="summary-card mb-4">
                    <div class="summary-item d-flex justify-content-between align-items-center">
                        <span class="item-label">Current Plan</span>
                        <span id="activePlan" class="item-value">Loading...</span>
                    </div>
                    <div class="summary-item d-flex justify-content-between align-items-center">
                        <span class="item-label">Staked Amount</span>
                        <div>
                            <span id="activeAmountLangit" class="item-value d-block text-end">0.00 LANGIT</span>
                            <span id="activeAmountUsdt" class="text-secondary small d-block text-end">~ $0.00 USDT</span>
                        </div>
                    </div>
                    <div class="summary-item d-flex justify-content-between align-items-center">
                        <span class="item-label">Staking Date</span>
                        <span id="activeStartDate" class="item-value">Loading...</span>
                    </div>
                </section>

                <section class="summary-card mb-4">
                    <h2 class="section-title">Staking Profit Cycle</h2>
                    <div class="d-flex justify-content-between align-items-center mb-2 text-secondary small">
                        <span id="activeProfitAchieved">Achieved: $0.00</span>
                        <span id="activeProfitMax">Max: $0.00 (500%)</span>
                    </div>
                    <div class="progress-bar-custom">
                        <div id="activeProfitProgress" class="progress-fill" style.width="0%;"></div>
                    </div>
                </section>

                <section class="d-grid gap-2 mb-4">
                    <button id="unstakeBtn" class="btn cta-button btn-danger">Unstake Now</button>
                </section>
            </div>

        </main>
    </div>

    <!-- PENAMBAHAN BARU: Struktur HTML untuk Notifikasi Pop-up -->
    <div id="customAlert" class="custom-alert-overlay">
        <div class="custom-alert-popup">
            <div id="alertIcon" class="alert-icon">
                <!-- Ikon akan diisi oleh JavaScript -->
            </div>
            <h3 id="alertTitle" class="alert-title"></h3>
            <p id="alertMessage" class="alert-message"></p>
            <button id="alertCloseBtn" class="btn alert-close-btn">Close</button>
        </div>
    </div>

    <?php include "nav.php"; ?>

    <!-- JS Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/ethers/5.7.2/ethers.umd.min.js"></script>
    
    <!-- Custom JS -->
    <script src="assets/js/nav_handler.js"></script>
    <script src="assets/js/stake_handler.js?v=<?php echo filemtime('assets/js/stake_handler.js'); ?>"></script>
    <!-- <script src="assets/js/stake_handler.js"></script> -->

</body>
</html>

