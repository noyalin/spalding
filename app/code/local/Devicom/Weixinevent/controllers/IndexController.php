    <?php
    //include_once("app/comm/emay/EmaySMS.php");
    //include_once("app/comm/yuntongxun/YunTongXunSMS.php");

class Devicom_Weixinevent_IndexController extends Mage_Core_Controller_Front_Action{

    public function sendCaptchaAction(){
        mage::log("Devicom_Weixinevent_IndexController sendCaptchaAction");
        $uid =  Mage::app()->getRequest()->getParam('uid');
        $actId =  Mage::app()->getRequest()->getParam('actId');
        $telephone =  Mage::app()->getRequest()->getParam('telephone');
        $signature = '【Sneakerhead】';
        $captcha = SMS_Check::getTelephoneCode($uid, $actId, $telephone);
//        EMAY_SMS::sendSMS($telephone,$signature,$captcha);
        echo $captcha;
    }

    public function checkCaptchaAction(){
        mage::log("Devicom_Weixinevent_IndexController checkCaptchaAction");
        $inputCaptcha = (int)Mage::app()->getRequest()->getParam('inputCaptcha');
        $uid =  Mage::app()->getRequest()->getParam('uid');
        $actId =  Mage::app()->getRequest()->getParam('actId');
        $telephone =  Mage::app()->getRequest()->getParam('telephone');

        mage::log($inputCaptcha."  ".$uid."  ".$actId."  ".$telephone);
        if (SMS_Check::checkTelephoneCode($uid,$actId,$telephone,$inputCaptcha)) {
            echo "Success";
        } else {
            echo "Fail";
        }
    }
    public function indexAction(){
        mage::log("Devicom_Weixinevent_IndexController indexAction");

        //TelephoneCheck::
        // 亿美短信发送
//        EMAY_SMS::sendSMS();

//        //Demo调用
//        //**************************************举例说明***********************************************************************
//        //*假设您用测试Demo的APP ID，则需使用默认模板ID 1，发送手机号是13800000000，传入参数为6532和5，则调用方式为           *
//        //*result = sendTemplateSMS("13800000000" ,array('6532','5'),"1");																		  *
//        //*则13800000000手机号收到的短信内容是：【云通讯】您使用的是云通讯短信模板，您的验证码是6532，请于5分钟内正确输入     *
//        //*********************************************************************************************************************
//        YunTongXunSMS::send("13651758225",array('4567','3'),"1");//手机号码，替换内容数组，模板ID

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
//            mage::log("openid_data=".$openid_data);
////    var_dump($openid_data);
//            $openid_obj = json_decode($openid_data);
//            $openId = $openid_obj->openid;
////    $accessToken = $openid_obj->access_token;
//
////            $token_url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$appid&secret=$appsecret";
////            $token_data = $this->httpdata($token_url);
////            mage::log("token_data=".$token_data);
//////    var_dump($token_data);
////            $token_obj = json_decode($token_data);
////            $accessToken = $token_obj->access_token;
////
////            $userUrl = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=$accessToken&openid=$openId&lang=zh_CN";
////            $useinfo =  $this->httpdata($userUrl);
////            mage::log("useinfo=".$useinfo);
//////            var_dump($useinfo);
//
//
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

}