document.addEventListener('DOMContentLoaded', async () => {
    // --- Elemen UI ---
    const preloader = document.querySelector('.preloader');
    const greetingWalletEl = document.getElementById('greetingWallet');
    const disconnectBtn = document.getElementById('disconnectBtn');

    // Tampilan Staking Baru
    const newStakeView = document.getElementById('newStakeView');
    const stakeAmountInput = document.getElementById('stakeAmount');
    const langitBalanceEl = document.getElementById('langitBalance');
    const usdtValueEl = document.getElementById('usdtValue');
    const langitPriceEl = document.getElementById('langitPrice');
    const planButtons = document.querySelectorAll('.plan-btn');
    const summaryAmountLangit = document.getElementById('summaryAmountLangit');
    const summaryAmountUsdt = document.getElementById('summaryAmountUsdt');
    const summaryPlan = document.getElementById('summaryPlan');
    const stakeBtn = document.getElementById('stakeBtn');

    // Tampilan Manajemen Stake
    const manageStakeView = document.getElementById('manageStakeView');
    const activePlanEl = document.getElementById('activePlan');
    const activeAmountLangitEl = document.getElementById('activeAmountLangit');
    const activeAmountUsdtEl = document.getElementById('activeAmountUsdt');
    const activeStartDateEl = document.getElementById('activeStartDate');
    const activeProfitAchievedEl = document.getElementById('activeProfitAchieved');
    const activeProfitMaxEl = document.getElementById('activeProfitMax');
    const activeProfitProgressEl = document.getElementById('activeProfitProgress');
    const unstakeBtn = document.getElementById('unstakeBtn');

    // --- State Aplikasi ---
    let provider, signer, userAddress;
    let langitPrice = 0;
    let selectedPlan = 'flexible';
    let contracts = {};
    let blockchainConfig = {};
    let activeStakeData = null;

    // --- Sistem Notifikasi Kustom ---
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
    alertOverlay.addEventListener('click', (e) => {
        if (e.target === alertOverlay) {
            alertOverlay.classList.remove('show');
        }
    });

    // --- Inisialisasi & Fungsi Helper ---

    const formatWalletAddress = (address) => {
        if (!address) return 'Loading...';
        return `${address.substring(0, 5)}...${address.substring(address.length - 4)}`;
    };
    
    const loadConfig = async () => {
        try {
            const response = await fetch('blockchain_config.json');
            if (!response.ok) throw new Error("blockchain_config.json not found");
            blockchainConfig = await response.json();
        } catch (error) {
            console.error(error);
            showCustomAlert("Configuration Error", "Could not load essential application settings. Please try refreshing the page.", "error");
        }
    };

    // --- Logika Utama ---

    const fetchPageInfo = async () => {
        try {
            const response = await fetch('api/get_stake_page_info.php');
            const result = await response.json();

            if (result.status === 'success') {
                const data = result.data;
                userAddress = data.wallet_address;
                langitPrice = parseFloat(data.langit_price_usdt);

                greetingWalletEl.textContent = `Hello, ${formatWalletAddress(userAddress)}`;
                
                if (data.has_active_stake) {
                    activeStakeData = data.active_stake_details;
                    displayManageStakeView(activeStakeData);
                } else {
                    await initializeNewStakeView();
                }
            } else {
                if (result.message.includes('authenticated')) window.location.href = 'index.php';
            }
        } catch (error) {
            console.error("Error fetching page info:", error);
        } finally {
            preloader.classList.remove('show');
        }
    };

    const initializeNewStakeView = async () => {
        newStakeView.classList.remove('d-none');
        manageStakeView.classList.add('d-none');

        langitPriceEl.textContent = `1 LANGIT ≈ $${langitPrice.toFixed(6)} USDT`;
        
        if (!blockchainConfig.langitToken) {
             console.error("Blockchain config not loaded correctly.");
             return;
        }

        contracts.langit = new ethers.Contract(blockchainConfig.langitToken.address, blockchainConfig.langitToken.abi, signer);
        
        try {
            const balance = await contracts.langit.balanceOf(userAddress);
            const formattedBalance = ethers.utils.formatUnits(balance, 18);
            langitBalanceEl.textContent = `Your Balance: ${parseFloat(formattedBalance).toLocaleString()} LANGIT`;
        } catch (e) {
            console.error("Could not get LANGIT balance:", e);
            langitBalanceEl.textContent = 'Your Balance: Error';
        }
        updateSummary();
    };

    const displayManageStakeView = (details) => {
        newStakeView.classList.add('d-none');
        manageStakeView.classList.remove('d-none');
        
        activePlanEl.textContent = details.plan;
        activeAmountLangitEl.textContent = `${details.amount_langit} LANGIT`;
        activeAmountUsdtEl.textContent = `~ $${details.amount_usdt_initial} USDT`;
        activeStartDateEl.textContent = details.start_date;
        
        activeProfitAchievedEl.textContent = `Achieved: $${details.profit_cycle.achieved}`;
        activeProfitMaxEl.textContent = `Max: $${details.profit_cycle.max} (500%)`;
        activeProfitProgressEl.style.width = `${details.profit_cycle.percentage}%`;
        
        if (details.plan !== 'Flexible' && details.expires_at && new Date() < new Date(details.expires_at)) {
            unstakeBtn.disabled = true;
            unstakeBtn.textContent = `Locked until ${details.expires_at}`;
        } else {
            unstakeBtn.disabled = false;
            unstakeBtn.textContent = 'Unstake Now';
        }
    };

    const updateSummary = () => {
        const amount = parseFloat(stakeAmountInput.value) || 0;
        const usdtValue = amount * langitPrice;

        usdtValueEl.textContent = `≈ $ ${usdtValue.toLocaleString('en-US', { minimumFractionDigits: 4, maximumFractionDigits: 4 })} USDT`;
        summaryAmountLangit.textContent = `${amount.toLocaleString()} LANGIT`;
        summaryAmountUsdt.textContent = `~ $${usdtValue.toLocaleString('en-US', { minimumFractionDigits: 4, maximumFractionDigits: 4 })} USDT`;
        summaryPlan.textContent = selectedPlan.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase());

        // Logika validasi tombol stake yang baru
        let minStake = 10;
        if (selectedPlan === '6_months' || selectedPlan === '12_months') {
            minStake = 50;
        }

        stakeBtn.disabled = usdtValue < minStake;
    };
    
    // --- Proses Staking & Unstaking ---

    const handleStake = async () => {
        stakeBtn.disabled = true;
        stakeBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Processing...';
        
        try {
            const amount = stakeAmountInput.value;
            if (!amount || parseFloat(amount) <= 0) {
                throw new Error("Invalid amount. Please enter a number greater than zero.");
            }
            const amountInWei = ethers.utils.parseUnits(amount, 18);

            if (!contracts.staking) {
                contracts.staking = new ethers.Contract(blockchainConfig.langitStaking.address, blockchainConfig.langitStaking.abi, signer);
            }
            if (!contracts.langit) {
                 contracts.langit = new ethers.Contract(blockchainConfig.langitToken.address, blockchainConfig.langitToken.abi, signer);
            }

            stakeBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Awaiting Approval...';
            const approveTx = await contracts.langit.approve(contracts.staking.address, amountInWei);
            await approveTx.wait();

            const stakeId = ethers.utils.keccak256(ethers.utils.toUtf8Bytes(userAddress + amount + Date.now()));
            
            stakeBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Awaiting Staking...';
            const stakeTx = await contracts.staking.stake(stakeId, amountInWei);
            const receipt = await stakeTx.wait();

            stakeBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Syncing...';
            await syncStakeToBackend(stakeId, amount, receipt.transactionHash);
            
            showCustomAlert("Success!", "Your tokens have been staked successfully. You will be redirected shortly.");
            setTimeout(() => window.location.reload(), 3000);

        } catch (error) {
            console.error("Staking process failed:", error);
            let userMessage = "An unexpected error occurred. Please check your wallet and try again.";
            if (error.code === 4001) {
                userMessage = "The transaction was rejected. Please try again if you wish to proceed.";
            } else if (error.message.includes("insufficient funds")) {
                userMessage = "You have insufficient funds to complete this transaction.";
            }
            showCustomAlert("Transaction Failed", userMessage, "error");
        } finally {
            stakeBtn.disabled = false;
            stakeBtn.textContent = 'Confirm Stake';
        }
    };

    const syncStakeToBackend = async (stakeId, amount, txHash) => {
        const payload = {
            stake_id_onchain: stakeId,
            plan: selectedPlan,
            amount_langit: amount,
            tx_hash: txHash
        };
        const response = await fetch('api/execute_stake.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        const result = await response.json();
        if (result.status !== 'success') {
            throw new Error('Backend sync failed: ' + result.message);
        }
    };

    const handleUnstake = async () => {
        unstakeBtn.disabled = true;
        unstakeBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Processing...';

        try {
            if (!activeStakeData) throw new Error("No active stake data found.");

            const stakeIdOnchain = activeStakeData.stake_id_onchain; 
            if(!stakeIdOnchain) throw new Error("Stake ID is missing.");

            if (!contracts.staking) {
                contracts.staking = new ethers.Contract(blockchainConfig.langitStaking.address, blockchainConfig.langitStaking.abi, signer);
            }

            const unstakeTx = await contracts.staking.unstake(stakeIdOnchain);
            const receipt = await unstakeTx.wait();

            await syncUnstakeToBackend(stakeIdOnchain, receipt.transactionHash);

            showCustomAlert("Unstake Initiated", "Your stake is now in the vesting period. You can start claiming your tokens from the Home page.");
            setTimeout(() => window.location.reload(), 3000);

        } catch (error) {
            console.error("Unstake process failed:", error);
            let userMessage = "An unexpected error occurred during unstake.";
            if (error.code === 4001) {
                userMessage = "The unstake transaction was rejected.";
            }
            showCustomAlert("Unstake Failed", userMessage, "error");
        } finally {
            unstakeBtn.disabled = false;
            unstakeBtn.textContent = 'Unstake Now';
        }
    };
    
    const syncUnstakeToBackend = async (stakeId, txHash) => {
        const payload = {
            stake_id_onchain: stakeId,
            tx_hash: txHash
        };
        const response = await fetch('api/execute_unstake.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        const result = await response.json();
        if (result.status !== 'success') {
            throw new Error('Backend unstake sync failed: ' + result.message);
        }
    };


    // --- Event Listeners ---
    stakeAmountInput.addEventListener('input', updateSummary);
    planButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            planButtons.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            selectedPlan = btn.dataset.plan;
            updateSummary();
        });
    });
    stakeBtn.addEventListener('click', handleStake);
    unstakeBtn.addEventListener('click', handleUnstake);


    // --- Inisialisasi ---
    const initializeApp = async () => {
        await loadConfig();
        
        // REVISI: Inisialisasi provider dan signer di sini agar selalu tersedia
        if (typeof window.ethereum !== 'undefined') {
            provider = new ethers.providers.Web3Provider(window.ethereum);
            signer = provider.getSigner();
        } else {
            showCustomAlert("MetaMask Not Found", "Please install the MetaMask browser extension to use this DApp.", "error");
            preloader.classList.remove('show');
            return;
        }

        await fetchPageInfo();
    };

    initializeApp();
});

