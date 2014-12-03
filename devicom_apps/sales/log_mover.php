<?php

require(dirname(dirname(__FILE__)) . '/lib/Devicom/config.php');

$root_dir = dirname(__FILE__);
$salesLogsDirectory = $root_dir . '/logs/';
$requestLogsDirectory = $root_dir . '/logs/request_logs/';
$transactionLogsDirectory = $root_dir . '/logs/transaction_logs/';
$exceptionLogsDirectory = $root_dir . '/logs/exception_logs/';

$realTime = realTime();

if (file_exists($salesLogsDirectory . 'new_order_request_log')) {
    //Move request_log to request_logs directory
    $filename = 'new_order_request_log_' . $realTime[5] . $realTime[4] . $realTime[3] . '_' . $realTime[2] . $realTime[1] . $realTime[0];
    rename($salesLogsDirectory . 'new_order_request_log', $requestLogsDirectory . $filename);
}

if (file_exists($salesLogsDirectory . 'order_status_update_request_log')) {
    //Move request_log to request_logs directory
    $filename = 'order_status_update_request_log_' . $realTime[5] . $realTime[4] . $realTime[3] . '_' . $realTime[2] . $realTime[1] . $realTime[0];
    rename($salesLogsDirectory . 'order_status_update_request_log', $requestLogsDirectory . $filename);
}

if (file_exists($salesLogsDirectory . 'post_authorizenet_transaction_log')) {
    //Move request_log to request_logs directory
    $filename = 'post_authorizenet_transaction_log_' . $realTime[5] . $realTime[4] . $realTime[3] . '_' . $realTime[2] . $realTime[1] . $realTime[0];
    rename($salesLogsDirectory . 'post_authorizenet_transaction_log', $transactionLogsDirectory . $filename);
}

if (file_exists($salesLogsDirectory . 'new_order_transaction_log')) {
    //Move new_order_transaction_log to transaction_logs directory
    $filename = 'new_order_transaction_log_' . $realTime[5] . $realTime[4] . $realTime[3] . '_' . $realTime[2] . $realTime[1] . $realTime[0];
    rename($salesLogsDirectory . 'new_order_transaction_log', $transactionLogsDirectory . $filename);
}

if (file_exists($salesLogsDirectory . 'post_new_order_transaction_log')) {
    //Move new_order_transaction_log to transaction_logs directory
    $filename = 'post_new_order_transaction_log_' . $realTime[5] . $realTime[4] . $realTime[3] . '_' . $realTime[2] . $realTime[1] . $realTime[0];
    rename($salesLogsDirectory . 'post_new_order_transaction_log', $transactionLogsDirectory . $filename);
}

if (file_exists($salesLogsDirectory . 'failed_post_transaction_log')) {
    //Move new_order_transaction_log to transaction_logs directory
    $filename = 'failed_post_transaction_log_' . $realTime[5] . $realTime[4] . $realTime[3] . '_' . $realTime[2] . $realTime[1] . $realTime[0];
    rename($salesLogsDirectory . 'failed_post_transaction_log', $transactionLogsDirectory . $filename);
}

if (file_exists($salesLogsDirectory . 'order_status_update_transaction_log')) {
    //Move request_log to request_logs directory
    $filename = 'order_status_update_transaction_log_' . $realTime[5] . $realTime[4] . $realTime[3] . '_' . $realTime[2] . $realTime[1] . $realTime[0];
    rename($salesLogsDirectory . 'order_status_update_transaction_log', $transactionLogsDirectory . $filename);
}

if (file_exists($salesLogsDirectory . 'subscriber_transaction_log')) {
    //Move subscriber_transaction_log to transaction_logs directory
    $filename = 'subscriber_transaction_log_' . $realTime[5] . $realTime[4] . $realTime[3] . '_' . $realTime[2] . $realTime[1] . $realTime[0];
    rename($salesLogsDirectory . 'subscriber_transaction_log', $transactionLogsDirectory . $filename);
}

if (file_exists($salesLogsDirectory . 'reward_validator_transaction_log')) {
    //Move transaction_log to transaction_logs directory
    $filename = 'reward_validator_transaction_log_' . $realTime[5] . $realTime[4] . $realTime[3] . '_' . $realTime[2] . $realTime[1] . $realTime[0];
    rename($salesLogsDirectory . 'reward_validator_transaction_log', $transactionLogsDirectory . $filename);
}

if (file_exists($salesLogsDirectory . 'exception_log')) {
    //Move exception_log to exception_logs directory
    $filename = 'exception_log_' . $realTime[5] . $realTime[4] . $realTime[3] . '_' . $realTime[2] . $realTime[1] . $realTime[0];
    rename($salesLogsDirectory . 'exception_log', $exceptionLogsDirectory . $filename);
}

?>