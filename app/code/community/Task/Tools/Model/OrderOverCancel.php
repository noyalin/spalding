<?php
class Task_Tools_Model_OrderOverCancel extends Task_Tools_Model_Base{

    public function __construct(){
        parent::__construct();
    }
    public function execute(){
    	
    	$overTime = date('Y-m-d H:i:s',strtotime("-24 hours"));
    	$orders = Mage::getModel('sales/order')->getCollection()
    	->addFieldToFilter('state', 'new')
    	->addFieldToFilter('status', 'alipay_wait_buyer_pay')
    	->addFieldToFilter('created_at', array('lt'=>$overTime));
    	if(count($orders) > 0){
    		foreach ($orders as $order){
//    			$id = $order->getIncrementId();
//     			if($id == '100000004'){
//     				$order->setState('canceled');
//     				$order->setStatus('canceled');
//     				$order->save();
    				//exit;
//     			}else{
    				//break;
//     			}
    			$order->setState('canceled');
    			$order->setStatus('canceled');
    			$order->save();
    		}	
    	}
    	echo "update over";
    }        

   
    public function validate(){
        return false;
    }

   

}