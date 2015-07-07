<?php

class Cobra_CustomMade_Block_Adminhtml_Check extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_controller = 'adminhtml_check';
        $this->_blockGroup = 'custommade';
        $this->_headerText = Mage::helper('custommade')->__('Employee Manager');
        $this->_addButtonLabel = Mage::helper('custommade')->__('Add Employee');
        parent::__construct();
    }
}