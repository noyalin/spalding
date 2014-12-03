<?php

require(dirname(dirname(__FILE__)) . '/lib/Devicom/config.php');

$root_dir = dirname(__FILE__);
$salesLogsDirectory = $root_dir . '/logs/';
$salesSentDirectory = $root_dir . '/sent/';

$filename = rtrim(basename(shell_exec('ls -t -r -1 ' . $salesLogsDirectory . '*.lock | head --lines 1')));

if ($filename) {
    $timestamp = time();

    //Get the last modified time of the file --
    $timeFileHasBeenLocked = $timestamp - filemtime($salesLogsDirectory . $filename);

    // Check if file has been locked for over 30 minutes (1800 seconds)
    if ($timeFileHasBeenLocked >= 1800) {

	$message = "You are being notified that the lock $filename is still active after 15 minutes.\r\n\r\nThis may indicate that either a transaction is still processing a request or an exception occurred causing the script to terminate and did not remove the lock file. You can log in to the admin and view the transaction log to determine if any activity is still taking place. If there is not, then the lock file can be removed by utilizing the lock button in the admin. This will allow future transactions to be processed.";
	sendNotification($subject = 'TRANSACTION LOCK STILL ACTIVE FOR PRODUCT PROCESSING', $message);

    } else {
	echo "Hasn't been 15 minutes yet!\n";
    }
}

$filename = rtrim(basename(shell_exec('ls -t -1 -A ' . $root_dir . '/sent/new_order_* | head --lines 1')));

if ($filename) {
    $timestamp = time();

    //Get the last modified time of the file --
    $timeFileHasBeenLocked = $timestamp - filemtime($salesSentDirectory . $filename);

    // Check if file is older than 60 minutes (3600 seconds)
    if ($timeFileHasBeenLocked >= 3600) {

		$message = "You are being notified that a new order has not been placed for over 60 minutes.";
		sendNotification($subject = 'IT HAS BEEN OVER 60 MINUTES SINCE A NEW ORDER HAS BEEN PLACED', $message, $cc = true, $override = false);
    } else {
		echo "Hasn't been 60 minutes yet!\n";
    }
}

?>
