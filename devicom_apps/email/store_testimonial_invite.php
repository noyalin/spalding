<?php

require(dirname(dirname(__FILE__)) . "/lib/Devicom/config.php");

// Initialize magento environment for 'admin' store
require_once(dirname(dirname(dirname(__FILE__))) . "/app/Mage.php");
// Set urls to match frontend store
Mage::app('sneakerhead_en');
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

$transactionLogFile = dirname(__FILE__) . "/logs/store_testimonial_invite_log";
$transactionLogHandle = fopen($transactionLogFile, 'a+');
if (!is_resource($transactionLogHandle))
	die("Failed to open log file for writing\n");

$exceptionLogFile = dirname(__FILE__) . "/logs/exception_log";

// Set interval
$storeTestimonialTargetDaysStart = (defined("CONF_STORE_TESTIMONIAL_INVITE_TARGET_ORDER_INTERVAL_DAYS_START")) ? CONF_STORE_TESTIMONIAL_INVITE_TARGET_ORDER_INTERVAL_DAYS_START : 10;
$storeTestimonialTargetDaysEnd = (defined("CONF_STORE_TESTIMONIAL_INVITE_TARGET_ORDER_INTERVAL_DAYS_START")) ? CONF_STORE_TESTIMONIAL_INVITE_TARGET_ORDER_INTERVAL_DAYS_END : 30;

// Set to "0" to prevent resending EVER (per order number)
$storeTestimonialBoundaryDays = (defined("CONF_STORE_TESTIMONIAL_INVITE_RESEND_BOUNDARY_DAYS")) ? CONF_STORE_TESTIMONIAL_INVITE_RESEND_BOUNDARY_DAYS : 0;

// Set target store ids for this script
$targetStores = explode(",", CONF_STORE_TESTIMONIAL_INVITE_TARGET_STORES);
$storeQuery = array();
foreach ($targetStores as $targetStore) {
	$storeQuery[] = "sfo.store_id = $targetStore";
}

// Create db connection
$resource = Mage::getSingleton('core/resource');
$readConnection = $resource->getConnection('core_read');
$writeConnection = $resource->getConnection('core_write');

// Retrieve Template Ids
$query = "SELECT template_id, orig_template_code FROM core_email_template WHERE " .
		"UPPER(orig_template_code) = 'CUSTOMER_STORE_TESTIMONIAL_INVITE_TEMPLATE' OR " .
		"UPPER(orig_template_code) = 'GUEST_STORE_TESTIMONIAL_INVITE_TEMPLATE'";
try {
	$coreTemplateIds = $readConnection->fetchAll($query);
} catch (Exception $e) {
	logMessage("ERROR: Failed to retrieve templateIds for Product Review -> SEE EXCEPTION_LOG", $transactionLogHandle);
	fatalError($e->getMessage(), $exceptionLogFile, __FILE__, __LINE__);
}

if (!count($coreTemplateIds))
	fatalError("Email Templates not defined", $exceptionLogFile, __FILE__, __LINE__);

// Set definitions for template_id constants
foreach ($coreTemplateIds as $template) {
	define("{$template['orig_template_code']}", $template['template_id']);
}

// Get email_transaction_type
$query = "SELECT id FROM devicom_email_transaction_types WHERE " .
		"transaction_type = 'STORE_TESTIMONIAL_INVITE_CUSTOMER'";
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
// For transaction email
$storeUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);
$imagesUrl = CONF_IMAGES_BASE_URL;

/* * *************** */
/* * *************** */

logMessage("->BEGIN PROCESSING: " . date("Y-m-d H:i:s"), $transactionLogHandle);

// Find target orders
$query = "SELECT sfo.entity_id as order_id, " .
		"sfo.increment_id AS order_number, " .
		"sfo.store_id, " .
		"sfo.customer_id, " .
		"sfo.customer_email, " .
		"sfo.customer_firstname, " .
		"sfo.customer_lastname, " .
		"sfo.created_at as order_date, " .
		"CAST(track.created_at AS DATE) as shipped_date " .
		"FROM sales_flat_order sfo " .
		"INNER JOIN sales_flat_shipment_track track " .
		"ON (track.order_id = sfo.entity_id) WHERE (" .
		implode(" OR ", $storeQuery) . ") AND " .
		// There's null entries, can't do anything with the order if there's no email address
		"sfo.customer_email IS NOT NULL AND " .
		// Must have shipped at least $storeTestimonialTargetDays but no more than 30
		// Otherwise we'll be grabbing everything every time
		"CAST(track.created_at AS DATE) <= CAST((now() - INTERVAL $storeTestimonialTargetDaysStart DAY) AS DATE) AND " .
		"CAST(track.created_at AS DATE) > CAST((now() - INTERVAL $storeTestimonialTargetDaysEnd DAY) AS DATE) ORDER BY sfo.increment_id DESC";

