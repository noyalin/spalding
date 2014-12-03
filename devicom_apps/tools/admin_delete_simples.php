<?php
//CSV file format is "sku"

$toolsLogsDirectory = '/chroot/home/rxkicksc/rxkicks.com/html/devicom_apps/tools/logs/';

//Initialize magento environment for 'admin' store
require_once '/chroot/home/rxkicksc/rxkicks.com/html/app/Mage.php';
umask(0);
Mage::app('admin'); //admin defines default value for all stores including the Main Website

$handle = fopen('csv_files/simples_to_delete.csv', 'r');
$data = fgetcsv($handle, 0, ';','"');

$deleteLogHandle = fopen($toolsLogsDirectory . 'delete_log', 'a+');
fwrite($deleteLogHandle, "STARTING: " . $data[0] . "\n");

$i = 0;
while ($data = fgetcsv($handle, 0, ';','"')) {

    $i++;

    if ($product = Mage::getModel('catalog/product')->loadByAttribute('sku', $data[0])) {
	$product->delete();
	echo $product->getSku() . "\n";
	
	fwrite($deleteLogHandle, "DELETED  : " . $data[0] . "\n");

    } else {
	echo $data[0] . " not found\n";
	fwrite($deleteLogHandle, "NOT FOUND: " . $data[0] . "\n");
   }

    unset($product);
    echo "Item " . $i . "\n";
}
fclose($handle);

echo "SIMPLE DELETION COMPLETED!\n";
fwrite($deleteLogHandle, "COMPLETED: " . $data[0] . "\n");
	
?>
