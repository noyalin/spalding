<?php

class Cobra_CustomMade_Model_Mysql4_Session_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    protected function _construct()
    {
        $this->_init('custommade/session');
    }
}
