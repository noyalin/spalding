<?php

class Cobra_CustomMade_IndexController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
//        $session = self::getSession();
//
////        Mage::getSingleton('core/session')->setTestMode(0);
//        $session->setTestMode(0);
//
//        $type = 1;
//        $content1 = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).'/custommade/tmp/test_p1_01.jpg';
//        $content2 = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).'/custommade/tmp/test_p1_02.jpg';
//
////        Mage::getSingleton('core/session')->setTypeP1($type);
////        Mage::getSingleton('core/session')->setContent1P1($content1);
////        Mage::getSingleton('core/session')->setContent2P1($content2);
//        $session->setTypeP1($type);
//        $session->setContent1P1($content1);
//        $session->setContent2P1($content2);
//
//        $type = 1;
//        $content1 = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).'/custommade/tmp/test_p2_01.jpg';
//        $content2 = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).'/custommade/tmp/test_p2_02.jpg';
//
//
////        Mage::getSingleton('core/session')->setTypeP2($type);
////        Mage::getSingleton('core/session')->setContent1P2($content1);
////        Mage::getSingleton('core/session')->setContent2P2($content2);
//        $session->setTypeP2($type);
//        $session->setContent1P2($content1);
//        $session->setContent2P2($content2);
//
//        self::setSession($session);
//
//        echo "indexAction Ok";
    }

    public function allAction()
    {
////        $session = self::getSession();
//        Mage::getSingleton('core/session')->setTestMode(1);
////        $session->setTestMode(1);
////        self::setSession($session);
//
//        echo "allAction Ok";
    }

    public function textAction()
    {
////        $session = self::getSession();
//        Mage::getSingleton('core/session')->setTestMode(0);
////        $session->setTestMode(0);
////        self::setSession($session);
//
//        echo "textAction Ok";
    }

    public function clearSessionAction()
    {
        Mage::getSingleton('core/session')->setCustomerId(null);
        Mage::getSingleton('core/session')->getCurrentSku(null);

        echo "Clear OK";
    }

    public function completeAction()
    {
        Mage::log('completeAction');
        $params = Mage::app()->getRequest()->getParams();
        //params
        //position 第一面或者第二面
        //font 字体格式
        //size 字体大小
        $sku = $params['sku'];
        $session = self::getSession($sku);

        $pos = $params['position'];
        $type = $params['type'];
        $content1 = null; 
        $content2 = null;
        $content3 = null;
        $content4 = null;

        if ($pos == 1) {
            if ($type == 1) {
                $url_array = Mage::helper('custommade/imagehandler')->createImages($sku, $pos, $params['originalImg'], $params['cut_pos']);
                if ($url_array) {
                    $content1 = $url_array['effect'];
                    $content2 = $url_array['show'];
                    $content3 = $url_array['print'];
                } else {
                    $content1 = "error";
                }
            } elseif ($type == 2) {
                $content3 = $params['size'];
                $content4 = $params['font'];
                $content1 = $this->getCheckedText($params['text1']);
                if ($content3 == '4') {
                    $content2 = $this->getCheckedText($params['text3']);
                } else {
                    $content2 = '';
                }
                Mage::log('completeAction P1 ： '.$content1.' | '.$content2.' | '.$content3.' | '.$content4);
            } elseif ($type == 4) {
            	$content1 = $params['text1'];;
            } else {
                $type = 3;
            }
            $session->setTypeP1($type);
            $session->setContent1P1($content1);
            $session->setContent2P1($content2);
            $session->setContent3P1($content3);
            $session->setContent4P1($content4);

        } elseif ($pos == 2) {
            if ($type == 1) {
                $url_array = Mage::helper('custommade/imagehandler')->createImages($sku, $pos, $params['originalImg'], $params['cut_pos']);
                if ($url_array) {
                    $content1 = $url_array['effect'];
                    $content2 = $url_array['show'];
                    $content3 = $url_array['print'];
                } else {
                    $content1 = "error";
                }
            } elseif ($type == 2) {
                $content3 = $params['size'];
                $content4 = $params['font'];
                $content1 = $this->getCheckedText($params['text2']);
                if ($content3 == '4') {
                    $content2 = $this->getCheckedText($params['text4']);
                } else {
                    $content2 = '';
                }
                Mage::log('completeAction P2 ： '.$content1.' | '.$content2.' | '.$content3.' | '.$content4);
            }elseif ($type == 4) {
            	$content1 = $params['text2'];;
            }
            else {
                $type = 3;
            }
            $session->setTypeP2($type);
            $session->setContent1P2($content1);
            $session->setContent2P2($content2);
            $session->setContent3P2($content3);
            $session->setContent4P2($content4);

        }

        $session->setPos($pos);
        self::setSession($session);

        echo self::getCustomMadeSession($pos, $sku);
    }

    public function resetAction()
    {
        Mage::log('resetAction');
        $params = Mage::app()->getRequest()->getParams();
        $sku = $params['sku'];
        $session = self::getSession($sku);

        $pos = $params['position'];
        if ($pos == 1) {
            $session->setTypeP1(null);
            $session->setContent1P1(null);
            $session->setContent2P1(null);
            $session->setContent3P1(null);
            $session->setContent4P1(null);
        } elseif ($pos == 2) {
            $session->setTypeP2(null);
            $session->setContent1P2(null);
            $session->setContent2P2(null);
            $session->setContent3P2(null);
            $session->setContent4P2(null);
        }
        $session->setPos($pos);
        self::setSession($session);
    }

    public function previewAction()
    {
        $dir = "/usr/custommade/";
        $file = fopen($dir . "preview_account.txt", "a");

        Mage::log('previewAction');
        $params = Mage::app()->getRequest()->getParams();
        $sku = $params['sku'];
        $session = self::getSession($sku);
        $status = $session->getCustomStatus();
        if ($status == 1) {
            $session->setCustomStatus(0);
            fwrite($file, "previewAction-----custom\r\n");
        } else {
            $session->setCustomStatus(1);
            fwrite($file, "previewAction-----preview\r\n");
        }
        fclose($file);
        self::setSession($session);
    }

    public function checkAction()
    {
        $customerId = Mage::getSingleton('core/session')->getCustomerId();
        Mage::log('checkAction $customerId='.$customerId);
        $params = Mage::app()->getRequest()->getParams();
        $position = $params['position'];
        $sku = $params['sku'];
        echo self::getCustomMadeSession($position, $sku);

    }

    public function agreeAction()
    {
        Mage::getSingleton('core/session')->setCustomermadeAgree(1);
    }

    private function getCustomMadeSession($position ,$sku)    {
        Mage::log("getCustomMadeSession($position ,$sku)");
        $session = self::getSession($sku);
        $res = array();;
        if ($position == 1) {
            $res['type'] = $session->getTypeP1();
            $res['content1'] = $session->getContent1P1();
            $res['content2'] = $session->getContent2P1();
            $res['content3'] = $session->getContent3P1();
            $res['content4'] = $session->getContent4P1();
        } elseif ($position == 2) {
            $res['type'] = $session->getTypeP2();
            $res['content1'] = $session->getContent1P2();
            $res['content2'] = $session->getContent2P2();
            $res['content3'] = $session->getContent3P2();
            $res['content4'] = $session->getContent4P2();
        }
        $resultString = json_encode($res);
        return $resultString;
    }

    public function reviewAction()
    {
        if (Mage::getSingleton('core/session')->getCustomerId()) {
            $customerId = Mage::getSingleton('core/session')->getCustomerId();
            Mage::log('CustomMade reviewAction--aaaaaaaa--customerId='.$customerId);
        } else {
            $customerId = Mage::getModel('custommade/customer')->createCustomer();
            Mage::getSingleton('core/session')->setCustomerId($customerId);
            Mage::log('CustomMade reviewAction--bbbbbbb--customerId='.$customerId);
        }
        if (Mage::getSingleton('customer/session')->isLoggedIn()) {
            $params = Mage::app()->getRequest()->getParams();
            $userCustomerId = Mage::getSingleton('customer/session')->getCustomer()->getId();
            Mage::getModel('custommade/session')->rewriteSession($customerId, $params['sku'], $userCustomerId);
        }
        Mage::getSingleton('core/session')->setCustomermadeAgree(1);
        $sku = $params['sku'];
        $productModel = Mage::getModel('catalog/product');
        $product = $productModel->load($productModel->getIdBySku($sku));
        $url = $product->getProductUrl();
        header("Location:".$url);
        exit;
        //$this->_redirect($product->getProductUrl());
    }


    private function getSession($sku)
    {
        $customerId = Mage::getSingleton('core/session')->getCustomerId();
        Mage::log('getSession $customerId='.$customerId);
//        $currentSku = Mage::getSingleton('core/session')->getCurrentSku();
//        $session = Mage::getModel('custommade/session')->getSession($customerId, $currentSku);
        $session = Mage::getModel('custommade/session')->getSession($customerId, $sku);
        return $session;
    }

    private function setSession($session)
    {
        Mage::getModel('custommade/session')->setSession($session);
    }

    private function getCheckedText($str)
    {
        $pattern = '/[^A-Za-z0-9\s\-\.@_&:]*/';
        $str = preg_replace($pattern, '', $str);
        return $str;
    }
}

