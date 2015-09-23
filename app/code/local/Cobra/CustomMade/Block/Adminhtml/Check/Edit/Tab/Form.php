<?php

class Cobra_CustomMade_Block_Adminhtml_Check_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        $username = Mage::getSingleton('admin/session')->getUser()->getUsername();
        $fieldset = $form->addFieldset('custommade_form', array(
                'legend' => Mage::helper('custommade')->__('篮球定制')
            )
        );

        $fieldset->addField('order_id', 'label', array(
                'label' => Mage::helper('custommade')->__('订单号'),
                'name' => 'order_id',
            )
        );

        $fieldset->addField('sku', 'label', array(
                'label' => Mage::helper('custommade')->__('SKU'),
                'name' => 'sku',
            )
        );

        $fieldset->addField('type_p1', 'select', array(
            'label' => Mage::helper('custommade')->__('P1类型'),
            'class' => 'required-entry',
            'name' => 'type_p1',
            'disabled' => true,
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

        $fieldset->addField('msg1_p1', 'label', array(
                'label' => Mage::helper('custommade')->__('P1第一行文字'),
                'name' => 'msg1_p1',
            )
        );

        $fieldset->addField('msg2_p1', 'label', array(
                'label' => Mage::helper('custommade')->__('P1第二行文字'),
                'name' => 'msg2_p1',
            )
        );

        $fieldset->addField('type_p2', 'select', array(
            'label' => Mage::helper('custommade')->__('P2类型'),
            'class' => 'required-entry',
            'name' => 'type_p2',
            'disabled' => true,
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

        $fieldset->addField('msg1_p2', 'label', array(
                'label' => Mage::helper('custommade')->__('P2第一行文字'),
                'name' => 'msg1_p2',
            )
        );

        $fieldset->addField('msg2_p2', 'label', array(
                'label' => Mage::helper('custommade')->__('P2第二行文字'),
                'name' => 'msg2_p2',
            )
        );

        if ($username == 'admin' || $username == 'spalding1') {
            $fieldset->addField('user1_approve', 'select', array(
                    'label' => Mage::helper('custommade')->__('用户1审批'),
                    'name' => 'user1_approve',
                    'values' => array(
                        array(
                            'value' => 0,
                            'label' => Mage::helper('custommade')->__('待审批'),
                        ),
                        array(
                            'value' => 1,
                            'label' => Mage::helper('custommade')->__('审批通过'),
                        ),
                        array(
                            'value' => 2,
                            'label' => Mage::helper('custommade')->__('审批不通过'),
                        ),
                    ),
                )
            );
        } else {
            $fieldset->addField('user1_approve', 'text', array(
                    'name' => 'user1_approve',
                    'style' => 'display:none',
                )
            );
        }

        if ($username == 'admin' || $username == 'spalding2') {
            $fieldset->addField('user2_approve', 'select', array(
                    'label' => Mage::helper('custommade')->__('用户2审批'),
                    'name' => 'user2_approve',
                    'values' => array(
                        array(
                            'value' => 0,
                            'label' => Mage::helper('custommade')->__('待审批'),
                        ),
                        array(
                            'value' => 1,
                            'label' => Mage::helper('custommade')->__('审批通过'),
                        ),
                        array(
                            'value' => 2,
                            'label' => Mage::helper('custommade')->__('审批不通过'),
                        ),
                    ),
                )
            );
        } else {
            $fieldset->addField('user2_approve', 'text', array(
                    'name' => 'user2_approve',
                    'style' => 'display:none',
                )
            );
        }

        if ($username == 'admin' || $username == 'spalding3') {
            $fieldset->addField('user3_approve', 'select', array(
                    'label' => Mage::helper('custommade')->__('用户3审批'),
                    'name' => 'user3_approve',
                    'values' => array(
                        array(
                            'value' => 0,
                            'label' => Mage::helper('custommade')->__('待审批'),
                        ),
                        array(
                            'value' => 1,
                            'label' => Mage::helper('custommade')->__('审批通过'),
                        ),
                        array(
                            'value' => 2,
                            'label' => Mage::helper('custommade')->__('审批不通过'),
                        ),
                    ),
                )
            );
        } else {
            $fieldset->addField('user3_approve', 'text', array(
                    'name' => 'user3_approve',
                    'style' => 'display:none',
                )
            );
        }

        if ($username == 'admin' || $username == 'spalding4') {
            $fieldset->addField('user4_approve', 'select', array(
                    'label' => Mage::helper('custommade')->__('用户4审批'),
                    'name' => 'user4_approve',
                    'values' => array(
                        array(
                            'value' => 0,
                            'label' => Mage::helper('custommade')->__('待审批'),
                        ),
                        array(
                            'value' => 1,
                            'label' => Mage::helper('custommade')->__('审批通过'),
                        ),
                        array(
                            'value' => 2,
                            'label' => Mage::helper('custommade')->__('审批不通过'),
                        ),
                    ),
                )
            );
        } else {
            $fieldset->addField('user4_approve', 'text', array(
                    'name' => 'user4_approve',
                    'style' => 'display:none',
                )
            );
        }

        $fieldset->addField('status', 'select', array(
//            'label' => Mage::helper('custommade')->__('订单状态'),
            'name' => 'status',
            'class' => 'required-entry',
            'required' => true,
            'style' => 'display:none',
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
                    'label' => Mage::helper('custommade')->__('审批不通过'),
                ),
                array(
                    'value' => 4,
                    'label' => Mage::helper('custommade')->__('取消订单'),
                ),
                array(
                    'value' => 5,
                    'label' => Mage::helper('custommade')->__('已导出'),
                ),
            ),
        ));

        if (Mage::getSingleton('adminhtml/session')->getCustommadeData()) {
            $form->setValues(Mage::getSingleton('adminhtml/session')->getCustommadeData());
            Mage::getSingleton('adminhtml/session')->setCustommadeData(null);
        } elseif (Mage::registry('custommade_data')) {
            $form->setValues(Mage::registry('custommade_data')->getData());
        }
        return parent::_prepareForm();
    }
}
