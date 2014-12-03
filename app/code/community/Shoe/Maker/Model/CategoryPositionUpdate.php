<?php
class Shoe_Maker_Model_CategoryPositionUpdate extends Shoe_Maker_Model_UpdateBase{
    public $categories;
    public $reindex = NULL;
    public function run(){
        set_time_limit(0); //no timout
        ini_set('memory_limit', '1024M');
        $this->getCategories();
        if(empty($this->categories)){
            return;
        }
        $this->transactionLogHandle( "  ->CHECKING CATEGORIES... ");

        $this->checkCategories();

        $this->transactionLogHandle( "  ->ACQUIRING RESOURCE CONNECTION... ");

        $this->checkProduct();

        $this->updatePosition();

        $this->reIndex();
    }

    public function getCategories(){
        list($rootXmlElement,$entityTypes) = self :: getXmlElementFromString($this->contents);
        $categories = array();
        //Process categories if entity is present
        if (isset($entityTypes['Categories'])) {

            $this->transactionLogHandle( "  ->BUILDING ARRAY... ");

            //Step into entities array to access configurable products
            foreach ($rootXmlElement->Categories as $entities) {

                //Loop through categories
                foreach ($entities->Category as $entity) {
                    $categoryId = (string) $entity->Id;
                    $categories[$categoryId]['category_id'] = $categoryId;
                    $categories[$categoryId]['product_codes'] = trim((string) $entity->ProductCodes);
                }
            }
        }
        $this->categories = $categories;

    }

    public function checkCategories(){
        $categories = $this->categories;
        //Check if all categories exist else bail
        foreach ($categories as $categoryArray) {
            //get a new category object
            $category = Mage::getModel('catalog/category')->load($categoryArray['category_id']);
            if (!$category->getId()) {
                $this->transactionLogHandle( "  ->CATEGORY NOT FOUND: " . $categoryArray['category_id']  );

                //Remove process.lock to allow processing of incremental and/or full updates
                if (file_exists($this->catalogLogsDirectory . $this->processLockFilename)) {
                    unlink($this->catalogLogsDirectory . $this->processLockFilename);
                    $this->transactionLogHandle( "  ->LOCK REMOVED  : " . $this->processLockFilename . "\n");
                }

                $endTime = realTime();
                $this->transactionLogHandle( "  ->FINISH TIME   : " . $endTime[5] . '/' . $endTime[4] . '/' . $endTime[3] . ' ' . $endTime[2] . ':' . $endTime[1] . ':' . $endTime[0] . "\n");
                $this->transactionLogHandle( "->END PROCESSING  : " . $this->filename . "\n");

                //Close transaction log
                fclose($this->transactionLogHandle);
                exit;
            }
        }
    }

