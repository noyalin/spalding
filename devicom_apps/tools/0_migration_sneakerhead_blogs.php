<?php
//NOTES
//1. Delete CategoryName, date, EntryCmp, and assocskus
//2. Change PostTime to YEAR-MM-DD
//3. For the four custom blogs, set the images value to 1
//4. For the four custom blogs, remove the blogContentWrap DIV and date
//5. Remove BottomLink

set_time_limit(0);//no timout
ini_set('memory_limit', '1024M');

$toolsLogsDirectory = '/chroot/home/rxkicksc/rxkicks.com/html/devicom_apps/tools/logs/';

// initialize magento environment for 'default' store
require_once '../../app/Mage.php';
umask(0);
Mage::app('admin'); // Default or your store view name.

// Get resource
$resource = Mage::getSingleton('core/resource');
$writeConnection = $resource->getConnection('core_write');

//Open transaction log
$transactionLogHandle = fopen($toolsLogsDirectory . 'blog_import_transaction_log', 'a+');
fwrite($transactionLogHandle, "->BEGIN PROCESSING...\n");

// Build array from csv file
fwrite($transactionLogHandle, "  ->BUILDING ARRAY...\n");

//Open CSV
//Formatting should be...
$handle = fopen("csv_files/blogs.csv", "r");
$data = fgetcsv($handle, 0, ',','"');

while ($data = fgetcsv($handle, 0, ',','"')) {

    $blogs[$data[1]]['cat_id'] = $data[0];
    $blogs[$data[1]]['post_id'] = $data[1];
    $blogs[$data[1]]['name'] = addslashes($data[2]);
    $blogs[$data[1]]['post_content'] = addslashes($data[3]);
    $blogs[$data[1]]['meta_keywords'] = addslashes($data[4]);
    $blogs[$data[1]]['identifier'] = $data[5];
    $blogs[$data[1]]['meta_description'] = addslashes($data[6]);
    $blogs[$data[1]]['title'] = addslashes($data[7]);

    $date = explode('-', $data[8]);
    $blogs[$data[1]]['created_time'] = $date[2] . '-' . $date[0] . '-' . $date[1];
    
    $blogs[$data[1]]['images'] = $data[9];
    $blogs[$data[1]]['short_content'] = addslashes($data[10]);
    $blogs[$data[1]]['shopping_link'] = $data[11];
}

fwrite($transactionLogHandle, "  ->INSERTING RECORDS...\n");

$resource = Mage::getSingleton('core/resource');
$writeConnection = $resource->getConnection('core_write');

//Truncate tables
$query = "TRUNCATE TABLE `aw_blog`";
$results = $writeConnection->query($query);
$query = "TRUNCATE TABLE `aw_blog_comment`";
$results = $writeConnection->query($query);
$query = "TRUNCATE TABLE `aw_blog_post_cat`";
$results = $writeConnection->query($query);
$query = "TRUNCATE TABLE `aw_blog_store`";
$results = $writeConnection->query($query);
$query = "TRUNCATE TABLE `aw_blog_tags`;";
$results = $writeConnection->query($query);

foreach ($blogs as $blogArray) {
    $query = "INSERT INTO `aw_blog` (`post_id`, `name`, `title`, `post_content`, `status`, `created_time`, `update_time`, `identifier`, `user`, `update_user`, `meta_keywords`, `meta_description`, `comments`, `tags`, `short_content`, `images`, `shopping_link`) VALUES
(" . $blogArray['post_id'] . ", '" . $blogArray['name'] . "', '" . $blogArray['title'] . "', '" . $blogArray['post_content'] . "', 1, '" . $blogArray['created_time'] . "', '" . $blogArray['created_time'] . "', '" . $blogArray['identifier'] . "', '', '', '" . $blogArray['meta_keywords'] . "', '" . $blogArray['meta_description'] . "', 0, '', '" . $blogArray['short_content'] . "', '" . $blogArray['images'] . "', '" . $blogArray['shopping_link'] . "')";
    $results = $writeConnection->query($query);

    $query = "INSERT INTO `aw_blog_post_cat` (`cat_id`, `post_id`) VALUES (" . $blogArray['cat_id'] . ", " . $blogArray['post_id'] . ")";
    $results = $writeConnection->query($query);
	    
    $query = "INSERT INTO `aw_blog_store` (`post_id`, `store_id`) VALUES (" . $blogArray['post_id'] . ", 21)";
    $results = $writeConnection->query($query);

    fwrite($transactionLogHandle, "  ->INSERTED BLOG " . $blogArray['post_id'] . "\n");

}

fwrite($transactionLogHandle, "  ->MIGRATION COMPLETED\n");
fwrite($transactionLogHandle, "->END PROCESSING\n");
//Close transaction log
fclose($transactionLogHandle);

?>