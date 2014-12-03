<?php
			
require(dirname(dirname(__FILE__)) . '/lib/Devicom/config.php');

set_time_limit(0);//no timout

$root_dir = dirname(__FILE__);
$salesLogsDirectory = $root_dir . '/logs/';
$transactionLogsDirectory = $root_dir . '/logs/transaction_logs/';

if (file_exists($salesLogsDirectory . 'authorizenet_response_log')) {

	$realTime = realTime();
	$filename = 'authorizenet_response_log_' . $realTime[5] . $realTime[4] . $realTime[3] . '_' . $realTime[2] . $realTime[1] . $realTime[0];
	
	//Open transaction log
	$transactionLogHandle = fopen($salesLogsDirectory . 'phantom_order_log', 'a+');
	fwrite($transactionLogHandle, "->BEGIN PROCESSING: " . $filename . "\n");
	fwrite($transactionLogHandle, "  ->START TIME    : " . $realTime[5] . '/' . $realTime[4] . '/' . $realTime[3] . ' ' . $realTime[2] . ':' . $realTime[1] . ':' . $realTime[0] . "\n");

	//Rename file to allow new log to be created for next check
	rename($salesLogsDirectory . 'authorizenet_response_log', $salesLogsDirectory . $filename);
	fwrite($transactionLogHandle, "  ->RENAMED FILE  : " . $filename . "\n");	

	$file_handle = fopen($salesLogsDirectory . $filename, "r");
	$i = 1;
	while (!feof($file_handle)) {
		$line = fgets($file_handle);
		$orderNumbers[$i] = explode("(~)", $line);
		$i++;
	}
	fclose($file_handle);
	
	foreach ($orderNumbers as $lineNumber => $line) {
		if ($line[3] && $line[7]) {
			// Initialize magento environment for 'admin' store
			require_once dirname(dirname(dirname(__FILE__))) . '/app/Mage.php';
			umask(0);
			Mage::app('admin'); //admin defines default value for all stores including the Main Website

			// Get resource
			$resource = Mage::getSingleton('core/resource');
			$readConnection = $resource->getConnection('core_read');

			//look up order if approved
			if ($line[3] == 'This transaction has been approved.') {	
				$testOrderQuery = "SELECT COUNT(*) AS `count` FROM `sales_flat_order` AS `sfo` WHERE `sfo`.`increment_id` = '" . $line[7] . "'";
				$testOrderResults = $readConnection->query($testOrderQuery);
				foreach ($testOrderResults as $testOrderResult) {
					if (!$testOrderResult['count']) {
						fwrite($transactionLogHandle, "  ->PHANTOM ORDER : " . $line[7] . "\n");
						$message = "You are being notified that a phantom order has been identified for order number " . $line[7] . ".";
						sendNotification($subject = 'Phantom Order', $message);
					}
				}
			}
		} else {
			fwrite($transactionLogHandle, "  ->BAD ENTRY     : LINE " . $lineNumber . "\n");
		}
	}
	
	//Move log file
	rename($salesLogsDirectory . $filename, $transactionLogsDirectory . $filename);
	
	$endTime = realTime();
	fwrite($transactionLogHandle, "  ->FINISH TIME   : " . $endTime[5] . '/' . $endTime[4] . '/' . $endTime[3] . ' ' . $endTime[2] . ':' . $endTime[1] . ':' . $endTime[0] . "\n");
	fwrite($transactionLogHandle, "->END PROCESSING  : " . $filename . "\n");

	//Close transaction log
	fclose($transactionLogHandle);
}

?>