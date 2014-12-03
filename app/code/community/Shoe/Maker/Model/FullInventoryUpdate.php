<?php
class Shoe_Maker_Model_FullInventoryUpdate extends Shoe_Maker_Model_UpdateBase{
    public $csvFilename;
    public function run(){
            list($rootXmlElement,$products) = $this->getXmlElementFromString($this->contents);
            if (!isset($products['Simple'])) {
                //Move Invalid XML file to failed directory
                $this->transactionLogHandle($this->receivedDirectory . $this->filename,
                    $this->failedDirectory . $this->filename);
                $this->transactionLogHandle(  "  ->INVALID FILE  : No Simple or Configurable items ");
				exit();
            }

            $this->generatedFileWrap();

            $this->updateDevicomStockWrap();

            $this->syncDevicomInventoryWrap();

            $this->rebuildXmlFilesWrap();
            $this->updateMagentoStockWrap();
            $this->deleteSimplesWrap();

            $this->reIndex();

            // Send email
            $message = "Full Update: " . $this->filename . "\r\n\r\nCOMPLETED!";
            $this->sendNotification(  'FULL INVENTORY UPDATE COMPLETED!', $message);
    }

    public function generatedFileWrap(){
        // Build CSV file from full product update XML file for importing inventory
        $filename = $this->filename;
        $this->transactionLogHandle( "  ->CONVERTING    : " . $filename  );
        if (!$this->generatedFile()) {
            $this->transactionLogHandle( "  ->NOT CONVERTED : " . $filename  );
            throw new Exception("File could not be converted");
        }
        $this->transactionLogHandle( "  ->CONVERTED     : " . $filename  );
    }

    public function generatedFile(){
        $xmlPath = $this->receivedDirectory . $this->filename;
        $pathInfo = pathinfo($this->filename);
        $this->csvFilename = $pathInfo['filename'] . '.' . 'csv';

        if (file_exists($xmlPath)) {
            $xml = simplexml_load_file($xmlPath);
            $csvHandle = fopen($this->temporaryDirectory . 'devicom_inventory_temporary.csv', 'w');

            //Step into entities array to access simple products
            foreach ($xml->Simple as $simpleProducts) {

                //Loop through configurable products
                foreach ($simpleProducts->Product as $entity) {
                    fputcsv($csvHandle, get_object_vars($entity), ':', '"');
                }
            }
            fclose($csvHandle);
            return true;
        }
        return false;
    }

    public function updateDevicomStockWrap(){
        $filename = $this->filename;
        // Update Devicom inventory
        $this->transactionLogHandle( "  ->UPDATE DEVICOM: " . $filename  );
        if (!$this->updateDevicomStock()) {
            $this->transactionLogHandle( "  ->FAILED        : " . $filename );
            throw new Exception("Devicom stock could not be updated");
        }
        $this->transactionLogHandle( "  ->UPDATED       : " . $filename  );
    }

