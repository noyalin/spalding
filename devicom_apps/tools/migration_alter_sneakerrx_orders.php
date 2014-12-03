1<?php
//Version 1.0 - 3/7/2012

set_time_limit(0);//no timout
ini_set('memory_limit', '1024M');

$migrationDirectory = '/chroot/home/rxkicksc/rxkicks.com/html/devicom_apps/tools/';

$xml_file = $migrationDirectory . 'orders.xml';

$xmlDoc = new DOMDocument();
$xmlDoc->load($xml_file);

$root = $xmlDoc->documentElement;              // get the first child node (the root)
$elms = $root->getElementsByTagName("*");      // gets all elements ("*") in root
$nr_elms = $elms->length;                      // gets the number of elements
$order = 0;
$productType = "";
// loop through all elements stored in $elms
for($i = 0; $i<$nr_elms; $i++) {
    $node = $elms->item($i);               // gets the current node

    if ($node->nodeName == 'order') {
	$order++;
	$itemNumber = 0;
	$status = "";
        echo $order . "\n";
    }
    
    if ($node->nodeName == 'store' && substr($node->nodeValue, 0, 3) == "Def") {
	$node->nodeValue = "sneakerrx_en";
    } else if ($node->nodeName == 'store' && substr($node->nodeValue, 0, 3) == "Mil") {
	$node->nodeValue = "military_en";
    }
    
    if ($node->nodeName == 'method' && $node->nodeValue == "fifthdimension_starcard") {
        $node->nodeValue = "purchaseorder";
    }

    if ($node->nodeName == 'state' && $node->nodeValue == "complete") {
	$node->nodeValue = "processing";
    }

    if ($node->nodeName == 'status' && $node->nodeValue == "processing") {
	$node->nodeValue = "In Processing";
    }

    if ($node->nodeName == 'status' && $node->nodeValue == "pending") {
	$node->nodeValue = "Pending";
    }
    
    if ($node->nodeName == 'state' && $node->nodeValue == "canceled") {
	$status = "canceled"; // set to blank at beginning of for each
    }

    if ($node->nodeName == 'status' && $status == "canceled") {
	$node->nodeValue = "Canceled";
    }

    if ($node->nodeName == 'item') {
	$OrderItem = $elms->item($i);
    }

    // Set to 0 otherwise import will show items shipped --shipped quantity will be reflected by tracking data import
    if ($node->nodeName == 'qty_shipped') {
	$node->nodeValue = '0.000'; 
    }
    
    if($node->nodeName=='product_type' && $node->nodeValue == "configurable") {
	$itemNumber++;
	$productType = "configurable";
    } else if ($node->nodeName=='product_type' && $node->nodeValue == "simple") {
        $productType = "simple";
    }
    if ($node->nodeName == 'sku' && $productType == "configurable") {
	$node->nodeValue = "Product " . $itemNumber; 
    } else if ($node->nodeName == 'parent_sku' && $productType == "simple") {
	$node->nodeValue = "Product " . $itemNumber;
    } else if ($node->nodeName == 'sku' && $productType == "simple") {
	//$realSku = $OrderItem->appendChild($xmlDoc->createElement('real_sku'));
	//$realSku->appendChild($xmlDoc->createTextNode($node->nodeValue));
	$node->nodeValue = "Product " . $itemNumber . "-OFFLINE";
    }
}

// save the new xml content in the same file and output its structure
if($xmlDoc->save('orders_new.xml')) {
  //echo htmlentities($xmlDoc->saveXML());
}

?>