<?php

    //Open transaction log
    $transactionLogHandle = fopen($catalogUpdateLogsDirectory . 'transaction_log', 'a+');

    // Initialize magento environment for 'admin' store
    require_once '/chroot/home/rxkicksc/rxkicks.com/html/app/Mage.php';
    umask(0);
    Mage::app('admin'); //admin defines default value for all stores including the Main Website
    
    // Get resource
    $resource = Mage::getSingleton('core/resource');
    $writeConnection = $resource->getConnection('core_write');
    
    // Truncate table to remove all simple items from inventory
    fwrite($transactionLogHandle, "    ->TRUNCATING  : Inventory\n");
    $query = "TRUNCATE TABLE `devicom_inventory`";
    $result = $writeConnection->query($query);
    fwrite($transactionLogHandle, "    ->TRUNCATED   : Inventory\n");

    // Run query to update inventory table with parent_sku built from sku, attribute_id queried by joins by sku and size built from sku
    fwrite($transactionLogHandle, "    ->ADDING DATA : Inventory\n");
    $query = "INSERT INTO `devicom_inventory` (`sku`, `parent_sku`, `attribute_id`, `size`, `qty`) SELECT `cpe`.`sku` AS `sku`, `cpe3`.`sku` AS `parent_sku`, (SELECT `cpei2`.`value` FROM `catalog_product_entity_int` AS `cpei2`
WHERE `cpe`.`entity_id` = `cpei2`.`entity_id` AND `cpei2`.`attribute_id` != '89' AND `cpei2`.`attribute_id` != '95' AND `cpei2`.`attribute_id` != '114' AND 
`cpei2`.`attribute_id` != '115') AS `attribute_id`, (SELECT SUBSTRING_INDEX(`cpe`.`sku`, '-', -1)) AS `size`, `csi`.`qty`
FROM `cataloginventory_stock_item` AS `csi` 
INNER JOIN `catalog_product_entity` AS `cpe` ON `csi`.`product_id` = `cpe`.`entity_id`
INNER JOIN `catalog_product_relation` AS `cpr` ON `cpe`.`entity_id` = `cpr`.`child_id`
INNER JOIN `catalog_product_entity_int` AS `cpei` ON `cpr`.`parent_id` = `cpei`.`entity_id` 
INNER JOIN `catalog_product_entity` AS `cpe3` ON `cpr`.`parent_id` =`cpe3`.`entity_id`
WHERE `cpei`.`attribute_id` = '89' AND `cpei`.`value` = '1' AND `cpe`.`sku` NOT LIKE '%Product%'";

    $result = $writeConnection->query($query);
    fwrite($transactionLogHandle, "    ->DATA ADDED  : Inventory\n");
    
    //XML FILES
    $inventoryDirectory = '/chroot/home/rxkicksc/rxkicks.com/html/devicom_apps/inventory/';
    $newInventoryDirectory = '/chroot/home/rxkicksc/rxkicks.com/html/devicom_apps/new_inventory/';
    $oldInventoryDirectory = '/chroot/home/rxkicksc/rxkicks.com/html/devicom_apps/old_inventory/';

    // Generate Inventory XML File
    $resource = Mage::getSingleton('core/resource');
    $readConnection = $resource->getConnection('core_read');
    
    // Select all simple products
    $query = "SELECT * FROM `devicom_inventory`";
    $results = $readConnection->fetchAll($query);

    // Build array from result set
    foreach ($results as $result) {
	$inventory[$result['parent_sku']][$result['sku']]['sku'] = $result['sku'];
	$inventory[$result['parent_sku']][$result['sku']]['parent_sku'] = $result['parent_sku'];
	$inventory[$result['parent_sku']][$result['sku']]['attribute_id'] = $result['attribute_id'];
	$inventory[$result['parent_sku']][$result['sku']]['size'] = $result['size'];
	$inventory[$result['parent_sku']][$result['sku']]['qty'] = $result['qty'];
    }

    fwrite($transactionLogHandle, "    ->BUILT ARRAY : Inventory\n");

    // Create temporary directory
    if (!mkdir($newInventoryDirectory, 0775, true)) {
	//die('Failed to create folders...');
    }
    fwrite($transactionLogHandle, "    ->CREATED DIR : Inventory\n");
   
    // Copy .htaccess
    $file = $inventoryDirectory . '.htaccess';
    $newfile = $newInventoryDirectory . '.htaccess';

    if (!copy($file, $newfile)) {

    }
    fwrite($transactionLogHandle, "    ->ADD HTACCESS: Inventory\n");
    
    fwrite($transactionLogHandle, "    ->BUILD FILES : Inventory\n");
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
		    $size->appendChild($dom->createTextNode('OneSize'));
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
	fwrite($transactionLogHandle, "    ->CREATED XML : " . $configurable . ".xml\n");

	echo $i . "\n";
    }
    fwrite($transactionLogHandle, "    ->FILES BUILT : Inventory\n");
    
    //Rename active inventory directory to old_directory
    rename($inventoryDirectory, $oldInventoryDirectory);
    //Rename new inventory directory to make active and restore symlink
    rename($newInventoryDirectory, $inventoryDirectory);
    fwrite($transactionLogHandle, "    ->RENAMED DIRS: Inventory\n");
    
    //Remove old inventory XML files
    $handle=opendir($oldInventoryDirectory);

    while (($file = readdir($handle))!==false) {
	@unlink($oldInventoryDirectory.'/'.$file);
    }
    fwrite($transactionLogHandle, "    ->REMOVE FILES: Inventory\n");
    closedir($handle);

    //Remove old inventory directory
    if (!rmdir($oldInventoryDirectory)) {
	//die('Failed to create folders...');
    }
    fwrite($transactionLogHandle, "    ->REMOVED DIR : Inventory\n");
    
    //Close transaction log
    fclose($transactionLogHandle);
?>
