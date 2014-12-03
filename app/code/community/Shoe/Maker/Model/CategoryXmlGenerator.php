<?php

/**
 * Class Shoe_Maker_Model_CategoryXmlGenerator
 * This script do no need xml file from receive folder
 */
class Shoe_Maker_Model_CategoryXmlGenerator extends Shoe_Maker_Model_UpdateBase{
    public $productsArray;

    public function  __construct(){
        parent::__construct();
        $needDir = $this->catalogDir;
        //for xml generator
        $this->categoryInventoryDirectory = self::createFolder( $needDir.'/..' . '/inventory/category/' );
        $this->newCategoryInventoryDirectory = self::createFolder( $needDir.'/..' . '/inventory/new_category/' );
        $this->oldCategoryInventoryDirectory = self::createFolder( $needDir.'/..' . '/inventory/old_category/' );
        $this->transactionCategoryXmlGeneratorLogHandle = fopen($this->catalogLogsDirectory . 'category_xml_generator_log', 'a+');

    }
    public function run(){
        $this->generateProductArray();

        $this->transactionCategoryXmlGeneratorLog( "    ->BUILT ARRAY : Inventory");

//        $this->copyHtaccessFile();


        $this->generateXmlFile();

        $this->generateTestCategoryFile();


        //Rename active inventory directory to old_directory
        rename($this->categoryInventoryDirectory, $this->oldCategoryInventoryDirectory);
        //Rename new inventory directory to make active and restore symlink
        rename($this->newCategoryInventoryDirectory, $this->categoryInventoryDirectory);
        $this->transactionCategoryXmlGeneratorLog( "    ->RENAMED DIRS: Inventory ");

        $this->removeOldFile();

    }

    public function copyHtaccessFile(){
        // Copy .htaccess
        $file = $this->categoryInventoryDirectory . '.htaccess';
        $newfile = $this->newCategoryInventoryDirectory . '.htaccess';

        if (!copy($file, $newfile)) {
            //Should be exception but will stop processing?
        }
        $this->transactionCategoryXmlGeneratorLog( "    ->ADD HTACCESS: Inventory\n");
    }
    public function generateProductArray(){
        // Generate category XML File
        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');
        $productQuery = "SELECT * FROM `devicom_inventory` WHERE `parent_sku` IS NOT NULL ORDER BY `sort_order` ASC";

        $products = $readConnection->fetchAll($productQuery);

        // Build array from result set
        $productsArray = array();
        foreach ($products as $product) {
            $productCategories = explode(',', $product['category_ids']);
            if (count($productCategories) > 1) {
                foreach ($productCategories as $productCategory) {
                    if (is_null($product['parent_product_id']) || $product['qty'] <= '0') {
                        $productsArray[$productCategory][$product['parent_sku']][$product['sku']] = null;
                    } else {
                        if ($product['label'] == 'One Size') {
                            $productsArray[$productCategory][$product['parent_sku']][$product['sku']] = $product['label'];
                        } else {
                            $skuArr = explode('-', $product['sku']);
                            $labelArr = explode(" ",$product['label']);
                            $eurSize = end($labelArr);
                            $productsArray[$productCategory][$product['parent_sku']][$product['sku']] = $eurSize;
                        }
                    }
                }
            } else {
                if (is_null($product['parent_product_id']) || $product['qty'] <= '0') {
                    $productsArray[$productCategory][$product['parent_sku']][$product['sku']] = null;
                } else {
                    if ($product['label'] == 'One Size') {
                        $productsArray[$productCategory][$product['parent_sku']][$product['sku']] = $product['label'];
                    } else {
                        $skuArr = explode('-', $product['sku']);
                        $labelArr = explode(" ",$product['label']);
                        $eurSize = end($labelArr);
                        $productsArray[$productCategory][$product['parent_sku']][$product['sku']] = $eurSize;
                    }
                }
            }
        }
        $this->productsArray = $productsArray;
    }
    public function generateXmlFile(){
        $this->transactionCategoryXmlGeneratorLog( "    ->BUILD FILES : Inventory\n");
        $productsArray = $this->productsArray;
        foreach ($productsArray as $categoryId => $productSet) {

            //Creates XML string and XML document from the DOM representation
            $itemFilename = 'category-' . $categoryId . '.xml';

            $dom = new DomDocument('1.0');

            $productsTag = $dom->appendChild($dom->createElement('Products'));

            foreach ($productSet as $productSku => $productSizes) {

                $productTag = $productsTag->appendChild($dom->createElement('Product'));

                $skuTag = $productTag->appendChild($dom->createElement('Sku'));
                $skuTag->appendChild($dom->createTextNode($productSku));

                $sizes = "";
                $quantityFound = null;
                foreach ($productSizes as $productSize) {
                    if (!is_null($productSize)) {
                        $sizes .= " " . $productSize;
                        $quantityFound = 1;
                    }
                }

                $sizeTag = $productTag->appendChild($dom->createElement('Sizes'));
                if (is_null($quantityFound)) {
                    $sizeTag->appendChild($dom->createTextNode('Sold Out'));
                } else {
                    if ($sizes == ' One Size') {
                        $sizeTag->appendChild($dom->createTextNode('One Size'));
                    } else {
                        $sizeTag->appendChild($dom->createTextNode('Size' . $sizes));
                    }
                }
            }

            // Make the output pretty
            $dom->formatOutput = true;

            // Save the XML string
            $xml = $dom->saveXML();

            //Write file to inventory directory
            $inventoryHandle = fopen($this->newCategoryInventoryDirectory . $itemFilename, 'w');
            fwrite($inventoryHandle, $xml);
            fclose($inventoryHandle);
            $this->transactionCategoryXmlGeneratorLog( "    ->CREATED XML : category-" . $categoryId . ".xml ");
        }
    }

