<?php

class Cobra_CustomMade_IndexController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
//        $test = Mage::getModel('custommade/info')->getCollection();
//        foreach ($test as $data) {
//            echo $data->getOrderId() . "\n";
//            echo $data->getType() . "\n";
//        }

        //////////////////////////////////////////////
//        $type = 2;
//        $content1 = "aaaaa";
//        $content2 = "2";

        Mage::getSingleton('core/session')->setTestMode(0);

        $type = 1;
        $content1 = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).'/custommade/tmp/test_p1_01.jpg';
        $content2 = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).'/custommade/tmp/test_p1_02.jpg';

        Mage::getSingleton('core/session')->setTypeP1($type);
        Mage::getSingleton('core/session')->setContent1P1($content1);
        Mage::getSingleton('core/session')->setContent2P1($content2);


//////////////////////////////////////////////////////////////////

//        $type = 2;
//        $content1 = "bbbb";
//        $content2 = "1";
        $type = 1;
        $content1 = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).'/custommade/tmp/test_p2_01.jpg';
        $content2 = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).'/custommade/tmp/test_p2_02.jpg';


        Mage::getSingleton('core/session')->setTypeP2($type);
        Mage::getSingleton('core/session')->setContent1P2($content1);
        Mage::getSingleton('core/session')->setContent2P2($content2);

        echo "indexAction Ok";
    }

    public function allAction()
    {
        Mage::getSingleton('core/session')->setTestMode(1);

        echo "allAction Ok";
    }

    public function textAction()
    {
        Mage::getSingleton('core/session')->setTestMode(0);

        echo "textAction Ok";
    }

    public function completeAction()
    {
        $params = Mage::app()->getRequest()->getParams();
        $pos = $params['position'];
        $type = $params['type'];
        $content1 = null;
        $content2 = null;
        $content3 = null;
        $content4 = null;

        if ($pos == 1) {
            if ($type == 1) {
                $imgPath = 'media/custommade/tmp/';
                $imgBaseName = time();
                $img0 = $imgBaseName . '_p1_0.jpg';
                $img1 = $imgBaseName . '_p1_1.jpg';
                $img2 = $imgBaseName . '_p1_2.jpg';

                $data = str_replace("data:image/jpeg;base64,", "", $params['originalImg']);
                $data_decode = base64_decode($data);
                file_put_contents($imgPath . $img0, $data_decode);


                $imgresize = new ImageResize(); //创建图片缩放和裁剪类
                $imgresize->load($data_decode); //载入原始图片

                $posary = explode(',', $params['cut_pos']);
                foreach ($posary as $k => $v) $posary[$k] = intval($v); //获得缩放比例和裁剪位置

                if ($posary[2] > 0 && $posary[3] > 0) $imgresize->resize($posary[2], $posary[3]); //图片缩放

                $imgresize->save($imgPath . $img1);
                $content1 = Mage::getUrl($imgPath) . $img1;

                $imgresize->cut(400, 190, intval($posary[0]), intval($posary[1]));

                $imgresize->save($imgPath . $img2);
                $content2 = Mage::getUrl($imgPath) . $img2;

            } elseif ($type == 2) {
                $content1 = $params['text1'];
                $content2 = $params['text3'];
                $content3 = $params['size'];
                $content4 = $params['font'];
            } else {
                $type = 3;
                $content1 = null;
                $content2 = null;
                $content3 = null;
                $content4 = null;
            }

            Mage::getSingleton('core/session')->setTypeP1($type);
            Mage::getSingleton('core/session')->setContent1P1($content1);
            Mage::getSingleton('core/session')->setContent2P1($content2);
            Mage::getSingleton('core/session')->setContent3P1($content3);
            Mage::getSingleton('core/session')->setContent4P1($content4);

        } elseif ($pos == 2) {
            if ($type == 1) {
                $imgPath = 'media/custommade/tmp/';
                $imgBaseName = time();
                $img0 = $imgBaseName . '_p2_0.jpg';
                $img1 = $imgBaseName . '_p2_1.jpg';
                $img2 = $imgBaseName . '_p2_2.jpg';

                $data = str_replace("data:image/jpeg;base64,", "", $params['originalImg']);
                $data_decode = base64_decode($data);
                file_put_contents($imgPath . $img0, $data_decode);

                $imgresize = new ImageResize(); //创建图片缩放和裁剪类
                $imgresize->load($data_decode); //载入原始图片

                $posary = explode(',', $params['cut_pos']);
                foreach ($posary as $k => $v) $posary[$k] = intval($v); //获得缩放比例和裁剪位置

                if ($posary[2] > 0 && $posary[3] > 0) $imgresize->resize($posary[2], $posary[3]); //图片缩放

                $imgresize->save($imgPath . $img1);
                $content1 = Mage::getUrl($imgPath) . $img1;

                $imgresize->cut(400, 190, intval($posary[0]), intval($posary[1]));

                $imgresize->save($imgPath . $img2);
                $content2 = Mage::getUrl($imgPath) . $img2;
            } elseif ($type == 2) {
                $content1 = $params['text2'];
                $content2 = $params['text4'];
                $content3 = $params['size'];
                $content4 = $params['font'];
            } else {
                $type = 3;
                $content1 = null;
                $content2 = null;
                $content3 = null;
                $content4 = null;
            }

            Mage::getSingleton('core/session')->setTypeP2($type);
            Mage::getSingleton('core/session')->setContent1P2($content1);
            Mage::getSingleton('core/session')->setContent2P2($content2);
            Mage::getSingleton('core/session')->setContent3P2($content3);
            Mage::getSingleton('core/session')->setContent4P2($content4);

        }

        Mage::getSingleton('core/session')->setPos($pos);

        echo self::getCustomMadeSession($pos);
    }

    public function resetAction()
    {
        $params = Mage::app()->getRequest()->getParams();
        $pos = $params['position'];
        if ($pos == 1) {
            Mage::getSingleton('core/session')->setTypeP1(null);
            Mage::getSingleton('core/session')->setContent1P1(null);
            Mage::getSingleton('core/session')->setContent2P1(null);
            Mage::getSingleton('core/session')->setContent3P1(null);
            Mage::getSingleton('core/session')->setContent4P1(null);
        } elseif ($pos == 2) {
            Mage::getSingleton('core/session')->setTypeP2(null);
            Mage::getSingleton('core/session')->setContent1P2(null);
            Mage::getSingleton('core/session')->setContent2P2(null);
            Mage::getSingleton('core/session')->setContent3P2(null);
            Mage::getSingleton('core/session')->setContent4P2(null);
        }
        Mage::getSingleton('core/session')->setPos($pos);
    }

    public function previewAction()
    {
        $status = Mage::getSingleton('core/session')->getCustomStatus();
        if ($status == 1) {
            Mage::getSingleton('core/session')->setCustomStatus(0);
        } else {
            Mage::getSingleton('core/session')->setCustomStatus(1);
        }
    }

    public function checkAction()
    {
        $params = Mage::app()->getRequest()->getParams();
        $position = $params['position'];
        echo self::getCustomMadeSession($position);

    }

    private function getCustomMadeSession($position)
    {
        $session = Mage::getSingleton('core/session');
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