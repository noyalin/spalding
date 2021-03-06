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

        $this->addColumn('sku', array(
            'header'            => Mage::helper('custommade')->__('SKU'),
            'align'             => 'right',
            'width'             => '80px',
            'index'             => 'sku',
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
           	   4   => '定制图片'
           ),
           'html_decorators'   => array('nobr')
       ));

        $this->addColumn('msg3_p1', array(
       		'header'            => Mage::helper('custommade')->__('P1字号'),
       		'align'             => 'center',
       		'width'             => '80px',
       		'index'             => 'msg3_p1',
       		'sortable'          => false,
       		'type'              => 'options',
       		'options'           => array(
       			3   => '大号',
       			2   => '中号',
       			1   => '小号单行',
       			4   => '小号双行',
       		),
       		'html_decorators'   => array('nobr')
        ));

        $this->addColumn('msg4_p1', array(
        	'header'            => Mage::helper('custommade')->__('P1字体'),
        	'align'             => 'center',
        	'width'             => '80px',
        	'index'             => 'msg4_p1',
        	'sortable'          => false,
        	'type'              => 'options',
        	'options'           => array(
        		0   => 'NBA字体',
        	    2	=> 'Aril',
                3	=> '宋体',
                4	=> '楷体',
        	),
        	'html_decorators'   => array('nobr')
        ));

        $this->addColumn('msg1_p1', array(
            'header'            => Mage::helper('custommade')->__('P1文字1'),
            'align'             => 'center',
            'index'             => 'msg5_p1',
            'renderer'          => 'custommade/adminhtml_check_renderer_content',
            'filter'            => false,
            'sortable'          => false,
            'position'          => 1,
            'content'           => 1,
            'html_decorators'   => array('nobr')
        ));

        $this->addColumn('msg2_p1', array(
            'header'            => Mage::helper('custommade')->__('P1文字2'),
            'align'             => 'center',
            'index'             => 'msg6_p1',
            'renderer'          => 'custommade/adminhtml_check_renderer_content',
            'filter'            => false,
            'sortable'          => false,
            'position'          => 1,
            'content'           => 2,
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
           	   4   => '定制图片'
           ),
           'html_decorators'   => array('nobr')
       ));

        $this->addColumn('msg3_p2', array(
        		'header'            => Mage::helper('custommade')->__('P2字号'),
        		'align'             => 'center',
        		'width'             => '80px',
        		'index'             => 'msg3_p2',
        		'sortable'          => false,
        		'type'              => 'options',
        		'options'           => array(
        			3   => '大号',
       				2   => '中号',
       				1   => '小号单行',
       				4   => '小号双行',
        		),
        		'html_decorators'   => array('nobr')
        ));

        $this->addColumn('msg4_p2', array(
        		'header'            => Mage::helper('custommade')->__('P2字体'),
        		'align'             => 'center',
        		'width'             => '80px',
        		'index'             => 'msg4_p2',
        		'sortable'          => false,
        		'type'              => 'options',
        		'options'           => array(
        			0   => 'NBA字体',
        	    	2	=> 'Aril',
                    3	=> '宋体',
                    4	=> '楷体',
        		),
        		'html_decorators'   => array('nobr')
        ));

        $this->addColumn('msg1_p2', array(
            'header'            => Mage::helper('custommade')->__('P2文字1'),
            'align'             => 'center',
            'index'             => 'msg5_p2',
            'renderer'          => 'custommade/adminhtml_check_renderer_content',
            'filter'            => false,
            'sortable'          => false,
            'position'          => 2,
            'content'           => 1,
            'html_decorators'   => array('nobr')
        ));

        $this->addColumn('msg2_p2', array(
            'header'            => Mage::helper('custommade')->__('P2文字2'),
            'align'             => 'center',
            'index'             => 'msg6_p2',
            'renderer'          => 'custommade/adminhtml_check_renderer_content',
            'filter'            => false,
            'sortable'          => false,
            'position'          => 2,
            'content'           => 2,
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
                5   => '已导出',
            ),
            'html_decorators'   => array('nobr')
        ));

        $this->addColumn('user1_approve', array(
            'header'            => Mage::helper('custommade')->__('SPALDING审批用户'),
            'align'             => 'center',
            'width'             => '80px',
            'index'             => 'user1_approve',
            'type'              => 'options',
            'options' => array(
                0   => '待审批',
                1   => '审批通过',
                2   => '审批不通过',
            ),
            'html_decorators'   => array('nobr')
        ));

        $this->addColumn('user1_reason', array(
            'header'            => Mage::helper('custommade')->__('SPALDING审批用户理由'),
            'align'             => 'left',
            'width'             => '80px',
            'index'             => 'user1_reason',
            'html_decorators'   => array('nobr')
        ));

        $this->addColumn('user2_approve', array(
            'header'            => Mage::helper('custommade')->__('VoyageOne审批用户'),
            'align'             => 'center',
            'width'             => '80px',
            'index'             => 'user2_approve',
            'type'              => 'options',
            'options' => array(
                0   => '待审批',
                1   => '审批通过',
                2   => '审批不通过',
            ),
            'html_decorators'   => array('nobr')
        ));

        $this->addColumn('user2_reason', array(
            'header'            => Mage::helper('custommade')->__('VoyageOne审批用户理由'),
            'align'             => 'left',
            'width'             => '80px',
            'index'             => 'user2_reason',
            'html_decorators'   => array('nobr')
        ));

