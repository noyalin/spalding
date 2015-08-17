<?php
//include  Mage :: getBaseDir().'/lib/phpqrcode/phpqrcode.php';

define('WEIXIN_PROMOTION_SIGNATURE',   '【Spalding】');
if (!defined('WEIXIN_PROMOTION_ACTIVITY_ID')) {
    define('WEIXIN_PROMOTION_ACTIVITY_ID',   '10000001');
}

class Devicom_Weixinevent_IndexController extends Mage_Core_Controller_Front_Action{

    public function sendCaptchaAction(){
        $result = array ();
        $opt = Mage::getSingleton('weixinevent/promotion');

        try {
            mage::log("Devicom_Weixinevent_IndexController sendCaptchaAction Start ---- ",
                Zend_Log::DEBUG);
            $openId = Mage::getSingleton('customer/session')->getOpenId();
            $actId = Mage::getSingleton('customer/session')->getActId();
            $telephone = Mage::app()->getRequest()->getParam('telephone');
            $signature = WEIXIN_PROMOTION_SIGNATURE;
            $captcha = SMS_Check::getTelephoneCode($openId, $actId, $telephone);
            if (!$captcha) {
                throw new Exception("SMS_Check::getTelephoneCode == false: openId=$openId, actId=$actId, telephone=$telephone");
            }
            EMAY_SMS::sendSMS($telephone, $signature, $captcha);
            $result["status"] = "Success";
            $result["data"] = $captcha;
            $result["lightedCnt"] = $opt->getPromotionCount();
        } catch (Exception $ex) {
            mage::log("Exception : " . $ex->getMessage(),
                Zend_Log::ERR);
            $result["status"] = "Fail";
            $result["message"] = "发送验证码失败。";
            $result["lightedCnt"] = $opt->getPromotionCount();
        }

        $resultString = json_encode ( $result );
        mage::log("Devicom_Weixinevent_IndexController sendCaptchaAction echo -> $resultString",
            Zend_Log::DEBUG);
        echo $resultString;

    }

    private function checkPara() {

        $openId = Mage::getSingleton('customer/session')->getOpenId();
        $actId = Mage::getSingleton('customer/session')->getActId();
        $orderId = Mage::getSingleton('customer/session')->getOrderId();

        if (!isset($openId) || empty($openId)) {
            mage::log("checkPara : openId异常",
                Zend_Log::ERR);
            return false;
        }

        if (!isset($actId) || $actId != WEIXIN_PROMOTION_ACTIVITY_ID) {
            mage::log("checkPara : actId异常",
                Zend_Log::ERR);
            return false;
        }

        if (!isset($orderId) || empty($orderId)) {
            mage::log("checkPara : orderId异常",
                Zend_Log::ERR);
            return false;
        }

        if (!Mage::getSingleton('weixinevent/promotion')->isPromotionOrderId2($orderId)) {
            return false;
        }
        return true;
    }

