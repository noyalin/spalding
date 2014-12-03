<?php
class Task_Tools_Model_SitemapGenerator extends Task_Tools_Model_Base{
    public function execute(){
        Mage::app('admin');
        Mage::app()->loadArea('adminhtml');
        $this->beginLog();

        $this->run();

        $this->endLog();
    }
    public function run(){
        // init and load sitemap model
        $id = 1;
        $sitemap = Mage::getModel('sitemap/sitemap');
        /* @var $sitemap Mage_Sitemap_Model_Sitemap */
        $sitemap->load($id);
        // if sitemap record exists
        if(!$sitemap->getId()){
            $this->errorLog();
            return;
        }
        $catalogLogsDirectory = $this->catalogLogsDirectory;
        try {
             $sitemap->generateXml();
             $this->transactionLogHandle(  "  ->SITEMAP       : CREATED\n");
        } catch (Mage_Core_Exception $e) {
             $this->transactionLogHandle(  "  ->ERROR         : See exception_log\n");

            //Append error to exception log file
            $exceptionLogHandle = fopen($catalogLogsDirectory . 'exception_log', 'a');
             $this->transactionLogHandle(  '->sitemap.xml - ' . $e->getMessage() . "\n");
            fclose($exceptionLogHandle);
        } catch (Exception $e) {
             $this->transactionLogHandle(  "  ->ERROR         : See exception_log\n");

            //Append error to exception log file
            $exceptionLogHandle = fopen($catalogLogsDirectory . 'exception_log', 'a');
             $this->transactionLogHandle(  "->sitemap.xml - Unable to generate the sitemap.\n");
            fclose($exceptionLogHandle);
        }

    }

    public function errorLog(){
         $this->transactionLogHandle(  "  ->ERROR         : See exception_log\n");

        //Append error to exception log file
        $exceptionLogHandle = fopen($this->catalogLogsDirectory . 'exception_log', 'a');
         $this->transactionLogHandle(  "->sitemap.xml - Sitemap not found.\n");
        fclose($exceptionLogHandle);
    }
    public function validate(){
        return false;
    }
}