/*
        $this->addColumn('user3_approve', array(
            'header'            => Mage::helper('custommade')->__('用户3'),
            'align'             => 'center',
            'width'             => '80px',
            'index'             => 'user3_approve',
            'type'              => 'options',
            'options' => array(
                0   => '待审批',
                1   => '审批通过',
                2   => '审批不通过',
            ),
            'html_decorators'   => array('nobr')
        ));

        $this->addColumn('user3_reason', array(
            'header'            => Mage::helper('custommade')->__('用户3理由'),
            'align'             => 'right',
            'width'             => '80px',
            'index'             => 'user3_reason',
            'html_decorators'   => array('nobr')
        ));

        $this->addColumn('user4_approve', array(
            'header'            => Mage::helper('custommade')->__('用户4'),
            'align'             => 'center',
            'width'             => '80px',
            'index'             => 'user4_approve',
            'type'              => 'options',
            'options' => array(
                0   => '待审批',
                1   => '审批通过',
                2   => '审批不通过',
            ),
            'html_decorators'   => array('nobr')
        ));

        $this->addColumn('user4_reason', array(
            'header'            => Mage::helper('custommade')->__('用户4理由'),
            'align'             => 'right',
            'width'             => '80px',
            'index'             => 'user4_reason',
            'html_decorators'   => array('nobr')
        ));
*/
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

//        $this->addExportType('*/*/exportCsv', Mage::helper('custommade')->__('CSV'));
//        $this->addExportType('*/*/exportXml', Mage::helper('custommade')->__('XML'));

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setFormFieldName('custommade');

//        $this->getMassactionBlock()->addItem('nonpayment', array(
//            'label'     => Mage::helper('custommade')->__('待付款'),
//            'url'       => $this->getUrl('*/*/massNonPayment')
//        ));
//
//        $this->getMassactionBlock()->addItem('approving', array(
//            'label'     => Mage::helper('custommade')->__('待审批'),
//            'url'       => $this->getUrl('*/*/massApproving')
//        ));
//
//        $this->getMassactionBlock()->addItem('approved', array(
//            'label'     => Mage::helper('custommade')->__('审批通过'),
//            'url'       => $this->getUrl('*/*/massApproved')
//        ));
//
//        $this->getMassactionBlock()->addItem('notapproved', array(
//            'label'     => Mage::helper('custommade')->__('审批不通过'),
//            'url'       => $this->getUrl('*/*/massNotApproved')
//        ));

        $this->getMassactionBlock()->addItem('cancel', array(
            'label'     => Mage::helper('custommade')->__('取消订单'),
            'url'       => $this->getUrl('*/*/massCancel')
        ));

        $this->getMassactionBlock()->addItem('export', array(
            'label'     => Mage::helper('custommade')->__('导出'),
            'url'       => $this->getUrl('*/*/massExport')
        ));

        return $this;
    }

}
