<?php

class Cobra_CustomMade_Block_Adminhtml_Check_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();

        $this->_objectId = 'id';
        $this->_blockGroup = 'custommade';
        $this->_controller = 'adminhtml_check';

        $this->_updateButton('save', 'label', Mage::helper('custommade')->__('Save'));
        $this->removeButton('delete');

        $this->_addButton('saveandcontinue', array(
            'label' => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick' => 'saveAndContinueEdit()',
            'class' => 'save',
        ),
            -100
        );

        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('web_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'custommade_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'custommade_content');
                }
            }

            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    public function getHeaderText()
    {
        return Mage::helper('custommade')->__('篮球定制');
    }
}
