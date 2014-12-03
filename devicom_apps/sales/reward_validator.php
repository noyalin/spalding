<?php

require(dirname(dirname(__FILE__)) . '/lib/Devicom/config.php');

// Initialize magento environment for 'admin' store
require_once dirname(dirname(dirname(__FILE__))) . '/app/Mage.php';
umask(0);
Mage::app('admin'); //admin defines default value for all stores including the Main Website

//Check if reward system enabled
$modules = Mage::getConfig()->getNode('modules')->children();
$modulesArray = (array)$modules;

if(!isset($modulesArray['J2t_Rewardproductvalue'])) {
    exit;
}

$salesLogsDirectory = dirname(__FILE__) . '/logs/';

$realTime = realTime();

//Open transaction log
$transactionLogHandle = fopen($salesLogsDirectory . 'reward_validator_transaction_log', 'a+');
fwrite($transactionLogHandle, "->BEGIN PROCESSING: AUTO\n");
fwrite($transactionLogHandle, "  ->START TIME    : " . $realTime[5] . '/' . $realTime[4] . '/' . $realTime[3] . ' ' . $realTime[2] . ':' . $realTime[1] . ':' . $realTime[0] . "\n");

try {
    // Get resource
    $resource = Mage::getSingleton('core/resource');
    $readConnection = $resource->getConnection('core_read');
    $writeConnection = $resource->getConnection('core_write');

    // Run query to find all pending rewards and see if 30 days has passed since order shipped date -- if so, validate points
    $query = "SELECT `drv`.`increment_id` FROM `rewardpoints_account` AS `ra` INNER JOIN `devicom_reward_validation` AS `drv` ON `ra`.`order_id` = `drv`.`increment_id` WHERE `drv`.`created_at` < DATE_SUB(CURDATE(), INTERVAL 30 DAY) AND `ra`.`order_id` > '100000000' AND `ra`.`rewardpoints_referral_id` IS NULL AND (`drv`.`status` != 'complete')";
    $results = $readConnection->query($query);

    fwrite($transactionLogHandle, "  ->STATUS CHANGE : STARTED\n");

    foreach ($results as $result) {

	fwrite($transactionLogHandle, "    ->FOR ORDER   : " . $result['increment_id'] . "\n");

	// Get order status
	$query = "SELECT `status`, `increment_id` FROM `sales_flat_order` WHERE `increment_id` = '" . $result['increment_id'] . "'";
	$orderStatusResults = $readConnection->query($query);

	foreach ($orderStatusResults as $orderStatusResult) {
	    $currentStatus = $orderStatusResult['status'];
	    fwrite($transactionLogHandle, "      ->STATUS    : CURRENT    : " . $currentStatus . "\n");

	    // Change status to complete for final reward point adjustment if any
	    switch ($currentStatus) {
		case 'shipped':
		    $status = 'shipped_complete';
		    $state = 'complete';
		    break;
		case 'partially_shipped':
		    $status = 'partially_shipped_complete';
		    $state = 'complete';
		    break;
		case 'backordered':
		    $status = 'backordered_complete';
		    $state = 'complete';
		    break;
		case 'cancel':
		    $status = 'cancel_complete';
		    $state = 'complete';
		    break;
		case 'returned':
		    $status = 'returned_complete';
		    $state = 'complete';
		    break;
		case 'complete';//partial refund
		    $status = 'returned_complete';
		    $state = 'complete';
		    break;
		case 'closed';//full refund
		    $status = 'returned_complete';
		    $state = 'complete';
		    break;
		default:
		    $message = "You are being notified that the status for order number " . $result['increment_id'] . " could not be matched.\r\n\r\n";
		    sendNotification($subject = 'STATUS NOT FOUND', $message);
		    continue;
	    }

	    if ($status && $state) {
		$query = "UPDATE `sales_flat_order` SET `status` = '" . $status . "', `state` = '" . $state . "' WHERE `increment_id` = '" . $result['increment_id'] . "'";
		$writeConnection->query($query);

		fwrite($transactionLogHandle, "      ->STATUS    : CHANGED TO : " . $status . " (sales_flat_order)\n");

		$query = "UPDATE `sales_flat_order_grid` SET `status` = '" . $status . "' WHERE `increment_id` = '" . $result['increment_id'] . "'";
		$writeConnection->query($query);

		fwrite($transactionLogHandle, "      ->STATUS    : CHANGED TO : " . $status . " (sales_flat_order_grid)\n");

		// Change status in devicom_rweard_validation table to indicate adjustment complete
		$query = "UPDATE `devicom_reward_validation` SET `status` = 'complete', `adjusted_at` = CURDATE() WHERE `increment_id` = '" . $result['increment_id'] . "'";
		$writeConnection->query($query);

		fwrite($transactionLogHandle, "      ->REWARD    : STATUS     : COMPLETE\n");
	    }

	    //If customerId then update points for order reward transaction
	    $order = Mage::getModel('sales/order')->loadByIncrementId($result['increment_id']);
	    $customerId = $order->getCustomerId();
	    $storeId = $order->getStoreId();

	    if ($customerId) {
		//Refresh rewarpoints_flat _account table
		RewardPoints_Model_Observer::processRecordFlatRefresh($customerId, $storeId);
		fwrite($transactionLogHandle, "      ->ADJUSTMENT: FLAT TABLE REFRESHED : " . $customerId . "\n");
	    }
	}
    }
    fwrite($transactionLogHandle, "  ->STATUS CHANGE : FINISHED\n");
} catch (Exception $e) {
    fwrite($transactionLogHandle, "  ->ERROR         : See exception_log\n");

    //Append error to exception log file
    $exceptionLogHandle = fopen($salesLogsDirectory . 'exception_log', 'a');
    fwrite($exceptionLogHandle, "->AUTO - " . $e->getMessage() . "\n");
    fclose($exceptionLogHandle);
}

$endTime = realTime();
fwrite($transactionLogHandle, "  ->FINISH TIME   : " . $endTime[5] . '/' . $endTime[4] . '/' . $endTime[3] . ' ' . $endTime[2] . ':' . $endTime[1] . ':' . $endTime[0] . "\n");
fwrite($transactionLogHandle, "->END PROCESSING  : AUTO\n");

//Close transaction log
fclose($transactionLogHandle);

?>