    public function updateDevicomStock() {

        // Get resource
        $resource = Mage::getSingleton('core/resource');
        $writeConnection = $resource->getConnection('core_write');

        // Truncate table to remove all simple items from inventory
        $this->transactionLogHandle( "    ->TRUNCATING  : Inventory ");
        //This is temporary table. Truncate anyway.
        $query = "TRUNCATE TABLE `devicom_inventory_temporary`";
        $result = $writeConnection->query($query);
        $this->transactionLogHandle( "    ->TRUNCATED   : Temporary Inventory ");

        // Import CSV file to rebuild inventory table
        $this->transactionLogHandle( "    ->UPDATING    : Inventory ");
        $dbInfo = $this->getDbInfo();
        $shellCommand = 'mysqlimport'
         . ' --user=' . $dbInfo['user']
         . ' --password=' . $dbInfo['pass']
         . ' --host=' . $dbInfo['host']
         . ' --columns=sku,qty,category_ids --compress --fields-terminated-by=":" --fields-escaped-by="" --lines-terminated-by="\n" --local --lock-tables --verbose '
         . $dbInfo['dbname'] . ' '
         . $this->temporaryDirectory . 'devicom_inventory_temporary.csv';

        if (!$shell_import = shell_exec($shellCommand)) {
            // Send email
            $message = "Full Update: " . $this->filename . "\r\n\r\nFailed Import Into Temporary Table.";
            $this->sendNotification( 'Critical System Notification - Full Inventory Update Failed', $message, $cc = false, $override = true);

            $this->transactionLogHandle("Failed to import data into temporary table");
            $this->transactionLogHandle("  ->INVALID FILE  : No Simples ");
            exit();
        }
        $this->transactionLogHandle( "    ->UPDATED     : Temporary Inventory ");

        // Run query to update inventory table with parent_sku built from sku, attribute_id queried by joins by sku and size built from sku
        //NOTE THAT 3000 through 3999 are IDs allocated to devicom for eav_attribute_options
        $this->transactionLogHandle( "    ->ADDING DATA : Inventory ");

//    $query = "UPDATE `devicom_inventory` AS `di`
//	SET `di`.`parent_sku` = SUBSTRING(`di`.`sku`, 1, LENGTH(`di`.`sku`) - LENGTH(SUBSTRING_INDEX(`di`.`sku`, '-', -1)) - 1),
//	`di`.`size` = (SUBSTRING_INDEX(`di`.`sku`, '-', -1))";

        $query = "UPDATE `devicom_inventory_temporary` AS `di`
	SET `di`.`parent_sku` = SUBSTRING(`di`.`sku`, 1, LENGTH(`di`.`sku`) - LENGTH(SUBSTRING_INDEX(`di`.`sku`, '-', -1)) - 1)";

        $result = $writeConnection->query($query);

        $query = "UPDATE `devicom_inventory_temporary` AS `di`
	INNER JOIN `catalog_product_entity` AS `cpe` ON `di`.`sku` = `cpe`.`sku`
	INNER JOIN `catalog_product_relation` AS `cpr` ON `cpr`.`child_id` = `cpe`.`entity_id`
	INNER JOIN `eav_attribute_set` AS `eas` ON `eas`.`attribute_set_id` = `cpe`.`attribute_set_id`
	INNER JOIN `catalog_product_entity_decimal` AS `cped` ON `cped`.`entity_id` = `cpe`.`entity_id`
	SET
	`di`.`parent_product_id` = `cpr`.`parent_id`,
	`di`.`base_price` = `cped`.`value`,
	`di`.`old_price` = `cped`.`value`,
	`di`.`product_id` = `cpe`.`entity_id`,
	`di`.`attribute_set_name` = `eas`.`attribute_set_name`,
	`di`.`sort_order` = (SELECT `eao`.`sort_order` FROM `eav_attribute_option` AS `eao` WHERE `eao`.`option_id` = (SELECT `cpei`.`value` FROM `catalog_product_entity_int` AS `cpei` WHERE `cpe`.`entity_id` = `cpei`.`entity_id` AND `cpei`.`value` >= 3000 AND `cpei`.`value` <= 3999 LIMIT 1)),
	`di`.`option_id` = (SELECT `cpei`.`value` FROM `catalog_product_entity_int` AS `cpei` WHERE `cpe`.`entity_id` = `cpei`.`entity_id` AND `cpei`.`value` >= 3000 AND `cpei`.`value` <= 3999 LIMIT 1),
	`di`.`label` = (SELECT `eao`.`value` FROM `catalog_product_entity_int` AS `cpei` INNER JOIN `eav_attribute_option_value` AS `eao` ON `eao`.`option_id` = `cpei`.`value` WHERE `cpe`.`entity_id` = `cpei`.`entity_id` AND `cpei`.`value` >= 3000 AND `cpei`.`value` <= 3999 LIMIT 1)
	WHERE `cped`.`attribute_id` = 69";

        $result = $writeConnection->query($query);
        $this->transactionLogHandle( "    ->DATA ADDED  : Temporary Inventory ");
        return true;
    }

    public function syncDevicomInventoryWrap(){
        $filename = $this->filename;
        // Sync devicom_inventory_temporary to devicom_inventory
        $this->transactionLogHandle( "  ->SYNC DEVICOM  : " . $filename  );
        if(!$this->syncDevicomInventory()){
            $this->transactionLogHandle( "  ->FAILED        : " . $filename  );
            throw new Exception("Sync could not be updated");
        }
        $this->transactionLogHandle( "  ->Synced to table devicom_inventory       : " . $filename  );
    }
    public function syncDevicomInventory(){
        $resource = Mage::getSingleton('core/resource');
        //Compare the devicom_inventory_temporary table with devicome_inventory by primary key SKU
        $writeConnection = $resource->getConnection('core_write');
        $query = "UPDATE `devicom_inventory` as `di`, `devicom_inventory_temporary` as `dit`
    SET
      `di`.`category_ids` = `dit`.`category_ids`,
      `di`.`qty` = `dit`.`qty`,
      `di`.`parent_product_id` = `dit`.`parent_product_id`,
      `di`.`parent_sku` = `dit`.`parent_sku`,
      `di`.`product_id` = `dit`.`product_id`,
      `di`.`base_price` = `dit`.`base_price`,
      `di`.`option_id` = `dit`.`option_id`,
      `di`.`label` = `dit`.`label`,
      `di`.`old_price` = `dit`.`old_price`
    where `di`.`sku` = `dit`.`sku`";
        $result = $writeConnection->query($query);
        return true;
    }

