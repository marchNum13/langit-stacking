<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Network - Langit Staking+</title>

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

        <h1 class="page-title">My Network</h1>

        <main>
            <!-- PENAMBAHAN: Journey to Grade -->
            <section class="summary-card mb-4">
                <h2 id="journeyTitle" class="section-title">Loading Journey...</h2>
                <div class="d-flex justify-content-between align-items-center mb-2 text-secondary small">
                    <span id="journeyCurrent">Turnover: $0.00</span>
                    <span id="journeyTarget">Target: $0.00</span>
                </div>
                <div class="progress-bar-custom">
                    <div id="journeyProgress" class="progress-fill" style="width: 0%;"></div>
                </div>
            </section>

            <!-- Network Stat Cards -->
            <section class="mb-4">
                <div class="row g-3">
                    <div class="col-6">
                        <div class="stat-card text-center">
                            <div class="stat-title">Direct</div>
                            <div id="directDownlinesCount" class="stat-value">0</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="stat-card text-center">
                            <div class="stat-title">Total Groups</div>
                            <div id="totalTeamMembers" class="stat-value">0</div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Referral Link -->
            <section class="mb-4">
                <div class="referral-card">
                    <div id="referralLink" class="referral-link">Loading...</div>
                    <button id="copyBtn" class="btn copy-btn"><i class="fas fa-copy me-1"></i> Copy</button>
                </div>
            </section>

            <!-- Direct Downlines List -->
            <section id="downlineListContainer" class="downline-list">
                <h2 class="section-title">Direct</h2>
                <div id="downlineList">
                    <!-- Konten dinamis akan dimuat di sini -->
                </div>
            </section>
        </main>
    </div>

    <?php include "nav.php"; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/nav_handler.js"></script>
    <script src="assets/js/network.js?v=<?php echo filemtime('assets/js/network.js'); ?>"></script>

</body>
</html>

