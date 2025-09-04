<?php
/**
 * BonusManager Class
 *
 * Mengelola semua logika untuk perhitungan dan distribusi bonus melalui cron job.
 */
class BonusManager
{
    private $userTable;
    private $stakeTable;
    private $balanceTable;
    private $transactionTable;

    private $dailyRates;

    private const MATCHING_BONUS_RATES = [
        'A' => 0.05, 'B' => 0.10, 'C' => 0.15, 'D' => 0.20,
        'E' => 0.25, 'F' => 0.30, 'G' => 0.40, 'H' => 0.50,
    ];
    private const ROYALTY_RATE = 0.04; // 4%

    public function __construct(UserTableClass $u, StakeTableClass $s, BalanceTableClass $b, TransactionTableClass $t)
    {
        $this->userTable = $u;
        $this->stakeTable = $s;
        $this->balanceTable = $b;
        $this->transactionTable = $t;
    }

    /**
     * Metode utama yang akan dipanggil oleh cron job.
     */
    public function processHourlyBonuses(): void
    {
        echo "Memulai proses bonus per jam...\n";
        
        // 1. Tentukan rate ROI harian
        $this->determineDailyRates();
        if ($this->dailyRates === null) {
            echo "Gagal menentukan rate ROI harian. Proses dihentikan.\n";
            return;
        }
        echo "Rate ROI hari ini: Flexible = {$this->dailyRates['flexible']}%, 6 Months = {$this->dailyRates['6_months']}%, 12 Months = {$this->dailyRates['12_months']}%\n";

        // 2. Ambil semua staking yang aktif
        $activeStakes = $this->stakeTable->getAllActiveStakes();
        if (empty($activeStakes)) {
            echo "Tidak ada staking aktif yang ditemukan. Proses selesai.\n";
            return;
        }
        echo "Ditemukan " . count($activeStakes) . " staking aktif.\n";

        // 3. (Khusus untuk Royalti) Hitung total modal aktif di platform
        $totalPlatformStake = $this->stakeTable->getTotalPlatformActiveStake();

        // 4. Proses setiap staking
        foreach ($activeStakes as $stake) {
            $this->processSingleStake($stake, $totalPlatformStake);
        }

        echo "Proses bonus per jam selesai.\n";
    }

    /**
     * Menentukan rate ROI harian, mengambil dari cache atau membuat yang baru.
     */
    private function determineDailyRates(): void
    {
        $cacheFile = __DIR__ . '/../cache/daily_roi_rates.json';
        $today = date('Y-m-d');

        if (file_exists($cacheFile)) {
            $cacheData = json_decode(file_get_contents($cacheFile), true);
            if (isset($cacheData['date']) && $cacheData['date'] === $today) {
                $this->dailyRates = $cacheData['rates'];
                return;
            }
        }

        // Buat rate baru jika cache tidak ada atau sudah kedaluwarsa
        $rate12m = $this->generateRandomFloat(0.5, 1.5);
        $rate6m = $this->generateRandomFloat(0.4, $rate12m);
        $rateFlex = $this->generateRandomFloat(0.3, $rate6m);

        $this->dailyRates = [
            '12_months' => $rate12m,
            '6_months' => $rate6m,
            'flexible' => $rateFlex,
        ];

        // Simpan ke cache
        if (!is_dir(dirname($cacheFile))) {
            mkdir(dirname($cacheFile), 0755, true);
        }
        file_put_contents($cacheFile, json_encode(['date' => $today, 'rates' => $this->dailyRates]));
    }

    /**
     * REVISI: Memproses bonus untuk satu staking, termasuk pengecekan kedaluwarsa.
     */
    private function processSingleStake(array $stake, float $totalPlatformStake): void
    {
        // PENGECEKAN BARU: Cek apakah stake berjangka sudah kedaluwarsa
        if ($this->checkAndHandleExpiry($stake)) {
            // Jika sudah kedaluwarsa, statusnya diubah menjadi 'expired' dan tidak menerima bonus lagi.
            // totalPlatformStake diperbarui agar perhitungan royalti tetap akurat.
            $totalPlatformStake -= (float)$stake['amount_usdt_initial'];
            return; // Hentikan proses untuk stake ini
        }

        $staker = $this->userTable->getUserById($stake['user_id']);
        if (!$staker) return;

        // A. Hitung dan distribusikan Staking ROI
        $dailyRate = $this->dailyRates[$stake['plan']];
        $hourlyRoiAmount = (float)$stake['amount_usdt_initial'] * (($dailyRate / 100) / 24);
        
        $this->balanceTable->addBonus($stake['user_id'], $hourlyRoiAmount, 'staking_roi');
        $this->transactionTable->createTransaction([
            'user_id' => $stake['user_id'],
            'related_stake_id' => $stake['id'],
            'type' => 'staking_roi_in',
            'amount_usdt' => $hourlyRoiAmount
        ]);
        
        // B. Proses bonus untuk para upline
        $this->processUplineBonuses($staker, $hourlyRoiAmount, $totalPlatformStake);
    }
    
