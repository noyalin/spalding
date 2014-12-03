<?php

require(dirname(__FILE__) . '/lib/Devicom/config.php');

$core_dir = dirname(dirname(__FILE__));

//$systemLogDirectory = $core_dir . '/html/var/log/';
//$systemLogFilename = rtrim(basename(shell_exec('ls -t -r -1 ' . $core_dir . '/html/var/log/system_log | head --lines 1')));
//
//if ($systemLogFilename) {
//
//    $message = "You are being notified that an error occurred during a system call.";
//    sendNotification($subject = 'A MAGENTO SYSTEM ERROR OCCURRED', $message);
//
//}

$reportLogDirectory = $core_dir . '/var/report/';
$reportLogFilename = rtrim(basename(shell_exec('ls -t -r -1 ' . $reportLogDirectory . '* | head --lines 1')));

if ($reportLogFilename) {

	$message = "You are being notified that an error report occurred.";
	sendNotification($subject = 'A MAGENTO ERROR REPORT OCCURRED', $message);
}

//$debugLogDirectory = $core_dir . '/var/debug/';
//$debugLogFilename = rtrim(basename(shell_exec('ls -t -r -1 ' . $debugLogDirectory . '* | head --lines 1')));
//
//if ($debugLogFilename) {
//
//	$message = "You are being notified that a MySQL slow query occurred.";
//	sendNotification($subject = 'A SLOW MYSQL QUERY OCCURRED', $message);
//}

$exceptionLogDirectory = $core_dir . '/var/log/';
$exceptionLogFilename = rtrim(basename(shell_exec('ls -t -r -1 ' . $exceptionLogDirectory . 'exception_log | head --lines 1')));

if ($exceptionLogFilename) {

	$file_handle = fopen($exceptionLogDirectory . $exceptionLogFilename, "r");
	while (!feof($file_handle)) {
		$line = fgets($file_handle);

		if (substr($line, 0, 9) == "exception") {
			if (substr($line, 0, 73) == "exception 'Mage_Core_Exception' with message 'Wrong gift card account ID.") {
				// echo substr($line, 0, 73) . "\n";
			} elseif (substr($line, 0, 63) == "exception 'Mage_Core_Exception' with message 'Gift card account") {
				// echo substr($line, 0, 63) . "\n";
			} elseif (substr($line, 0, 119) == "exception 'Mage_Core_Exception' with message 'PayPal NVP gateway errors: Instruct the customer to retry the transaction") {//contact customer service
				// echo substr($line, 0, 119) . "\n";
			} elseif (substr($line, 0, 112) == "exception 'Mage_Core_Exception' with message 'PayPal NVP gateway errors: This transaction couldn't be completed.") {
				// echo substr($line, 0, 112) . "\n";
			} elseif (substr($line, 0, 140) == "exception 'Mage_Core_Exception' with message 'PayPal NVP gateway errors: A match of the Shipping Address City, State, and Postal Code failed") {//correct address
				// echo substr($line, 0, 140) . "\n";
			} elseif (substr($line, 0, 129) == "exception 'Mage_Core_Exception' with message 'PayPal NVP gateway errors: There was an error in the Shipping Address Country field") {//correct address
				// echo substr($line, 0, 129) . "\n";
			} elseif (substr($line, 0, 126) == "exception 'Mage_Core_Exception' with message 'PayPal NVP gateway errors: The Buyer cannot pay with PayPal for this Transaction") {//contact customer service
				//echo substr($line, 0, 126) . "\n";
			} elseif (substr($line, 0, 137) == "exception 'Mage_Core_Exception' with message 'PayPal NVP gateway errors: The customer must return to PayPal to select new funding sources") {//provide link
				//echo substr($line, 0, 137) . "\n";
			} elseif (substr($line, 0, 146) == "exception 'Mage_Core_Exception' with message 'PayPal NVP gateway errors: This transaction cannot be processed. The shipping country is not allowed") {//??
				//echo substr($line, 0, 146) . "\n";
			} elseif (substr($line, 0, 123) == "exception 'Mage_Core_Exception' with message 'PayPal NVP gateway errors: The field Shipping Address Postal Code is required") {//Need to enter postal code
				//echo substr($line, 0, 123) . "\n";
			} elseif (substr($line, 0, 113) == "exception 'Mage_Core_Exception' with message 'PayPal NVP gateway errors: Internal Error (#10001: Internal Error).") {//Order gets placed - just Magento confusion
				//echo substr($line, 0, 113) . "\n";
			} elseif (substr($line, 0, 117) == "exception 'Mage_Core_Exception' with message 'PayPal NVP gateway errors: The field Shipping Address State is required") {//correct address
				//echo substr($line, 0, 117) . "\n";
			} elseif (substr($line, 0, 64) == "exception 'Exception' with message 'PayPal IPN postback failure.") {
				// echo substr($line, 0, 64) . "\n";
			} elseif (substr($line, 0, 64) == "exception 'Exception' with message 'Cannot handle payment status") {
				// echo substr($line, 0, 64) . "\n";
			} elseif (substr($line, 0, 80) == "exception 'Mage_Core_Exception' with message 'Maximum amount available to refund") {
				// echo substr($line, 0, 80) . "\n";
			} elseif (substr($line, 0, 104) == "exception 'Mage_Core_Exception' with message 'Gateway error: A duplicate transaction has been submitted.") {//what 2 minutes before retryinh
				//echo substr($line, 0, 104) . "\n";
			} elseif (substr($line, 0, 96) == "exception 'Mage_Core_Exception' with message 'Gateway error: This transaction has been declined.") {
				//echo substr($line, 0, 96) . "\n";
			} elseif (substr($line, 0, 95) == "exception 'Mage_Core_Exception' with message 'Gateway error: The credit card number is invalid.") {
				//echo substr($line, 0, 95) . "\n";
			} elseif (substr($line, 0, 89) == "exception 'Mage_Core_Exception' with message 'Gateway error: The credit card has expired.") {
				// echo substr($line, 0, 89) . "\n";
			} else {
				echo substr($line, 0) . "\n";
				$message = "$line";
				sendNotification($subject = 'AN UNKNOWN MAGENTO EXCEPTION OCCURRED', $message);
			}
		}
	}
	fclose($file_handle);
}