//--------------------------------------------------------

/**
 * 图片缩放和裁剪类
 */
class ImageResize
{
    //源图象
    var $_img;
    //图片类型
    var $_imagetype;
    //实际宽度
    var $_width;
    //实际高度
    var $_height;

    //载入图片
    function load($img_name, $img_type = '')
    {
        if (!empty($img_type)) $this->_imagetype = $img_type;
        else $this->_imagetype = $this->get_type($img_name);
        switch ($this->_imagetype) {
            case 'gif':
                if (function_exists('imagecreatefromgif')) $this->_img = imagecreatefromgif($img_name);
                break;
            case 'jpg':
                $this->_img = imagecreatefromjpeg($img_name);
                break;
            case 'png':
                $this->_img = imagecreatefrompng($img_name);
                break;
            default:
                $this->_img = imagecreatefromstring($img_name);
                break;
        }
        $this->getxy();
    }

    //缩放图片
    function resize($width, $height, $percent = 0)
    {
        if (!is_resource($this->_img)) return false;
        if (empty($width) && empty($height)) {
            if (empty($percent)) return false;
            else {
                $width = round($this->_width * $percent);
                $height = round($this->_height * $percent);
            }
        } elseif (empty($width) && !empty($height)) {
            $width = round($height * $this->_width / $this->_height);
        } else {
            $height = round($width * $this->_height / $this->_width);
        }
        $tmpimg = imagecreatetruecolor($width, $height);
        if (function_exists('imagecopyresampled')) imagecopyresampled($tmpimg, $this->_img, 0, 0, 0, 0, $width, $height, $this->_width, $this->_height);
        else imagecopyresized($tmpimg, $this->_img, 0, 0, 0, 0, $width, $height, $this->_width, $this->_height);
        $this->destroy();
        $this->_img = $tmpimg;
        $this->getxy();
    }