if (DEBUG_MODE && CONF_DEBUG_ORDER_LIMIT) {
	$query .= " LIMIT " . CONF_DEBUG_ORDER_LIMIT;
}

try {
	$shippedOrders = $readConnection->fetchAll($query);
} catch (Exception $e) {
	logMessage("ERROR: Failed to retrieve target orders -> SEE EXCEPTION_LOG", $transactionLogHandle);
	fatalError($e->getMessage(), $exceptionLogFile, __FILE__, __LINE__);
}

// No orders
if (!count($shippedOrders)) {
	logMessage("\t->NO ORDERS FOUND. Exiting.", $transactionLogHandle);
	logMessage("->FINISH PROCESSING: " . date("Y-m-d H:i:s"), $transactionLogHandle);
	exit();
}

/* * *************** */
/* * *************** */
// We want customers to login before providing review, if available
// They get points for doing it if logged in?

$order_count = count($shippedOrders);

logMessage("->ORDERS FOUND: $order_count", $transactionLogHandle);

/* * ************************************************** */
// Initialize ConstantContact api
/* * ************************************************** */
require(dirname(dirname(__FILE__)) . "/lib/ConstantContact/class.cc.php");

// Set your Constant Contact account username and password below
$cc = new cc(CREATE_CONTACT_CC_USERNAME, CREATE_CONTACT_CC_PASSWORD);
/* * ************************************************** */
/* * ************************************************** */

