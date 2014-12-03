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

//REWARDS
//echo "GETTING COMPLETED orderRewardGrant\n";//pending and completed
//$query = "SELECT `u`.`email`, '21' AS `store_id`, `so`.`yahooOrderIdNumeric` + 600000000 AS `order_id`, `rt`.`points` AS 'points_current', `rt`.`transactionType`, `rt`.`transactionDate` AS `date_start`
//FROM `rewardsTransaction` AS `rt`
//LEFT JOIN `users` AS `u` ON `u`.`uid` = `rt`.`uid`
//LEFT JOIN `storeorder` AS `so` ON `so`.`orderId` = `rt`.`orderId`
//WHERE `rt`.`transactionType` = 'orderRewardGrant' AND `rt`.`status` != 'cancelled'";
//$results = $writeConnection->query($query);
//$i = 1;
//foreach ($results as $result) {
//
//    //SELECT customer
//    $customerQuery = "SELECT `entity_id` FROM `customer_entity` WHERE `email` = '" . $result['email'] . "' AND `website_id` = 21";
//    echo $customerQuery . "\n";
//    $customerResults = $writeConnection->query($customerQuery);
//    foreach ($customerResults as $customerResult) {  
//	//Add review detail
//	$rewardQuery = "INSERT INTO `rewardpoints_account` (`customer_id`, `store_id`, `order_id`, `points_current`, `points_spent`, `date_start`, `date_end`, `convertion_rate`, `rewardpoints_referral_id`, `quote_id`) VALUES (" . $customerResult['entity_id'] . ", '21', '" . $result['order_id'] . "', " . $result['points_current'] . ", 0, '" . $result['date_start'] . "', NULL, 0, NULL, NULL)";
//	echo $rewardQuery . "\n";
//	$rewardResults = $writeConnection->query($rewardQuery);
//	fwrite($transactionLogHandle, "    ->ADDED       : Reward COMPLETED orderRewardGrant : " . $i . " - Customer ID: " . $customerResult['entity_id'] . " -> Points: " . $result['points_current'] . "\n");
//    }
//    $i++;
//}
//
//echo "GETTING COMPLETED orderRewardRevoke\n";
//$query = "SELECT `u`.`email`, '21' AS `store_id`, `so`.`yahooOrderIdNumeric` + 600000000 AS `order_id`, - `rt`.`points` AS 'points_spent', 
//`rt`.`transactionType`, `rt`.`transactionDate` AS `date_start`
//FROM `rewardsTransaction` AS `rt`
//LEFT JOIN `users` AS `u` ON `u`.`uid` = `rt`.`uid`
//LEFT JOIN `storeorder` AS `so` ON `so`.`orderId` = `rt`.`orderId`
//WHERE `rt`.`transactionType` = 'orderRewardRevoke' AND `rt`.`status` = 'completed'";
//$results = $writeConnection->query($query);
//$i = 1;
//foreach ($results as $result) {
//
//    //SELECT customer
//    $customerQuery = "SELECT `entity_id` FROM `customer_entity` WHERE `email` = '" . $result['email'] . "' AND `website_id` = 21";
//    echo $customerQuery . "\n";
//    $customerResults = $writeConnection->query($customerQuery);
//    foreach ($customerResults as $customerResult) {  
//	//Add review detail
//	$rewardQuery = "INSERT INTO `rewardpoints_account` (`customer_id`, `store_id`, `order_id`, `points_current`, `points_spent`, `date_start`, `date_end`, `convertion_rate`, `rewardpoints_referral_id`, `quote_id`) VALUES (" . $customerResult['entity_id'] . ", '21', '" . $result['order_id'] . "', 0, " . $result['points_spent'] . ", '" . $result['date_start'] . "', NULL, NULL, NULL, NULL)";
//	echo $rewardQuery . "\n";
//	$rewardResults = $writeConnection->query($rewardQuery);
//	fwrite($transactionLogHandle, "    ->ADDED       : Reward COMPLETED orderRewardRevoke : " . $i . " - Customer ID: " . $customerResult['entity_id'] . " -> Points: " . $result['points_spent'] . "\n");
//    }
//    $i++;
//}
echo "GETTING ORDERS\n";
$query = "SELECT `increment_id` FROM `sales_flat_order` WHERE `increment_id` >= '600408225' AND `increment_id` <= '600410853'";
$results = $writeConnection->query($query);
foreach ($results as $result) {
    
    $order = Mage::getModel('sales/order')->loadByIncrementId($result['increment_id']);
    if (!$order->getId()) {
        fwrite($transactionLogHandle, "  ->ORDER       : ORDER NOT FOUND\n");
        continue;
    }

    fwrite($transactionLogHandle, "  ->ORDER LOADED: " . $result['increment_id'] . "\n");
    
    //For rewards adjustment
    $customerId = $order->getCustomerId();
    $orderDate = $order->getCreatedAt();
    $orderId = $result['increment_id'];
    $pointsCurrent = $order->getBaseSubtotal();

    //Do for all orders
    if ($customerId) {
        $query = "INSERT INTO `rewardpoints_account` (`customer_id`, `store_id`, `order_id`, `points_current`, `points_spent`, `date_start`, `date_end`, `convertion_rate`, `rewardpoints_referral_id`, `quote_id`) VALUES (" . $customerId .", '21', '" . $orderId . "', " . $pointsCurrent . ", 0, '" . $orderDate . "', NULL, 0, NULL, NULL)";
        $writeConnection->query($query);
        fwrite($transactionLogHandle, "    ->CREATED   : REWARD POINTS FOR Customer ID: " . $customerId .  " : " . $pointsCurrent . "\n");

	//If customerId then update points for order reward transaction
	if ($customerId) {
	    //Refresh rewarpoints_flat _account table -- hard-coded 21
	    RewardPoints_Model_Observer::processRecordFlatRefresh($customerId, 21);
	    fwrite($transactionLogHandle, "      ->ADJUSTMENT: FLAT TABLE REFRESHED : " . $customerId . "\n");
	}
    }
    fwrite($transactionLogHandle, "  ->COMPLETED   : " . $result['increment_id'] . "\n");
}

