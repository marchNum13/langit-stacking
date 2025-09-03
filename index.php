<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Langit Staking+</title>

    <?php include "seo.php" ?>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Font Awesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">

</head>
<body class="page-landing">

    <!-- Preloader -->
    <div class="preloader">
        <img src="assets/images/logo-langit.png" alt="Loading..." class="loader-logo">
    </div>

    <div class="main-container">
        <div class="content-card">
            <img src="assets/images/logo-langit.png" alt="Langit Stacking+ Logo" class="logo-img">
            <h1 class="welcome-title">Langit Staking+</h1>
            <p class="short-description">A hybrid staking platform with active and passive earnings.</p>
            <button id="connectWalletBtn" class="btn connect-wallet-btn">
                <i class="fas fa-wallet btn-icon"></i>
                Connect Wallet
            </button>
            <div id="errorMessage" class="text-danger mt-3" style="display: none;"></div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Ethers.js CDN -->
    <script src="https://cdn.ethers.io/lib/ethers-5.2.umd.min.js" type="application/javascript"></script>
    <script src="https://cdn.jsdelivr.net/npm/ethers@5.7.2/dist/ethers.umd.min.js" type="application/javascript"></script>

    <!-- Custom Wallet Connector Script -->
    <script src="assets/js/wallet_connector.js"></script>
</body>
</html>