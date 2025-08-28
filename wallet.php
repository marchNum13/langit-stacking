<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wallet - Langit Stacking+</title>
    
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
        <header class="d-flex justify-content-between align-items-center header">
            <div class="greeting-wallet">Hello, 0x123...abcd</div>
            <a href="#" class="disconnect-btn">
                <i class="fas fa-sign-out-alt"></i>
            </a>
        </header>

        <!-- Judul Halaman -->
        <h1 class="page-title">Bonus Wallet</h1>

        <!-- Main Content -->
        <main>
            <!-- Kartu Saldo Utama -->
            <section class="balance-card">
                <div class="balance-label">Withdrawable Balance</div>
                <div class="balance-amount">$ 350.75</div>
            </section>

            <!-- Form Penarikan -->
            <section class="form-group-custom">
                <label for="withdrawAmount" class="form-label-custom">Withdrawal Amount (USDT)</label>
                <input type="number" class="form-control form-control-custom" id="withdrawAmount" placeholder="0.00">
                <div class="d-flex justify-content-between input-info">
                    <span>Min withdrawal: $5.00</span>
                    <span>You will receive ≈ 28,423 LANGIT</span>
                </div>
            </section>

            <!-- Tombol Aksi -->
            <section class="d-grid gap-2">
                <button class="btn cta-button">Withdraw Funds</button>
            </section>

            <!-- Informasi Penting -->
            <section class="info-box">
                Withdrawals will count towards your 500% profit cycle limit. You will receive the equivalent amount in LANGIT tokens based on the current market price.
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
        <a href="wallet.php" class="nav-item active">
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
