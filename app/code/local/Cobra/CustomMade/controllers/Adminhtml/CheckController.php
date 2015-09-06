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

    public function massNotApprovedAction()
    {
        $infoIds = $this->getRequest()->getParam('custommade');
        if (!is_array($infoIds)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('custommade')->__('Please select order(s)'));
        }
        foreach ($infoIds as $infoId) {
            $subscriber = Mage::getModel('custommade/info')->load($infoId);
            $subscriber->notapproved();
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

    public function massExportAction()
    {
        $infoIds = $this->getRequest()->getParam('custommade');
        if (!is_array($infoIds)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('custommade')->__('Please select order(s)'));
        }

        $this->export($infoIds);

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

            $urlShowP1 = $model->createP1Url($data['type_p1'], $data['msg1_p1'], $data['msg2_p1'], $data['msg3_p1'], $data['msg4_p1'], 'show');
            $urlPrintP1 = $model->createP1Url($data['type_p1'], $data['msg1_p1'], $data['msg2_p1'], $data['msg3_p1'], $data['msg4_p1'], 'print');
            $data['msg5_p1'] = $urlShowP1;
            $data['msg6_p1'] = $urlPrintP1;
            $urlShowP2 = $model->createP2Url($data['type_p2'], $data['msg1_p2'], $data['msg2_p2'], $data['msg3_p2'], $data['msg4_p2'], 'show');
            $urlPrintP2 = $model->createP2Url($data['type_p2'], $data['msg1_p2'], $data['msg2_p2'], $data['msg3_p2'], $data['msg4_p2'], 'print');
            $data['msg5_p2'] = $urlShowP2;
            $data['msg6_p2'] = $urlPrintP2;

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

    private function export($infoIds)
    {
        $time = date("dMYHis");
        $dir = "/usr/custommade/customlist/" . $time;
        mkdir($dir);
        $file = fopen($dir . "/" . $time . ".txt", "w");

        foreach ($infoIds as $infoId) {
            $subscriber = Mage::getModel('custommade/info')->load($infoId);
            $content = $subscriber->getId() . "-" . $subscriber->getSku() . "-" . $subscriber->getOrderId() . "\r\n";
            fwrite($file, $content);
            $p1_preview_url = $subscriber->getMsg5P1();
            $p1_print_url = $subscriber->getMsg6P1();
            $p2_preview_url = $subscriber->getMsg5P2();
            $p2_print_url = $subscriber->getMsg6P2();
            $img_prefix = $dir . "/" . $subscriber->getSku() . "-" . $subscriber->getOrderId() . "-" . $time;
            if ($p1_preview_url != null) {
                $this->grabImage($p1_preview_url, $img_prefix . "-p1-preview.png");
            }
            if ($p1_print_url != null) {
                $this->grabImage($p1_print_url, $img_prefix . "-p1-print.png");
            }
            if ($p2_preview_url != null) {
                $this->grabImage($p2_preview_url, $img_prefix . "-p2-preview.png");
            }
            if ($p2_print_url != null) {
                $this->grabImage($p2_print_url, $img_prefix . "-p2-print.png");
            }
            $subscriber->export();
        }
        fclose($file);
    }

    private function grabImage($url, $filename)
    {
        if ($url == ""):return false;endif;

        ob_start();
        readfile($url);
        $img = ob_get_contents();
        ob_end_clean();
        $size = strlen($img);
        if ($img && $size) {
            $fp2 = @fopen($filename, "a");
            fwrite($fp2, $img);
            fclose($fp2);
            return $filename;
        } else {
            return null;
        }
    }
}