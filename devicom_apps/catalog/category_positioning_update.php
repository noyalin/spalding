<?php

//Version 1.0 - 3/7/2012
//NOTES:
//LANDING PAGES
//Customer Service
//Authorship
//Why Sneakerhead.com
//Sneakerhead.com Promotion
//F.A.Q.
//Track Your Order
//Ordering
//Payment Options
//Shipping Information
//Sneakerhead.com Return Policy
//Contact Us
//Email Newsletter Subscription
//Sneakerhead.com Gift Card
//Sneakerhead.com Rewards Program
//Sneaker Care
//Sneaker Terms
//Privacy Policy
//Terms of Use
//International Service
//International Faq's
//International Payments
//International Shipping
//International Size Help
//ALLOCATED CATEGORY IDS
// 171 - 374 Category CMS pages
// 5 - New Arrivals
// 6 - Mens
// 9 - Womens
// 10 -Kids
// 36 - Rewards
// 124 - Gear
// 150 - Outlet
// 375 - Sneakerfolio

require(dirname(dirname(__FILE__)) . '/lib/Devicom/config.php');

set_time_limit(0); //no timout
ini_set('memory_limit', '1024M');

$root_dir = dirname(__FILE__);
$catalogLogsDirectory = $root_dir . '/logs/';
$receivedDirectory = $root_dir . '/received/';
$failedDirectory = $root_dir . '/failed/';
$processedDirectory = $root_dir . '/processed/';

