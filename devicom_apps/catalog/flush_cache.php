<?php

require(dirname(dirname(__FILE__)) . '/lib/Devicom/config.php');

$catalogLogsDirectory = dirname(__FILE__) . '/logs/';

// Initialize magento environment for 'admin' store
require_once dirname(dirname(dirname(__FILE__))) . '/app/Mage.php';
umask(0);
Mage::app('admin'); //admin defines default value for all stores including the Main Website

$realTime = realTime();

//Open transaction log
$transactionLogHandle = fopen($catalogLogsDirectory . 'transaction_log', 'a+');
fwrite($transactionLogHandle, "->BEGIN PROCESSING:\n");
fwrite($transactionLogHandle, "  ->START TIME    : " . $realTime[5] . '/' . $realTime[4] . '/' . $realTime[3] . ' ' . $realTime[2] . ':' . $realTime[1] . ':' . $realTime[0] . "\n");

//Load adminhtml area of config.xml to initialize adminhtml observers -- required to trigger pagecache
Mage::app()->loadArea('adminhtml');

//REFRESH block level cache (refreshes category and product detail pages)-- This will be targeted by ID in the future
//Note full_page doesn't seem to be required???
$types = array(
	2 => "block_html",
	7 => "full_page"
);

if (!empty($types)) {
	fwrite($transactionLogHandle, "  ->CACHE         : START\n");
	foreach ($types as $type) {
		Mage::app()->getCacheInstance()->cleanType($type);
		Enterprise_PageCache_Model_Cache::getCacheInstance()->cleanType($type);
		//Mage::dispatchEvent('adminhtml_cache_refresh_type', array('type' => $type));
		fwrite($transactionLogHandle, "  ->CACHE TYPE    : " . $type . "\n");
	}
	fwrite($transactionLogHandle, "  ->CACHE         : END\n");
}


$endTime = realTime();
fwrite($transactionLogHandle, "  ->FINISH TIME   : " . $endTime[5] . '/' . $endTime[4] . '/' . $endTime[3] . ' ' . $endTime[2] . ':' . $endTime[1] . ':' . $endTime[0] . "\n");
fwrite($transactionLogHandle, "->END PROCESSING  :\n");

//Close transaction log
fclose($transactionLogHandle);
?>