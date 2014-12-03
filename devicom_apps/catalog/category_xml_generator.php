<?php

//Version 1.0 - 3/7/2012

set_time_limit(0); //no timout

$core_dir = dirname(dirname(__FILE__));
$root_dir = dirname(__FILE__);
$catalogLogsDirectory = $root_dir . '/logs/';
$categoryInventoryDirectory = $core_dir . '/inventory/category/';
$newCategoryInventoryDirectory = $core_dir . '/inventory/new_category/';
$oldCategoryInventoryDirectory = $core_dir . '/inventory/old_category/';

////Look for "process*.lock" which is created by all other scripts. All other scripts look for "*.lock"
//if (rtrim(basename(shell_exec('ls ' . $catalogLogsDirectory . '/process*.lock | head --lines 1')))
//	|| rtrim(basename(shell_exec('ls ' . $catalogLogsDirectory . '/stage | head --lines 1')))
//	&& !rtrim(basename(shell_exec('ls ' . $catalogLogsDirectory . '/placeholder.lock | head --lines 1')))) {
//    //Create placeholder.lock to stop further processing of incremental and/or full updates
//    $placeholderLockFilename = 'placeholder.lock';
//    $placeholderLockHandle = fopen($catalogLogsDirectory . $placeholderLockFilename, 'w+');
//    fclose($placeholderLockHandle);
//} elseif (!rtrim(basename(shell_exec('ls ' . $catalogLogsDirectory . '/process*.lock | head --lines 1')))
//	&& !rtrim(basename(shell_exec('ls ' . $catalogLogsDirectory . '/stage | head --lines 1')))) {

