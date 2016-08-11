<?php

class Cobra_CustomClothes_Model_TempInfo extends Mage_Core_Model_Abstract
{

    protected function _construct()
    {
        $this->_init('customclothes/tempInfo');
    }

    public function deleteByCustomerId($customerId)
    {
        $ids = $this->getResource()
            ->loadByFieldArray('customer_id', $customerId);
        foreach ($ids as $id) {
            $this->load($id)->delete();
        }
    }
	
    public function getDataByCustomerId($customerId)
    {
    	$idArray = $this->getResource()
    	->loadByFieldArray('customer_id', $customerId);
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