    public function checkCaptchaAction()
    {
        $opt = Mage::getSingleton('weixinevent/promotion');

        mage::log("checkCaptchaAction ",
            Zend_Log::DEBUG);

        mage::log(Mage::app()->getRequest(),
            Zend_Log::DEBUG);

        $result = array();
        if ($this->checkPara()) {//
            try {
                mage::log("Devicom_Weixinevent_IndexController checkCaptchaAction Start ---- ",
                    Zend_Log::DEBUG);
//            $inputCaptcha = (int)Mage::app()->getRequest()->getParam('inputCaptcha');
                $telephone = Mage::app()->getRequest()->getParam('telephone');
                $clickOrder = Mage::app()->getRequest()->getParam('clickOrder');
                $openId = Mage::getSingleton('customer/session')->getOpenId();
                $actId = Mage::getSingleton('customer/session')->getActId();
                $orderId = Mage::getSingleton('customer/session')->getOrderId();

                $result = array();

//            if (SMS_Check::checkTelephoneCode($openId, $actId, $telephone, $inputCaptcha)) {
                $opt->setCaptchaData($telephone);
                $isPromotioned = $opt->isPromotioned();
                $promotion_opt = $opt->getPromotionCount();
                $flag = true;
                if ($isPromotioned == 0) {
                    if ($promotion_opt >= 5) {
                        $result["status"] = "Finished";
                        $result["message"] = "被人抢先了一步，篮球已全部点亮";
                        $result["data"] = 5;
                    } else {
                        $opt->setPromotionData(5, $telephone, $clickOrder);
                        $promotion_opt++;
                        $record = $opt->updateCoupon($openId, "m100j10");
                        if ($record != -1) {
                            EMAY_SMS::sendPromotionSMS($telephone, WEIXIN_PROMOTION_SIGNATURE, "恭喜你获得10元优惠券（满100元可使用）,优惠券号码：" . $record["code"] . "，您可在官网购物结算时使用该券，有效期至：" . substr($record["end_time"], 0, 4)
                                . "年" . substr($record["end_time"], 4, 2) . "月" . substr($record["end_time"], 6, 2) . "日");
                        } else {
                            $flag = false;
                        }
                        if ($promotion_opt >= 5) {
                            $record2 = $opt->updateCoupon($openId, "lj10");
                            $sponsorTel = $opt->getSponsorTel($orderId);
                            if ($record2 != -1) {
                                EMAY_SMS::sendPromotionSMS($sponsorTel, WEIXIN_PROMOTION_SIGNATURE, "恭喜你获得10元优惠券（无使用门槛）,优惠券号码：" . $record2["code"] . "，您可在官网购物结算时使用该券，有效期至：" . substr($record2["end_time"], 0, 4)
                                    . "年" . substr($record2["end_time"], 4, 2) . "月" . substr($record2["end_time"], 6, 2) . "日");
                            }
                        }
                        if ($flag) {
                            $result["status"] = "Success";
                        } else {
                            $result["status"] = "End";
                            $result["message"] = "优惠券已发完";
                        }
                        $result["data"] = $promotion_opt;
                        $result["lightedCnt"] = $promotion_opt;
                    }
                } else {
                    $result["status"] = "Joined";
                    $result["message"] = "您已参加过活动，不能再次点亮篮球";
                    $result["lightedCnt"] = $promotion_opt;
                }
//            } else {
//                throw new Exception("SMS_Check::checkTelephoneCode == false: openId=$openId, actId=$actId, telephone=$telephone, inputCaptcha=$inputCaptcha");
//            }
            } catch (Exception $ex) {
                mage::log("Exception : " . $ex->getMessage(),
                    Zend_Log::ERR);
                $result["status"] = "Fail";
                $result["message"] = "验证码不正确";
                $result["lightedCnt"] = $opt->getPromotionCount();
            }
        } else {
                $result["status"] = "Fail";
                $result["message"] = "非法操作。";
                $result["lightedCnt"] = $opt->getPromotionCount();
        }

        $resultString = json_encode($result);
        mage::log("Devicom_Weixinevent_IndexController checkCaptchaAction echo -> $resultString",
            Zend_Log::DEBUG);
        echo $resultString;
    }

    public function updatePromotionDataAction()
    {
        $result = array ();
        $opt = Mage::getSingleton('weixinevent/promotion');
        try {
            Mage::log("updatePromotionDataAction start",
                Zend_Log::DEBUG);
            $actId = Mage::getSingleton('customer/session')->getActId();
            $orderId = Mage::getSingleton('customer/session')->getOrderId();
            Mage::getSingleton('weixinevent/promotion')->updatePromotionData($actId, $orderId);
            $result["status"] = "Success";
            $result["lightedCnt"] = $opt->getPromotionCount();

        } catch (Exception $ex) {
            mage::log("Exception : ".$ex->getMessage(),
            Zend_Log::ERR);
            $result["status"] = "Fail";
            $result["message"] = "更新数据失败。";
            $result["lightedCnt"] = $opt->getPromotionCount();
        }

        $resultString = json_encode ( $result );
        mage::log("Devicom_Weixinevent_IndexController updatePromotionDataAction echo -> $resultString",
            Zend_Log::DEBUG);
        echo $resultString;

    }

