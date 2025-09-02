document.addEventListener('DOMContentLoaded', () => {
    const preloader = document.querySelector('.preloader');
    const vestingClaimsContainer = document.getElementById('vestingClaimsContainer');

    // --- State Aplikasi ---
    let provider, signer, contracts = {}, blockchainConfig = {};

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
    };
    const showCustomAlert = (title, message, type = 'success') => {
        alertIconEl.innerHTML = icons[type] || icons.success;
        alertTitleEl.textContent = title;
        alertMessageEl.textContent = message;
        alertPopup.className = 'custom-alert-popup ' + type;
        alertOverlay.classList.add('show');
    };
    alertCloseBtn.addEventListener('click', () => alertOverlay.classList.remove('show'));
    
    const loadConfig = async () => {
        try {
            const response = await fetch('blockchain_config.json');
            blockchainConfig = await response.json();
        } catch(e) {
            showCustomAlert('Configuration Error', 'Could not load application settings.', 'error');
        }
    };

    // Fungsi untuk memformat alamat wallet
    const formatWalletAddress = (address) => {
        if (!address) return 'Loading...';
        return `${address.substring(0, 5)}...${address.substring(address.length - 4)}`;
    };

    // Fungsi untuk mengupdate UI dengan data dari API
    const updateDashboardUI = (data) => {
        document.getElementById('greetingWallet').innerText = `Hello, ${formatWalletAddress(data.wallet_address)}`;
        document.getElementById('totalActiveStake').innerText = `$ ${data.total_active_stake}`;
        document.getElementById('totalEarnings').innerText = `$ ${data.total_earnings}`;
        document.getElementById('userGrade').innerHTML = `<i class="fas fa-star"></i> Grade ${data.user_grade}`;
        document.getElementById('networkTurnover').innerText = `$ ${data.network_turnover}`;
        
        document.getElementById('stakingRoi').innerText = `$ ${data.earnings_breakdown.staking_roi}`;
        document.getElementById('matchingBonus').innerText = `$ ${data.earnings_breakdown.matching_bonus}`;
        document.getElementById('royaltyBonus').innerText = `$ ${data.earnings_breakdown.royalty_bonus}`;

        document.getElementById('profitCycleAchieved').innerText = `Achieved: $${data.profit_cycle.achieved}`;
        document.getElementById('profitCycleMax').innerText = `Max: $${data.profit_cycle.max} (500%)`;
        
        const progressBar = document.getElementById('profitCycleProgress');
        progressBar.style.width = `${data.profit_cycle.percentage}%`;
    };

    // REVISI: Menambahkan parameter releasedAmount untuk ditampilkan
    const createVestingItemHTML = (stake, releasedAmount) => {
        const totalAmount = parseFloat(stake.amount_langit);
        const releasedFormatted = parseFloat(ethers.utils.formatUnits(releasedAmount, 18)).toLocaleString('en-US', {maximumFractionDigits: 2});
        const totalFormatted = totalAmount.toLocaleString('en-US', {maximumFractionDigits: 2});
        
        const progressPercentage = totalAmount > 0 ? (ethers.utils.formatUnits(releasedAmount, 18) / totalAmount) * 100 : 0;

        return `
            <div class="summary-card vesting-card mb-3">
                <h2 class="section-title">Vesting Claim Available</h2>
                <div class="summary-item d-flex justify-content-between align-items-center">
                    <span class="item-label">Unstaked Principal</span>
                    <span class="item-value">${totalFormatted} LANGIT</span>
                </div>
                <!-- PENAMBAHAN BARU: Menampilkan progress vesting -->
                <div class="summary-item">
                     <div class="d-flex justify-content-between align-items-center mb-2 text-secondary small">
                        <span>Claimed: ${releasedFormatted}</span>
                        <span>Total: ${totalFormatted}</span>
                    </div>
                    <div class="progress-bar-custom">
                        <div class="progress-fill" style="width: ${progressPercentage}%;"></div>
                    </div>
                </div>
                <div class="d-grid gap-2 mt-3">
                    <button class="btn cta-button claim-vesting-btn" data-stake-id="${stake.stake_id_onchain}">
                        Claim Vested Tokens
                    </button>
                </div>
            </div>
        `;
    };

    // REVISI: Mengambil data on-chain sebelum menampilkan
    const fetchAndDisplayVestingClaims = async () => {
        try {
            const response = await fetch('api/get_vesting_info.php');
            const result = await response.json();

            if (result.status === 'success' && result.data.length > 0) {
                if (!contracts.staking) {
                    contracts.staking = new ethers.Contract(blockchainConfig.langitStaking.address, blockchainConfig.langitStaking.abi, signer);
                }
                
                vestingClaimsContainer.innerHTML = ''; // Kosongkan kontainer
                
                // Loop melalui setiap stake dan ambil data on-chain
                for (const stake of result.data) {
                    const stakeInfo = await contracts.staking.getStakeInfo(stake.stake_id_onchain);
                    vestingClaimsContainer.innerHTML += createVestingItemHTML(stake, stakeInfo.releasedAmount);
                }
            }
        } catch (error) {
            console.error("Failed to fetch vesting claims:", error);
        }
    };
    
    const handleClaimVesting = async (e) => {
        const claimBtn = e.target;
        const stakeId = claimBtn.dataset.stakeId;
        if (!stakeId) return;

        claimBtn.disabled = true;
        claimBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Claiming...';

        try {
            if (!contracts.staking) {
                contracts.staking = new ethers.Contract(blockchainConfig.langitStaking.address, blockchainConfig.langitStaking.abi, signer);
            }

            const claimTx = await contracts.staking.claimVestedTokens(stakeId);
            const receipt = await claimTx.wait();
            
            let amountClaimed = 0;
            const event = receipt.events?.find(e => e.event === 'VestingClaimed');
            if(event) {
                amountClaimed = ethers.utils.formatUnits(event.args.amount, 18);
            }

            const stakeInfo = await contracts.staking.getStakeInfo(stakeId);
            const isComplete = stakeInfo.status === 2; // 2 adalah enum 'Completed'

            await syncVestingClaimToBackend(stakeId, receipt.transactionHash, amountClaimed, isComplete);
            
            showCustomAlert("Claim Successful", `You have successfully claimed ${parseFloat(amountClaimed).toFixed(2)} LANGIT.`);
            setTimeout(() => window.location.reload(), 3000);

        } catch (error) {
            console.error("Vesting claim failed:", error);
            showCustomAlert("Claim Failed", "The transaction failed. Please try again.", "error");
        } finally {
            claimBtn.disabled = false;
            claimBtn.innerHTML = 'Claim Vested Tokens';
        }
    };

    const syncVestingClaimToBackend = async (stakeId, txHash, amount, isComplete) => {
        await fetch('api/execute_vesting_claim.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                stake_id_onchain: stakeId,
                tx_hash: txHash,
                amount_langit_claimed: amount,
                is_complete: isComplete
            })
        });
    };

    vestingClaimsContainer.addEventListener('click', (e) => {
        if (e.target.classList.contains('claim-vesting-btn')) {
            handleClaimVesting(e);
        }
    });

    // Fungsi utama untuk mengambil data dari backend
    const fetchDashboardData = async () => {
        try {
            const response = await fetch('api/get_dashboard_data.php');
            
            // Cek jika respons bukan JSON atau ada error server
            if (!response.ok) {
                // Tampilkan error di console untuk debugging
                const errorText = await response.text();
                console.error('Server Error:', response.status, errorText);
                throw new Error(`Server responded with status: ${response.status}`);
            }

            const result = await response.json();

            if (result.status === 'success') {
                updateDashboardUI(result.data);
            } else {
                // Jika error karena tidak terautentikasi, redirect ke halaman login
                if (result.message.includes('authenticated')) {
                    window.location.href = 'index.php';
                } else {
                    console.error('API Error:', result.message);
                    document.getElementById('greetingWallet').innerText = 'Error: ' + result.message;
                }
            }
        } catch (error) {
            console.error('Fetch error:', error);
            document.getElementById('greetingWallet').innerText = 'Failed to load dashboard data.';
        } finally {
            // REVISI: Selalu sembunyikan preloader, baik berhasil maupun gagal
            preloader.classList.remove('show');
        }
    };

        // --- Inisialisasi Aplikasi ---
    const initializeApp = async () => {
        await loadConfig();
        if (typeof window.ethereum !== 'undefined') {
            provider = new ethers.providers.Web3Provider(window.ethereum);
            signer = provider.getSigner();
        } else {
            preloader.classList.remove('show');
            return;
        }
        await fetchDashboardData();
    };

    initializeApp();

    // Panggil fungsi untuk mengambil data saat halaman dimuat
    fetchDashboardData();
    fetchAndDisplayVestingClaims();
});

