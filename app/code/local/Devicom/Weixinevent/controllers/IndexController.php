<?php
class Devicom_Weixinevent_IndexController extends Mage_Core_Controller_Front_Action{
    public function indexAction(){
        mage :: log($this->_request->getParams());

        $appid = 'wx79873079dca36474';
        $appsecret = 'ba74acc7f680e7bbe62203815df1df41';
        $redirectUrl = urlencode ("http://www.m.spaldingchina.com.cn/auth2.php");
        $url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=$appid&redirect_uri=$redirectUrl&response_type=code&scope=snsapi_base&state=spaldingchina#wechat_redirect";
        $code = trim($_GET["code"]);
        $state = trim($_GET['state']);
        if ($code && $state == 'spaldingchina') {
            $openid_url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=$appid&secret=$appsecret&code=$code&grant_type=authorization_code";
            $openid_data = $this->httpdata($openid_url);
//    var_dump($openid_data);
            $openid_obj = json_decode($openid_data);
            $openId = $openid_obj->openid;
//    $accessToken = $openid_obj->access_token;

            $token_url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$appid&secret=$appsecret";
            $token_data = $this->httpdata($token_url);
//    var_dump($token_data);
            $token_obj = json_decode($token_data);
            $accessToken = $token_obj->access_token;

            $userUrl = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=$accessToken&openid=$openId&lang=zh_CN";
            $useinfo =  $this->httpdata($userUrl);
            var_dump($useinfo);
        }else{
            header("Location: $url");
        }


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