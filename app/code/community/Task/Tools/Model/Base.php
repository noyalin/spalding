<?php
abstract class  Task_Tools_Model_Base{
    const CONF_NEWORDER_RECEIVER_ASPX_URL ="http://74.212.242.42/Net4/NewOrder/Receiver.aspx";
    const CONF_NEW_ORDER_POST_LIMIT = 25;
    // Constant Contact API info
    const CREATE_CONTACT_CC_USERNAME = 'davis.du';//'dennis.zhang';
    const CREATE_CONTACT_CC_PASSWORD = 'dirdir';
    /**************************************/
    /* CONSTANT CONTACT CONFIGURATIONS
    /**************************************/

// 12 for rxkicks.com and 14 for xpairs.com
    const CREATE_CONTACT_LIST_NEWSLETTER =  12;
// 11 for rxkicks.com and 15 for xpairs.com
    const CREATE_CONTACT_LIST_REVIEWS = 11;
// Points earned for subscribing
    const CREATE_CONTACT_POINTS_EARNED = 0;

    const DEBUG_MODE = true;



    // This is a global configuration for system notifications. All on/off
    const CONF_SYSTEM_NOTIFICATION_ENABLED = true;
    //const CONF_SYSTEM_NOTIFICATION_TO_ADDRESS = "scott.crain@sneakerhead.com";
    const CONF_SYSTEM_NOTIFICATION_TO_ADDRESS = "davis.du@sneakerhead.com";
    const CONF_SYSTEM_NOTIFICATION_CC_ADDRESS =  "davis.du@sneakerhead.com";
    const CONF_SYSTEM_NOTIFICATION_FROM_ADDRESS = "admin@sneakerhead.cn";

    public $coreDir;
    public $catalogLogsDirectory;
    public $catalogReceivedDirectory;
    public $catalogProcessedDirectory;
    public $transactionLogHandle;

    public $filename = null;
    public $configurableProductsGroupsToUpdate;
    public $receivedDirectory;
    public $processLockFilename;
    public $inventoryDirectory;
    public $temporaryDirectory;
    public $newInventoryDirectory;
    public $oldInventoryDirectory;
    public $contents;

    public $createLockFile =false;

    //for xml generator
    public $categoryInventoryDirectory;
    public $newCategoryInventoryDirectory;
    public $oldCategoryInventoryDirectory;
    public $transactionCategoryXmlGeneratorLogHandle;
    public $catalogDir;

    /*
     * For category update
     */
    public $categoryListToNotProcess = array(6, 7, 9, 10, 11, 12, 25, 30, 75, 123, 150, 375, 542, 543, 544, 545);
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
        $this->coreDir = Mage :: getBaseDir()."/devicom_apps/catalog/";
        $this->catalogLogsDirectory = $this->coreDir . 'logs/';
        $this->catalogReceivedDirectory = $this->coreDir . 'received/';
        $this->catalogProcessedDirectory = $this->coreDir . 'processed/';

        $needDir = $this->coreDir;
        $this->catalogDir = $needDir;
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
    public function execute(){
        if($this->validate()){
            return; //No execute
        }
        Mage::app('admin');
        Mage::app()->loadArea('adminhtml');
        $this->beginLog();
        try{
            $this->run();
        }catch(Exception $e){
            $this->transactionLogHandle(  "  ->ERROR         : See exception_log ");

            //Append error to exception log file
            $exceptionLogHandle = fopen($this->catalogLogsDirectory . 'exception_log', 'a');
            $this->transactionLogHandle( '->' .  $this->getFileName() . " - " . $e->getMessage() . "\n");
            fclose($exceptionLogHandle);
        }
        $this->endLog();
    }
    public function beginLog(){
        $realTime = $this->realTime();
        $this->transactionLogHandle("->BEGIN PROCESSING: " . $this->getFileName() );
        $this->transactionLogHandle("  ->START TIME    : " .$realTime[5] . '/' . $realTime[4] . '/' . $realTime[3] . ' ' . $realTime[2] . ':' . $realTime[1] . ':' . $realTime[0]);
        $this->createLockFile($realTime);
    }

