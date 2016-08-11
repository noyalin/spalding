<?php

class Cobra_CustomClothes_Model_Temp extends Mage_Core_Model_Abstract
{

    protected function _construct()
    {
        $this->_init('customclothes/temp');
    }

    public function deleteByCustomerId($customerId)
    {
        $ids = $this->getResource()
            ->loadByFieldArray('customer_id', $customerId);
        foreach ($ids as $id) {
            $this->load($id)->delete();
        }
    }

    public function loadByCustomerId($customerId)
    {
        $id = $this->getResource()
            ->loadByField('customer_id', $customerId);
    	if($id){
        	return $this->load($id);
        }else{
        	return array();
        }
        
    }
    
    public function getDataByCustomerId($customerId)
    {
    	$id = $this->getResource()
    	->loadByField('customer_id', $customerId);

    	if($id){
    		$return = array();
    		$data = $this->load($id)->toArray();
    		$return = $data;
    		return $return;
    	}else{
    		return array();
    	}
    
    }
}
