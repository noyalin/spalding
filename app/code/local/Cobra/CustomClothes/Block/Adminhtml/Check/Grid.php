<?php

class Cobra_CustomClothes_Block_Adminhtml_Check_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('customclothesGrid');
        $this->setDefaultSort('create_time');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('customclothes/order')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {

        $this->addColumn('order_id', array(
            'header'            => Mage::helper('customclothes')->__('订单编号'),
            'align'             => 'right',
            'width'             => '120px',
            'index'             => 'order_id',
            'html_decorators'   => array('nobr')
        ));

        $this->addColumn('sku', array(
            'header'            => Mage::helper('customclothes')->__('SKU'),
            'align'             => 'right',
            'width'             => '120px',
            'index'             => 'sku',
            'html_decorators'   => array('nobr')
        ));

        $this->addColumn('create_time', array(
            'header'            => Mage::helper('customclothes')->__('订单时间'),
            'width'             => '150px',
            'index'             => 'create_time',
            'type'              => 'datetime',
            'align'             => 'center',
            'default'           => $this->__('N/A'),
            'html_decorators'   => array('nobr')
        ));

        $this->addColumn('color', array(
        		'header'            => Mage::helper('customclothes')->__('颜色'),
        		'align'             => 'right',
        		'width'             => '120px',
        		'index'             => 'color',
        ));
        
        $this->addColumn('font', array(
           'header'            => Mage::helper('customclothes')->__('字体'),
           'align'             => 'center',
           'width'             => '120px',
           'index'             => 'font',
           'sortable'          => false,
           'type'              => 'options',
           'options'           => array(
               1   => 'font1(汉仪楷体)',
               2   => 'font2(汉仪粗圆)',
               3   => 'font3(宋体)',
           ),
           'html_decorators'   => array('nobr')
        ));

        $this->addColumn('font_color', array(
        		'header'            => Mage::helper('customclothes')->__('字体颜色'),
        		'align'             => 'right',
        		'width'             => '120px',
        		'index'             => 'font_color',
        ));
        
        $this->addColumn('font_style', array(
       		'header'            => Mage::helper('customclothes')->__('样式'),
       		'align'             => 'center',
       		'width'             => '120px',
       		'index'             => 'font_style',
       		'sortable'          => false,
       		'type'              => 'options',
       		'options'           => array(
                1   => '直线',
                2   => '曲线',
       		),
       		'html_decorators'   => array('nobr')
        ));
               
        $this->addColumn('result_image', array(
            'header'            => Mage::helper('customclothes')->__('效果图'),
            'align'             => 'center',
            'index'             => 'result_image',
        		'renderer'          => 'customclothes/adminhtml_check_renderer_content',
           'width'             => '80px',
           'sortable'          => false,
           'type'              => 'options',
           
           'html_decorators'   => array('nobr')
       ));
        
        $this->addColumn('order_count', array(
        	'header'            => Mage::helper('customclothes')->__('定制数量'),
        	'align'             => 'right',
        	'width'             => '120px',
        	'index'             => 'order_count',
        	'html_decorators'   => array('nobr')
        ));
	    	
       $this->addColumn('status', array(
       		'header'            => Mage::helper('customclothes')->__('订单状态'),
       		'align'             => 'center',
       		'width'             => '120px',
       		'index'             => 'status',
       		'type'              => 'options',
       		'options' => array(
       				1   => '待付款',
       				2   => '已付款',
       				3   => '已导出',
       				4   => '取消订单',
       				5   => '订单错误'
       		),
       		'html_decorators'   => array('nobr')
        ));

      
       $this->addColumn('update_time', array(
       		'header'            => Mage::helper('customclothes')->__('更新时间'),
       		'width'             => '150px',
       		'index'             => 'update_time',
       		'type'              => 'datetime',
       		'align'             => 'center',
       		'default'           => $this->__('N/A'),
       		'html_decorators'   => array('nobr')
       ));

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setFormFieldName('customclothes');

        $this->getMassactionBlock()->addItem('cancel', array(
            'label'     => Mage::helper('customclothes')->__('取消订单'),
            'url'       => $this->getUrl('*/*/massCancel')
        ));

        $this->getMassactionBlock()->addItem('export', array(
            'label'     => Mage::helper('customclothes')->__('导出'),
            'url'       => $this->getUrl('*/*/massExport')
        ));

        return $this;
    }

}