    public function updateMagentoStockWrap(){
        $filename = $this->filename;
        // Update Magento inventory
        $this->transactionLogHandle( "  ->UPDATE MAGENTO: " . $filename  );
        if (!$this->updateMagentoStock()) {
            $this->transactionLogHandle( "  ->FAILED        : " . $filename  );
            throw new Exception("Magento stock could not be updated");
        }
        $this->transactionLogHandle( "  ->UPDATED       : " . $filename  );
    }

    public function updateMagentoStock(){
        // Get resource
        $resource = Mage::getSingleton('core/resource');
        $writeConnection = $resource->getConnection('core_write');

        // Set qty and stock status to that of the update
        $query = "UPDATE `cataloginventory_stock_status` AS `css`
                INNER JOIN `cataloginventory_stock_item` AS `csi` ON `css`.`product_id` = `csi`.`product_id`
                INNER JOIN `catalog_product_entity` AS `cpe` ON `csi`.`product_id` = `cpe`.`entity_id`
                INNER JOIN `devicom_inventory` AS `di` ON `cpe`.`sku` = `di`.`sku`
                SET `csi`.`qty` = IF(`di`.`qty` > 0, 6, 0), `csi`.`is_in_stock` = IF(`di`.`qty` > 0, 1, 0), `css`.`qty` =
				IF(`di`.`qty` > 0, 6, 0), `css`.`stock_status` = IF(`di`.`qty` > 0, 1, 0)";
        $result = $writeConnection->query($query);
       // $this->transactionLogHandle($query);
        $this->transactionLogHandle( "    ->MAGE STOCK  : Updated ");

        return true;
    }

    public static function getXmlElementFromString($contents){
        //Create new XML object
        $rootXmlElement = new SimpleXMLElement($contents);

        foreach ($rootXmlElement->children() as $child) {
            $entityTypes[$child->getName()] = $child->getName();
        }
        return array($rootXmlElement,$entityTypes);
    }

    public function rebuildXmlFilesWrap(){
        $filename = $this->filename;
        // Rebuild XML files
        $this->transactionLogHandle( "  ->REBUILDING    : " . $filename );
        if (!$this->rebuildXmlFiles()) {
            $this->transactionLogHandle( "  ->NOT REBILT: " . $filename);
            throw new Exception("XML files could not be built");
        }
    }

