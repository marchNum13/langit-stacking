document.addEventListener('DOMContentLoaded', () => {
    const preloader = document.querySelector('.preloader');

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

    // Panggil fungsi untuk mengambil data saat halaman dimuat
    fetchDashboardData();
});

