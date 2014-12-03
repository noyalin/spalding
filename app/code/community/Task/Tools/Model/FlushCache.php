<?php
class Task_Tools_Model_FlushCache extends Task_Tools_Model_Base{
    public function run(){
        $types = array(
            2 => "block_html",
            7 => "full_page"
        );
        $this->transactionLogHandle( "  ->CACHE         : START\n");
        foreach ($types as $type) {
            Mage::app()->getCacheInstance()->cleanType($type);
            Enterprise_PageCache_Model_Cache::getCacheInstance()->cleanType($type);
            //Mage::dispatchEvent('adminhtml_cache_refresh_type', array('type' => $type));
            $this->transactionLogHandle( "  ->CACHE TYPE    : " . $type . "\n");
        }
        $this->transactionLogHandle( "  ->CACHE         : END\n");
    }


    public function validate(){
        return false;
    }
}