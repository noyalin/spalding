<?php

require(dirname(dirname(__FILE__)) . '/lib/Devicom/config.php');

$core_dir = dirname(dirname(dirname(dirname(dirname(__FILE__)))));
$web_dir = dirname(dirname(dirname(dirname(__FILE__))));
$root_dir = dirname(__FILE__);
$catalogLogsDirectory = $root_dir . '/logs/';
$realTime = realTime();

//Open rsync log
$transactionLogHandle = fopen($catalogLogsDirectory . 'rsync_log', 'a+');
fwrite($transactionLogHandle, "->BEGIN PROCESSING:\n");
fwrite($transactionLogHandle, "  ->START TIME    : " . $realTime[5] . '/' . $realTime[4] . '/' . $realTime[3] . ' ' . $realTime[2] . ':' . $realTime[1] . ':' . $realTime[0] . "\n");

// Synchronize sneakerheads storage group
fwrite($transactionLogHandle, "    ->UPDATING    : " . CONF_RSYNC_DOMAIN_NAME . " : js\n");
if (!$shell_import = shell_exec('rsync -rzv --update --delete --log-file=' . $catalogLogsDirectory . 'rsync_log -e "ssh -i ' . $core_dir . '/netstorage_keys/netstorage_' . CONF_RSYNC_DOMAIN_NAME . '_key" ' . $web_dir . '/html/js/ sshacs@' . CONF_RSYNC_DOMAIN_NAME . '.upload.akamai.com:/' . CONF_RSYNC_AKAMAI_ID . '/js/')) {
	
}
fwrite($transactionLogHandle, "    ->UPDATED     : " . CONF_RSYNC_DOMAIN_NAME . " : js\n");
fwrite($transactionLogHandle, "    ->UPDATING    : " . CONF_RSYNC_DOMAIN_NAME . " : skin\n");
if (!$shell_import = shell_exec('rsync -rzv --update --delete --log-file=' . $catalogLogsDirectory . 'rsync_log -e "ssh -i ' . $core_dir . '/netstorage_keys/netstorage_' . CONF_RSYNC_DOMAIN_NAME . '_key" ' . $web_dir . '/html/skin/ sshacs@' . CONF_RSYNC_DOMAIN_NAME . '.upload.akamai.com:/' . CONF_RSYNC_AKAMAI_ID . '/skin/')) {
	
}
fwrite($transactionLogHandle, "    ->UPDATED     : " . CONF_RSYNC_DOMAIN_NAME . " : skin\n");
fwrite($transactionLogHandle, "    ->UPDATING    : " . CONF_RSYNC_DOMAIN_NAME . " : favicon.ico\n");
if (!$shell_import = shell_exec('rsync -rzv --update --log-file=' . $catalogLogsDirectory . 'rsync_log -e "ssh -i ' . $core_dir . '/netstorage_keys/netstorage_' . CONF_RSYNC_DOMAIN_NAME . '_key" ' . $web_dir . '/html/favicon.ico sshacs@' . CONF_RSYNC_DOMAIN_NAME . '.upload.akamai.com:/' . CONF_RSYNC_AKAMAI_ID . '/')) {
	
}
fwrite($transactionLogHandle, "    ->UPDATED     : " . CONF_RSYNC_DOMAIN_NAME . " : favicon.ico\n");
fwrite($transactionLogHandle, "    ->UPDATING    : " . CONF_RSYNC_DOMAIN_NAME . " : robots.txt\n");
if (!$shell_import = shell_exec('rsync -rzv --update --log-file=' . $catalogLogsDirectory . 'rsync_log -e "ssh -i ' . $core_dir . '/netstorage_keys/netstorage_' . CONF_RSYNC_DOMAIN_NAME . '_key" ' . $web_dir . '/html/robots.txt sshacs@' . CONF_RSYNC_DOMAIN_NAME . '.upload.akamai.com:/' . CONF_RSYNC_AKAMAI_ID . '/')) {
	
}
fwrite($transactionLogHandle, "    ->UPDATED     : " . CONF_RSYNC_DOMAIN_NAME . " : robots.txt\n");
fwrite($transactionLogHandle, "    ->UPDATING    : " . CONF_RSYNC_DOMAIN_NAME . " : sitemap.xml\n");
if (!$shell_import = shell_exec('rsync -rzv --update --log-file=' . $catalogLogsDirectory . 'rsync_log -e "ssh -i ' . $core_dir . '/netstorage_keys/netstorage_' . CONF_RSYNC_DOMAIN_NAME . '_key" ' . $web_dir . '/html/sitemap/sneakerhead/sitemap.xml sshacs@' . CONF_RSYNC_DOMAIN_NAME . '.upload.akamai.com:/' . CONF_RSYNC_AKAMAI_ID . '/')) {
	
}
fwrite($transactionLogHandle, "    ->UPDATED     : " . CONF_RSYNC_DOMAIN_NAME . " : sitemap.xml\n");

$endTime = realTime();
fwrite($transactionLogHandle, "  ->FINISH TIME   : " . $endTime[5] . '/' . $endTime[4] . '/' . $endTime[3] . ' ' . $endTime[2] . ':' . $endTime[1] . ':' . $endTime[0] . "\n");
fwrite($transactionLogHandle, "->END PROCESSING  :\n");

//Close transaction log
fclose($transactionLogHandle);
?>