    public function checkProduct(){
        $categories = $this->categories;
        // Get resource
        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');
        $writeConnection = $resource->getConnection('core_write');

        $this->transactionLogHandle( "  ->CHECKING PRODUCTS... ");
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
                                $this->transactionLogHandle( "  ->PRODUCT NOT FOUND : " . $productCodesArray[$i] );

                                //Remove process.lock to allow processing of incremental and/or full updates
                                if (file_exists($this->catalogLogsDirectory . $this->processLockFilename)) {
                                    unlink($this->catalogLogsDirectory . $this->processLockFilename);
                                    fwrite($this->transactionLogHandle, "  ->LOCK REMOVED  : " . $this->processLockFilename . "\n");
                                }

                                $endTime = $this->realTime();
                                $this->transactionLogHandle( "  ->FINISH TIME   : " . $endTime[5] . '/' . $endTime[4] . '/' . $endTime[3] . ' ' . $endTime[2] . ':' . $endTime[1] . ':' . $endTime[0] . "\n");
                                $this->transactionLogHandle( "->END PROCESSING  : " . $this->filename . "\n");

                                //Close transaction log
                                //TODO real evn need control the count at home page
//                                fclose($this->transactionLogHandle);
//                                exit;
                            }
                        }
                    }
                    $i++;
                }
            }
        }
    }

    //TODO unit tst
    public function updatePosition(){
        $this->transactionLogHandle( "  ->UPDATING POSITIONING...");
        //Update Positioning if processing did not get stopped due to missing products for home or what's hot categories
        $categories = $this->categories;
        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');
        $writeConnection = $resource->getConnection('core_write');
        foreach ($categories as $categoryArray) {
            $productCodesArray = explode(',', $categoryArray['product_codes']);
            $count = count($productCodesArray);
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
                $this->reindex = 1;
                $this->transactionLogHandle( "    ->UPDATED               ->" . $categoryArray['category_id'] . "\n");
            } elseif ($categoryArray['category_id'] != 573 && $categoryArray['category_id'] != 543 && $categoryArray['category_id'] != 544 && $categoryArray['category_id'] != 545 && $categoryArray['category_id'] != 2764) {

                //Find mapping not in new list
                $productCodes = str_replace(",", "','", $categoryArray['product_codes']);
                $finalString .= "'" . $productCodes . "'";
                $deleteCategoryProductQuery = "DELETE `ccp` FROM `catalog_category_product` AS `ccp`
                LEFT JOIN `catalog_product_entity` AS `cpe` ON `ccp`.`product_id` = `cpe`.`entity_id`
                where category_id = " . $categoryArray['category_id'] . " AND `cpe`.`sku` NOT IN (" . $finalString . ")";
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
                                $this->transactionLogHandle($productCodesArray[$i]. "    ->PRODUCT NOT FOUND   ->" . $categoryArray['category_id'] );
                            }
                        }
                    }
                    $i++;
                }
                $this->reindex = 1;
                $this->transactionLogHandle( "    ->UPDATED               ->" . $categoryArray['category_id']  );
            } elseif (($categoryArray['category_id'] == 573 && $count != 12) || ($categoryArray['category_id'] == 543 && $count != 15) || ($categoryArray['category_id'] == 544 && $count != 15) || ($categoryArray['category_id'] == 545 && $count != 15) || ($categoryArray['category_id'] == 2764 && $count != 16)) {
                $this->transactionLogHandle( "    ->WRONG COUNT           ->" . $categoryArray['category_id'] );
            } else {
                $this->transactionLogHandle( "    ->ERROR       : UNKNOWN");

                $message = "Category Positiong: " . $this->filename . "\r\n\r\nCategory " . $categoryArray['category_id'] . " could not be matched for updating. I have no idea what this would indicate. That is why I am making a notiifcation.";
                $this->sendNotification('Criteria Not Matched - Not Updated', $message);
            }
        }
        $this->transactionLogHandle( "  ->POSITIONING UPDATED... ");
    }

    public function reIndex(){
        $reindex = $this->reindex;
        if ($reindex) {
            //Run reindexing
            $this->transactionLogHandle( "  ->INDEXING      : " . $this->filename  );
            try {
                $processIds = array(6); // Category_products

                foreach ($processIds as $processId) {
                    $this->transactionLogHandle( "    ->START       : " . $processId );
                    $process = Mage::getModel('index/process')->load($processId);
                    $process->reindexAll();
                    $this->transactionLogHandle( "    ->END         : " . $processId  );
                }

                $this->transactionLogHandle( "  ->COMPLETED     : " . $this->filename );

                //REFRESH block level cache (refreshes category and product detail pages)-- This will be targeted by ID in the future
                //Note full_page doesn't seem to be required???
                $types = array(
                    2 => "block_html",
                    7 => "full_page"
                );

                if (!empty($types)) {
                    $this->transactionLogHandle( "  ->CACHE         : START ");
                    foreach ($types as $type) {
                        Mage::app()->getCacheInstance()->cleanType($type);
                        Enterprise_PageCache_Model_Cache::getCacheInstance()->cleanType($type);
                        //Mage::dispatchEvent('adminhtml_cache_refresh_type', array('type' => $type));
                        $this->transactionLogHandle( "  ->CACHE TYPE    : " . $type  );
                    }
                    $this->transactionLogHandle( "  ->CACHE         : END ");
                }
            } catch (Exception $e) {
                $this->transactionLogHandle( "  ->NOT COMPLETED : " . $e  );
            }
        }
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
        if( count(glob($receivedDirectory . 'incremental_product_update*')) > 0 ){
            echo "incremental_product_update file  find. Incremental does not finish. exit... There is no xml file to be executed \n";
            return true;
        }
        if( count(glob($receivedDirectory . 'full_inventory_update*')) > 0 ){
            echo "full_inventory_update file  find. full_inventory_update does not finish. exit... There is no xml file to be executed \n";
            return true;
        }
        if( count(glob($receivedDirectory . 'category_update*')) > 0 ){
            echo "category_update file  find. category_update does not finish. exit... There is no xml file to be executed \n";
            return true;
        }
        if( count(glob($receivedDirectory . 'category_positioning_update*')) == 0 ){
            echo "category_positioning_update file  does not find.  exit... There is no xml file to be executed \n";
            return true;
        }
        $filename = rtrim(basename(shell_exec('ls -t -r ' . $receivedDirectory . 'category_positioning_update* | head --lines 1')));
        $this->filename = $filename;

        return false;
    }

}