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
     * Mencari pengguna berdasarkan alamat wallet, atau membuat yang baru jika tidak ditemukan.
     * Ini adalah fungsi utama untuk login/registrasi pengguna.
     *
     * @param string $walletAddress Alamat wallet pengguna.
     * @param string|null $uplineWallet Alamat wallet referrer, jika ada.
     * @return array Data pengguna (baik yang sudah ada atau yang baru dibuat).
     */
    public function findOrCreateUser(string $walletAddress, ?string $uplineWallet = null): array
    {
        $existingUser = $this->getUserByWalletAddress($walletAddress);

        if ($existingUser) {
            return $existingUser;
        }

        // Pengguna tidak ada, buat yang baru
        $conn = $this->dbConn();
        $sql = "INSERT INTO {$this->table_name} ({$this->col_wallet_address}, {$this->col_upline_wallet}) VALUES (?, ?)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ss', $walletAddress, $uplineWallet);
        $stmt->execute();
        $newUserId = $stmt->insert_id;
        $stmt->close();

        // Mengembalikan data pengguna yang baru dibuat
        return [
            $this->col_id => $newUserId,
            $this->col_wallet_address => $walletAddress,
            $this->col_upline_wallet => $uplineWallet,
            $this->col_grade => null,
            // created_at akan diatur secara default di MySQL
        ];
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
        return $row['total'] ?? 0;
    }
}
?>

