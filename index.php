<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Langit Stacking+</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Google Fonts (Inter) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Font Awesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">

</head>
<body class="page-landing">

    <div class="main-container">
        <div class="content-card">
            <!-- Logo -->
            <img src="assets/images/logo-langit.png" alt="Langit Stacking+ Logo" class="logo-img">

            <!-- Judul Selamat Datang -->
            <h1 class="welcome-title">Langit Stacking+</h1>
            
            <!-- Deskripsi Singkat -->
            <p class="short-description">A hybrid staking platform with active and passive earnings.</p>
            
            <!-- Tombol Aksi Utama -->
            <button id="connectWalletBtn" class="btn connect-wallet-btn">
                <i class="fas fa-wallet btn-icon"></i>
                Connect Wallet
            </button>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Menambahkan event listener ke tombol
        const connectWalletBtn = document.getElementById('connectWalletBtn');

        // Fungsi placeholder untuk menghubungkan wallet
        function connectWallet() {
            console.log('Attempting to connect wallet...');
            
            // Simulasi loading
            connectWalletBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Connecting...';
            connectWalletBtn.disabled = true;

            // Setelah 2 detik, arahkan ke halaman home.html
            setTimeout(() => {
                window.location.href = 'home.php';
            }, 2000);
        }

        connectWalletBtn.addEventListener('click', connectWallet);
    </script>
</body>
</html>
