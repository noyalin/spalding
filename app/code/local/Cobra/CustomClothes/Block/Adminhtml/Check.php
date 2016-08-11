<?php

class Cobra_CustomClothes_Block_Adminhtml_Check extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_controller = 'adminhtml_check';
        $this->_blockGroup = 'customclothes';
        $this->_headerText = Mage::helper('customclothes')->__('定制球衣管理');
//        $this->_addButtonLabel = Mage::helper('customclothes')->__('Add Employee');
        parent::__construct();
        $this->removeButton('add');
    }

}