<?php
include '/lib/phpqrcode/phpqrcode.php';

class Devicom_Weixinevent_IndexController extends Mage_Core_Controller_Front_Action{

    public function sendCaptchaAction(){
        mage::log("Devicom_Weixinevent_IndexController sendCaptchaAction");
        $openId = Mage::getSingleton('customer/session')->getOpenId();
        $actId = Mage::getSingleton('customer/session')->getActId();
        $telephone =  Mage::app()->getRequest()->getParam('telephone');
        $signature = '【Sneakerhead】';
        $captcha = SMS_Check::getTelephoneCode($openId, $actId, $telephone);
//        EMAY_SMS::sendSMS($telephone,$signature,$captcha);

        echo $captcha;
    }

    public function checkCaptchaAction(){
        mage::log("Devicom_Weixinevent_IndexController checkCaptchaAction");
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
                if(count($promotion_opt)<4){
                    Mage::getSingleton('weixinevent/promotion')->setPromotionData(5,$clickOrder);

                    echo "Success";
                }else if(count($promotion_opt)==4){
                    Mage::getSingleton('weixinevent/promotion')->setPromotionData(5,$clickOrder);

                    echo "Success";
                }else{
                    echo "Fail";
                }
            }else{
                echo "Fail";
            }

        } else {
            echo "Fail";
        }
    }
    public function indexAction(){
        mage::log("Devicom_Weixinevent_IndexController indexAction");

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
        Mage::getSingleton('customer/session')->setOpenId("88888");

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

        QRcode::png('http://mingzi.111cn.net');

    }

}