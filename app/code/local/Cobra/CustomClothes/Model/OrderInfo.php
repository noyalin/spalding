<?php

class Cobra_CustomClothes_Model_OrderInfo extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('customclothes/orderInfo');
    }
    
    public function getDataByOrderId($orderId)
    {
    	$idArray = $this->getResource()
    	->loadByFieldArray('order_id', $orderId);
    	if($idArray){
    		$return = array();
    		foreach ($idArray as $id){
    			$data = $this->load($id['id'])->toArray();
    			$return[] = $data;
    		}
    		return $return;
    	}else{
    		return array();
    	}
    
    }
}
