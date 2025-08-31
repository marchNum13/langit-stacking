<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') exit;

require_once '../database/connMySQL.php';
require_once '../database/UserTableClass.php';
require_once '../database/BalanceTableClass.php';
require_once '../database/StakeTableClass.php';
require_once '../utils/MarketPriceFetcher.php';

function send_error($message) {
    echo json_encode(['status' => 'error', 'message' => $message]);
    exit;
}

if (!isset($_SESSION['wallet_address'])) send_error('User not authenticated.');
$walletAddress = $_SESSION['wallet_address'];

$data = json_decode(file_get_contents('php://input'), true);
if (!isset($data['amount_usdt']) || !is_numeric($data['amount_usdt'])) {
    send_error('Invalid withdrawal amount.');
}
$withdrawAmountUSDT = (float)$data['amount_usdt'];

try {
    $userTable = new UserTableClass();
    $balanceTable = new BalanceTableClass();
    $stakeTable = new StakeTableClass();
    $priceFetcher = new MarketPriceFetcher();

    $user = $userTable->getUserByWalletAddress($walletAddress);
    if (!$user) send_error('User not found.');
    $userId = $user['id'];

    // Validasi dasar
    if ($withdrawAmountUSDT < 5) send_error('Minimum withdrawal is $5 USDT.');
    
    $balances = $balanceTable->getUserBalance($userId);
    $totalBalance = (float)$balances['staking_roi'] + (float)$balances['matching_bonus'] + (float)$balances['royalty_bonus'];
    if ($withdrawAmountUSDT > $totalBalance) send_error('Insufficient withdrawable balance.');

    // Validasi 500%
    $activeStake = $stakeTable->getUserStakesByStatus($userId, 'active');
    if (empty($activeStake)) send_error('No active stake found to process withdrawal.');
    
    $stake = $activeStake[0]; // Hanya ada satu stake aktif
    $totalWithdrawn = (float)$stake['total_withdrawn_usdt'];
    $maxProfit = (float)$stake['max_profit_usdt'];
    
    $finalAmountToWithdrawUSDT = $withdrawAmountUSDT;
    $isBurn = false;

    if (($totalWithdrawn + $withdrawAmountUSDT) >= $maxProfit) {
        $finalAmountToWithdrawUSDT = $maxProfit - $totalWithdrawn;
        $isBurn = true;
        if ($finalAmountToWithdrawUSDT <= 0) {
             send_error('You have reached your 500% profit limit. No more withdrawals are possible on this stake.');
        }
    }

    // Konversi ke LANGIT
    $langitPrice = $priceFetcher->getLangitPriceInUsdt();
    if ($langitPrice === null) send_error('Could not fetch market price.');
    $finalAmountInLangit = $finalAmountToWithdrawUSDT / (float)$langitPrice;

    $response_data = [
        'stake_id_onchain' => $stake['stake_id_onchain'],
        'amount_in_langit_wei' => number_format($finalAmountInLangit * (10**18), 0, '.', ''), // Kirim dalam format WEI
        'is_burn' => $isBurn,
        'actual_withdraw_usdt' => $finalAmountToWithdrawUSDT // Kirim jumlah USDT final
    ];

    echo json_encode(['status' => 'success', 'data' => $response_data]);

} catch (Exception $e) {
    error_log("API prepare_withdraw error: " . $e->getMessage());
    send_error('An internal server error occurred.');
}
?>
