<?php
/**
 * MarketPriceFetcher Class
 *
 * REVISI: Bertanggung jawab untuk mengambil harga pasar terbaru dari token LANGIT
 * dari API publik DexScreener, yang melacak data dari PancakeSwap.
 * Kelas ini juga mengimplementasikan sistem caching sederhana untuk mengurangi
 * beban API dan mempercepat respons.
 */
class MarketPriceFetcher
{
    // REVISI: URL endpoint API diubah ke DexScreener.
    // PENTING: Anda harus mengganti 'YOUR_PANCAKESWAP_PAIR_ADDRESS_HERE' dengan alamat
    // kontrak pair (LP Token) dari LANGIT/USDT atau LANGIT/WBNB di PancakeSwap.
    // Anda bisa mendapatkan alamat ini dari halaman pair di PancakeSwap atau DexScreener.
    private const DEXSCREENER_API_URL = 'https://api.dexscreener.com/latest/dex/pairs/bsc/0x90aE4D066D8bF6656A0D1A81033aB801eFCA1f3F';
    
    // Lokasi file cache
    private $cacheFile;
    // Durasi cache dalam detik (contoh: 5 menit)
    private const CACHE_DURATION = 300; 

    public function __construct()
    {
        // Menentukan path file cache di dalam folder 'cache' di root proyek
        // Pastikan folder 'cache' ada dan bisa ditulis (writable) oleh server.
        $this->cacheFile = __DIR__ . '/../cache/market_price.json';
    }

    /**
     * Metode utama untuk mendapatkan harga Langit dalam USDT.
     * Metode ini akan mencoba mengambil harga dari cache terlebih dahulu.
     *
     * @return string|null Harga terakhir sebagai string untuk presisi, atau null jika terjadi error.
     */
    public function getLangitPriceInUsdt(): ?string
    {
        // 1. Coba ambil dari cache
        $cachedPrice = $this->getPriceFromCache();
        if ($cachedPrice !== null) {
            return $cachedPrice;
        }

        // 2. Jika cache tidak valid, ambil dari API
        $livePrice = $this->fetchPriceFromApi();
        if ($livePrice !== null) {
            // 3. Simpan harga baru ke cache
            $this->savePriceToCache($livePrice);
            return $livePrice;
        }
        
        // Gagal mengambil harga
        error_log("Gagal total mengambil harga LANGIT/USDT baik dari cache maupun API.");
        return null;
    }

    /**
     * REVISI: Mengambil harga dari API DexScreener menggunakan cURL.
     */
    private function fetchPriceFromApi(): ?string
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, self::DEXSCREENER_API_URL);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10); // Timeout 10 detik
        // Menambahkan User-Agent untuk menghindari blokir dari beberapa API
        curl_setopt($ch, CURLOPT_USERAGENT, 'LangitStaking Price Fetcher');

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            error_log('cURL Error saat mengambil harga: ' . curl_error($ch));
            curl_close($ch);
            return null;
        }

        curl_close($ch);

        $data = json_decode($response, true);

        // REVISI: Validasi struktur respons dari DexScreener
        if (isset($data['pair']) && isset($data['pair']['priceUsd'])) {
            return $data['pair']['priceUsd']; // 'priceUsd' adalah harga dalam USD
        }
        
        error_log("Respons API DexScreener tidak valid atau mengandung error: " . $response);
        return null;
    }

    /**
     * Mengambil harga dari file cache jika masih valid.
     */
    private function getPriceFromCache(): ?string
    {
        if (file_exists($this->cacheFile)) {
            $cacheContent = file_get_contents($this->cacheFile);
            $cacheData = json_decode($cacheContent, true);

            // Cek apakah data cache valid dan belum kedaluwarsa
            if (isset($cacheData['timestamp']) && isset($cacheData['price'])) {
                if (time() - $cacheData['timestamp'] < self::CACHE_DURATION) {
                    return $cacheData['price'];
                }
            }
        }
        return null;
    }

    /**
     * Menyimpan harga yang baru diambil ke file cache.
     */
    private function savePriceToCache(string $price): void
    {
        $cacheData = [
            'price' => $price,
            'timestamp' => time()
        ];
        // Pastikan direktori 'cache' ada
        if (!is_dir(dirname($this->cacheFile))) {
            mkdir(dirname($this->cacheFile), 0755, true);
        }
        file_put_contents($this->cacheFile, json_encode($cacheData));
    }
}
?>

