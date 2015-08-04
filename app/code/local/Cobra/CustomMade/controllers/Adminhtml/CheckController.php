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
        if (!is_array($infoIds)) {
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
        if (!is_array($infoIds)) {
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
        if (!is_array($infoIds)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('custommade')->__('Please select order(s)'));
        }
        foreach ($infoIds as $infoId) {
            $subscriber = Mage::getModel('custommade/info')->load($infoId);
            $subscriber->cancel();
        }
        $this->_redirect('*/*/index');
    }

    public function newAction()
    {
        $this->loadLayout();
        $this->_addContent(
            $this->getLayout()->createBlock('custommade/adminhtml_check_edit')
        )
            ->_addLeft(
                $this->getLayout()->createBlock('custommade/adminhtml_check_edit_tabs')
            );
        $this->renderLayout();
    }

    public function exportCsvAction()
    {
        $fileName = 'custommade.csv';
        $content = $this->getLayout()->createBlock('custommade/adminhtml_check_grid')->getCsv();
        $this->_sendUploadResponse($fileName, $content);
    }

    public function exportXmlAction()
    {
        $fileName = 'custommade.xml';
        $content = $this->getLayout()->createBlock('custommade/adminhtml_check_grid')->getXml();
        $this->_sendUploadResponse($fileName, $content);
    }

    protected function _sendUploadResponse($fileName, $content, $contentType='application/octet-stream')
    {
        $response = $this->getResponse();
        $response->setHeader('HTTP/1.1 200 OK','');
        $response->setHeader('Pragma', 'public', true);
        $response->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true);
        $response->setHeader('Content-Disposition', 'attachment; filename='.$fileName);
        $response->setHeader('Last-Modified', date('r'));
        $response->setHeader('Accept-Ranges', 'bytes');
        $response->setHeader('Content-Length', strlen($content));
        $response->setHeader('Content-type', $contentType);
        $response->setBody($content);
        $response->sendResponse();
        die;
    }

}