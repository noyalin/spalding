<?php
class Shoe_Maker_Model_ConfigurableProduct extends Shoe_Maker_Model_IncrementalUpdate{
    public function executeJob($rootXmlElement){

        $refreshCache = true;
        //Step into entities array to access configurable products
        foreach ($rootXmlElement->Configurable as $configurableProducts) {

            $this->saveEachConfigurableProduct($configurableProducts);
        }

        //Update simple product if update to configurable products
        $this->updateSimpleProductIfUpdateConfigurable();
    }

    public function saveEachConfigurableProduct($configurableProducts){
        $itemCounter = 1;
        foreach ($configurableProducts->Product as $entity) {
            $this->transactionLogHandle("  ->ITEM          : " . $itemCounter);
            $valueArr = $this->getValueAttr($entity);
            $sku = $valueArr['sku'];
            $this->transactionLogHandle("    ->BEGIN       : Configurable : " . $sku);

            //Check if configurable product already exists
            $product = $this->checkProductExist($sku);
            if(!$product){
                $product = $this->saveNewConfigurableProduct($valueArr);
            }else{
                $product = $this->updateConfigurableProduct($product,$valueArr);
            }
            $this->transactionLogHandle("    ->SAVED       : Configurable : " . $sku);
            $entityId = $product->entity_id;
            $this->transactionLogHandle("    ->Entity ID        : " .$entityId );
            unset($product);
            $itemCounter++;
        }
    }

    /**
     * @param $product
     * @param $valueArr
     * Update Logic
     */
    public function updateConfigurableProduct(&$product,$valueArr){
        $sku = $valueArr['sku'];
        $this->transactionLogHandle("    ->UPDATING    : Configurable : " . $sku);

        $this->saveTopAttributes($product,$valueArr);

        // Check if configurable is offline -- if so, delete simples
        if ( $valueArr['isOffline'] == 1 ) {
            $this->removeXml($sku);
            $this->removeInventory($sku);
        }
        $this->saveOtherAttributes($product,$valueArr);
        // Add sku for updating related simple products
        $this->configurableProductsGroupsToUpdate[(string) $sku] = Mage::getModel('catalog/product_type_configurable')->getChildrenIds($product->getId());
        //Save product
        $product->save();

        //save other store
        $this->saveOtherStore($product,$valueArr);
        return $product;
    }

