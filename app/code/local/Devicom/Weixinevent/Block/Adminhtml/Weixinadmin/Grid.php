<?php

class Devicom_Weixinevent_Block_Adminhtml_Weixinadmin_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('weixineventGrid');
        $this->setDefaultSort('create_time');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('weixinevent/weixin')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {

        $this->addColumn('id', array(
            'header' => Mage::helper('weixinevent')->__('ID'),
            'align' => 'right',
            'width' => '80px',
            'index' => 'id',
            'html_decorators' => array('nobr')
        ));

        $this->addColumn('order_id', array(
            'header' => Mage::helper('weixinevent')->__('订单编号'),
            'align' => 'right',
            'width' => '80px',
            'index' => 'order_id',
            'html_decorators' => array('nobr')
        ));

        $this->addColumn('telephone_no', array(
            'header' => Mage::helper('weixinevent')->__('手机号码'),
            'align' => 'right',
            'width' => '80px',
            'index' => 'telephone_no',
            'html_decorators' => array('nobr')
        ));

        $this->addColumn('create_time', array(
            'header' => Mage::helper('weixinevent')->__('参与时间'),
            'width' => '150px',
            'index' => 'create_time',
            'type' => 'datetime',
            'align' => 'center',
            'default' => $this->__('N/A'),
            'html_decorators' => array('nobr')
        ));

        $this->addColumn('sponsor_flag', array(
            'header' => Mage::helper('weixinevent')->__('状态'),
            'width' => '150px',
            'index' => 'sponsor_flag',
            'align' => 'center',
            'type' => 'options',
            'options' => array(
                0 => '未分享',
                1 => '已分享',
                5 => '参与者',
            ),
            'html_decorators' => array('nobr')
        ));
    }
}