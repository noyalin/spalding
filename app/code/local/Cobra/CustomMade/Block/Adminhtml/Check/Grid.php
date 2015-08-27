<?php

class Cobra_CustomMade_Block_Adminhtml_Check_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('custommadeGrid');
        $this->setDefaultSort('create_time');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('custommade/info')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {

        $this->addColumn('order_id', array(
            'header'            => Mage::helper('custommade')->__('订单编号'),
            'align'             => 'right',
            'width'             => '80px',
            'index'             => 'order_id',
            'html_decorators'   => array('nobr')
        ));

        $this->addColumn('create_time', array(
            'header'            => Mage::helper('custommade')->__('订单时间'),
            'width'             => '150px',
            'index'             => 'create_time',
            'type'              => 'datetime',
            'align'             => 'center',
            'default'           => $this->__('N/A'),
            'html_decorators'   => array('nobr')
        ));

        $this->addColumn('type_p1', array(
            'header'            => Mage::helper('custommade')->__('P1类型'),
            'align'             => 'center',
            'width'             => '80px',
            'index'             => 'type_p1',
            'sortable'          => false,
            'type'              => 'options',
            'options'           => array(
                1   => '图片',
                2   => '文字',
            ),
            'html_decorators'   => array('nobr')
        ));

        $this->addColumn('msg5_p1', array(
            'header'            => Mage::helper('custommade')->__('预览图'),
            'align'             => 'center',
            'index'             => 'msg5_p1',
            'renderer'          => 'custommade/adminhtml_check_renderer_content',
            'filter'            => false,
            'sortable'          => false,
            'position'          => 'P1',
            'html_decorators'   => array('nobr')
        ));

        $this->addColumn('msg6_p1', array(
            'header'            => Mage::helper('custommade')->__('打印图'),
            'align'             => 'center',
            'index'             => 'msg6_p1',
            'renderer'          => 'custommade/adminhtml_check_renderer_content',
            'filter'            => false,
            'sortable'          => false,
            'position'           => 'P1',
            'html_decorators'   => array('nobr')
        ));

        $this->addColumn('type_p2', array(
            'header'            => Mage::helper('custommade')->__('P2类型'),
            'align'             => 'center',
            'width'             => '80px',
            'index'             => 'type_p2',
            'sortable'          => false,
            'type'              => 'options',
            'options'           => array(
                1   => '图片',
                2   => '文字',
            ),
            'html_decorators'   => array('nobr')
        ));

        $this->addColumn('msg5_p2', array(
            'header'            => Mage::helper('custommade')->__('预览图'),
            'align'             => 'center',
            'index'             => 'msg5_p2',
            'renderer'          => 'custommade/adminhtml_check_renderer_content',
            'filter'            => false,
            'sortable'          => false,
            'position'          => 'P2',
            'html_decorators'   => array('nobr')
        ));

        $this->addColumn('msg6_p2', array(
            'header'            => Mage::helper('custommade')->__('打印图'),
            'align'             => 'center',
            'index'             => 'msg6_p2',
            'renderer'          => 'custommade/adminhtml_check_renderer_content',
            'filter'            => false,
            'sortable'          => false,
            'position'          => 'P2',
            'html_decorators'   => array('nobr')
        ));


        $this->addColumn('status', array(
            'header'            => Mage::helper('custommade')->__('订单状态'),
            'align'             => 'center',
            'width'             => '80px',
            'index'             => 'status',
            'type'              => 'options',
            'options' => array(
                0   => '待付款',
                1   => '待审批',
                2   => '审批通过',
                3   => '审批不通过',
                4   => '取消订单',
            ),
            'html_decorators'   => array('nobr')
        ));

        $this->addColumn('action',
            array(
                'header'    =>  Mage::helper('custommade')->__('Action'),
                'width'     => '100',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
                    array(
                        'caption'   => Mage::helper('custommade')->__('Edit'),
                        'url'       => array('base'=> '*/*/edit'),
                        'field'     => 'id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true,
            ));

        $this->addExportType('*/*/exportCsv', Mage::helper('custommade')->__('CSV'));
        $this->addExportType('*/*/exportXml', Mage::helper('custommade')->__('XML'));

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setFormFieldName('custommade');

        $this->getMassactionBlock()->addItem('nonpayment', array(
            'label'     => Mage::helper('custommade')->__('待付款'),
            'url'       => $this->getUrl('*/*/massNonPayment')
        ));

        $this->getMassactionBlock()->addItem('approving', array(
            'label'     => Mage::helper('custommade')->__('待审批'),
            'url'       => $this->getUrl('*/*/massApproving')
        ));

        $this->getMassactionBlock()->addItem('approved', array(
            'label'     => Mage::helper('custommade')->__('审批通过'),
            'url'       => $this->getUrl('*/*/massApproved')
        ));

        $this->getMassactionBlock()->addItem('cancel', array(
            'label'     => Mage::helper('custommade')->__('取消订单'),
            'url'       => $this->getUrl('*/*/massCancel')
        ));

        return $this;
    }

}