    public function rebuildXmlFiles(){
        $oldInventoryDirectory = $this->oldInventoryDirectory;
        $newInventoryDirectory = $this->newInventoryDirectory;
        $inventoryDirectory = $this->inventoryDirectory;
        // Generate Inventory XML File
        $resource = Mage::getSingleton('core/resource');
        $writeConnection = $resource->getConnection('core_read');

        // Select all simple products with a qty > 0
        $query = "SELECT * FROM `devicom_inventory` WHERE `qty` > 0 AND `parent_sku` IS NOT NULL AND `parent_product_id` IS NOT NULL ORDER BY `sort_order` ASC";
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
            // Create temporary directory
            if (!mkdir($newInventoryDirectory, 0777, true)) {
                //die('Failed to create folders...');
            }
            $this->transactionLogHandle( "    ->CREATED DIR : Inventory\n");

            // Copy .htaccess
            $file = $inventoryDirectory . '.htaccess';
            $newfile = $newInventoryDirectory . '.htaccess';

            if (!copy($file, $newfile)) {
                //Should be exception but will stop processing?
            }

            $this->transactionLogHandle( "    ->ADD HTACCESS: Inventory\n");


            $query = "SELECT * FROM `devicom_inventory` WHERE `qty` > 0 AND `parent_sku` IS NOT NULL AND `parent_product_id` IS NOT NULL GROUP BY `parent_product_id` ORDER BY `sort_order` ASC";
            $results = $writeConnection->fetchAll($query);
            foreach ($results as $result) {
                $parent[$result['parent_sku']]['parent_product_id'] = $result['parent_product_id'];
                $parent[$result['parent_sku']]['base_price'] = $result['base_price'];
                $parent[$result['parent_sku']]['old_price'] = $result['old_price'];
                $parent[$result['parent_sku']]['attribute_set_name'] = $result['attribute_set_name'];
                $parent[$result['parent_sku']]['category_ids'] = $result['category_ids'];
            }

            $this->transactionLogHandle( "    ->BUILD FILES : Inventory\n");

            foreach ($inventory as $configurable => $simples) {

                $itemFilename = $configurable . '.xml';

                //Creates XML string and XML document from the DOM representation
                $dom = new DomDocument('1.0');
                $products = $dom->appendChild($dom->createElement('Products'));
                $info = $products->appendChild($dom->createElement('Info'));

                $parent_product_id = $info->appendChild($dom->createElement('ParentProductId'));
                $parent_product_id->appendChild($dom->createTextNode($parent[$configurable]['parent_product_id']));

                $base_price = $info->appendChild($dom->createElement('BasePrice'));
                $base_price->appendChild($dom->createTextNode($parent[$configurable]['base_price']));

                $old_price = $info->appendChild($dom->createElement('OldPrice'));
                $old_price->appendChild($dom->createTextNode($parent[$configurable]['old_price']));

                $attribute_set_name = $info->appendChild($dom->createElement('AttributeSetName'));
                $attribute_set_name->appendChild($dom->createTextNode($parent[$configurable]['attribute_set_name']));

                $category_ids = $info->appendChild($dom->createElement('CategoryIds'));
                $category_ids->appendChild($dom->createTextNode($parent[$configurable]['category_ids']));

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
                $inventoryHandle = fopen($newInventoryDirectory . $itemFilename, 'w');
                fwrite($inventoryHandle, $xml);
                fclose($inventoryHandle);
                $this->transactionLogHandle( "      ->CREATE XML: " . $configurable . ".xml\n");
            }
            shell_exec("chmod -R 777 $inventoryDirectory ");
            //Build test product file

            $dom = new DomDocument('1.0');

            $products = $dom->appendChild($dom->createElement('Products'));
            $info = $products->appendChild($dom->createElement('Info'));

            $parent_product_id = $info->appendChild($dom->createElement('ParentProductId'));
            $parent_product_id->appendChild($dom->createTextNode('132706'));

            $base_price = $info->appendChild($dom->createElement('BasePrice'));
            $base_price->appendChild($dom->createTextNode('100.000'));

            $old_price = $info->appendChild($dom->createElement('OldPrice'));
            $old_price->appendChild($dom->createTextNode('100.000'));

            $attribute_set_name = $info->appendChild($dom->createElement('AttributeSetName'));
            $attribute_set_name->appendChild($dom->createTextNode('onesize'));

            $category_ids = $info->appendChild($dom->createElement('CategoryIds'));
            $category_ids->appendChild($dom->createTextNode('238'));

            $product = $products->appendChild($dom->createElement('Product'));

            $product_id = $product->appendChild($dom->createElement('ProductId'));
            $product_id->appendChild($dom->createTextNode('132707'));

            $option_id = $product->appendChild($dom->createElement('OptionId'));
            $option_id->appendChild($dom->createTextNode('3071'));

            $label_id = $product->appendChild($dom->createElement('Label'));
            $label_id->appendChild($dom->createTextNode('One Size'));

            $quantity = $product->appendChild($dom->createElement('Quantity'));
            $quantity->appendChild($dom->createTextNode('100'));

            // Make the output pretty
            $dom->formatOutput = true;

            // Save the XML string
            $xml = $dom->saveXML();
            $inventoryHandle = fopen($newInventoryDirectory . 'test-product.xml', 'w');
            fwrite($inventoryHandle, $xml);
            fclose($inventoryHandle);
            $this->transactionLogHandle( "      ->CREATE XML: test-product.xml\n");

            $this->transactionLogHandle( "    ->FILES BUILT : Inventory\n");

            //Rename active inventory directory to old_directory
            rename($inventoryDirectory, $oldInventoryDirectory);
            //Rename new inventory directory to make active and restore symlink
            rename($newInventoryDirectory, $inventoryDirectory);
            $this->transactionLogHandle( "    ->RENAMED DIRS: Inventory\n");

            //Remove old inventory XML files
            $handle = opendir($oldInventoryDirectory);

            while (($file = readdir($handle)) !== false) {
                @unlink($oldInventoryDirectory . '/' . $file);
            }
            $this->transactionLogHandle( "    ->REMOVE FILES: Inventory\n");
            closedir($handle);

            //Remove old inventory directory
            if (!rmdir($oldInventoryDirectory)) {
                //die('Failed to create folders...');
            }
            $this->transactionLogHandle( "    ->REMOVED DIR : Inventory\n");
        } else {
            $this->transactionLogHandle( "  ->ERROR         : FULL INVENTORY UPDATE FAILED\n");

            // Send email
            $message = "Full Update: " . $this->filename . "\r\n\r\nNo inventory was found.";
            $this->sendNotification( 'Critical System Notification - Full Inventory Update Failed', $message, $cc = false, $override = true);
        }

