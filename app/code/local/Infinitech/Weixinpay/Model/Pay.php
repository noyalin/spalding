<?php
class Infinitech_Weixinpay_Model_Pay extends Mage_Payment_Model_Method_Abstract
{
    protected $_code = 'weixinpay';
    protected $_formBlockType = 'weixinpay/pay';

    protected $_bank  = '';

    // Alipay return codes of payment
    const RETURN_CODE_ACCEPTED      = 'Success';
    const RETURN_CODE_TEST_ACCEPTED = 'Success';
    const RETURN_CODE_ERROR         = 'Fail';

    // Payment configuration
    protected $_isGateway               = false;
    protected $_canAuthorize            = true;
    protected $_canCapture              = true;
    protected $_canCapturePartial       = false;
    protected $_canRefund               = false;
    protected $_canVoid                 = false;
    protected $_canUseInternal          = false;
    protected $_canUseCheckout          = true;
    protected $_canUseForMultishipping  = false;

    // Order instance
    protected $_order = null;

    /**
     *  Returns Target URL
     *
     *  @return	  string Target URL
     */
    public function logTrans($trans,$type){
        $log = Mage::getModel('weixinpay/log');
        $log->setLogAt(time());
        $log->setOrderId($trans['out_trade_no']);
        $log->setTradeNo(null);
        $log->setType($type);
        $log->setPostData(implode('|',$trans));
        $log->save();

    }
    public function setBank($bank)
    {
        $this->_bank =$bank;
    }
    public function getBank()
    {
        return $this->_bank;
    }



    /**
     *  Return URL for Alipay success response
     *
     *  @return	  string URL
     */
    protected function getSuccessURL()
    {
        return Mage::getUrl('checkout/onepage/success', array('_secure' => true));
    }




    /**
     * Capture payment
     *
     * @param   Varien_Object $orderPayment
     * @return  Mage_Payment_Model_Abstract
     */
    public function capture(Varien_Object $payment, $amount)
    {
        $payment->setStatus(self::STATUS_APPROVED)
            ->setLastTransId($this->getTransactionId());

        return $this;
    }



    /**
     *  Return Order Place Redirect URL
     *
     *  @return	  string Order Redirect URL
     */
    public function getOrderPlaceRedirectUrl()
    {
        return Mage::getUrl('weixinpay/payment/pay');
    }



    public function sign($prestr) {
        $mysign = md5($prestr);
        return $mysign;
    }

    public function para_filter($parameter) {
        $para = array();
        while (list ($key, $val) = each ($parameter)) {
            if($key == "sign" || $key == "sign_type" || $val == "")continue;
            else	$para[$key] = $parameter[$key];

        }
        return $para;
    }

    public function arg_sort($array) {
        ksort($array);
        reset($array);
        return $array;
    }

    public function charset_encode($input,$_output_charset ,$_input_charset ="GBK" ) {
        $output = "";
        if($_input_charset == $_output_charset || $input ==null) {
            $output = $input;
        } elseif (function_exists("mb_convert_encoding")){
            $output = mb_convert_encoding($input,$_output_charset,$_input_charset);
        } elseif(function_exists("iconv")) {
            $output = iconv($_input_charset,$_output_charset,$input);
        } else die("sorry, you have no libs support for charset change.");
        return $output;
    }

    /**
     * Return language code to send to Alipay
     *
     * @param	none
     * @return	String
     */
    protected function _getLanguageCode()
    {
        // Store language
        $language = strtoupper(substr(Mage::getStoreConfig('general/locale/code'), 0, 2));

        // Authorized Languages
        $authorized_languages = $this->_getAuthorizedLanguages();

        if (count($authorized_languages) === 1)
        {
            $codes = array_keys($authorized_languages);
            return $codes[0];
        }

        if (array_key_exists($language, $authorized_languages))
        {
            return $language;
        }

        // By default we use language selected in store admin
        return $this->getConfigData('language');
    }



    /**
     *  Output failure response and stop the script
     *
     *  @param    none
     *  @return	  void
     */
    public function generateErrorResponse()
    {
        die($this->getErrorResponse());
    }

    /**
     *  Return response for Alipay success payment
     *
     *  @param    none
     *  @return	  string Success response string
     */
    public function getSuccessResponse()
    {
        $response = array(
            'Pragma: no-cache',
            'Content-type : text/plain',
            'Version: 1',
            'OK'
        );
        return implode("\n", $response) . "\n";
    }

    /**
     *  Return response for Alipay failure payment
     *
     *  @param    none
     *  @return	  string Failure response string
     */
    public function getErrorResponse()
    {
        $response = array(
            'Pragma: no-cache',
            'Content-type : text/plain',
            'Version: 1',
            'Document falsifie'
        );
        return implode("\n", $response) . "\n";
    }

    public function getAppId(){
        $appId = $this->getConfigData('app_id');
        return $appId;
    }

    public function getAccessToken(){
        $appid = $this->appid;
        $appsecret = $this->appsecret;
        // Get resource
        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');
        $writeConnection = $resource->getConnection('core_write');
        $query = "select token,createtime  FROM weixin_token where id=1";
        $res = $readConnection->fetchAll($query);
        if(!empty($res)){
            $arr = $res[0];
            $createtime = $arr['createtime'];
            if(time()-$createtime < 2*3600){
                return $arr['token'];
            }
        }
        $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$appid&secret=$appsecret";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检查，0表示阻止对证书的合法性的检查。
        @curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 1); // 从证书中检查SSL加密算法是否存在
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0)");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $r = curl_exec($ch);
        $result = json_decode ($r ,true);
        if($result){
            $token = $result['access_token'];
            if(empty($res)){
                $time = time();
                $sql = "insert into weixin_token(token,createtime) values ('$token',$time)";
                $writeConnection->query($sql,array($token));
            }else{
                $time = time();
                $sql = "update weixin_token set token='$token',createtime=$time where id=1";
                $writeConnection->query($sql);
            }
            return $result['access_token'];
        }else{
            return null;
        }

    }

    public function getList(){
        $connection = Mage::getSingleton('core/resource')->getConnection('core_read');
        $select = $connection->select()
            ->from('alipay_log', array('trade_no','type','post_data'))
            ->where(' type="weixinsuccess" ');
        $res = $connection->fetchAll($select);
        foreach($res as $each){
            $post_data = $each['post_data'];
            $arr = explode('|',$post_data);
            $out_trade_no = $arr[0];
            $trade_no = $arr[1];
            $openId = $arr[2];

        }
    }
}