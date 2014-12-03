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
$transactionLogHandle = fopen($toolsLogsDirectory . 'migration_sneakerhead_rewards_transaction_log', 'a+');
fwrite($transactionLogHandle, "->BEGIN PROCESSING...\n");

echo "GETTING CUSTOMERS\n";
$query = "SELECT `entity_id`, `created_at` FROM `customer_entity` WHERE `store_id` = 21";
$results = $writeConnection->query($query);
foreach ($results as $result) {
    
    //Do for all customers

    $query = "INSERT IGNORE INTO `rewardpoints_flat_account` (`user_id`, `store_id`, `points_collected`, `points_used`, `points_waiting`, `points_current`, `points_lost`, `last_check`) VALUES
(" . $result['entity_id'] . ", 21, 0, 0, 0, 0, 0, '" . $result['created_at'] . "')";
        $writeConnection->query($query);
        fwrite($transactionLogHandle, "      ->CREATED   : REWARD ACCOUNT FOR Customer ID: " . $result['entity_id'] . "\n");

}

fwrite($transactionLogHandle, "->END PROCESSING\n");
//Close transaction log
fclose($transactionLogHandle);

?>
