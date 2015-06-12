<?php

//检测API路径
if(!defined('PHPQRCODE_API_PATH'))
    define('PHPQRCODE_API_PATH', dirname(__FILE__));

//加载conf.inc.php文件
include_once PHPQRCODE_API_PATH . DIRECTORY_SEPARATOR . 'phpqrcode.php';

final class phpqrcode_qrcode{

    //----------------------------------------------------------------------
    public static function CreateQRCodePNG($text, $outfile = false, $level = QR_ECLEVEL_L, $size = 3, $margin = 4, $saveandprint=false)
    {
        $enc = QRencode::factory($level, $size, $margin);
        return  $enc->encodePNG($text, $outfile, $saveandprint=false);
    }
}