<?php

class Cobra_CustomMade_Adminhtml_CheckController extends Mage_Adminhtml_Controller_action
{
    public function indexAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function massNonPaymentAction()
    {
        $infoIds = $this->getRequest()->getParam('custommade');
        if (!is_array($infoIds)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('custommade')->__('Please select order(s)'));
        }
        foreach ($infoIds as $infoId) {
            $subscriber = Mage::getModel('custommade/info')->load($infoId);
            $subscriber->nonpayment();
        }
        $this->_redirect('*/*/index');
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

    public function editAction() {
        $id     = $this->getRequest()->getParam('id');
        $model  = Mage::getModel('custommade/info')->load($id);

        if ($model->getId() || $id == 0) {
            $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
            if (!empty($data)) {
                $model->setData($data);
            }

            Mage::register('custommade_data', $model);

            $this->loadLayout();
            $this->_setActiveMenu('custommade/items');

            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item Manager'), Mage::helper('adminhtml')->__('Item Manager'));
            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item News'), Mage::helper('adminhtml')->__('Item News'));

            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

            $this->_addContent($this->getLayout()->createBlock('custommade/adminhtml_check_edit'))
                ->_addLeft($this->getLayout()->createBlock('custommade/adminhtml_check_edit_tabs'));

            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('custommade')->__('Item does not exist'));
            $this->_redirect('*/*/');
        }
    }

    public function saveAction() {
        if ($data = $this->getRequest()->getPost()) {

            if(isset($_FILES['filename']['name']) && $_FILES['filename']['name'] != '') {
                try {
                    /* Starting upload */
                    $uploader = new Varien_File_Uploader('filename');

                    // Any extention would work
                    $uploader->setAllowedExtensions(array('jpg','jpeg','gif','png'));
                    $uploader->setAllowRenameFiles(false);

                    // Set the file upload mode
                    // false -> get the file directly in the specified folder
                    // true -> get the file in the product like folders
                    //	(file.jpg will go in something like /media/f/i/file.jpg)
                    $uploader->setFilesDispersion(false);

                    // We set media as the upload dir
                    $path = Mage::getBaseDir('media') . DS ;
                    $uploader->save($path, $_FILES['filename']['name'] );

                } catch (Exception $e) {

                }

                //this way the name is saved in DB
                $data['filename'] = $_FILES['filename']['name'];
            }


            $model = Mage::getModel('custommade/info');

            $urlShowP1 = $model->getP1Url($data->getTypeP1(), $data->getMsg1P1(), $data->getMsg2P1(), $data->getMsg3P1(), $data->getMsg4P1(), 'show');
            $urlPrintP1 = $model->getP1Url($data->getTypeP1(), $data->getMsg1P1(), $data->getMsg2P1(), $data->getMsg3P1(), $data->getMsg4P1(), 'print');
            $data->setMsg5P1($urlShowP1);
            $data->setMsg6P1($urlPrintP1);
            $urlShowP2 = $model->getP2Url($data->getTypeP2(), $data->getMsg1P2(), $data->getMsg2P2(), $data->getMsg3P2(), $data->getMsg4P2(), 'show');
            $urlPrintP2 = $model->getP2Url($data->getTypeP2(), $data->getMsg1P2(), $data->getMsg2P2(), $data->getMsg3P2(), $data->getMsg4P2(), 'print');
            $data->setMsg5P2($urlShowP2);
            $data->setMsg6P2($urlPrintP2);

            $model->setData($data)
                ->setId($this->getRequest()->getParam('id'));

            try {
                if ($model->getCreatedTime() == NULL || $model->getUpdateTime() == NULL) {
                    $model->setCreatedTime(now())
                        ->setUpdateTime(now());
                } else {
                    $model->setUpdateTime(now());
                }

                $model->save();
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('custommade')->__('Item was successfully saved'));
                Mage::getSingleton('adminhtml/session')->setFormData(false);

                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $model->getId()));
                    return;
                }
                $this->_redirect('*/*/');
                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('custommade')->__('Unable to find item to save'));
        $this->_redirect('*/*/');
    }
}