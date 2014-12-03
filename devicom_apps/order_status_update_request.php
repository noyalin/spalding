<?php

require(dirname(__FILE__) . '/lib/Devicom/config.php');

set_time_limit(0); //no timout

$ip = $_SERVER['REMOTE_ADDR'];
// $allowed (array), defined in config.php

$core_dir = dirname(dirname(__FILE__));
$salesLogsDirectory = $core_dir . '/devicom_apps/sales/logs/';
$temporaryDirectory = $core_dir . '/devicom_apps/sales/temporary/';
$receivedDirectory = $core_dir . '/devicom_apps/sales/received/';
$failedDirectory = $core_dir . '/devicom_apps/sales/failed/';

$realTime = realTime();

//Only allow POST from allowed IP addresses
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	//Open request log
	$requestLogHandle = fopen($salesLogsDirectory . 'order_status_update_request_log', 'a+');
	fwrite($requestLogHandle, "->START TIME      : " . $realTime[5] . '/' . $realTime[4] . '/' . $realTime[3] . ' ' . $realTime[2] . ':' . $realTime[1] . ':' . $realTime[0] . "\n");

	//Create write.lock to stop further processing of incremental and/or full updates until post is written and moved to the received directory
	$writeLockFilename = 'write_' . $realTime[5] . $realTime[4] . $realTime[3] . '_' . $realTime[2] . $realTime[1] . $realTime[0] . '.lock';
	$writeLockHandle = fopen($salesLogsDirectory . $writeLockFilename, 'w+');
	fclose($writeLockHandle);

	fwrite($requestLogHandle, "  ->LOCK CREATED  : " . $writeLockFilename . "\n");

	try {

		//Store request
		if (!$request = file_get_contents('php://input')) {

			//Write file to failed directory
			$filename = 'unknown_' . $realTime[5] . $realTime[4] . $realTime[3] . '_' . $realTime[2] . $realTime[1] . $realTime[0] . substr(microtime(), 2, 3) . '.xml';
			$failedHandle = fopen($failedDirectory . $filename, 'w');
			fwrite($failedHandle, $request);
			fclose($failedHandle);

			throw new Exception("No data posted in request");
		}

		$xml = new SimpleXMLElement($request);


		$filename = 'order_status_update_' . $realTime[5] . $realTime[4] . $realTime[3] . '_' . $realTime[2] . $realTime[1] . $realTime[0] . substr(microtime(), 2, 3) . '.xml';

		//Write file to temporary directory to prevent processing by another script instance
		$temporaryHandle = fopen($temporaryDirectory . $filename, 'w');
		fwrite($temporaryHandle, $request);
		fclose($temporaryHandle);
		fwrite($requestLogHandle, "  ->FILE CREATED  : " . $filename . "\n");

		//Move XML file to received directory
		if (@!rename($temporaryDirectory . $filename, $receivedDirectory . $filename)) {
			throw new Exception("Failed to move file to received directory");
		}

		fwrite($requestLogHandle, "  ->FILE MOVED    : " . $filename . "\n");
		$response = 'Success';
	} catch (Exception $e) {

		if (file_exists($temporaryDirectory . $filename)) {
			rename($temporaryDirectory . $filename, $failedDirectory . $filename);
		}

		$response = 'Failed';

		//Append error to exception log
		$exceptionLogHandle = fopen($salesLogsDirectory . 'exception_log', 'a');
		fwrite($exceptionLogHandle, '->' . $filename . " - " . $e->getMessage() . "\n");
		fclose($exceptionLogHandle);
	}

	//Remove write.lock to allow processing of incremental and/or full updates
	if (file_exists($salesLogsDirectory . $writeLockFilename)) {
		unlink($salesLogsDirectory . $writeLockFilename);
		fwrite($requestLogHandle, "  ->LOCK REMOVED  : " . $writeLockFilename . "\n");
	}

	$endTime = realTime();
	fwrite($requestLogHandle, "->FINISH TIME     : " . $endTime[5] . '/' . $endTime[4] . '/' . $endTime[3] . ' ' . $endTime[2] . ':' . $endTime[1] . ':' . $endTime[0] . "\n");

	//Close request log
	fclose($requestLogHandle);
} else {

	$response = false;

	//Append illegal IP to access log
	$accessLogHandle = fopen($salesLogsDirectory . 'access_log', 'a');
	fwrite($accessLogHandle, $realTime[5] . $realTime[4] . $realTime[3] . '_' . $realTime[2] . $realTime[1] . $realTime[0] . ' - ' . $ip . "\n");
	fclose($accessLogHandle);
}

if ($response) {

//    //Close connection since request received
//    header('HTTP/1.1 200 OK');
//    header('Content-Length: 0');
//    header('Connection: close');
//    //Apparently required to send response
//    echo 'Close';
//    ob_end_flush();
//    ob_start();
//    ob_flush();
//    flush();
	//Send response
	header("Content-Type:text/xml");
	echo '<?xml version="1.0" encoding="UTF-8"?><root>' . $response . '</root>';
}
?>
