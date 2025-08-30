<?php
/**
 * Transaction Table Class
 * Menangani semua operasi database untuk tabel `transactions`.
 */
class TransactionTableClass extends connMySQL
{
    // --- Definisi Tabel dan Kolom ---
    private $table_name = "transactions";
    private $col_id = "id";
    private $col_user_id = "user_id";
    private $col_related_stake_id = "related_stake_id";
    private $col_type = "type";
    private $col_amount_usdt = "amount_usdt";
    private $col_amount_langit = "amount_langit"; // Kolom baru
    private $col_tx_hash = "tx_hash";
    private $col_created_at = "created_at";

    /**
     * Constructor
     * Membangun koneksi DB dan membuat tabel `transactions` jika belum ada.
     */
    public function __construct()
    {
        if ($this->checkTable($this->table_name) == 0) {
            // SQL diperbarui sesuai skema database final
            $sql = "CREATE TABLE `{$this->table_name}` (
                `{$this->col_id}` INT AUTO_INCREMENT PRIMARY KEY,
                `{$this->col_user_id}` INT NOT NULL,
                `{$this->col_related_stake_id}` INT NULL,
                `{$this->col_type}` ENUM('stake', 'withdraw', 'claim_vesting', 'matching_bonus_in', 'staking_roi_in', 'royalty_bonus_in') NOT NULL,
                `{$this->col_amount_usdt}` DECIMAL(20, 8) NULL,
                `{$this->col_amount_langit}` DECIMAL(36, 18) NULL,
                `{$this->col_tx_hash}` VARCHAR(66) NULL,
                `{$this->col_created_at}` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (`{$this->col_user_id}`) REFERENCES `users`(`id`) ON DELETE CASCADE,
                FOREIGN KEY (`{$this->col_related_stake_id}`) REFERENCES `stakes`(`id`) ON DELETE SET NULL
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
     * Mencatat transaksi baru ke dalam database.
     *
     * @param array $data Data transaksi yang berisi semua kolom yang diperlukan.
     * @return int|false ID dari transaksi yang baru dicatat, atau false jika gagal.
     */
    public function createTransaction(array $data)
    {
        $conn = $this->dbConn();
        // SQL diperbarui untuk memasukkan kolom amount_langit
        $sql = "INSERT INTO {$this->table_name} (
                    {$this->col_user_id}, 
                    {$this->col_related_stake_id}, 
                    {$this->col_type}, 
                    {$this->col_amount_usdt},
                    {$this->col_amount_langit},
                    {$this->col_tx_hash}
                ) VALUES (?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);

        // Menyiapkan nilai, menggunakan null jika tidak ada
        $userId = $data['user_id'];
        $relatedStakeId = $data['related_stake_id'] ?? null;
        $type = $data['type'];
        $amountUsdt = $data['amount_usdt'] ?? null;
        $amountLangit = $data['amount_langit'] ?? null;
        $txHash = $data['tx_hash'] ?? null;
        
        // bind_param diperbarui untuk menangani 6 parameter
        $stmt->bind_param(
            'iisdds',
            $userId,
            $relatedStakeId,
            $type,
            $amountUsdt,
            $amountLangit,
            $txHash
        );
        
        if ($stmt->execute()) {
            return $stmt->insert_id;
        }
        return false;
    }

    /**
     * REVISI: Logika filter bonus diimplementasikan di sini.
     * Mengambil daftar transaksi seorang pengguna dengan filter dan paginasi.
     */
    public function getUserTransactions(int $userId, ?string $type = null, int $limit = 20, int $offset = 0): array
    {
        $conn = $this->dbConn();
        $params = [];
        $types = '';
        
        $sql = "SELECT * FROM {$this->table_name} WHERE {$this->col_user_id} = ?";
        $params[] = $userId;
        $types .= 'i';

        if ($type !== null && $type !== 'all') {
            if ($type === 'bonus') {
                $sql .= " AND {$this->col_type} IN (?, ?, ?)";
                $params[] = 'staking_roi_in';
                $params[] = 'matching_bonus_in';
                $params[] = 'royalty_bonus_in';
                $types .= 'sss';
            } else {
                $sql .= " AND {$this->col_type} = ?";
                $params[] = $type;
                $types .= 's';
            }
        }

        $sql .= " ORDER BY {$this->col_created_at} DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        $types .= 'ii';
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        $transactions = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $transactions;
    }

    /**
     * REVISI: Logika filter bonus diimplementasikan di sini.
     * Menghitung total jumlah transaksi untuk paginasi.
     */
    public function countUserTransactions(int $userId, ?string $type = null): int
    {
        $conn = $this->dbConn();
        $params = [];
        $types = '';
        
        $sql = "SELECT COUNT({$this->col_id}) as total FROM {$this->table_name} WHERE {$this->col_user_id} = ?";
        $params[] = $userId;
        $types .= 'i';

        if ($type !== null && $type !== 'all') {
            if ($type === 'bonus') {
                $sql .= " AND {$this->col_type} IN (?, ?, ?)";
                $params[] = 'staking_roi_in';
                $params[] = 'matching_bonus_in';
                $params[] = 'royalty_bonus_in';
                $types .= 'sss';
            } else {
                $sql .= " AND {$this->col_type} = ?";
                $params[] = $type;
                $types .= 's';
            }
        }
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        
        return (int)($row['total'] ?? 0);
    }
}
?>