        return true;
    }

    public function deleteSimplesWrap(){
        $filename = $this->filename;
        // Delete simples with 0 or less qty
        $this->transactionLogHandle( "  ->DELETE SIMPLES: " . $filename );
        if (!$this->deleteSimples()) {
            $this->transactionLogHandle( "  ->FAILED        : " . $filename  );
            throw new Exception("Simples could not be deleted");
        }
        $this->transactionLogHandle( "  ->DELETED       : " . $filename );
    }
    //TODO test case
    public function deleteSimples(){

        // Get resource
        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');
        $query = "SELECT `sku` FROM `devicom_inventory` WHERE `qty` <= 0 AND `option_id` IS NOT NULL";
        $results = $readConnection->query($query);

        foreach ($results as $result) {
            if ($product = Mage::getModel('catalog/product')->loadByAttribute('sku', $result['sku'])) {
                //Delete product
                $product->delete();
                $this->transactionLogHandle( "    ->DELETED     : " . $result['sku']  );
            }
        }

        return true;
    }
    //TODO test case
    public function reIndex(){
        $filename = $this->filename;
        //Run reindexing
        $this->transactionLogHandle( "  ->INDEXING      : " . $filename . "\n");

        try {
            $processIds = array(8, 1, 2); // ORDER IS CRITICAL -- STOCK MUST COME FIRST

            foreach ($processIds as $processId) {
                $this->transactionLogHandle( "    ->START       : " . $processId );
                $process = Mage::getModel('index/process')->load($processId);
                $process->reindexAll();
                $this->transactionLogHandle( "    ->END         : " . $processId );
            }

            $this->transactionLogHandle( "  ->COMPLETED     : " . $filename );
        } catch (Exception $e) {
            $this->transactionLogHandle( "  ->NOT COMPLETED : " . $e );
        }
    }
    public function removeFile(){
        //Move XML file to processed directory and make copy for ftp directory
        rename($this->receivedDirectory . $this->filename, $this->processedDirectory . $this->filename);

        //Move CSV file to processed directory
        rename($this->temporaryDirectory . 'devicom_inventory_temporary.csv', $this->processedDirectory . $this->csvFilename);
    }
    public function validate(){
        $catalogLogsDirectory = $this->catalogLogsDirectory;
        $receivedDirectory = $this->receivedDirectory;

        if(count(glob($catalogLogsDirectory . '*.lock')) > 0){
            echo "Log file exist. exit... There is no xml file to be executed \n";
            return true;
        }
        if( count(glob($receivedDirectory . 'full_inventory_update*')) == 0 ){
            echo "full_inventory_update* file not find. exit... There is no xml file to be executed \n";
            return true;
        }
        $filename = rtrim(basename(shell_exec('ls -t -r ' . $receivedDirectory . 'full_inventory_update* | head --lines 1')));
        $this->filename = $filename;

        //Exit if full file is older or equal to any incremental fi  le (if exists)
        if(count(glob($receivedDirectory . 'incremental_product_update*')) > 0){
            $incrementalFilename = rtrim(basename(shell_exec('ls -t -r ' . $receivedDirectory . 'incremental_product_update* | head --lines 1')));
            if (substr($filename, -22, 8) . substr($filename, -13, 9) >= substr($incrementalFilename, -22, 8) . substr($incrementalFilename, -13, 9)) {
                return true;
            }
        }

        return false;
    }

    public function removeCsvFile(){
        //Move CSV file to failed directory
        if (file_exists($this->temporaryDirectory . 'devicom_inventory_temporary.csv')) {
            rename($this->temporaryDirectory . 'devicom_inventory_temporary.csv', $this->failedDirectory . $this->csvFilename);
        }
    }
}