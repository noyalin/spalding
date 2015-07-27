<?php
class Shoe_Maker_Model_SimpleProduct extends Shoe_Maker_Model_IncrementalUpdate{
    public $newRelatedProductGroups;

    private $_mailMessage = "";

    public function executeJob($rootXmlElement){

        $this->_mailMessage = "";

        try{
            //Step into entities array to access simple products
            foreach ($rootXmlElement->Simple as $simpleProducts) {
                $itemCounter = 1;
                foreach ($simpleProducts->Product as $entity) {
                    $this->transactionLogHandle( "  ->ITEM          : " . $itemCounter );
                    $this->handleEachSimpleEntity($entity);
                    $itemCounter++;
                }
                $this->updateRelatedProductGroups();
            }
        } catch (Exception $ex) {
            $this->_mailMessage .= ("\r\nexecuteJob Exception: ".$ex->getMessage());
        }

        if ($this->_mailMessage != "") {
            $this->_mailMessage = ("executeJob ( 1 ) Incremental Update: " . $this->filename .$this->_mailMessage);
            $this->sendNotification( 'Configurable product Error ', $this->_mailMessage);
        }


        $this->_mailMessage = "";
        try{
            //Process Simple products to update quantities
            foreach ($rootXmlElement->Simple as $simpleProducts) {
                $itemCounter = 1;
                foreach ($simpleProducts->Product as $entity) {
                    $this->transactionLogHandle( "  ->ITEM  Quantity         : " . $itemCounter );
                    $this->handleUpdateQuantityForEachSimpleEntity($entity);
                    $itemCounter++;
                }
            }
        } catch (Exception $ex) {
            $this->_mailMessage .= ("\r\nexecuteJob Exception: ".$ex->getMessage());
        }

        if ($this->_mailMessage != "") {
            $this->_mailMessage = ("executeJob ( 2 ) Incremental Update: " . $this->filename .$this->_mailMessage);
            $this->sendNotification( 'Configurable product UpdateQuantity Error ', $this->_mailMessage);
        }
    }

