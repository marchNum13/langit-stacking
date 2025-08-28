<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stake - Langit Stacking+</title>
    
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
        <!-- Header ditambahkan di sini -->
        <header class="d-flex justify-content-between align-items-center header">
            <div class="greeting-wallet">Hello, 0x123...abcd</div>
            <a href="#" class="disconnect-btn">
                <i class="fas fa-sign-out-alt"></i>
            </a>
        </header>

        <!-- Judul Halaman -->
        <h1 class="page-title">Start Staking</h1>

        <!-- Main Content -->
        <main>
            <!-- Input Jumlah Staking -->
            <section class="form-group-custom">
                <label for="stakeAmount" class="form-label-custom">Amount in Langit</label>
                <input type="number" class="form-control form-control-custom" id="stakeAmount" placeholder="0.00">
                <div class="d-flex justify-content-between input-info">
                    <span>Your Balance: 10,000 LANGIT</span>
                    <span>≈ $ 123.45 USDT</span>
                </div>
                 <div class="text-start input-info mt-1">
                    <span>1 LANGIT ≈ $0.01234 USDT</span>
                </div>
            </section>

            <!-- Pilihan Plan Kontrak -->
            <section class="form-group-custom plan-selection">
                <label class="form-label-custom">Select Contract Plan</label>
                <button class="btn plan-btn active">
                    <div class="plan-title">Flexible</div>
                    <div class="plan-roi">Daily ROI: 0.3% - 1.5%</div>
                </button>
                <button class="btn plan-btn">
                    <div class="plan-title">6 Months</div>
                    <div class="plan-roi">Daily ROI: 0.4% - 1.5%</div>
                </button>
                <button class="btn plan-btn">
                    <div class="plan-title">12 Months</div>
                    <div class="plan-roi">Daily ROI: 0.5% - 1.5%</div>
                </button>
            </section>

            <!-- Ringkasan Konfirmasi -->
            <section class="confirmation-summary mb-4">
                 <div class="summary-item d-flex justify-content-between align-items-center">
                    <span class="item-label">Stake Amount</span>
                    <span class="item-value">10,000 LANGIT</span>
                </div>
                 <div class="summary-item d-flex justify-content-between align-items-center">
                    <span class="item-label">Current Value</span>
                    <span class="item-value">~ $123.45 USDT</span>
                </div>
                 <div class="summary-item d-flex justify-content-between align-items-center">
                    <span class="item-label">Selected Plan</span>
                    <span class="item-value">Flexible</span>
                </div>
            </section>

            <!-- Tombol Aksi -->
            <section class="d-grid gap-2 mb-4">
                <button class="btn cta-button">Confirm Stake</button>
            </section>
        </main>
    </div>

    <!-- Navigasi Bawah -->
    <nav class="bottom-nav">
        <a href="home.php" class="nav-item">
            <i class="fas fa-home nav-icon"></i>
            <span class="nav-label">Home</span>
        </a>
        <a href="stake.php" class="nav-item active">
            <i class="fas fa-layer-group nav-icon"></i>
            <span class="nav-label">Stake</span>
        </a>
        <a href="network.php" class="nav-item">
            <i class="fas fa-sitemap nav-icon"></i>
            <span class="nav-label">Network</span>
        </a>
        <a href="wallet.php" class="nav-item">
            <i class="fas fa-wallet nav-icon"></i>
            <span class="nav-label">Wallet</span>
        </a>
        <a href="history.php" class="nav-item">
            <i class="fas fa-history nav-icon"></i>
            <span class="nav-label">History</span>
        </a>
    </nav>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
