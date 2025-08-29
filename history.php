<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>History - Langit Stacking+</title>

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
    <div class="preloader">
        <img src="assets/images/logo-langit.png" alt="Loading..." class="loader-logo">
    </div>

    <div class="container">
        <!-- Header -->
        <header class="d-flex justify-content-between align-items: center header">
            <div class="greeting-wallet">Hello, 0x123...abcd</div>
            <a href="#" class="disconnect-btn">
                <i class="fas fa-sign-out-alt"></i>
            </a>
        </header>

        <!-- Judul Halaman -->
        <h1 class="page-title">Transaction History</h1>

        <!-- Main Content -->
        <main>
            <!-- Filter Tabs -->
            <section class="history-tabs">
                <button class="btn history-tab-item active">All</button>
                <button class="btn history-tab-item">Staking</button>
                <button class="btn history-tab-item">Bonuses</button>
                <button class="btn history-tab-item">Withdrawals</button>
            </section>

            <!-- Transaction List -->
            <section class="summary-card">
                <div class="transaction-list">
                    <!-- Contoh 1: Bonus Masuk -->
                    <div class="transaction-item">
                        <div class="transaction-icon income"><i class="fas fa-arrow-down"></i></div>
                        <div class="transaction-details">
                            <div class="type">Matching Bonus</div>
                            <div class="date">Aug 28, 2025, 19:30</div>
                        </div>
                        <div class="transaction-amount income">+ $15.20</div>
                    </div>
                    <!-- Contoh 2: Penarikan -->
                    <div class="transaction-item">
                        <div class="transaction-icon outcome"><i class="fas fa-arrow-up"></i></div>
                        <div class="transaction-details">
                            <div class="type">Withdrawal</div>
                            <div class="date">Aug 27, 2025, 10:15</div>
                        </div>
                        <div class="transaction-amount outcome">- $50.00</div>
                    </div>
                    <!-- Contoh 3: Staking -->
                    <div class="transaction-item">
                        <div class="transaction-icon stake"><i class="fas fa-layer-group"></i></div>
                        <div class="transaction-details">
                            <div class="type">New Stake</div>
                            <div class="date">Aug 25, 2025, 08:00</div>
                        </div>
                        <div class="transaction-amount">- $100.00</div>
                    </div>
                     <!-- Contoh 4: ROI Masuk -->
                    <div class="transaction-item">
                        <div class="transaction-icon income"><i class="fas fa-arrow-down"></i></div>
                        <div class="transaction-details">
                            <div class="type">Staking ROI</div>
                            <div class="date">Aug 25, 2025, 07:30</div>
                        </div>
                        <div class="transaction-amount income">+ $1.50</div>
                    </div>
                </div>
            </section>
            
            <!-- Pagination -->
            <section class="pagination-container">
                <button class="btn pagination-btn" disabled><i class="fas fa-chevron-left"></i></button>
                <div class="pagination-info">Page 1 of 5</div>
                <button class="btn pagination-btn"><i class="fas fa-chevron-right"></i></button>
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
        <a href="network.php" class="nav-item">
            <i class="fas fa-sitemap nav-icon"></i>
            <span class="nav-label">Network</span>
        </a>
        <a href="wallet.php" class="nav-item">
            <i class="fas fa-wallet nav-icon"></i>
            <span class="nav-label">Wallet</span>
        </a>
        <a href="history.php" class="nav-item active">
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
