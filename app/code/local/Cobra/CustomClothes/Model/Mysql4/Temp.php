<?php

class Cobra_CustomClothes_Model_Mysql4_Temp extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('customclothes/temp', 'id');
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
    
    public function loadByFieldArray($field, $value)
    {
    	$table = $this->getMainTable();
    	$where = $this->_getReadAdapter()->quoteInto("$field = ?", $value);
    	$sql = $this->_getReadAdapter()
    	->select()
    	->from($table, array('id'))
    	->where($where);
    	$items = $this->_getReadAdapter()->fetchAll($sql);
        return $items;
    }
}
