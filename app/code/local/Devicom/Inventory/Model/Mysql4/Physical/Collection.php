<?php

class Devicom_Inventory_Model_Mysql4_Physical_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    protected function _construct()
    {
        $this->_init('inventory/physical');
    }
}
