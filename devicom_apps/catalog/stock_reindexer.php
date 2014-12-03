<?php

//Version 1.0 - 3/7/2012

require(dirname(dirname(__FILE__)) . '/lib/Devicom/config.php');

set_time_limit(0); //no timout
ini_set('memory_limit', '1024M');

$root_dir = dirname(__FILE__);
$catalogLogsDirectory = $root_dir . '/logs/';

//Check that no lock exists (indicating file is being written or processed by another script) and, if any, grab the oldest incremental_category_update to begin processing
if (!rtrim(basename(shell_exec('ls ' . $catalogLogsDirectory . 'reindexer_process* | head --lines 1')))
		&& !rtrim(basename(shell_exec('ls ' . $catalogLogsDirectory . 'stage | head --lines 1')))
) {

	// Initialize magento environment for 'admin' store
	require_once dirname(dirname(dirname(__FILE__))) . '/app/Mage.php';
	umask(0);
	Mage::app('admin'); //admin defines default value for all stores including the Main Website

	$realTime = realTime();

	//Open transaction log
	$transactionLogHandle = fopen($catalogLogsDirectory . 'transaction_log', 'a+');
	fwrite($transactionLogHandle, "->BEGIN PROCESSING:\n");
	fwrite($transactionLogHandle, "  ->START TIME    : " . $realTime[5] . '/' . $realTime[4] . '/' . $realTime[3] . ' ' . $realTime[2] . ':' . $realTime[1] . ':' . $realTime[0] . "\n");

	//Create process.lock to stop further processing of incremental and/or full updates
	$processLockFilename = 'reindexer_process_' . $realTime[5] . $realTime[4] . $realTime[3] . '_' . $realTime[2] . $realTime[1] . $realTime[0] . '.lock';
	$processLockHandle = fopen($catalogLogsDirectory . $processLockFilename, 'w+');
	fclose($processLockHandle);

	fwrite($transactionLogHandle, "  ->LOCK CREATED  : " . $processLockFilename . "\n");

	//Load adminhtml area of config.xml to initialize adminhtml observers
	Mage::app()->loadArea('adminhtml');

	fwrite($transactionLogHandle, "  ->INDEXING\n");

	try {
		// Reindex urls
		$processIds = array(8);

		foreach ($processIds as $processId) {
			fwrite($transactionLogHandle, "    ->START       : " . $processId . "\n");
			$process = Mage::getModel('index/process')->load($processId);
			$process->reindexAll();
			fwrite($transactionLogHandle, "    ->END         : " . $processId . "\n");
		}

		fwrite($transactionLogHandle, "  ->COMPLETED\n");
	} catch (Exception $e) {
		fwrite($transactionLogHandle, "  ->NOT COMPLETED : " . $e . "\n");
	}

	//Remove xml_process to allow processing of incremental and/or full updates
	if (file_exists($catalogLogsDirectory . $processLockFilename)) {
		unlink($catalogLogsDirectory . $processLockFilename);
		fwrite($transactionLogHandle, "  ->LOCK REMOVED  : " . $processLockFilename . "\n");
	}

	$endTime = realTime();
	fwrite($transactionLogHandle, "  ->FINISH TIME   : " . $endTime[5] . '/' . $endTime[4] . '/' . $endTime[3] . ' ' . $endTime[2] . ':' . $endTime[1] . ':' . $endTime[0] . "\n");
	fwrite($transactionLogHandle, "->END PROCESSING  :\n");

	//Close transaction log
	fclose($transactionLogHandle);
}
?>