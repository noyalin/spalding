<?php
set_time_limit(0); //no timout
ini_set('memory_limit', '1024M');
class Task_Tools_Model_StockReindexer extends Task_Tools_Model_Base{
    public $createLockFile = true;

    public function run(){
        $this->transactionLogHandle(  "  ->INDEXING\n");
        // Reindex urls
        $processIds = array(8);

        foreach ($processIds as $processId) {
            $this->transactionLogHandle( "    ->START       : " . $processId . "\n");
            $process = Mage::getModel('index/process')->load($processId);
            $process->reindexAll();
            $this->transactionLogHandle(  "    ->END         : " . $processId . "\n");
        }

        $this->transactionLogHandle(  "  ->COMPLETED\n");
    }


    public function validate(){
        //Check that no lock exists (indicating file is being written or processed by another script) and, if any, grab the oldest incremental_category_update to begin processing
        if($this->checkFileExist($this->catalogLogsDirectory,"reindexer_process*")){
            $this->displayMsg("reindexer_process exist. exit now...") ;
            return true;
        }
        if($this->checkFileExist($this->catalogLogsDirectory,"stage")){
            $this->displayMsg("stag file exist. Full update do not finish. exit now...") ;
            return true;
        }

        return false;
    }

}