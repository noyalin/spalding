<?php

class Devicom_Inventory_Model_Mysql4_Physical extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('inventory/physical', 'id');
    }

    public function loadByField($field, $value)
    {
        $table  = $this->getMainTable();
        $where  = $this->_getReadAdapter()->quoteInto("$field = ?", $value);
        $select = $this->_getReadAdapter()
            ->select()
            ->from($table, array('id'))
            ->where($where);
        $item   = $this->_getReadAdapter()->fetchOne($select);
        return $item;
    }

}
