<?php

require(dirname(dirname(__FILE__)) . "/lib/Devicom/config.php");

// Initialize magento environment for 'admin' store
require_once(dirname(dirname(dirname(__FILE__))) . "/app/Mage.php");
// Set urls to match frontend store
Mage::app('sneakerhead_cn');
//Load adminhtml area of config.xml to initialize adminhtml observers
Mage::app()->loadArea('adminhtml');

$sender = array(
	'name' => Mage::getStoreConfig('trans_email/ident_support/name'),
	'email' => Mage::getStoreConfig('trans_email/ident_support/email')
);

// Get core email template
$mageTemplate = Mage::getModel('core/email_template');
// Sets translation object for use with send_email_general()
$translate = Mage::getSingleton('core/translate');

$transactionLogFile = dirname(__FILE__) . "/logs/guest_create_account_invite_log";
$transactionLogHandle = fopen($transactionLogFile, 'a+');

$exceptionLogFile = dirname(__FILE__) . "/logs/exception_log";

// Set interval
if (!defined("CONF_GUEST_INVITE_TARGET_ORDER_INTERVAL_DAYS_START")
		|| !defined("CONF_GUEST_INVITE_TARGET_ORDER_INTERVAL_DAYS_END")) {
	logMessage("ERROR: Target Interval Range not defined. Exiting.", $transactionLogHandle);
	fatalError("Target Interval Range not defined", $exceptionLogFile, __FILE__, __LINE__);
}

$guestInviteTargetDaysStart = CONF_GUEST_INVITE_TARGET_ORDER_INTERVAL_DAYS_START;
$guestInviteTargetDaysEnd = CONF_GUEST_INVITE_TARGET_ORDER_INTERVAL_DAYS_END;

// Don't allow resending of this message within $guestInviteBoundaryDays
$guestInviteBoundaryDays = (defined("CONF_GUEST_INVITE_RESEND_BOUNDARY_DAYS")) ? CONF_GUEST_INVITE_RESEND_BOUNDARY_DAYS : 0;

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

// TemplateId
$query = "SELECT template_id FROM core_email_template WHERE " .
		"UPPER(orig_template_code) = 'GUEST_INVITE_CREATE_ACCOUNT_TEMPLATE'";
try {
	$templateId = $readConnection->fetchOne($query);
} catch (Exception $e) {
	logMessage("ERROR: Failed to retrieve templateId for Guest Invite Create Account -> SEE EXCEPTION_LOG", $transactionLogHandle);
	fatalError($e->getMessage(), $exceptionLogFile, __FILE__, __LINE__);
}

if (!$templateId)
	fatalError("Email Template not defined", $exceptionLogFile, __FILE__, __LINE__);

// Get email_transaction_type
$query = "SELECT id FROM devicom_email_transaction_types WHERE " .
		"transaction_type = 'GUEST_INVITE_CREATE_ACCOUNT'";
try {
	$transactionTypeId = $readConnection->fetchOne($query);
} catch (Exception $e) {
	logMessage("ERROR: Failed to retrieve email transaction types for Product Review -> SEE EXCEPTION_LOG", $transactionLogHandle);
	fatalError($e->getMessage(), $exceptionLogFile, __FILE__, __LINE__);
}

if (!$transactionTypeId)
	fatalError("Email transaction types not defined", $exceptionLogFile, __FILE__, __LINE__);

/* * *************** */
/* * *************** */

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
		"created_at " .
		"FROM sales_flat_order WHERE " .
		"customer_id IS NULL AND (" .
		implode(" OR ", $storeQuery) . ") AND " .
		"customer_is_guest = 1 AND " .
		"status != 'canceled' AND " .
		// There's null entries, can't do anything with the order if there's no email address
		"customer_email IS NOT NULL AND " .
		// Target guest orders placed between configured start and end dates.
		// Separate cron will handle converting guest_orders to customer orders as applicable.
		// Only sending the email and creating a record

		"CAST(created_at AS DATE) <= CAST((now() - INTERVAL $guestInviteTargetDaysStart DAY) AS DATE) AND " .
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

/* * *************** */
/* * *************** */

$order_count = count($guestOrders);

logMessage("->ORDERS FOUND: $order_count", $transactionLogHandle);

