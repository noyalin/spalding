<?php

class Devicom_Couponorder_Block_Adminhtml_Couponorderadmin extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_controller = 'adminhtml_couponorderadmin';
        $this->_blockGroup = 'couponorder';
        $this->_headerText = Mage::helper('couponorder')->__('折扣订单');
        parent::__construct();
        $this->removeButton('add');
    }
}