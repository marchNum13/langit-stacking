<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Langit Stacking+</title>

    <?php include "seo.php" ?>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Google Fonts (Space Grotesk) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<!-- Diubah dari page-dashboard menjadi page-app untuk konsistensi -->
<body class="page-app">

    <!-- Preloader -->
    <div class="preloader">
        <img src="assets/images/logo-langit.png" alt="Loading..." class="loader-logo">
    </div>

    <div class="container">
        <!-- Header -->
        <header class="d-flex justify-content-between align-items-center header">
            <div class="greeting-wallet">Hello, 0x123...abcd</div>
            <a href="#" class="disconnect-btn">
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
                            <div class="stat-value">$ 1,500.00</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="stat-card">
                            <div class="stat-title">Total Earnings</div>
                            <div class="stat-value">$ 750.50</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="stat-card">
                            <div class="stat-title">Your Grade</div>
                            <div class="stat-value"><i class="fas fa-star"></i> Grade D</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="stat-card">
                            <div class="stat-title">Network Turnover</div>
                            <div class="stat-value">$ 1,650.00</div>
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
                        <span class="item-label">Staking ROI</span>
                        <span class="item-value">$ 450.00</span>
                    </div>
                    <div class="summary-item d-flex justify-content-between align-items-center">
                        <span class="item-label">Matching Bonus</span>
                        <span class="item-value">$ 300.50</span>
                    </div>
                    <div class="summary-item d-flex justify-content-between align-items-center">
                        <span class="item-label">Royalty Bonus</span>
                        <span class="item-value">$ 0.00</span>
                    </div>
                </div>
            </section>
            
            <!-- Progress Bar Batas Penarikan -->
            <section class="mb-4">
                <div class="summary-card">
                    <h2 class="section-title">Staking Profit Cycle</h2>
                    <div class="d-flex justify-content-between align-items-center mb-2 text-secondary small">
                        <span>Achieved: $750.50</span>
                        <span>Max: $7,500.00 (500%)</span>
                    </div>
                    <div class="progress-bar-custom">
                        <div class="progress-fill" style="width: 10%;"></div>
                    </div>
                </div>
            </section>

            <!-- Bagian Baru: Klaim Vesting -->
            <section class="mb-4">
                <div class="summary-card vesting-card">
                    <h2 class="section-title">Vesting Claims</h2>
                    <div class="summary-item d-flex justify-content-between align-items-center">
                        <span class="item-label">Total Unstaked</span>
                        <span class="item-value">300.00 LANGIT</span>
                    </div>
                    <div class="d-grid gap-2">
                        <button class="btn cta-button claim-button">Claim Vested Tokens</button>
                    </div>
                </div>
            </section>

        </main>
    </div>

    <!-- Navigasi Bawah -->
    <nav class="bottom-nav">
        <a href="home.php" class="nav-item active">
            <i class="fas fa-home nav-icon"></i>
            <span class="nav-label">Home</span>
        </a>
        <a href="stake.php" class="nav-item">
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

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const preloader = document.querySelector('.preloader');
            const navLinks = document.querySelectorAll('.nav-item');

            // Sembunyikan preloader saat halaman selesai dimuat
            preloader.classList.remove('show');

            // Tambahkan event listener ke semua link navigasi
            navLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault(); // Mencegah perpindahan halaman instan
                    const destination = this.href;

                    // Tampilkan preloader
                    preloader.classList.add('show');

                    // Tunggu sebentar lalu pindah halaman
                    setTimeout(() => {
                        window.location.href = destination;
                    }, 500); // 0.5 detik untuk efek transisi
                });
            });

            // Handle back/forward browser
            window.addEventListener('pageshow', function(event) {
                if (event.persisted) {
                    preloader.classList.remove('show');
                }
            });
        });
    </script>
</body>
</html>
