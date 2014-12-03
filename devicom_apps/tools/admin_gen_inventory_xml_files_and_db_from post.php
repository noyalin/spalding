<?php
$filename = rtrim(basename(shell_exec('ls -t -r /chroot/home/rxkicksc/rxkicks.com/html/devicom_apps/catalog/temporary/full_product_update* | head --lines 1')));
$receivedDirectory = '/chroot/home/rxkicksc/rxkicks.com/html/devicom_apps/catalog/temporary/';
$temporaryDirectory = '/chroot/home/rxkicksc/rxkicks.com/html/devicom_apps/catalog/temporary/';
$catalogUpdateLogsDirectory = '/chroot/home/rxkicksc/rxkicks.com/html/devicom_apps/catalog/logs/';
$inventoryDirectory = '/chroot/home/rxkicksc/rxkicks.com/html/devicom_apps/inventory/';
$newInventoryDirectory = '/chroot/home/rxkicksc/rxkicks.com/html/devicom_apps/new_inventory/';
$oldInventoryDirectory = '/chroot/home/rxkicksc/rxkicks.com/html/devicom_apps/old_inventory/';

$realTime = realTime();

    $transactionLogHandle = fopen($catalogUpdateLogsDirectory . 'transaction_log', 'a+');
    fwrite($transactionLogHandle, "->BEGIN PROCESSING: " . $filename . "\n");
    fwrite($transactionLogHandle, "  ->START TIME    : " . $realTime[5] . '/' . $realTime[4] . '/' . $realTime[3] . ' ' . $realTime[2] . ':' . $realTime[1] . ':' . $realTime[0] . "\n");

// Initialize magento environment for 'admin' store
require_once '/chroot/home/rxkicksc/rxkicks.com/html/app/Mage.php';
umask(0);
Mage::app('admin'); //admin defines default value for all stores including the Main Website

// Get connection
$resource = Mage::getSingleton('core/resource');
$writeConnection = $resource->getConnection('core_write');

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

// Truncate table to remove all simple items from inventory
fwrite($transactionLogHandle, "  ->TRUNCATING    : Inventory    : " . $sku . "\n");
$query = "TRUNCATE TABLE `devicom_inventory`";
$result = $writeConnection->query($query);
fwrite($transactionLogHandle, "  ->TRUNCATED     : Inventory    : " . $sku . "\n");

// Import CSV file to rebuild inventory table
fwrite($transactionLogHandle, "  ->UPDATING STOCK: " . $filename . "\n");
if (!shell_exec('mysqlimport --user=rxkicksc_devicom --password=!1devicom9 --host=localhost --columns=sku,qty --compress --fields-terminated-by="," --fields-escaped-by="" --lines-terminated-by="\n" --local --lock-tables --verbose rxkicksc_magento_11202_31 /chroot/home/rxkicksc/rxkicks.com/html/devicom_apps/catalog/temporary/devicom_inventory.csv')) {

}
fwrite($transactionLogHandle, "  ->STOCK UPDATED : " . $filename . "\n");

// Run query to update inventory table with parent_sku built from sku, attribute_id queried by joins by sku and size built from sku
fwrite($transactionLogHandle, "  ->UPDATING SKUS : " . $filename . "\n");
$query = "UPDATE `devicom_inventory` AS `di` 
    INNER JOIN `catalog_product_entity` AS `cpe` ON `di`.`sku` = `cpe`.`sku`
    SET `di`.`parent_sku` = SUBSTRING(`di`.`sku`, 1, LENGTH(`di`.`sku`) - 
    LENGTH(SUBSTRING_INDEX(`di`.`sku`, '-', -1)) - 1), `di`.`attribute_id` = (SELECT `cpei`.`value` 
    FROM `catalog_product_entity_int` AS `cpei`
    WHERE `cpe`.`entity_id` = `cpei`.`entity_id` AND `cpei`.`attribute_id` != '89' AND `cpei`.`attribute_id` != '95' AND `cpei`.`attribute_id` != '114' 
    AND `cpei`.`attribute_id` != '115'), `di`.`size` = (SUBSTRING_INDEX(`di`.`sku`, '-', -1))";

$result = $writeConnection->query($query);
fwrite($transactionLogHandle, "  ->SKUS UPDATED  : " . $filename . "\n");



// Generate Inventory XML File
$resource = Mage::getSingleton('core/resource');
$readConnection = $resource->getConnection('core_read');
$query = "SELECT * FROM `devicom_inventory` WHERE `qty` <= 0 AND `parent_sku` = '000001111'";
$results = $readConnection->fetchAll($query);

