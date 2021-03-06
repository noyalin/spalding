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
        Mage::log("CustomMade Send Mail loadByConditions time=" . $time);
        return $items;
    }

    public function loadByConditions2($time,$status,$user1_approve,$user2_approve)
    {
        $table = $this->getMainTable();
        $where1 = $this->_getReadAdapter()->quoteInto("create_time < ?", $time);
        $where2 = $this->_getReadAdapter()->quoteInto("status = ?",$status);
        $where3 = $this->_getReadAdapter()->quoteInto("user1_approve = ?", $user1_approve);
        $where4 = $this->_getReadAdapter()->quoteInto("user2_approve = ?", $user2_approve);
        $sql = $this->_getReadAdapter()
            ->select()
            ->from($table, array('order_id'))
            ->where($where1)
            ->where($where2)
            ->where($where3)
            ->where($where4);
        $items = $this->_getReadAdapter()->fetchAll($sql);
        Mage::log("CustomMade Send Mail loadByConditions time=" . $time);
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
        Mage::log("CustomMade Send Mail loadByTime time1=" . $time1 . ", time2=" . $time2);
        return $items;
    }
        public function OrderAutoApprove($satus1, $satus2){
        $table = $this->getMainTable();
        $where1 = $this->_getReadAdapter()->quoteInto("status= ?", $satus1);
        $where2 = $this->_getReadAdapter()->quoteInto("user1_approve=?", $satus2);
        $sql = $this->_getReadAdapter()
            ->select()
            ->from($table,array('id','order_id','sku','msg6_p1','msg6_p2'))
            ->where($where1)
            ->where($where2);
        $items = $this->_getReadAdapter()->fetchAll($sql);
        Mage::log($items);
        return $items;

    }
}
