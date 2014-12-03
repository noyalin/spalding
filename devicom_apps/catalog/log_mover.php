<?php

require(dirname(dirname(__FILE__)) . '/lib/Devicom/config.php');

$core_dir = dirname(dirname(dirname(__FILE__)));
$root_dir = dirname(__FILE__);
$catalogLogsDirectory = $root_dir . '/logs/';
$requestLogsDirectory = $root_dir . '/logs/request_logs/';
$transactionLogsDirectory = $root_dir . '/logs/transaction_logs/';
$categoryXmlGeneratorLogsDirectory = $root_dir . '/logs/category_xml_generator_logs/';
$rsyncLogsDirectory = $root_dir . '/logs/rsync_logs/';
$exceptionLogsDirectory = $root_dir . '/logs/exception_logs/';
$errorLogsDirectory = $root_dir . '/logs/error_logs/';
$varLogsDirectory = $core_dir . '/var/log/';
$myBuysLogsDirectory = $core_dir . '/var/log/mybuys_logs/';

$realTime = realTime();

if (file_exists($catalogLogsDirectory . 'request_log')) {
	$filename = 'request_log_' . $realTime[5] . $realTime[4] . $realTime[3] . '_' . $realTime[2] . $realTime[1] . $realTime[0];
	rename($catalogLogsDirectory . 'request_log', $requestLogsDirectory . $filename);
}

if (file_exists($catalogLogsDirectory . 'transaction_log')) {
	$filename = 'transaction_log_' . $realTime[5] . $realTime[4] . $realTime[3] . '_' . $realTime[2] . $realTime[1] . $realTime[0];
	rename($catalogLogsDirectory . 'transaction_log', $transactionLogsDirectory . $filename);
}

if (file_exists($catalogLogsDirectory . 'exception_log')) {
	$filename = 'exception_log_' . $realTime[5] . $realTime[4] . $realTime[3] . '_' . $realTime[2] . $realTime[1] . $realTime[0];
	rename($catalogLogsDirectory . 'exception_log', $exceptionLogsDirectory . $filename);
}

if (file_exists($catalogLogsDirectory . 'http_response_error_log')) {
	$filename = 'http_response_error_log_' . $realTime[5] . $realTime[4] . $realTime[3] . '_' . $realTime[2] . $realTime[1] . $realTime[0];
	rename($catalogLogsDirectory . 'http_response_error_log', $errorLogsDirectory . $filename);
}

if (file_exists($catalogLogsDirectory . 'category_xml_generator_log')) {
	$filename = 'category_xml_generator_log_' . $realTime[5] . $realTime[4] . $realTime[3] . '_' . $realTime[2] . $realTime[1] . $realTime[0];
	rename($catalogLogsDirectory . 'category_xml_generator_log', $categoryXmlGeneratorLogsDirectory . $filename);
}

if (file_exists($catalogLogsDirectory . 'rsync_log')) {
	$filename = 'rsync_log_' . $realTime[5] . $realTime[4] . $realTime[3] . '_' . $realTime[2] . $realTime[1] . $realTime[0];
	rename($catalogLogsDirectory . 'rsync_log', $rsyncLogsDirectory . $filename);
}

if (file_exists($varLogsDirectory . 'mybuys.log')) {
	$filename = 'mybuys_log_' . $realTime[5] . $realTime[4] . $realTime[3] . '_' . $realTime[2] . $realTime[1] . $realTime[0];
	rename($varLogsDirectory . 'mybuys.log', $myBuysLogsDirectory . $filename);
}
?>