<?php
/**
* 	配置账号信息
*/

class Infinitech_Weixinpay_Model_Wxpaypubconfig extends Mage_Core_Model_Abstract
{
	const JS_API_CALL_URL = '';

	//=======【curl超时设置】===================================
	//本例程通过curl使用HTTP POST方法，此处可修改其超时时间，默认为30秒
	const CURL_TIMEOUT = 30;

    public static $path = 'payment/weixinpay/';

    public static function getCode($field){
        $path = self::$path.$field;
        return Mage::getStoreConfig($path);
    }
}
	
?>