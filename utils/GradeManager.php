<?php
/**
 * GradeManager Class
 *
 * Bertanggung jawab untuk menangani semua logika terkait pengecekan
 * dan pembaruan grade pengguna.
 */
class GradeManager
{
    private $userTable;
    private $stakeTable;

    // Menyimpan syarat-syarat grade
    private const GRADE_REQUIREMENTS = [
        'A' => 50, 'B' => 150, 'C' => 500, 'D' => 1500,
        'E' => 5000, 'F' => 20000, 'G' => 100000, 'H' => 500000,
    ];
    private const GRADES_ORDER = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'];

    public function __construct(UserTableClass $userTable, StakeTableClass $stakeTable)
    {
        $this->userTable = $userTable;
        $this->stakeTable = $stakeTable;
    }

    /**
     * Fungsi utama untuk memeriksa dan meng-upgrade semua upline secara berurutan.
     *
     * @param string|null $firstUplineWallet Alamat wallet dari upline pertama.
     */
    public function checkAndUpgradeUplines(?string $firstUplineWallet): void
    {
        if ($firstUplineWallet === null) {
            return; // Tidak ada upline, berhenti.
        }

        $currentUplineWallet = $firstUplineWallet;

        // Loop ke atas, dari satu upline ke upline berikutnya
        while ($currentUplineWallet !== null) {
            $uplineUser = $this->userTable->getUserByWalletAddress($currentUplineWallet);
            
            // Jika upline tidak ditemukan atau sudah mencapai grade tertinggi, berhenti.
            if (!$uplineUser) {
                break;
            }

            // Lakukan pengecekan untuk upline saat ini
            $this->checkAndUpgradeSingleUser($uplineUser);

            // Pindah ke upline berikutnya
            $currentUplineWallet = $uplineUser['upline_wallet'];
        }
    }

    /**
     * Memeriksa dan meng-upgrade satu pengguna.
     *
     * @param array $user Data pengguna yang akan diperiksa.
     */
    public function checkAndUpgradeSingleUser(array $user): void
    {
        $userId = $user['id'];
        $userWallet = $user['wallet_address'];
        $currentGrade = $user['grade'];

        // 1. Cek Staking Pribadi
        $totalPersonalStake = $this->stakeTable->getUserTotalStakesUSDT($userId);
        if ($totalPersonalStake < 50) {
            return; // Syarat dasar tidak terpenuhi
        }

        // 2. Cek Jumlah Downline Langsung
        $directDownlineCount = $this->userTable->getDirectDownlineCount($userWallet);
        if ($directDownlineCount < 2) {
            return; // Syarat dasar tidak terpenuhi
        }

        // 3. Cek Omset Jaringan
        $networkStats = $this->userTable->getNetworkStats($userId);
        $networkTurnover = 0;
        if (!empty($networkStats['all_member_ids'])) {
            $networkTurnover = $this->stakeTable->getTotalStakesForUserList($networkStats['all_member_ids']);
        }
        
        // Tentukan grade berikutnya yang menjadi target
        $nextGrade = $this->getNextGrade($currentGrade);

        // Jika omset sudah mencapai target untuk grade berikutnya
        if ($networkTurnover >= self::GRADE_REQUIREMENTS[$nextGrade]) {
            // Lakukan upgrade!
            $this->userTable->updateUserGrade($userId, $nextGrade);
            error_log("User ID {$userId} has been upgraded to Grade {$nextGrade}.");
        }
    }

    /**
     * Menentukan grade berikutnya berdasarkan grade saat ini.
     */
    private function getNextGrade(?string $currentGrade): string
    {
        if ($currentGrade === null) {
            return 'A';
        }
        $currentIndex = array_search($currentGrade, self::GRADES_ORDER);
        if ($currentIndex !== false && $currentIndex < count(self::GRADES_ORDER) - 1) {
            return self::GRADES_ORDER[$currentIndex + 1];
        }
        return 'H'; // Jika sudah di puncak, target tetap H
    }
}
?>
