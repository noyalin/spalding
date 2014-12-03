<?php
/**
 * Full inventory update
 */

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
if(rtrim(basename(@shell_exec('ls ' . $catalogLogsDirectory . '*.lock | head --lines 1')))){
    exit;
}
$filename = rtrim(basename(@shell_exec('ls -t -r ' . $receivedDirectory . 'full_inventory_update* | head --lines 1')));
if (!$filename) {
    exit;
}

//Exit if full file is older or equal to any incremental fi  le (if exists)
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

    if (!isset($products['Simple'])) {
        //Move Invalid XML file to failed directory
        rename($receivedDirectory . $filename, $failedDirectory . $filename);
        fwrite($transactionLogHandle, "  ->INVALID FILE  : No Simple or Configurable items\n");
        die("INVALID FILE  : No Simple or Configurable items\n");
    }

    //Begin the main logic

    // Build CSV file from full product update XML file for importing inventory
    $xmlPath = $receivedDirectory . $filename;
    $pathInfo = pathinfo($filename);
    $csvFilename = $pathInfo['filename'] . '.' . 'csv';

    fwrite($transactionLogHandle, "  ->CONVERTING    : " . $filename . "\n");
    if (!convertFile($xmlPath, $temporaryDirectory)) {
        fwrite($transactionLogHandle, "  ->NOT CONVERTED : " . $filename . "\n");
        throw new Exception("File could not be converted");
    }
    fwrite($transactionLogHandle, "  ->CONVERTED     : " . $filename . "\n");

    // Update Devicom inventory
    fwrite($transactionLogHandle, "  ->UPDATE DEVICOM: " . $filename . "\n");
    if (!updateDevicomStock($transactionLogHandle, $temporaryDirectory)) {
        fwrite($transactionLogHandle, "  ->FAILED        : " . $filename . "\n");
        throw new Exception("Devicom stock could not be updated");
    }
    fwrite($transactionLogHandle, "  ->UPDATED       : " . $filename . "\n");

    // Sync devicom_inventory_temporary to devicom_inventory
    fwrite($transactionLogHandle, "  ->SYNC DEVICOM  : " . $filename . "\n");
    if(!syncDevicomInventory()){
        fwrite($transactionLogHandle, "  ->FAILED        : " . $filename . "\n");
        throw new Exception("Sync could not be updated");
    }
    fwrite($transactionLogHandle, "  ->Synced to table devicom_inventory       : " . $filename . "\n");

    // Update Magento inventory
    fwrite($transactionLogHandle, "  ->UPDATE MAGENTO: " . $filename . "\n");
    if (!updateMagentoStock($transactionLogHandle)) {
        fwrite($transactionLogHandle, "  ->FAILED        : " . $filename . "\n");
        throw new Exception("Magento stock could not be updated");
    }
    fwrite($transactionLogHandle, "  ->UPDATED       : " . $filename . "\n");

    // Rebuild XML files
    fwrite($transactionLogHandle, "  ->REBUILDING    : " . $filename . "\n");
    if (!rebuildXmlFiles($transactionLogHandle)) {
        fwrite($transactionLogHandle, "  ->NOT REBILT: " . $filename . "\n");
        throw new Exception("XML files could not be built");
    }
    fwrite($transactionLogHandle, "  ->REBUILT       : " . $filename . "\n");

    // Delete simples with 0 or less qty
    fwrite($transactionLogHandle, "  ->DELETE SIMPLES: " . $filename . "\n");
    if (!deleteSimples($transactionLogHandle)) {
        fwrite($transactionLogHandle, "  ->FAILED        : " . $filename . "\n");
        throw new Exception("Simples could not be deleted");
    }
    fwrite($transactionLogHandle, "  ->DELETED       : " . $filename . "\n");

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
    rename($temporaryDirectory . 'devicom_inventory_temporary.csv', $processedDirectory . $csvFilename);

    // Send email
    $message = "Full Update: " . $filename . "\r\n\r\nCOMPLETED!";
    sendNotification($subject = 'FULL INVENTORY UPDATE COMPLETED!', $message);
} catch (Exception $e) {

    fwrite($transactionLogHandle, "  ->ERROR         : See exception_log\n");

    //Append error to exception log
    $exceptionLogHandle = fopen($catalogLogsDirectory . 'exception_log', 'a');
    fwrite($exceptionLogHandle, '->' . $filename . " - " . $e->getMessage() . "\n");
    fclose($exceptionLogHandle);

    unlink($catalogLogsDirectory . 'stage');

    //Move XML file to failed directory
    rename($receivedDirectory . $filename, $failedDirectory . $filename);

    //Move CSV file to failed directory
    if (file_exists($temporaryDirectory . 'devicom_inventory_temporary.csv')) {
        rename($temporaryDirectory . 'devicom_inventory_temporary.csv', $failedDirectory . $csvFilename);
    }
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

function convertFile($xmlPath, $temporaryDirectory) {
    if (file_exists($xmlPath)) {
        $xml = simplexml_load_file($xmlPath);
        $csvHandle = fopen($temporaryDirectory . 'devicom_inventory_temporary.csv', 'w');

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

function updateDevicomStock($transactionLogHandle, $temporaryDirectory) {

    // Get resource
    $resource = Mage::getSingleton('core/resource');
    $writeConnection = $resource->getConnection('core_write');

    // Truncate table to remove all simple items from inventory
    fwrite($transactionLogHandle, "    ->TRUNCATING  : Inventory\n");
    //This is temporary table. Truncate anyway.
    $query = "TRUNCATE TABLE `devicom_inventory_temporary`";
    $result = $writeConnection->query($query);
    fwrite($transactionLogHandle, "    ->TRUNCATED   : Temporary Inventory\n");

    // Import CSV file to rebuild inventory table
    fwrite($transactionLogHandle, "    ->UPDATING    : Inventory\n");
    $shellCommand = 'mysqlimport --user=' . CONF_DATABASE_CONNECTION_USERNAME . ' --password=' . CONF_DATABASE_CONNECTION_PASSWORD . ' --host=' . CONF_DATABASE_CONNECTION_HOSTNAME . ' --columns=sku,qty,category_ids --compress --fields-terminated-by=":" --fields-escaped-by="" --lines-terminated-by="\n" --local --lock-tables --verbose ' . CONF_DATABASE_CONNECTION_DBNAME . ' ' . $temporaryDirectory . 'devicom_inventory_temporary.csv';

	if (!$shell_import = shell_exec($shellCommand)) {
        // Send email
        $message = "Full Update: " . $filename . "\r\n\r\nFailed Import Into Temporary Table.";
        sendNotification($subject = 'Critical System Notification - Full Inventory Update Failed', $message, $cc = false, $override = true);

        fwrite($transactionLogHandle, "Failed to import data into temporary table");
		fwrite($transactionLogHandle, "  ->INVALID FILE  : No Simples\n");
		exit();
    }
    fwrite($transactionLogHandle, "    ->UPDATED     : Temporary Inventory\n");

    // Run query to update inventory table with parent_sku built from sku, attribute_id queried by joins by sku and size built from sku
    //NOTE THAT 3000 through 3999 are IDs allocated to devicom for eav_attribute_options
    fwrite($transactionLogHandle, "    ->ADDING DATA : Inventory\n");

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
    fwrite($transactionLogHandle, "    ->DATA ADDED  : Temporary Inventory\n");
    return true;
}

/**
 * update table devicom_inventory when sku existed in temporary table devicom_inventory_temporary
 */
function syncDevicomInventory(){
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

function updateMagentoStock($transactionLogHandle) {

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
    fwrite($transactionLogHandle, "    ->MAGE STOCK  : Updated\n");

    return true;
}

function rebuildXmlFiles($transactionLogHandle) {

    $core_dir = dirname(dirname(dirname(__FILE__)));
    $inventoryDirectory = $core_dir . '/devicom_apps/inventory/product/';
    $newInventoryDirectory = $core_dir . '/devicom_apps/inventory/new_product/';
    $oldInventoryDirectory = $core_dir . '/devicom_apps/inventory/old_product/';

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
        fwrite($transactionLogHandle, "    ->CREATED DIR : Inventory\n");

        // Copy .htaccess
        $file = $inventoryDirectory . '.htaccess';
        $newfile = $newInventoryDirectory . '.htaccess';

        if (!copy($file, $newfile)) {
            //Should be exception but will stop processing?
        }

        fwrite($transactionLogHandle, "    ->ADD HTACCESS: Inventory\n");


        $query = "SELECT * FROM `devicom_inventory` WHERE `qty` > 0 AND `parent_sku` IS NOT NULL AND `parent_product_id` IS NOT NULL GROUP BY `parent_product_id` ORDER BY `sort_order` ASC";
        $results = $writeConnection->fetchAll($query);
        foreach ($results as $result) {
            $parent[$result['parent_sku']]['parent_product_id'] = $result['parent_product_id'];
            $parent[$result['parent_sku']]['base_price'] = $result['base_price'];
            $parent[$result['parent_sku']]['old_price'] = $result['old_price'];
            $parent[$result['parent_sku']]['attribute_set_name'] = $result['attribute_set_name'];
            $parent[$result['parent_sku']]['category_ids'] = $result['category_ids'];
        }

        fwrite($transactionLogHandle, "    ->BUILD FILES : Inventory\n");

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
            fwrite($transactionLogHandle, "      ->CREATE XML: " . $configurable . ".xml\n");
        }

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
        fwrite($transactionLogHandle, "      ->CREATE XML: test-product.xml\n");

        fwrite($transactionLogHandle, "    ->FILES BUILT : Inventory\n");

        //Rename active inventory directory to old_directory
        rename($inventoryDirectory, $oldInventoryDirectory);
        //Rename new inventory directory to make active and restore symlink
        rename($newInventoryDirectory, $inventoryDirectory);
        fwrite($transactionLogHandle, "    ->RENAMED DIRS: Inventory\n");

        //Remove old inventory XML files
        $handle = opendir($oldInventoryDirectory);

        while (($file = readdir($handle)) !== false) {
            @unlink($oldInventoryDirectory . '/' . $file);
        }
        fwrite($transactionLogHandle, "    ->REMOVE FILES: Inventory\n");
        closedir($handle);

        //Remove old inventory directory
        if (!rmdir($oldInventoryDirectory)) {
            //die('Failed to create folders...');
        }
        fwrite($transactionLogHandle, "    ->REMOVED DIR : Inventory\n");
    } else {
        fwrite($transactionLogHandle, "  ->ERROR         : FULL INVENTORY UPDATE FAILED\n");

        // Send email
        $message = "Full Update: " . $filename . "\r\n\r\nNo inventory was found.";
        sendNotification($subject = 'Critical System Notification - Full Inventory Update Failed', $message, $cc = false, $override = true);
    }

    return true;
}

function deleteSimples($transactionLogHandle) {

    // Get resource
    $resource = Mage::getSingleton('core/resource');
    $readConnection = $resource->getConnection('core_read');
    $query = "SELECT `sku` FROM `devicom_inventory` WHERE `qty` <= 0 AND `option_id` IS NOT NULL";
    $results = $readConnection->query($query);

    foreach ($results as $result) {
        if ($product = Mage::getModel('catalog/product')->loadByAttribute('sku', $result['sku'])) {
            //Delete product
            $product->delete();
            fwrite($transactionLogHandle, "    ->DELETED     : " . $result['sku'] . "\n");
        }
    }

    return true;
}

?>