    /**
     * Update simple product if update to configurable products
     *
     */
    public function updateSimpleProductIfUpdateConfigurable(){
        if(!$this->configurableProductsGroupsToUpdate){
            //There is no configurable product to be updated.
            return;
        }
        $configurableProductsGroupsToUpdate = $this->configurableProductsGroupsToUpdate;
        $itemCounter = 1;

        foreach ($configurableProductsGroupsToUpdate as $existingConfigurableProduct => $configurableProductGroups) {
            // Load configurable product
            $configurableProduct = Mage::getModel('catalog/product')->loadByAttribute('sku', $existingConfigurableProduct);
            if(!$configurableProduct){
                $this->transactionLogHandle("  ->ERROR         : Configurable not found " . $existingConfigurableProduct );

                //critical error. Need email
                $message = "Incremental Update: " . $this->filename . "\r\n\r\nThe simple product could not be updated because the configurable product " . $existingConfigurableProduct . " was not found.";
                $subject = 'Configurable product not found';
                $this->sendNotification($subject,$message);
                continue;
            }
            foreach ($configurableProductGroups as $simpleProductGroup) {

                foreach ($simpleProductGroup as $simpleProductToUpdate) {
                    $this->transactionLogHandle("  ->ITEM          : " . $itemCounter);
                    if ($product = Mage::getModel('catalog/product')->load($simpleProductToUpdate)) {
                        $sku = $product->getSku();
                        // If status disabled (2) then delete simple
                        if ($configurableProduct->getIsOffline() == 1) {
                            $this->transactionLogHandle("    ->DELETING    : Simple       : " . $sku );
                            $product->delete();
                            $this->transactionLogHandle("    ->DELETED     : Simple       : " . $sku );
                        } else {
                            $this->transactionLogHandle( "    ->UPDATING    : Simple       : " . $sku );

                            //Set size
                            $arr = explode('-', $sku);
                            $size = end($arr);
                            $eursize = $size;
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
                            //取得configurable product 规格
                            $productNorm = $configurableProduct->getProductNorm();

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
                                if(!$item['value']){
                                    continue;
                                }
//                                $fullLabelArray = explode(',', $item['label']);
//                                $subLabelArray = explode(' ', $fullLabelArray[0]);
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
                            // begin product size
                            $product->setProductsize($this->getProductSizeOptionId($eursize));
                            // end

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
                            $this->transactionLogHandle("    ->UPDATING    : Inventory    : " . $sku);
                            $resource = Mage::getSingleton('core/resource');
                            $writeConnection = $resource->getConnection('core_write');

                            //old price, final price
                            $query = "UPDATE `devicom_inventory` SET `base_price` = " . $product->getFinalPrice() . ", `old_price` = " . $product->getPrice() . ", `category_ids` = '" . $categoryIds . "' WHERE `sku` = '" . $product->getSku() . "'";
                            $results = $writeConnection->query($query);
                            $this->transactionLogHandle("    ->UPDATED     : Inventory    : " . $sku);
                            if ($sizeFound) {
                                //Save product
                                $product->save();
                                $this->transactionLogHandle("    ->SAVED       : Simple       : " . $sku);
                                unset($product);
                            } else {
                                // Send email

                                $message = "Incremental Update: " . $this->filename . "\r\n\r\nThe specified size was not found for simple product " . $sku . ". This indicates that either a new size needs to be added to the attribute set or the size was entered in Item Manager incorrectly.";
                                $this->sendNotification('Size not found', $message);

                                $this->transactionLogHandle("    ->NOT SAVED   : Simple       : " .$sku);
                            }
                        }
                    }
                    $itemCounter++;
                }
            }
        }
    }

    public function checkProductExist($sku){
        
        $product = Mage::getModel('catalog/product')->loadByAttribute('sku', $sku);
        return $product;
    }

    public function saveNewConfigurableProduct($valueArr){
        //Get new product model
        $product = Mage::getModel('catalog/product');
        $this->saveTopAttributes($product,$valueArr);
        $this->transactionLogHandle("    ->Creating    : Configurable : " . $valueArr['sku']);

        //Find and set categoryIds to an existing category contained in tree or don't
        $defaultCategoryTreeArray = explode(',', $valueArr['defaultCategory']);
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
        $sku = $valueArr['sku'];
        //TODO put it into a function
        if (is_null($categoryFound)) {
            // Send email
            $message = "Incremental Update: " . $this->filename . "\r\n\r\nDefault category mapping not created for for simple product " . $sku . ". This indicates that no category ID defined in the products default category tree was found.";
            $this->sendNotification($subject = 'Category not found', $message);
            $this->transactionLogHandle("    ->CATEGORY    : NOT FOUND    : " . $sku . "\n");
        }

        // Set product type
        $product->setTypeId( $valueArr['typeId'] ); //simple or configurable

        // Set attribute set
        $attributeSetName = $valueArr['attributeSetName'];
        $entityTypeId = Mage::getModel('eav/entity')->setType('catalog_product')->getTypeId();
        $attributeSetId = Mage::getModel('eav/entity_attribute_set')->getCollection()->setEntityTypeFilter($entityTypeId)->addFieldToFilter('attribute_set_name', $attributeSetName)->getFirstItem()->getAttributeSetId();
        $product->setAttributeSetId($attributeSetId);

        // Set configurable attributes
        $configAttributeCodes[$attributeSetName] = $attributeSetName;
        $usingAttributeIds = array();

        $attribute = $product->getResource()->getAttribute($attributeSetName);
        $product->setCanSaveConfigurableAttributes(true);
        $configurableAttributesData = array('0' => array('attribute_id' => $attribute->getAttributeId()));
        $product->setConfigurableAttributesData($configurableAttributesData);
        $product->setCanSaveConfigurableAttributes(true);
        $product->setCanSaveCustomOptions(true);

        $this->saveOtherAttributes($product,$valueArr);

        //Set stock
        $product->setStockData(array(
            'is_in_stock' =>  $valueArr['isInStock']
        )); // 0 or 1 -- cannot define qty for configurable -- will be 0

        //Save product
        $product->save();

        $this->saveOtherStore($product,$valueArr);

        return $product;

    }