    public function removeOldFile(){
        //Remove old inventory XML files
        $handle = opendir($this->oldCategoryInventoryDirectory);

        while (($file = readdir($handle)) !== false) {
            @unlink($this->oldCategoryInventoryDirectory . '/' . $file);
        }
        $this->transactionCategoryXmlGeneratorLog( "    ->REMOVE FILES: Inventory\n");
        closedir($handle);

        //Remove old inventory directory
        if (!rmdir($this->oldCategoryInventoryDirectory)) {
            //die('Failed to create folders...');
        }
        $this->transactionCategoryXmlGeneratorLog( "    ->REMOVED DIR : Inventory\n");
    }
    public function generateTestCategoryFile(){
        //Build test category file

        $dom = new DomDocument('1.0');

        $testTag = $dom->appendChild($dom->createElement('Test'));
        $testTag->appendChild($dom->createTextNode('Test'));

        // Make the output pretty
        $dom->formatOutput = true;

        // Save the XML string
        $xml = $dom->saveXML();
        $inventoryHandle = fopen($this->newCategoryInventoryDirectory . 'category-test.xml', 'w');
        fwrite($inventoryHandle, $xml);
        fclose($inventoryHandle);
        $this->transactionCategoryXmlGeneratorLog( "      ->CREATE XML: category-test.xml");

        $this->transactionCategoryXmlGeneratorLog( "    ->FILES BUILT : Inventory\n");

    }
    public function beginLog(){
        $realTime = $this->realTime();
        $this->transactionCategoryXmlGeneratorLog( "->BEGIN PROCESSING:");
        $this->transactionCategoryXmlGeneratorLog("  ->START TIME    : " . $realTime[5] . '/' . $realTime[4] . '/' . $realTime[3] . ' ' . $realTime[2] . ':' . $realTime[1] . ':' . $realTime[0] . "\n");
        $this->createLockFile($realTime);
    }
    public function createLockFile($realTime){
        //Create process.lock to stop further processing of incremental and/or full updates
        $processLockFilename = 'xml_process_' . $realTime[5] . $realTime[4] . $realTime[3] . '_' . $realTime[2] . $realTime[1] . $realTime[0] . '.lock';
        $processLockHandle = fopen($this->catalogLogsDirectory . $processLockFilename, 'w+');
        fclose($processLockHandle);
        $this->processLockFilename =$processLockFilename;
        $this->transactionCategoryXmlGeneratorLog("  ->LOCK CREATED  : " . $processLockFilename);
    }
    public function removeXmlFileWhenFailed(){

    }
    public function validate(){
        if(count(glob($this->catalogLogsDirectory . 'xml_process*')) > 0){
            echo "xml_process exist. exit... There is no xml file to be executed \n";
            return true;
        }
        if(file_exists($this->catalogLogsDirectory . 'stage')){
            echo "stage file exist. exit... There is no xml file to be executed \n";
            return true;
        }
        return false;
    }

    public function removeLockFile(){
        //Remove xml_process to allow processing of incremental and/or full updates
        if (file_exists($this->catalogLogsDirectory . $this->processLockFilename)) {
            unlink($this->catalogLogsDirectory . $this->processLockFilename);
            $this->transactionCategoryXmlGeneratorLog(  "  ->LOCK REMOVED  : " . $this->processLockFilename . "\n");
        }

        $endTime = $this->realTime();
        $this->transactionCategoryXmlGeneratorLog(  "  ->FINISH TIME   : " . $endTime[5] . '/' . $endTime[4] . '/' . $endTime[3] . ' ' . $endTime[2] . ':' . $endTime[1] . ':' . $endTime[0] . "\n");
        $this->transactionCategoryXmlGeneratorLog(  "->END PROCESSING  :\n");

        //Close transaction log
        fclose($this->transactionCategoryXmlGeneratorLogHandle);
    }
    //There is no xml from received. SO override this functino to be empty
    public function removeFile(){
    }


}