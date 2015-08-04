<?php

class Cobra_CustomMade_Block_Adminhtml_Check_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('custommadeGrid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('ASC');
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
            'header' => Mage::helper('custommade')->__('order_id'),
            'align' => 'right',
            'width' => '50px',
            'index' => 'order_id',
        ));

        $this->addColumn('type_p1', array(
            'header' => Mage::helper('custommade')->__('type_p1'),
            'align' => 'left',
            'index' => 'type_p1',
            'sortable' => false,
            'type' => 'options',
            'options' => array(
                1 => '图片',
                2 => '文字',
            ),
        ));

        $this->addColumn('msg1_p1', array(
                'header' => Mage::helper('custommade')->__('msg1_p1'),
                'align' => 'left',
                'index' => 'msg1_p1',
                'renderer' => 'custommade/adminhtml_check_renderer_content',
                'filter' => false,
                'sortable' => false,
                'position' => 'P1',
            )
        );

        $this->addColumn('msg2_p1', array(
                'header' => Mage::helper('custommade')->__('msg2_p1'),
                'align' => 'left',
                'index' => 'msg2_p1',
                'renderer' => 'custommade/adminhtml_check_renderer_content',
                'filter' => false,
                'sortable' => false,
                'position' => 'P1',
                'type' => 'options',
                'options' => array(
                    1 => '小号字体',
                    2 => '中号字体',
                    3 => '大号字体',
                ),
            )
        );

        $this->addColumn('type_p2', array(
            'header' => Mage::helper('custommade')->__('type_p2'),
            'align' => 'left',
            'index' => 'type_p2',
            'sortable' => false,
            'type' => 'options',
            'options' => array(
                1 => '图片',
                2 => '文字',
            ),
        ));

        $this->addColumn('msg1_p2', array(
            'header' => Mage::helper('custommade')->__('msg1_p2'),
            'align' => 'left',
            'index' => 'msg1_p2',
            'renderer' => 'custommade/adminhtml_check_renderer_content',
            'filter' => false,
            'sortable' => false,
            'position' => 'P2',
        ));

        $this->addColumn('msg2_p2', array(
            'header' => Mage::helper('custommade')->__('msg2_p2'),
            'align' => 'left',
            'index' => 'msg2_p2',
            'renderer' => 'custommade/adminhtml_check_renderer_content',
            'filter' => false,
            'sortable' => false,
            'position' => 'P2',
            'type' => 'options',
            'options' => array(
                1 => '小号字体',
                2 => '中号字体',
                3 => '大号字体',
            ),
        ));

        $this->addColumn('type', array(
            'header' => Mage::helper('custommade')->__('Status'),
            'align' => 'left',
            'width' => '80px',
            'index' => 'status',
            'type' => 'options',
            'options' => array(
                1 => 'Approved',
                2 => 'Approving',
                3 => 'Cancel',
            ),
        ));

        $this->addColumn('action', array(
            'header' => Mage::helper('custommade')->__('Action'),
            'width' => '100',
            'type' => 'action',
            'getter' => 'getId',
            'actions' => array(
                array(
                    'caption' => Mage::helper('custommade')->__('Edit'),
                    'url' => array('base' => '*/*/edit'),
                    'field' => 'id'
                )
            ),
            'filter' => false,
            'sortable' => false,
            'index' => 'stores',
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

        $this->getMassactionBlock()->addItem('approved', array(
            'label' => Mage::helper('custommade')->__('Approved'),
            'url' => $this->getUrl('*/*/massApproved')
        ));

        $this->getMassactionBlock()->addItem('approving', array(
            'label' => Mage::helper('custommade')->__('Approving'),
            'url' => $this->getUrl('*/*/massApproving')
        ));

        $this->getMassactionBlock()->addItem('cancel', array(
            'label' => Mage::helper('custommade')->__('Cancel'),
            'url' => $this->getUrl('*/*/massCancel')
        ));

        return $this;
    }

}