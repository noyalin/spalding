<?php
class Cobra_CustomMade_Model_Customer extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        $this->_init('custommade/customer');
    }

    public function createCustomer()
    {
        $id = $this->setCustomerId(null)->save()->getCustomerId();
        Mage::log('createCustomer id=' . $id);
        return $id;
    }
}