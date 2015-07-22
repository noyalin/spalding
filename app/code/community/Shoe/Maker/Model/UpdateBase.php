<?php
class  Shoe_Maker_Model_UpdateBase extends Shoe_Maker_Model_Base{

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
                $this->contents = file_get_contents($this->receivedDirectory . $this->filename);

                $this->run();

            }catch(Exception $e){
                $this->sendNotification( 'Shoe_Maker_Model_UpdateBase Exception', $this->filename . " - " . $e->getMessage());

                $this->transactionLogHandle(  "  ->ERROR         : See exception_log ");

                //Append error to exception log file
                $exceptionLogHandle = fopen($this->catalogLogsDirectory . 'exception_log', 'a');
                fwrite($exceptionLogHandle, '->' . $this->filename . " - " . $e->getMessage() . "\n");
                fclose($exceptionLogHandle);
                $this->removeXmlFileWhenFailed();


                $this->removeCsvFile();
            }

            //remove lock file
            $this->removeLockFile();
            $this->removeFile();
    }
    public function removeXmlFileWhenFailed(){
        //Move XML file to failed directory
        rename($this->receivedDirectory . $this->filename, $this->failedDirectory . $this->filename);
    }
    /**
     * Override in class FullInventoryUpdate
     */
    public function removeCsvFile(){

    }

    public function validate(){

    }

    public function createLockFile($realTime){
        //Create process.lock to prevent another catalog process from starting
        $processLockFilename = 'process_'. $realTime[5] . $realTime[4] . $realTime[3] . '_' . $realTime[2] . $realTime[1] . $realTime[0]. $this->filename . '.lock';
        $this->processLockFilename =$processLockFilename;
        $processLockHandle = fopen($this->catalogLogsDirectory . $processLockFilename, 'w+');
        shell_exec("chmod 777 ".$this->catalogLogsDirectory . $processLockFilename);
        fclose($processLockHandle);
        $this->transactionLogHandle("  ->LOCK CREATED  : " . $processLockFilename);
    }
    public function removeLockFile(){
        //Remove process.lock to allow processing of incremental and/or full updates
        $diskFile = $this->catalogLogsDirectory . $this->processLockFilename;
        if (file_exists($diskFile)) {
            unlink($diskFile);
            $this->transactionLogHandle(  "  ->LOCK REMOVED  : " . $this->processLockFilename  );
        }

        $endTime = date("Y-m-d H:i:s");
        $realTime = $this->realTime();
        $this->transactionLogHandle(  "  ->FINISH TIME   : " . $realTime[5] . '/' . $realTime[4] . '/' . $realTime[3] . ' ' . $realTime[2] . ':' . $realTime[1] . ':' . $realTime[0]);
        $this->transactionLogHandle( "->END PROCESSING  : " . $this->filename . "\n");

        //Close transaction log
        fclose($this->transactionLogHandle);
    }
    public static function getXmlElementFromString($contents){
        $rootXmlElement = new SimpleXMLElement($contents);
        $products = array();
        foreach ($rootXmlElement->children() as $child) {
            $products[$child->getName()] = $child->getName();
        }
        return array($rootXmlElement,$products);
    }

    public function transactionLogHandle($msg){
        $str = date("YmdHis")."\t".$msg." \n";
//        $msg .= " \n";
//        $root_dir = dirname(__FILE__);
        fwrite($this->transactionLogHandle, $str);
    }
    public function transactionCategoryXmlGeneratorLog($msg){
        $str = date("YmdHis")."\t".$msg." \n";
//        $msg .= " \n";
//        $root_dir = dirname(__FILE__);
        fwrite($this->transactionCategoryXmlGeneratorLogHandle, $str);
    }
    public function removeFile(){
        rename($this->receivedDirectory . $this->filename, $this->processedDirectory . $this->filename);
    }


}