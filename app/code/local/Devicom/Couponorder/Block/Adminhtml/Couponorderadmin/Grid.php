<?php

class Devicom_Couponorder_Block_Adminhtml_Couponorderadmin_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('couponorderGrid');
        $this->setDefaultSort('create_time');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('couponorder/couponorder')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {

        $this->addColumn('id', array(
            'header' => Mage::helper('couponorder')->__('ID'),
            'align' => 'right',
            'width' => '80px',
            'index' => 'id',
            'html_decorators' => array('nobr')
        ));

        $this->addColumn('order_id', array(
            'header' => Mage::helper('couponorder')->__('订单编号'),
            'align' => 'right',
            'width' => '80px',
            'index' => 'order_id',
            'html_decorators' => array('nobr')
        ));

        $this->addColumn('coupon_rule_id', array(
        		'header' => Mage::helper('couponorder')->__('规则ID'),
        		'align' => 'right',
        		'width' => '80px',
        		'index' => 'coupon_rule_id',
        		'html_decorators' => array('nobr')
        ));
        
        $this->addColumn('coupon_rule_name', array(
        		'header' => Mage::helper('couponorder')->__('折扣名称'),
        		'align' => 'right',
        		'width' => '80px',
        		'index' => 'coupon_rule_name',
        		'html_decorators' => array('nobr')
        ));
        
        $this->addColumn('coupon_code', array(
            'header' => Mage::helper('couponorder')->__('折扣信息'),
            'align' => 'right',
            'width' => '80px',
            'index' => 'coupon_code',
            'html_decorators' => array('nobr')
        ));

        $this->addColumn('create_time', array(
            'header' => Mage::helper('couponorder')->__('创建时间'),
            'width' => '150px',
            'index' => 'create_time',
            'type' => 'varchar',
            'align' => 'center',
            'default' => $this->__('N/A'),
            'html_decorators' => array('nobr')
        ));
    }
}