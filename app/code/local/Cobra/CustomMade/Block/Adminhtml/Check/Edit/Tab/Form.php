<?php

class Cobra_CustomMade_Block_Adminhtml_Check_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        $fieldset = $form->addFieldset('custommade_form', array(
                'legend' => Mage::helper('custommade')->__('篮球定制')
            )
        );

        $fieldset->addField('order_id', 'text', array(
                'label' => Mage::helper('custommade')->__('订单号'),
                'class' => 'required-entry',
                'disabled' => true,
                'name' => 'order_id',
            )
        );

        $fieldset->addField('type_p1', 'select', array(
            'label' => Mage::helper('custommade')->__('P1类型'),
            'class' => 'required-entry',
            'name' => 'type_p1',
            'values' => array(
                array(
                    'value' => 0,
                    'label' => Mage::helper('custommade')->__('无'),
                ),
                array(
                    'value' => 1,
                    'label' => Mage::helper('custommade')->__('图片'),
                ),
                array(
                    'value' => 2,
                    'label' => Mage::helper('custommade')->__('文字'),
                ),
            )
        ));

        $fieldset->addField('msg1_p1', 'text', array(
                'label' => Mage::helper('custommade')->__('P1属性1'),
                'class' => 'required-entry',
                'name' => 'msg1_p1',
            )
        );

        $fieldset->addField('msg2_p1', 'text', array(
                'label' => Mage::helper('custommade')->__('P1属性2'),
                'class' => 'required-entry',
                'name' => 'msg2_p1',
                'after_element_html' => '<br /><small>当类型为文字时，1：小，2：中，3：大</small>',
            )
        );

        $fieldset->addField('type_p2', 'select', array(
            'label' => Mage::helper('custommade')->__('P2类型'),
            'class' => 'required-entry',
            'name' => 'type_p2',
            'values' => array(
                array(
                    'value' => 0,
                    'label' => Mage::helper('custommade')->__('无'),
                ),
                array(
                    'value' => 1,
                    'label' => Mage::helper('custommade')->__('图片'),
                ),
                array(
                    'value' => 2,
                    'label' => Mage::helper('custommade')->__('文字'),
                ),
            )
        ));

        $fieldset->addField('msg1_p2', 'text', array(
                'label' => Mage::helper('custommade')->__('P2属性1'),
                'class' => 'required-entry',
                'name' => 'msg1_p2',
            )
        );

        $fieldset->addField('msg2_p2', 'text', array(
                'label' => Mage::helper('custommade')->__('P2属性2'),
                'class' => 'required-entry',
                'name' => 'msg2_p2',
                'after_element_html' => '<br /><small>当类型为文字时，1：小，2：中，3：大</small>',
            )
        );

        $fieldset->addField('status', 'select', array(
            'label' => Mage::helper('custommade')->__('订单状态'),
            'name' => 'status',
            'class' => 'required-entry',
            'required' => true,
            'values' => array(
                array(
                    'value' => 0,
                    'label' => Mage::helper('custommade')->__('待付款'),
                ),
                array(
                    'value' => 1,
                    'label' => Mage::helper('custommade')->__('待审批'),
                ),
                array(
                    'value' => 2,
                    'label' => Mage::helper('custommade')->__('审批通过'),
                ),
                array(
                    'value' => 3,
                    'label' => Mage::helper('custommade')->__('取消订单'),
                ),
            ),
        ));

        if (Mage::getSingleton('adminhtml/session')->getCustommadeData()) {
            $form->setValues(Mage::getSingleton('adminhtml/session')->getCustommadeData());
            Mage::getSingleton('adminhtml/session')->getCustommadeData(null);
        } elseif (Mage::registry('custommade_data')) {
            $form->setValues(Mage::registry('custommade_data')->getData());
        }
        return parent::_prepareForm();
    }
}
