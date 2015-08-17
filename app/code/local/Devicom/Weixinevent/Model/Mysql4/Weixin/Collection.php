<?php

class Devicom_Weixinevent_Model_Mysql4_Weixin_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    protected function _construct()
    {
        $this->_init('weixinevent/weixin');
    }
}
