<?php

/*****************************************************************************/
/*****************************************************************************/

function logMessage($message, $logHandle)
{
	if (DEBUG_MODE)
		print "$message\n";

	fwrite($logHandle, "$message\n");
}

/*****************************************************************************/
/*****************************************************************************/

function fatalError($message, $exceptionLogFile, $filename, $line)
{
	$exceptionLogHandle = fopen($exceptionLogFile, 'a+');
	$message = "TIME: " . date("Y-m-d H:i:s") . " -> FILE: $filename -> LINE: $line -> $message";
	logMessage($message, $exceptionLogHandle);

	fclose($exceptionLogHandle);
	exit();
}

/*****************************************************************************/
/*****************************************************************************/

function sendTransactionalEmail($translate, $mageTemplate, $templateId, $sender, $email, $name, $vars, $storeId, &$error)
{
	// Create Transactional Email
	$mageTemplate->sendTransactional($templateId, $sender, $email, $name, $vars, $storeId);

	// Send Email
        try {
		$translate->setTranslateInline(true);
		return true;
        } catch (Exception $e) {
		$error = $e->getMessage();
		return false;
        }
}

/*****************************************************************************/
/*****************************************************************************/

function createDevicomEmailTransactionRecord($writeConnection, $emailTransactionTypeId, $customerEmail, $customerFirstname, $customerLastname, $orderNumber, $orderDate, $sentSuccessfully = true, &$error)
{
	$fields = array(
		"email_transaction_type_id",
		"customer_email",
		"customer_firstname",
		"customer_lastname",
		"order_number",
		"order_date",
		"sent_successfully"
	);

	$values = array(
		"$emailTransactionTypeId",
		"'" . mysql_escape_string($customerEmail) . "'",
		"'" . mysql_escape_string($customerFirstname) . "'",
		"'" . mysql_escape_string($customerLastname) . "'",
		"$orderNumber",
		"'$orderDate'",
		"$sentSuccessfully"
	);

	$query = "INSERT INTO devicom_email_transaction_record (" .
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

function checkDevicomEmailTransactionRecord($readConnection, $customerEmail, $orderNumber = NULL, $emailTransactionTypeId = 1, $boundaryDays = 0, &$emailSent, &$error)
{
	$query = "SELECT COUNT(*) FROM devicom_email_transaction_record WHERE ";
	$query .= ($orderNumber) ? "order_number = $orderNumber" : "customer_email = '" . mysql_escape_string($customerEmail) . "'";
	$query .= " AND email_transaction_type_id = $emailTransactionTypeId " .
		"AND sent_successfully = true";

	// If set to 0 explicitly, then the boundary is "ever"; is there a record or not, regardless of date, for the given order
	$query .= ($boundaryDays) ? " AND CAST(created AS DATE) >= CAST((now() - INTERVAL $boundaryDays DAY) AS DATE)" : "";

        try {
		$emailSent = $readConnection->fetchOne($query);
		return true;
        } catch (Exception $e) {
		$error = $e->getMessage();
		return false;
        }
}

/*****************************************************************************/
/*****************************************************************************/

function getRewardpointsByOrderNumber($readConnection, $orderNumber, &$pointsCurrent, &$error)
{
	$pointsCurrent = 0;

	// Collect all items for this order that are reward eligible
	$query = "SELECT * FROM devicom_order_item WHERE " .
		"increment_id = $orderNumber AND " .
		"quantity_ordered > 0 AND " .
		"reward_eligible = 1";
        try {
			$rewardEligibleItems = $readConnection->fetchAll($query);
        } catch (Exception $e) {
			$error = $e->getMessage();
			return false;
        }

	if (!count($rewardEligibleItems))
		return true;

	foreach ($rewardEligibleItems as $rewardEligibleItem) {
		$finalCount = $rewardEligibleItem['quantity_shipped'] - $rewardEligibleItem['quantity_returned'];
		if ($finalCount && strtolower($rewardEligibleItem['status']) == 'shipped' ||
				$finalCount && strtolower($rewardEligibleItem['status']) == 'returned' ||
				strtolower($rewardEligibleItem['status']) == 'in processing' ||
				strtolower($rewardEligibleItem['status']) == 'on hold') {
			$pointsCurrent += $finalCount * ceil($rewardEligibleItem['price']);
		}
	}

	return true;
}


/*****************************************************************************/
/*****************************************************************************/

function sendNotification($this_subject, $this_message, $cc = false, $subject_override = false) {

    $to = CONF_SYSTEM_NOTIFICATION_TO_ADDRESS;
	$to .= ($cc) ? "," . CONF_SYSTEM_NOTIFICATION_CC_ADDRESS : "";
    $subject = ($subject_override) ? $this_subject : "System Notification - $this_subject";
    $message = "This is a system notification.\r\n\r\n";
    $message .= "$this_message";
    $message .= "\r\n\r\n";

    $headers = 'From: ' . CONF_SYSTEM_NOTIFICATION_FROM_ADDRESS . "\r\n" .
	'Reply-To: ' . CONF_SYSTEM_NOTIFICATION_FROM_ADDRESS . "\r\n" .
	'X-Mailer: PHP/' . phpversion();

	if (CONF_SYSTEM_NOTIFICATION_ENABLED)
	    mail($to, $subject, $message, $headers);
}

/*****************************************************************************/
/*****************************************************************************/

function realTime($time = null, $isAssociative = false) {

	$offsetInHours = +8;
	$offsetInSeconds = $offsetInHours * 60 * 60;

	if (is_null($time)) {
		$time = time();
	}

	$pstTime = $time + $offsetInSeconds;

	$explodedTime = explode(',', gmdate('s,i,H,d,m,Y,w,z,I', $pstTime));

	if (!$isAssociative) {
		return $explodedTime;
	}
	return array_combine(array('tm_sec', 'tm_min', 'tm_hour', 'tm_mday', 'tm_mon', 'tm_year', 'tm_wday', 'tm_yday', 'tm_isdst'), $explodedTime);
}

/*****************************************************************************/
/*****************************************************************************/

?>
