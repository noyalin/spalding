<?php

class Cobra_CustomMade_Helper_ImageHandler extends Mage_Core_Helper_Abstract
{
    //源图象
    var $_img;
    //图片类型
    var $_imagetype;
    //实际宽度
    var $_width;
    //实际高度
    var $_height;

    //获得图片的格式
    private function imageType($img_url)
    {
        $this->_imagetype = exif_imagetype($img_url);
    }

    public function createImages($sku, $position, $img_data, $info)
    {
        $_path = '/usr/custommade/tmp/';
        $watermark_show = Mage::getDesign()->getSkinUrl('images/customMade/'.$sku.'/'.$position.'_show.png');
        $watermark_print = Mage::getDesign()->getSkinUrl('images/customMade/'.$sku.'/'.$position.'_print.png');

        //原图
        $data = explode(',',$img_data);
        $data_decode = base64_decode($data[1]);
//        file_put_contents($_path.'1.jpg', $data_decode);
        $aa = exif_imagetype($data_decode);
        $this->load($data_decode);

        $this->save($_path.'1.jpg');
        $this->resize(4000,2000);
        $this->save($_path.'2.jpg');

    }

    //载入图片
    function load($img, $img_type = '')
    {

        switch ($this->_imagetype) {
            case 'jpg':
                $this->_img = imagecreatefromjpeg($img);
                break;
            case 'png':
                $this->_img = imagecreatefrompng($img);
                break;
            default:
                $this->_img = imagecreatefromstring($img);
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

}