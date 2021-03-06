<?php

class Cobra_CustomMade_Block_Adminhtml_Check_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('custommade_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('custommade')->__('篮球定制'));
    }

    protected function _beforeToHtml()
    {
        $this->addTab('form_section111', array(
                'label' => Mage::helper('custommade')->__('篮球定制'),
                'title' => Mage::helper('custommade')->__('篮球定制'),
                'content' => $this->getLayout()
                    ->createBlock('custommade/adminhtml_check_edit_tab_form')
                    ->toHtml(),)
        );

        return parent::_beforeToHtml();
    }
}
