<?php

//Version 1.0 - 3/7/2012
//NOTES:
//1. Verify children count is correct for root, default and all once receiving all categories
//2. Have to manually assign static blocks for landing pages
//3. Positioning (product codes) -- may be NULL
//4. Canonical -- maybe NULL
//1. Lester CANNOT EVER SEND AN ID THAT WAS NEVER USED IN THE PAST
//2. Lester to set any year category (not brand) needs to have include in menu set to NO
//3. Revisit whether the ability to move categories should be provided -- this effects what Lester needs to send if a parent_id changes for a given category
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
// 200 - Customer Service
// 300 - Blog/Brands
// 375 - Sneakerfolio

set_time_limit(0); //no timout
ini_set('memory_limit', '1024M');

require(dirname(dirname(__FILE__)) . '/lib/Devicom/config.php');

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
		&& $filename = rtrim(basename(shell_exec('ls -t -r ' . $receivedDirectory . 'category_update* | head --lines 1')))
) {

//    if (substr($filename, 0, 15) != 'category_update') {
//	exit;
//    }

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

					if (in_array($categoryId, $categoryListToNotProcess)) {

						fwrite($transactionLogHandle, "    ->SKIPPING CATEGORY " . $categoryId . "...\n");

						$message = "Category Update: " . $filename . "\r\n\r\nA category update for a landing page was received and was skipped to force a manual changed.";
						sendNotification($subject = 'Category Is Landing Page - Not Updated', $message);
						continue;
					}

					$categories[$categoryId]['category_id'] = $categoryId;
					$categories[$categoryId]['parent_id'] = (string) $entity->ParentId;
					$categories[$categoryId]['path'] = (string) $entity->CategoryPath;
					$categories[$categoryId]['position'] = (string) $entity->DisplayOrder;
					$categories[$categoryId]['name'] = (string) $entity->Name;
					$categories[$categoryId]['sneakerhead_only'] = $entity->IsSneakerheadOnly;
					$categories[$categoryId]['is_active'] = $entity->IsPublished;
					$categories[$categoryId]['url_key'] = (string) $entity->UrlKey;
					$categories[$categoryId]['highlighted_product'] = (string) $entity->HighlightedProductCode;
					$categories[$categoryId]['heading'] = (string) $entity->HeaderTitle;
					$categories[$categoryId]['meta_title'] = (string) $entity->SEO_Title;
					$categories[$categoryId]['meta_description'] = (string) $entity->SEO_Description;
					$categories[$categoryId]['meta_keywords'] = (string) $entity->SEO_Keywords;
					$categories[$categoryId]['canonical'] = (string) $entity->SEO_Canonical;
					$categories[$categoryId]['include_in_menu'] = $entity->IsVisibleOnMenu;
					$categories[$categoryId]['is_anchor'] = $entity->IsEnableFilter;
					$categoryPath = explode('/', (string) $entity->CategoryPath);
					$counter = 0;
					foreach ($categoryPath as $checkCategory) {
						if ($checkCategory == $entity->Id) {
							$categories[$categoryId]['level'] = $counter;
						}
						$counter++;
					}

					//Set offline for sneakerfolio category
					if ($categoryPath[2] == 375) {
						$categories[$categoryId]['is_offline_category'] = 1;
					} else {
						$categories[$categoryId]['is_offline_category'] = 0;
					}

					//Set user parent settings for outlet, customer service, blog and sneakerfolio
					if (($categoryId != 150 && $categoryPath[2] == 150) || ($categoryId != 200 && $categoryId != 213 && $categoryPath[2] == 200) || ($categoryId != 300 && $categoryPath[2] == 300) || ($categoryId != 375 && $categoryPath[2] == 375)) {
						$categories[$categoryId]['custom_use_parent_settings'] = 1;
					} else {
						$categories[$categoryId]['custom_use_parent_settings'] = 0;
					}
				}
			}
		}

		fwrite($transactionLogHandle, "  ->ACQUIRING RESOURCE CONNECTION...\n");

		// Get resource
		$resource = Mage::getSingleton('core/resource');
		$writeConnection = $resource->getConnection('core_write');

		fwrite($transactionLogHandle, "  ->CREATING/UPDATING CATEGORIES FOR DEFAULT STORE\n");

		// Flag used to initiate reindexing for url rewrites when new category found
		$reindex = NULL;

		foreach ($categories as $categoryArray) {
			//get a new category object
			$category = Mage::getModel('catalog/category');
			$category->setStoreId(0); // 0 = default/all store view.
			$category->load($categoryArray['category_id']);
			if (!$category->getId()) {
				//ATTEMPT TO CREATE IT
				//$query = "ALTER TABLE `catalog_category_entity` AUTO_INCREMENT = " . $categoryArray['category_id'];

				$query = "INSERT INTO `catalog_category_entity` (`entity_id`, `entity_type_id`, `attribute_set_id`, `parent_id`, `created_at`, `updated_at`, `path`, `position`, `level`, `children_count`) VALUES
(" . $categoryArray['category_id'] . ", 3, 3, 2, '" . $realTime[5] . '-' . $realTime[4] . '-' . $realTime[3] . ' ' . $realTime[2] . ':' . $realTime[1] . ':' . $realTime[0] . "', NULL, '1/2/" . $categoryArray['category_id'] . "', 10000, 2, 0)";

				$results = $writeConnection->query($query);
				fwrite($transactionLogHandle, "    ->CREATING CATEGORY     ->" . $categoryArray['category_id'] . "\n");

				$reindex = NULL;//turn off due to issues
				//
				//		$category->setAttributeSetId($category->getDefaultAttributeSetId());
				//		$category->setParentId(1);
				//		$category->setPath('1');
				//		$category->setPosition(1);
				//		$category->setLevel(1);
				//		$category->setChildrenCount(0);
				//		$category->setName('placeholder');
				//		$category->setIsActive(0);
				//		$category->setUrlKey('placeholder');
				//		$category->setIsOfflineCategory(0);
				//		$category->setHighlightedProduct(NULL);
				//		//$category->setData('thumbnail', false); //not using right now
				//		$category->setDescription(NULL);
				//		//$category->setData('image', false); //not using right now
				//		$category->setMetaTitle(NULL);
				//		$category->setMetaKeywords(NULL);
				//		$category->setMetaDescription(NULL);
				//		$category->setIncludeInMenu(1);
				//		$category->setDisplayMode('PRODUCTS');//if landing assing PAGE else PRODUCTS
				//		$category->setLandingPage(NULL);
				//		$category->setIsAnchor(0);
				//		$category->setAvailableSortBy(NULL);
				//		//$category->setData('default_sort_by', false); //not using right now
				//		$category->setFilterPriceRange(NULL);
				//		$category->setCustomUseParentSettings(0);
				//		$category->setCustomApplyToProducts(0);
				//		$category->setCustomDesign(NULL);
				//		$category->setCustomDesignFrom(NULL);
				//		$category->setCustomDesignTo(NULL);
				//		$category->setPageLayout(NULL);
				//		$category->setCustomLayoutUpdate(NULL);
				//		//$category->setUrlPath();//Automatically created from UrlKey//NO NEED TO SET
				//
    //		try {
				//		    $category->save();
				//		    $reindex = true;
				//		}
				//		catch (Exception $e){
				//		    fwrite($transactionLogHandle, "    ->ERROR                 ->" . $e->getMessage() . "\n");
				//		}
				//		unset ($category);
			}

			$category = Mage::getModel('catalog/category');
			$category->setStoreId(0); // 0 = default/all store view.
			$category->load($categoryArray['category_id']);

			fwrite($transactionLogHandle, "    ->UPDATING CATEGORY     ->" . $category->getId() . "\n");

			//$category->setAttributeSetId($category->getDefaultAttributeSetId());//Does not need to be set again as it does not change
			$category->setParentId($categoryArray['parent_id']);
			$category->setPath($categoryArray['path']);
			$category->setPosition($categoryArray['position']);
			$category->setLevel($categoryArray['level']);
			//$category->setChildrenCount($categoryArray['children_count']); //Set below

			$category->setName($categoryArray['name']);
			if ($categoryArray['sneakerhead_only'] == 0) {
				$category->setIsActive($categoryArray['is_active']);
			} else {
				$category->setIsActive(0);
			}
			$category->setUrlKey($categoryArray['url_key']); // for sneakerhead.com
			$category->setIsOfflineCategory($categoryArray['is_offline_category']);
			if ($categoryArray['highlighted_product'] != '') {
				$category->setHighlightedProduct($categoryArray['highlighted_product']);
			} else {
				$category->setHighlightedProduct(NULL);
			}
			//$category->setData('thumbnail', false); //not using right now
			if ($categoryArray['heading'] != '') {
				$category->setDescription($categoryArray['heading']);
			} else {
				$category->setDescription(NULL);
			}
			//$category->setData('image', false); //not using right now
			$category->setMetaTitle($categoryArray['meta_title']);
			if ($categoryArray['meta_description'] != '') {
				$category->setMetaDescription($categoryArray['meta_description']);
			} else {
				$category->setMetaDescription(NULL);
			}
			if ($categoryArray['meta_keywords'] != '') {
				$category->setMetaKeywords($categoryArray['meta_keywords']);
			} else {
				$category->setMetaKeywords(NULL);
			}
			if ($categoryArray['canonical'] != '') {
				$category->setCanonical($categoryArray['canonical']);
			} else {
				$category->setMetaKeywords(NULL);
			}
			$category->setIncludeInMenu($categoryArray['include_in_menu']);
			//$category->setDisplayMode('PRODUCTS'); // Set via admin panel -- prevent overwrite
			//$category->setLandingPage(NULL); // Set via admin panel -- prevent overwrite
			$category->setIsAnchor($categoryArray['is_anchor']);
			//$category->setAvailableSortBy(NULL); //not using right now
			//$category->setData('default_sort_by', false); //not using right now
			//$category->setFilterPriceRange(NULL); //not using right now
			$category->setCustomUseParentSettings($categoryArray['custom_use_parent_settings']);
			//$category->setCustomApplyToProducts(0); //not using right now
			//$category->setCustomDesign(NULL); //not using right now
			//$category->setCustomDesignFrom(NULL); //not using right now
			//$category->setCustomDesignTo(NULL); //not using right now
			//$category->setPageLayout(NULL); //not using right now
			//$category->setCustomLayoutUpdate(NULL); // Set via admin panel -- prevent overwrite
			//$category->setUrlPath();//Automatically created from UrlKey//NO NEED TO SET

			try {
				$category->save();
			} catch (Exception $e) {
				fwrite($transactionLogHandle, "    ->ERROR                 ->" . $e->getMessage() . "\n");
			}

			if ($category->getId() != $categoryArray['category_id']) {
				fwrite($transactionLogHandle, "    ->ERROR                 ->CATEGORY ID MISMATCH\n");
				throw new Exception("CATEGORY ID MISMATCH");
			} else {
				fwrite($transactionLogHandle, "    ->CREATED/UPDATED CAT   ->" . $category->getId() . "\n");
			}
			unset($category);
		}

		fwrite($transactionLogHandle, "  ->CATEGORIES CREATED/UPDATED FOR DEFAULT STORE\n");

		fwrite($transactionLogHandle, "  ->UPDATING CHILD COUNTS\n");

		foreach ($categories as $categoryArray) {
			$query = "SELECT COUNT(*) AS `count` FROM `catalog_category_entity` WHERE `path` LIKE '" . $categoryArray['path'] . "/%'";
			$results = $writeConnection->query($query);
			foreach ($results as $result) {
				$category = Mage::getModel('catalog/category');
				$category->setStoreId(0); // 0 = default/all store view.
				$category->load($categoryArray['category_id']);
				$category->setChildrenCount($result['count']);
				try {
					$category->save();
					fwrite($transactionLogHandle, "    ->CHILD COUNT UPDATED   ->" . $category->getId() . "->" . $result['count'] . "\n");
				} catch (Exception $e) {
					fwrite($transactionLogHandle, "    ->ERROR                 ->" . $e->getMessage() . "\n");
				}
				unset($category);
			}
		}
		fwrite($transactionLogHandle, "  ->CHILD COUNTS UPDATED\n");

		// Create a unique array of just the "sneakerhead only" categories that
		// need to be modified for mobile
		$sneakerheadOnlyCategories = array();

		fwrite($transactionLogHandle, "  ->UPDATING SNEAKERHEAD STORE CATEGORIES...\n");

		//Sneakerhead.com Category Pages
		foreach ($categories as $categoryArray) {
			//get a new category object
			$category = Mage::getModel('catalog/category');
			$category->setStoreId(21); // 0 = default/all store view. If you want to save data for a specific store view, replace 0 by Mage::app()->getStore()->getId().

			$id = $categoryArray['category_id'];
			$category->load($id);

			//Set all values to default/remove overrides using setData
			$category->setData('name', false);
			$category->setData('url_key', false);
			$category->setData('is_offline_category', false);
			$category->setData('highlighted_product', false);
			//$category->setData('thumbnail', false); //not using right now
			$category->setData('description', false);
			//$category->setData('image', false); //not using right now
			$category->setData('meta_title', false);
			$category->setData('meta_keywords', false);
			$category->setData('meta_description', false);
			$category->setData('canonical', false);
			$category->setData('include_in_menu', false);
			$category->setData('display_mode', false);
			$category->setData('landing_page', false);
			//$category->setData('is_anchor', false);//DO NOT SET -- IS GLOBAL
			$category->setData('available_sort_by', false);
			//$category->setData('default_sort_by', false); //not using right now
			$category->setData('filter_price_range', false);
			$category->setData('custom_use_parent_settings', false);
			$category->setData('custom_apply_to_products', false);
			$category->setData('custom_design', false);
			$category->setData('custom_design_from', false);
			$category->setData('custom_design_to', false);
			$category->setData('page_layout', false);
			$category->setData('custom_layout_update', false);

			if ($categoryArray['sneakerhead_only'] == 0) {
				$category->setData('is_active', false);
			} else {
				$category->setIsActive($categoryArray['is_active']);
				$sneakerheadOnlyCategories[] = $categoryArray;
			}

			try {
				$category->save();
				fwrite($transactionLogHandle, "    ->UPDATED CATEGORY ID   ->" . $category->getId() . "\n");
			} catch (Exception $e) {
				fwrite($transactionLogHandle, "    ->ERROR                 ->" . $e->getMessage() . "\n");
			}
			unset($category);
		}

		fwrite($transactionLogHandle, "  ->UPDATING SNEAKERHEAD MOBILE STORE CATEGORIES...\n");

		//Sneakerhead.com MOBILE Category Pages
		foreach ($sneakerheadOnlyCategories as $categoryArray) {
			//get a new category object
			$category = Mage::getModel('catalog/category');
			$category->setStoreId(22);

			$id = $categoryArray['category_id'];
			$category->load($id);

			//Set all values to default/remove overrides using setData
			$category->setData('name', false);
			$category->setData('url_key', false);
			$category->setData('is_offline_category', false);
			$category->setData('highlighted_product', false);
			//$category->setData('thumbnail', false); //not using right now
			$category->setData('description', false);
			//$category->setData('image', false); //not using right now
			$category->setData('meta_title', false);
			$category->setData('meta_keywords', false);
			$category->setData('meta_description', false);
			$category->setData('canonical', false);
			$category->setData('include_in_menu', false);
			$category->setData('display_mode', false);
			$category->setData('landing_page', false);
			//$category->setData('is_anchor', false);//DO NOT SET -- IS GLOBAL
			$category->setData('available_sort_by', false);
			//$category->setData('default_sort_by', false); //not using right now
			$category->setData('filter_price_range', false);
			$category->setData('custom_use_parent_settings', false);
			$category->setData('custom_apply_to_products', false);
			$category->setData('custom_design', false);
			$category->setData('custom_design_from', false);
			$category->setData('custom_design_to', false);
			$category->setData('page_layout', false);
			$category->setData('custom_layout_update', false);

			$setIsActive = $categoryArray['is_active'];

			if ($setIsActive === 1) {
				// Check if this category_id or its ancestor is excluded
				// $exclude_categories defined in config.php
				$path = $categoryArray['path'];
				$parent_ids = explode('/', $path);
				if (is_array($parent_ids) && count($parent_ids)) {
					foreach($parent_ids as $parent_id) {
						if (in_array($parent_id, $mobile_exclude_categories)) {
							$setIsActive = 0;
							break;
						}
					}
				}
			}

			$category->setIsActive($setIsActive);

			try {
				$category->save();
				fwrite($transactionLogHandle, "    ->UPDATED CATEGORY ID   ->" . $category->getId() . "\n");
			} catch (Exception $e) {
				fwrite($transactionLogHandle, "    ->ERROR                 ->" . $e->getMessage() . "\n");
			}
			unset($category);
		}

		//Does not appear to be needed except on a full rebuild of the categories
		if ($reindex) {
			//Load adminhtml area of config.xml to initialize adminhtml observers
			Mage::app()->loadArea('adminhtml');

			fwrite($transactionLogHandle, "  ->INDEXING\n");

			try {
				// Reindex urls
				$processIds = array(3);

				foreach ($processIds as $processId) {
					fwrite($transactionLogHandle, "    ->START       : " . $processId . "\n");
					$process = Mage::getModel('index/process')->load($processId);
					$process->reindexAll();
					fwrite($transactionLogHandle, "    ->END         : " . $processId . "\n");
				}

				fwrite($transactionLogHandle, "  ->COMPLETED\n");
			} catch (Exception $e) {
				fwrite($transactionLogHandle, "  ->NOT COMPLETED : " . $e . "\n");
			}
		}


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

		fwrite($transactionLogHandle, "  ->UPDATE COMPLETED FOR SNEAKERHEAD\n");

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