<?php
/**
 * Balance Table Class
 * Menangani semua operasi database untuk tabel `balances`.
 */
class BalanceTableClass extends connMySQL
{
    // --- Definisi Tabel dan Kolom ---
    private $table_name = "balances";
    private $col_id = "id";
    private $col_user_id = "user_id";
    private $col_staking_roi = "staking_roi";
    private $col_matching_bonus = "matching_bonus";
    private $col_royalty_bonus = "royalty_bonus";
    private $col_updated_at = "updated_at";

    /**
     * Constructor
     * Membangun koneksi DB dan membuat tabel `balances` jika belum ada.
     */
    public function __construct()
    {
        if ($this->checkTable($this->table_name) == 0) {
            $sql = "CREATE TABLE `{$this->table_name}` (
                `{$this->col_id}` INT AUTO_INCREMENT PRIMARY KEY,
                `{$this->col_user_id}` INT NOT NULL UNIQUE,
                `{$this->col_staking_roi}` DECIMAL(20, 8) NOT NULL DEFAULT 0.00,
                `{$this->col_matching_bonus}` DECIMAL(20, 8) NOT NULL DEFAULT 0.00,
                `{$this->col_royalty_bonus}` DECIMAL(20, 8) NOT NULL DEFAULT 0.00,
                `{$this->col_updated_at}` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
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
     * Membuat entri saldo awal untuk pengguna baru.
     *
     * @param int $userId ID pengguna yang baru dibuat.
     * @return bool True jika berhasil, false jika gagal.
     */
    public function createBalanceEntry(int $userId): bool
    {
        $conn = $this->dbConn();
        $sql = "INSERT INTO {$this->table_name} ({$this->col_user_id}) VALUES (?)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $userId);
        
        return $stmt->execute();
    }

    /**
     * Mengambil data saldo seorang pengguna.
     *
     * @param int $userId ID pengguna.
     * @return array|false Data saldo, atau false jika tidak ditemukan.
     */
    public function getUserBalance(int $userId)
    {
        $conn = $this->dbConn();
        $sql = "SELECT * FROM {$this->table_name} WHERE {$this->col_user_id} = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $balance = $result->fetch_assoc();
        $stmt->close();
        
        return $balance;
    }
    
    /**
     * Menambahkan sejumlah bonus ke salah satu kolom saldo pengguna.
     *
     * @param int $userId ID pengguna.
     * @param float $amount Jumlah bonus yang ditambahkan.
     * @param string $bonusType Tipe bonus ('staking_roi', 'matching_bonus', 'royalty_bonus').
     * @return bool True jika berhasil, false jika gagal.
     */
    public function addBonus(int $userId, float $amount, string $bonusType): bool
    {
        // Validasi tipe bonus untuk mencegah SQL injection
        $allowedTypes = [$this->col_staking_roi, $this->col_matching_bonus, $this->col_royalty_bonus];
        if (!in_array($bonusType, $allowedTypes)) {
            error_log("Invalid bonus type provided: " . $bonusType);
            return false;
        }

        $conn = $this->dbConn();
        // Menggunakan nama kolom secara dinamis setelah divalidasi
        $sql = "UPDATE {$this->table_name} SET `{$bonusType}` = `{$bonusType}` + ? WHERE {$this->col_user_id} = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('di', $amount, $userId);

        return $stmt->execute();
    }

    /**
     * FUNGSI BARU: Mengurangi saldo pengguna setelah penarikan berhasil.
     * Logika ini akan mengurangi saldo dari staking_roi terlebih dahulu,
     * kemudian matching_bonus, dan terakhir royalty_bonus.
     *
     * @param int $userId ID pengguna.
     * @param float $withdrawnAmount Jumlah total USDT yang ditarik.
     * @return bool True jika berhasil, false jika gagal.
     */
    public function deductBalancesAfterWithdrawal(int $userId, float $withdrawnAmount): bool
    {
        $conn = $this->dbConn();
        $currentBalance = $this->getUserBalance($userId);

        if (!$currentBalance) {
            error_log("Failed to deduct balance: user with ID {$userId} not found.");
            return false;
        }

        $roi = (float)$currentBalance[$this->col_staking_roi];
        $matching = (float)$currentBalance[$this->col_matching_bonus];
        $royalty = (float)$currentBalance[$this->col_royalty_bonus];

        // Pastikan total saldo mencukupi
        if (($roi + $matching + $royalty) < $withdrawnAmount) {
            error_log("Failed to deduct balance: insufficient funds for user ID {$userId}.");
            return false;
        }
        
        $amountLeftToDeduct = $withdrawnAmount;

        // 1. Kurangi dari Staking ROI
        $deductFromRoi = min($roi, $amountLeftToDeduct);
        $newRoi = $roi - $deductFromRoi;
        $amountLeftToDeduct -= $deductFromRoi;

        // 2. Kurangi dari Matching Bonus
        $deductFromMatching = min($matching, $amountLeftToDeduct);
        $newMatching = $matching - $deductFromMatching;
        $amountLeftToDeduct -= $deductFromMatching;

        // 3. Kurangi dari Royalty Bonus
        $deductFromRoyalty = min($royalty, $amountLeftToDeduct);
        $newRoyalty = $royalty - $deductFromRoyalty;

        // Lakukan update ke database
        $sql = "UPDATE {$this->table_name} SET 
                    {$this->col_staking_roi} = ?,
                    {$this->col_matching_bonus} = ?,
                    {$this->col_royalty_bonus} = ?
                WHERE {$this->col_user_id} = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('dddi', $newRoi, $newMatching, $newRoyalty, $userId);

        return $stmt->execute();
    }
}
?>

