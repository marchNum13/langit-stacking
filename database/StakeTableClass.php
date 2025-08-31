<?php
/**
 * Stake Table Class
 * Menangani semua operasi database untuk tabel `stakes`.
 */
class StakeTableClass extends connMySQL
{
    // --- Definisi Tabel dan Kolom ---
    private $table_name = "stakes";
    private $col_id = "id";
    private $col_user_id = "user_id";
    private $col_stake_id_onchain = "stake_id_onchain";
    private $col_plan = "plan";
    private $col_amount_langit = "amount_langit";
    private $col_amount_usdt_initial = "amount_usdt_initial";
    private $col_max_profit_usdt = "max_profit_usdt";
    private $col_total_withdrawn_usdt = "total_withdrawn_usdt";
    private $col_status = "status";
    private $col_created_at = "created_at";
    private $col_expires_at = "expires_at";

    /**
     * Constructor
     * Membangun koneksi DB dan membuat tabel `stakes` jika belum ada.
     */
    public function __construct()
    {
        if ($this->checkTable($this->table_name) == 0) {
            $sql = "CREATE TABLE `{$this->table_name}` (
                `{$this->col_id}` INT AUTO_INCREMENT PRIMARY KEY,
                `{$this->col_user_id}` INT NOT NULL,
                `{$this->col_stake_id_onchain}` VARCHAR(66) NOT NULL UNIQUE,
                `{$this->col_plan}` ENUM('flexible', '6_months', '12_months') NOT NULL,
                `{$this->col_amount_langit}` DECIMAL(36, 18) NOT NULL,
                `{$this->col_amount_usdt_initial}` DECIMAL(20, 8) NOT NULL,
                `{$this->col_max_profit_usdt}` DECIMAL(20, 8) NOT NULL,
                `{$this->col_total_withdrawn_usdt}` DECIMAL(20, 8) NOT NULL DEFAULT 0.00,
                `{$this->col_status}` ENUM('active', 'expired', 'vesting', 'vesting_complete', 'burned') NOT NULL DEFAULT 'active',
                `{$this->col_created_at}` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `{$this->col_expires_at}` TIMESTAMP NULL DEFAULT NULL,
                FOREIGN KEY (`{$this->col_user_id}`) REFERENCES `users`(`id`) ON DELETE CASCADE
            )";

            try {
                $this->dbConn()->query($sql);
                error_log("Tabel '{$this->table_name}' berhasil dibuat.");
            } catch (mysqli_sql_exception $e) {
                error_log("Error saat membuat tabel '{$this->table_name}': " . $e->getMessage());
            }
        }
    }

    /**
     * Membuat entri staking baru di database.
     *
     * @param array $data Data staking yang berisi semua kolom yang diperlukan.
     * @return int|false ID dari baris yang baru dimasukkan, atau false jika gagal.
     */
    public function createStake(array $data)
    {
        $conn = $this->dbConn();
        $sql = "INSERT INTO {$this->table_name} (
                    {$this->col_user_id}, 
                    {$this->col_stake_id_onchain}, 
                    {$this->col_plan}, 
                    {$this->col_amount_langit}, 
                    {$this->col_amount_usdt_initial}, 
                    {$this->col_max_profit_usdt},
                    {$this->col_expires_at}
                ) VALUES (?, ?, ?, ?, ?, ?, ?)";

        // Hitung expires_at berdasarkan plan
        $expires_at = null;
        if ($data['plan'] == '6_months') {
            $expires_at = date('Y-m-d H:i:s', strtotime('+180 days'));
        } elseif ($data['plan'] == '12_months') {
            $expires_at = date('Y-m-d H:i:s', strtotime('+360 days'));
        }

        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            'isssdds',
            $data['user_id'],
            $data['stake_id_onchain'],
            $data['plan'],
            $data['amount_langit'],
            $data['amount_usdt_initial'],
            $data['max_profit_usdt'], // Ini dihitung di backend sebelum memanggil fungsi ini
            $expires_at
        );
        
        if ($stmt->execute()) {
            return $stmt->insert_id;
        }
        return false;
    }

    /**
     * Mengambil data staking berdasarkan stake_id_onchain yang unik.
     *
     * @param string $stakeIdOnchain Kunci rahasia dari on-chain.
     * @return array|false Data staking, atau false jika tidak ditemukan.
     */
    public function getStakeByIdOnchain(string $stakeIdOnchain)
    {
        $conn = $this->dbConn();
        $sql = "SELECT * FROM {$this->table_name} WHERE {$this->col_stake_id_onchain} = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('s', $stakeIdOnchain);
        $stmt->execute();
        $result = $stmt->get_result();
        $stake = $result->fetch_assoc();
        $stmt->close();
        
        return $stake;
    }

    /**
     * Memperbarui status dari sebuah entri staking.
     *
     * @param string $stakeIdOnchain Kunci rahasia dari on-chain.
     * @param string $newStatus Status baru ('active', 'expired', 'vesting', etc.).
     * @return bool True jika berhasil, false jika gagal.
     */
    public function updateStakeStatus(string $stakeIdOnchain, string $newStatus): bool
    {
        $conn = $this->dbConn();
        $sql = "UPDATE {$this->table_name} SET {$this->col_status} = ? WHERE {$this->col_stake_id_onchain} = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ss', $newStatus, $stakeIdOnchain);
        
        return $stmt->execute();
    }

    /**
     * Menambahkan jumlah yang ditarik ke total yang sudah ada untuk sebuah stake.
     *
     * @param string $stakeIdOnchain Kunci rahasia dari on-chain.
     * @param float $withdrawnAmount Jumlah USDT yang baru saja ditarik.
     * @return bool True jika berhasil, false jika gagal.
     */
    public function addWithdrawnAmount(string $stakeIdOnchain, float $withdrawnAmount): bool
    {
        $conn = $this->dbConn();
        $sql = "UPDATE {$this->table_name} 
                SET {$this->col_total_withdrawn_usdt} = {$this->col_total_withdrawn_usdt} + ? 
                WHERE {$this->col_stake_id_onchain} = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ds', $withdrawnAmount, $stakeIdOnchain);

        return $stmt->execute();
    }

    /**
     * Menghitung total nilai USDT dari semua staking (aktif & tidak aktif) seorang pengguna.
     *
     * @param int $userId ID pengguna.
     * @return float Total nilai staking dalam USDT.
     */
    public function getUserTotalStakesUSDT(int $userId): float
    {
        $conn = $this->dbConn();
        $sql = "SELECT SUM({$this->col_amount_usdt_initial}) as total FROM {$this->table_name} WHERE {$this->col_user_id} = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        return (float)($row['total'] ?? 0.0);
    }
    
    /**
     * Menghitung total nilai USDT dari staking dengan status tertentu.
     *
     * @param int $userId ID pengguna.
     * @param string $status Status yang dicari (misal: 'active').
     * @return float Total nilai staking dalam USDT.
     */
    public function getUserTotalStakesUSDTByStatus(int $userId, string $status): float
    {
        $conn = $this->dbConn();
        $sql = "SELECT SUM({$this->col_amount_usdt_initial}) as total 
                FROM {$this->table_name} 
                WHERE {$this->col_user_id} = ? AND {$this->col_status} = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('is', $userId, $status);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        return (float)($row['total'] ?? 0.0);
    }
    
    /**
     * Menghitung total USDT yang sudah ditarik oleh seorang pengguna.
     *
     * @param int $userId ID pengguna.
     * @return float Total USDT yang sudah ditarik.
     */
    public function getUserTotalWithdrawnUSDT(int $userId): float
    {
        $conn = $this->dbConn();
        $sql = "SELECT SUM({$this->col_total_withdrawn_usdt}) as total FROM {$this->table_name} WHERE {$this->col_user_id} = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        return (float)($row['total'] ?? 0.0);
    }


    /**
     * Mengambil semua data staking seorang pengguna dengan status tertentu.
     *
     * @param int $userId ID pengguna.
     * @param string $status Status yang dicari (misal: 'vesting').
     * @return array Daftar data staking.
     */
    public function getUserStakesByStatus(int $userId, string $status): array
    {
        $conn = $this->dbConn();
        $sql = "SELECT * FROM {$this->table_name} WHERE {$this->col_user_id} = ? AND {$this->col_status} = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('is', $userId, $status);
        $stmt->execute();
        $result = $stmt->get_result();
        $stakes = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $stakes;
    }

    /**
     * Menghitung total omset dari daftar user ID.
     */
    public function getTotalStakesForUserList(array $userIds): float
    {
        if (empty($userIds)) {
            return 0.0;
        }
        $conn = $this->dbConn();
        $placeholders = implode(',', array_fill(0, count($userIds), '?'));
        $types = str_repeat('i', count($userIds));

        $sql = "SELECT SUM({$this->col_amount_usdt_initial}) as total 
                FROM {$this->table_name} 
                WHERE {$this->col_user_id} IN ({$placeholders})";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$userIds);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        return (float)($row['total'] ?? 0.0);
    }

    /**
     * FUNGSI BARU: Mengambil semua staking yang sedang aktif di seluruh platform.
     *
     * @return array Daftar semua staking aktif.
     */
    public function getAllActiveStakes(): array
    {
        $conn = $this->dbConn();
        $sql = "SELECT * FROM {$this->table_name} WHERE {$this->col_status} = 'active'";
        $result = $conn->query($sql);
        $stakes = $result->fetch_all(MYSQLI_ASSOC);
        $result->close();
        return $stakes;
    }

    /**
     * FUNGSI BARU: Menghitung total modal aktif di seluruh platform.
     *
     * @return float Total modal aktif dalam USDT.
     */
    public function getTotalPlatformActiveStake(): float
    {
        $conn = $this->dbConn();
        $sql = "SELECT SUM({$this->col_amount_usdt_initial}) as total 
                FROM {$this->table_name} 
                WHERE {$this->col_status} = 'active'";
        $result = $conn->query($sql);
        $row = $result->fetch_assoc();
        $result->close();
        return (float)($row['total'] ?? 0.0);
    }
}
?>

