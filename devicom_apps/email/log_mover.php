<?php

require(dirname(dirname(__FILE__)) . '/lib/Devicom/config.php');

$emailLogsDirectory = dirname(__FILE__) . '/logs/';
$guestInviteLogsDirectory = $emailLogsDirectory . '/guest_create_account_invite_logs/';
$productReviewLogsDirectory = $emailLogsDirectory . '/product_review_invite_logs/';
$storeTestimonialLogsDirectory = $emailLogsDirectory . '/store_testimonial_invite_logs/';

$realTime = realTime();

if (file_exists($emailLogsDirectory . 'guest_create_account_invite_log')) {
	$filename = 'guest_create_account_invite_log_' . $realTime[5] . $realTime[4] . $realTime[3] . '_' . $realTime[2] . $realTime[1] . $realTime[0];
	rename($emailLogsDirectory . 'guest_create_account_invite_log', $guestInviteLogsDirectory . $filename);
	echo $guestInviteLogsDirectory . $filename . '\n';
}

if (file_exists($emailLogsDirectory . 'product_review_invite_log')) {
	$filename = 'product_review_invite_log_' . $realTime[5] . $realTime[4] . $realTime[3] . '_' . $realTime[2] . $realTime[1] . $realTime[0];
	rename($emailLogsDirectory . 'product_review_invite_log', $productReviewLogsDirectory . $filename);
	echo $productReviewLogsDirectory . $filename . '\n';
}

if (file_exists($emailLogsDirectory . 'store_testimonial_invite_log')) {
	$filename = 'store_testimonial_invite_log_' . $realTime[5] . $realTime[4] . $realTime[3] . '_' . $realTime[2] . $realTime[1] . $realTime[0];
	rename($emailLogsDirectory . 'store_testimonial_invite_log', $storeTestimonialLogsDirectory . $filename);
	echo $storeTestimonialLogsDirectory . $filename . '\n';
}
?>