    /**
     * @param $product
     * @param $valueArr
     * Warp two functions because function saveNewConfigurableProduct
     * and function updateConfigurableProduct both use them
     */
    public function saveTopAttributes(&$product,$valueArr){
        $product->setStoreId($this->getStoreId($valueArr['store']));
        $product->setWebsiteIds($this->getWebsiteIds($valueArr['websites']));
        //Set categories
        $product->setStoreCategoryIds($valueArr['categoryIds']);
    }

    public function getColorOptionId($strColorSn,$colorMap){
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
    /**
     * @param $product
     * @param $valueArr
     * Save attriubte
     */
    public function saveOtherAttributes(&$product,$valueArr){
        //Set everything else
        $product->setName( $valueArr['name']);
        $product->setDescription( $valueArr['description'] );
        $product->setShortDescription( $valueArr['shortDescription'] );
        $product->setSku($valueArr['sku']);
        $product->setWeightSn( $valueArr['weight'] ); // cannot define for configurable -- will be NULL
        $product->setIsOffline( $valueArr['isOffline'] );
        $product->setCustomLayoutUpdate( $valueArr['customLayoutUpdate'] );
        $product->setAbstract( $valueArr['abstract'] );
        $product->setAccessory( $valueArr['accessory'] );
        $product->setMsrp( $valueArr['msrp'] );
        $product->setColorSn( $valueArr['colorSn'] );

        $productColor = $this->getColorOptionId( $valueArr['shColorSn'],$valueArr['shColorMap'] ) ;
        if($productColor){
            $product->setProductcolor( $productColor );
        }
        $product->setVisibility( $valueArr['visibility'] ); // 1 or 4
//                    $product->setCreatedAt($createdAt);
        $product->setModel( $valueArr['model']);
        $product->setImageCount( $valueArr['imageCount'] );
        $product->setBrand( $valueArr['brand'] );
        $product->setProductbrand( $this->getAttributeOptionId('productbrand',$valueArr['brand']) );
        $product->setProductgender( $this->getAttributeOptionId('productgender',$valueArr['shgender']) );
        $product->setTaxClassId( $valueArr['taxClassId'] ); // 0 or 2
        $product->setIsNewArrival( $valueArr['isNewArrival'] );
        $product->setIsOnSale( $valueArr['isOnSale'] );
        $product->setPromotionTag( $valueArr['promotionTag'] );
        $product->setCanonical( $valueArr['canonical'] );
        $product->setUrlKey( $valueArr['urlKey'] );
        //根据URLKEY取得所有的图片
        $skuImage =$valueArr['sku'];
        $this->getAllImagesByUrlkey($valueArr['sku'],$valueArr['urlKey'],$valueArr['imageCount']);
        //image gallery
        $imageCount = $valueArr['imageCount'] ;


//        $urlKeyImageTmp =  $valueArr['urlKey']."-1.jpg";
//        if($imageCount > 1){
//            for($n=1;$n<=$imageCount;$n++){
//                $fullPathImage = Mage::getBaseDir()."/media/catalog/product"."/$skuImage/".$valueArr['urlKey']."-$n.jpg";
//                mage :: log($fullPathImage);
//                $product->addImageToMediaGallery($fullPathImage, array('image','thumbnail','small_image'), false, false) ;
//
//            }
//        }




//        $urlKeyImageTmp =  $valueArr['urlKey']."-2.jpg";
//        $product->addImageToMediaGallery($fullPath."/$skuImage/$urlKeyImageTmp", array('image','thumbnail','small_image'), false, false) ;
//        if($imageCount > 1){
//            $product->setMediaGallery (array('images'=>array (), 'values'=>array ()));
//            for($i=1;$i<=$imageCount;$i++){
//                $urlKeyImageTmp =  $valueArr['urlKey']."-$i.jpg";
//                $product->addImageToMediaGallery("/$skuImage/$urlKeyImageTmp", array('image','thumbnail','small_image'), false, false) ;
//            }
//        }
        //保存图片

        $urlKeyImage = $valueArr['urlKey']."-1.jpg";
        $product->setImage("/$skuImage/$urlKeyImage");
        $product->setSmallImage("/$skuImage/$urlKeyImage");
        $product->setThumbnail("/$skuImage/$urlKeyImage");

//        $fullPathImage1 = Mage::getBaseDir()."/media/catalog/product"."/$skuImage/".$valueArr['urlKey']."-1.jpg";
//        $fullPathImage2 = Mage::getBaseDir()."/media/catalog/product"."/$skuImage/".$valueArr['urlKey']."-2.jpg";
//        $fullPathImage3 = Mage::getBaseDir()."/media/catalog/product"."/$skuImage/".$valueArr['urlKey']."-3.jpg";
//        mage :: log($fullPathImage1);
//        mage :: log($fullPathImage2);
//        $product->addImageToMediaGallery($fullPathImage1, array('image','thumbnail','small_image'), false, false) ;
//        $product->addImageToMediaGallery($fullPathImage2, array('image','thumbnail','small_image'), false, false) ;
//        $product->addImageToMediaGallery($fullPathImage3, array('image','thumbnail','small_image'), false, false) ;


        $product->setSpecialShippingGroup( $valueArr['specialShippingGroup'] ); // 0, 1 or 2
        if ( $valueArr['eligibleForRewards'] == '0') {
            $product->setRewardPoints('0'); //
        } else {
            $product->setRewardPoints(); //
        }
        $product->setEligibleForDiscount( $valueArr['eligibleForDiscount'] ); // 0 or 1
        $product->setPrice( $valueArr['price'] );
        $product->setDefaultCategory( $valueArr['defaultCategory'] );
        $product->setLimitQuantity( $valueArr['limitQuantity'] );
        $product->setPhoneOrderOnly( $valueArr['phoneOrderOnly'] );
        $product->setOrderingMessage( $valueArr['phoneOrderOnlyMessage'] );
        $product->setTestimonial( $valueArr['testimonial'] );
        $product->setMetaDescription( $valueArr['metaDescription'] );
        $product->setMetaKeyword( $valueArr['metaKeyword'] );
        $product->setLifeSpanFrom( $valueArr['lifeSpanFrom'] );
        $product->setLifeSpanTo( $valueArr['lifeSpanTo'] );
        $product->setReturnRate( $valueArr['returnRate'] );
        $product->setPopularity( $valueArr['popularity'] );
        $product->setPriceHistory( $valueArr['priceHistory'] );
        $product->setStatus( $valueArr['status'] ); // 1 or 2

        $product->setProductNorm( $this->getAttributeProductNorm('product_norm',$valueArr['productNorm']) );//规格
        $product->setProductSide( $this->getAttributeOptionId('product_side',$valueArr['productSide']) );//场地
        $product->setProductCatena( $this->getAttributeOptionId('product_catena',$valueArr['productCatena']) );//系列

        //如果是篮球 使用product_material
        if($valueArr['attributeSetName'] == 'ball'){
            $product->setProductMaterial( $this->getProductMaterialOptionId($valueArr['productMaterial']) );//材质 多选
        }else{
            $product->setProductMaterial( $this->getProductMaterialOthersOptionId($valueArr['productMaterial']) );//衣服 包的材质 多选
        }

        //衣服 保存上下装和尺码表
        $product->setApparelType( $this->getAttributeOptionId('apparel_type',$valueArr['apparelType']) );
        $product->setSizeTable( $this->getAttributeOptionId('size_table',$valueArr['sizeTable']) );
    }

    public function getAllImagesByUrlkey($sku,$urlKey,$count){
        $dir = Mage::getBaseDir()."/media/catalog/product/";
        for($i=1;$i<=$count;$i++){
//            $url = "http://image.sneakerhead.com/is/image/sneakerhead/$urlKey-$i?$270$";
            $url = 'http://s7d5.scene7.com/is/image/sneakerhead/spalding1200?$1200x1200$&$imagemoban='."$urlKey-$i";
//            mage :: log($url);
            $needDir = $dir.$sku."/";
            if(!file_exists($needDir)){
                mkdir($needDir);
            }
            $filename = $needDir."$urlKey-$i.jpg";
            if(file_exists($filename)){
                continue;
            }
            $return = $this->grabImage($url,$filename);
            if($return){
                // means save succcess, save database
            }else{
                //重新获取
            }
        }
    }
    function grabImage($url,$filename="") {
        if($url==""):return false;endif;

        if($filename=="") {
            $filename=date("dMYHis").'jpg';
        }

        ob_start();
        readfile($url);
        $img = ob_get_contents();
        ob_end_clean();
        $size = strlen($img);
        if($img && $size){
            $fp2=@fopen($filename, "a");
            fwrite($fp2,$img);
            fclose($fp2);
            return $filename;
        }else{
            return null;
        }



    }
    public function saveOtherStore($product,$valueArr){
        $sku =  $valueArr['sku'];
        $websites = $valueArr['websites'];
        $thirdPartyPrice = $valueArr['thirdPartyPrice'];
        $urlKey = $valueArr['urlKey'];
        $return = false;
        if (strpos($websites, 'sneakerrx') !== false && strpos($websites, 'military') !== false) {
            //Unset and reload product after save
            unset($product);
            $product = Mage::getModel('catalog/product')->loadByAttribute('sku', $sku);
            $product->setUrlKey( $urlKey."-m" );
            $product->setStoreId(1);
            $product->setPrice($thirdPartyPrice);
            //Save product
            $product->save();

            $product->setUrlKey( $urlKey."-s" );
            $product->setStoreId(20);
            $product->setPrice($thirdPartyPrice);
            //Save product
            $product->save();

            $return = true;
        }
        return $return;
    }


    public function getMaxEntityIdFromTableCatalogProductEntity(){
        $resource = Mage::getSingleton('core/resource');
        $writeConnection = $resource->getConnection('core_read');
        $query = "SELECT max(entity_id) FROM catalog_product_entity";
        $results = $writeConnection->fetchOne($query);
        return $results;
    }

    public function getValueAttr($entity){
        $valueArr = array();
        //Set variables to supplied values
        $store = (string) $entity->Store; // 0 or admin -- NO NEED IF HARD-CODED
        $valueArr['store'] =$store;

        $websites = (string) $entity->Websites; // sneakerhead, sneakerrx, military
        $valueArr['websites'] =$websites;

        $categoryIds = (string) $entity->CategoryIds; // ids
        $valueArr['categoryIds'] =$categoryIds;

        $typeId = 'configurable'; // configurable or simple -- NO NEED
        $valueArr['typeId'] =$typeId;

        $attributeSetName = (string) $entity->AttributeSetName; // mensize, womesize, kidsize, bigkidsize, preschoolsize, apparelsize, onesize
        $valueArr['attributeSetName'] =$attributeSetName;

        $name = (string) $entity->Name;
        $valueArr['name'] =$name;

        $description = (string) $entity->Description;
        $valueArr['description'] =$description;

        $shortDescription = (string) $entity->ShortDescription;
        $valueArr['shortDescription'] =$shortDescription;

        $sku = $entity->Sku;
        $valueArr['sku'] =$sku;

        $weight = $this->getWeight((string)$entity->Weight);
        $valueArr['weight'] =$weight;

        list($isOffline,$customLayoutUpdate) = $this->getStatus((string) $entity->Status);
        $valueArr['isOffline'] =$isOffline;
        $valueArr['customLayoutUpdate'] =$customLayoutUpdate;

        $abstract = (string) $entity->Abstract;
        $valueArr['abstract'] =$abstract;

        $accessory = (string) $entity->Accessory;
        $valueArr['accessory'] =$accessory;

        $msrp = $entity->Msrp;
        $valueArr['msrp'] =$msrp;

        $colorSn = (string) $entity->ColorSn;
        $valueArr['colorSn'] =$colorSn;

        $colorMap = (string) $entity->ColorMap;
        $valueArr['colorMap'] =$colorMap;

        $shColorSn = (string) $entity->ShColorSn;
        $valueArr['shColorSn'] =$shColorSn;

        $shColorMap = (string) $entity->ShColorMap;
        $valueArr['shColorMap'] =$shColorMap;

        $urlKey = (string) $entity->UrlKey;
        $valueArr['urlKey'] =$urlKey;

        $visibility = 4; // Catalog, Search
        $valueArr['visibility'] =$visibility;

        $thirdPartyPrice = $entity->Price;
        $valueArr['thirdPartyPrice'] =$thirdPartyPrice;

        $createdAt = $entity->CreatedAt;
        $valueArr['createdAt'] =$createdAt;

        $model = (string) $entity->Model;
        $valueArr['model'] =$model;

        $imageCount = $entity->ImageCount;
        $valueArr['imageCount'] =$imageCount;

        $brand = (string) $entity->Brand;
        $valueArr['brand'] =$brand;

        $shGender = (string) $entity->ShGender;
        $valueArr['shgender'] =$shGender;

        $taxClassId = $entity->Taxable; // 0 None taxable, 2 Taxable Goods
        $valueArr['taxClassId'] =$taxClassId;

        $isNewArrival = $entity->IsNewArrival;
        $valueArr['isNewArrival'] =$isNewArrival;

        $isOnSale = $entity->IsOnSale;
        $valueArr['isOnSale'] =$isOnSale;

        $promotionTag = $entity->PromotionTag;
        $valueArr['promotionTag'] =$promotionTag;

        $canonical = (string) $entity->Canonical;
        $valueArr['canonical'] =$canonical;

        $specialShippingGroup = $entity->FreeShippingTypeId;
        $valueArr['specialShippingGroup'] =$specialShippingGroup;

        $eligibleForRewards = $entity->IsRewardEligible;
        $valueArr['eligibleForRewards'] =$eligibleForRewards;

        $eligibleForDiscount = $entity->IsDiscountEligible;
        $valueArr['eligibleForDiscount'] =$eligibleForDiscount;

        $price = $entity->SalePrice;
        $valueArr['price'] =$price;

        if ((string) $entity->Status == 2) { //  1 online, 2 offline
            $defaultCategory = (string) $entity->CategoryIds;
        } else {
            if($attributeSetName == 'ball'){
                $defaultCategory = "6,10";//篮球规格 和 系列 -
            }else if($attributeSetName == 'accessories'){//配件
                $defaultCategory = "26";
            }else{
                $defaultCategory = (string) $entity->PrimaryCategoryId;
            }
        }
        $valueArr['defaultCategory'] =$defaultCategory;

        $limitQuantity = $entity->OrderLimitCount;
        $valueArr['limitQuantity'] =$limitQuantity;

        $phoneOrderOnly = $entity->IsPhoneOrderOnly;
        $valueArr['phoneOrderOnly'] =$phoneOrderOnly;

        $phoneOrderOnlyMessage = (string) $entity->PhoneOrderOnlyMessage;
        $valueArr['phoneOrderOnlyMessage'] =$phoneOrderOnlyMessage;

        $testimonial = (string) $entity->Testimonial;
        $valueArr['testimonial'] =$testimonial;

        $metaDescription = (string) $entity->SEO_Description;
        $valueArr['metaDescription'] =$metaDescription;

        $metaKeyword = (string) $entity->SEO_Keywords;
        $valueArr['metaKeyword'] =$metaKeyword;

        $lifeSpanFrom = strtotime($entity->LifeFrom);
        $valueArr['lifeSpanFrom'] =$lifeSpanFrom;

        $lifeSpanTo = strtotime($entity->LifeTo);
        $valueArr['lifeSpanTo'] =$lifeSpanTo;

        $returnRate = $entity->ReturnRatePercentage;
        $valueArr['returnRate'] =$returnRate;

        $popularity = $entity->Popularity;
        $valueArr['popularity'] =$popularity;

        $priceHistory = (string) $entity->PriceHistory;
        $valueArr['priceHistory'] =$priceHistory;

        $materialFabric = (string) $entity->MaterialFabric_01;
        $valueArr['materialFabric'] =$materialFabric;

        // Configurable is always in stock
        $status = 1; // 1 enabled, 2 disabled
        $valueArr['status'] =$status;

        $isInStock = 1; // 0 out of stock, 1 in stock
        $valueArr['isInStock'] =$isInStock ;

        $CNMagentoNorm = $entity->CNMagentoNorm;//规格
        $valueArr['productNorm'] =$CNMagentoNorm ;

        $CNMagentoSide = $entity->CNMagentoSide;//场地
        $valueArr['productSide'] =$CNMagentoSide ;

        $CNMagentoCatena = $entity->CNMagentoCatena;//系列
        $valueArr['productCatena'] =$CNMagentoCatena ;

        $MaterialFabric = $entity->MaterialFabric;//材质
        $valueArr['productMaterial'] =$MaterialFabric ;

        $apparelType = $entity->CNMagentoApparelType;//上下装
        $valueArr['apparelType'] =$apparelType;

        $sizeTable = $entity->CNMagentoSizeTable;//尺码表
        $valueArr['sizeTable'] =$sizeTable;

        return $valueArr;
    }

    public function  checkExecuteSign(){
        $catalogLogsDirectory = $this->catalogLogsDirectory;
        $receivedDirectory = $this->receivedDirectory;

        if(count(glob($catalogLogsDirectory . '*.lock')) > 0){
            echo "Log file exist. exit... There is no xml file to be executed \n";
            return true;
        }
        if(file_exists($catalogLogsDirectory . 'stage')){
            echo "Full update do not finish. exit... There is no xml file to be executed \n";
            return true;
        }
        if( count(glob($receivedDirectory . 'incremental_product_update*')) == 0 ){
            echo "incremental_product_update* file not find. exit... There is no xml file to be executed \n";
            return true;
        }
        $filename = rtrim(basename(shell_exec('ls -t -r ' . $receivedDirectory . 'incremental_product_update* | head --lines 1')));
        $this->filename = $filename;
        //Exit if a simple incremental file is equal to a configurable incremental file or older than full file (if exists)
        if (count(glob($receivedDirectory . 'full_inventory_update*'))>0 && $fullFilename = rtrim(basename(shell_exec('ls -t -r ' . $receivedDirectory . 'full_inventory_update* | head --lines 1')))) {
            if (substr($filename, -22, 8) . substr($filename, -13, 9) > substr($fullFilename, -22, 8) . substr($fullFilename, -13, 9)) {
                return true;
            }
        }
        return false;
    }
    public function getProductListFromXml(){
        $contents = file_get_contents($this->receivedDirectory . $this->filename);

        $rootXmlElement = new SimpleXMLElement($contents);
        $products = array();
        foreach ($rootXmlElement->children() as $child) {
            $products[$child->getName()] = $child->getName();
        }
        return array($rootXmlElement,$products);
    }

    public function echoMsg(){
        return  "COOl";
    }

    /**
     * Delete all the product in order to test create new product
     */
    public function deleteAllProduct(){
        $collection = Mage::getModel('catalog/product')
            ->getCollection();

        foreach ($collection as $product) {
            $product->delete();
        }
    }



}