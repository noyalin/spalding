<?php

class Devicom_Weixinevent_Block_Adminhtml_Weixinadmin extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_controller = 'adminhtml_weixinadmin';
        $this->_blockGroup = 'weixinevent';
        $this->_headerText = Mage::helper('weixinevent')->__('微信活动');
        parent::__construct();
        $this->removeButton('add');
    }
}