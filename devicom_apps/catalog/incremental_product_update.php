<?php

//Version 1.0 - 3/7/2012

set_time_limit(0); //no timout

require(dirname(dirname(__FILE__)) . '/lib/Devicom/config.php');

$root_dir = dirname(__FILE__);
$catalogLogsDirectory = $root_dir . '/logs/';
$receivedDirectory = $root_dir . '/received/';
$failedDirectory = $root_dir . '/failed/';
$processedDirectory = $root_dir . '/processed/';
$inventoryDirectory = dirname(dirname(__FILE__)) . '/inventory/product/';
if(rtrim(basename(shell_exec('ls ' . $catalogLogsDirectory . '*.lock | head --lines 1')))){
    echo "Log file exist. exit... There is no xml file to be executed \n"; exit;
}

if(rtrim(basename(shell_exec('ls ' . $catalogLogsDirectory . 'stage | head --lines 1')))){
    echo "Full update do not finish. exit... There is no xml file to be executed \n"; exit;
}
if(!$filename = rtrim(basename(shell_exec('ls -t -r ' . $receivedDirectory . 'incremental_product_update* | head --lines 1')))){
    echo "incremental_product_update* file not find. exit... There is no xml file to be executed \n"; exit;
}


//Exit if a simple incremental file is equal to a configurable incremental file or older than full file (if exists)
if ($fullFilename = rtrim(basename(shell_exec('ls -t -r ' . $receivedDirectory . 'full_inventory_update* | head --lines 1')))) {
    if (substr($filename, -22, 8) . substr($filename, -13, 9) > substr($fullFilename, -22, 8) . substr($fullFilename, -13, 9)) {
        exit;
    }
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

    //Get contents of XML file
    $contents = file_get_contents($receivedDirectory . $filename);

    //Create new XML object
    $rootXmlElement = new SimpleXMLElement($contents);

    foreach ($rootXmlElement->children() as $child) {
        $products[$child->getName()] = $child->getName();
    }

    // Initialize magento environment for 'admin' store
    require_once dirname(dirname(dirname(__FILE__))) . '/app/Mage.php';
    umask(0);
    Mage::app('admin'); //admin defines default value for all stores including the Main Website
    //Load adminhtml area of config.xml to initialize adminhtml observers -- required to trigger pagecache
    Mage::app()->loadArea('adminhtml');

    //Get existing websites for accessing store information
    $existingWebsites = Mage::app()->getWebsites(true);

    //Variables to set for additional processing
    $configurableProductsGroupsToUpdate = array();
    $refreshCache = false;

    //Process Configurable products if entity is present
    if (isset($products['Configurable'])) {

        $itemCounter = 1;
        $refreshCache = true;

        //Step into entities array to access configurable products
        foreach ($rootXmlElement->Configurable as $configurableProducts) {

            fwrite($transactionLogHandle, "  ->ITEM          : " . $itemCounter . "\n");

            //Loop through configurable products
            foreach ($configurableProducts->Product as $entity) {

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
                $urlKey = (string) $entity->UrlKey;
                $visibility = 4; // Catalog, Search
                $thirdPartyPrice = $entity->Price;
                $createdAt = $entity->CreatedAt;
                $model = (string) $entity->Model;
                $imageCount = $entity->ImageCount;
                $brand = (string) $entity->Brand;
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
                $metaKeyword = (string) $entity->SEO_Keywords;
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

                    fwrite($transactionLogHandle, "    ->CREATING    : Configurable : " . $sku . "\n");

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
                            fwrite($transactionLogHandle, "    ->ERROR       : Website not found\n");
                            $message = "Incremental Update: " . $filename . "\r\n\r\nThe specified website was not found for configurable product" . $sku . ". This may indicate that the website is not spelled correctly in Item Manager.";
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
                    $product->setUrlKey($urlKey);
                    $product->setVisibility($visibility); // 1 or 4
//                    $product->setCreatedAt($createdAt);
                    $product->setModel($model);
                    $product->setImageCount($imageCount);
                    $product->setBrand($brand);
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
                    $product->setMetaKeyword($metaKeyword);
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
                    if (strpos($websites, 'sneakerrx') !== false && strpos($websites, 'military') !== false) {
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
                        fwrite($transactionLogHandle, "    ->3PARTY PRICE: SET          : " . $sku . "\n");
                    }
                } else {

                    //UPDATE CONFIGURABLE PRODUCT
                    fwrite($transactionLogHandle, "    ->UPDATING    : Configurable : " . $sku . "\n");

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
                            fwrite($transactionLogHandle, "    ->ERROR       : Website not found\n");
                            $message = "Incremental Update: " . $filename . "\r\n\r\nThe specified website was not found for configurable product" . $sku . ". This may indicate that the website is not spelled correctly in Item Manager.";
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
                    $product->setUrlKey($urlKey);
                    $product->setVisibility($visibility); // 1 or 4
//                    $product->setCreatedAt($createdAt);
                    $product->setModel($model);
                    $product->setImageCount($imageCount);
                    $product->setBrand($brand);
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
                    $product->setMetaKeyword($metaKeyword);
                    $product->setLifeSpanFrom($lifeSpanFrom);
                    $product->setLifeSpanTo($lifeSpanTo);
                    $product->setReturnRate($returnRate);
                    $product->setPopularity($popularity);
                    $product->setPriceHistory($priceHistory);
                    $product->setStatus($status); // 1 or 2
                    // Check if configurable is offline -- if so, delete simples
                    if ($isOffline == 1) {
                        $itemFilename = $sku . '.xml';
                        //Remove associated inventory XML file
                        if (file_exists($inventoryDirectory . $itemFilename)) {
                            unlink($inventoryDirectory . $itemFilename);
                            fwrite($transactionLogHandle, "    ->XML REMOVED : " . $itemFilename . "\n");
                        }
                        // Remove records from devicom_inventory table
                        fwrite($transactionLogHandle, "    ->REMOVING    : Inventory    : " . $sku . "\n");
                        $resource = Mage::getSingleton('core/resource');
                        $writeConnection = $resource->getConnection('core_write');
                        $query = "DELETE FROM `devicom_inventory` WHERE `parent_sku` = '" . $sku . "'";
                        $results = $writeConnection->query($query);
                        fwrite($transactionLogHandle, "    ->REMOVED     : Inventory    : " . $sku . "\n");
                    }

                    // Add sku for updating related simple products
                    $configurableProductsGroupsToUpdate[(string) $sku] = Mage::getModel('catalog/product_type_configurable')->getChildrenIds($product->getId());

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
                        fwrite($transactionLogHandle, "    ->3PARTY PRICE: SET          : " . $sku . "\n");
                    }
                }

                fwrite($transactionLogHandle, "    ->SAVED       : Configurable : " . $sku . "\n");
                unset($product);

                $itemCounter++;
            }
        }
    }

    // Update simple product if update to configurable products
    if ($configurableProductsGroupsToUpdate) {

        $itemCounter = 1;

        foreach ($configurableProductsGroupsToUpdate as $existingConfigurableProduct => $configurableProductGroups) {

            // Load configurable product
            if (!$configurableProduct = Mage::getModel('catalog/product')->loadByAttribute('sku', $existingConfigurableProduct)) {
                fwrite($transactionLogHandle, "  ->ERROR         : Configurable not found " . $existingConfigurableProduct . "\n");
                $message = "Incremental Update: " . $filename . "\r\n\r\nThe simple product could not be updated because the configurable product " . $existingConfigurableProduct . " was not found.";
                sendNotification($subject = 'Configurable product not found', $message);
            } else {

                foreach ($configurableProductGroups as $simpleProductGroup) {

                    foreach ($simpleProductGroup as $simpleProductToUpdate) {

                        fwrite($transactionLogHandle, "  ->ITEM          : " . $itemCounter . "\n");

                        if ($product = Mage::getModel('catalog/product')->load($simpleProductToUpdate)) {

                            // If status disabled (2) then delete simple
                            if ($configurableProduct->getIsOffline() == 1) {
                                fwrite($transactionLogHandle, "    ->DELETING    : Simple       : " . $product->getSku() . "\n");
                                $product->delete();
                                fwrite($transactionLogHandle, "    ->DELETED     : Simple       : " . $product->getSku() . "\n");
                            } else {

                                fwrite($transactionLogHandle, "    ->UPDATING    : Simple       : " . $product->getSku() . "\n");

                                //Set size
                                $size = end(explode('-', $product->getSku()));

                                //Set variables to supplied values
                                $storeId = $configurableProduct->getStore()->getId();
                                $websiteIds = $configurableProduct->getWebSiteIds();
                                $categoryIds = $configurableProduct->getStoreCategoryIds();
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
                                    $fullLabelArray = explode(',', $item['label']);
                                    $subLabelArray = explode(' ', $fullLabelArray[0]);
                                    if ($attributeSetName == 'onesize' || $attributeSetName == 'apparelsize') {
                                        if (strtoupper($item['label']) == strtoupper($size) || str_replace(' ', '', strtoupper($item['label'])) == strtoupper($size)) {
                                            $optionAttributeId = $item['value'];
                                            $optionSortOrder = $i;
                                            $sizeFound = 1;
                                            break;
                                        }
                                    } else {
                                        if ($subLabelArray[1] == $size) {
                                            $optionAttributeId = $item['value'];
                                            $optionSortOrder = $i;
                                            $sizeFound = 1;
                                            break;
                                        }
                                    }
                                    $i++;
                                }
                                $product->setData($attributeSetName, $optionAttributeId);

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
                                //Add category ids to devicom_inventory
                                fwrite($transactionLogHandle, "    ->UPDATING    : Inventory    : " . $product->getSku() . "\n");
                                $resource = Mage::getSingleton('core/resource');
                                $writeConnection = $resource->getConnection('core_write');
//									$i = 1;
//									foreach ($categoryIds as $categoryId) {
//										if ($i == 1) {
//											$catIds = $categoryId;
//										} else {
//											$catIds .= ',' . $categoryId;
//										}
//										$i++;
//									}
                                //old price, final price
                                $query = "UPDATE `devicom_inventory` SET `base_price` = " . $product->getFinalPrice() . ", `old_price` = " . $product->getPrice() . ", `category_ids` = '" . $categoryIds . "' WHERE `sku` = '" . $product->getSku() . "'";
                                $results = $writeConnection->query($query);
                                fwrite($transactionLogHandle, "    ->UPDATED     : Inventory    : " . $product->getSku() . "\n");

                                if ($sizeFound) {
                                    //Save product
                                    $product->save();
                                    fwrite($transactionLogHandle, "    ->SAVED       : Simple       : " . $product->getSku() . "\n");
                                    unset($product);
                                } else {
                                    // Send email
                                    $message = "Incremental Update: " . $filename . "\r\n\r\nThe specified size was not found for simple product " . $sku . ". This indicates that either a new size needs to be added to the attribute set or the size was entered in Item Manager incorrectly.";
                                    sendNotification($subject = 'Size not found', $message);
                                    fwrite($transactionLogHandle, "    ->NOT SAVED   : Simple       : " . $product->getSku() . "\n");
                                }
                            }
                        }
                        $itemCounter++;
                    }
                }
            }
        }
    }

    //Create simple products if they do not exist and if configurable is enabled
    if (isset($products['Simple'])) {

        //Step into entities array to access simple products
        foreach ($rootXmlElement->Simple as $simpleProducts) {

            $itemCounter = 1;

            //Loop through simple products
            foreach ($simpleProducts->Product as $entity) {

                fwrite($transactionLogHandle, "  ->ITEM          : " . $itemCounter . "\n");

                //Set sku, size, quantity and isInStock in case this is just an inventory update
                $sku = $entity->Sku;
                $size = end(explode('-', $sku));
                $qty = $entity->Quantity;

                if ($qty > 0) {
                    //Check if simple product already exists
                    if (!$product = Mage::getModel('catalog/product')->loadByAttribute('sku', $sku)) {

                        // Load configurable product
                        if (!$configurableProduct = Mage::getModel('catalog/product')->loadByAttribute('sku', substr($sku, 0, -(strlen($size) + 1)))) {
                            //Send email
                            fwrite($transactionLogHandle, "    ->ERROR       : Configurable not found " . substr($sku, 0, -(strlen($size) + 1)) . "\n");
                            $message = "Incremental Update: " . $filename . "\r\n\r\nThe simple product could not be created because the configurable product " . substr($sku, 0, -(strlen($size) + 1)) . " was not found.";
                            sendNotification($subject = 'Configurable product not found', $message);
                        } else {

                            // Create simple if configurable is not offline
                            if ($configurableProduct->getIsOffline() != 1) {

                                fwrite($transactionLogHandle, "    ->CREATING    : Simple       : " . $sku . "\n");

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
                                    } else {
                                        if ($subLabelArray[1] == $size) {
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

                                // SPECIAL NOTE: FOR INCREMENTAL ONLY
                                // Add item to devicom_inventory table -- qty is initially set to 0
                                fwrite($transactionLogHandle, "    ->ADDING      : Inventory    : " . $sku . "\n");
                                $resource = Mage::getSingleton('core/resource');
                                $writeConnection = $resource->getConnection('core_write');
//									$i = 1;
//									foreach ($categoryIds as $categoryId) {
//										if ($i == 1) {
//											$catIds = $categoryId;
//										} else {
//											$catIds .= ',' . $categoryId;
//										}
//										$i++;
//									}

                                // Add sku as configurable related product if size found
                                if ($sizeFound) {

                                    $newRelatedProductGroups[substr($sku, 0, -(strlen($size) + 1))][] = $sku;
                                    //Save product
                                    $product->save();
                                    fwrite($transactionLogHandle, "    ->SAVED       : Simple       : " . $sku . "\n");

                                    //Insert inventory record -- HAS TO HAPPEN AFTER SAVE TO GET PRODUCT ID
                                    $query = "INSERT INTO `devicom_inventory` (`sku`, `parent_sku`, `parent_product_id`, `base_price`, `old_price`, `product_id`, `attribute_set_name`, `category_ids`, `sort_order`, `option_id`, `label`, `qty`) VALUES ('" . $sku . "', '" . substr($sku, 0, -(strlen($size) + 1)) . "', " . $configurableProduct->getId() . ", " . $configurableProduct->getPrice() . ", " . $configurableProduct->getFinalPrice() . ", " . $product->getId() . ", '" . $attributeSetName . "', '" . $categoryIds . "', " . $optionSortOrder . ", " . $optionAttributeId . ", '" . addslashes($fullLabel) . "', '0')
                    ON DUPLICATE KEY UPDATE `parent_sku` = '" . substr($sku, 0, -(strlen($size) + 1)) . "', `parent_product_id` = " . $configurableProduct->getId() . ", `base_price` = " . $configurableProduct->getFinalPrice() . ", `old_price` = " . $configurableProduct->getPrice() . ", `product_id` = " . $product->getId() . ", `attribute_set_name` = '" . $attributeSetName . "', `category_ids` = '" . $categoryIds . "', `sort_order` = " . $optionSortOrder . ", `option_id` = '" . $optionAttributeId . "', `label` = '" . addslashes($fullLabel) . "', `qty` = '0'";
                                    $results = $writeConnection->query($query);
                                    fwrite($transactionLogHandle, "    ->ADDED       : Inventory    : " . $sku . "\n");

                                    unset($product);
                                } else {
                                    // Send email
                                    $message = "Incremental Update: " . $filename . "\r\n\r\nThe specified size was not found for simple product " . $sku . ". This indicates that either a new size needs to be added to the attribute set or the size was entered in Item Manager incorrectly.";
                                    sendNotification($subject = 'Size not found', $message);
                                    fwrite($transactionLogHandle, "    ->NOT SAVED   : Simple       : " . $sku . "\n");
                                }
                            } else {
                                fwrite($transactionLogHandle, "    ->SKIPPING    : Simple       : " . $sku . "\n");
                            }
                        }
                    } else {
                        fwrite($transactionLogHandle, "    ->SKIPPING    : Simple       : " . $sku . "\n");
                    }
                } else {
                    fwrite($transactionLogHandle, "    ->SKIPPING    : Simple       : " . $sku . "\n");
                }

                $itemCounter++;
            }

            // Add newly created simple products to configurable product
            if ($newRelatedProductGroups) {

                $relatedItemCounter = 1;
                $relatedProducts = array();

                foreach ($newRelatedProductGroups as $existingConfigurableProduct => $newRelatedProductGroup) {

                    fwrite($transactionLogHandle, "  ->ITEM          : " . $relatedItemCounter . "\n");

                    // get product id
                    if (!$product = Mage::getModel('catalog/product')->loadByAttribute('sku', $existingConfigurableProduct)) {
                        fwrite($transactionLogHandle, "  ->ERROR         : Configurable not found " . $existingConfigurableProduct . "\n");

                        // Send email
                        $message = "Incremental Update: " . $filename . "\r\n\r\nThe simple products could not associated because the configurable product " . $existingConfigurableProduct . " was not found.";
                        sendNotification($subject = 'Configurable product not found', $message);
                    } else {

                        // get previous children if any
                        //$relatedProducts = array();
                        //fwrite($transactionLogHandle, "  ->UPDATING      : Related      : " . $existingConfigurableProduct . "\n");
                        // get previous children if any
                        $relatedProducts = Mage::getModel('catalog/product_type_configurable')->getChildrenIds($product->getId());

                        foreach ($relatedProducts as $relatedProduct) {
                            if (count($relatedProduct)) {
                                fwrite($transactionLogHandle, "    ->UPDATING    : Related      : " . $existingConfigurableProduct . "\n");
                            } else {
                                fwrite($transactionLogHandle, "    ->CREATING    : Related      : " . $existingConfigurableProduct . "\n");
                            }
                        }

                        // add new simple product to configurable product
                        foreach ($newRelatedProductGroup as $newRelatedProduct) {
                            $relatedProducts[0][] = Mage::getModel('catalog/product')->getIdBySku($newRelatedProduct);
                        }

                        // add all simple product to configurable product
                        Mage::getResourceModel('catalog/product_type_configurable')->saveProducts($product, array_unique($relatedProducts[0]));
                        fwrite($transactionLogHandle, "    ->SAVED       : Related      : " . $existingConfigurableProduct . "\n");
                        unset($relatedProducts);
                    }
                    $relatedItemCounter++;
                }
            }
        }
    }

    //Process Simple products to update quantities
    if (isset($products['Simple'])) {

        //Step into entities array to access simple products
        foreach ($rootXmlElement->Simple as $simpleProducts) {

            $itemCounter = 1;

            //Loop through simple products
            foreach ($simpleProducts->Product as $entity) {

                fwrite($transactionLogHandle, "  ->ITEM          : " . $itemCounter . "\n");

                //Set sku, size, quantity and isInStock in case this is just an inventory update
                $sku = $entity->Sku;
                $size = end(explode('-', $sku));
                $qty = $entity->Quantity;

                //Check if simple product already exists
                if ($product = Mage::getModel('catalog/product')->loadByAttribute('sku', $sku)) {

                    $resource = Mage::getSingleton('core/resource');
                    $writeConnection = $resource->getConnection('core_write');

                    // If qunatity is greater than 0, update stock else delete simple
                    if ($qty > 0) {

                        fwrite($transactionLogHandle, "    ->UPDATING    : Stock        : " . $sku . "\n");

                        //Set stock
                        $productId = $product->getId();
                        $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($productId);
                        $stockItemId = $stockItem->getId();
                        $stockItem->setData('qty', 6); //force 6
                        $stockItem->setData('is_in_stock', 1); //force in stock
                        //Save stock
                        $stockItem->save();
                        fwrite($transactionLogHandle, "    ->SAVED       : Stock        : " . $sku . "\n");
                        unset($product);
                        unset($stockItem);

                        $query = "UPDATE `devicom_inventory` SET `qty` = '" . $qty . "' WHERE `sku` = '" . $sku . "'";
                        $results = $writeConnection->query($query);
                        fwrite($transactionLogHandle, "    ->UPDATED     : Inventory    : " . $sku . "\n");
                    } else {

                        $query = "UPDATE `devicom_inventory` SET `qty` = 0 WHERE `sku` = '" . $sku . "'";
                        $results = $writeConnection->query($query);
                        fwrite($transactionLogHandle, "    ->UPDATED     : Inventory    : " . $sku . "\n");

                        //Quantity is 0 or less so delete it
                        $product->delete();
                        fwrite($transactionLogHandle, "    ->DEL SIMPLE  : " . $sku . "\n");
                    }

                    // Generate Inventory XML File
                    $parent_sku = substr($sku, 0, -(strlen($size) + 1));

                    $query = "SELECT * FROM `devicom_inventory` WHERE `qty` > 0 AND `parent_sku` = '" . $parent_sku . "' AND `parent_product_id` IS NOT NULL ORDER BY `sort_order` ASC";
                    $results = $writeConnection->fetchAll($query);

                    // Build array from result set
                    $inventoryFound = null;
                    $inventory = array();
                    foreach ($results as $result) {
                        $inventoryFound = 1;

                        $inventory[$result['parent_sku']][$result['sku']]['sku'] = $result['sku'];
                        $inventory[$result['parent_sku']][$result['sku']]['parent_sku'] = $result['parent_sku'];
                        $inventory[$result['parent_sku']][$result['sku']]['product_id'] = $result['product_id'];
                        $inventory[$result['parent_sku']][$result['sku']]['option_id'] = $result['option_id'];
                        $inventory[$result['parent_sku']][$result['sku']]['label'] = $result['label'];
                        $inventory[$result['parent_sku']][$result['sku']]['qty'] = $result['qty'];
                    }

                    if ($inventoryFound) {

                        $query = "SELECT * FROM `devicom_inventory` WHERE `qty` > 0 AND `parent_sku` = '" . $parent_sku . "' AND `parent_product_id` IS NOT NULL GROUP BY `parent_product_id` ORDER BY `sort_order` ASC";
                        $results = $writeConnection->fetchAll($query);
                        foreach ($results as $result) {
                            $parentProductId = $result['parent_product_id'];
                            $basePrice = $result['base_price'];
                            $oldPrice = $result['old_price'];
                            $attributeSetName = $result['attribute_set_name'];
                            $categoryIds = $result['category_ids'];
                        }

                        fwrite($transactionLogHandle, "    ->BUILD FILE  : Inventory    : " . $sku . "\n");

                        foreach ($inventory as $configurable => $simples) {

                            $itemFilename = $configurable . '.xml';

                            //Creates XML string and XML document from the DOM representation
                            $dom = new DomDocument('1.0');
                            $products = $dom->appendChild($dom->createElement('Products'));
                            $info = $products->appendChild($dom->createElement('Info'));

                            $parent_product_id = $info->appendChild($dom->createElement('ParentProductId'));
                            $parent_product_id->appendChild($dom->createTextNode($parentProductId));

                            $base_price = $info->appendChild($dom->createElement('BasePrice'));
                            $base_price->appendChild($dom->createTextNode($basePrice));

                            $old_price = $info->appendChild($dom->createElement('OldPrice'));
                            $old_price->appendChild($dom->createTextNode($oldPrice));

                            $attribute_set_name = $info->appendChild($dom->createElement('AttributeSetName'));
                            $attribute_set_name->appendChild($dom->createTextNode($attributeSetName));

                            $category_ids = $info->appendChild($dom->createElement('CategoryIds'));
                            $category_ids->appendChild($dom->createTextNode($categoryIds));

                            foreach ($simples as $simple) {
                                $product = $products->appendChild($dom->createElement('Product'));

                                $product_id = $product->appendChild($dom->createElement('ProductId'));
                                $product_id->appendChild($dom->createTextNode($simple['product_id']));

                                $option_id = $product->appendChild($dom->createElement('OptionId'));
                                $option_id->appendChild($dom->createTextNode($simple['option_id']));

                                $label_id = $product->appendChild($dom->createElement('Label'));
                                $label_id->appendChild($dom->createTextNode($simple['label']));

                                $quantity = $product->appendChild($dom->createElement('Quantity'));
                                $quantity->appendChild($dom->createTextNode($simple['qty']));
                            }

                            // Make the output pretty
                            $dom->formatOutput = true;

                            // Save the XML string
                            $xml = $dom->saveXML();

                            //Write file to inventory directory
                            $inventoryHandle = fopen($inventoryDirectory . $itemFilename, 'w');
                            fwrite($inventoryHandle, $xml);
                            fclose($inventoryHandle);
                            fwrite($transactionLogHandle, "    ->FILE BUILT  : Inventory    : " . $sku . "\n");
                        }
                    } else {
                        if (file_exists($inventoryDirectory . $parent_sku . '.xml')) {
                            unlink($inventoryDirectory . $parent_sku . '.xml');
                            fwrite($transactionLogHandle, "  ->REMOVED XML   : " . $parent_sku . '.xml' . "\n");
                        }
                    }
                } else {
                    fwrite($transactionLogHandle, "    ->SKIPPING    : Simple       : " . $sku . "\n");
                }
                $itemCounter++;
            }
        }
    }

//	if ($refreshCache) {
//	    try {
//		fwrite($transactionLogHandle, "    ->START       : Full Page Cache\n");
//		//Mage::app()->getCacheInstance()->cleanType('full_page');
//		Enterprise_PageCache_Model_Cache::getCacheInstance()->cleanType('full_page');
//		fwrite($transactionLogHandle, "    ->END         : Full Page Cache\n");
//
//		fwrite($transactionLogHandle, "  ->COMPLETED     : " . $filename . "\n");
//	    } catch (Exception $e) {
//		fwrite($transactionLogHandle, "  ->NOT COMPLETED : " . $e . "\n");
//	    }
//	}
    //Flush all cache (handled by crontab now)
//	shell_exec('rm -fr ' . dirname(dirname(dirname(dirname(__FILE__)))) . '/html/var/cache/');
//	shell_exec('rm -fr ' . dirname(dirname(dirname(dirname(__FILE__)))) . '/html/var/full_page_cache/');
//	$response = shell_exec('echo "flush_all" | nc -U /var/tmp/memcached.rxkicksc.rxkicks.com_cache.sock');
//	if (!$response) {
//	    fwrite($transactionLogHandle, "  ->ERROR         : Memcached not flushed\n");
//	    // Send email
//	    $message = "Incremental Update: " . $filename . "\r\n\r\nMemcached was not flushed successfully during incremental update.";
//	    sendNotification($subject = 'Memcached Not Flushed', $message);
//	} else {
//	    fwrite($transactionLogHandle, "  ->CACHE FLUSHED :\n");
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
?>
