<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Network - Langit Stacking+</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Google Fonts (Space Grotesk) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="page-app">

    <div class="container">
        <!-- Header -->
        <header class="d-flex justify-content-between align-items-center header">
            <div class="greeting-wallet">Hello, 0x123...abcd</div>
            <a href="#" class="disconnect-btn">
                <i class="fas fa-sign-out-alt"></i>
            </a>
        </header>

        <!-- Judul Halaman -->
        <h1 class="page-title">My Network</h1>

        <!-- Main Content -->
        <main>
            <!-- Grade Progress Bar -->
            <section class="summary-card mb-4">
                <h2 class="section-title">Journey to Grade E</h2>
                <div class="d-flex justify-content-between align-items-center mb-2 text-secondary small">
                    <span>Turnover: $1,650</span>
                    <span>Target: $5,000</span>
                </div>
                <div class="progress-bar-custom">
                    <div class="progress-fill" style="width: 33%;"></div>
                </div>
            </section>

            <!-- Network Stat Cards -->
            <section class="mb-4">
                <div class="row g-3">
                    <div class="col-6">
                        <div class="stat-card text-center">
                            <div class="stat-title">Direct Downlines</div>
                            <div class="stat-value">5</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="stat-card text-center">
                            <div class="stat-title">Total Team Members</div>
                            <div class="stat-value">25</div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Referral Link -->
            <section class="mb-4">
                <div class="referral-card">
                    <div class="referral-link">langit.plus/ref/0x123...</div>
                    <button class="btn copy-btn"><i class="fas fa-copy me-1"></i> Copy</button>
                </div>
            </section>

            <!-- Direct Downlines List -->
            <section class="downline-list">
                <h2 class="section-title">Direct Downlines</h2>
                <div class="downline-item">
                    <div>
                        <div class="wallet-address">0xabc...def</div>
                        <div class="turnover-label">Total Turnover</div>
                    </div>
                    <div class="text-end">
                        <div class="turnover-amount">$ 550.00</div>
                    </div>
                </div>
                <div class="downline-item">
                    <div>
                        <div class="wallet-address">0x1a2...b3c</div>
                        <div class="turnover-label">Total Turnover</div>
                    </div>
                    <div class="text-end">
                        <div class="turnover-amount">$ 400.00</div>
                    </div>
                </div>
                 <div class="downline-item">
                    <div>
                        <div class="wallet-address">0x4d5...e6f</div>
                        <div class="turnover-label">Total Turnover</div>
                    </div>
                    <div class="text-end">
                        <div class="turnover-amount">$ 350.00</div>
                    </div>
                </div>
            </section>

        </main>
    </div>

    <!-- Navigasi Bawah -->
    <nav class="bottom-nav">
        <a href="home.php" class="nav-item">
            <i class="fas fa-home nav-icon"></i>
            <span class="nav-label">Home</span>
        </a>
        <a href="stake.php" class="nav-item">
            <i class="fas fa-layer-group nav-icon"></i>
            <span class="nav-label">Stake</span>
        </a>
        <a href="network.php" class="nav-item active">
            <i class="fas fa-sitemap nav-icon"></i>
            <span class="nav-label">Network</span>
        </a>
        <a href="wallet.php" class="nav-item">
            <i class="fas fa-wallet nav-icon"></i>
            <span class="nav-label">Wallet</span>
        </a>
        <a href="#" class="nav-item">
            <i class="fas fa-history nav-icon"></i>
            <span class="nav-label">History</span>
        </a>
    </nav>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
