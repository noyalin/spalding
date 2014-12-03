<?php

require(dirname(dirname(__FILE__)) . '/lib/Devicom/config.php');

$root_dir = dirname(__FILE__);
$salesLogsDirectory = $root_dir . '/logs/';
$sentDirectory = $root_dir . '/sent/';
$failedDirectory = $root_dir . '/failed/';







/* Read all "new_order" files in POST directory into an array */
$newOrders = array();
if (is_dir($failedDirectory)) {
	if (($dh = opendir($failedDirectory))) {
		while (($file = readdir($dh)) !== false) {
			if (is_file($failedDirectory . $file)
				&& preg_match('/new_order/', $file)) {
				$newOrders[] = $file;
			}
		}

		closedir($dh);
	}
}

/* Nothing to do */
if (!count($newOrders)) {
	exit();
}

/* Sort by filename (timestamp) to handle in order */
sort($newOrders);

/* Open transaction log */
$transactionLogHandle = fopen($salesLogsDirectory . 'failed_post_tracker_transaction_log', 'a+');

$realTime = realTime();
fwrite($transactionLogHandle, "->BEGIN PROCESSING    : \n");
fwrite($transactionLogHandle, "  ->START TIME        : " . $realTime[5] . '/' . $realTime[4] . '/' . $realTime[3] . ' ' . $realTime[2] . ':' . $realTime[1] . ':' . $realTime[0] . "\n");

$n = 0;
foreach ($newOrders as $filename) {

    try {

	$realTime = realTime();
	fwrite($transactionLogHandle, "    ->PROCESSING FILE : " . $filename . "\n");
	fwrite($transactionLogHandle, "      ->START TIME    : " . $realTime[5] . '/' . $realTime[4] . '/' . $realTime[3] . ' ' . $realTime[2] . ':' . $realTime[1] . ':' . $realTime[0] . "\n");

	$xml = file_get_contents($failedDirectory . $filename);
	$url = CONF_NEWORDER_RECEIVER_ASPX_URL;

	$context = stream_context_create(array(
	    'http' => array(
	    'method'  => 'POST',
	    'header'  => "Content-Type: text/xml",
	    'content' => $xml
	)));

	// Send post
	$response = file_get_contents($url, false, $context);

	// Determine if successful
	if (strstr($response, 'Success')) {
	    //Move file to sent directory
	    rename($failedDirectory . $filename, $sentDirectory . $filename);
	    fwrite($transactionLogHandle, "      ->SUCCESS   : " . $realTime[5] . '/' . $realTime[4] . '/' . $realTime[3] . ' ' . $realTime[2] . ':' . $realTime[1] . ':' . $realTime[0] . "\n");
	} elseif (strstr($response, 'Failed')) {
	    throw new Exception($filename . " : Failed response returned\n");
	} else {
	    throw new Exception($filename . " : Unknown exception\n");
	}

    } catch (Exception $e) {

		fwrite($transactionLogHandle, "  ->ERROR         : See exception_log\n");

		//Append error to exception log
		$exceptionLogHandle = fopen($salesLogsDirectory . 'exception_log', 'a');
		fwrite($exceptionLogHandle, $e->getMessage());
		fclose($exceptionLogHandle);

		$message = "You are being notified that the new order $filename failed to be posted.\r\n\r\nThis may indicate that there is a temporary problem with the connection or with the remote server.";
		sendNotification($subject = 'NEW ORDER POST FAILED', $message);
    }

    $endTime = realTime();
    fwrite($transactionLogHandle, "      ->FINISH TIME   : " . $endTime[5] . '/' . $endTime[4] . '/' . $endTime[3] . ' ' . $endTime[2] . ':' . $endTime[1] . ':' . $endTime[0] . "\n");
    fwrite($transactionLogHandle, "    ->END PROCESSING  : " . $filename . "\n");

	$n++;

	/* If limit is set and is greater than zero,
	 * then only handle that many. Otherwise, process all files. */
	if (defined("CONF_NEW_ORDER_POST_LIMIT")
		&& CONF_NEW_ORDER_POST_LIMIT > 0
		&& $n > CONF_NEW_ORDER_POST_LIMIT) {
		break;
	}
}

$endTime = realTime();
fwrite($transactionLogHandle, "  ->FINISH TIME       : " . $endTime[5] . '/' . $endTime[4] . '/' . $endTime[3] . ' ' . $endTime[2] . ':' . $endTime[1] . ':' . $endTime[0] . "\n");
fwrite($transactionLogHandle, "->END PROCESSING      : \n");

/* Close transaction log */
fclose($transactionLogHandle);

?>
