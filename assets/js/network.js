document.addEventListener('DOMContentLoaded', () => {
    const preloader = document.querySelector('.preloader');
    
    // Fungsi untuk memformat alamat wallet
    const formatWalletAddress = (address) => {
        if (!address) return '';
        return `${address.substring(0, 5)}...${address.substring(address.length - 4)}`;
    };

    // Fungsi untuk membuat satu item downline HTML
    const createDownlineItemHTML = (downline) => {
        return `
            <div class="downline-item">
                <div>
                    <div class="wallet-address">${formatWalletAddress(downline.wallet_address)}</div>
                    <div class="turnover-label">Total Staking</div>
                </div>
                <div class="text-end">
                    <div class="turnover-amount">$ ${downline.turnover}</div>
                </div>
            </div>
        `;
    };

    // Fungsi untuk mengupdate UI halaman Network
    const updateNetworkUI = (data) => {
        // Update header
        document.getElementById('greetingWallet').textContent = `Hello, ${formatWalletAddress(data.wallet_address)}`;
        
        // PENAMBAHAN: Update Journey to Grade
        const journey = data.grade_journey;
        document.getElementById('journeyTitle').textContent = `Journey to Grade ${journey.next_grade}`;
        document.getElementById('journeyCurrent').textContent = `Turnover: $${parseFloat(journey.current_turnover).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
        document.getElementById('journeyTarget').textContent = `Target: $${parseFloat(journey.target_turnover).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
        
        let percentage = 0;
        if (journey.target_turnover > 0) {
            percentage = (journey.current_turnover / journey.target_turnover) * 100;
        }
        document.getElementById('journeyProgress').style.width = `${Math.min(percentage, 100)}%`;


        // Update statistik
        document.getElementById('directDownlinesCount').textContent = data.direct_downline_count;
        document.getElementById('totalTeamMembers').textContent = data.total_team_members;
        
        // Update link referral dan tombol copy
        const referralLinkEl = document.getElementById('referralLink');
        const copyBtn = document.getElementById('copyBtn');
        referralLinkEl.textContent = data.referral_link.replace('https://', '');
        copyBtn.addEventListener('click', () => {
            navigator.clipboard.writeText(data.referral_link).then(() => {
                copyBtn.innerHTML = '<i class="fas fa-check"></i> Copied!';
                setTimeout(() => {
                     copyBtn.innerHTML = '<i class="fas fa-copy me-1"></i> Copy';
                }, 2000);
            });
        });

        // Update daftar downline
        const downlineListContainer = document.getElementById('downlineList');
        downlineListContainer.innerHTML = ''; // Kosongkan dulu
        if (data.direct_downlines_list.length > 0) {
            data.direct_downlines_list.forEach(downline => {
                downlineListContainer.innerHTML += createDownlineItemHTML(downline);
            });
        } else {
            downlineListContainer.innerHTML = '<p class="text-center text-secondary">You have no direct downlines yet.</p>';
        }
    };


    // Fungsi utama untuk mengambil data dari backend
    const fetchNetworkData = async () => {
        try {
            const response = await fetch('api/get_network_data.php');
            if (!response.ok) throw new Error('Network response was not ok');
            
            const result = await response.json();

            if (result.status === 'success') {
                updateNetworkUI(result.data);
            } else {
                if (result.message.includes('authenticated')) {
                    window.location.href = 'index.php';
                } else {
                    console.error('API Error:', result.message);
                }
            }
        } catch (error) {
            console.error('Fetch Network Error:', error);
        } finally {
            preloader.classList.remove('show');
        }
    };

    // Panggil fungsi untuk mengambil data
    fetchNetworkData();
});

