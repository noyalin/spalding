<?php

class Cobra_CustomMade_Helper_ImageHandler extends Mage_Core_Helper_Abstract
{
    const SHOW_WIDTH = 705;//显示宽度
    const SHOW_HEIGHT = 186;//显示高度
    const PRINT_WIDTH = 1980;//打印宽度
    const PRINT_HEIGHT = 544;//打印高度

    //获得图片的格式
    private function imageType($img_url)
    {
        $this->_imagetype = exif_imagetype($img_url);
    }

    public function createImages($sku, $position, $img_data, $info)
    {
        $path = '/usr/custommade/tmp/';
        $watermark_show = Mage::getDesign()->getSkinUrl('images/customMade/'.$sku.'/'.$position.'_show.png');
        $watermark_print = Mage::getDesign()->getSkinUrl('images/customMade/'.$sku.'/'.$position.'_print.png');

        // 获取定制信息
        $info_array = explode(',', $info);
        $cut_x = intval($info_array[0]);
        $cut_y = intval($info_array[1]);
        $resize_w = intval($info_array[2]);
        $resize_h = intval($info_array[3]);

        // 创建原图
        $data = explode(',', $img_data);
        $data_decode = base64_decode($data[1]);
        $img_info = getimagesizefromstring($data_decode);
        if ($img_info) {
            // 原图格式
            $img_type = $img_info[2];
            // 原图尺寸
            $original_w = $img_info[0];
            $original_h = $img_info[1];

            switch ($img_type) {
                case IMAGETYPE_JPEG:
                    $img = imagecreatefromstring($data_decode);

                    // 保存原图
                    $original_name = $path . 'image1' . image_type_to_extension($img_type);
                    imagejpeg($img, $original_name);

                    // 保存效果图
                    $effect_name = $path . 'image2' . image_type_to_extension($img_type);

                    break;
                case IMAGETYPE_PNG:
                    break;
                default:
                    return false;
            }

        } else {
            return false;
        }





        //保存原图
        $original_name = $path.'image1.png';
        $this->save($img, $original_name, $original_w, $original_h, $original_w, $original_h, 0, 0, $original_w, $original_h);

        // 获取定制信息
        $info_array = explode(',', $info);
        $cut_x = intval($info_array[0]);
        $cut_y = intval($info_array[1]);
        $resize_w = intval($info_array[2]);
        $resize_h = intval($info_array[3]);

        //保存效果图
        $effect_name = $path.'image2.png';
        $this->save($img, $effect_name, $resize_w, $resize_h, $original_w, $original_h, $cut_x, $cut_y, self::SHOW_WIDTH, self::SHOW_HEIGHT);
//        $this->load($data_decode);

//        $this->save($_path.'1.jpg');
//        $this->resize(4000,2000);
//        $this->save($_path.'2.jpg');

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
    private function save($img, $name, $dst_w, $dst_h, $src_w, $src_h, $cut_x, $cut_y, $cut_w, $cut_h)
    {
//        if (!is_resource($this->_img)) return false;
//        if (empty($width) && empty($height)) {
//            if (empty($percent)) return false;
//            else {
//                $width = round($this->_width * $percent);
//                $height = round($this->_height * $percent);
//            }
//        } elseif (empty($width) && !empty($height)) {
//            $width = round($height * $this->_width / $this->_height);
//        } else {
//            $height = round($width * $this->_height / $this->_width);
//        }
//        $tmpimg = imagecreatetruecolor($width, $height);
//        if (function_exists('imagecopyresampled')) imagecopyresampled($tmpimg, $this->_img, 0, 0, 0, 0, $width, $height, $this->_width, $this->_height);
//        else imagecopyresized($tmpimg, $this->_img, 0, 0, 0, 0, $width, $height, $this->_width, $this->_height);
//        $this->destroy();
//        $this->_img = $tmpimg;
//        $this->getxy();

        $resize_img = imagecreatetruecolor($dst_w, $dst_h);
        $alpha = imagecolorallocatealpha($resize_img, 0, 0, 0, 127);
        imagefill($resize_img, 0, 0, $alpha);
        imagecopyresampled($resize_img, $img, 0, 0, 0, 0, $dst_w, $dst_h, $src_w, $src_h);
        imagesavealpha($resize_img, true);


        $cut_img = imagecreatetruecolor($cut_w,$cut_h);
        $alpha1 = imagecolorallocatealpha($cut_img, 0, 0, 0, 127);
        imagefill($cut_img, 0, 0, $alpha1);
        imagecopy($cut_img, $resize_img, 0, 0, $cut_x, $cut_y, $cut_w, $cut_h);
        imagesavealpha($cut_img, true);
        imagepng($cut_img, $name);

        imagedestroy($cut_img);
        imagedestroy($resize_img);

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