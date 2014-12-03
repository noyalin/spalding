<?php
//NOTES:
//Some 20 plus rewards will not be imported as several customers have been deleted from Practical Data database

$toolsLogsDirectory = '/chroot/home/rxkicksc/rxkicks.com/html/devicom_apps/tools/logs/';

// Initialize magento environment for 'admin' store
require_once '/chroot/home/rxkicksc/rxkicks.com/html/app/Mage.php';
umask(0);
Mage::app('admin'); //admin defines default value for all stores including the Main Website

// Get resource
$resource = Mage::getSingleton('core/resource');
$writeConnection = $resource->getConnection('core_write');

//Open transaction log
$transactionLogHandle = fopen($toolsLogsDirectory . 'clean_duplicate_reward_transaction_log', 'a+');
fwrite($transactionLogHandle, "->BEGIN PROCESSING...\n");

//2
$query = "SELECT `rewardpoints_account_id`, `order_id` FROM `rewardpoints_account` WHERE `store_id` = 21 AND `order_id` > '600000000' GROUP BY `order_id` having count(*) >= 2";
$deleteResults = $writeConnection->query($query);
foreach ($deleteResults as $deleteResult) {
    $query = "DELETE FROM `rewardpoints_account` WHERE `rewardpoints_account_id` = " . $deleteResult['rewardpoints_account_id'];
    $writeConnection->query($query);
    fwrite($transactionLogHandle, "  ->DELETE      : 2 : " . $deleteResult['order_id'] . "\n");
}

//3
$query = "SELECT `rewardpoints_account_id`, `order_id` FROM `rewardpoints_account` WHERE `store_id` = 21 AND `order_id` > '600000000' GROUP BY `order_id` having count(*) >= 2";
$deleteResults = $writeConnection->query($query);
foreach ($deleteResults as $deleteResult) {
    $query = "DELETE FROM `rewardpoints_account` WHERE `rewardpoints_account_id` = " . $deleteResult['rewardpoints_account_id'];
    $writeConnection->query($query);
    fwrite($transactionLogHandle, "  ->DELETE      : 3 : " . $deleteResult['order_id'] . "\n");
}

//4
$query = "SELECT `rewardpoints_account_id`, `order_id` FROM `rewardpoints_account` WHERE `store_id` = 21 AND `order_id` > '600000000' GROUP BY `order_id` having count(*) >= 2";
$deleteResults = $writeConnection->query($query);
foreach ($deleteResults as $deleteResult) {
    $query = "DELETE FROM `rewardpoints_account` WHERE `rewardpoints_account_id` = " . $deleteResult['rewardpoints_account_id'];
    $writeConnection->query($query);
    fwrite($transactionLogHandle, "  ->DELETE      : 4 : " . $deleteResult['order_id'] . "\n");
}

//5
$query = "SELECT `rewardpoints_account_id`, `order_id` FROM `rewardpoints_account` WHERE `store_id` = 21 AND `order_id` > '600000000' GROUP BY `order_id` having count(*) >= 2";
$deleteResults = $writeConnection->query($query);
foreach ($deleteResults as $deleteResult) {
    $query = "DELETE FROM `rewardpoints_account` WHERE `rewardpoints_account_id` = " . $deleteResult['rewardpoints_account_id'];
    $writeConnection->query($query);
    fwrite($transactionLogHandle, "  ->DELETE      : 5 : " . $deleteResult['order_id'] . "\n");
}

$query = "SELECT `customer_id` FROM `rewardpoints_account` WHERE `order_id` >= '600402239'";
$refreshResults = $writeConnection->query($query);
foreach ($refreshResults as $refreshResult) {
    $customerId = $refreshResult['customer_id'];
    $storeId = 21;
    //Refresh rewarpoints_flat _account table
    RewardPoints_Model_Observer::processRecordFlatRefresh($customerId, $storeId);
    fwrite($transactionLogHandle, "  ->REFRESH     : FLAT TABLE REFRESHED : " . $customerId . "\n");
}

fwrite($transactionLogHandle, "->END PROCESSING\n");
//Close transaction log
fclose($transactionLogHandle);

?>
