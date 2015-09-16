<?php

class Cobra_CustomMade_Model_Mysql4_Session extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {
        $this->_init('custommade/session', 'session_id');
    }

}