    /**
     * FUNGSI BARU: Memeriksa dan menangani jika stake berjangka telah kedaluwarsa.
     * @return bool True jika stake kedaluwarsa dan statusnya diubah, false jika tidak.
     */
    private function checkAndHandleExpiry(array $stake): bool
    {
        // Hanya cek untuk plan berjangka yang memiliki tanggal kedaluwarsa
        if ($stake['plan'] !== 'flexible' && !empty($stake['expires_at'])) {
            $now = new DateTime();
            $expiryDate = new DateTime($stake['expires_at']);

            if ($now >= $expiryDate) {
                // Stake sudah kedaluwarsa, ubah statusnya
                $this->stakeTable->updateStakeStatus($stake['stake_id_onchain'], 'expired');
                echo "Stake ID {$stake['id']} telah kedaluwarsa. Status diubah menjadi 'expired'.\n";
                return true; // Menandakan stake sudah tidak aktif lagi
            }
        }
        return false; // Stake masih aktif
    }


    /**
     * Memproses Matching dan Royalty Bonus untuk hierarki upline.
     */
    private function processUplineBonuses(array $staker, float $roiAmount, float $totalPlatformStake): void
    {
        if (empty($staker['upline_wallet'])) {
            return;
        }

        $currentUplineWallet = $staker['upline_wallet'];
        $lastGrade = null;
        $matchingStoppedAtH = false;
        $royaltyRecipients = [];
        $matchingRateNow = 0;

        // Loop ke atas dalam hierarki
        while ($currentUplineWallet !== null) {
            $uplineUser = $this->userTable->getUserByWalletAddress($currentUplineWallet);
            if (!$uplineUser) {
                break; // Berhenti jika upline tidak ada atau tidak punya grade
            }

            $currentGrade = $uplineUser['grade'];

            if($uplineUser['grade'] === null){
                // Siapkan untuk iterasi berikutnya
                // $lastGrade = $currentGrade;
                $currentUplineWallet = $uplineUser['upline_wallet'];
                continue;
            }

            // --- Logika Matching Bonus ---
            if (!$matchingStoppedAtH) {
                // Berhenti jika grade sama dengan upline sebelumnya
                if ($currentGrade === $lastGrade) {
                    $matchingStoppedAtH = true; // Berhenti total
                } else {
                    $matchingRate = self::MATCHING_BONUS_RATES[$currentGrade] - $matchingRateNow ?? 0;
                    $matchingAmount = $roiAmount * $matchingRate;

                    $this->balanceTable->addBonus($uplineUser['id'], $matchingAmount, 'matching_bonus');
                    $this->transactionTable->createTransaction([
                        'user_id' => $uplineUser['id'], 'type' => 'matching_bonus_in', 'amount_usdt' => $matchingAmount
                    ]);

                    // Jika upline adalah Grade H, berhenti memberikan matching bonus setelah ini
                    if ($currentGrade === 'H') {
                        $matchingStoppedAtH = true;
                    }
                }
            }
            
            // --- Logika Royalty Bonus ---
            // Terus berjalan bahkan jika matching bonus berhenti, khusus untuk mencari Grade H
            if ($matchingStoppedAtH && $currentGrade === 'H') {
                $royaltyRecipients[] = $uplineUser['id'];
            }

            // Siapkan untuk iterasi berikutnya
            $lastGrade = $currentGrade;
            $matchingRateNow = self::MATCHING_BONUS_RATES[$currentGrade];
            $currentUplineWallet = $uplineUser['upline_wallet'];
        }

        // --- Distribusi Royalty Bonus ---
        if (!empty($royaltyRecipients)) {
            $hourlyRoyaltyPool = $roiAmount * self::ROYALTY_RATE;
            $sharePerRecipient = count($royaltyRecipients) > 0 ? $hourlyRoyaltyPool / count($royaltyRecipients) : 0;

            if($sharePerRecipient > 0) {
                foreach ($royaltyRecipients as $recipientId) {
                    $this->balanceTable->addBonus($recipientId, $sharePerRecipient, 'royalty_bonus');
                    $this->transactionTable->createTransaction([
                        'user_id' => $recipientId, 'type' => 'royalty_bonus_in', 'amount_usdt' => $sharePerRecipient
                    ]);
                }
            }
        }
    }

    /**
     * Menghasilkan angka float acak di antara min dan max.
     */
    private function generateRandomFloat(float $min, float $max, int $precision = 4): float
    {
        if ($min > $max) {
            return $max;
        }
        return round($min + mt_rand() / mt_getrandmax() * ($max - $min), $precision);
    }
}
?>