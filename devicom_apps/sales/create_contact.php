<?php

require(dirname(dirname(__FILE__)) . '/lib/Devicom/config.php');

$core_dir = dirname(dirname(dirname(dirname(__FILE__))));
$root_dir = dirname(__FILE__);
$subscribersDirectory = $root_dir . '/subscribers/';
$failedDirectory = $root_dir . '/failed/';
$sentDirectory = $root_dir . '/sent/';
$salesLogsDirectory = $root_dir . '/logs/';

$filename = rtrim(basename(shell_exec('ls -t -r ' . $subscribersDirectory . 'subscriber_* | head --lines 1')));

if ($filename) {

	$realTime = realTime();

	//Open transaction log
	$transactionLogHandle = fopen($salesLogsDirectory . 'subscriber_transaction_log', 'a+');
	fwrite($transactionLogHandle, "->BEGIN PROCESSING: " . $filename . "\n");
	fwrite($transactionLogHandle, "  ->START TIME    : " . $realTime[5] . '/' . $realTime[4] . '/' . $realTime[3] . ' ' . $realTime[2] . ':' . $realTime[1] . ':' . $realTime[0] . "\n");

	try {

		require_once $core_dir . '/html/devicom_apps/lib/ConstantContact/class.cc.php';

		umask(0);

		// Set your Constant Contact account username and password below
		$cc = new cc(CREATE_CONTACT_CC_USERNAME, CREATE_CONTACT_CC_PASSWORD);

		//Get contents of XML file
		$contents = file_get_contents($subscribersDirectory . $filename);

		//Create new XML object
		$rootXmlElement = new SimpleXMLElement($contents);

		$storeId = $rootXmlElement->StoreId;
		$customerId = $rootXmlElement->CustomerId;
		$email = (string) $rootXmlElement->Email;
		$firstName = (string) $rootXmlElement->FirstName;
		$lastName = (string) $rootXmlElement->LastName;
		$extra_fields = array(
			'FirstName' => $firstName,
			'LastName' => $lastName,
		);
		$newsList = (int) $rootXmlElement->NewsList;
		$reviewsList = (int) $rootXmlElement->ReviewsList;

		// Make $lists an array
		$lists = array();
		if ($newsList == '1' && $cc->get_list(CREATE_CONTACT_LIST_NEWSLETTER)) {
			$lists[] = CREATE_CONTACT_LIST_NEWSLETTER;
		}
		if ($reviewsList == '1' && $cc->get_list(CREATE_CONTACT_LIST_REVIEWS)) {
			$lists[] = CREATE_CONTACT_LIST_REVIEWS;
		}

		// Amount of points to award customer (0)
		$pointsEarned = CREATE_CONTACT_POINTS_EARNED;

		// check if the contact exists
		$contact = $cc->query_contacts($email);

		if (count($lists)) {
			if ($contact) {
				// update the contact
				fwrite($transactionLogHandle, "  ->UPDATING      : " . $email . "\n");

				$status = $cc->update_contact($contact['id'], $email, $lists, $extra_fields);

				if ($status) {
					fwrite($transactionLogHandle, "  ->UPDATED       : " . $email . "\n");
				} else {
					throw new Exception($cc->http_get_response_code_error($cc->http_response_code));
				}
			} else {
				// create the contact
				fwrite($transactionLogHandle, "  ->CREATING      : " . $email . "\n");
				$new_id = $cc->create_contact($email, $lists, $extra_fields);

				if ($new_id) {
					fwrite($transactionLogHandle, "  ->CREATED       : " . $email . "\n");
					/*
					  if ($pointsEarned > 0 && $newsList == '1') {
					  // Get resource
					  $resource = Mage::getSingleton('core/resource');
					  $writeConnection = $resource->getConnection('core_write');

					  // Add point for subscribing to newsletter
					  $query = "INSERT INTO `rewardpoints_account` (`customer_id`, `store_id`, `order_id`, `points_current`, `points_spent`, `date_start`, `date_end`, `convertion_rate`, `rewardpoints_referral_id`, `quote_id`) VALUES ('" . $customerId . "', '" . $storeId . "', '-200', '" . $pointsEarned . "', 0, NULL, NULL, NULL, NULL, NULL)";
					  $writeConnection->query($query);

					  fwrite($transactionLogHandle, "  ->ADDED POINTS  : " . $email . "\n");
					  }
					 */
				} else {
					throw new Exception($cc->http_get_response_code_error($cc->http_response_code));
				}
			}
		}

		//Move XML file to processed directory
		rename($subscribersDirectory . $filename, $sentDirectory . $filename);

		if (DEBUG_MODE) {
			print "REVIEW MEMBERS:\n";
			print_r($cc->get_list_members(CREATE_CONTACT_LIST_REVIEWS));
			print "NEWS MEMBERS:\n";
			print_r($cc->get_list_members(CREATE_CONTACT_LIST_NEWSLETTER));
		}

	} catch (Exception $e) {

		fwrite($transactionLogHandle, "  ->ERROR         : See exception_log\n");

		//Append error to exception log file
		$exceptionLogHandle = fopen($salesLogsDirectory . 'exception_log', 'a');
		fwrite($exceptionLogHandle, '->' . $filename . " - " . $e->getMessage() . "\n");
		fclose($exceptionLogHandle);

		//Move XML file to failed directory
		rename($subscribersDirectory . $filename, $failedDirectory . $filename);
	}

	$endTime = realTime();
	fwrite($transactionLogHandle, "  ->FINISH TIME   : " . $endTime[5] . '/' . $endTime[4] . '/' . $endTime[3] . ' ' . $endTime[2] . ':' . $endTime[1] . ':' . $endTime[0] . "\n");
	fwrite($transactionLogHandle, "->END PROCESSING  : " . $filename . "\n");

	//Close transaction log
	fclose($transactionLogHandle);
}

?>