<?php
/**
 * Created by PhpStorm.
 * User: fang
 * Date: 5/1/14
 * Time: 3:21 PM
 */
class Shoe_Maker_Model_IncrementalUpdate extends  Shoe_Maker_Model_UpdateBase{

//    public static function executeIncremental($contents,$object){
//        list($rootXmlElement,$products) = self :: getXmlElementFromString($contents);
//        if (isset($products['Configurable'])) {
//            $configurableProduct = new Shoe_Maker_Model_ConfigurableProduct($object);
//            $configurableProduct->run($rootXmlElement);
//        }
//        if (isset($products['Simple'])) {
//            $simpleProduct = new Shoe_Maker_Model_SimpleProduct($object);
//            $simpleProduct->run($rootXmlElement);
//        }
//    }

    public  function run(){
        list($rootXmlElement,$products) = self :: getXmlElementFromString($this->contents);
        if (isset($products['Configurable'])) {
            $configurableProduct = new Shoe_Maker_Model_ConfigurableProduct();
            $configurableProduct->executeJob($rootXmlElement);
        }
        if (isset($products['Simple'])) {
            $simpleProduct = new Shoe_Maker_Model_SimpleProduct();
            $simpleProduct->executeJob($rootXmlElement);
        }
    }

    /**
     * Update configurable product.
     * Once it is isOffline equal true, need delete the xml in the folder inventory/product
     */
    public function removeXml($sku){
        $itemFilename = $sku . '.xml';
        $inventoryDirectory =   dirname(dirname(__FILE__)) . '/inventory/product/';
        //Remove associated inventory XML file
        if (file_exists($inventoryDirectory . $itemFilename)) {
            unlink($inventoryDirectory . $itemFilename);
            $this->transactionLogHandle("    ->XML REMOVED : " . $itemFilename);
        }
    }
    /**
     * Update configurable product.
     * Once it is isOffline equal true, need Remove records from devicom_inventory table
     */
    public function removeInventory($sku){
        $this->transactionLogHandle( "->REMOVING    : Inventory    : " . $sku);
        $resource = Mage::getSingleton('core/resource');
        $writeConnection = $resource->getConnection('core_write');
        $query = "DELETE FROM `devicom_inventory` WHERE `parent_sku` = '" . $sku . "'";
        $results = $writeConnection->query($query);
        $this->transactionLogHandle( "    ->REMOVED     : Inventory    : " . $sku);
    }

    public function getWebsiteIds($websites){
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
                $this->transactionLogHandle("  $websiteCode  ->ERROR       : Website not found\n");
                $message = "Incremental Update: " . $this->filename . "\r\n\r\nThe specified website was not found for configurable product" . $sku . ". This may indicate that the website is not spelled correctly in Item Manager.";
                $this->sendNotification($subject = 'Website not found', $message);
            }
        }
        return $websiteIds;
    }

    public function getStatus($status){
        $isOffline = null;
        $customLayoutUpdate = null;
        if ($status == 2) { //  1 online, 2 offline
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
        return array($isOffline,$customLayoutUpdate);
    }

    public function getStoreId($store){
        $existingWebsites = Mage::app()->getWebsites(true);
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

        return $storeId;
    }

    public function getWeight($weight){
        if ($weight == 2.5) {
            $weight = 4;
        }else if ($weight == 1.00) {
            $weight = 4;
        }  elseif ($weight == .5) {
            $weight = 1;
        } else {
            $this->transactionLogHandle("    ->ERROR       : Weight not found");
        }
        return $weight;
    }

    public function removeLockFile(){
        //Remove process.lock to allow processing of incremental and/or full updates
        $diskFile = $this->catalogLogsDirectory . $this->processLockFilename;
        if (file_exists($diskFile)) {
            unlink($diskFile);
            $this->transactionLogHandle(  "  ->LOCK REMOVED  : " . $this->processLockFilename  );
        }

        $endTime = date("Y-m-d H:i:s");
        $realTime = $this->realTime();
        $this->transactionLogHandle(  "  ->FINISH TIME   : " . $realTime[5] . '/' . $realTime[4] . '/' . $realTime[3] . ' ' . $realTime[2] . ':' . $realTime[1] . ':' . $realTime[0]);
        $this->transactionLogHandle( "->END PROCESSING  : " . $this->filename . "\n");

        //Close transaction log
        fclose($this->transactionLogHandle);
    }

    public function validate(){
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

    //取得篮球规格
    function getAttributeProductNorm($attribute,$valueNeed){
        if(!trim($valueNeed))
            return null;
        $productSizeOptionId = null;
        $attributeProductSize = Mage::getModel('eav/config')->getAttribute('catalog_product', $attribute);
        $optionsProductSize = $attributeProductSize->getSource()->getAllOptions(true, true);
        foreach($optionsProductSize as $key => $eachOption){
            if(empty($eachOption['value'])){
                continue;
            }
            $valueNeed =  str_replace("#","",$valueNeed);
            if(strstr($eachOption['label'],$valueNeed) ){
                $productSizeOptionId = $eachOption['value'];
                break;
            }
        }
        return $productSizeOptionId;
    }
    //取得篮球材质
    public function getProductMaterialOptionId($value){
        $arr = explode(",",$value);
        $attribute = Mage::getModel('eav/config')->getAttribute('catalog_product', "product_material");
        $options = $attribute->getSource()->getAllOptions(true, true);
        $selectedArr = array();
        foreach($options as $key => $eachValue){
            if(empty($eachValue['value'])) continue;
            if(in_array($eachValue['label'],$arr)){
                $selectedArr[] = $eachValue['value'];
            }
        }
        return implode(",",$selectedArr);
    }
    //取得包和衣服材质
    public function getProductMaterialOthersOptionId($value){
        $arr = explode(",",$value);
        $attribute = Mage::getModel('eav/config')->getAttribute('catalog_product', "product_material_others");
        $options = $attribute->getSource()->getAllOptions(true, true);
        $selectedArr = array();
        foreach($options as $key => $eachValue){
            if(empty($eachValue['value'])) continue;
            if(in_array($eachValue['label'],$arr)){
                $selectedArr[] = $eachValue['value'];
            }
        }
        return implode(",",$selectedArr);
    }
    function getAttributeOptionId($attribute,$valueNeed){
        if(!$valueNeed)
            return null;
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
    public function getProductNormLabel($optionId){
        $attribute = Mage::getModel('eav/config')->getAttribute('catalog_product', "product_norm");
        $options = $attribute->getSource()->getAllOptions(true, true);
        $selectedArr = array();
        foreach($options as $key => $eachValue){
            if($eachValue['value'] == $optionId){
                return $eachValue['label'];
            }
        }
        return null;
    }
}