if (!rtrim(basename(shell_exec('ls ' . $catalogLogsDirectory . 'xml_process* | head --lines 1')))
		&& !rtrim(basename(shell_exec('ls ' . $catalogLogsDirectory . 'stage | head --lines 1')))
) {

	require(dirname(dirname(__FILE__)) . '/lib/Devicom/config.php');

	// Initialize magento environment for 'admin' store
	require_once dirname(dirname(dirname(__FILE__))) . '/app/Mage.php';
	umask(0);
	Mage::app('admin'); //admin defines default value for all stores including the Main Website

	$realTime = realTime();

	//Open transaction log
	$transactionLogHandle = fopen($catalogLogsDirectory . 'category_xml_generator_log', 'a+');
	fwrite($transactionLogHandle, "->BEGIN PROCESSING:\n");
	fwrite($transactionLogHandle, "  ->START TIME    : " . $realTime[5] . '/' . $realTime[4] . '/' . $realTime[3] . ' ' . $realTime[2] . ':' . $realTime[1] . ':' . $realTime[0] . "\n");

	//Create process.lock to stop further processing of incremental and/or full updates
	$processLockFilename = 'xml_process_' . $realTime[5] . $realTime[4] . $realTime[3] . '_' . $realTime[2] . $realTime[1] . $realTime[0] . '.lock';
	$processLockHandle = fopen($catalogLogsDirectory . $processLockFilename, 'w+');
	fclose($processLockHandle);

//    //Remove category placeholder.lock after creating process.lock to allow next scheduled cron to run
//    $placeholderLockFilename = 'placeholder.lock';
//    if (file_exists($catalogLogsDirectory . $placeholderLockFilename)){
//	unlink($catalogLogsDirectory . $placeholderLockFilename);
//	fwrite($transactionLogHandle, "->LOCK REMOVED    : " . $placeholderLockFilename . "\n");
//    }

	fwrite($transactionLogHandle, "  ->LOCK CREATED  : " . $processLockFilename . "\n");

	try {
		// Generate category XML File
		$resource = Mage::getSingleton('core/resource');
		$readConnection = $resource->getConnection('core_read');

//	$nullCategoryQuery = "SELECT * FROM `devicom_inventory` WHERE `category_ids` IS NULL";
//	$nullCategories = $readConnection->fetchAll($nullCategoryQuery);
//
//	// Check for result
//	$addCategories = null;
//	foreach ($nullCategories as $nullCategory) {
//	    $addCategories = true;
//	}
//
//	if ($addCategories) {
//
//	    // Get resource
//	    $resource = Mage::getSingleton('core/resource');
//	    $writeConnection = $resource->getConnection('core_write');
//
//	    $query = "SELECT DISTINCT `parent_sku` FROM `devicom_inventory` WHERE `parent_sku` IS NOT NULL AND `category_ids` IS NULL";
//	    $products = $writeConnection->query($query);
//
//	    //for each sku, look up result set of categories
//	    fwrite($transactionLogHandle, "    ->ADDING CATS : Inventory\n");
//
//	    $timeStart = new DateTime(date('H:i:s', time()));
//	    $itemCounter = 0;
//	    foreach ($products as $product) {
//		if ($itemCounter %100 == 0){
//		    //Check how often???
//		    $timeEnd = new DateTime(date('H:i:s', time()));
//		    $elapsed = $timeStart->diff($timeEnd);
//		    fwrite($transactionLogHandle, "      ->ELAPSED   : " . $elapsed->format( '%H:%I:%S' ) . "\n");
//		}
//
//		$query = "SELECT `ccp`.`category_id`, `cpe`.`sku` FROM `catalog_category_product` AS `ccp` INNER JOIN `catalog_product_entity` AS `cpe` ON `ccp`.`product_id` = `cpe`.`entity_id` WHERE `cpe`.`sku` = '" . $product['parent_sku'] . "'";
//		$categories = $writeConnection->query($query);
//
//		$i = 1;
//		foreach ($categories as $category) {
//		    if($i == 1){
//			$catIds = $category['category_id'];
//		    } else {
//			$catIds .= ',' . $category['category_id'];
//		    }
//		    $i++;
//		}
//
//		$query = "UPDATE `devicom_inventory` AS `di` SET `di`.`category_ids` = '" . $catIds ."' WHERE `di`.`parent_sku` = '" . $product['parent_sku'] . "'";
//
//		$result = $writeConnection->query($query);
//		//}
//		$itemCounter++;
//	    }
//	    $timeEnd = new DateTime(date('H:i:s', time()));
//	    $elapsed = $timeStart->diff($timeEnd);
//	    fwrite($transactionLogHandle, "      ->ELAPSED   : " . $elapsed->format( '%H:%I:%S' ) . "\n");
//	    fwrite($transactionLogHandle, "    ->CATS ADDED  : Inventory\n");
//	}

		$productQuery = "SELECT * FROM `devicom_inventory` WHERE `parent_sku` IS NOT NULL ORDER BY `sort_order` ASC";

		$products = $readConnection->fetchAll($productQuery);

		// Build array from result set
		foreach ($products as $product) {
			$productCategories = explode(',', $product['category_ids']);
			if (count($productCategories) > 1) {
				foreach ($productCategories as $productCategory) {
					if (is_null($product['parent_product_id']) || $product['qty'] <= '0') {
						$productsArray[$productCategory][$product['parent_sku']][$product['sku']] = null;
					} else {
						if ($product['label'] == 'One Size') {
							$productsArray[$productCategory][$product['parent_sku']][$product['sku']] = $product['label'];
						} else {
							$productsArray[$productCategory][$product['parent_sku']][$product['sku']] = end(explode('-', $product['sku']));
						}
					}
				}
			} else {
				if (is_null($product['parent_product_id']) || $product['qty'] <= '0') {
					$productsArray[$productCategory][$product['parent_sku']][$product['sku']] = null;
				} else {
					if ($product['label'] == 'One Size') {
						$productsArray[$productCategory][$product['parent_sku']][$product['sku']] = $product['label'];
					} else {
						$productsArray[$productCategory][$product['parent_sku']][$product['sku']] = end(explode('-', $product['sku']));
					}
				}
			}
		}

		fwrite($transactionLogHandle, "    ->BUILT ARRAY : Inventory\n");

		// Create temporary directory
		if (!mkdir($newCategoryInventoryDirectory, 0777, true)) {
			//die('Failed to create folders...');
		}
		fwrite($transactionLogHandle, "    ->CREATED DIR : Inventory\n");

		// Copy .htaccess
		$file = $categoryInventoryDirectory . '.htaccess';
		$newfile = $newCategoryInventoryDirectory . '.htaccess';

		if (!copy($file, $newfile)) {
			//Should be exception but will stop processing?
		}
		fwrite($transactionLogHandle, "    ->ADD HTACCESS: Inventory\n");

		fwrite($transactionLogHandle, "    ->BUILD FILES : Inventory\n");

		foreach ($productsArray as $categoryId => $productSet) {

			//Creates XML string and XML document from the DOM representation
			$itemFilename = 'category-' . $categoryId . '.xml';

			$dom = new DomDocument('1.0');

			$productsTag = $dom->appendChild($dom->createElement('Products'));

			foreach ($productSet as $productSku => $productSizes) {

				$productTag = $productsTag->appendChild($dom->createElement('Product'));

				$skuTag = $productTag->appendChild($dom->createElement('Sku'));
				$skuTag->appendChild($dom->createTextNode($productSku));

				$sizes = "";
				$quantityFound = null;
				foreach ($productSizes as $productSize) {
					if (!is_null($productSize)) {
						$sizes .= " " . $productSize;
						$quantityFound = 1;
					}
				}

				$sizeTag = $productTag->appendChild($dom->createElement('Sizes'));
				if (is_null($quantityFound)) {
					$sizeTag->appendChild($dom->createTextNode('Sold Out'));
				} else {
					if ($sizes == ' One Size') {
						$sizeTag->appendChild($dom->createTextNode('One Size'));
					} else {
						$sizeTag->appendChild($dom->createTextNode('Size' . $sizes));
					}
				}
			}

			// Make the output pretty
			$dom->formatOutput = true;

			// Save the XML string
			$xml = $dom->saveXML();

			//Write file to inventory directory
			$inventoryHandle = fopen($newCategoryInventoryDirectory . $itemFilename, 'w');
			fwrite($inventoryHandle, $xml);
			fclose($inventoryHandle);
			fwrite($transactionLogHandle, "    ->CREATED XML : category-" . $categoryId . ".xml\n");
		}

		//Build test category file

		$dom = new DomDocument('1.0');

		$testTag = $dom->appendChild($dom->createElement('Test'));
		$testTag->appendChild($dom->createTextNode('Test'));

		// Make the output pretty
		$dom->formatOutput = true;

		// Save the XML string
		$xml = $dom->saveXML();
		$inventoryHandle = fopen($newCategoryInventoryDirectory . 'category-test.xml', 'w');
		fwrite($inventoryHandle, $xml);
		fclose($inventoryHandle);
		fwrite($transactionLogHandle, "      ->CREATE XML: category-test.xml\n");

		fwrite($transactionLogHandle, "    ->FILES BUILT : Inventory\n");

		//Rename active inventory directory to old_directory
		rename($categoryInventoryDirectory, $oldCategoryInventoryDirectory);
		//Rename new inventory directory to make active and restore symlink
		rename($newCategoryInventoryDirectory, $categoryInventoryDirectory);
		fwrite($transactionLogHandle, "    ->RENAMED DIRS: Inventory\n");

		//Remove old inventory XML files
		$handle = opendir($oldCategoryInventoryDirectory);

		while (($file = readdir($handle)) !== false) {
			@unlink($oldCategoryInventoryDirectory . '/' . $file);
		}
		fwrite($transactionLogHandle, "    ->REMOVE FILES: Inventory\n");
		closedir($handle);

		//Remove old inventory directory
		if (!rmdir($oldCategoryInventoryDirectory)) {
			//die('Failed to create folders...');
		}
		fwrite($transactionLogHandle, "    ->REMOVED DIR : Inventory\n");
	} catch (Exception $e) {

		fwrite($transactionLogHandle, "  ->ERROR         : See exception_log\n");

		//Append error to exception log file
		$exceptionLogHandle = fopen($catalogLogsDirectory . 'exception_log', 'a');
		//fwrite($exceptionLogHandle, '->' . $filename . " - " . $e->getMessage() . "\n");
		fclose($exceptionLogHandle);

		//Move XML file to failed directory
		//rename($receivedDirectory . $filename, $failedDirectory . $filename);
	}

	//Remove xml_process to allow processing of incremental and/or full updates
	if (file_exists($catalogLogsDirectory . $processLockFilename)) {
		unlink($catalogLogsDirectory . $processLockFilename);
		fwrite($transactionLogHandle, "  ->LOCK REMOVED  : " . $processLockFilename . "\n");
	}

	$endTime = realTime();
	fwrite($transactionLogHandle, "  ->FINISH TIME   : " . $endTime[5] . '/' . $endTime[4] . '/' . $endTime[3] . ' ' . $endTime[2] . ':' . $endTime[1] . ':' . $endTime[0] . "\n");
	fwrite($transactionLogHandle, "->END PROCESSING  :\n");

	//Close transaction log
	fclose($transactionLogHandle);
}
?>