echo "GETTING POSITIVE manualAdjustment\n";
$query = "SELECT `u`.`email`, '21' AS `store_id`, -1 AS `order_id`, `rt`.`points` AS 'points_current', `rt`.`transactionType`, 
`rt`.`transactionDate` AS `date_start`
FROM `rewardsTransaction` AS `rt`
LEFT JOIN `users` AS `u` ON `u`.`uid` = `rt`.`uid`
WHERE `rt`.`transactionType` = 'manualAdjustment' AND `rt`.`points` > 0";
$results = $writeConnection->query($query);
$i = 1;
foreach ($results as $result) {

    //SELECT customer
    $customerFound = NULL;
    $customerQuery = "SELECT `entity_id` FROM `customer_entity` WHERE `email` = '" . $result['email'] . "' AND `website_id` = 21";
    //echo $customerQuery . "\n";
    $customerResults = $writeConnection->query($customerQuery);
    foreach ($customerResults as $customerResult) {  
	fwrite($transactionLogHandle, "  ->CUSTOMER    : " . $result['email'] . "\n");
	//Add review detail
	$customerFound = 1;
	$rewardQuery = "INSERT INTO `rewardpoints_account` (`customer_id`, `store_id`, `order_id`, `points_current`, `points_spent`, `date_start`, `date_end`, `convertion_rate`, `rewardpoints_referral_id`, `quote_id`) VALUES (" . $customerResult['entity_id'] . ", '21', '" . $result['order_id'] . "', " . $result['points_current'] . ", 0, '" . $result['date_start'] . "', NULL, 0, NULL, NULL)";
	//echo $rewardQuery . "\n";
	$rewardResults = $writeConnection->query($rewardQuery);
	fwrite($transactionLogHandle, "    ->ADDED     : Reward POSITIVE manualAdjustment : " . $i . " - Customer ID: " . $customerResult['entity_id'] . " -> Points: " . $result['points_current'] . "\n");
	
	//If customerId then update points for order reward transaction
	if ($customerResult['entity_id']) {
	    //Refresh rewarpoints_flat _account table -- hard-coded 21
	    RewardPoints_Model_Observer::processRecordFlatRefresh($customerResult['entity_id'], 21);
	    fwrite($transactionLogHandle, "      ->ADJUSTMENT: FLAT TABLE REFRESHED : " . $customerResult['entity_id'] . "\n");
	}
	
	fwrite($transactionLogHandle, "  ->DONE        : " . $result['email'] . "\n");
    }
    if (!$customerFound) {
	fwrite($transactionLogHandle, "  ->NOT FOUND   : " . $result['email'] . "\n");
    }
    $i++;
}

