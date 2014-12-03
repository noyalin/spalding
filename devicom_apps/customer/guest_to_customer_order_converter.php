<?php
$longopts  = array(
    "ordernumber::",// Optional value
);
$options = getopt("", $longopts);
$orderNumber = $options['ordernumber'];

require(dirname(dirname(__FILE__)) . "/lib/Devicom/config.php");

// Initialize magento environment for 'admin' store
require_once(dirname(dirname(dirname(__FILE__))) . "/app/Mage.php");
// Set urls to match frontend store
Mage::app('sneakerhead_cn');
//Load adminhtml area of config.xml to initialize adminhtml observers
Mage::app()->loadArea('adminhtml');

$transactionLogFile = dirname(__FILE__) . "/logs/guest_to_customer_order_converter_log";
$transactionLogHandle = fopen($transactionLogFile, 'a+');

$exceptionLogFile = dirname(__FILE__) . "/logs/exception_log";

// Set interval
if (!defined("CONF_GUEST_INVITE_TARGET_ORDER_INTERVAL_DAYS_END")) {
	logMessage("ERROR: Target Interval Range not defined. Exiting.", $transactionLogHandle);
	fatalError("Target Interval Range not defined", $exceptionLogFile, __FILE__, __LINE__);
}

$guestInviteTargetDaysEnd = CONF_GUEST_INVITE_TARGET_ORDER_INTERVAL_DAYS_END;

// Set target store ids for this script
$targetStores = explode(",", CONF_GUEST_INVITE_TARGET_STORES);
$storeQuery = array();
foreach ($targetStores as $targetStore) {
	$storeQuery[] = "store_id = $targetStore";
}

// Create db connection
$resource = Mage::getSingleton('core/resource');
$readConnection = $resource->getConnection('core_read');
$writeConnection = $resource->getConnection('core_write');

/******************/
/******************/

logMessage("->BEGIN PROCESSING: " . date("Y-m-d H:i:s"), $transactionLogHandle);

// Find customer order (guest) info
$query = "SELECT entity_id as order_id, " .
	"increment_id AS order_number, " .
	"store_id, " .
	"status, " .
	"customer_is_guest, " .
	"customer_email, " .
	"customer_firstname, " .
	"customer_lastname, " .
	"created_at, " .
	"updated_at, " .
	"(SELECT created_at FROM sales_flat_shipment_track WHERE entity_id = order_id) as shipped_date " .
	"FROM sales_flat_order WHERE " .
	"customer_id IS NULL AND (" .
	implode (" OR ", $storeQuery) . ") AND " .
	"customer_is_guest = 1 AND " .
	"status != 'canceled' AND " .
	// There's null entries, can't do anything with the order if there's no email address
	"customer_email IS NOT NULL AND ";

	$query .= (DEBUG_MODE && strlen($orderNumber)) ? "increment_id = $orderNumber AND " : "";
	// Orders place prior to this target will not be processed, and therefore will not be included
	// for reward eligibility if the customer creates an account after-the-fact

	// Existing customers will automatically have guest orders (and reward points) assigned to them

	// New guest orders will be picked up by this process for beginning on $guestInviteTargetDaysStart days.
	// Customer has until $guestInviteTargetDaysEnd days to create an account.
	// Orders older than that will be ignored.

	$query .= "CAST(created_at AS DATE) <= CAST(now() AS DATE) AND " .
		"CAST(created_at AS DATE) > CAST((now() - INTERVAL $guestInviteTargetDaysEnd DAY) AS DATE) ORDER BY order_number DESC";

if (DEBUG_MODE && CONF_DEBUG_ORDER_LIMIT) {
	$query .= " LIMIT " . CONF_DEBUG_ORDER_LIMIT;
}

try {
	$guestOrders = $readConnection->fetchAll($query);
} catch (Exception $e) {
	logMessage("ERROR: Failed to retrieve target orders -> SEE EXCEPTION_LOG", $transactionLogHandle);
	fatalError($e->getMessage(), $exceptionLogFile, __FILE__, __LINE__);
}

// No orders
if (!count($guestOrders)) {
	logMessage("\t->NO ORDERS FOUND. Exiting.", $transactionLogHandle);
	logMessage("->FINISH PROCESSING: " . date("Y-m-d H:i:s"), $transactionLogHandle);
	exit();
}