$catalogLogsDirectory = $core_dir . '/devicom_apps/catalog/logs/';
$catalogLogFilename = rtrim(basename(shell_exec('ls -t -r -1 ' . $catalogLogsDirectory . 'exception_log | head --lines 1')));

if ($catalogLogFilename) {

	$message = "You are being notified that an exception occurred during a catalog update.";
	sendNotification($subject = 'A CATALOG LOG EXCEPTION OCCURRED', $message);
}

$catalogDirectory = $core_dir . '/devicom_apps/catalog/';
$catalogLogFilename = rtrim(basename(shell_exec('ls -t -r -1 ' . $catalogDirectory . 'exception_log | head --lines 1')));

if ($catalogLogFilename) {

	$message = "You are being notified that an exception occurred during a catalog update.";
	sendNotification($subject = 'A CATALOG LOG EXCEPTION OCCURRED', $message);
}

$salesLogsDirectory = $core_dir . '/devicom_apps/sales/logs/';
$salesLogFilename = rtrim(basename(shell_exec('ls -t -r -1 ' . $salesLogsDirectory . 'exception_log | head --lines 1')));

if ($salesLogFilename) {

	$message = "You are being notified that an exception occurred during order processing.";
	sendNotification($subject = 'A SALES LOG EXCEPTION OCCURRED', $message);
}

$salesDirectory = $core_dir . '/devicom_apps/sales/';
$salesLogFilename = rtrim(basename(shell_exec('ls -t -r -1 ' . $salesDirectory . 'exception_log | head --lines 1')));

if ($salesLogFilename) {

	$message = "You are being notified that an exception occurred during order processing.";
	sendNotification($subject = 'A SALES EXCEPTION OCCURRED', $message);
}

$customerLogsDirectory = $core_dir . '/devicom_apps/customer/logs/';
$customerLogFilename = rtrim(basename(shell_exec('ls -t -r -1 ' . $customerLogsDirectory . 'exception_log | head --lines 1')));

if ($customerLogFilename) {

	$message = "You are being notified that an exception occurred during customer processing.";
	sendNotification($subject = 'A CUSTOMER LOG EXCEPTION OCCURRED', $message);
}




$customerDirectory = $core_dir . '/devicom_apps/customer/';
$customerLogFilename = rtrim(basename(shell_exec('ls -t -r -1 ' . $customerDirectory . 'exception_log | head --lines 1')));

if ($customerLogFilename) {

	$message = "You are being notified that an exception occurred during customer processing.";
	sendNotification($subject = 'A CUSTOMER EXCEPTION OCCURRED', $message);
}

$emailLogsDirectory = $core_dir . '/devicom_apps/email/logs/';
$emailLogFilename = rtrim(basename(shell_exec('ls -t -r -1 ' . $emailLogsDirectory . 'exception_log | head --lines 1')));

if ($emailLogFilename) {

	$message = "You are being notified that an exception occurred during email processing.";
	sendNotification($subject = 'AN EMAIL LOG EXCEPTION OCCURRED', $message);
}

$emailDirectory = $core_dir . '/devicom_apps/email/';
$emailLogFilename = rtrim(basename(shell_exec('ls -t -r -1 ' . $emailDirectory . 'exception_log | head --lines 1')));

if ($emailLogFilename) {

	$message = "You are being notified that an exception occurred during email processing.";
	sendNotification($subject = 'AN EMAIL EXCEPTION OCCURRED', $message);
}
?>
