<?php

class Devicom_Weixinevent_Model_Mysql4_Weixin extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('weixinevent/weixin', 'id');
    }
}