//$handle = fopen("cataloginventory_stock_item.csv", "r");
//$data = fgetcsv($handle, 0, ';','"');


// Build array from result set
foreach ($results as $result) {
//echo $result['sku'];
    $inventory[$result['parent_sku']][$result['sku']]['sku'] = $result['sku'];
    $inventory[$result['parent_sku']][$result['sku']]['parent_sku'] = $result['parent_sku'];
    $inventory[$result['parent_sku']][$result['sku']]['attribute_id'] = $result['attribute_id'];
    $inventory[$result['parent_sku']][$result['sku']]['size'] = $result['size'];
    $inventory[$result['parent_sku']][$result['sku']]['qty'] = $result['qty'];
}

echo "COMPLETED ARRAY BUILD\n";

// Create temporary directory
if (!mkdir($newInventoryDirectory, 0775, true)) {
    //die('Failed to create folders...');
}

// Copy .htaccess
$file = $inventoryDirectory . '.htaccess';
$newfile = $newInventoryDirectory . '.htaccess';

if (!copy($file, $newfile)) {

}

$i = 0;
foreach ($inventory as $configurable => $simples) {
    $i++;
    
    //Creates XML string and XML document from the DOM representation
    $itemFilename = $configurable . '.xml';

    $dom = new DomDocument('1.0');
    $products = $dom->appendChild($dom->createElement('Products'));
    
    foreach ($simples as $simple) {

	if ($simple['qty'] <= "0") {
	    $product = $products->appendChild($dom->createElement('Product'));

	    $attribute_id = $product->appendChild($dom->createElement('AttributeId'));
	    $attribute_id->appendChild($dom->createTextNode($simple['attribute_id']));

	    $size = $product->appendChild($dom->createElement('Size'));

	    if (strtolower($simple['size']) == 'onesize') {
		$size->appendChild($dom->createTextNode(strtolower($simple['size'])));
	    } else {
		$size->appendChild($dom->createTextNode(strtoupper($simple['size'])));
	    }

	    $quantity = $product->appendChild($dom->createElement('Quantity'));
	    $quantity->appendChild($dom->createTextNode($simple['qty']));
	}
    }

    // Make the output pretty
    $dom->formatOutput = true;

    // Save the XML string
    $xml = $dom->saveXML();

    //Write file to inventory directory
    $inventoryHandle = fopen($newInventoryDirectory . $itemFilename, 'w');
    fwrite($inventoryHandle, $xml);
    fclose($inventoryHandle);
    fwrite($transactionLogHandle, "  ->GENERATED XML : Inventory    : " . $sku . "\n");

    echo $i . "\n";
}
echo "FILE GENERATION COMPLETED!\n";

//Rename active inventory directory to old_directory
rename($inventoryDirectory, $oldInventoryDirectory);
//Rename new inventory directory to make active and restore symlink
rename($newInventoryDirectory, $inventoryDirectory);

//Remove old inventory XML files
$handle=opendir($oldInventoryDirectory);

while (($file = readdir($handle))!==false) {
    @unlink($oldInventoryDirectory.'/'.$file);
}

closedir($handle);

//Remove old inventory directory
if (!rmdir($oldInventoryDirectory)) {
    //die('Failed to create folders...');
}

function convertFile($xmlPath, $temporaryDirectory) {
    if (file_exists($xmlPath)) {
	$xml = simplexml_load_file($xmlPath);
        $csvHandle = fopen($temporaryDirectory . 'devicom_inventory.csv', 'w');

	//Step into entities array to access simple products
	foreach ($xml->Simple as $simpleProducts) {

	    //Loop through configurable products
	    foreach ($simpleProducts->Product as $entity) {
		    fputcsv($csvHandle, get_object_vars($entity),',','"');
	    }
	}
	fclose($csvHandle);
	return true;
    }
    return false;
}


function realTime($time = null, $isAssociative = false){

    $offsetInHours = +8;
    $offsetInSeconds = $offsetInHours * 60 * 60;
   
    if (is_null($time)) {
	$time = time();
    }

    $pstTime = $time + $offsetInSeconds;

    $explodedTime = explode(',', gmdate('s,i,H,d,m,Y,w,z,I', $pstTime));
    
    if (!$isAssociative) {
	return $explodedTime;
    }
    return array_combine(array('tm_sec','tm_min','tm_hour','tm_mday','tm_mon','tm_year','tm_wday','tm_yday','tm_isdst'), $explodedTime);
}

?>