echo "GETTING NEGATIVE manualAdjustment\n";
$query = "SELECT `u`.`email`, '21' AS `store_id`, -1 AS `order_id`, - `rt`.`points` AS 'points_spent', `rt`.`transactionType`, `rt`.`transactionDate` AS `date_start`
FROM `rewardsTransaction` AS `rt`
LEFT JOIN `users` AS `u` ON `u`.`uid` = `rt`.`uid`
WHERE `rt`.`transactionType` = 'manualAdjustment' AND `rt`.`points` < 0";
$results = $writeConnection->query($query);
$i = 1;
foreach ($results as $result) {

    //SELECT customer
    $customerFound = NULL;
    $customerQuery = "SELECT `entity_id` FROM `customer_entity` WHERE `email` = '" . $result['email'] . "' AND `website_id` = 21";
    //echo $customerQuery . "\n";
    $customerResults = $writeConnection->query($customerQuery);
    foreach ($customerResults as $customerResult) {
	fwrite($transactionLogHandle, "  ->CUSTOMER    : " . $result['email'] . "\n");

	//Add review detail
	$customerFound = 1;
	$rewardQuery = "INSERT INTO `rewardpoints_account` (`customer_id`, `store_id`, `order_id`, `points_current`, `points_spent`, `date_start`, `date_end`, `convertion_rate`, `rewardpoints_referral_id`, `quote_id`) VALUES (" . $customerResult['entity_id'] . ", '21', '" . $result['order_id'] . "', 0, " . $result['points_spent'] . ", '" . $result['date_start'] . "', NULL, NULL, NULL, NULL)";
	//echo $rewardQuery . "\n";
	$rewardResults = $writeConnection->query($rewardQuery);
	fwrite($transactionLogHandle, "    ->ADDED     : Reward NEGATIVE manualAdjustment : " . $i . " - Customer ID: " . $customerResult['entity_id'] . " -> Points: " . $result['points_spent'] . "\n");

	//If customerId then update points for order reward transaction
	if ($customerResult['entity_id']) {
	    //Refresh rewarpoints_flat _account table -- hard-coded 21
	    RewardPoints_Model_Observer::processRecordFlatRefresh($customerResult['entity_id'], 21);
	    fwrite($transactionLogHandle, "      ->ADJUSTMENT: FLAT TABLE REFRESHED : " . $customerResult['entity_id'] . "\n");
	}
	
	fwrite($transactionLogHandle, "  ->DONE        : " . $result['email'] . "\n");
    }
    if (!$customerFound) {
	fwrite($transactionLogHandle, "  ->NOT FOUND   : " . $result['email'] . "\n");
    }
    $i++;
}

