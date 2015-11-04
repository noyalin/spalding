<?php

class Cobra_CustomMade_Model_Mysql4_Info extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('custommade/info', 'id');
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

    public function loadByConditions($time, $status)
    {
        $table = $this->getMainTable();
        $where1 = $this->_getReadAdapter()->quoteInto("create_time < ?", $time);
        $where2 = $this->_getReadAdapter()->quoteInto("status = ?", $status);
        $sql = $this->_getReadAdapter()
            ->select()
            ->from($table, array('order_id'))
            ->where($where1)
            ->where($where2);
        $items = $this->_getReadAdapter()->fetchAll($sql);
        return $items;
    }

    public function loadByTime($time1, $time2, $status)
    {
        $table = $this->getMainTable();
        $where1 = $this->_getReadAdapter()->quoteInto("create_time < ?", $time1);
        $where2 = $this->_getReadAdapter()->quoteInto("create_time >= ?", $time2);
        $where3 = $this->_getReadAdapter()->quoteInto("status = ?", $status);
        $sql = $this->_getReadAdapter()
            ->select()
            ->from($table, array('order_id'))
            ->where($where1)
            ->where($where2)
            ->where($where3);
        $items = $this->_getReadAdapter()->fetchAll($sql);
        return $items;
    }
}
