<?php

require(dirname(dirname(__FILE__)) . '/lib/Devicom/config.php');

$customerLogsDirectory = dirname(__FILE__) . '/logs/';
$passwordLogsDirectory = $customerLogsDirectory . '/password_logs/';
$guestOrderConverterLogsDirectory = $customerLogsDirectory . '/guest_to_customer_order_converter_logs/';

$realTime = realTime();

if (file_exists($customerLogsDirectory . 'password_log')) {
    //Move password_log to customer logs directory
    $filename = 'password_log_' . $realTime[5] . $realTime[4] . $realTime[3] . '_' . $realTime[2] . $realTime[1] . $realTime[0];
    rename($customerLogsDirectory . 'password_log', $passwordLogsDirectory . $filename);
}

if (file_exists($customerLogsDirectory . 'guest_to_customer_order_converter_log')) {
    $filename = 'guest_to_customer_order_converter_log_' . $realTime[5] . $realTime[4] . $realTime[3] . '_' . $realTime[2] . $realTime[1] . $realTime[0];
    rename($customerLogsDirectory . 'guest_to_customer_order_converter_log', $guestOrderConverterLogsDirectory . $filename);
}

?>