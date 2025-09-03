<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wallet - Langit Staking+</title>

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
        <!-- Header -->
        <header class="d-flex justify-content-between align-items-center header">
            <div id="greetingWallet" class="greeting-wallet">Loading...</div>
            <a href="#" class="disconnect-btn"><i class="fas fa-sign-out-alt"></i></a>
        </header>

        <h1 class="page-title">Bonus Wallet</h1>

        <main>
            <section class="balance-card">
                <div class="balance-label">Withdrawable Balance</div>
                <div id="balanceAmount" class="balance-amount">$ 0.00</div>
            </section>

            <!-- Pesan jika belum bisa withdraw -->
            <section id="noWithdrawMessage" class="info-box d-none">
                You must reach at least Grade A to be eligible for withdrawals.
            </section>
            
            <!-- Form Penarikan -->
            <div id="withdrawForm">
                <section class="form-group-custom">
                    <label for="withdrawAmount" class="form-label-custom">Withdrawal Amount (USDT)</label>
                    <input type="number" class="form-control form-control-custom" id="withdrawAmount" placeholder="0.00">
                    <div class="d-flex justify-content-between input-info">
                        <span>Min withdrawal: $5.00</span>
                        <span id="equivalentLangit">You will receive ≈ 0.00 LANGIT</span>
                    </div>
                </section>

                <section class="d-grid gap-2">
                    <button id="withdrawBtn" class="btn cta-button" disabled>Withdraw Funds</button>
                </section>

                <section class="info-box">
                    Withdrawals will count towards your 500% profit cycle limit. You will receive the equivalent amount in LANGIT tokens based on the current market price.
                </section>
            </div>
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

    <?php include "nav.php"; ?>

    <!-- JS Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/ethers/5.7.2/ethers.umd.min.js"></script>

    <!-- Custom JS -->
    <script src="assets/js/nav_handler.js"></script>
    <script src="assets/js/wallet_handler.js?v=<?php echo filemtime('assets/js/wallet_handler.js'); ?>"></script>

</body>
</html>
