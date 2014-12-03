<?php
class Shoe_Maker_Test_Model_Prepare extends EcomDev_PHPUnit_Test_Case{
    public function testPrepare(){
        //delete simple product 647542-010-Onesize
//        $sku = "647542-010-Onesize";
//        $product = Mage::getModel('catalog/product')->loadByAttribute('sku', $sku);
//        $this->assertNotNull($product);
//
//        $product->delete();
//
//        $product = Mage::getModel('catalog/product')->loadByAttribute('sku', $sku);
//        $this->assertFalse($product);
    }

    public function testEntity(){
        echo "begin";
        //Set sku, size, quantity and isInStock in case this is just an inventory update
        $sku ="599424-303-8.5";
        $arr = explode('-', $sku);
        $size = end($arr);
        $qty = 5;
        if ($qty <= 0) {
//            $this->transactionLogHandle( "    ->SKIPPING    : Simple       : " . $sku . " Because qty equal 0 ");
            return;
        }
        echo "\nbegin2\n";

        //Check if simple product already exists
        $product = Mage::getModel('catalog/product')->loadByAttribute('sku', $sku);
        if($product){
//            $this->transactionLogHandle( "    ->SKIPPING    : Simple       : " . $sku . " Because simple product already exists");
            return $product;
        }
        echo "here";
        // Load configurable product
        $configurableProduct = Mage::getModel('catalog/product')->loadByAttribute('sku', substr($sku, 0, -(strlen($size) + 1)));
        if (!$configurableProduct) {
            //Send email
//            $this->transactionLogHandle( "    ->ERROR       : Configurable not found " . substr($sku, 0, -(strlen($size) + 1)));
            $message = "Incremental Update: " . $this->filename . "\r\n\r\nThe simple product could not be created because the configurable product " . substr($sku, 0, -(strlen($size) + 1)) . " was not found.";
//            $this->sendNotification( 'Configurable product not found', $message);
            return;
        }
        if($configurableProduct->getIsOffline() == 1) {
//            $this->transactionLogHandle( "    ->SKIPPING    : Simple       : " . $sku . " Because configurableProduct offline");
            return;
        }
        // Create simple if configurable is not offline
//        $this->transactionLogHandle("    ->CREATING    : Simple       : " . $sku);

        //Get new product model
        $product = Mage::getModel('catalog/product');

        //Set variables to supplied values
        $storeId = $configurableProduct->getStore()->getId();
        $websiteIds = $configurableProduct->getWebSiteIds();
        $categoryIds = $configurableProduct->getStoreCategoryIds();
        $typeId = 'simple';
        $attributeSetId = $configurableProduct->getAttributeSetId();

        echo $attributeSetId;
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
//        var_dump($options);
        $optionAttributeId = null;
        $fullLabel = null;
        $sizeFound = null; // used to prevent exception when adding related product to configurable when attribute not available
        $i = 0;
//        var_dump($options);
        foreach ($options as $option => $item) {
            if(!$item['label'])
                continue;
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
        var_dump($sizeFound);

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
//        $this->transactionLogHandle( "    ->ADDING      : Inventory    : " . $sku);
        $resource = Mage::getSingleton('core/resource');
        $writeConnection = $resource->getConnection('core_write');

        // Add sku as configurable related product if size found
        if ($sizeFound) {

            $this->newRelatedProductGroups[substr($sku, 0, -(strlen($size) + 1))][] = $sku;
            //Save product
//            $product->save();
//            $this->transactionLogHandle("    ->SAVED       : Simple       : " . $sku);
//            //Insert inventory record -- HAS TO HAPPEN AFTER SAVE TO GET PRODUCT ID
//            $query = "INSERT INTO `devicom_inventory` (`sku`, `parent_sku`, `parent_product_id`, `base_price`, `old_price`, `product_id`, `attribute_set_name`, `category_ids`, `sort_order`, `option_id`, `label`, `qty`) VALUES ('" . $sku . "', '" . substr($sku, 0, -(strlen($size) + 1)) . "', " . $configurableProduct->getId() . ", " . $configurableProduct->getPrice() . ", " . $configurableProduct->getFinalPrice() . ", " . $product->getId() . ", '" . $attributeSetName . "', '" . $categoryIds . "', " . $optionSortOrder . ", " . $optionAttributeId . ", '" . addslashes($fullLabel) . "', '0')
//    ON DUPLICATE KEY UPDATE `parent_sku` = '" . substr($sku, 0, -(strlen($size) + 1)) . "', `parent_product_id` = " . $configurableProduct->getId() . ", `base_price` = " . $configurableProduct->getFinalPrice() . ", `old_price` = " . $configurableProduct->getPrice() . ", `product_id` = " . $product->getId() . ", `attribute_set_name` = '" . $attributeSetName . "', `category_ids` = '" . $categoryIds . "', `sort_order` = " . $optionSortOrder . ", `option_id` = '" . $optionAttributeId . "', `label` = '" . addslashes($fullLabel) . "', `qty` = '0'";
//            $results = $writeConnection->query($query);
//            $this->transactionLogHandle("    ->ADDED       : Inventory    : " . $sku);
            unset($product);
        } else {
            // Send email
            $message = "Incremental Update: " . $this->filename . "\r\n\r\nThe specified size was not found for simple product " . $sku . ". This indicates that either a new size needs to be added to the attribute set or the size was entered in Item Manager incorrectly.";
//            $this->sendNotification('Size not found', $message);
//            $this->transactionLogHandle("    ->NOT SAVED   : Simple       : " . $sku . "\n");
        }
    }
}