<?php
class  Shoe_Sale_Model_UpdateBase extends Shoe_Sale_Model_Base{
    public $salesLogsDirectory;
    public $postDirectory;
    public $sentDirectory;
    public $failedDirectory;
    public $saleDir;
    public $fileName;
    public $transactionLogHandle;
    public  $processLockFilename;
    public  $receivedDirectory;
    public $processedDirectory;

    public function __construct(){
        $needDir = Mage :: getBaseDir()."/devicom_apps/sales";;
        $this->saleDir = $needDir;
        $this->salesLogsDirectory = self::createFolder($needDir . '/logs/');
        $this->postDirectory = self::createFolder($needDir . '/post/');
        $this->sentDirectory = self::createFolder($needDir . '/sent/');
        $this->failedDirectory = self::createFolder($needDir . '/failed/');
        $this->processedDirectory  = self::createFolder($needDir . '/processed/');
        $this->receivedDirectory  = self::createFolder($needDir . '/received/');
        $this->transactionLogHandle = fopen($this->salesLogsDirectory . 'post_new_order_transaction_log', 'a+');

   }
    public function getContents(){
        if(isset($this->filename))
            $this->contents = file_get_contents($this->postDirectory . $this->filename);
    }
    public function execute(){
        if($this->validate()){
            //can not execute
            return;
        }
        Mage::app('admin'); //admin defines default value for all stores including the Main Website
        Mage::app()->loadArea('adminhtml');
        $this->beginLog();
        try{
            //Main logic begin
            $this->getContents();

            $this->run();

        }catch(Exception $e){
            $this->transactionLogHandle(  "  ->ERROR         : See exception_log ");

            //Append error to exception log file
            $exceptionLogHandle = fopen($this->salesLogsDirectory . 'exception_log', 'a');
            $fileName = null;
            if(isset($this->filename)){
                $fileName =$this->filename;
            }
            fwrite($exceptionLogHandle, '->' . $fileName . " - " . $e->getMessage() . "\n");
            fclose($exceptionLogHandle);
            $this->removeXmlFileWhenFailed();
            $this->removeCsvFile();
        }

        //remove lock file
        $this->removeLockFile();
        $this->removeFile();
    }

    public function sendPost($xml){
        $url = self::CONF_NEWORDER_RECEIVER_ASPX_URL;

        $context = stream_context_create(array(
            'http' => array(
                'method'  => 'POST',
                'header'  => "Content-Type: text/xml",
                'content' => $xml
            )));

        // Send post
        $response = file_get_contents($url, false, $context);
        return $response;
    }
    public function removeXmlFileWhenFailed(){
        if(isset( $this->filename ) && file_exists($this->postDirectory . $this->filename)){
            //Move XML file to failed directory
            rename($this->postDirectory . $this->filename, $this->failedDirectory . $this->filename);
        }

    }
    public function removeCsvFile(){

    }

    public function validate(){
        return false;
    }
    public function createLockFile($realTime){
        if($this->createLockFile){
            /* Create process_lock so that these don't overlap
 * Use "_lock" extension so as to be ignored by other sales processes looking for ".lock" */
            $processLockFilename = 'process_' . $realTime[5] . $realTime[4] . $realTime[3] . '_' . $realTime[2] . $realTime[1] . $realTime[0] . '.lock';
            $this->processLockFilename =$processLockFilename;
            $processLockHandle = fopen($this->salesLogsDirectory . $processLockFilename, 'w+');
            fclose($processLockHandle);
            $this->transactionLogHandle( "  ->LOCK CREATED      : " . $processLockFilename . "\n");
        }

    }
    public function removeLockFile(){
        /* Remove process lock */
        if ($this->processLockFilename && file_exists($this->salesLogsDirectory . $this->processLockFilename)){
            unlink($this->salesLogsDirectory . $this->processLockFilename);
            $this->transactionLogHandle( "  ->LOCK REMOVED      : " . $this->processLockFilename . "\n");
        }
        $endTime = $this->realTime();
        $this->transactionLogHandle( "  ->FINISH TIME       : " . $endTime[5] . '/' . $endTime[4] . '/' . $endTime[3] . ' ' . $endTime[2] . ':' . $endTime[1] . ':' . $endTime[0] . "\n");
        $this->transactionLogHandle( "->END PROCESSING      :  ");

        /* Close transaction log */
        fclose($this->transactionLogHandle);
    }
    public function transactionLogHandle($msg){
        $msg .= " \n";
        fwrite($this->transactionLogHandle, $msg);
    }
    public function removeFile(){
        //Move XML file to failed directory
        if(isset($this->filename) && file_exists($this->postDirectory . $this->filename)){
            rename($this->postDirectory . $this->filename, $this->processedDirectory . $this->filename);
        }
    }

    public function readFileFromDirectory($postDirectory){
        $newOrders = array();
        if (is_dir($postDirectory)) {
            if (($dh = opendir($postDirectory))) {
                while (($file = readdir($dh)) !== false) {
                    if (is_file($postDirectory . $file)
                        && preg_match('/new_order/', $file)) {
                        $newOrders[] = $file;
                    }
                }
                closedir($dh);
            }
        }
        return $newOrders;
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