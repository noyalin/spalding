<?php

class Task_Tools_Model_CouponOrderUpdate extends Mage_Core_Model_Abstract
{

    public function execute()
    {    	
    	$orders = Mage::getModel('sales/order')->getCollection();
    	echo count($orders);
    	$i = 1;
    	if(count($orders) > 0){
    		foreach ($orders as $order){
    			echo $i."\r\n";
    			Mage::getModel('couponorder/couponorder')->updateDataByOrder($order);
    			$i++;
    		}
    	}
    	echo "update over";
    }

    public function validate()
    {
        return false;
    }

}

?>