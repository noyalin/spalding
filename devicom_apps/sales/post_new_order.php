<?php

require(dirname(dirname(__FILE__)) . '/lib/Devicom/config.php');

$root_dir = dirname(__FILE__);
$salesLogsDirectory = $root_dir . '/logs/';
$postDirectory = $root_dir . '/post/';
$sentDirectory = $root_dir . '/sent/';
$failedDirectory = $root_dir . '/failed/';

/* Check for new order process lock. Limit process to one instance only */
if (rtrim(basename(shell_exec('ls ' . $salesLogsDirectory . 'post_new_order_process*.lock | head --lines 1')))) {
	exit();
}

/* Read all "new_order" files in POST directory into an array */
$newOrders = array();
if (is_dir($postDirectory)) {
	if (($dh = opendir($postDirectory))) {
		while (($file = readdir($dh)) !== false) {
			if (is_file($postDirectory . $file)
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
$transactionLogHandle = fopen($salesLogsDirectory . 'post_new_order_transaction_log', 'a+');
$realTime = realTime();
fwrite($transactionLogHandle, "->BEGIN PROCESSING    : \n");
fwrite($transactionLogHandle, "  ->START TIME        : " . $realTime[5] . '/' . $realTime[4] . '/' . $realTime[3] . ' ' . $realTime[2] . ':' . $realTime[1] . ':' . $realTime[0] . "\n");

/* Create process_lock so that these don't overlap
 * Use "_lock" extension so as to be ignored by other sales processes looking for ".lock" */
$processLockFilename = 'post_new_order_process_' . $realTime[5] . $realTime[4] . $realTime[3] . '_' . $realTime[2] . $realTime[1] . $realTime[0] . '.lock';
$processLockHandle = fopen($salesLogsDirectory . $processLockFilename, 'w+');
fclose($processLockHandle);
fwrite($transactionLogHandle, "  ->LOCK CREATED      : " . $processLockFilename . "\n");

$n = 0;
require_once dirname(dirname(dirname(__FILE__))) . '/app/Mage.php';
umask(0);
Mage::app('admin');
foreach ($newOrders as $filename) {
    $xml = simplexml_load_file($postDirectory.$filename);
    $result = $xml->xpath("/Root/Order/OrderNumber");
    $number = $result[0];
    if(!$number){
        continue;
    }
    $order = Mage::getModel('sales/order')->loadByIncrementId($number);
    $status = $order->getStatus();
    if($status != 'alipay_trade_finished'){
        continue;
    }
    try {

	$realTime = realTime();
	fwrite($transactionLogHandle, "    ->PROCESSING FILE : " . $filename . "\n");
	fwrite($transactionLogHandle, "      ->START TIME    : " . $realTime[5] . '/' . $realTime[4] . '/' . $realTime[3] . ' ' . $realTime[2] . ':' . $realTime[1] . ':' . $realTime[0] . "\n");

	$xml = file_get_contents($postDirectory . $filename);
	$url = CONF_NEWORDER_RECEIVER_ASPX_URL;

	$context = stream_context_create(array(
	    'http' => array(
	    'method'  => 'POST',
	    'header'  => "Content-Type: text/xml",
	    'content' => $xml
	)));

	// Send post
	$response = file_get_contents($url, false, $context);
    fwrite($transactionLogHandle, "      ->response         : $response\n");
	// Determine if successful
	if (strstr($response, 'Success')) {
	    //Move file to sent directory
	    rename($postDirectory . $filename, $sentDirectory . $filename);
	    fwrite($transactionLogHandle, "      ->SUCCESS   : " . $realTime[5] . '/' . $realTime[4] . '/' . $realTime[3] . ' ' . $realTime[2] . ':' . $realTime[1] . ':' . $realTime[0] . "\n");
	} elseif (strstr($response, 'Failed')) {
	    throw new Exception($filename . " : Failed response returned\n");
	} else {
	    throw new Exception($filename . " : Unknown exception\n");
	}

    } catch (Exception $e) {

		fwrite($transactionLogHandle, "      ->ERROR         : See exception_log\n");

		//Append error to exception log
		$exceptionLogHandle = fopen($salesLogsDirectory . 'exception_log', 'a');
		fwrite($exceptionLogHandle, $e->getMessage());
		fclose($exceptionLogHandle);

		//Move XML file to failed directory
		rename($postDirectory . $filename, $failedDirectory . $filename);
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

/* Remove process lock */
if (file_exists($salesLogsDirectory . $processLockFilename)){
	unlink($salesLogsDirectory . $processLockFilename);
	fwrite($transactionLogHandle, "  ->LOCK REMOVED      : " . $processLockFilename . "\n");
}

$endTime = realTime();
fwrite($transactionLogHandle, "  ->FINISH TIME       : " . $endTime[5] . '/' . $endTime[4] . '/' . $endTime[3] . ' ' . $endTime[2] . ':' . $endTime[1] . ':' . $endTime[0] . "\n");
fwrite($transactionLogHandle, "->END PROCESSING      : \n");

/* Close transaction log */
fclose($transactionLogHandle);

?>
