<?php

/**
 * 定义程序绝对路径
 */
define('EMAY_SMS_ROOT',  dirname(__FILE__).'/');
require_once EMAY_SMS_ROOT . 'include/Client.php';


/**
 * 网关地址
 */	
 
 
final class EMAY_SMS {
    static private $gwUrl = 'http://sdk4report.eucp.b2m.cn:8080/sdk/SDKService';

    /**
     * 序列号,请通过亿美销售人员获取
     */
    static private $serialNumber = '6SDK-EMY-6688-JCTUN';

    /**
     * 密码,请通过亿美销售人员获取
     */
    static private $password = '216500';

    /**
     * 登录后所持有的SESSION KEY，即可通过login方法时创建
     */
    static private $sessionKey = '216500';

    /**
     * 连接超时时间，单位为秒
     */
    static private $connectTimeOut = 2;

    /**
     * 远程信息读取超时时间，单位为秒
     */
    static private $readTimeOut = 10;

/**
	$proxyhost		可选，代理服务器地址，默认为 false ,则不使用代理服务器
	$proxyport		可选，代理服务器端口，默认为 false
	$proxyusername	可选，代理服务器用户名，默认为 false
	$proxypassword	可选，代理服务器密码，默认为 false
*/
    static private $proxyhost = false;
    static private $proxyport = false;
    static private $proxyusername = false;
    static private $proxypassword = false;

    static private $client;
    /**
     * 发送向服务端的编码，如果本页面的编码为GBK，请使用GBK
     */

    /**
     * 接口调用错误查看 用例
     */

    public static function initialize()
    {
        self::$client = new Client(self::$gwUrl, self::$serialNumber, self::$password, self::$sessionKey, self::$proxyhost, self::$proxyport, self::$proxyusername, self::$proxypassword, self::$connectTimeOut, self::$readTimeOut);
        self::$client->setOutgoingEncoding("UTF-8");
    }

    public static function chkError()
    {
        $err = self::$client->getError();
        if ($err)
        {
            /**
             * 调用出错，可能是网络原因，接口版本原因 等非业务上错误的问题导致的错误
             * 可在每个方法调用后查看，用于开发人员调试
             */

            echo $err;
        }

    }

    /**
     * 短信发送 用例
     * @param $telephone
     * @param $signature
     * @param $captcha
     */
    public static function sendSMS($telephone, $signature, $captcha)
    {
        self::getBalance();
        /**
         * 下面的代码将发送内容为 test 给 159xxxxxxxx 和 159xxxxxxxx
         * $client->sendSMS还有更多可用参数，请参考 Client.php
         */
        $statusCode = self::$client->sendSMS(array($telephone),$signature.'您的验证码是：'.$captcha);
        mage::log("发送号码：$telephone -> ".$signature.'您的验证码是：'.$captcha);
        mage::log("处理状态码:" . $statusCode);
        self::getBalance();
        if ($statusCode != null && $statusCode == 0) {
            return true;
        } else {
            mage::log(self::chkError());
            return false;
        }
        Mage::log($telephone.$signature.$captcha, Zend_Log::DEBUG);
    }

    public static function sendPromotionSMS($telephone, $signature, $content)
    {
        self::getBalance();
        /**
         * 下面的代码将发送内容为 test 给 159xxxxxxxx 和 159xxxxxxxx
         * $client->sendSMS还有更多可用参数，请参考 Client.php
         */
        $statusCode = self::$client->sendSMS(array($telephone),$signature.$content);
        mage::log("发送号码：$telephone -> ".$signature.$content);
        mage::log("处理状态码:" . $statusCode);
        self::getBalance();
        if ($statusCode != null && $statusCode == 0) {
            return true;
        } else {
            mage::log(self::chkError());
            return false;
        }

        Mage::log($telephone.$signature.$content, Zend_Log::DEBUG);

    }

    /**
     * 余额查询 用例
     */
    public static function getBalance()
    {
        $balance = self::$client->getBalance();
        mage::log("余额:".$balance);
    }

}

EMAY_SMS::initialize();

?>
