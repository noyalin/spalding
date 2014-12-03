<?php

//Account user needed directories for exception tracker
$systemLogDirectory = '/chroot/home/rxkicksc/rxkicks.com/html/var/log/';
$testFileHandle = fopen($systemLogDirectory . 'test_log', 'a+');
fwrite($testFileHandle, "TEST\n");
fclose($testFileHandle);
$exceptionLogDirectory = '/chroot/home/rxkicksc/rxkicks.com/html/var/log/';
$catalogLogsDirectory = '/chroot/home/rxkicksc/rxkicks.com/html/devicom_apps/catalog/logs/';
$salesLogsDirectory = '/chroot/home/rxkicksc/rxkicks.com/html/devicom_apps/sales/logs/';

//Account user needed directories for catalog update processors
$temporaryDirectory = '/chroot/home/rxkicksc/rxkicks.com/html/devicom_apps/catalog/temporary/';
$receivedDirectory = '/chroot/home/rxkicksc/rxkicks.com/html/devicom_apps/catalog/received/';
$failedDirectory = '/chroot/home/rxkicksc/rxkicks.com/html/devicom_apps/catalog/failed/';
$processedDirectory = '/chroot/home/rxkicksc/rxkicks.com/html/devicom_apps/catalog/processed/';
$catalogLogsDirectory = '/chroot/home/rxkicksc/rxkicks.com/html/devicom_apps/catalog/logs/';

//Account user needed directories for full catalog update processor -- ran by rebuildXmlFiles function
$inventoryDirectory = '/chroot/home/rxkicksc/rxkicks.com/html/devicom_apps/inventory/';
$newInventoryDirectory = '/chroot/home/rxkicksc/rxkicks.com/html/devicom_apps/new_inventory/';
$oldInventoryDirectory = '/chroot/home/rxkicksc/rxkicks.com/html/devicom_apps/old_inventory/';

//Account user needed directories for incremental catalog update processor
$inventoryDirectory = '/chroot/home/rxkicksc/rxkicks.com/html/devicom_apps/inventory/';
$receivedDirectory = '/chroot/home/rxkicksc/rxkicks.com/html/devicom_apps/catalog/received/';
$failedDirectory = '/chroot/home/rxkicksc/rxkicks.com/html/devicom_apps/catalog/failed/';
$processedDirectory = '/chroot/home/rxkicksc/rxkicks.com/html/devicom_apps/catalog/processed/';
$catalogLogsDirectory = '/chroot/home/rxkicksc/rxkicks.com/html/devicom_apps/catalog/logs/';

//Account user needed directories for catalog lock tracker and log mover
$catalogLogsDirectory = '/chroot/home/rxkicksc/rxkicks.com/html/devicom_apps/catalog/logs/';
$requestLogsDirectory = '/chroot/home/rxkicksc/rxkicks.com/html/devicom_apps/catalog/logs/request_logs/';
$transactionLogsDirectory = '/chroot/home/rxkicksc/rxkicks.com/html/devicom_apps/catalog/logs/transaction_logs/';
$exceptionLogsDirectory = '/chroot/home/rxkicksc/rxkicks.com/html/devicom_apps/catalog/logs/exception_logs/';

//Account user needed directories for create contact processor
$subscribersDirectory = '/chroot/home/rxkicksc/rxkicks.com/html/devicom_apps/sales/subscribers/';
$failedDirectory = '/chroot/home/rxkicksc/rxkicks.com/html/devicom_apps/sales/failed/';
$sentDirectory = '/chroot/home/rxkicksc/rxkicks.com/html/devicom_apps/sales/sent/';
$salesLogsDirectory = '/chroot/home/rxkicksc/rxkicks.com/html/devicom_apps/sales/logs/';

//Account user needed directories for failed post tracker
$failedDirectory = '/chroot/home/rxkicksc/rxkicks.com/html/devicom_apps/sales/failed/';
$sentDirectory = '/chroot/home/rxkicksc/rxkicks.com/html/devicom_apps/sales/sent/';
$salesLogsDirectory = '/chroot/home/rxkicksc/rxkicks.com/html/devicom_apps/sales/logs/';

//Account user needed directories for sales log mover
$salesLogsDirectory = '/chroot/home/rxkicksc/rxkicks.com/html/devicom_apps/sales/logs/';
$requestLogsDirectory = '/chroot/home/rxkicksc/rxkicks.com/html/devicom_apps/sales/logs/request_logs/';
$transactionLogsDirectory = '/chroot/home/rxkicksc/rxkicks.com/html/devicom_apps/sales/logs/transaction_logs/';
$exceptionLogsDirectory = '/chroot/home/rxkicksc/rxkicks.com/html/devicom_apps/sales/logs/exception_logs/';

//Account user needed directories for order status udpate processor
$receivedDirectory = '/chroot/home/rxkicksc/rxkicks.com/html/devicom_apps/sales/received/';
$failedDirectory = '/chroot/home/rxkicksc/rxkicks.com/html/devicom_apps/sales/failed/';
$processedDirectory = '/chroot/home/rxkicksc/rxkicks.com/html/devicom_apps/sales/processed/';
$salesLogsDirectory = '/chroot/home/rxkicksc/rxkicks.com/html/devicom_apps/sales/logs/';

//Account user needed directories for post new order processor
$postDirectory = '/chroot/home/rxkicksc/rxkicks.com/html/devicom_apps/sales/post/';
$sentDirectory = '/chroot/home/rxkicksc/rxkicks.com/html/devicom_apps/sales/sent/';
$failedDirectory = '/chroot/home/rxkicksc/rxkicks.com/html/devicom_apps/sales/failed/';
$salesLogsDirectory = '/chroot/home/rxkicksc/rxkicks.com/html/devicom_apps/sales/logs/';

//Account user needed directories for reward validator processor
$salesLogsDirectory = '/chroot/home/rxkicksc/rxkicks.com/html/devicom_apps/sales/logs/';






?>
