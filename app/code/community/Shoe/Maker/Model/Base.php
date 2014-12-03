<?php
abstract class  Shoe_Maker_Model_Base extends Task_Tools_Model_Base{
    public $transactionLogHandle;
    public $filename = null;
    public $configurableProductsGroupsToUpdate;
    public $receivedDirectory;
    public $catalogLogsDirectory;
    public $processLockFilename;
    public $inventoryDirectory;
    public $temporaryDirectory;
    public $newInventoryDirectory;
    public $oldInventoryDirectory;
    public $contents;

    //for xml generator
    public $categoryInventoryDirectory;
    public $newCategoryInventoryDirectory;
    public $oldCategoryInventoryDirectory;
    public $transactionCategoryXmlGeneratorLogHandle;
    public $catalogDir;

    /*
     * For category update
     */
//    public $categoryListToNotProcess = array(6, 7, 9, 10, 11, 12, 25, 30, 75, 123, 150, 375, 542, 543, 544, 545);
    public $categoryListToNotProcess = array();
    public $mobile_exclude_categories = array(
        300,	// Blog/Brands
        375,	// Sneakerfolio
        546,	// Special Category
        2747,	// New Item Pending Category
        2760,	// Pending Categories
        238,	// Alertbot
        237,	// Nextopia Search Template
    );


    public function __construct(){
        $root_dir = dirname(__FILE__);

//        $needDir = "$root_dir/../../../../../../devicom_apps/catalog";
        $needDir = Mage::getBaseDir(). "/devicom_apps/catalog";
        $this->catalogDir = $needDir;
        $this->catalogLogsDirectory = self::createFolder($needDir . '/logs/');
        $this->receivedDirectory = self::createFolder ( $needDir . '/received/' );
        $this->transactionLogHandle = fopen($this->catalogLogsDirectory . 'transaction_log', 'a+');
        shell_exec('chmod 777 '.$this->catalogLogsDirectory . 'transaction_log');
        $this->inventoryDirectory = self::createFolder( $needDir.'/..' . '/inventory/product/' );
        $this->processedDirectory = self::createFolder( $needDir . '/processed/' );
        $this->failedDirectory = self::createFolder ( $needDir . '/failed/' );
        $this->temporaryDirectory = self::createFolder ( $needDir . '/temporary/' );

        $this->newInventoryDirectory = self::createFolder( $needDir.'/..' . '/inventory/new_product/' );
        $this->oldInventoryDirectory = self::createFolder( $needDir.'/..' . '/inventory/old_product/' );
    }
}