document.addEventListener("DOMContentLoaded", function() {
    const connectWalletBtn = document.getElementById('connectWalletBtn');
    const errorMessageDiv = document.getElementById('errorMessage');

    // --- Konfigurasi Jaringan BNB Smart Chain (Mainnet) ---
    const bscMainnet = {
        chainId: '0x38', // 56
        chainName: 'BNB Smart Chain',
        nativeCurrency: {
            name: 'BNB',
            symbol: 'BNB',
            decimals: 18,
        },
        rpcUrls: ['https://bsc-dataseed.binance.org/'],
        blockExplorerUrls: ['https://bscscan.com/'],
    };

    // Fungsi untuk menampilkan pesan error
    const showError = (message) => {
        errorMessageDiv.textContent = message;
        errorMessageDiv.style.display = 'block';
        connectWalletBtn.innerHTML = '<i class="fas fa-wallet btn-icon"></i> Connect Wallet';
        connectWalletBtn.disabled = false;
    };

    // Fungsi untuk melakukan registrasi/login ke backend
    const registerOrLogin = async (walletAddress, uplineAddress) => {
        try {
            const response = await fetch('api/connect_wallet.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    wallet_address: walletAddress,
                    upline_address: uplineAddress,
                }),
            });

            const result = await response.json();

            if (result.status === 'success') {
                sessionStorage.setItem('userWalletAddress', walletAddress);
                window.location.href = 'home.php';
            } else {
                showError(result.message || 'Registration failed.');
            }
        } catch (error) {
            console.error('Error during registration:', error);
            showError('An error occurred while communicating with the server.');
        }
    };

    // Fungsi untuk beralih atau menambahkan jaringan BSC
    const switchToBscNetwork = async (provider) => {
        try {
            await provider.send('wallet_switchEthereumChain', [{ chainId: bscMainnet.chainId }]);
            return true;
        } catch (switchError) {
            // Error ini (kode 4902) berarti chain belum ditambahkan ke MetaMask.
            if (switchError.code === 4902) {
                try {
                    await provider.send('wallet_addEthereumChain', [bscMainnet]);
                    return true;
                } catch (addError) {
                    console.error('Failed to add BSC network:', addError);
                    showError('Failed to add BNB Smart Chain network.');
                    return false;
                }
            }
            console.error('Failed to switch network:', switchError);
            showError('Failed to switch to BNB Smart Chain network. Please do it manually in MetaMask.');
            return false;
        }
    };

    // Fungsi utama untuk menghubungkan wallet
    const connectWallet = async () => {
        errorMessageDiv.style.display = 'none';
        connectWalletBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Connecting...';
        connectWalletBtn.disabled = true;

        if (typeof window.ethereum === 'undefined') {
            showError('Please install MetaMask!');
            return;
        }

        try {
            const provider = new ethers.providers.Web3Provider(window.ethereum, "any");
            
            // 1. Cek jaringan saat ini
            const network = await provider.getNetwork();
            if (network.chainId !== parseInt(bscMainnet.chainId, 16)) {
                const switched = await switchToBscNetwork(provider);
                if (!switched) return; // Hentikan proses jika gagal beralih jaringan
            }
            
            // 2. Minta koneksi akun setelah memastikan jaringan benar
            const accounts = await provider.send("eth_requestAccounts", []);
            const connectedAddress = accounts[0];
            
            if (connectedAddress) {
                const urlParams = new URLSearchParams(window.location.search);
                const uplineAddress = urlParams.get('ref');

                // 3. Lanjutkan ke backend
                await registerOrLogin(connectedAddress, uplineAddress);

            } else {
                showError('No account found. Please connect to MetaMask.');
            }
        } catch (error) {
            console.error('Wallet connection error:', error);
            let userMessage = 'Wallet connection was rejected.';
            if (error.code === 4001) {
                userMessage = 'You rejected the connection request.';
            }
            showError(userMessage);
        }
    };

    connectWalletBtn.addEventListener('click', connectWallet);
});