/******************/
/******************/

$order_count = count($guestOrders);

logMessage("->ORDERS FOUND: $order_count", $transactionLogHandle);

$converted_to_customer = 0;

foreach ($guestOrders as $guestOrder) {

	$orderNumber = $guestOrder['order_number'];
	$orderId = $guestOrder['order_id'];
	$orderDate = date("m/d/Y", strtotime($guestOrder['created_at']));
	$order_date = date("Y-m-d", strtotime($guestOrder['created_at']));

	// Get Store ID
	$storeId = $guestOrder['store_id'];
	//$customerEmail = $guestOrder['customer_email'];

	logMessage("\n\t->BEGIN HANDLING ORDER: #$orderNumber ($orderId)", $transactionLogHandle);

	/******************/
	/******************/

	// Attempt to retrieve existing customer
	// Only one customer account can exist with a given email address, but check
	// all target stores
	$customer = Mage::getModel("customer/customer");
	foreach ($targetStores as $targetStore) {
		$customer->setWebsiteId($targetStore);
		$customer->loadByEmail($guestOrder['customer_email']);
		$customerData = $customer->getData();
		if ($customerId = $customerData['entity_id']) {
			break;
		}
	}


	/******************/
	/******************/

	// Retrieve the qualifying points value for this order
	if (!getRewardpointsByOrderNumber($readConnection, $orderNumber, $pointsCurrent, $error)) {
		logMessage("ERROR: Failed to retrieve order items for order number $orderNumber. -> SEE EXCEPTION_LOG", $transactionLogHandle);
		fatalError($error, $exceptionLogFile, __FILE__, __LINE__);
	}

	logMessage("\t->ELIGIBLE REWARD POINTS: $pointsCurrent", $transactionLogHandle);

	/*****************************************************************************/
	/* PERFORM ADDITIONAL REWARD VALIDATION HANDLING IF CUSTOMER EXISTS          */
	/*****************************************************************************/

	// If customer exists, perform reward validation handling
	if ($customerId) {

		$customerFirstname = ucfirst($customerData['firstname']);
		$customerLastname = ucfirst($customerData['lastname']);

		// Use transaction, so that we don't do any updates if any part fails
		try {
			$readConnection->query("START TRANSACTION");
		} catch (Exception $e) {
			logMessage("ERROR: Failed to begin transaction. -> SEE EXCEPTION_LOG", $transactionLogHandle);
			fatalError($e->getMessage(), $exceptionLogFile, __FILE__, __LINE__);
		}

		/****************************** BEGIN TRANSACTION *****************************/

		logMessage("\t->CUSTOMER EXISTS ($customerId)", $transactionLogHandle);
		logMessage("\t\t->UPDATING SALES FLAT ORDER TABLE", $transactionLogHandle);

		// Update the sales_order_flat tables with the the customer info
		$query = "UPDATE sales_flat_order SET " .
			"customer_is_guest = 0, " .
			"customer_id = $customerId, " .
			"customer_firstname = '" . mysql_escape_string($customerData['firstname']) . "', " .
			"customer_lastname = '" . mysql_escape_string($customerData['lastname']) . "', " .
			"customer_email = '" . mysql_escape_string($guestOrder['customer_email']) . "' " .
			"WHERE increment_id = $orderNumber";
		try {
			$writeConnection->query($query);
		} catch (Exception $e) {
			logMessage("ERROR: Failed to update sales_flat_order for order #$orderNumber -> SEE EXCEPTION_LOG", $transactionLogHandle);
			fatalError($e->getMessage(), $exceptionLogFile, __FILE__, __LINE__);
		}

		logMessage("\t\t->UPDATING SALES FLAT ORDER GRID TABLE", $transactionLogHandle);

		$query = "UPDATE sales_flat_order_grid SET " .
			"customer_id = $customerId " .
			"WHERE increment_id = $orderNumber";
		try {
			$writeConnection->query($query);
		} catch (Exception $e) {
			logMessage("ERROR: Failed to update sales_flat_order_grid for order #$orderNumber -> SEE EXCEPTION_LOG", $transactionLogHandle);
			fatalError($e->getMessage(), $exceptionLogFile, __FILE__, __LINE__);
		}

		logMessage("\t\t->UPDATING DEVICOM ORDER ITEM TABLE", $transactionLogHandle);

		// Update the sales_order_flat tables with the the customer info
		$query = "UPDATE devicom_order_item SET " .
			"customer_id = $customerId " .
			"WHERE increment_id = $orderNumber";
		try {
			$writeConnection->query($query);
		} catch (Exception $e) {
			logMessage("ERROR: Failed to update sales_flat_order for order #$orderNumber -> SEE EXCEPTION_LOG", $transactionLogHandle);
			fatalError($e->getMessage(), $exceptionLogFile, __FILE__, __LINE__);
		}
		// Check if rewardpoints_account entry already exists
		$query = "SELECT COUNT(*) FROM rewardpoints_account WHERE " .
			"order_id = $orderNumber";
		try {
			$rpaEntryExists = $readConnection->fetchOne($query);
		} catch (Exception $e) {
			logMessage("ERROR: Failed to check rewardpoints_account entry for order #$orderNumber -> SEE EXCEPTION_LOG", $transactionLogHandle);
			fatalError($e->getMessage(), $exceptionLogFile, __FILE__, __LINE__);
		}

		// Insert reward_points_account entry (EVEN IF 0 value)
		if (!$rpaEntryExists) {
			logMessage("\t\t->CREATING REWARD POINTS ACCOUNT ENTRY", $transactionLogHandle);
			if (!createRewardpointsAccount($writeConnection, $customerId, $storeId, $orderNumber, $pointsCurrent, $guestOrder, $e)) {
				logMessage("ERROR: Failed to create rewardpoints_account entry for order $orderNumber. -> SEE EXCEPTION_LOG", $transactionLogHandle);
				fatalError($error, $exceptionLogFile, __FILE__, __LINE__);
			}
		} else {
			logMessage("\t\t->REWARD POINTS ACCOUNT ENTRY EXISTS", $transactionLogHandle);
		}

		$createDevicomAction = true;

		// ONLY IF WE DECIDE TO TARGET GUEST ORDERS OLDER THAN 30 DAYS, OTHERWISE THIS ENTIRE BLOCK WILL BE SKIPPED, OR CAN BE REMOVED ENTIRELY
		// If target is > 30 days, then we need to make sure the order's shipped date is less than 30 days old or non-existent.
		// If older, we don't create the validation record
		if ($guestInviteTargetDaysEnd > 30) {
			logMessage("\t\t\t->TARGET DAYS > 30, VERIFYING SHIPMENT WITHIN BOUNDARY", $transactionLogHandle);

			// If the order actually shipped
			if (strlen($guestOrder['shipped_date']) &&
				(time() - strtotime($guestOrder['shipped_date'])) / 86400 > 60) {
				$createDevicomAction = false;
				logMessage("\t\t\t->SHIP DATE > 30 DAYS. NO DEVICOM REWARD VALIDATION NEEDED.", $transactionLogHandle);
			}
		}

		if ($createDevicomAction) {

			if ($guestOrder['status'] != 'shipped' &&
				$guestOrder['status'] != 'backordered' &&
				$guestOrder['status'] != 'cancel' &&
				$guestOrder['status'] != 'partially_shipped' &&
				$guestOrder['status'] != 'returned') {
				logMessage("\t\t->ORDER STILL IN PROCESS. NO DEVICOM REWARD VALIDATION NEEDED.", $transactionLogHandle);
			} else {
				// Create devicom_rewards_validation entry (if it doesn't exist)
				$query = "SELECT COUNT(*) FROM devicom_reward_validation WHERE increment_id = $orderNumber";
				try {
					$validationEntryExists = $readConnection->fetchOne($query);
				} catch (Exception $e) {
					logMessage("ERROR: Failed to check devicom_reward_validation entry for order number $orderNumber -> SEE EXCEPTION_LOG", $transactionLogHandle);
					fatalError($e->getMessage(), $exceptionLogFile, __FILE__, __LINE__);
				}

				if (!$validationEntryExists) {
					logMessage("\t\t->CREATING DEVICOM REWARD VALIDATION ENTRY", $transactionLogHandle);

					// If shipped, partially_shipped, backordered, returned, or cancel order_status,
					// then we still create the entry based on the updated_at date
					$createdAt = (strlen($guestOrder['shipped_date'])) ? $guestOrder['shipped_date'] : $guestOrder['updated_at'];

					$query = "INSERT INTO devicom_reward_validation (increment_id, status, created_at) " .
						"VALUES ($orderNumber, 'pending', '$createdAt')";
					try {
						$writeConnection->query($query);
					} catch (Exception $e) {
						logMessage("ERROR: Failed to create devicom_reward_validation entry for order number $orderNumber. -> SEE EXCEPTION_LOG", $transactionLogHandle);
						fatalError($e->getMessage(), $exceptionLogFile, __FILE__, __LINE__);
					}
				} else {
					logMessage("\t\t->DEVICOM REWARD VALIDATION ENTRY EXISTS", $transactionLogHandle);
				}
			}
		}

		/****************************** END TRANSACTION *****************************/
		try {
			$readConnection->query("COMMIT");
		} catch (Exception $e) {
			logMessage("ERROR: Failed to commit transaction when updating order number $orderNumber. -> SEE EXCEPTION_LOG", $transactionLogHandle);
			fatalError($e->getMessage(), $exceptionLogFile, __FILE__, __LINE__);
		}

		// Create customer rewardpoints_flat_account (if it doesn't exist)
		$query = "SELECT COUNT(*) FROM rewardpoints_flat_account WHERE user_id = $customerId";
		try {
			$rewardPointsAccountExists = $readConnection->fetchOne($query);
		} catch (Exception $e) {
			logMessage("ERROR: Failed to check rewardpoints_flat_account entry for user_id $customerId -> SEE EXCEPTION_LOG", $transactionLogHandle);
			fatalError($e->getMessage(), $exceptionLogFile, __FILE__, __LINE__);
		}

		if (!$rewardPointsAccountExists) {
			logMessage("\t\t->CREATING REWARD POINTS FLAT ACCOUNT", $transactionLogHandle);
			$query = "INSERT INTO rewardpoints_flat_account (user_id, store_id, last_check) VALUES ($customerId, $storeId, '" . date("Y-m-d") . "')";
			try {
				$writeConnection->query($query);
			} catch (Exception $e) {
				logMessage("ERROR: Failed to create rewardpoints_flat_account entry for user_id $customerId -> SEE EXCEPTION_LOG", $transactionLogHandle);
				fatalError($e->getMessage(), $exceptionLogFile, __FILE__, __LINE__);
			}
		} else {
			logMessage("\t\t->REWARD POINTS FLAT ACCOUNT EXISTS", $transactionLogHandle);
		}

		logMessage("\t\t->REFRESHING REWARD POINTS ACCOUNT FLAT TABLE", $transactionLogHandle);

		// NOTE: This has to wait until we commit the transaction
		// Refresh rewardpoints_flat_account table
		RewardPoints_Model_Observer::processRecordFlatRefresh($customerId, $storeId);

		logMessage("\t->END HANDLING ORDER: #$orderNumber (ID $orderId)", $transactionLogHandle);

		$converted_to_customer++;
	} else {
		logMessage("\t->NO CUSTOMER. NOTHING TO DO.", $transactionLogHandle);
	}
}

logMessage("->END PROCESSING: " . date("Y-m-d H:i:s") . "\n\n", $transactionLogHandle);

// Close transaction log
fclose($transactionLogHandle);

/*****************************************************************************/
/*****************************************************************************/

function createRewardpointsAccount($writeConnection, $customerId, $storeId, $orderNumber, $pointsCurrent, $guestOrder, &$error)
{
	$pointsCurrent = ($guestOrder['status'] == "backordered") ? 0 : $pointsCurrent;
	$createdAt = $guestOrder['created_at'];

	$fields = array(
		"customer_id",
		"store_id",
		"order_id",
		"points_current",
		"date_start"
	);

	$values = array(
		"$customerId",
		"'$storeId'",
		"$orderNumber",
		"$pointsCurrent",
		"'$createdAt'"
	);

	$query = "INSERT INTO rewardpoints_account (" .
		implode(",", $fields) . ") VALUES (" .	implode(",", $values) . ")";

        try {
			$writeConnection->query($query);
			return true;
        } catch (Exception $e) {
			$error = $e->getMessage();
			return false;
        }
}

/*****************************************************************************/
/*****************************************************************************/

?>