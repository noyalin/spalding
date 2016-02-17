<?php

class Cobra_CustomMade_Helper_ImageHandler extends Mage_Core_Helper_Abstract
{
    const SHOW_WIDTH = 705;// 显示宽度
    const SHOW_HEIGHT = 186;// 显示高度
    const PRINT_WIDTH = 1980;// 打印宽度
    const PRINT_HEIGHT = 544;// 打印高度
    const EXTENSION_JPG = '.jpg';
    const EXTENSION_PNG = '.png';

    public function createImages($sku, $position, $img_data, $info)
    {
        $path = Mage::getBaseDir() . '/media/custommade/tmp/';
        $url = Mage::getBaseUrl() . 'media/custommade/tmp/';
        $cover_show = Mage::getDesign()->getSkinUrl('images/customMade/' . $sku . '/' . $position . '_show.png');
        $cover_print = Mage::getDesign()->getSkinUrl('images/customMade/' . $sku . '/' . $position . '_print.png');

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
                    $date = Mage::getModel('core/date')->date('YmdHis').uniqid();
                    Mage::log('date = '.$date);
                    //$date = uniqid();
                    $img = imagecreatefromstring($data_decode);

                    // 保存原图
                    $original_name = $date . '-original' . self::EXTENSION_JPG;
                    imagejpeg($img, $path . $original_name);

                    // 保存效果图
                    $effect_name = $date . '-effect' . self::EXTENSION_JPG;
                    $resize_img = imagecreatetruecolor($resize_w, $resize_h);
                    imagecopyresampled($resize_img, $img, 0, 0, 0, 0, $resize_w, $resize_h, $original_w, $original_h);
                    $cut_img = imagecreatetruecolor(self::SHOW_WIDTH, self::SHOW_HEIGHT);
                    imagecopy($cut_img, $resize_img, 0, 0, $cut_x, $cut_y, self::SHOW_WIDTH, self::SHOW_HEIGHT);
                    imagejpeg($cut_img, $path . $effect_name);
                    imagedestroy($resize_img);
                    imagedestroy($cut_img);

                    $new_info = $this->getNewInfo($cut_x, $cut_y, $resize_w, $original_w);
                    // 保存预览图
                    $show_name = $date . '-show' . self::EXTENSION_JPG;
                    $this->addCoverJPG($img, $cover_show, $path . $show_name, $new_info);
                    // 保存打印图
                    $print_name = $date . '-print' . self::EXTENSION_JPG;
                    $this->addCoverJPG($img, $cover_print, $path . $print_name, $new_info);

                    imagedestroy($img);
                    return array("effect" => $url . $effect_name, "show" => $url . $show_name, "print" => $url . $print_name);
                case IMAGETYPE_PNG:
                    $date = Mage::getModel('core/date')->date('YmdHis').uniqid();
                    $img = imagecreatefromstring($data_decode);

                    // 保存原图
                    $original_name = $date . '-original' . self::EXTENSION_PNG;
                    $this->setAlpha($img);
                    imagepng($img, $path . $original_name);

                    // 保存效果图
                    $effect_name = $date . '-effect' . self::EXTENSION_PNG;
                    $resize_img = imagecreatetruecolor($resize_w, $resize_h);
                    $this->setAlpha($resize_img);
                    imagecopyresampled($resize_img, $img, 0, 0, 0, 0, $resize_w, $resize_h, $original_w, $original_h);

                    $cut_img = imagecreatetruecolor(self::SHOW_WIDTH, self::SHOW_HEIGHT);
                    $this->setAlpha($cut_img);
                    imagecopy($cut_img, $resize_img, 0, 0, $cut_x, $cut_y, self::SHOW_WIDTH, self::SHOW_HEIGHT);
                    imagepng($cut_img, $path . $effect_name);
                    imagedestroy($resize_img);
                    imagedestroy($cut_img);

                    $new_info = $this->getNewInfo($cut_x, $cut_y, $resize_w, $original_w);
                    // 保存预览图
                    $show_name = $date . '-show' . self::EXTENSION_PNG;
                    $this->addCoverPNG($img, $cover_show, $path . $show_name, $new_info);
                    // 保存打印图
                    $print_name = $date . '-print' . self::EXTENSION_PNG;
                    $this->addCoverPNG($img, $cover_print, $path . $print_name, $new_info);

                    imagedestroy($img);
                    return array("effect" => $url . $effect_name, "show" => $url . $show_name, "print" => $url . $print_name);
                default:
                    return false;
            }
        } else {
            return false;
        }
    }

    private function addCoverJPG($img, $url, $name, $new_info)
    {
        $new_cut_img = imagecreatetruecolor($new_info["new_cut_w"], $new_info["new_cut_h"]);
        imagecopy($new_cut_img, $img, 0, 0, $new_info["new_cut_x"], $new_info["new_cut_y"], $new_info["new_cut_w"], $new_info["new_cut_h"]);
        $new_resize_img = imagecreatetruecolor(self::PRINT_WIDTH, self::PRINT_HEIGHT);
        imagecopyresampled($new_resize_img, $new_cut_img, 0, 0, 0, 0, self::PRINT_WIDTH, self::PRINT_HEIGHT, $new_info["new_cut_w"], $new_info["new_cut_h"]);
        $cover_img = imagecreatefrompng($url);
        imagecopy($new_resize_img, $cover_img, 0, 0, 0, 0, self::PRINT_WIDTH, self::PRINT_HEIGHT);
        imagejpeg($new_resize_img, $name);
        imagedestroy($new_cut_img);
        imagedestroy($cover_img);
        imagedestroy($new_resize_img);
    }

    private function addCoverPNG($img, $url, $name, $new_info)
    {
        $new_cut_img = imagecreatetruecolor($new_info["new_cut_w"], $new_info["new_cut_h"]);
        $this->setAlpha($new_cut_img);
        imagecopy($new_cut_img, $img, 0, 0, $new_info["new_cut_x"], $new_info["new_cut_y"], $new_info["new_cut_w"], $new_info["new_cut_h"]);
        $new_resize_img = imagecreatetruecolor(self::PRINT_WIDTH, self::PRINT_HEIGHT);
        $this->setAlpha($new_resize_img);
        imagecopyresampled($new_resize_img, $new_cut_img, 0, 0, 0, 0, self::PRINT_WIDTH, self::PRINT_HEIGHT, $new_info["new_cut_w"], $new_info["new_cut_h"]);
        $cover_img = imagecreatefrompng($url);
        imagecopy($new_resize_img, $cover_img, 0, 0, 0, 0, self::PRINT_WIDTH, self::PRINT_HEIGHT);
        imagepng($new_resize_img, $name);
        imagedestroy($new_cut_img);
        imagedestroy($cover_img);
        imagedestroy($new_resize_img);
    }

    private function getNewInfo($cut_x, $cut_y, $resize_w, $original_w)
    {
        $new_cut_x = round($cut_x * $original_w / $resize_w);
        $new_cut_y = round($cut_y * $original_w / $resize_w);
        $new_cut_w = round(self::SHOW_WIDTH * $original_w / $resize_w);
        $new_cut_h = round($new_cut_w * self::PRINT_HEIGHT / self::PRINT_WIDTH);
        return array(
            "new_cut_x" => $new_cut_x,
            "new_cut_y" => $new_cut_y,
            "new_cut_w" => $new_cut_w,
            "new_cut_h" => $new_cut_h,
        );
    }

    private function setAlpha($img)
    {
        $effect_cut_alpha = imagecolorallocatealpha($img, 0, 0, 0, 127);
        imagefill($img, 0, 0, $effect_cut_alpha);
        imagesavealpha($img, true);
    }
}