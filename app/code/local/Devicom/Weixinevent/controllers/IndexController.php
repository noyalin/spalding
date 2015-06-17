<?php
//include  Mage :: getBaseDir().'/lib/phpqrcode/phpqrcode.php';

define('SIGNATURE',   '【Spalding】');

class Devicom_Weixinevent_IndexController extends Mage_Core_Controller_Front_Action{

    public function sendCaptchaAction(){
        mage::log("Devicom_Weixinevent_IndexController sendCaptchaAction",
            Zend_Log::DEBUG);
        $openId = Mage::getSingleton('customer/session')->getOpenId();
        $actId = Mage::getSingleton('customer/session')->getActId();
        $telephone =  Mage::app()->getRequest()->getParam('telephone');
        $signature = SIGNATURE;
        $captcha = SMS_Check::getTelephoneCode($openId, $actId, $telephone);
        Mage::log("验证码：".$captcha);
        //EMAY_SMS::sendSMS($telephone,$signature,$captcha);

        echo $captcha;
    }

    public function checkCaptchaAction()
    {
        mage::log("Devicom_Weixinevent_IndexController checkCaptchaAction",
            Zend_Log::DEBUG);
        $inputCaptcha = (int)Mage::app()->getRequest()->getParam('inputCaptcha');
        $telephone = Mage::app()->getRequest()->getParam('telephone');
        $clickOrder = Mage::app()->getRequest()->getParam('clickOrder');
        $openId = Mage::getSingleton('customer/session')->getOpenId();
        $actId = Mage::getSingleton('customer/session')->getActId();
        $orderId = Mage::getSingleton('customer/session')->getOrderId();

        if (SMS_Check::checkTelephoneCode($openId, $actId, $telephone, $inputCaptcha)) {
            $result = Mage::getSingleton('weixinevent/promotion')->setCaptchaData($telephone);
            if ($result) {
                $promotion_opt = Mage::getSingleton('customer/session')->getPromotionStatus($orderId, $actId);
                if (count($promotion_opt) < 4) {
                    Mage::getSingleton('weixinevent/promotion')->setPromotionData(5, $clickOrder);
                    //EMAY_SMS::sendPromotionSMS
//                    $result1 = Mage::getSingleton('weixinevent/promotion')->updateCoupon("12","1");
                    echo "Success";
                } else if (count($promotion_opt) == 4) {
                    Mage::getSingleton('weixinevent/promotion')->setPromotionData(5, $clickOrder);
                    //EMAY_SMS::sendPromotionSMS
                    echo "Success";
                } else {
                    echo "球已被点完";
                }
            } else {
                echo "已参加过活动";
            }

        } else {
            echo "Fail";
        }
    }

    public function updatePromotionDataAction()
    {
        Mage::log("updatePromotionDataAction start",
            Zend_Log::DEBUG);
        $actId = Mage::getSingleton('customer/session')->getActId();
        $orderId = Mage::getSingleton('customer/session')->getOrderId();
        Mage::getSingleton('weixinevent/promotion')->updatePromotionData($actId, $orderId);

        Mage::log("updatePromotionDataAction end",
            Zend_Log::DEBUG);
    }

    public function indexAction(){
        mage::log("Devicom_Weixinevent_IndexController indexAction",
            Zend_Log::DEBUG);

        $params = $this->getRequest()->getParams();
        Mage::getSingleton('customer/session')->setOrderId($params['oid']);
        Mage::getSingleton('customer/session')->setActId($params['aid']);

//        $appid = 'wx79873079dca36474';
//        $appsecret = 'ba74acc7f680e7bbe62203815df1df41';
//        $redirectUrl = urlencode(Mage::helper('core/url')->getCurrentUrl());
//        $url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=$appid&redirect_uri=$redirectUrl&response_type=code&scope=snsapi_base&state=spaldingchina#wechat_redirect";
//        $code =  Mage::app()->getRequest()->getParam('code');
//        $state = Mage::app()->getRequest()->getParam('state');
//
//        if ($code && $state == 'spaldingchina') {//
//            $openid_url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=$appid&secret=$appsecret&code=$code&grant_type=authorization_code";
//            $openid_data = $this->httpdata($openid_url);
//            Mage::log("openid_data=".$openid_data);
////    var_dump($openid_data);
//            $openid_obj = json_decode($openid_data);
//            $openId = $openid_obj->openid;
//            Mage::getSingleton('customer/session')->setOpenId($openId);
        Mage::getSingleton('customer/session')->setOpenId("666666");
        if (Mage::getSingleton('weixinevent/promotion')->checkSponsor()) {
            Mage::getSingleton('weixinevent/promotion')->setPromotionData(0, null);
        }
//    $accessToken = $openid_obj->access_token;
//
//            $token_url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$appid&secret=$appsecret";
//            $token_data = $this->httpdata($token_url);
//            mage::log("token_data=".$token_data);
////    var_dump($token_data);
//            $token_obj = json_decode($token_data);
//            $accessToken = $token_obj->access_token;
//
//            $userUrl = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=$accessToken&openid=$openId&lang=zh_CN";
//            $useinfo =  $this->httpdata($userUrl);
//            mage::log("useinfo=".$useinfo);
////            var_dump($useinfo);
//
//        }else{
//            mage::log($url);
//            $this->_redirectUrl("$url");
//        }



        $this->loadLayout();
        $this->renderLayout();

    }

    function httpdata($url, $method="get", $postfields = null, $headers = array(), $debug = false)
    {
        $ci = curl_init();
        /* Curl settings */
        curl_setopt($ci, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ci, CURLOPT_TIMEOUT, 30);
        curl_setopt($ci, CURLOPT_RETURNTRANSFER, true);

        switch ($method) {
            case 'POST':
                curl_setopt($ci, CURLOPT_POST, true);
                if (!empty($postfields)) {
                    curl_setopt($ci, CURLOPT_POSTFIELDS, $postfields);
                    $this->postdata = $postfields;
                }
                break;
        }
        curl_setopt($ci, CURLOPT_URL, $url);
        curl_setopt($ci, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ci, CURLINFO_HEADER_OUT, true);

        $response = curl_exec($ci);
        $http_code = curl_getinfo($ci, CURLINFO_HTTP_CODE);

        if ($debug) {
            echo "=====post data======\r\n";
            var_dump($postfields);

            echo '=====info=====' . "\r\n";
            print_r(curl_getinfo($ci));

            echo '=====$response=====' . "\r\n";
            print_r($response);
        }
        curl_close($ci);
        return $response;
    }

    public function qrcodeAction(){

        $params = $this->getRequest()->getParams();

        $baseUrl = 'http://www.m.spaldingchina.com.cn/weixinevent/index/index/';

        foreach ($params as $k=>$v) {
            if ($k == null || $v == null) {
                continue;
            }
            $baseUrl = $baseUrl.$k.'/'.$v.'/';
        }

        Mage::log('baseUrl = '.$baseUrl,
            Zend_Log::DEBUG);

        phpqrcode_qrcode::CreateQRCodePNG($baseUrl, false, 'L', 6, 2);

    }

}