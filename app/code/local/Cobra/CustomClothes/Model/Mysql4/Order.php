<?php

class Cobra_CustomClothes_Model_Mysql4_Order extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('customclothes/order', 'id');
    }

    public function loadByField($field, $value)
    {
        $table = $this->getMainTable();
        $where = $this->_getReadAdapter()->quoteInto("$field = ?", $value);
        $sql = $this->_getReadAdapter()
            ->select()
            ->from($table, array('id'))
            ->where($where);
        $item = $this->_getReadAdapter()->fetchOne($sql);
        return $item;
    }

 
}
