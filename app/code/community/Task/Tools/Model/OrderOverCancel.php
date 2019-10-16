<?php
class Task_Tools_Model_OrderOverCancel extends Task_Tools_Model_Base{

    public function __construct(){
        parent::__construct();
    }
    public function execute(){
    	
    	$resource = Mage::getSingleton('core/resource');
    	$readConnection = $resource->getConnection('core_read');
    	$overTime = date('Y-m-d H:i:s',strtotime("-4 hours"));
    	$orders = Mage::getModel('sales/order')->getCollection()
    	->addFieldToFilter('state', 'new')
    	->addFieldToFilter('status', 'alipay_wait_buyer_pay')
    	->addFieldToFilter('created_at', array('lt'=>$overTime));
    	if(count($orders) > 0){
    		foreach ($orders as $order){
    			$orderId = $order->getIncrementId();
    			$orderModel = Mage::getModel('sales/order')->loadByIncrementId($orderId);
    			$orderItems = $order->getItemsCollection();
    			if(count($orderItems) > 0){
    				foreach ($orderItems as $item){
    					$productId = $item->product_id;
    					$productSku = $item->sku;
    					$product = Mage::getModel('catalog/product')->load($productId);
    					$type = $product->getTypeId();
    					if($type == 'simple'){
    						$stock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product);
    						$sql = "SELECT `qty` FROM `devicom_physical_inventory` WHERE `sku` = '$productSku'";
    						$results = intval($readConnection->fetchOne($sql));
    						if($results > 0){
    							$stock->setQty($results);
    						}else{
    							$stock->setQty(0);
    						}
    						$stock->save();
    					}
    				}
    			}
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