echo "GETTING REVIEW REWARDS\n";
$query = "SELECT `u`.`email`, '21' AS `store_id`, -2 AS `order_id`, `rt`.`points` AS 'points_current', `rt`.`transactionType`, `rt`.`transactionDate` AS `date_start`, `rt`.`reviewId` AS `quote_id`
FROM `rewardsTransaction` AS `rt`
LEFT JOIN `users` AS `u` ON `u`.`uid` = `rt`.`uid`
WHERE `rt`.`transactionType` = 'review' AND `rt`.`status` = 
'completed'";
$results = $writeConnection->query($query);
$i = 1;
foreach ($results as $result) {

    //SELECT customer
    $customerFound = NULL;
    $customerQuery = "SELECT `entity_id` FROM `customer_entity` WHERE `email` = '" . $result['email'] . "' AND `website_id` = 21";
    //echo $customerQuery . "\n";
    $customerResults = $writeConnection->query($customerQuery);
    foreach ($customerResults as $customerResult) {
	fwrite($transactionLogHandle, "  ->CUSTOMER    : " . $result['email'] . "\n");

	//Add review detail
	$customerFound = 1;
	$rewardQuery = "INSERT INTO `rewardpoints_account` (`customer_id`, `store_id`, `order_id`, `points_current`, `points_spent`, `date_start`, `date_end`, `convertion_rate`, `rewardpoints_referral_id`, `quote_id`) VALUES (" . $customerResult['entity_id'] . ", '21', '-2', " . $result['points_current'] . ", 0, '" . $result['date_start'] . "', NULL, 0, NULL, " . $result['quote_id'] . ")";
	//echo $rewardQuery . "\n";
	$rewardResults = $writeConnection->query($rewardQuery);
	fwrite($transactionLogHandle, "    ->ADDED     : Reward COMPLETED review : " . $i . " - Customer ID: " . $customerResult['entity_id'] . " -> Points: " . $result['points_current'] . "\n");

	//If customerId then update points for order reward transaction
	if ($customerResult['entity_id']) {
	    //Refresh rewarpoints_flat _account table -- hard-coded 21
	    RewardPoints_Model_Observer::processRecordFlatRefresh($customerResult['entity_id'], 21);
	    fwrite($transactionLogHandle, "      ->ADJUSTMENT: FLAT TABLE REFRESHED : " . $customerResult['entity_id'] . "\n");
	}
	
	fwrite($transactionLogHandle, "  ->DONE        : " . $result['email'] . "\n");
    }
    if (!$customerFound) {
	fwrite($transactionLogHandle, "  ->NOT FOUND   : " . $result['email'] . "\n");
    }
    $i++;
}

echo "GETTING COMPLETED rewardRedemptionFulfill\n";
$query = "SELECT `u`.`email`, '21' AS `store_id`, -200 AS `order_id`, - `rt`.`points` AS 'points_spent', `rt`.`transactionType`, 
`rt`.`transactionDate` AS `date_start`
FROM `rewardsTransaction` AS `rt`
LEFT JOIN `users` AS `u` ON `u`.`uid` = `rt`.`uid`
WHERE `rt`.`transactionType` = 'rewardRedemptionFulfill'";
$results = $writeConnection->query($query);
$i = 1;
foreach ($results as $result) {

    //SELECT customer
    $customerFound = NULL;
    $customerQuery = "SELECT `entity_id` FROM `customer_entity` WHERE `email` = '" . $result['email'] . "' AND `website_id` = 21";
    //echo $customerQuery . "\n";
    $customerResults = $writeConnection->query($customerQuery);
    foreach ($customerResults as $customerResult) {  
	fwrite($transactionLogHandle, "  ->CUSTOMER    : " . $result['email'] . "\n");

	//Add review detail
	$customerFound = 1;
	$rewardQuery = "INSERT INTO `rewardpoints_account` (`customer_id`, `store_id`, `order_id`, `points_current`, `points_spent`, `date_start`, `date_end`, `convertion_rate`, `rewardpoints_referral_id`, `quote_id`) VALUES (" . $customerResult['entity_id'] . ", '21', '" . $result['order_id'] . "', 0, " . $result['points_spent'] . ", '" . $result['date_start'] . "', NULL, NULL, NULL, NULL)";
	//echo $rewardQuery . "\n";
	$rewardResults = $writeConnection->query($rewardQuery);
	fwrite($transactionLogHandle, "    ->ADDED     : Reward COMPLETED rewardRedemptionFulfill : " . $i . " - Customer ID: " . $customerResult['entity_id'] . " -> Points: " . $result['points_spent'] . "\n");

	//If customerId then update points for order reward transaction
	if ($customerResult['entity_id']) {
	    //Refresh rewarpoints_flat _account table -- hard-coded 21
	    RewardPoints_Model_Observer::processRecordFlatRefresh($customerResult['entity_id'], 21);
	    fwrite($transactionLogHandle, "      ->ADJUSTMENT: FLAT TABLE REFRESHED : " . $customerResult['entity_id'] . "\n");
	}
	
	fwrite($transactionLogHandle, "  ->DONE        : " . $result['email'] . "\n");
    }
    if (!$customerFound) {
	fwrite($transactionLogHandle, "  ->NOT FOUND   : " . $result['email'] . "\n");
    }
    $i++;
}

fwrite($transactionLogHandle, "->END PROCESSING\n");
//Close transaction log
fclose($transactionLogHandle);

?>
