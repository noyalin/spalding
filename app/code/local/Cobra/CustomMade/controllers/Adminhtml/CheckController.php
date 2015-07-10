<?php

class Cobra_CustomMade_Adminhtml_CheckController extends Mage_Adminhtml_Controller_action
{
    public function indexAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function massDeleteAction()
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

//    public function massDeleteAction()
//    {
//        $subscribersIds = $this->getRequest()->getParam('subscriber');
//        if (!is_array($subscribersIds)) {
//            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('newsletter')->__('Please select subscriber(s)'));
//        }
//        else {
//            try {
//                foreach ($subscribersIds as $subscriberId) {
//                    $subscriber = Mage::getModel('newsletter/subscriber')->load($subscriberId);
//                    $subscriber->delete();
//                }
//                Mage::getSingleton('adminhtml/session')->addSuccess(
//                    Mage::helper('adminhtml')->__('Total of %d record(s) were deleted', count($subscribersIds))
//                );
//            } catch (Exception $e) {
//                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
//            }
//        }
//
//        $this->_redirect('*/*/index');
//    }
}