    public function indexAction(){

        try {

            mage::log("Devicom_Weixinevent_IndexController indexAction",
                Zend_Log::DEBUG);

            $params = $this->getRequest()->getParams();
            $oid = $params['oid'];
            $aid = $params['aid'];
            $cc = (int)$params['cc'];
            Mage::getSingleton('customer/session')->setOrderId($oid);
            Mage::getSingleton('customer/session')->setActId($aid);

            $strParams = '';
            foreach ($params as $k=>$v) {
                if ($k == null || $v == null) {
                    continue;
                }
                $strParams = $strParams.$k.'='.$v.' ;';
            }

            mage::log('params : '.$strParams,
                Zend_Log::DEBUG);

            if ($aid != WEIXIN_PROMOTION_ACTIVITY_ID) {
                throw new Exception('活动ID不正确。');
            }

            $oidCC = Mage::getSingleton('weixinevent/promotion')->getCheckCode($oid);
            if ($oidCC != $cc) {
                throw new Exception('活动验证码不正确。('.$oidCC.' != '.$oid.')');
            }

            if (!Mage::getSingleton('weixinevent/promotion')->isPromotionOrderId($oid)) {
                throw new Exception('订单不正确。');
            }

           $apidata = Mage::getSingleton('weixinevent/promotion')->getApidata();
            $appid = $apidata['appid'];
            $appsecret = $apidata['appsecret'];
            $redirectUrl = urlencode(Mage::helper('core/url')->getCurrentUrl());
            $url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=$appid&redirect_uri=$redirectUrl&response_type=code&scope=snsapi_base&state=spaldingchina#wechat_redirect";
            $code =  Mage::app()->getRequest()->getParam('code');
            $state = Mage::app()->getRequest()->getParam('state');

            if ($code && $state == 'spaldingchina') {//
                $openid_url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=$appid&secret=$appsecret&code=$code&grant_type=authorization_code";
                $openid_data = $this->httpdata($openid_url);
                Mage::log("openid_data=".$openid_data);
                $openid_obj = json_decode($openid_data);
                $openId = $openid_obj->openid;
                Mage::getSingleton('customer/session')->setOpenId($openId);
                if (Mage::getSingleton('weixinevent/promotion')->hasSponsor() == 0) {
                    Mage::getSingleton('weixinevent/promotion')->setPromotionData(0, null, null);
                }

//                // 微信用户信息取得
//                $token_url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$appid&secret=$appsecret";
//                $token_data = $this->httpdata($token_url);
//                mage::log("token_data=".$token_data);
//                $token_obj = json_decode($token_data);
//                $accessToken = $token_obj->access_token;
//                $userUrl = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=$accessToken&openid=$openId&lang=zh_CN";
//                $useinfo =  $this->httpdata($userUrl);
//                mage::log("useinfo=".$useinfo);

                $this->loadLayout();
                $this->renderLayout();
                mage::log("Devicom_Weixinevent_IndexController indexAction End ---- ",
                    Zend_Log::DEBUG);
            }else{
                $this->_redirectUrl("$url");
                mage::log("_redirectUrl : $url",
                    Zend_Log::DEBUG);
            }

            // Test
//            Mage::getSingleton('customer/session')->setOpenId("666666");
//            if (Mage::getSingleton('weixinevent/promotion')->hasSponsor() == 0) {
//                Mage::getSingleton('weixinevent/promotion')->setPromotionData(0, null, null);
//            }
//            $this->loadLayout();
//            $this->renderLayout();
        } catch (Exception $ex) {
            mage::log("Exception : ".$ex->getMessage(),
                Zend_Log::ERR);
            $this->_redirect('cms/index/noRoute');
        }
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

        $baseUrl = 'http://www.m.spaldingchina.com.cn/weixinevent/index/index';

        foreach ($params as $k=>$v) {
            if ($k == null || $v == null) {
                continue;
            }
            $baseUrl = $baseUrl.'/'.$k.'/'.$v;
        }

        Mage::log('baseUrl = '.$baseUrl,
            Zend_Log::DEBUG);

        phpqrcode_qrcode::CreateQRCodePNG($baseUrl, false, 'L', 4, 2);

    }

}