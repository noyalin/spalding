<?php

require(dirname(dirname(__FILE__)) . '/lib/Devicom/config.php');

$root_dir = dirname(__FILE__);
$catalogLogsDirectory = $root_dir . '/logs/';

$realTime = realTime();

//Open transaction log
$transactionLogHandle = fopen($catalogLogsDirectory . 'transaction_log', 'a+');
fwrite($transactionLogHandle, "->BEGIN PROCESSING:\n");
fwrite($transactionLogHandle, "  ->START TIME    : " . $realTime[5] . '/' . $realTime[4] . '/' . $realTime[3] . ' ' . $realTime[2] . ':' . $realTime[1] . ':' . $realTime[0] . "\n");

// Initialize magento environment for 'admin' store
require_once dirname(dirname(dirname(__FILE__))) . '/app/Mage.php';
umask(0);
Mage::app('admin'); //admin defines default value for all stores including the Main Website
//Load adminhtml area of config.xml to initialize adminhtml observers -- required to trigger pagecache
Mage::app()->loadArea('adminhtml');

// init and load sitemap model
$id = 1;
$sitemap = Mage::getModel('sitemap/sitemap');
/* @var $sitemap Mage_Sitemap_Model_Sitemap */
$sitemap->load($id);
// if sitemap record exists
if ($sitemap->getId()) {
	try {
		$sitemap->generateXml();
		fwrite($transactionLogHandle, "  ->SITEMAP       : CREATED\n");
	} catch (Mage_Core_Exception $e) {
		fwrite($transactionLogHandle, "  ->ERROR         : See exception_log\n");

		//Append error to exception log file
		$exceptionLogHandle = fopen($catalogLogsDirectory . 'exception_log', 'a');
		fwrite($exceptionLogHandle, '->sitemap.xml - ' . $e->getMessage() . "\n");
		fclose($exceptionLogHandle);
	} catch (Exception $e) {
		fwrite($transactionLogHandle, "  ->ERROR         : See exception_log\n");

		//Append error to exception log file
		$exceptionLogHandle = fopen($catalogLogsDirectory . 'exception_log', 'a');
		fwrite($exceptionLogHandle, "->sitemap.xml - Unable to generate the sitemap.\n");
		fclose($exceptionLogHandle);
	}
} else {
	fwrite($transactionLogHandle, "  ->ERROR         : See exception_log\n");

	//Append error to exception log file
	$exceptionLogHandle = fopen($catalogLogsDirectory . 'exception_log', 'a');
	fwrite($exceptionLogHandle, "->sitemap.xml - Sitemap not found.\n");
	fclose($exceptionLogHandle);
}

$endTime = realTime();
fwrite($transactionLogHandle, "  ->FINISH TIME   : " . $endTime[5] . '/' . $endTime[4] . '/' . $endTime[3] . ' ' . $endTime[2] . ':' . $endTime[1] . ':' . $endTime[0] . "\n");
fwrite($transactionLogHandle, "->END PROCESSING  :\n");

//Close transaction log
fclose($transactionLogHandle);
?>