//Check that no lock exists (indicating file is being written or processed by another script) and, if any, grab the oldest incremental_category_update to begin processing
if (!rtrim(basename(shell_exec('ls ' . $catalogLogsDirectory . '*.lock | head --lines 1')))
		&& !rtrim(basename(shell_exec('ls ' . $catalogLogsDirectory . 'stage | head --lines 1')))
		&& !rtrim(basename(shell_exec('ls ' . $receivedDirectory . 'incremental_product_update* | head --lines 1')))
		&& !rtrim(basename(shell_exec('ls ' . $receivedDirectory . 'full_inventory_update* | head --lines 1')))
		&& !rtrim(basename(shell_exec('ls ' . $receivedDirectory . 'category_update* | head --lines 1')))
		&& $filename = rtrim(basename(shell_exec('ls -t -r ' . $receivedDirectory . '* | head --lines 1')))
) {

	if (substr($filename, 0, 27) != 'category_positioning_update') {
		exit;
	}

	$realTime = realTime();

	//Open transaction log
	$transactionLogHandle = fopen($catalogLogsDirectory . 'transaction_log', 'a+');
	fwrite($transactionLogHandle, "->BEGIN PROCESSING: " . $filename . "\n");
	fwrite($transactionLogHandle, "  ->START TIME    : " . $realTime[5] . '/' . $realTime[4] . '/' . $realTime[3] . ' ' . $realTime[2] . ':' . $realTime[1] . ':' . $realTime[0] . "\n");

	//Create process.lock to prevent another catalog process from starting
	$processLockFilename = 'process_' . $realTime[5] . $realTime[4] . $realTime[3] . '_' . $realTime[2] . $realTime[1] . $realTime[0] . '.lock';
	$processLockHandle = fopen($catalogLogsDirectory . $processLockFilename, 'w+');
	fclose($processLockHandle);

	fwrite($transactionLogHandle, "  ->LOCK CREATED  : " . $processLockFilename . "\n");

	try {

		fwrite($transactionLogHandle, "  ->LOADING XML...\n");

		//Get contents of XML file
		$contents = file_get_contents($receivedDirectory . $filename);

		//Create new XML object
		$rootXmlElement = new SimpleXMLElement($contents);

		foreach ($rootXmlElement->children() as $child) {
			$entityTypes[$child->getName()] = $child->getName();
		}

		// Initialize magento environment for 'admin' store
		require_once dirname(dirname(dirname(__FILE__))) . '/app/Mage.php';
		umask(0);
		Mage::app('admin'); //admin defines default value for all stores including the Main Website
		//Load adminhtml area of config.xml to initialize adminhtml observers -- required to trigger pagecache
		Mage::app()->loadArea('adminhtml');

		//Process categories if entity is present
		if (isset($entityTypes['Categories'])) {

			fwrite($transactionLogHandle, "  ->BUILDING ARRAY...\n");

			//Step into entities array to access configurable products
			foreach ($rootXmlElement->Categories as $entities) {

				//Loop through categories
				foreach ($entities->Category as $entity) {
					$categoryId = (string) $entity->Id;
					$categories[$categoryId]['category_id'] = $categoryId;
					$categories[$categoryId]['product_codes'] = (string) $entity->ProductCodes;
				}
			}
		}

		fwrite($transactionLogHandle, "  ->CHECKING CATEGORIES...\n");

		//Check if all categories exist else bail
		foreach ($categories as $categoryArray) {
			//get a new category object
			$category = Mage::getModel('catalog/category')->load($categoryArray['category_id']);
			if (!$category->getId()) {
				fwrite($transactionLogHandle, "  ->CATEGORY NOT FOUND: " . $categoryArray['category_id'] . "\n");

				//Remove process.lock to allow processing of incremental and/or full updates
				if (file_exists($catalogLogsDirectory . $processLockFilename)) {
					unlink($catalogLogsDirectory . $processLockFilename);
					fwrite($transactionLogHandle, "  ->LOCK REMOVED  : " . $processLockFilename . "\n");
				}

				$endTime = realTime();
				fwrite($transactionLogHandle, "  ->FINISH TIME   : " . $endTime[5] . '/' . $endTime[4] . '/' . $endTime[3] . ' ' . $endTime[2] . ':' . $endTime[1] . ':' . $endTime[0] . "\n");
				fwrite($transactionLogHandle, "->END PROCESSING  : " . $filename . "\n");

				//Close transaction log
				fclose($transactionLogHandle);
				exit;
			}
		}

		fwrite($transactionLogHandle, "  ->ACQUIRING RESOURCE CONNECTION...\n");

		// Get resource
		$resource = Mage::getSingleton('core/resource');
		$readConnection = $resource->getConnection('core_read');
		$writeConnection = $resource->getConnection('core_write');

		fwrite($transactionLogHandle, "  ->CHECKING PRODUCTS...\n");
//
//	//Test for products
//	foreach ($categories as $categoryArray) {
//	    $productCodesArray = explode(',', $categoryArray['product_codes']);
//	    $count = count($productCodesArray);
//	    $i = 0;
//	    while ($i < $count) {
//		if (!$product = Mage::getModel('catalog/product')->loadByAttribute('sku', $productCodesArray[$i])) {
//		    fwrite($transactionLogHandle, "  ->PRODUCT NOT FOUND : " . $productCodesArray[$i] . "\n");
//
//		    //Remove process.lock to allow processing of incremental and/or full updates
//		    if (file_exists($catalogLogsDirectory . $processLockFilename)){
//			unlink($catalogLogsDirectory . $processLockFilename);
//			fwrite($transactionLogHandle, "  ->LOCK REMOVED  : " . $processLockFilename . "\n");
//		    }
//
//		    $endTime = realTime();
//		    fwrite($transactionLogHandle, "  ->FINISH TIME   : " . $endTime[5] . '/' . $endTime[4] . '/' . $endTime[3] . ' ' . $endTime[2] . ':' . $endTime[1] . ':' . $endTime[0] . "\n");
//		    fwrite($transactionLogHandle, "->END PROCESSING  : " . $filename . "\n");
//
//		    //Close transaction log
//		    fclose($transactionLogHandle);
//		    exit;
//		} else {
////fwrite($transactionLogHandle, "  ->PRODUCT FOUND : " . $productCodesArray[$i] . "\n");
//		}
//		$i++;
//	    }
//	}
		//TEST FOR PRODUCTS ONLY FOR HOME PAGE AND WHAT'S HOT BECAUSE THEY HAVE TO EXIST UNLIKE FOR OTHER CATEGORIES
		foreach ($categories as $categoryArray) {
			$productCodesArray = explode(',', $categoryArray['product_codes']);
			$count = count($productCodesArray);

			//Check for correct count on home page and what's hot categories
			if (($categoryArray['category_id'] == 573 && $count == 12) || ($categoryArray['category_id'] == 543 && $count == 15) || ($categoryArray['category_id'] == 544 && $count == 15) || ($categoryArray['category_id'] == 545 && $count == 15) || ($categoryArray['category_id'] == 2764 && $count == 16)) {
				$i = 0;
				while ($i < $count) {
					if ($productCodesArray[$i]) {
						$testProductQuery = "SELECT COUNT(*) AS `count` FROM `catalog_product_entity` AS `cpe` WHERE `cpe`.`sku` = '" . $productCodesArray[$i] . "'";
						$testProductResults = $readConnection->query($testProductQuery);
						foreach ($testProductResults as $testProductResult) {
							if (!$testProductResult['count']) {
								fwrite($transactionLogHandle, "  ->PRODUCT NOT FOUND : " . $productCodesArray[$i] . "\n");

								//Remove process.lock to allow processing of incremental and/or full updates
								if (file_exists($catalogLogsDirectory . $processLockFilename)) {
									unlink($catalogLogsDirectory . $processLockFilename);
									fwrite($transactionLogHandle, "  ->LOCK REMOVED  : " . $processLockFilename . "\n");
								}

								$endTime = realTime();
								fwrite($transactionLogHandle, "  ->FINISH TIME   : " . $endTime[5] . '/' . $endTime[4] . '/' . $endTime[3] . ' ' . $endTime[2] . ':' . $endTime[1] . ':' . $endTime[0] . "\n");
								fwrite($transactionLogHandle, "->END PROCESSING  : " . $filename . "\n");

								//Close transaction log
								fclose($transactionLogHandle);
								exit;
							}
						}
					}
					$i++;
				}
			}
		}

		fwrite($transactionLogHandle, "  ->UPDATING POSITIONING...\n");

		//Update Positioning if processing did not get stopped due to missing products for home or what's hot categories
		foreach ($categories as $categoryArray) {
			$productCodesArray = explode(',', $categoryArray['product_codes']);
			$count = count($productCodesArray);
			$reindex = NULL;
			$finalString = '';

			//Update home page or what's hot
			if (($categoryArray['category_id'] == 573 && $count == 12) || ($categoryArray['category_id'] == 543 && $count == 15) || ($categoryArray['category_id'] == 544 && $count == 15) || ($categoryArray['category_id'] == 545 && $count == 15) || ($categoryArray['category_id'] == 2764 && $count == 16)) {
				$i = 0;
				//Delete existing mappings only for special categories
				$query = "DELETE FROM `catalog_category_product` WHERE `category_id` = " . $categoryArray['category_id'];
				$writeConnection->query($query);
				while ($i < $count) {
					if ($productCodesArray[$i]) {
						//Straight insert since products were already tested for existence
						$query = "INSERT INTO `catalog_category_product` (`category_id`, `product_id`, `position`) VALUES (" . $categoryArray['category_id'] . ", (SELECT `cpe`.`entity_id` FROM `catalog_product_entity` AS `cpe` WHERE `cpe`.`sku` = '" . $productCodesArray[$i] . "'), " . $i . ")";
						$writeConnection->query($query);
					}
					$i++;
				}
				$reindex = 1;
				fwrite($transactionLogHandle, "    ->UPDATED               ->" . $categoryArray['category_id'] . "\n");
			} elseif ($categoryArray['category_id'] != 573 && $categoryArray['category_id'] != 543 && $categoryArray['category_id'] != 544 && $categoryArray['category_id'] != 545 && $categoryArray['category_id'] != 2764) {

				//Find mapping not in new list
				$productCodes = str_replace(",", "','", $categoryArray['product_codes']);
				$finalString .= "'" . $productCodes . "'";
				$deleteCategoryProductQuery = "DELETE `ccp` FROM `catalog_category_product` AS `ccp` LEFT JOIN `catalog_product_entity` AS `cpe` ON `ccp`.`product_id` = `cpe`.`entity_id` where category_id = " . $categoryArray['category_id'] . " AND `cpe`.`sku` NOT IN (" . $finalString . ")";
				$writeConnection->query($deleteCategoryProductQuery);

				$i = 0;
				while ($i < $count) {
					if ($productCodesArray[$i]) {
						$testProductQuery = "SELECT COUNT(*) AS `count` FROM `catalog_product_entity` AS `cpe` WHERE `cpe`.`sku` = '" . $productCodesArray[$i] . "'";
						$testProductResults = $readConnection->query($testProductQuery);
						foreach ($testProductResults as $testProductResult) {
							if ($testProductResult['count']) {
								$query = "REPLACE INTO `catalog_category_product` (`category_id`, `product_id`, `position`) VALUES (" . $categoryArray['category_id'] . ", (SELECT `cpe`.`entity_id` FROM `catalog_product_entity` AS `cpe` WHERE `cpe`.`sku` = '" . $productCodesArray[$i] . "'), " . $i . ")";
								$writeConnection->query($query);
							} else {
								fwrite($transactionLogHandle, "    ->PRODUCT NOT FOUND     ->" . $categoryArray['category_id'] . "\n");
							}
						}
					}
					$i++;
				}
				$reindex = 1;
				fwrite($transactionLogHandle, "    ->UPDATED               ->" . $categoryArray['category_id'] . "\n");
			} elseif (($categoryArray['category_id'] == 573 && $count != 12) || ($categoryArray['category_id'] == 543 && $count != 15) || ($categoryArray['category_id'] == 544 && $count != 15) || ($categoryArray['category_id'] == 545 && $count != 15) || ($categoryArray['category_id'] == 2764 && $count != 16)) {
				fwrite($transactionLogHandle, "    ->WRONG COUNT           ->" . $categoryArray['category_id'] . "\n");
			} else {
				fwrite($transactionLogHandle, "    ->ERROR       : UNKNOWN\n");

				$message = "Category Positiong: " . $filename . "\r\n\r\nCategory " . $categoryArray['category_id'] . " could not be matched for updating. I have no idea what this would indicate. That is why I am making a notiifcation.";
				sendNotification($subject = 'Criteria Not Matched - Not Updated', $message);
			}
		}
		fwrite($transactionLogHandle, "  ->POSITIONING UPDATED...\n");

		if ($reindex) {
			//Run reindexing
			fwrite($transactionLogHandle, "  ->INDEXING      : " . $filename . "\n");
			try {
				$processIds = array(6); // Category_products

				foreach ($processIds as $processId) {
					fwrite($transactionLogHandle, "    ->START       : " . $processId . "\n");
					$process = Mage::getModel('index/process')->load($processId);
					$process->reindexAll();
					fwrite($transactionLogHandle, "    ->END         : " . $processId . "\n");
				}

				fwrite($transactionLogHandle, "  ->COMPLETED     : " . $filename . "\n");

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
			} catch (Exception $e) {
				fwrite($transactionLogHandle, "  ->NOT COMPLETED : " . $e . "\n");
			}
		}

//	//Refreshing cache
//	fwrite($transactionLogHandle, "  ->REFRESHING    : " . $filename . "\n");
//
//	try {
//	    fwrite($transactionLogHandle, "    ->START       : Full Page Cache\n");
//	    Mage::app()->getCacheInstance()->cleanType('full_page');
//	    Enterprise_PageCache_Model_Cache::getCacheInstance()->cleanType('full_page');
//	    fwrite($transactionLogHandle, "    ->END         : Full Page Cache\n");
//
//	    fwrite($transactionLogHandle, "  ->COMPLETED     : " . $filename . "\n");
//	} catch (Exception $e) {
//	    fwrite($transactionLogHandle, "  ->NOT COMPLETED : " . $e . "\n");
//	}
		//Move XML file to processed directory and make copy for ftp directory
		rename($receivedDirectory . $filename, $processedDirectory . $filename);
	} catch (Exception $e) {

		fwrite($transactionLogHandle, "  ->ERROR         : See exception_log\n");

		//Append error to exception log file
		$exceptionLogHandle = fopen($catalogLogsDirectory . 'exception_log', 'a');
		fwrite($exceptionLogHandle, '->' . $filename . " - " . $e->getMessage() . "\n");
		fclose($exceptionLogHandle);

		//Move XML file to failed directory
		rename($receivedDirectory . $filename, $failedDirectory . $filename);
	}

	//Remove process.lock to allow processing of incremental and/or full updates
	if (file_exists($catalogLogsDirectory . $processLockFilename)) {
		unlink($catalogLogsDirectory . $processLockFilename);
		fwrite($transactionLogHandle, "  ->LOCK REMOVED  : " . $processLockFilename . "\n");
	}

	$endTime = realTime();
	fwrite($transactionLogHandle, "  ->FINISH TIME   : " . $endTime[5] . '/' . $endTime[4] . '/' . $endTime[3] . ' ' . $endTime[2] . ':' . $endTime[1] . ':' . $endTime[0] . "\n");
	fwrite($transactionLogHandle, "->END PROCESSING  : " . $filename . "\n");

	//Close transaction log
	fclose($transactionLogHandle);
}
?>