    public function getFileName(){
        $fileName = null;
        if(isset($this->filename)){
            $fileName = $this->filename;
        }
        return $fileName;
    }
    public function createLockFile($realTime){
        if($this->createLockFile){
            //Create process.lock to stop further processing of incremental and/or full updates
            $processLockFilename = 'reindexer_process_' . $realTime[5] . $realTime[4] . $realTime[3] . '_' . $realTime[2] . $realTime[1] . $realTime[0]. $this->filename . '.lock';
            $processLockHandle = fopen($this->catalogLogsDirectory . $processLockFilename, 'w+');
            fclose($processLockHandle);
            $this->processLockFilename = $processLockFilename;
            $this->transactionLogHandle( "  ->LOCK CREATED  : " . $processLockFilename . "\n");
        }
    }

    public abstract function validate();
    public function sendNotification($this_subject, $this_message, $cc = false, $subject_override = false) {

        $to = self :: CONF_SYSTEM_NOTIFICATION_TO_ADDRESS;
        $to .= ($cc) ? "," . self :: CONF_SYSTEM_NOTIFICATION_CC_ADDRESS : "";
        $subject = ($subject_override) ? $this_subject : "System Notification - $this_subject";
        $message = "This is a system notification.\r\n\r\n";
        $message .= "$this_message";
        $message .= "\r\n\r\n";

        $headers = 'From: ' . self :: CONF_SYSTEM_NOTIFICATION_FROM_ADDRESS . "\r\n" .
            'Reply-To: ' . self :: CONF_SYSTEM_NOTIFICATION_FROM_ADDRESS . "\r\n" .
            'X-Mailer: PHP/' . phpversion();

        if (self :: CONF_SYSTEM_NOTIFICATION_ENABLED){
            mail($to, $subject, $message, $headers);
        }
        echo "\n". $message."\n";
    }
    public function transactionLogHandle($msg){
        $msg .= " \n";
        $root_dir = dirname(__FILE__);
        fwrite($this->transactionLogHandle, $msg);
    }
    public function endLog(){
        if($this->createLockFile){
            unlink($this->catalogLogsDirectory . $this->processLockFilename);
            $this->transactionLogHandle( "  ->LOCK REMOVED  : " . $this->processLockFilename . "\n");
        }
        $endTime = date("Y-m-d H:i:s");
        $this->transactionLogHandle(  "  ->FINISH TIME   : " . $endTime);
        $this->transactionLogHandle( "->END PROCESSING  : " . $this->filename . "\n");

        //Close transaction log
        fclose($this->transactionLogHandle);
    }

    public static function createFolder($path){
        if(!file_exists($path)){
            mkdir($path,0777);
        }
        return $path;
    }

    public function displayMsg($msg){
        $msg = "\n $msg \n";
        echo $msg;
    }

    /**
     * @param $directory
     * @param $type
     * @return bool
     * Used in function validate
     */
    public function  checkFileExist($directory,$type){
        if(count(glob($directory . $type)) > 0){
            return true;
        }
        return false;
    }
    function realTime($time = null, $isAssociative = false) {

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
        return array_combine(array('tm_sec', 'tm_min', 'tm_hour', 'tm_mday', 'tm_mon', 'tm_year', 'tm_wday', 'tm_yday', 'tm_isdst'), $explodedTime);
    }

    /**
     * @return array
     * In function updateDevicomStock of class Shoe_Maker_Model_FullInventoryUpdate
     * mysqlimport need db config info
     */
    public function getDbInfo(){
        $config  = Mage::getConfig()->getResourceConnectionConfig("default_setup");

        $result = array(
            "host" => $config->host,
            "user" => $config->username,
            "pass" => $config->password,
            "dbname" => $config->dbname
        );
        return $result;
    }


}