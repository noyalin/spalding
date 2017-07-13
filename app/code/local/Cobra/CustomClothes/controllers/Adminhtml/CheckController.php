<?php

class Cobra_CustomClothes_Adminhtml_CheckController extends Mage_Adminhtml_Controller_action
{
    public function indexAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }
	

    public function massCancelAction()
    {
        $infoIds = $this->getRequest()->getParam('customclothes');
        if (!is_array($infoIds)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('customclothes')->__('Please select order(s)'));
        } else {
            foreach ($infoIds as $infoId) {
                $subscriber = Mage::getModel('customclothes/order')->load($infoId);
                $subscriber->cancel();
            }
        }
        $this->_redirect('*/*/index');
    }

    public function massExportAction()
    {	
        $infoIds = $this->getRequest()->getParam('customclothes');
        if (!is_array($infoIds)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('customclothes')->__('Please select order(s)'));
        } else {
        	$ret = $this->export($infoIds);
        	if ($ret == 1) {
        		// Success
        		//Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('customclothes')->__('订单导出:文件下载成功。'));
        	} else if ($ret == -1) {
        		Mage::getSingleton('adminhtml/session')->addError(Mage::helper('customclothes')->__('订单导出:文件下载异常，请重新导出。'));
        	} else if ($ret == -2) {
        		Mage::getSingleton('adminhtml/session')->addError(Mage::helper('customclothes')->__('订单导出:没有符合审批通过的订单。'));
        	} else if ($ret == -3) {
        		Mage::getSingleton('adminhtml/session')->addError(Mage::helper('customclothes')->__('订单导出:下载目录读写异常，请联系IT处理。'));
        	} else {
        		Mage::getSingleton('adminhtml/session')->addError(Mage::helper('customclothes')->__('订单导出:发生未知异常，请联系IT处理。'));
        	}
        }
        $this->_redirect('*/*/index');
    }
    
    public function newAction()
    {
        $this->loadLayout();
        $this->_addContent(
            $this->getLayout()->createBlock('customclothes/adminhtml_check_edit')
        )
            ->_addLeft(
                $this->getLayout()->createBlock('customclothes/adminhtml_check_edit_tabs')
            );
        $this->renderLayout();
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
        $model  = Mage::getModel('customclothes/order')->load($id);

        if ($model->getId() || $id == 0) {
            $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
            if (!empty($data)) {
                $model->setData($data);
            }

            Mage::register('customclothes_data', $model);

            $this->loadLayout();
            $this->_setActiveMenu('customclothes/items');

            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item Manager'), Mage::helper('adminhtml')->__('Item Manager'));
            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item News'), Mage::helper('adminhtml')->__('Item News'));

            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

            $this->_addContent($this->getLayout()->createBlock('customclothes/adminhtml_check_edit'))
                ->_addLeft($this->getLayout()->createBlock('customclothes/adminhtml_check_edit_tabs'));

            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('customclothes')->__('Item does not exist'));
            $this->_redirect('*/*/');
        }
    }


    private function export($infoIds)
    {

    	$orderModel = Mage::getModel('customclothes/order');
    	foreach ($infoIds as $key => $id){
    		$orderRow = $orderModel->load($id);
    		if($orderRow->getStatus() != Cobra_CustomClothes_Model_Order::STATUS_ALREADY_PAY){
    			unset($infoIds[$key]);
    		}
    	}
    	
    	if(!$infoIds){
    		return -2;
    	}
    	
        Mage::log("CustomClothes start export");
        $time = date('YmdHis', Mage::getModel('core/date')->timestamp(time()));
        
        $dir = Cobra_CustomClothes_Model_CustomClothes::$exportDir . $time;
        if (!mkdir($dir)) {
            Mage::log("CustomClothes end export return = －3");
            return -3;
        }

        $exportError = false;
		        
        foreach ($infoIds as $infoId) {
            Mage::log("export id=".$infoId." Start");
            $currentOrder = $orderModel->load($infoId);
            if ($currentOrder->getStatus() != Cobra_CustomClothes_Model_Order::STATUS_ALREADY_PAY) {
                Mage::log("export id=".$infoId." Status is not STATUS_ALREADY_PAY");
                continue;
            }
            $resultImage = $currentOrder->getResultImage();
            if (!mkdir($dir . "/" .$currentOrder->getOrderId())) {
                Mage::log("mkdir error. ".$dir . "/" .$currentOrder->getOrderId());
                return -3;
            }

            $imagePrefix = $dir . "/" . $currentOrder->getSku() . "-" . $currentOrder->getOrderId() . "-" . $time;

            if (!$this->grabImage($resultImage, $imagePrefix . ".png")) {
            	$exportError = true;
                break;
            }

            Mage::log("export id=".$infoId." End");
        }

        if ($exportError) {
            $ret = -1;
        }else {
//             $file = fopen($dir . "/" . $time . ".txt", "w");
//             if (!$file) {
//                 Mage::log("CustomClothes end export return = －3");
//                 return -3;
//             }
            foreach ($infoIds as $infoId) {
                $currentOrder = $orderModel->load($infoId);
                $content = $currentOrder->getSku() . "-" . $currentOrder->getOrderId() . "\r\n";
                if ($currentOrder->getStatus() != Cobra_CustomClothes_Model_Order::STATUS_ALREADY_PAY) {
                    continue;
                }
                $currentOrder->export($dir);
                //fwrite($file, $content);
            }
            //fclose($file);

            $zip = new ZipArchive();
            $zip_name = $dir . '.zip';
            if ($zip->open($zip_name, ZipArchive::OVERWRITE) === TRUE) {
            	$this->folderToZip($dir,$zip);
            }
            
//             $zip = new ZipArchive();
//             $zip_name = $dir . '.zip';
//             if ($zip->open($zip_name, ZipArchive::OVERWRITE) === TRUE) {
//                 $handler = opendir($dir);
//                 while (($filename = readdir($handler)) !== false) {
//                     if (is_file($dir . "/" . $filename)) {
//                         $zip->addFile($dir . "/" . $filename);
//                     }
//                 }
//                 closedir($handler);
//             }
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

        Mage::log("CustomClothes end export return = ".$ret);

        return $ret;
    }

    private function folderToZip($folder, &$zipFile, $subfolder = null) {
    	 
    	// we check if $folder has a slash at its end, if not, we append one
    	$folder .= end(str_split($folder)) == "/" ? "" : "/";
    	$subfolder .= end(str_split($subfolder)) == "/" ? "" : "/";
    	// we start by going through all files in $folder
    	$handle = opendir($folder);
    	while ($f = readdir($handle)) {
    		if ($f != "." && $f != "..") {
    			if (is_file($folder . $f)) {
    				// if we find a file, store it
    				// if we have a subfolder, store it there
    				if ($subfolder != null)
    					$zipFile->addFile($folder . $f, $subfolder . $f);
    				else
    					$zipFile->addFile($folder . $f);
    			} elseif (is_dir($folder . $f)) {
    				// if we find a folder, create a folder in the zip
    				$zipFile->addEmptyDir($f);
    				// and call the function again
    				$this->folderToZip($folder . $f, $zipFile, $f);
    			}
    		}
    	}
    	closedir($handle);
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