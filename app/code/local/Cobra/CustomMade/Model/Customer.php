<?php
class Cobra_CustomMade_Model_Customer extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        $this->_init('custommade/customer');
    }

    public function createCustomer()
    {
        return $this->setCustomerId(null)->save()->getCustomerId();
    }
}