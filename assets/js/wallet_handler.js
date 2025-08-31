document.addEventListener('DOMContentLoaded', async () => {
    // --- Elemen UI ---
    const preloader = document.querySelector('.preloader');
    const greetingWalletEl = document.getElementById('greetingWallet');
    const balanceAmountEl = document.getElementById('balanceAmount');
    const withdrawAmountInput = document.getElementById('withdrawAmount');
    const equivalentLangitEl = document.getElementById('equivalentLangit');
    const withdrawBtn = document.getElementById('withdrawBtn');
    const noWithdrawMessage = document.getElementById('noWithdrawMessage');
    const withdrawForm = document.getElementById('withdrawForm');

    // --- State Aplikasi ---
    let provider, signer, userAddress;
    let availableBalance = 0;
    let langitPrice = 0;
    let contracts = {};
    let blockchainConfig = {};

    // --- Sistem Notifikasi Kustom (diasumsikan sudah ada) ---
    const alertOverlay = document.getElementById('customAlert');
    const alertPopup = alertOverlay.querySelector('.custom-alert-popup');
    const alertIconEl = document.getElementById('alertIcon');
    const alertTitleEl = document.getElementById('alertTitle');
    const alertMessageEl = document.getElementById('alertMessage');
    const alertCloseBtn = document.getElementById('alertCloseBtn');
    const icons = {
        success: '<i class="fas fa-check-circle"></i>',
        error: '<i class="fas fa-times-circle"></i>',
        warning: '<i class="fas fa-exclamation-triangle"></i>'
    };
    const showCustomAlert = (title, message, type = 'success') => {
        alertIconEl.innerHTML = icons[type] || icons.success;
        alertTitleEl.textContent = title;
        alertMessageEl.textContent = message;
        alertPopup.className = 'custom-alert-popup ' + type;
        alertOverlay.classList.add('show');
    };
    alertCloseBtn.addEventListener('click', () => alertOverlay.classList.remove('show'));

    // --- Inisialisasi & Fungsi Helper ---
    const formatWalletAddress = (address) => address ? `${address.substring(0, 5)}...${address.substring(address.length - 4)}` : '';
    
    const loadConfig = async () => {
        try {
            const response = await fetch('blockchain_config.json');
            blockchainConfig = await response.json();
        } catch (e) {
            showCustomAlert("Configuration Error", "Could not load application settings.", "error");
        }
    };

    // --- Logika Utama ---
    const fetchWalletInfo = async () => {
        try {
            const response = await fetch('api/get_wallet_info.php');
            const result = await response.json();

            if (result.status === 'success') {
                const data = result.data;
                userAddress = data.wallet_address;
                availableBalance = parseFloat(data.withdrawable_balance_usdt);
                langitPrice = parseFloat(data.langit_price_usdt);

                greetingWalletEl.textContent = `Hello, ${formatWalletAddress(userAddress)}`;
                balanceAmountEl.textContent = `$ ${availableBalance.toLocaleString('en-US', {minimumFractionDigits: 4, maximumFractionDigits: 4})}`;

                // REVISI: Menggunakan flag baru dari backend
                if (data.is_eligible_for_withdraw) {
                    withdrawForm.classList.remove('d-none');
                    noWithdrawMessage.classList.add('d-none');
                } else {
                    withdrawForm.classList.add('d-none');
                    noWithdrawMessage.classList.remove('d-none');
                    // Ganti pesan menjadi lebih informatif
                    noWithdrawMessage.textContent = "You are not eligible for withdrawals. You must have at least Grade A and an active stake.";
                }
            } else {
                if (result.message.includes('authenticated')) window.location.href = 'index.php';
            }
        } catch (error) {
            console.error(error);
        } finally {
            preloader.classList.remove('show');
        }
    };
    
    const updateEquivalentLangit = () => {
        const amountUSDT = parseFloat(withdrawAmountInput.value) || 0;
        const equivalent = amountUSDT > 0 && langitPrice > 0 ? amountUSDT / langitPrice : 0;
        equivalentLangitEl.textContent = `You will receive ≈ ${equivalent.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})} LANGIT`;
        
        withdrawBtn.disabled = !(amountUSDT >= 5 && amountUSDT <= availableBalance);
    };

    const handleWithdraw = async () => {
        withdrawBtn.disabled = true;
        withdrawBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Preparing...';

        const amountUSDT = parseFloat(withdrawAmountInput.value);

        try {
            const prepareResponse = await fetch('api/prepare_withdraw.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ amount_usdt: amountUSDT })
            });
            const prepareResult = await prepareResponse.json();

            if (prepareResult.status !== 'success') throw new Error(prepareResult.message);

            const { stake_id_onchain, amount_in_langit_wei, is_burn, actual_withdraw_usdt } = prepareResult.data;

            withdrawBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Awaiting Confirmation...';
            if (!contracts.staking) {
                contracts.staking = new ethers.Contract(blockchainConfig.langitStaking.address, blockchainConfig.langitStaking.abi, signer);
            }
            const claimTx = await contracts.staking.claimLangit(stake_id_onchain, amount_in_langit_wei, is_burn);
            const receipt = await claimTx.wait();

            withdrawBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Finalizing...';
            const executeResponse = await fetch('api/execute_withdraw.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    stake_id_onchain,
                    amount_usdt: actual_withdraw_usdt,
                    tx_hash: receipt.transactionHash,
                    is_burn
                })
            });
            const executeResult = await executeResponse.json();

            if (executeResult.status !== 'success') throw new Error(executeResult.message);

            showCustomAlert("Withdrawal Success!", `You have successfully withdrawn $${actual_withdraw_usdt}. The LANGIT tokens are on their way to your wallet.`);
            setTimeout(() => window.location.reload(), 3000);

        } catch (error) {
            console.error("Withdrawal failed:", error);
            showCustomAlert("Withdrawal Failed", error.message, "error");
        } finally {
            withdrawBtn.disabled = false;
            withdrawBtn.innerHTML = 'Withdraw Funds';
        }
    };
    
    const initializeApp = async () => {
        await loadConfig();
        if (typeof window.ethereum !== 'undefined') {
            provider = new ethers.providers.Web3Provider(window.ethereum);
            signer = provider.getSigner();
        } else {
            showCustomAlert("MetaMask Not Found", "Please install MetaMask to use this DApp.", "error");
            preloader.classList.remove('show');
            return;
        }
        await fetchWalletInfo();
        
        withdrawAmountInput.addEventListener('input', updateEquivalentLangit);
        withdrawBtn.addEventListener('click', handleWithdraw);
    };

    initializeApp();
});

