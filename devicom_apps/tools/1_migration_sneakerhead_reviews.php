<?php

// NOTE: May wish to use addslashes
set_time_limit(0);//no timout
ini_set('memory_limit', '1024M');

$toolsLogsDirectory = '/chroot/home/rxkicksc/rxkicks.com/html/devicom_apps/tools/logs/';

// initialize magento environment for 'default' store
require_once '../../app/Mage.php';
Mage::app('admin'); // Default or your store view name.

//Open transaction log
$transactionLogHandle = fopen($toolsLogsDirectory . 'migration_sneakerhead_reviews_transaction_log', 'a+');
fwrite($transactionLogHandle, "->BEGIN PROCESSING...\n");
    
//REVIEWS
echo "GETTING REVIEWS\n";
$resource = Mage::getSingleton('core/resource');
$writeConnection = $resource->getConnection('core_write');
$query = "SELECT `pr`.`reviewId` AS `review_id`, `pr`.`productId`, `pr`.`dateSubmitted` AS `created_at`, IF(`pr`.`status` = 'NEW', 2, IF(`pr`.`status` = 'APPROVED', 1, IF(`pr`.`status` = 'DELETED', 3,0))) AS `status_id`, `pr`.`reviewTitle` AS `title`, `pr`.`reviewBody` AS `detail`, `pr`.`name` AS `nickname`, `pr`.`location`, `u`.`email`, `pr`.`rating` FROM `productReviews` AS `pr` LEFT JOIN `users` AS `u` ON `pr`.`uid` = `u`.`uid`";
$results = $writeConnection->query($query);
foreach ($results as $result) {
    
    //SELECT configurable url_key to get product id
    $productQuery = "SELECT `cpev`.`entity_id` AS `entity_id` FROM `catalog_product_entity_varchar` AS `cpev` INNER JOIN `catalog_product_entity` AS `cpe` ON `cpev`.`entity_id` = `cpe`.`entity_id` WHERE `cpe`.`type_id` = 'configurable' AND `store_id` = 0 AND `attribute_id` = 90 AND `value` = '" . $result['productId'] . "' AND `cpe`.`sku` NOT LIKE 'promo%' AND `value` NOT LIKE 'offline-product'";
    $productResults = $writeConnection->query($productQuery);

    $productFound = NULL;
    foreach ($productResults as $productResult) {    
	//Add review

	$testReviewFound = NULL;
	$testReviewQuery = "SELECT COUNT(*) AS `count` FROM `review` WHERE `review_id` = " . $result['review_id'];
	//echo $testReviewQuery . "\n";
	$testReviewResults = $writeConnection->query($testReviewQuery);
	foreach ($testReviewResults as $testReviewResult) {  
	    if (!$testReviewResult['count']) {
		echo $testReviewResult['count'] . "\n";

		$productFound = 1;
		$reviewQuery = "INSERT INTO `review` (`review_id`, `created_at`, `entity_id`, `entity_pk_value`, `status_id`) VALUES (" . $result['review_id'] . ", '" . $result['created_at'] . "', 1, " . $productResult['entity_id'] . ", " . $result['status_id'] . ")";
		$reviewResults = $writeConnection->query($reviewQuery);
		if (count($reviewResults) > 0) {
		    fwrite($transactionLogHandle, "  ->ADDED : Review                  : " . $result['review_id'] . "-> Product " . $productResult['entity_id'] . "\n");

		    if ($result['email'] == NULL) {
			$reviewDetailQuery = "INSERT INTO `review_detail` (`review_id`, `store_id`, `title`, `detail`, `nickname`, `customer_id`, `location`) VALUES (" . $result['review_id'] . ", 21, '" .  mysql_escape_string($result['title']) . "', '" .  mysql_escape_string($result['detail']) . "', '" .  mysql_escape_string($result['nickname']) . "', NULL, '" .  mysql_escape_string($result['location']) . "')";
			//echo $reviewDetailQuery . "\n";
			$reviewDetailResults = $writeConnection->query($reviewDetailQuery);
			fwrite($transactionLogHandle, "  ->ADDED : Review Detail - Guest   : " . $result['review_id'] . "-> Product " . $productResult['entity_id'] . "\n");

			//Add rating for guest
			$ratingQuery = "INSERT INTO `rating_option_vote` (`option_id`, `remote_ip`, `remote_ip_long`, `customer_id`, `entity_pk_value`, `rating_id`, `review_id`, `percent`, `value`) VALUES (" . $result['rating'] . ", '', 0, NULL, " . $productResult['entity_id'] . ", " . 1 . ", " . $result['review_id'] . ", " . 20 * $result['rating'] . ", " . $result['rating'] . ")";
			//echo $reviewDetailQuery . "\n";
			$ratingResults = $writeConnection->query($ratingQuery);
			fwrite($transactionLogHandle, "  ->ADDED : Rating - Guest          : " . $result['rating'] . "-> Product " . $productResult['entity_id'] . "\n");
		    } else {
			//SELECT customer if registered
			$customerQuery = "SELECT `entity_id` FROM `customer_entity` WHERE `email` = '" . $result['email'] . "' AND `store_id` = 21";
			//echo $customerQuery . "\n";
			$customerResults = $writeConnection->query($customerQuery);
			foreach ($customerResults as $customerResult) {  
			    //Add review detail
			    $reviewDetailQuery = "INSERT INTO `review_detail` (`review_id`, `store_id`, `title`, `detail`, `nickname`, `customer_id`, `location`) VALUES (" . $result['review_id'] . ", 21, '" .  mysql_escape_string($result['title']) . "', '" .  mysql_escape_string($result['detail']) . "', '" .  mysql_escape_string($result['nickname']) . "', " . $customerResult['entity_id'] . ", '" .  mysql_escape_string($result['location']) . "')";
			    //echo $reviewDetailQuery . "\n";
			    $reviewDetailResults = $writeConnection->query($reviewDetailQuery);
			    fwrite($transactionLogHandle, "  ->ADDED : Review Detail - Customer: " . $result['review_id'] . "-> Product " . $productResult['entity_id'] . "\n");

			    //Add rating for customer
			    $ratingQuery = "INSERT INTO `rating_option_vote` (`option_id`, `remote_ip`, `remote_ip_long`, `customer_id`, `entity_pk_value`, `rating_id`, `review_id`, `percent`, `value`) VALUES (" . $result['rating'] . ", '', 0, " . $customerResult['entity_id'] . ", " . $productResult['entity_id'] . ", " . 1 . ", " . $result['review_id'] . ", " . 20 * $result['rating'] . ", " . $result['rating'] . ")";
			    //echo $reviewDetailQuery . "\n";
			    $ratingResults = $writeConnection->query($ratingQuery);
			    fwrite($transactionLogHandle, "  ->ADDED : Rating - Customer       : " . $result['rating'] . "-> Product " . $productResult['entity_id'] . "\n");
			}
		    }

		    //Add stores
		    $reviewStoreQuery = "INSERT INTO `review_store` (`review_id`, `store_id`) VALUES (" . $result['review_id'] . ", 0), (" . $result['review_id'] . ", 21)";
		    $reviewStoreResults = $writeConnection->query($reviewStoreQuery);
		    fwrite($transactionLogHandle, "  ->ADDED : Review Store            : " . $result['review_id'] . "-> Product " . $productResult['entity_id'] . "\n");

		    //Get aggregate
		    //$ratingAggregationQuery = "SELECT `entity_pk_value`, COUNT(*) AS `vote_count`, SUM(`value`) AS `vote_value_sum`, round((SUM(`value`) * 20)/COUNT(*)) AS `percent` FROM `rating_option_vote` WHERE `entity_pk_value` = " . $productResult['entity_id'] . " GROUP BY `entity_pk_value`";
		    $ratingAggregationQuery = "SELECT `rov`.`entity_pk_value`, COUNT(*) AS `vote_count`, SUM(`rov`.`value`) AS `vote_value_sum`, round((SUM(`rov`.`value`) * 20)/COUNT(*)) AS `percent` FROM `rating_option_vote` AS `rov` INNER JOIN `review` AS `r` ON `rov`.`review_id` = `r`.`review_id` WHERE `rov`.`entity_pk_value` = " . $productResult['entity_id'] . " AND `r`.`status_id` = 1 GROUP BY `entity_pk_value`";
		    $ratingAggregationResults = $writeConnection->query($ratingAggregationQuery);
		    if (count($ratingAggregationResults) > 0) {
			//UPDATE
			foreach ($ratingAggregationResults as $ratingAggregationResult) { 
			    //Get aggregate approved
			    $ratingAggregationApprovedQuery = "SELECT round((SUM(`value`) * 20)/COUNT(*)) AS `percent_approved` FROM `rating_option_vote` INNER JOIN `review` ON `review`.`review_id` = `rating_option_vote`.`review_id` WHERE `review`.`status_id` = 1 AND `review`.`entity_pk_value` = " . $productResult['entity_id'];
			    $ratingAggregationApprovedResults = $writeConnection->query($ratingAggregationApprovedQuery);
			    foreach ($ratingAggregationApprovedResults as $ratingAggregationApprovedResult) { 
				if (is_null($ratingAggregationApprovedResult['percent_approved'])) {
	//			    if ($result['status_id'] == 1) {
	//				$percentApproved = $result['rating'] * 20;
	//			    } else {
					$percentApproved = 0;
	//			    }
				} else {
				    $percentApproved = $ratingAggregationApprovedResult['percent_approved'];
				}

				$aggregatedExistsQuery = "SELECT COUNT(*) AS `count` FROM `rating_option_vote_aggregated` WHERE `entity_pk_value` = " .$productResult['entity_id'];
				$aggregatedExistsResults = $writeConnection->query($aggregatedExistsQuery);
				//Insert/update aggregate
				foreach ($aggregatedExistsResults as $aggregatedExistsResult) { 
				    if ($aggregatedExistsResult['count'] > 0) {
					$ratingAggregatedQuery = "UPDATE `rating_option_vote_aggregated` SET `vote_count` = " . $ratingAggregationResult['vote_count'] . ",`vote_value_sum` = " . $ratingAggregationResult['vote_value_sum'] . ",`percent` = " . $ratingAggregationResult['percent'] . ",`percent_approved` = " . $percentApproved . " WHERE `entity_pk_value` = " . $productResult['entity_id'] . " AND `store_id` = 0";
					$ratingAggregatedResults = $writeConnection->query($ratingAggregatedQuery);
					$ratingAggregatedQuery = "UPDATE `rating_option_vote_aggregated` SET `vote_count` = " . $ratingAggregationResult['vote_count'] . ",`vote_value_sum` = " . $ratingAggregationResult['vote_value_sum'] . ",`percent` = " . $ratingAggregationResult['percent'] . ",`percent_approved` = " . $percentApproved . " WHERE `entity_pk_value` = " . $productResult['entity_id'] . " AND `store_id` = 21";
					$ratingAggregatedResults = $writeConnection->query($ratingAggregatedQuery);			    
					fwrite($transactionLogHandle, "  ->ADDED : UPDATE Rating Aggregated: " . $result['review_id'] . "-> Product " . $productResult['entity_id'] . "\n");

				    } else {
					$ratingAggregatedQuery = "INSERT INTO `rating_option_vote_aggregated` (`rating_id`, `entity_pk_value`, `vote_count`, `vote_value_sum`, `percent`, `percent_approved`, `store_id`) VALUES (1, " . $productResult['entity_id'] . ", 1, " . $result['rating'] . ", " . $result['rating'] * 20 . ", " . $percentApproved . ", 0)";
					$ratingAggregatedResults = $writeConnection->query($ratingAggregatedQuery);
					$ratingAggregatedQuery = "INSERT INTO `rating_option_vote_aggregated` (`rating_id`, `entity_pk_value`, `vote_count`, `vote_value_sum`, `percent`, `percent_approved`, `store_id`) VALUES (1, " . $productResult['entity_id'] . ", 1, " . $result['rating'] . ", " . $result['rating'] * 20 . ", " . $percentApproved . ", 21)";
					$ratingAggregatedResults = $writeConnection->query($ratingAggregatedQuery);
					fwrite($transactionLogHandle, "  ->ADDED : INSERT Rating Aggregated: " . $result['review_id'] . "-> Product " . $productResult['entity_id'] . "\n");
				    }
				}
				//Summary
				$summaryExistsQuery = "SELECT COUNT(*) AS `count` FROM `review_entity_summary` WHERE `entity_pk_value` = " .$productResult['entity_id'];
				$summaryExistsResults = $writeConnection->query($summaryExistsQuery);
				foreach ($summaryExistsResults as $summaryExistsResult) { 
				    if ($summaryExistsResult['count'] > 0) {
					$ratingSummaryQuery = "UPDATE `review_entity_summary` SET `reviews_count` = " . $ratingAggregationResult['vote_count'] . ",`rating_summary` = " . $percentApproved . " WHERE `entity_pk_value` = " . $productResult['entity_id'] . " AND `store_id` = 0";
					$ratingSummaryResults = $writeConnection->query($ratingSummaryQuery);
					$ratingSummaryQuery = "UPDATE `review_entity_summary` SET `reviews_count` = " . $ratingAggregationResult['vote_count'] . ",`rating_summary` = " . $percentApproved . " WHERE `entity_pk_value` = " . $productResult['entity_id'] . " AND `store_id` = 21";
					$ratingSummaryResults = $writeConnection->query($ratingSummaryQuery);
					fwrite($transactionLogHandle, "  ->ADDED : UPDATE Rating Summary   : " . $result['review_id'] . "-> Product " . $productResult['entity_id'] . "\n");
				    } else {
					if (is_null($ratingAggregationApprovedResult['percent_approved'])) {
					    $ratingSummaryQuery = "INSERT INTO `review_entity_summary` (`entity_pk_value`, `entity_type`, `reviews_count`, `rating_summary`, `store_id`) VALUES (" . $productResult['entity_id'] . ", 1, 0, 0, 21),(" . $productResult['entity_id'] . ", 1, 0, 0, 20),(" . $productResult['entity_id'] . ", 1, 0, 0, 1),(" . $productResult['entity_id'] . ", 1, 1, 100, 0)";
					    $ratingSummaryResults = $writeConnection->query($ratingSummaryQuery);
					} else {
					    $ratingSummaryQuery = "INSERT INTO `review_entity_summary` (`entity_pk_value`, `entity_type`, `reviews_count`, `rating_summary`, `store_id`) VALUES (" . $productResult['entity_id'] . ", 1, " . $ratingAggregationResult['vote_count'] . ", " . $percentApproved . ", 21),(" . $productResult['entity_id'] . ", 1, 0, 0, 20),(" . $productResult['entity_id'] . ", 1, 0, 0, 1),(" . $productResult['entity_id'] . ", 1, " . $ratingAggregationResult['vote_count'] . ", " . $percentApproved . ", 0)";
					    $ratingSummaryResults = $writeConnection->query($ratingSummaryQuery);
					}				
					fwrite($transactionLogHandle, "  ->ADDED : INSERT Rating Summary   : " . $result['review_id'] . "-> Product " . $productResult['entity_id'] . "\n");
				    }
				}
			    }
			}
		    }
		}
	    }
	}

    }
    if (!$productFound) {
	fwrite($transactionLogHandle, "  ->ADDED : PRODUCT NOT FOUND       : " . $result['review_id'] . "-> Product " . $result['productId'] . "\n");
    }
}

fwrite($transactionLogHandle, "->END PROCESSING\n");
//Close transaction log
fclose($transactionLogHandle);

?>
