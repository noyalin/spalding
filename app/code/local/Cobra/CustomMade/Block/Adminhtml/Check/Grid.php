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
        $this->addColumn('id', array(
            'header' => Mage::helper('custommade')->__('ID'),
            'align' => 'right',
            'width' => '50px',
            'index' => 'id',
        ));

        $this->addColumn('position', array(
            'header' => Mage::helper('custommade')->__('Title'),
            'align' => 'left',
            'index' => 'position',
        ));

        /*
        $this->addColumn('content', array(
            'header'    =>  Mage::helper('employee')->__('Item Content'),
            'width'     =>  '150px',
            'index'     =>  'content',
        ));
        */

        $this->addColumn('type', array(
            'header' => Mage::helper('custommade')->__('Status'),
            'align' => 'left',
            'width' => '80px',
            'index' => 'status',
            'type' => 'options',
            'options' => array(
                1 => 'Enabled',
                2 => 'Disabled',
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

        $this->getMassactionBlock()->addItem('delete', array(
            'label' => Mage::helper('custommade')->__('Delete'),
            'url' => $this->getUrl('*/*/massDelete'),
            'confirm' => Mage::helper('custommade')->__('Are you sure?')
        ));

        //$statuses = Mage::getSingleton('employee/status')->getOptionArray();
        $statuses = 1;

        array_unshift($statuses, array('label' => '', 'value' => ''));
        $this->getMassactionBlock()->addItem('status', array(
            'label' => Mage::helper('custommade')->__('Change status'),
            'url' => $this->getUrl('*/*/massStatus', array('_current' => true)),
            'additional' => array(
                'visibility' => array(
                    'name' => 'status',
                    'type' => 'select',
                    'class' => 'required-entry',
                    'label' => Mage::helper('custommade')->__('Status'),
                    'values' => $statuses
                )
            )
        ));
        return $this;
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }
}