    //裁剪图片
    function cut($width, $height, $x = 0, $y = 0)
    {
        if (!is_resource($this->_img)) return false;
        if ($width > $this->_width) $width = $this->_width;
        if ($height > $this->_height) $height = $this->_height;
        if ($x < 0) $x = 0;
        if ($y < 0) $y = 0;
        $tmpimg = imagecreatetruecolor($width, $height);
        imagecopy($tmpimg, $this->_img, 0, 0, $x, $y, $width, $height);
        $this->destroy();
        $this->_img = $tmpimg;
        $this->getxy();
    }


    //显示图片
    function display($destroy = true)
    {
        if (!is_resource($this->_img)) return false;
        switch ($this->_imagetype) {
            case 'jpg':
            case 'jpeg':
                header("Content-type: image/jpeg");
                imagejpeg($this->_img);
                break;
            case 'gif':
                header("Content-type: image/gif");
                imagegif($this->_img);
                break;
            case 'png':
            default:
                header("Content-type: image/png");
                imagepng($this->_img);
                break;
        }
        if ($destroy) $this->destroy();
    }

    //保存图片 $destroy=true 是保存后销毁图片变量，false这不销毁，可以继续处理这图片
    function save($fname, $destroy = false, $type = '')
    {
        if (!is_resource($this->_img)) return false;
        if (empty($type)) $type = $this->_imagetype;
        switch ($type) {
            case 'jpg':
            case 'jpeg':
                $ret = imagejpeg($this->_img, $fname);
                break;
            case 'gif':
                $ret = imagegif($this->_img, $fname);
                break;
            case 'png':
            default:
                $ret = imagepng($this->_img, $fname);
                break;
        }
        if ($destroy) $this->destroy();
        return $ret;
    }

    //销毁图像
    function destroy()
    {
        if (is_resource($this->_img)) imagedestroy($this->_img);
    }

    //取得图像长宽
    function getxy()
    {
        if (is_resource($this->_img)) {
            $this->_width = imagesx($this->_img);
            $this->_height = imagesy($this->_img);
        }
    }


    //获得图片的格式，包括jpg,png,gif
    function get_type($img_name)//获取图像文件类型
    {
        if (preg_match("/\.(jpg|jpeg|gif|png)$/i", $img_name, $matches)) {
            $type = strtolower($matches[1]);
        } else {
            $type = "string";
        }
        return $type;
    }

}