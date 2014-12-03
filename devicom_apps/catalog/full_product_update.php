<?php

//Version 1.0 - 2/16/2012

set_time_limit(0); //no timout
ini_set('memory_limit', '1024M');

require(dirname(dirname(__FILE__)) . '/lib/Devicom/config.php');

// If this path changes, change it in the mysqlimport command and in the rebuildInventory function below
$root_dir = dirname(__FILE__);
$catalogLogsDirectory = $root_dir . '/logs/';
$receivedDirectory = $root_dir . '/received/';
$failedDirectory = $root_dir . '/failed/';
$processedDirectory = $root_dir . '/processed/';
$temporaryDirectory = $root_dir . '/temporary/';

//Check that no lock exists (indicating file is being written or processed by another script) and, if not, grab the oldest full_product_update to begin processing
if (!rtrim(basename(shell_exec('ls ' . $catalogLogsDirectory . '*.lock | head --lines 1')))
		&& $filename = rtrim(basename(shell_exec('ls -t -r ' . $receivedDirectory . 'full_product_update* | head --lines 1')))) {

	//Exit if full file is older or equal to any incremental file (if exists)
	if ($incrementalFilename = rtrim(basename(shell_exec('ls -t -r ' . $receivedDirectory . 'incremental_product_update* | head --lines 1')))) {
		if (substr($filename, -22, 8) . substr($filename, -13, 9) >= substr($incrementalFilename, -22, 8) . substr($incrementalFilename, -13, 9)) {
			exit;
		}
	}

//    if (substr($filename, 0, 19) != 'full_product_update') {
//	exit;
//    }
	// Initialize magento environment for 'admin' store
	require_once dirname(dirname(dirname(__FILE__))) . '/app/Mage.php';
	umask(0);
	Mage::app('admin'); //admin defines default value for all stores including the Main Website

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

		//Get contents of XML file
		$contents = file_get_contents($receivedDirectory . $filename);

		//Create new XML object
		$rootXmlElement = new SimpleXMLElement($contents);

		foreach ($rootXmlElement->children() as $child) {
			$products[$child->getName()] = $child->getName();
		}

		//If Configurable exists then it is a product update. Otherwise, just full inventory update.
		//Note: A full inventory update is required after updating products
		if (isset($products['Configurable'])) {

			$numberOfItemsToProcess = 10000;
			$itemCounter = 0;
			$itemCount = 0;

			if (file_exists($catalogLogsDirectory . 'stage')) {
				$stageContents = file_get_contents($catalogLogsDirectory . 'stage');
				$stage = substr($stageContents, 0, 1);
				$startItem = substr($stageContents, 2);
				$endItem = $startItem + $numberOfItemsToProcess;
			} else {
				$stage = 0; //Configurable
				$startItem = 0;
				$endItem = $numberOfItemsToProcess;
				//Create file if it does not exist
				$stageHandle = fopen($catalogLogsDirectory . 'stage', 'w+');
				fwrite($transactionLogHandle, "  ->STAGE CREATED :\n");
				fclose($stageHandle);
			}

			switch ($stage) {
				case 0: //Process configurables
					//Load adminhtml area of config.xml to initialize adminhtml observers -- required to trigger pagecache
					Mage::app()->loadArea('adminhtml');

					//Disable indexes
					fwrite($transactionLogHandle, "  ->CHANGE INDEX  : MANUAL\n");
					$processCollection = Mage::getSingleton('index/indexer')->getProcessesCollection();
					foreach ($processCollection as $process) {
						$process->setMode(Mage_Index_Model_Process::MODE_MANUAL)->save();
					}
					fwrite($transactionLogHandle, "  ->CHANGED INDEX : MANUAL\n");

					//Get existing websites for accessing store information
					$existingWebsites = Mage::app()->getWebsites(true);

					//Variables to set for additional processing
					$configurableProductsGroupsToDelete = array();

					//Step into entities array to access configurable products
					foreach ($rootXmlElement->Configurable as $configurableProducts) {

						//Get count
						$itemCount = count($configurableProducts);
						fwrite($transactionLogHandle, "  ->ITEM COUNT    : " . $itemCount . "\n");

						//Loop through configurable products
						foreach ($configurableProducts->Product as $entity) {

							if ($startItem <= $itemCounter && $endItem >= $itemCounter) {

								//Set variables to supplied values
								$store = (string) $entity->Store; // 0 or admin -- NO NEED IF HARD-CODED
								$websites = (string) $entity->Websites; // sneakerhead, sneakerrx, military
								$categoryIds = (string) $entity->CategoryIds; // ids
								$typeId = 'configurable'; // configurable or simple -- NO NEED
								$attributeSetName = (string) $entity->AttributeSetName; // mensize, womesize, kidsize, bigkidsize, preschoolsize, apparelsize, onesize
								$name = (string) $entity->Name;
								$description = (string) $entity->Description;
								$shortDescription = (string) $entity->ShortDescription;
								$sku = $entity->Sku;
								if ($entity->Weight == 2.5) {
									$weight = 4;
								} elseif ($entity->Weight == .5) {
									$weight = 1;
								} else {
									$weight = $entity->Weight;
									fwrite($transactionLogHandle, "    ->ERROR       : Weight not found\n");
								}
								if ((string) $entity->Status == 2) { //  1 online, 2 offline
									$isOffline = 1;
									$customLayoutUpdate = '<reference name="head">
     <action method="removeItem"><type>skin_js</type><name>js/jquery_ui.js</name><params /></action>
     <action method="removeItem"><type>skin_js</type><name>js/common.js</name><params /></action>
     <action method="removeItem"><type>js</type><name>varien/product.js</name><params /></action>
     <action method="removeItem"><type>js</type><name>varien/configurable.js</name><params /></action>
     <action method="removeItem"><type>skin_js</type><name>js/detail.js</name><params /></action>
     <action method="removeItem"><type>skin_css</type><name>css/detail.css</name><params /></action>
     <action method="addItem"><type>skin_js</type><name>js/lightbox.js</name></action>
     <action method="addCss"><stylesheet>css/sneakerfolio.css</stylesheet></action>
</reference>';
								} else {
									$isOffline = 0;
									$customLayoutUpdate = NULL;
								}
								$abstract = (string) $entity->Abstract;
								$accessory = (string) $entity->Accessory;
								$msrp = $entity->Msrp;
								$colorSn = (string) $entity->ColorSn;
								$shColorSn = (string) $entity->ShColorSn;
								$shColorMap = (string) $entity->ShColorMap;
								$urlKey = (string) $entity->UrlKey;
								$visibility = 4; // Catalog, Search
								$thirdPartyPrice = $entity->Price;
								$createdAt = $entity->CreatedAt;
								$model = (string) $entity->Model;
								$imageCount = $entity->ImageCount;
								$brand = (string) $entity->Brand;
								$shGender = (string) $entity->ShGender;
								$taxClassId = $entity->Taxable; // 0 None taxable, 2 Taxable Goods
								$isNewArrival = $entity->IsNewArrival;
								$isOnSale = $entity->IsOnSale;
								$promotionTag = $entity->PromotionTag;
								$canonical = (string) $entity->Canonical;
								$specialShippingGroup = $entity->FreeShippingTypeId;
								$eligibleForRewards = $entity->IsRewardEligible;
								$eligibleForDiscount = $entity->IsDiscountEligible;
								$price = $entity->SalePrice;
								if ((string) $entity->Status == 2) { //  1 online, 2 offline
									$defaultCategory = (string) $entity->CategoryIds;
								} else {
									$defaultCategory = (string) $entity->PrimaryCategoryId;
								}
								$limitQuantity = $entity->OrderLimitCount;
								$phoneOrderOnly = $entity->IsPhoneOrderOnly;
								$phoneOrderOnlyMessage = (string) $entity->PhoneOrderOnlyMessage;
								$testimonial = (string) $entity->Testimonial;
								$metaDescription = (string) $entity->SEO_Description;
								$metaKeywords = (string) $entity->SEO_Keywords;
								$lifeSpanFrom = strtotime($entity->LifeFrom);
								$lifeSpanTo = strtotime($entity->LifeTo);
								$returnRate = $entity->ReturnRatePercentage;
								$popularity = $entity->Popularity;
								$priceHistory = (string) $entity->PriceHistory;

								// Configurable is always in stock
								$status = 1; // 1 enabled, 2 disabled
								$isInStock = 1; // 0 out of stock, 1 in stock
								//Check if configurable product already exists
								if (!$product = Mage::getModel('catalog/product')->loadByAttribute('sku', $sku)) {

									//Get new product model
									$product = Mage::getModel('catalog/product');

									fwrite($transactionLogHandle, "  ->CREATING      : Configurable : " . $sku . "\n");

									//Set store id
									foreach ($existingWebsites as $existingWebsite) {
										foreach ($existingWebsite->getGroups() as $group) {
											$existingStores = $group->getStores();
											foreach ($existingStores as $existingStore) {
												if ($store == $existingStore->getCode()) {
													$storeId = $existingStore->getId();
												}
											}
										}
									}
									$product->setStoreId($storeId);

									//Set websites
									$websiteIds = array();
									$websiteCodes = explode(',', $websites);
									foreach ($websiteCodes as $websiteCode) {
										try {
											$website = Mage::app()->getWebsite(trim($websiteCode));
											if (!in_array($website->getId(), $websiteIds)) {
												$websiteIds[] = $website->getId();
											}
										} catch (Exception $e) {
											fwrite($transactionLogHandle, "  ->ERROR         : Website not found\n");

											// Send email
											$message = "Full Update: " . $filename . "\r\n\r\nThe specified website was not found for configurable product " . $sku . ". This may indicate that the website is not spelled correctly in Item Manager.";
											sendNotification($subject = 'Website not found', $message);
										}
									}
									$product->setWebsiteIds($websiteIds);
									//Set categories
									$product->setStoreCategoryIds($categoryIds);

									//Find and set categoryIds to an existing category contained in tree or don't
									$defaultCategoryTreeArray = explode(',', $defaultCategory);
									$reversedDefaultCategoryTreeArray = array_reverse($defaultCategoryTreeArray);
									$categoryFound = null;
									foreach ($reversedDefaultCategoryTreeArray as $defaultCategoryId) {
										if ($categoryToCheck = Mage::getModel('catalog/category')->load($defaultCategoryId)) {
											if ($categoryToCheck->getId()) {
												//Create mapping
												$product->setCategoryIds($defaultCategoryId);
												$categoryFound = 1;
												break;
											}
										}
									}

									if (is_null($categoryFound)) {
										// Send email
										$message = "Incremental Update: " . $filename . "\r\n\r\nDefault category mapping not created for for simple product " . $sku . ". This indicates that no category ID defined in the products default category tree was found.";
										sendNotification($subject = 'Category not found', $message);
										fwrite($transactionLogHandle, "    ->CATEGORY    : NOT FOUND    : " . $sku . "\n");							
									}
						
									// Set product type
									$product->setTypeId($typeId); //simple or configurable
									// Set attribute set
									$entityTypeId = Mage::getModel('eav/entity')->setType('catalog_product')->getTypeId();
									$attributeSetId = Mage::getModel('eav/entity_attribute_set')->getCollection()->setEntityTypeFilter($entityTypeId)->addFieldToFilter('attribute_set_name', $attributeSetName)->getFirstItem()->getAttributeSetId();
									$product->setAttributeSetId($attributeSetId);

									// Set configurable attributes
									$configAttributeCodes[$attributeSetName] = $attributeSetName;
									$usingAttributeIds = array();

									$attribute = $product->getResource()->getAttribute($attributeSetName);
									//if ($product->getTypeInstance()->canUseAttribute($attribute)) {
									$product->setCanSaveConfigurableAttributes(true);
									$configurableAttributesData = array('0' => array('attribute_id' => $attribute->getAttributeId()));
									$product->setConfigurableAttributesData($configurableAttributesData);
									$product->setCanSaveConfigurableAttributes(true);
									$product->setCanSaveCustomOptions(true);
									//}
									//Set everything else
									$product->setName($name);
									$product->setDescription($description);
									$product->setShortDescription($shortDescription);
                                    $product->setSku($sku);
									$product->setWeightSn($weight); // cannot define for configurable -- will be NULL
									$product->setIsOffline($isOffline);
									$product->setCustomLayoutUpdate($customLayoutUpdate);
									$product->setAbstract($abstract);
									$product->setAccessory($accessory);
									$product->setMsrp($msrp);
									$product->setColorSn($colorSn);
                                    $productColor = getColorOptionId( $shColorSn, $shColorMap) ;
                                    if($productColor){
                                        $product->setProductcolor( $productColor );
                                    }
                                    $idUrlKey = Mage::getModel('catalog/product')->loadByAttribute('url_key', $urlKey);
                                    if(!$idUrlKey){
                                        fwrite($transactionLogHandle, " URL KEY $urlKey do not exist ". "\n");
                                        $product->setUrlKey($urlKey);
                                    }else{
                                        fwrite($transactionLogHandle, " URL KEY $urlKey  exist ". "\n");
                                    }

									$product->setVisibility($visibility); // 1 or 4
//									$product->setCreatedAt($createdAt);
									$product->setModel($model);
									$product->setImageCount($imageCount);
									$product->setBrand($brand);
									$product->setProductbrand(getAttributeOptionId('productbrand',$brand));
									$product->setProductgender(getAttributeOptionId('productgender',$shGender));
									$product->setTaxClassId($taxClassId); // 0 or 2
									$product->setIsNewArrival($isNewArrival);
									$product->setIsOnSale($isOnSale);
									$product->setPromotionTag($promotionTag);
									$product->setCanonical($canonical);
									$product->setSpecialShippingGroup($specialShippingGroup); // 0, 1 or 2
									if ($eligibleForRewards == '0') {
										$product->setRewardPoints('0'); //
									} else {
										$product->setRewardPoints(); //
									}
									$product->setEligibleForDiscount($eligibleForDiscount); // 0 or 1
									$product->setPrice($price);
									$product->setDefaultCategory($defaultCategory);
									$product->setLimitQuantity($limitQuantity);
									$product->setPhoneOrderOnly($phoneOrderOnly);
									$product->setOrderingMessage($phoneOrderOnlyMessage);
									$product->setTestimonial($testimonial);
									$product->setMetaDescription($metaDescription);
									$product->setMetaKeyword($metaKeywords);
									$product->setLifeSpanFrom($lifeSpanFrom);
									$product->setLifeSpanTo($lifeSpanTo);
									$product->setReturnRate($returnRate);
									$product->setPopularity($popularity);
									$product->setPriceHistory($priceHistory);
									$product->setStatus($status); // 1 or 2
									//Set stock
									$product->setStockData(array(
										'is_in_stock' => $isInStock
									)); // 0 or 1 -- cannot define qty for configurable -- will be 0
									//Save product
									$product->save();

									//Set pricing for 3rd Party websites (sneakerrx and military)
									/*if (strpos($websites, 'sneakerrx') !== false && strpos($websites, 'military') !== false) {
										//Unset and reload product after save
										unset($product);
										$product = Mage::getModel('catalog/product')->loadByAttribute('sku', $sku);

										$product->setStoreId(1);
										$product->setPrice($thirdPartyPrice);
										//Save product
										$product->save();
										$product->setStoreId(20);
										$product->setPrice($thirdPartyPrice);
										//Save product
										$product->save();
										fwrite($transactionLogHandle, "  ->3PARTY PRICE: SET          : " . $sku . "\n");
									}*/
								} else {

									//UPDATE CONFIGURABLE PRODUCT
									fwrite($transactionLogHandle, "  ->UPDATING      : Configurable : " . $sku . "\n");

									//NOTES: NO SKU ON UPDATE
									//Set store id
									foreach ($existingWebsites as $existingWebsite) {
										foreach ($existingWebsite->getGroups() as $group) {
											$existingStores = $group->getStores();
											foreach ($existingStores as $existingStore) {
												if ($store == $existingStore->getCode()) {
													$storeId = $existingStore->getId();
												}
											}
										}
									}

									$product->setStoreId($storeId);

									//Set websites
									$websiteIds = array();
									$websiteCodes = explode(',', $websites);
									foreach ($websiteCodes as $websiteCode) {
										try {
											$website = Mage::app()->getWebsite(trim($websiteCode));
											if (!in_array($website->getId(), $websiteIds)) {
												$websiteIds[] = $website->getId();
											}
										} catch (Exception $e) {
											fwrite($transactionLogHandle, "  ->ERROR         : Website not found\n");

											// Send email
											$message = "Full Update: " . $filename . "\r\n\r\nThe specified website was not found for configurable product " . $sku . ". This may indicate that the website is not spelled correctly in Item Manager.";
											sendNotification($subject = 'Website not found', $message);
										}
									}
									$product->setWebsiteIds($websiteIds);

									//Set categories
									$product->setStoreCategoryIds($categoryIds);

									//Set everything else
									$product->setName($name);
									$product->setDescription($description);
									$product->setShortDescription($shortDescription);
									//$product->setSku($sku);
									$product->setWeightSn($weight); // cannot define for configurable -- will be NULL
									$product->setIsOffline($isOffline);
									$product->setCustomLayoutUpdate($customLayoutUpdate);
									$product->setAbstract($abstract);
									$product->setAccessory($accessory);
									$product->setMsrp($msrp);
									$product->setColorSn($colorSn);
                                    $productColor = getColorOptionId( $shColorSn, $shColorMap) ;
                                    if($productColor){
                                        $product->setProductcolor( $productColor );
                                    }
									$product->setUrlKey($urlKey);
									$product->setVisibility($visibility); // 1 or 4
//									$product->setCreatedAt($createdAt);
									$product->setModel($model);
									$product->setImageCount($imageCount);
									$product->setBrand($brand);
                                    $product->setProductbrand(getAttributeOptionId('productbrand',$brand));
                                    $product->setProductgender(getAttributeOptionId('productgender',$shGender));
                                    $product->setTaxClassId($taxClassId); // 0 or 2
									$product->setIsNewArrival($isNewArrival);
									$product->setIsOnSale($isOnSale);
									$product->setPromotionTag($promotionTag);
									$product->setCanonical($canonical);
									$product->setSpecialShippingGroup($specialShippingGroup); // 0, 1 or 2
									if ($eligibleForRewards == '0') {
										$product->setRewardPoints('0'); //
									} else {
										$product->setRewardPoints(); //
									}
									$product->setEligibleForDiscount($eligibleForDiscount); // 0 or 1
									$product->setPrice($price);
									$product->setDefaultCategory($defaultCategory);
									$product->setLimitQuantity($limitQuantity);
									$product->setPhoneOrderOnly($phoneOrderOnly);
									$product->setOrderingMessage($phoneOrderOnlyMessage);
									$product->setTestimonial($testimonial);
									$product->setMetaDescription($metaDescription);
									$product->setMetaKeyword($metaKeywords);
									$product->setLifeSpanFrom($lifeSpanFrom);
									$product->setLifeSpanTo($lifeSpanTo);
									$product->setReturnRate($returnRate);
									$product->setPopularity($popularity);
									$product->setPriceHistory($priceHistory);
									$product->setStatus($status); // 1 or 2
									// Add sku to for deleting related simple products
									if ($isOffline == 1) {
										$configurableProductsGroupsToDelete[(string) $sku] = Mage::getModel('catalog/product_type_configurable')->getChildrenIds($product->getId());
									}

									//Save product
									$product->save();

									//Set pricing for 3rd Party websites (sneakerrx and military)
									if (strpos($websites, 'sneakerrx') !== false && strpos($websites, 'military') !== false) {
										$product->setStoreId(1);
										$product->setPrice($thirdPartyPrice);
										//Save product
										$product->save();
										$product->setStoreId(20);
										$product->setPrice($thirdPartyPrice);
										//Save product
										$product->save();
										fwrite($transactionLogHandle, "  ->3PARTY PRICE: SET          : " . $sku . "\n");
									}
								}

								fwrite($transactionLogHandle, "  ->SAVED         : Configurable : " . $sku . "\n");

								unset($product);

								fwrite($transactionLogHandle, "  ->ITEM          : " . $itemCounter . "\n");
							} elseif ($startItem <= $itemCounter && $endItem <= $itemCount) {
								//Update stage start
								fwrite($transactionLogHandle, "  ->UPDATE START  : " . $itemCounter . "\n");
								$stageHandle = fopen($catalogLogsDirectory . 'stage', 'w+');
								fwrite($stageHandle, '0:' . $itemCounter);
								fclose($stageHandle);
								break;
							} else {
								fwrite($transactionLogHandle, "  ->SKIP          : " . $itemCounter . "\n");
							}
							$itemCounter++;
						}
					}

					// Delete simple products if update to configurable products
					if ($configurableProductsGroupsToDelete) {

						foreach ($configurableProductsGroupsToDelete as $existingConfigurableProduct => $configurableProductGroups) {

							// Load configurable product
							if (!$configurableProduct = Mage::getModel('catalog/product')->loadByAttribute('sku', $existingConfigurableProduct)) {
								fwrite($transactionLogHandle, "  ->ERROR         : Configurable not found " . $existingConfigurableProduct . "\n");

								// Send email
								$message = "Full Update: " . $filename . "\r\n\r\nThe simple product could not be updated because the configurable product " . $existingConfigurableProduct . " was not found.";
								sendNotification($subject = 'Configurable product not found', $message);
							} else {
								foreach ($configurableProductGroups as $simpleProductGroup) {

									foreach ($simpleProductGroup as $simpleProductToDelete) {

										if ($product = Mage::getModel('catalog/product')->load($simpleProductToDelete)) {

											// Check if configurable is offline -- if so, delete simples
											if ($configurableProduct->getIsOffline() == 1) {
												fwrite($transactionLogHandle, "  ->DELETING      : Simple       : " . $product->getSku() . "\n");
												$product->delete();
												fwrite($transactionLogHandle, "  ->DELETED       : Simple       : " . $product->getSku() . "\n");
											}
										}
									}
								}
							}
						}
					}

					if ($itemCount <= $itemCounter) {
						if (!isset($products['Simple'])) {
							//Change stage
							fwrite($transactionLogHandle, "  ->CHANGE STAGE  : 3\n"); //Skip simple and skip stock update -- reindex and flush cache
							$stageHandle = fopen($catalogLogsDirectory . 'stage', 'w+');
							fwrite($stageHandle, '3:0');
							fclose($stageHandle);
						} else {
							//Change stage
							fwrite($transactionLogHandle, "  ->CHANGE STAGE  : 1\n");
							$stageHandle = fopen($catalogLogsDirectory . 'stage', 'w+');
							fwrite($stageHandle, '1:0');
							fclose($stageHandle);
						}
					}
					break;
				case 1:  //Process simple products
					//Load adminhtml area of config.xml to initialize adminhtml observers -- required to trigger pagecache
					Mage::app()->loadArea('adminhtml');
                    $resource = Mage::getSingleton('core/resource');
                    $writeConnection = $resource->getConnection('core_write');
					//Get existing websites for accessing store information
					$existingWebsites = Mage::app()->getWebsites(true);

					//Variable to set if new simple of configurable product found
					//$newProductFound = false;
					//Step into entities array to access simple products
					foreach ($rootXmlElement->Simple as $simpleProducts) {

						//Get count
						$itemCount = count($simpleProducts);
						fwrite($transactionLogHandle, "  ->ITEM COUNT    : " . $itemCount . "\n");

						//Loop through simple products
						foreach ($simpleProducts->Product as $entity) {

							if ($startItem <= $itemCounter && $endItem >= $itemCounter) {

								//Set sku, size, quantity and isInStock in case this is just an inventory update
								$sku = $entity->Sku;
                                $arr = explode('-', $sku);
                                $eursize = $entity->Eursize;
                                $size = end($arr);
								$qty = $entity->Quantity;

								if ($qty > 0) {
									//Check if simple product already exists
									if (!$product = Mage::getModel('catalog/product')->loadByAttribute('sku', $sku)) {

										// Load configurable product
										if (!$configurableProduct = Mage::getModel('catalog/product')->loadByAttribute('sku', substr($sku, 0, -(strlen($size) + 1)))) {
											fwrite($transactionLogHandle, "  ->ERROR         : Configurable not found " . substr($sku, 0, -(strlen($size) + 1)) . "\n");

											// Send email
											$message = "Full Update: " . $filename . "\r\n\r\nThe simple product could not be created because the configurable product " . substr($sku, 0, -(strlen($size) + 1)) . " was not found.";
											sendNotification($subject = 'Configurable product not found', $message);
										} else {
											// Create simple if configurable status enabled
											if ($configurableProduct->getIsOffline() != 1) {

												fwrite($transactionLogHandle, "  ->CREATING      : Simple       : " . $sku . "\n");

												//Get new product model
												$product = Mage::getModel('catalog/product');

												//Set variables to supplied values
												$storeId = $configurableProduct->getStore()->getId();
												$websiteIds = $configurableProduct->getWebSiteIds();
                                                $categoryIds = $configurableProduct->getStoreCategoryIds();
												$typeId = 'simple';
												$attributeSetId = $configurableProduct->getAttributeSetId();
												$name = $configurableProduct->getName();
												$description = $configurableProduct->getDescription();
												$shortDescription = $configurableProduct->getShortDescription();
												$weight = $configurableProduct->getWeightSn();
												$visibility = 1; // Not Visible
												$taxClassId = $configurableProduct->getTaxClassId(); // Taxable Goods
												$specialShippingGroup = $configurableProduct->getSpecialShippingGroup();
												$eligibleForRewards = $configurableProduct->getRewardPoints();
												$price = $configurableProduct->getPrice();
												$status = 1; // 1 enabled, 2 disabled
												//Set attributes
												$product->setStoreId($storeId);
												$product->setWebsiteIds($websiteIds);
												//$product->setCategoryIds($categoryIds);
												$product->setTypeId($typeId); //simple or configurable
												$product->setAttributeSetId($attributeSetId);

												//Get attribute set name
												$attributeSetModel = Mage::getModel("eav/entity_attribute_set");
												$attributeSetModel->load($configurableProduct->getAttributeSetId());
												$attributeSetName = $attributeSetModel->getAttributeSetName();

												// Set size attribute option
												$attribute = Mage::getModel('eav/config')->getAttribute('catalog_product', $attributeSetName);
												$options = $attribute->getSource()->getAllOptions(true, true);
												$optionAttributeId = null;
												$fullLabel = null;
												$sizeFound = null; // used to prevent exception when adding related product to configurable when attribute not available
												$i = 0;
												foreach ($options as $option => $item) {
                                                    if(!$item['value']){
                                                        continue;
                                                    }
													$fullLabelArray = explode(',', $item['label']);
													$subLabelArray = explode(' ', $fullLabelArray[0]);
													if ($attributeSetName == 'onesize' || $attributeSetName == 'apparelsize') {
														if (strtoupper($item['label']) == strtoupper($size) || str_replace(' ', '', strtoupper($item['label'])) == strtoupper($size)) {
															$fullLabel = $item['label'];
															$optionAttributeId = $item['value'];
															$optionSortOrder = $i;
															$sizeFound = 1;
															break;
														}
													}else if($attributeSetName == 'unisexcommon'){
                                                        if($fullLabelArray[0] == $eursize){
                                                            $fullLabel = $item['label'];
                                                            $optionAttributeId = $item['value'];
                                                            $optionSortOrder = $i;
                                                            $sizeFound = 1;
                                                            break;
                                                        }
                                                    }  else {
														if ($subLabelArray[1] == $eursize) {
															$fullLabel = $item['label'];
															$optionAttributeId = $item['value'];
															$optionSortOrder = $i;
															$sizeFound = 1;
															break;
														}
													}
													$i++;
												}
												$product->setData($attributeSetName, $optionAttributeId);
                                                $product->setEursize($eursize);
                                                $product->setProductsize(getProductSizeOptionId($eursize));
												//Set some more things
												$product->setName($name);
												$product->setDescription($description);
												$product->setShortDescription($shortDescription);
												$product->setSku($sku);
												$product->setWeight($weight); // cannot define for configurable -- will be NULL
												$product->setVisibility($visibility); // 1 or 4
												$product->setTaxClassId($taxClassId); //
												$product->setSpecialShippingGroup($specialShippingGroup);
												if ($eligibleForRewards == '0') {
													$product->setRewardPoints('0'); //
												} else {
													$product->setRewardPoints(); //
												}
												$product->setPrice($price);
												$product->setStatus($status); // 1 or 2
												//Set stock
												$product->setStockData(array(
													'qty' => 0,
													'is_in_stock' => 0
												));

												// Add sku as configurable related product if size found
												if ($sizeFound) {
													$newRelatedProductGroups[substr($sku, 0, -(strlen($size) + 1))][] = $sku;
                                                    $configUrlKey = $configurableProduct->getUrlKey();
                                                    $storeId = $configurableProduct->getStore()->getId();
                                                    $simpleUrlKey = $configUrlKey."_".$storeId."_".$size;
                                                    //fwrite($transactionLogHandle, "  ->URL KEY  : " . $simpleUrlKey . "\n");
                                                    $product->setUrlKey($simpleUrlKey);
													//Save product
													$product->save();
													fwrite($transactionLogHandle, "  ->SAVED         : Simple       : " . $sku . "\n");
                                                    $query = "INSERT INTO `devicom_inventory` (`sku`, `parent_sku`, `parent_product_id`, `base_price`, `old_price`, `product_id`, `attribute_set_name`, `category_ids`, `sort_order`, `option_id`, `label`, `qty`) VALUES ('" . $sku . "', '" . substr($sku, 0, -(strlen($size) + 1)) . "', " . $configurableProduct->getId() . ", " . $configurableProduct->getPrice() . ", " . $configurableProduct->getFinalPrice() . ", " . $product->getId() . ", '" . $attributeSetName . "', '" . $categoryIds . "', " . $optionSortOrder . ", " . $optionAttributeId . ", '" . addslashes($fullLabel) . "', '0')
                    ON DUPLICATE KEY UPDATE `parent_sku` = '" . substr($sku, 0, -(strlen($size) + 1)) . "', `parent_product_id` = " . $configurableProduct->getId() . ", `base_price` = " . $configurableProduct->getFinalPrice() . ", `old_price` = " . $configurableProduct->getPrice() . ", `product_id` = " . $product->getId() . ", `attribute_set_name` = '" . $attributeSetName . "', `category_ids` = '" . $categoryIds . "', `sort_order` = " . $optionSortOrder . ", `option_id` = '" . $optionAttributeId . "', `label` = '" . addslashes($fullLabel) . "', `qty` = '0'";
                                                    $results = $writeConnection->query($query);
                                                    fwrite($transactionLogHandle, "    ->ADDED       : Inventory    : " . $sku . "\n");
													unset($product);
												} else {
													// Send email
													$message = "Full Update: " . $filename . "\r\n\r\nThe specified size was not found for simple product " . $sku . ". This indicates that either a new size needs to be added to the attribute set or the size was entered in Item Manager incorrectly.";
													sendNotification($subject = 'Size not found', $message);
													fwrite($transactionLogHandle, "  ->NOT SAVED     : Simple       : " . $sku . "\n");
												}
											} else {
												fwrite($transactionLogHandle, "  ->SKIPPING      : Simple       : " . $sku . " DISABLED\n");
											}
										}
									} else {
										// Load configurable product
										if (!$configurableProduct = Mage::getModel('catalog/product')->loadByAttribute('sku', substr($sku, 0, -(strlen($size) + 1)))) {
											fwrite($transactionLogHandle, "  ->ERROR         : Configurable not found " . substr($sku, 0, -(strlen($size) + 1)) . "\n");

											// Send email
											$message = "Full Update: " . $filename . "\r\n\r\nThe simple product could not be updated because the configurable product " . substr($sku, 0, -(strlen($size) + 1)) . " was not found.";
											sendNotification($subject = 'Configurable product not found', $message);
										} else {

											// Update simple if configurable status enabled
											if ($configurableProduct->getIsOffline() != 1) {

												fwrite($transactionLogHandle, "  ->UPDATING      : Simple       : " . $sku . "\n");

												//Set variables to supplied values
												$storeId = $configurableProduct->getStore()->getId();
												$websiteIds = $configurableProduct->getWebSiteIds();
												//$categoryIds = $configurableProduct->getCategoryIds();
												$typeId = 'simple';
												$attributeSetId = $configurableProduct->getAttributeSetId();
												$name = $configurableProduct->getName();
												$description = $configurableProduct->getDescription();
												$shortDescription = $configurableProduct->getShortDescription();
												//sku
												$weight = $configurableProduct->getWeightSn();
												$visibility = 1; // Not Visible
												$taxClassId = $configurableProduct->getTaxClassId(); // Taxable Goods
												$specialShippingGroup = $configurableProduct->getSpecialShippingGroup();
												$eligibleForRewards = $configurableProduct->getRewardPoints();
												$price = $configurableProduct->getPrice();
												$status = 1; // 1 enabled, 2 disabled
												//NOTES: NO SKU ON UPDATE
												//Set attributes
												$product->setStoreId($storeId);
												$product->setWebsiteIds($websiteIds);
												//$product->setCategoryIds($categoryIds);
												$product->setTypeId($typeId); //simple or configurable
												$product->setAttributeSetId($attributeSetId);

												//Get attribute set name
												$attributeSetModel = Mage::getModel("eav/entity_attribute_set");
												$attributeSetModel->load($configurableProduct->getAttributeSetId());
												$attributeSetName = $attributeSetModel->getAttributeSetName();

												// Set size attribute option
												$attribute = Mage::getModel('eav/config')->getAttribute('catalog_product', $attributeSetName);
												$options = $attribute->getSource()->getAllOptions(true, true);
												$optionAttributeId = null;
												$fullLabel = null;
												$sizeFound = null; // used to prevent exception when adding related product to configurable when attribute not available
												$i = 0;
												foreach ($options as $option => $item) {
                                                    if(!$item['value']){
                                                        continue;
                                                    }
													$fullLabelArray = explode(',', $item['label']);
													$subLabelArray = explode(' ', $fullLabelArray[0]);
													if ($attributeSetName == 'onesize' || $attributeSetName == 'apparelsize') {
														if (strtoupper($item['label']) == strtoupper($size) || str_replace(' ', '', strtoupper($item['label'])) == strtoupper($size)) {
															$optionAttributeId = $item['value'];
															$optionSortOrder = $i;
															$sizeFound = 1;
															break;
														}
													} else if($attributeSetName == 'unisexcommon'){
                                                        if($fullLabelArray[0] == $eursize){
                                                            $optionAttributeId = $item['value'];
                                                            $optionSortOrder = $i;
                                                            $sizeFound = 1;
                                                            break;
                                                        }
                                                    } else {
														if ($subLabelArray[1] == $eursize) {
															$optionAttributeId = $item['value'];
															$optionSortOrder = $i;
															$sizeFound = 1;
															break;
														}
													}
													$i++;
												}
												$product->setData($attributeSetName, $optionAttributeId);
                                                $product->setProductsize(getProductSizeOptionId($eursize));
												//Set some more things
												$product->setName($name);
												$product->setDescription($description);
												$product->setShortDescription($shortDescription);
												//$product->setSku($sku);
												$product->setWeight($weight); // cannot define for configurable -- will be NULL
												$product->setVisibility($visibility); // 1 or 4
												$product->setTaxClassId($taxClassId); //
												$product->setSpecialShippingGroup($specialShippingGroup);
												if ($eligibleForRewards == '0') {
													$product->setRewardPoints('0'); //
												} else {
													$product->setRewardPoints(); //
												}
												$product->setPrice($price);
												$product->setStatus($status); // 1 or 2
												// Add sku as configurable related product if size found
												if ($sizeFound) {
													$newRelatedProductGroups[substr($sku, 0, -(strlen($size) + 1))][] = $sku;
													//Save product
													$product->save();
													fwrite($transactionLogHandle, "  ->SAVED         : Simple       : " . $sku . "\n");
													unset($product);
												} else {
													// Send email
													$message = "Full Update: " . $filename . "\r\n\r\nThe specified size was not found for simple product " . $sku . ". This indicates that either a new size needs to be added to the attribute set or the size was entered in Item Manager incorrectly.";
													sendNotification($subject = 'Size not found', $message);
													fwrite($transactionLogHandle, "  ->NOT SAVED     : Simple       : " . $sku . "\n");
												}
											}
										}
									}
								} else {
									fwrite($transactionLogHandle, "  ->SKIPPING      : " . $itemCounter . "\n");
								}
								fwrite($transactionLogHandle, "  ->ITEM          : " . $itemCounter . "\n");
							} elseif ($startItem <= $itemCounter && $endItem <= $itemCount) {
								//Update stage start
								fwrite($transactionLogHandle, "  ->UPDATE START  : " . $itemCounter . "\n");
								$stageHandle = fopen($catalogLogsDirectory . 'stage', 'w+');
								fwrite($stageHandle, '1:' . $itemCounter);
								fclose($stageHandle);
								break;
							} else {
								fwrite($transactionLogHandle, "  ->SKIPPING      : " . $itemCounter . "\n");
							}
							$itemCounter++;
						}
					}

					// Add newly created simple products to configurable product
					if ($newRelatedProductGroups) {

						$relatedItemCounter = 0;
						$relatedProducts = array();

						foreach ($newRelatedProductGroups as $existingConfigurableProduct => $newRelatedProductGroup) {

							// get product id
							if (!$product = Mage::getModel('catalog/product')->loadByAttribute('sku', $existingConfigurableProduct)) {
								fwrite($transactionLogHandle, "  ->ERROR         : Configurable not found " . $existingConfigurableProduct . "\n");

								// Send email
								$message = "Full Update: " . $filename . "\r\n\r\nThe simple products could not associated because the configurable product " . $existingConfigurableProduct . " was not found.";
								sendNotification($subject = 'Configurable product not found', $message);
							} else {

								// get previous children if any
								$relatedProducts = Mage::getModel('catalog/product_type_configurable')->getChildrenIds($product->getId());

								foreach ($relatedProducts as $relatedProduct) {
									if (count($relatedProduct)) {
										fwrite($transactionLogHandle, "  ->UPDATING      : Related      : " . $existingConfigurableProduct . "\n");
									} else {
										fwrite($transactionLogHandle, "  ->CREATING      : Related      : " . $existingConfigurableProduct . "\n");
									}
								}

								// add new simple product to configurable product
								foreach ($newRelatedProductGroup as $newRelatedProduct) {
									$relatedProducts[0][] = Mage::getModel('catalog/product')->getIdBySku($newRelatedProduct);
								}

								// add all simple product to configurable product
								Mage::getResourceModel('catalog/product_type_configurable')->saveProducts($product, array_unique($relatedProducts[0]));
								fwrite($transactionLogHandle, "  ->SAVED         : Related      : " . $existingConfigurableProduct . "\n");
								unset($relatedProducts);
							}
							fwrite($transactionLogHandle, "  ->ITEM          : " . $relatedItemCounter . "\n");
							$relatedItemCounter++;
						}
					}

					//Change stage
					if ($itemCount <= $itemCounter) {
						//Change stage
						fwrite($transactionLogHandle, "  ->CHANGE STAGE  : 2\n");
						$stageHandle = fopen($catalogLogsDirectory . 'stage', 'w+');
						fwrite($stageHandle, '2:0');
						fclose($stageHandle);
					}
					break;
				case 2: // Process stock update
					//Load adminhtml area of config.xml to initialize adminhtml observers -- required to trigger pagecache
					Mage::app()->loadArea('adminhtml');

					fwrite($transactionLogHandle, "  ->INDEXING      : " . $filename . "\n");
					fwrite($transactionLogHandle, "    ->START       : STOCK\n");

					try {
						$processIds = array(8);

						foreach ($processIds as $processId) {
							$process = Mage::getModel('index/process')->load($processId);
							$process->reindexAll();
						}

						fwrite($transactionLogHandle, "    ->END         : STOCK\n");
						fwrite($transactionLogHandle, "  ->COMPLETED     : " . $filename . "\n");
					} catch (Exception $e) {
						fwrite($transactionLogHandle, "  ->NOT COMPLETED : " . $e . "\n");
					}

					//Change stage
					fwrite($transactionLogHandle, "  ->CHANGE STAGE  : 3\n"); //Skip simple and skip stock update -- reindex and flush cache
					$stageHandle = fopen($catalogLogsDirectory . 'stage', 'w+');
					fwrite($stageHandle, '3:0');
					fclose($stageHandle);

					//Move CSV file to failed directory
					if (file_exists($temporaryDirectory . 'devicom_inventory.csv')) {
						rename($temporaryDirectory . 'devicom_inventory.csv', $processedDirectory . $csvFilename);
					}

					break;
				case 3: // Reindexing and clear cache
//		    //Load adminhtml area of config.xml to initialize adminhtml observers -- required to trigger pagecache
		            Mage::app()->loadArea('adminhtml');

					//Enable indexes
					fwrite($transactionLogHandle, "  ->CHANGE INDEX  :AUTO\n");
					$processCollection = Mage::getSingleton('index/indexer')->getProcessesCollection();
					foreach ($processCollection as $process) {
						$process->setMode(Mage_Index_Model_Process::MODE_REAL_TIME)->save();
					}
					fwrite($transactionLogHandle, "  ->CHANGED INDEX :AUTO\n");

					//Move XML file to processed directory
					rename($receivedDirectory . $filename, $processedDirectory . $filename);

					// remove stage file
					if (file_exists($catalogLogsDirectory . 'stage')) {
						unlink($catalogLogsDirectory . 'stage');
						fwrite($transactionLogHandle, "  ->STAGE REMOVED :\n");
					}
					break;
				default:
			}
		} elseif (isset($products['Simple'])) {

			// Build CSV file from full product update XML file for importing inventory
			$xmlPath = $receivedDirectory . $filename;
			$pathInfo = pathinfo($filename);


			//Run reindexing
			fwrite($transactionLogHandle, "  ->INDEXING      : " . $filename . "\n");

			try {
				$processIds = array(8, 1, 2); // ORDER IS CRITICAL -- STOCK MUST COME FIRST

				foreach ($processIds as $processId) {
					fwrite($transactionLogHandle, "    ->START       : " . $processId . "\n");
					$process = Mage::getModel('index/process')->load($processId);
					$process->reindexAll();
					fwrite($transactionLogHandle, "    ->END         : " . $processId . "\n");
				}

				fwrite($transactionLogHandle, "  ->COMPLETED     : " . $filename . "\n");
			} catch (Exception $e) {
				fwrite($transactionLogHandle, "  ->NOT COMPLETED : " . $e . "\n");
			}

			//Move XML file to processed directory and make copy for ftp directory
			rename($receivedDirectory . $filename, $processedDirectory . $filename);

			//Move CSV file to processed directory
			rename($temporaryDirectory . 'devicom_inventory.csv', $processedDirectory . $csvFilename);

			// Send email
			$message = "Full Update: " . $filename . "\r\n\r\nCOMPLETED!";
			sendNotification($subject = 'FULL INVENTORY UPDATE COMPLETED!', $message);
		} else {
			//Move Invalid XML file to failed directory
			rename($receivedDirectory . $filename, $failedDirectory . $filename);
			fwrite($transactionLogHandle, "  ->INVALID FILE  : No Simple or Configurable items\n");
		}
	} catch (Exception $e) {

		fwrite($transactionLogHandle, "  ->ERROR         : See exception_log\n");

		//Append error to exception log
		$exceptionLogHandle = fopen($catalogLogsDirectory . 'exception_log', 'a');
		fwrite($exceptionLogHandle, '->' . $filename . " - " . $e->getMessage() . "\n");
		fclose($exceptionLogHandle);

		unlink($catalogLogsDirectory . 'stage');

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

function getProductSizeOptionId($eursize){
    $productSizeOptionId = null;
    $attributeProductSize = Mage::getModel('eav/config')->getAttribute('catalog_product', "productsize");
    $optionsProductSize = $attributeProductSize->getSource()->getAllOptions(true, true);
    foreach($optionsProductSize as $key => $eachOption){
        if(empty($eachOption['value'])){
            continue;
        }
        if($eursize == $eachOption['label']){
            $productSizeOptionId = $eachOption['value'];
            break;
        }
    }
    return $productSizeOptionId;
}
function getColorOptionId($strColorSn,$colorMap){
//        $strColorSn = $valueArr['colorSn'];
    if(empty($strColorSn) && empty($colorMap)){
        return null;
    }
    if($colorMap){
        $arrColorSn = array($colorMap);
    }else{
        $strColorSn = str_replace(" ","",$strColorSn);
        $strColorSn = str_replace("-","/",$strColorSn);
        $arrColorSn = explode("/",$strColorSn);
    }
    $attribute = Mage::getModel('eav/config')->getAttribute('catalog_product', "productcolor");
    $options = $attribute->getSource()->getAllOptions(true, true);
    $selectedArr = array();
    foreach($options as $key => $eachValue){
        if(empty($eachValue['value'])) continue;
        if(in_array($eachValue['label'],$arrColorSn)){
            $selectedArr[] = $eachValue['value'];
        }
    }
    return implode(",",$selectedArr);
}
function getAttributeOptionId($attribute,$valueNeed){
    $productSizeOptionId = null;
    $attributeProductSize = Mage::getModel('eav/config')->getAttribute('catalog_product', $attribute);
    $optionsProductSize = $attributeProductSize->getSource()->getAllOptions(true, true);
    foreach($optionsProductSize as $key => $eachOption){
        if(empty($eachOption['value'])){
            continue;
        }
        if($valueNeed == $eachOption['label']){
            $productSizeOptionId = $eachOption['value'];
            break;
        }
    }
    return $productSizeOptionId;
}
?>