    public function handleEachSimpleEntity($entity){
        //Set sku, size, quantity and isInStock in case this is just an inventory update
        $sku = $entity->Sku;

        $arr = explode('-', $sku);
        $size = end($arr);
        $eursize = $size;
        $qty = $entity->Quantity;
        $optionSize = $size;
        if( $entity->Size ){
            $optionSize = $entity->Size;
        }
        if ($qty <= 0) {
            $this->transactionLogHandle( "    ->SKIPPING    : Simple       : " . $sku . " Because qty equal 0 ");
            return;
        }

        //Check if simple product already exists
        $product = Mage::getModel('catalog/product')->loadByAttribute('sku', $sku);
        if($product){
            $this->transactionLogHandle( "    ->SKIPPING    : Simple       : " . $sku . " Because simple product already exists");
            return $product;
        }

        // Load configurable product
        $configurableProduct = Mage::getModel('catalog/product')->loadByAttribute('sku', substr($sku, 0, -(strlen($size) + 1)));
        if (!$configurableProduct) {
            //Send email
            $this->transactionLogHandle( "    ->ERROR       : Configurable not found " . substr($sku, 0, -(strlen($size) + 1)));
            $this->_mailMessage = "\r\nThe simple product could not be created because the configurable product " . substr($sku, 0, -(strlen($size) + 1)) . " was not found.";
            return;
        }
        if($configurableProduct->getIsOffline() == 1) {
            $this->transactionLogHandle( "    ->SKIPPING    : Simple       : " . $sku . " Because configurableProduct offline");
            return;
        }
        // Create simple if configurable is not offline
        $this->transactionLogHandle("    ->CREATING    : Simple       : " . $sku);

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

        //取得configurable product 规格
        $productNorm = $configurableProduct->getProductNorm();
        $labelProductNorm = $this->getProductNormLabel($productNorm);


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
        //如果是篮球 使用product_material
        if($attributeSetName == 'ball'){
            $labelProductNorm = $this->getProductNormLabel($productNorm);
        }else{
            $labelProductNorm = $eursize;
        }
        // Set size attribute option
        $attribute = Mage::getModel('eav/config')->getAttribute('catalog_product', $attributeSetName);
        $options = $attribute->getSource()->getAllOptions(true, true);
        $optionAttributeId = null;
        $fullLabel = null;
        $sizeFound = null; // used to prevent exception when adding related product to configurable when attribute not available
        $i = 0;
        foreach ($options as $option => $item) {
            if(!$item['label'])
                continue;
//            $fullLabelArray = explode(',', $item['label']);
//            $subLabelArray = explode(' ', $fullLabelArray[0]);
                if ($item['label']== $labelProductNorm) {
                    $fullLabel = $item['label'];
                    $optionAttributeId = $item['value'];
                    $optionSortOrder = $i;
                    $sizeFound = 1;
                    break;
                }
            $i++;
        }
        $product->setData($attributeSetName, $optionAttributeId);
        $product->setEursize($eursize);
        // begin product size
        $product->setProductsize($this->getProductSizeOptionId($eursize));
        // end
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
        $this->transactionLogHandle( "    ->ADDING      : Inventory    : " . $sku);
        $resource = Mage::getSingleton('core/resource');
        $writeConnection = $resource->getConnection('core_write');

        // Add sku as configurable related product if size found
        if ($sizeFound) {

            $this->newRelatedProductGroups[substr($sku, 0, -(strlen($size) + 1))][] = $sku;
            //Save product
            $product->save();
            $this->transactionLogHandle("    ->SAVED       : Simple       : " . $sku);
            //Insert inventory record -- HAS TO HAPPEN AFTER SAVE TO GET PRODUCT ID
            $query = "INSERT INTO `devicom_inventory` (`sku`, `parent_sku`, `parent_product_id`, `base_price`, `old_price`,
            `product_id`,
            `attribute_set_name`,
            `category_ids`,
            `sort_order`,
            `option_id`,
            `label`, `qty`) VALUES
            ('" . $sku . "', '" . substr($sku, 0, -(strlen($size) + 1)) . "', " .
                $configurableProduct->getId() . ", " .
                $configurableProduct->getPrice() . ", " .
                111 . ", " .
                $product->getId() . ", '" .
                $attributeSetName . "', '" .
                $categoryIds . "', " .
                $optionSortOrder . ", " .
                $optionAttributeId . ", '" .
                addslashes($fullLabel) . "', '0')
    ON DUPLICATE KEY UPDATE `parent_sku` = '" . substr($sku, 0, -(strlen($size) + 1)) . "', `parent_product_id` = " . $configurableProduct->getId() . ", `base_price` = " . 11 . ", `old_price` = " . $configurableProduct->getPrice() . ", `product_id` = " . $product->getId() . ", `attribute_set_name` = '" . $attributeSetName . "', `category_ids` = '" . $categoryIds . "', `sort_order` = " . $optionSortOrder . ", `option_id` = '" . $optionAttributeId . "', `label` = '" . addslashes($fullLabel) . "', `qty` = '0'";
            $results = $writeConnection->query($query);
            $this->transactionLogHandle("    ->ADDED       : Inventory    : " . $sku);
            unset($product);
        } else {
            // Send email
            $this->_mailMessage = "\r\nThe specified size was not found for simple product " . $sku . ". This indicates that either a new size needs to be added to the attribute set or the size was entered in Item Manager incorrectly.";
//            $this->sendNotification('Size not found', $message);
            $this->transactionLogHandle("    ->NOT SAVED   : Simple       : " . $sku . " because $size not found\n");
        }
    }

    public function updateRelatedProductGroups(){
        $newRelatedProductGroups = $this->newRelatedProductGroups;
        if ($newRelatedProductGroups) {

            $relatedItemCounter = 1;
            $relatedProducts = array();

            foreach ($newRelatedProductGroups as $existingConfigurableProduct => $newRelatedProductGroup) {

                $this->transactionLogHandle( "  ->ITEM          : " . $relatedItemCounter);

                // get product id
                if (!$product = Mage::getModel('catalog/product')->loadByAttribute('sku', $existingConfigurableProduct)) {
                    $this->transactionLogHandle(  "  ->ERROR         : Configurable not found " . $existingConfigurableProduct);

                    // Send email
                    $this->_mailMessage = "\r\nThe simple products could not associated because the configurable product " . $existingConfigurableProduct . " was not found.";
//                    $this->sendNotification( 'Configurable product not found', $message);
                } else {

                    // get previous children if any
                    //$relatedProducts = array();
                    //fwrite($transactionLogHandle, "  ->UPDATING      : Related      : " . $existingConfigurableProduct . "\n");
                    // get previous children if any
                    $relatedProducts = Mage::getModel('catalog/product_type_configurable')->getChildrenIds($product->getId());

                    foreach ($relatedProducts as $relatedProduct) {
                        if (count($relatedProduct)) {
                            $this->transactionLogHandle(  "    ->UPDATING    : Related      : " . $existingConfigurableProduct  );
                        } else {
                            $this->transactionLogHandle( "    ->CREATING    : Related      : " . $existingConfigurableProduct  );
                        }
                    }

                    // add new simple product to configurable product
                    foreach ($newRelatedProductGroup as $newRelatedProduct) {
                        $relatedProducts[0][] = Mage::getModel('catalog/product')->getIdBySku($newRelatedProduct);
                    }

                    // add all simple product to configurable product
                    Mage::getResourceModel('catalog/product_type_configurable')->saveProducts($product, array_unique($relatedProducts[0]));
                    $this->transactionLogHandle( "    ->SAVED       : Related      : " . $existingConfigurableProduct );
                    unset($relatedProducts);
                }
                $relatedItemCounter++;
            }
        }
    }

