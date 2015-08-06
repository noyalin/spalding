<?php

class Cobra_CustomMade_Block_Adminhtml_Check_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        $fieldset = $form->addFieldset('custommade_form', array(
                'legend' => Mage::helper('custommade')->__('Employee information')
            )
        );

        $fieldset->addField('order_id', 'text', array(
                'label' => Mage::helper('custommade')->__('order_id'),
                'class' => 'required-entry',
                'required' => true,
                'name' => 'order_id',
            )
        );

        $fieldset->addField('status', 'select', array(
            'label' => Mage::helper('custommade')->__('status'),
            'name' => 'status',
            'class' => 'required-entry',
            'required' => true,
            'values' => array(
                array(
                    'value' => 1,
                    'label' => Mage::helper('custommade')->__('Yes'),
                ),
                array(
                    'value' => 0,
                    'label' => Mage::helper('custommade')->__('No'),
                ),
            ),
        ));

        if ( Mage::getSingleton('adminhtml/session')->getCustommadeData() )
        {
            $form->setValues(Mage::getSingleton('adminhtml/session')->getCustommadeData());
            Mage::getSingleton('adminhtml/session')->getCustommadeData(null);
        } elseif ( Mage::registry('custommade_data') ) {
            $form->setValues(Mage::registry('custommade_data')->getData());
        }
        return parent::_prepareForm();
    }
}
