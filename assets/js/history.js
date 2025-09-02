document.addEventListener('DOMContentLoaded', () => {
    const preloader = document.querySelector('.preloader');
    const transactionListContainer = document.getElementById('transactionList');
    const paginationInfo = document.getElementById('paginationInfo');
    const prevPageBtn = document.getElementById('prevPageBtn');
    const nextPageBtn = document.getElementById('nextPageBtn');
    const filterTabs = document.querySelectorAll('.history-tab-item');
    // PENAMBAHAN: Ambil elemen header
    const greetingWalletEl = document.getElementById('greetingWallet');

    let currentPage = 1;
    let totalPages = 1;
    let currentFilter = 'all';

    // Fungsi untuk memformat alamat wallet
    const formatWalletAddress = (address) => {
        if (!address) return 'Loading...';
        return `${address.substring(0, 5)}...${address.substring(address.length - 4)}`;
    };

    // Fungsi untuk memformat tanggal (mengubah UTC ke zona waktu lokal)
    const formatLocalDate = (utcDateString) => {
        if (!utcDateString) return '';
        const date = new Date(utcDateString + 'Z'); // Tambah 'Z' untuk menandakan UTC
        return date.toLocaleString('en-US', {
            month: 'short', day: 'numeric', year: 'numeric',
            hour: '2-digit', minute: '2-digit', hour12: false
        });
    };

    // Fungsi untuk membuat satu item transaksi HTML
    const createTransactionItemHTML = (tx) => {
        const isIncome = ['withdraw', 'staking_roi_in', 'matching_bonus_in', 'royalty_bonus_in'].includes(tx.type);
        const isOutcome = ['stake'].includes(tx.type);

        const iconClass = isIncome ? 'fa-arrow-down' : (isOutcome ? 'fa-arrow-up' : 'fa-layer-group');
        const iconColorClass = isIncome ? 'income' : (isOutcome ? 'outcome' : 'stake');
        
        let amountHTML = '';
        if (tx.amount_usdt) {
            const sign = isIncome ? '+' : '-';
            amountHTML = `<div class="transaction-amount ${iconColorClass}">${sign} $${parseFloat(tx.amount_usdt).toFixed(4)}</div>`;
        } else if (tx.amount_langit) {
            amountHTML = `<div class="transaction-amount">${parseFloat(tx.amount_langit).toFixed(2)} LANGIT</div>`;
        }
        
        // Mengganti underscore dengan spasi dan membuat huruf kapital
        var formattedType = tx.type.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());

        if (formattedType === "Staking Roi In"){
            formattedType = "Staking Reward In";
        } else if (formattedType === "Matching Bonus In") {
            formattedType = "Matching Reward In";
        } else if (formattedType === "Royalty Bonus In") {
            formattedType = "Royalty Reward In";
        }

        return `
            <div class="transaction-item">
                <div class="transaction-icon ${iconColorClass}"><i class="fas ${iconClass}"></i></div>
                <div class="transaction-details">
                    <div class="type">${formattedType}</div>
                    <div class="date">${formatLocalDate(tx.created_at)}</div>
                </div>
                ${amountHTML}
            </div>
        `;
    };

    // Fungsi utama untuk mengambil dan menampilkan data
    const fetchHistory = async () => {
        preloader.classList.add('show');
        transactionListContainer.innerHTML = ''; // Kosongkan daftar sebelum memuat data baru

        try {
            const response = await fetch(`api/get_history.php?type=${currentFilter}&page=${currentPage}`);
            if (!response.ok) throw new Error('Network response was not ok');
            
            const result = await response.json();

            if (result.status === 'success') {
                // PENAMBAHAN: Update header dengan wallet address dari respons
                greetingWalletEl.textContent = `Hello, ${formatWalletAddress(result.wallet_address)}`;

                if(result.data.length > 0) {
                    result.data.forEach(tx => {
                        transactionListContainer.innerHTML += createTransactionItemHTML(tx);
                    });
                } else {
                    transactionListContainer.innerHTML = '<p class="text-center text-secondary">No transactions found.</p>';
                }

                // Update info paginasi
                const pagination = result.pagination;
                currentPage = pagination.current_page;
                totalPages = pagination.total_pages;
                paginationInfo.textContent = `Page ${currentPage} of ${totalPages || 1}`;

                // Atur status tombol paginasi
                prevPageBtn.disabled = currentPage <= 1;
                nextPageBtn.disabled = currentPage >= totalPages;
            } else {
                 if (result.message.includes('authenticated')) {
                    window.location.href = 'index.php';
                } else {
                    transactionListContainer.innerHTML = `<p class="text-center text-danger">${result.message}</p>`;
                }
            }
        } catch (error) {
            console.error('Fetch History Error:', error);
            transactionListContainer.innerHTML = '<p class="text-center text-danger">Failed to load history.</p>';
        } finally {
            preloader.classList.remove('show');
        }
    };

    // Event listener untuk filter tabs
    filterTabs.forEach(tab => {
        tab.addEventListener('click', () => {
            filterTabs.forEach(t => t.classList.remove('active'));
            tab.classList.add('active');
            currentFilter = tab.dataset.filter;
            currentPage = 1; // Reset ke halaman pertama saat filter berubah
            fetchHistory();
        });
    });

    // Event listener untuk tombol paginasi
    prevPageBtn.addEventListener('click', () => {
        if (currentPage > 1) {
            currentPage--;
            fetchHistory();
        }
    });

    nextPageBtn.addEventListener('click', () => {
        if (currentPage < totalPages) {
            currentPage++;
            fetchHistory();
        }
    });

    // Panggil untuk memuat data awal
    fetchHistory();
});

