<?php
/**
 * User Table Class
 * Menangani semua operasi database untuk tabel `users`.
 */
class UserTableClass extends connMySQL
{
    // --- Definisi Tabel dan Kolom ---
    private $table_name = "users";
    private $col_id = "id";
    private $col_wallet_address = "wallet_address";
    private $col_upline_wallet = "upline_wallet";
    private $col_grade = "grade";
    private $col_created_at = "created_at";

    /**
     * Constructor
     * Membangun koneksi DB dan membuat tabel `users` jika belum ada.
     */
    public function __construct()
    {
        // Memeriksa apakah tabel sudah ada
        if ($this->checkTable($this->table_name) == 0) {
            // Query SQL untuk membuat tabel berdasarkan skema kita
            $sql = "CREATE TABLE `{$this->table_name}` (
                `{$this->col_id}` INT AUTO_INCREMENT PRIMARY KEY,
                `{$this->col_wallet_address}` VARCHAR(42) NOT NULL UNIQUE,
                `{$this->col_upline_wallet}` VARCHAR(42) NULL,
                `{$this->col_grade}` CHAR(1) NULL,
                `{$this->col_created_at}` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                INDEX (`{$this->col_upline_wallet}`)
            )";

            // Eksekusi query untuk membuat tabel
            try {
                $this->dbConn()->query($sql);
                error_log("Tabel '{$this->table_name}' berhasil dibuat.");
            } catch (mysqli_sql_exception $e) {
                error_log("Error saat membuat tabel '{$this->table_name}': " . $e->getMessage());
            }
        }
    }

    /**
     * Mencari pengguna berdasarkan alamat wallet mereka.
     *
     * @param string $walletAddress Alamat wallet pengguna.
     * @return array|false Data pengguna dalam array jika berhasil, false jika tidak ditemukan.
     */
    public function getUserByWalletAddress(string $walletAddress)
    {
        $conn = $this->dbConn();
        $sql = "SELECT * FROM {$this->table_name} WHERE {$this->col_wallet_address} = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('s', $walletAddress);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();
        
        return $user;
    }
    
    /**
     * REVISI: Mencari pengguna berdasarkan ID mereka.
     *
     * @param int $userId ID pengguna.
     * @return array|false Data pengguna, atau false jika tidak ditemukan.
     */
    public function getUserById(int $userId)
    {
        $conn = $this->dbConn();
        $sql = "SELECT * FROM {$this->table_name} WHERE {$this->col_id} = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();
        
        return $user;
    }


    /**
     * REVISI: Logika findOrCreateUser diperbaiki.
     * Sekarang mengembalikan data lengkap pengguna baru dan flag 'is_new'.
     *
     * @param string $walletAddress Alamat wallet pengguna.
     * @param string|null $uplineWallet Alamat wallet referrer, jika ada.
     * @return array Berisi data pengguna dan flag 'is_new'
     */
    public function findOrCreateUser(string $walletAddress, ?string $uplineWallet = null): array
    {
        $existingUser = $this->getUserByWalletAddress($walletAddress);

        if ($existingUser) {
            return ['user' => $existingUser, 'is_new' => false];
        }

        // Pengguna tidak ada, buat yang baru
        $conn = $this->dbConn();
        $sql = "INSERT INTO {$this->table_name} ({$this->col_wallet_address}, {$this->col_upline_wallet}) VALUES (?, ?)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ss', $walletAddress, $uplineWallet);
        $stmt->execute();
        $newUserId = $stmt->insert_id;
        $stmt->close();
        
        // Ambil kembali data pengguna yang baru dibuat untuk memastikan konsistensi data
        $newUser = $this->getUserById($newUserId);

        return ['user' => $newUser, 'is_new' => true];
    }
    
    /**
     * Memperbarui grade seorang pengguna.
     *
     * @param int $userId ID pengguna yang akan di-update.
     * @param string $newGrade Grade baru (misal: 'A', 'B', 'C').
     * @return bool True jika berhasil, false jika gagal.
     */
    public function updateUserGrade(int $userId, string $newGrade): bool
    {
        $conn = $this->dbConn();
        $sql = "UPDATE {$this->table_name} SET {$this->col_grade} = ? WHERE {$this->col_id} = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('si', $newGrade, $userId);
        
        return $stmt->execute();
    }

    /**
     * Menghitung jumlah total downline langsung (level 1).
     *
     * @param string $uplineWallet Alamat wallet dari upline.
     * @return int Jumlah total downline.
     */
    public function getDirectDownlineCount(string $uplineWallet): int
    {
        $conn = $this->dbConn();
        $sql = "SELECT COUNT({$this->col_id}) as total FROM {$this->table_name} WHERE {$this->col_upline_wallet} = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('s', $uplineWallet);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        return (int)($row['total'] ?? 0);
    }

    /**
     * FUNGSI BARU: Mengambil semua data dari downline langsung.
     */
    public function getDirectDownlinesData(string $uplineWallet): array
    {
        $conn = $this->dbConn();
        $sql = "SELECT {$this->col_id}, {$this->col_wallet_address} 
                FROM {$this->table_name} 
                WHERE {$this->col_upline_wallet} = ? 
                ORDER BY {$this->col_id} DESC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('s', $uplineWallet);
        $stmt->execute();
        $result = $stmt->get_result();
        $downlines = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $downlines;
    }

    /**
     * FUNGSI BARU: Menggunakan query rekursif untuk mendapatkan statistik jaringan.
     */
    public function getNetworkStats(int $userId): array
    {
        $conn = $this->dbConn();
        $sql = "
            WITH RECURSIVE DownlineHierarchy AS (
                -- Anchor member: Start with the direct downlines of the given user
                SELECT id, wallet_address, upline_wallet
                FROM users
                WHERE upline_wallet = (SELECT wallet_address FROM users WHERE id = ?)

                UNION ALL

                -- Recursive member: Find downlines of the previous level
                SELECT u.id, u.wallet_address, u.upline_wallet
                FROM users u
                INNER JOIN DownlineHierarchy dh ON u.upline_wallet = dh.wallet_address
            )
            SELECT id FROM DownlineHierarchy;
        ";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $rows = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        $allMemberIds = [];
        foreach ($rows as $row) {
            $allMemberIds[] = $row['id'];
        }

        return [
            'total_members' => count($allMemberIds),
            'all_member_ids' => $allMemberIds
        ];
    }
}
?>

