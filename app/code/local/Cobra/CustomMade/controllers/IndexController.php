<?php

class Cobra_CustomMade_IndexController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
        $test = Mage::getModel('custommade/info')->getCollection();
        foreach ($test as $data) {
            echo $data->getOrderId() . "\n";
            echo $data->getType() . "\n";
        }
    }

    public function completeAction()
    {
        $params = Mage::app()->getRequest()->getParams();
        if ($params[position] == 'P1') {
            Mage::getSingleton('core/session')->setData('step_p1', 1);
        } elseif ($params[position] == 'P2') {
            Mage::getSingleton('core/session')->setData('step_p2', 1);
        }

        $data = str_replace("data:image/jpeg;base64,", "", $params['originalImg']);
        $data_decode = base64_decode($data);
        file_put_contents("media/tmp/img.jpg", $data_decode);


        $imgresize = new ImageResize(); //创建图片缩放和裁剪类
        $imgresize->load($data_decode); //载入原始图片

        $posary=explode(',', $params['cut_pos']);
        foreach($posary as $k=>$v) $posary[$k]=intval($v); //获得缩放比例和裁剪位置

        if($posary[2]>0 && $posary[3]>0) $imgresize->resize($posary[2], $posary[3]); //图片缩放

        $imgresize->save('media/tmp/img0.jpg');

        $imgresize->cut(400, 186, intval($posary[0]), intval($posary[1]));
        $imgresize->save('media/tmp/img1.jpg');

    }

    public function resetAction()
    {
        $params = Mage::app()->getRequest()->getParams();
        if ($params[position] == 'P1') {
            Mage::getSingleton('core/session')->setData('step_p1', 0);
        } elseif ($params[position] == 'P2') {
            Mage::getSingleton('core/session')->setData('step_p2', 0);
        }

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
    function load($img_name, $img_type=''){
        if(!empty($img_type)) $this->_imagetype = $img_type;
        else $this->_imagetype = $this->get_type($img_name);
        switch ($this->_imagetype){
            case 'gif':
                if (function_exists('imagecreatefromgif'))	$this->_img=imagecreatefromgif($img_name);
                break;
            case 'jpg':
                $this->_img=imagecreatefromjpeg($img_name);
                break;
            case 'png':
                $this->_img=imagecreatefrompng($img_name);
                break;
            default:
                $this->_img=imagecreatefromstring($img_name);
                break;
        }
        $this->getxy();
    }

    //缩放图片
    function resize($width, $height, $percent=0)
    {
        if(!is_resource($this->_img)) return false;
        if(empty($width) && empty($height)){
            if(empty($percent)) return false;
            else{
                $width = round($this->_width * $percent);
                $height = round($this->_height * $percent);
            }
        }elseif(empty($width) && !empty($height)){
            $width = round($height * $this->_width / $this->_height );
        }else{
            $height = round($width * $this->_height / $this->_width);
        }
        $tmpimg = imagecreatetruecolor($width,$height);
        if(function_exists('imagecopyresampled')) imagecopyresampled($tmpimg, $this->_img, 0, 0, 0, 0, $width, $height, $this->_width, $this->_height);
        else imagecopyresized($tmpimg, $this->_img, 0, 0, 0, 0, $width, $height, $this->_width, $this->_height);
        $this->destroy();
        $this->_img = $tmpimg;
        $this->getxy();
    }

    //裁剪图片
    function cut($width, $height, $x=0, $y=0){
        if(!is_resource($this->_img)) return false;
        if($width > $this->_width) $width = $this->_width;
        if($height > $this->_height) $height = $this->_height;
        if($x < 0) $x = 0;
        if($y < 0) $y = 0;
        $tmpimg = imagecreatetruecolor($width,$height);
        imagecopy($tmpimg, $this->_img, 0, 0, $x, $y, $width, $height);
        $this->destroy();
        $this->_img = $tmpimg;
        $this->getxy();
    }


    //显示图片
    function display($destroy=true)
    {
        if(!is_resource($this->_img)) return false;
        switch($this->_imagetype){
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
        if($destroy) $this->destroy();
    }

    //保存图片 $destroy=true 是保存后销毁图片变量，false这不销毁，可以继续处理这图片
    function save($fname, $destroy=false, $type='')
    {
        if(!is_resource($this->_img)) return false;
        if(empty($type)) $type = $this->_imagetype;
        switch($type){
            case 'jpg':
            case 'jpeg':
                $ret=imagejpeg($this->_img, $fname);
                break;
            case 'gif':
                $ret=imagegif($this->_img, $fname);
                break;
            case 'png':
            default:
                $ret=imagepng($this->_img, $fname);
                break;
        }
        if($destroy) $this->destroy();
        return $ret;
    }

    //销毁图像
    function destroy()
    {
        if(is_resource($this->_img)) imagedestroy($this->_img);
    }

    //取得图像长宽
    function getxy()
    {
        if(is_resource($this->_img)){
            $this->_width = imagesx($this->_img);
            $this->_height = imagesy($this->_img);
        }
    }


    //获得图片的格式，包括jpg,png,gif
    function get_type($img_name)//获取图像文件类型
    {
        if (preg_match("/\.(jpg|jpeg|gif|png)$/i", $img_name, $matches)){
            $type = strtolower($matches[1]);
        }else{
            $type = "string";
        }
        return $type;
    }
}