foreach ($guestOrders as $guestOrder) {

	$orderNumber = $guestOrder['order_number'];
	$orderId = $guestOrder['order_id'];
	$orderDate = date("m/d/Y", strtotime($guestOrder['created_at']));
	$order_date = date("Y-m-d", strtotime($guestOrder['created_at']));

	// Get Store ID
	$storeId = $guestOrder['store_id'];
	$customerEmail = $guestOrder['customer_email'];

	logMessage("\n\t->BEGIN HANDLING ORDER: #$orderNumber ($orderId)", $transactionLogHandle);

	/*	 * *************** */
	/*	 * *************** */
	// Don't send to actual customer when testing (debug_mode = true)
	if (DEBUG_MODE) {
		$customerEmail = CONF_DEBUG_EMAIL_OVERRIDE;
	}
	/*	 * *************** */
	/*	 * *************** */

	// Retrieve the qualifying points value for this order
	if (!getRewardpointsByOrderNumber($readConnection, $orderNumber, $pointsCurrent, $error)) {
		logMessage("ERROR: Failed to retrieve order items for order number $orderNumber. -> SEE EXCEPTION_LOG", $transactionLogHandle);
		fatalError($error, $exceptionLogFile, __FILE__, __LINE__);
	}

	logMessage("\t->ELIGIBLE REWARD POINTS: $pointsCurrent", $transactionLogHandle);

	$customerFirstname = ucfirst($guestOrder['customer_firstname']);
	$customerLastname = ucfirst($guestOrder['customer_lastname']);

	// Correct blank name fields
	if (!strlen($customerFirstname))
		$customerFirstname = (defined("CONF_GUEST_DEFAULT_FIRSTNAME")) ? CONF_GUEST_DEFAULT_FIRSTNAME : "Sneakerhead";

	$name = "$customerFirstname";
	// Append lastname if available
	$name .= (strlen($customerLastname)) ? " $customerLastname" : "";

	// If not reward eligible (and that's required), nothing to send, skip the order
	if (!$pointsCurrent) {
		$not_eligible++;
		logMessage("\t\t->ORDER IS NOT ELIGIBLE FOR REWARD POINTS. SKIPPING.", $transactionLogHandle);
		logMessage("\t->END HANDLING ORDER: #$orderNumber (ID $orderId)", $transactionLogHandle);
		continue;
	}

	/*	 * *************** */
	/*	 * *************** */

	logMessage("\t\t->CHECKING FOR EMAIL TRANSACTION RECORD...", $transactionLogHandle);

	/*	 * *************** */
	/*	 * *************** */

	// If this customer has already been sent this email within configured boundary days, continue.
	// $orderNumber = NULL because we're only checking the email address for this emailTransactionType
	if (!checkDevicomEmailTransactionRecord($readConnection, $guestOrder['customer_email'], NULL, $transactionTypeId, $guestInviteBoundaryDays, $invitationExists, $error)) {
		logMessage("ERROR: Failed to check devicom_email_transaction_record for customer '{$guestOrder['customer_email']}' -> SEE EXCEPTION_LOG", $transactionLogHandle);
		fatalError($error, $exceptionLogFile, __FILE__, __LINE__);
	}

	if ($invitationExists) {
		$duplicate_addresses++;
		logMessage("\t\t->INVITATION ALREADY SENT TO {$guestOrder['customer_email']}' WITHIN LAST $guestInviteBoundaryDays DAYS. SKIPPING.", $transactionLogHandle);
		logMessage("\t->END HANDLING ORDER: #$orderNumber (ID $orderId)", $transactionLogHandle);
		continue;
	}

	/*	 * *************** */
	/*	 * *************** */

	logMessage("\t\t->PREPARING INVITATION EMAIL FOR {$guestOrder['customer_email']}'.", $transactionLogHandle);

	// Set variables that can be used in email template
	// Using format {{var var_name}}
	$vars = array();

	// Add all column name/values to $vars array
	foreach ($guestOrder as $key => $value) {
		$vars[$key] = $value;
	}

	// Need to calculate days remaining between order date and end date
	$query = "SELECT DATEDIFF(CAST(('$order_date') AS DATE), CAST((now() - INTERVAL 30 DAY) AS DATE))";
	$daysRemaining = $readConnection->fetchOne($query);

	// Overwrite $vars with formatted values
	$vars['name'] = $name;
	$vars['customer_firstname'] = $customerFirstname;
	$vars['customer_lastname'] = $customerLastname;
	$vars['email'] = $guestOrder['customer_email'];
	$vars['order_number'] = $orderNumber;
	$vars['order_date'] = $orderDate;
	$vars['points_current'] = $pointsCurrent;
	$vars['days_remaining'] = ($daysRemaining > 1) ? "$daysRemaining days" : "$daysRemaining day";

	$isBackend = true;
	require(dirname(dirname(dirname(__FILE__))) . "/app/design/frontend/default/sneakerhead/template/email/footer_review.phtml");
	$vars['footer_review'] = $footer_review;

	/*	 * *************** */
	/*	 * *************** */
	$guest_invites++;

	if (!sendTransactionalEmail(
					$translate, $mageTemplate, $templateId, $sender,
					// Override only where we send the message
					$customerEmail, $name, $vars, $storeId, $error)) {
		logMessage("\t\t->FAILED TO SEND INVITATION TO '$name <{$guestOrder['customer_email']}>' WILL TRY AGAIN LATER.", $transactionLogHandle);
		$sentSuccessfully = false;
	} else {
		logMessage("\t\t->SUCCESS: INVITATION SENT TO '$name <{$guestOrder['customer_email']}>'.", $transactionLogHandle);
		$sentSuccessfully = true;
	}

	/*	 * *************** */
	/*	 * *************** */

	logMessage("\t\t->CREATING DEVICOM EMAIL TRANSACTION RECORD.", $transactionLogHandle);

	if (!createDevicomEmailTransactionRecord($writeConnection, $transactionTypeId, $guestOrder['customer_email'], $customerFirstname, $customerLastname, $orderNumber, $guestOrder['created_at'], $sentSuccessfully, $error)) {
		logMessage("ERROR: Failed to record email transaction for order_number $orderNumber -> SEE EXCEPTION_LOG", $transactionLogHandle);
		fatalError($error, $exceptionLogFile, __FILE__, __LINE__);
	}

	logMessage("\t->END HANDLING ORDER: #$orderNumber ($orderId)", $transactionLogHandle);
}

logMessage("->END PROCESSING: " . date("Y-m-d H:i:s") . "\n\n", $transactionLogHandle);

// Close transaction log
fclose($transactionLogHandle);

/* * ************************************************************************** */
/* * ************************************************************************** */
?>