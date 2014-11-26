<?php

class Infinitech_Weixinpay_Model_Log extends Mage_Core_Model_Abstract
{
    /**
     * Model initialization
     *
     */
    protected function _construct()
    {
        $this->_init('weixinpay/log');
    }
    public function getYes()
    {
        return 'yes';
    }
}