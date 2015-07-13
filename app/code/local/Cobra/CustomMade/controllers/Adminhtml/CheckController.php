<?php

class Cobra_CustomMade_Adminhtml_CheckController extends Mage_Adminhtml_Controller_action
{
    public function indexAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function massApprovedAction()
    {
        $infoIds = $this->getRequest()->getParam('custommade');
        if(!is_array($infoIds)){
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('custommade')->__('Please select order(s)'));
        }
        foreach ($infoIds as $infoId) {
            $subscriber = Mage::getModel('custommade/info')->load($infoId);
            $subscriber->approved();
        }
        $this->_redirect('*/*/index');
    }

    public function massApprovingAction()
    {
        $infoIds = $this->getRequest()->getParam('custommade');
        if(!is_array($infoIds)){
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('custommade')->__('Please select order(s)'));
        }
        foreach ($infoIds as $infoId) {
            $subscriber = Mage::getModel('custommade/info')->load($infoId);
            $subscriber->approving();
        }
        $this->_redirect('*/*/index');
    }

    public function massCancelAction()
    {
        $infoIds = $this->getRequest()->getParam('custommade');
        if(!is_array($infoIds)){
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('custommade')->__('Please select order(s)'));
        }
        foreach ($infoIds as $infoId) {
            $subscriber = Mage::getModel('custommade/info')->load($infoId);
            $subscriber->cancel();
        }
        $this->_redirect('*/*/index');
    }

}