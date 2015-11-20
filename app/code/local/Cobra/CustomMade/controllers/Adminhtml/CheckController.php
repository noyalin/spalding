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
        } else {
            foreach ($infoIds as $infoId) {
                $subscriber = Mage::getModel('custommade/info')->load($infoId);
                $subscriber->cancel();
            }
        }
        $this->_redirect('*/*/index');
    }

    public function massExportAction()
    {
        $infoIds = $this->getRequest()->getParam('custommade');
        if (!is_array($infoIds)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('custommade')->__('Please select order(s)'));
        } else {
            $ret = $this->export($infoIds);
            if ($ret == 1) {
                // Success
                //Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('custommade')->__('订单导出:文件下载成功。'));
            } else if ($ret == -1) {
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('custommade')->__('订单导出:文件下载异常，请重新导出。'));
            } else if ($ret == -2) {
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('custommade')->__('订单导出:没有符合审批通过的订单。'));
            } else if ($ret == -3) {
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('custommade')->__('订单导出:下载目录读写异常，请联系IT处理。'));
            } else {
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('custommade')->__('订单导出:发生未知异常，请联系IT处理。'));
            }
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

            if ($data['status'] == 1 || $data['status'] == 2 || $data['status'] == 3) {
                if ($data['user1_approve'] == 0 && $data['user2_approve'] == 0 /*|| $data['user3_approve'] == 0 || $data['user4_approve'] == 0*/) {
                    $data['status'] = 1;
                } elseif ($data['user1_approve'] == 1 && $data['user2_approve'] == 1 /*&& $data['user3_approve'] == 1 && $data['user4_approve'] == 1*/) {
                    $data['status'] = 2;
                } elseif ($data['user1_approve'] == 2 || $data['user2_approve'] == 2 /*&& $data['user3_approve'] == 1 && $data['user4_approve'] == 1*/) {
                    $data['status'] = 3;
                } elseif ($data['user1_approve'] == 0 || $data['user2_approve'] == 0 /*&& $data['user3_approve'] == 1 && $data['user4_approve'] == 1*/) {
                    $data['status'] = 1;
                }
            }

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
        Mage::log("CustomMade start export");
        $time = date('YmdHis', Mage::getModel('core/date')->timestamp(time()));
        $dir = "/tmp/custommade/customlist/" . $time;
        if (!mkdir($dir)) {
            Mage::log("CustomMade end export return = －3");
            return -3;
        }

        $exportFlag = false;
        $exportError = false;

        foreach ($infoIds as $infoId) {
            Mage::log("export id=".$infoId." Start");
            $subscriber = Mage::getModel('custommade/info')->load($infoId);
            if ($subscriber->getStatus() != 2) {
                Mage::log("export id=".$infoId." Status != 2");
                continue;
            }
            $p1_preview_url = $subscriber->getMsg5P1();
            $p1_print_url = $subscriber->getMsg6P1();
            $p2_preview_url = $subscriber->getMsg5P2();
            $p2_print_url = $subscriber->getMsg6P2();
            $img_prefix = $dir . "/" . $subscriber->getSku() . "-" . $subscriber->getOrderId() . "-" . $time;
            if($subscriber->getSku() == '74-602yc-ID02') {
                if ($p1_preview_url) {
                    if (!$this->grabImage($p1_preview_url, $img_prefix . "-2-preview.png")) {
                        $exportError = true;
                        break;
                    }
                }
                if ($p1_print_url) {
                    if (!$this->grabImage($p1_print_url, $img_prefix . "-2-print.png")) {
                        $exportError = true;
                        break;
                    }
                }
                if ($p2_preview_url) {
                    if (!$this->grabImage($p2_preview_url, $img_prefix . "-6-preview.png")) {
                        $exportError = true;
                        break;
                    }
                }
                if ($p2_print_url) {
                    if (!$this->grabImage($p2_print_url, $img_prefix . "-6-print.png")) {
                        $exportError = true;
                        break;
                    }
                }
            } else {
                if ($p1_preview_url) {
                    if (!$this->grabImage($p1_preview_url, $img_prefix . "-4-preview.png")) {
                        $exportError = true;
                        break;
                    }
                }
                if ($p1_print_url) {
                    if (!$this->grabImage($p1_print_url, $img_prefix . "-4-print.png")) {
                        $exportError = true;
                        break;
                    }
                }
                if ($p2_preview_url) {
                    if (!$this->grabImage($p2_preview_url, $img_prefix . "-8-preview.png")) {
                        $exportError = true;
                        break;
                    }
                }
                if ($p2_print_url) {
                    if (!$this->grabImage($p2_print_url, $img_prefix . "-8-print.png")) {
                        $exportError = true;
                        break;
                    }
                }
            }
            $exportFlag = true;
            Mage::log("export id=".$infoId." End");
        }

        if ($exportError) {
            $ret = -1;
        }
        else if (!$exportFlag) {
            $ret = -2;
        }
        else {

            $file = fopen($dir . "/" . $time . ".txt", "w");
            if (!$file) {
                Mage::log("CustomMade end export return = －3");
                return -3;
            }
            foreach ($infoIds as $infoId) {
                $subscriber = Mage::getModel('custommade/info')->load($infoId);
                $content = $subscriber->getSku() . "-" . $subscriber->getOrderId() . "\r\n";
                if ($subscriber->getStatus() != 2) {
                    continue;
                }
                $subscriber->export();
                fwrite($file, $content);
            }
            fclose($file);

            $zip = new ZipArchive();
            $zip_name = $dir . '.zip';
            if ($zip->open($zip_name, ZipArchive::OVERWRITE) === TRUE) {
                $handler = opendir($dir);
                while (($filename = readdir($handler)) !== false) {
                    if (is_file($dir . "/" . $filename)) {
                        $zip->addFile($dir . "/" . $filename);
                    }
                }
                closedir($handler);
            }
            $zip->close();

            header("Cache-Control: public");
            header("Content-Description: File Transfer");
            header('Content-disposition: attachment; filename='.basename($zip_name));
            header("Content-Type: application/zip");
            header("Content-Transfer-Encoding: binary");
            header('Content-Length: '. filesize($zip_name));
            readfile($zip_name);

            $ret = 1;
        }

        Mage::log("CustomMade end export return = ".$ret);

        return $ret;
    }

    private function grabImage($url, $filename)
    {
        ob_start();
        $downloadUrl = str_replace(' ', '%20', $url);
        Mage::log("grabImage url=" . $downloadUrl . ", filename=" . $filename);

        $tryFlg = 0;

        while (true) {
            if ($tryFlg > 3) {
                return false;
            }
            if (readfile($downloadUrl)) {
                break;
            }
            $tryFlg++;
            Mage::log("grabImage err readfile == false, Try:".$tryFlg);
        }
        $img = ob_get_contents();
        ob_end_clean();
        $size = strlen($img);
        if ($img && $size) {
            $fp2 = @fopen($filename, "a");
            if (!$fp2) {
                Mage::log("grabImage fp2 == null");
                return false;
            }
            fwrite($fp2, $img);
            fclose($fp2);
            return $filename;
        } else {
            Mage::log("grabImage err size == 0");
            return false;
        }
    }
}