    public function handleUpdateQuantityForEachSimpleEntity($entity){

        //Set sku, size, quantity and isInStock in case this is just an inventory update
        $sku = $entity->Sku;
        $arr = explode('-', $sku);
        $size = end($arr);
        $qty = $entity->Quantity;

        //Check if simple product already exists
        $product = Mage::getModel('catalog/product')->loadByAttribute('sku', $sku);
        if(!$product) {
            $this->transactionLogHandle( "    ->SKIPPING    : Simple       : " . $sku );
            return;
        }

        $resource = Mage::getSingleton('core/resource');
        $writeConnection = $resource->getConnection('core_write');

        Mage::getModel('inventory/physical')->updateQtyBySku($sku, $qty);

        // If qunatity is greater than 0, update stock else delete simple
        if ($qty > 0) {

            $this->transactionLogHandle( "    ->UPDATING    : Stock        : " . $sku  );
            //Set stock
            $productId = $product->getId();
            $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($productId);
            $stockItemId = $stockItem->getId();
            $stockItem->setData('qty', $qty);
            $stockItem->setData('is_in_stock', 1); //force in stock
            //Save stock
            $stockItem->save();
            $this->transactionLogHandle( "    ->SAVED       : Stock        : " . $sku );
            unset($product);
            unset($stockItem);

            $query = "UPDATE `devicom_inventory` SET `qty` = '" . $qty . "' WHERE `sku` = '" . $sku . "'";
            $results = $writeConnection->query($query);
            $this->transactionLogHandle(  "    ->UPDATED     : Inventory    : " . $sku  );
        } else {
            $query = "UPDATE `devicom_inventory` SET `qty` = 0 WHERE `sku` = '" . $sku . "'";
            $results = $writeConnection->query($query);
            $this->transactionLogHandle( "    ->UPDATED     : Inventory    : " . $sku  );

            //Quantity is 0 or less so delete it
            $product->delete();
            $this->transactionLogHandle(  "    ->DEL SIMPLE  : " . $sku  );
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

        $inventoryDirectory = $this->inventoryDirectory;
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

            $this->transactionLogHandle( "    ->BUILD FILE  : Inventory    : " . $sku  );

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
                $this->transactionLogHandle( "    ->FILE BUILT  : Inventory    : " . $sku );
            }
            shell_exec("chmod -R 777 $inventoryDirectory ");
        } else {
            if (file_exists($inventoryDirectory . $parent_sku . '.xml')) {
                unlink($inventoryDirectory . $parent_sku . '.xml');
                $this->transactionLogHandle( "  ->REMOVED XML   : " . $parent_sku . '.xml' );
            }
        }
    }
    /**
     * Delete all the product in order to test create new product
     */
    public function deleteAllSimpleProduct(){
        $collectionConfigurable = Mage :: getResourceModel('catalog/product_collection')
            ->addAttributeToFilter('type_id',array('eq' => 'configurable'));
        foreach($collectionConfigurable as $_configurableProduct){
            $productId = $_configurableProduct->getId();
            $product = Mage :: getModel('catalog/product')->load($productId);
            if($product->getTypeId() == "configurable"){
                $conf = Mage :: getModel('catalog/product_type_configurable')->setProduct($product);
                $simpleCollection = $conf->getUsedProductCollection()
                    ->addAttributeToSelect('*')
                    ->addFilterByRequiredOptions();
                foreach($simpleCollection as $simpleProduct){
                    $simpleProduct->delete();
                }
            }
        }
    }



}