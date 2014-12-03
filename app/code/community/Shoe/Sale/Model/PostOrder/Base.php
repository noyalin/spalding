<?php
class  Shoe_Sale_Model_PostOrder_Base extends Shoe_Sale_Model_UpdateBase{
    public function execute(){
        if($this->validate()){
            //can not execute
            return;
        }
        $this->beginLog();
        $this->run();

        //remove lock file
        $this->removeLockFile();
        $this->removeFile();
    }
    public function run(){
        $this->readAllNewOrderFiles();
        $this->sendXml();
    }

    public function createLockFile($realTime){
        /* Create process_lock so that these don't overlap
 * Use "_lock" extension so as to be ignored by other sales processes looking for ".lock" */
        $processLockFilename = 'post_new_order_process_' . $realTime[5] . $realTime[4] . $realTime[3] . '_' . $realTime[2] . $realTime[1] . $realTime[0] . '.lock';
        $this->processLockFilename =$processLockFilename;
        $processLockHandle = fopen($this->salesLogsDirectory . $processLockFilename, 'w+');
        fclose($processLockHandle);
        $this->transactionLogHandle( "  ->LOCK CREATED      : " . $processLockFilename . "\n");
    }

    public function readAllNewOrderFiles(){
        /* Read all "new_order" files in POST directory into an array */

        $newOrders = $this->readFile();
        /* Nothing to do */
        if (!count($newOrders)) {
            echo "\n There is no file name contain new_order, exit...\n";
            return;
        }
        /* Sort by filename (timestamp) to handle in order */
        sort($newOrders);
        $this->newOrders = $newOrders;
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

    public static function createFolder($path){
        if(!file_exists($path)){
            mkdir($path,0777);
        }
        return $path;
    }

    public function removeFile(){

    }

}