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
    let contracts = {}; // Untuk menyimpan instance kontrak
    let blockchainConfig = {}; // Untuk menyimpan ABI dan alamat

    // --- Inisialisasi & Fungsi Helper ---

    const formatWalletAddress = (address) => {
        if (!address) return 'Loading...';
        return `${address.substring(0, 5)}...${address.substring(address.length - 4)}`;
    };
    
    // Fungsi untuk menampilkan pesan error atau sukses
    const showToast = (message, isError = false) => {
        // Implementasi toast/notifikasi sederhana di sini
        alert(message);
    };

    // Fungsi untuk memuat konfigurasi blockchain
    const loadConfig = async () => {
        const response = await fetch('blockchain_config.json');
        blockchainConfig = await response.json();
    };

    // --- Logika Utama ---

    // Fungsi untuk mengambil data awal halaman
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
                    displayManageStakeView(data.active_stake_details);
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

    // Fungsi untuk menyiapkan tampilan staking baru
    const initializeNewStakeView = async () => {
        newStakeView.classList.remove('d-none');
        manageStakeView.classList.add('d-none');

        langitPriceEl.textContent = `1 LANGIT ≈ $${langitPrice.toFixed(6)} USDT`;

        if (!provider) provider = new ethers.providers.Web3Provider(window.ethereum);
        if (!signer) signer = provider.getSigner();

        contracts.langit = new ethers.Contract(blockchainConfig.LANGIT.address, blockchainConfig.LANGIT.abi, signer);
        
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

    // Fungsi untuk menampilkan tampilan manajemen stake
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
        
        // Logika untuk tombol unstake (termasuk lock period)
        if (details.plan !== 'Flexible' && new Date() < new Date(details.expires_at)) {
            unstakeBtn.disabled = true;
            unstakeBtn.textContent = `Locked until ${details.expires_at}`;
        } else {
            unstakeBtn.disabled = false;
            unstakeBtn.textContent = 'Unstake Now';
        }
    };

    // Fungsi untuk update ringkasan saat input berubah
    const updateSummary = () => {
        const amount = parseFloat(stakeAmountInput.value) || 0;
        const usdtValue = amount * langitPrice;

        usdtValueEl.textContent = `≈ $ ${usdtValue.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })} USDT`;
        
        summaryAmountLangit.textContent = `${amount.toLocaleString()} LANGIT`;
        summaryAmountUsdt.textContent = `~ $${usdtValue.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })} USDT`;
        summaryPlan.textContent = selectedPlan.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase());

        // Validasi untuk tombol stake
        stakeBtn.disabled = usdtValue < 10;
    };
    
    // --- Proses Staking (On-chain & Off-chain) ---

    const handleStake = async () => {
        stakeBtn.disabled = true;
        stakeBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Processing...';
        
        try {
            const amount = stakeAmountInput.value;
            if (!amount || parseFloat(amount) <= 0) {
                throw new Error("Invalid amount");
            }
            const amountInWei = ethers.utils.parseUnits(amount, 18);

            if (!contracts.staking) {
                contracts.staking = new ethers.Contract(blockchainConfig.LangitStaking.address, blockchainConfig.LangitStaking.abi, signer);
            }
            if (!contracts.langit) {
                 contracts.langit = new ethers.Contract(blockchainConfig.LANGIT.address, blockchainConfig.LANGIT.abi, signer);
            }

            // 1. Approve
            stakeBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Awaiting Approval...';
            const approveTx = await contracts.langit.approve(contracts.staking.address, amountInWei);
            await approveTx.wait();

            // 2. Generate Stake ID
            const stakeId = ethers.utils.keccak256(ethers.utils.toUtf8Bytes(userAddress + amount + Date.now()));
            
            // 3. Stake
            stakeBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Awaiting Staking...';
            const stakeTx = await contracts.staking.stake(stakeId, amountInWei);
            const receipt = await stakeTx.wait();

            // 4. Sync to Backend
            stakeBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Syncing...';
            await syncStakeToBackend(stakeId, amount, receipt.transactionHash);
            
            showToast("Staking successful!");
            window.location.href = 'home.php';

        } catch (error) {
            console.error("Staking process failed:", error);
            showToast("Staking process failed: " + (error.data?.message || error.message), true);
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


    // --- Inisialisasi ---
    await loadConfig();
    await fetchPageInfo();
});