foreach ($shippedOrders as $shippedOrder) {

	$orderNumber = $shippedOrder['order_number'];
	$orderId = $shippedOrder['order_id'];
	$shippedDate = date("m/d/Y", strtotime($shippedOrder['shipped_date']));
	$orderDate = date("m/d/Y", strtotime($shippedOrder['order_date']));

	// Get Store ID
	$storeId = (!$shippedOrder['store_id']) ? 21 : $shippedOrder['store_id'];
	$customerEmail = $shippedOrder['customer_email'];

	logMessage("\n\t->BEGIN HANDLING ORDER: #$orderNumber ($orderId)", $transactionLogHandle);

	// Retrieve constant contact info for this customer
	$contact = $cc->query_contacts($customerEmail); // Needed to retrieve contact ID
	// Customer details
	$contact = $cc->get_contact($contact['id']);


	// $contact['lists'] is a returned array of list_ids to which this contact is subscribed to (if any)
	// If the review list_id is not in the contact['lists'] array, skip 'em
	if (!is_array($contact['lists']) || !in_array(CREATE_CONTACT_LIST_REVIEWS, $contact['lists'])) {
		logMessage("\t\t->EMAIL NOT SUBSCRIBED TO REVIEWS LIST ($customerEmail). SKIPPING.", $transactionLogHandle);
		logMessage("\t->END HANDLING ORDER: #$orderNumber ($orderId)", $transactionLogHandle);
		continue;
	}

	/*	 * *************** */
	/*	 * *************** */
	// Don't send to actual customer when testing (debug_mode = true)
	if (DEBUG_MODE) {
		$customerEmail = CONF_DEBUG_EMAIL_OVERRIDE;
	}
	/*	 * *************** */
	/*	 * *************** */

	// Attempt to retrieve existing customer
	// Only one customer account can exist with a given email address
	$customer = Mage::getModel("customer/customer");
	$customer->setWebsiteId($storeId);
	$customer->loadByEmail($shippedOrder['customer_email']);

	$customerData = $customer->getData();
	$customerId = $customerData['entity_id'];

	/*	 * *************** */
	/*	 * *************** */

	if (!$customerId) {
		logMessage("\t\t->CUSTOMER NOT FOUND. USING SHIPPING INFO.", $transactionLogHandle);

		$customerFirstname = ucfirst($shippedOrder['customer_firstname']);
		$customerLastname = ucfirst($shippedOrder['customer_lastname']);

		// Correct blank name fields
		if (!strlen($customerFirstname))
			$customerFirstname = (defined("CONF_GUEST_DEFAULT_FIRSTNAME")) ? CONF_GUEST_DEFAULT_FIRSTNAME : "Sneakerhead";

		$name = "$customerFirstname";
		// Append lastname if available
		$name .= (strlen($customerLastname)) ? " $customerLastname" : "";
	} else {
		logMessage("\t\t->CUSTOMER FOUND", $transactionLogHandle);

		$customerFirstname = ucfirst($customerData['firstname']);
		$customerLastname = ucfirst($customerData['lastname']);
		$name = "$customerFirstname $customerLastname";
	}

	logMessage("\t\t->CHECKING FOR EMAIL TRANSACTION RECORD...", $transactionLogHandle);

	/*	 * *************** */
	/*	 * *************** */

	// If this customer has already been sent this email within configured boundary days, continue.
	if (!checkDevicomEmailTransactionRecord($readConnection, $shippedOrder['customer_email'], $orderNumber, $transactionTypeId, $storeTestimonialBoundaryDays, $invitationExists, $error)) {
		logMessage("ERROR: Failed to check devicom_email_transaction_record for customer '{$shippedOrder['customer_email']}' -> SEE EXCEPTION_LOG", $transactionLogHandle);
		fatalError($error, $exceptionLogFile, __FILE__, __LINE__);
	}

	if ($invitationExists) {
		logMessage("\t\t->STORE TESTIMONIAL INVITATION ALREADY SENT TO CUSTOMER FOR ORDER #$orderNumber ($orderId). SKIPPING.", $transactionLogHandle);
		logMessage("\t->END HANDLING ORDER: #$orderNumber (ID $orderId)", $transactionLogHandle);
		continue;
	}

	/*	 * *************** */
	/*	 * *************** */

	logMessage("\t\t->PREPARING STORE TESTIMONIAL INVITATION EMAIL FOR {$shippedOrder['customer_email']}'.", $transactionLogHandle);

	// Retrieve order items that shipped for this order
	if (!getOrderItemsShippedByOrderNumber($readConnection, $orderNumber, $storeId, $orderItems, $error)) {
		logMessage("ERROR: Failed to retrieve order items for order $orderNumber ($orderId) -> SEE EXCEPTION_LOG", $transactionLogHandle);
		fatalError($error, $exceptionLogFile, __FILE__, __LINE__);
	}

	// Shouldn't happen
	if (!count($orderItems)) {
		logMessage("\t\t->NO SHIPPED ITEMS FOUND FOR ORDER #$orderNumber ($orderId). SKIPPING.", $transactionLogHandle);
		logMessage("\t->END HANDLING ORDER: #$orderNumber ($orderId)", $transactionLogHandle);
		continue;
	}

	$isBackend = true;
	require(dirname(dirname(dirname(__FILE__))) . "/app/design/frontend/default/sneakerhead/template/email/footer_review.phtml");

	$contentBlock = "<a href=\"{$reviewUrls[0]}\" target=\"_blank\" style=\"color:#A80000; font-weight:bold;\">Rate Your Sneakerhead Shopping Experience.</a>";

	// Set variables that can be used in email template
	// Using format {{var var_name}}
	$vars = array();

	// Add all column name/values to $vars array
	foreach ($shippedOrder as $key => $value) {
		$vars[$key] = $value;
	}

	// Overwrite $vars with formatted values
	$vars['name'] = $name;
	$vars['customer_firstname'] = $customerFirstname;
	$vars['customer_lastname'] = $customerLastname;
	$vars['email'] = $shippedOrder['customer_email'];
	$vars['order_number'] = $orderNumber;
	$vars['order_date'] = $orderDate;
	$vars['shipped_date'] = $shippedDate;
	$vars['order_review_block'] = $contentBlock;
	$vars['footer_review'] = $footer_review;

	/*	 * *************** */
	/*	 * *************** */

	// FIXME: Order already updated. Need a separate script to resend any failed messages.
	// Need to store the full message content for sending.
	if (!sendTransactionalEmail(
					$translate, $mageTemplate, (($customerId) ? CUSTOMER_STORE_TESTIMONIAL_INVITE_TEMPLATE : GUEST_STORE_TESTIMONIAL_INVITE_TEMPLATE), $sender, $customerEmail, $name, $vars, $storeId, $error)) {
		logMessage("\t\t->FAILED TO SEND INVITATION TO '$name <{$shippedOrder['customer_email']}>' WILL TRY AGAIN LATER.", $transactionLogHandle);
		$sentSuccessfully = false;
	} else {
		logMessage("\t\t->SUCCESS: INVITATION SENT TO '$name <{$shippedOrder['customer_email']}>'.", $transactionLogHandle);
		$sentSuccessfully = true;
	}

	/*	 * *************** */
	/*	 * *************** */

	logMessage("\t\t->CREATING DEVICOM EMAIL TRANSACTION RECORD.", $transactionLogHandle);

	if (!createDevicomEmailTransactionRecord($writeConnection, $transactionTypeId, $shippedOrder['customer_email'], $customerFirstname, $customerLastname, $orderNumber, $shippedOrder['order_date'], $sentSuccessfully, $error)) {
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

function getOrderItemsShippedByOrderNumber($readConnection, $orderNumber, $storeId, &$orderItems, &$error) {
	$orderItems = array();

	// Collect all items for this order that have shipped AND have not been returned
	$query = "SELECT * FROM devicom_order_item WHERE " .
			"increment_id = $orderNumber AND " .
			"quantity_shipped > (quantity_returned + quantity_backordered)";
	try {
		$orderItems = $readConnection->fetchAll($query);
	} catch (Exception $e) {
		$error = $e->getMessage();
		return false;
	}

	return true;
}

/* * ************************************************************************** */
/* * ************************************************************************** */
?>
