<?php
/**
 * 统一支付接口类
 */
class Infinitech_Weixinpay_Model_Unifiedorderpub extends Infinitech_Weixinpay_Model_Wxpayclientpub
{
    function __construct()
    {
        //设置接口链接
        $this->url = "https://api.mch.weixin.qq.com/pay/unifiedorder";
        //设置curl超时时间
        $this->curl_timeout = Infinitech_Weixinpay_Model_Wxpaypubconfig::CURL_TIMEOUT;
    }

    /**
     * 生成接口参数xml
     */
    function createXml()
    {
        try
        {
            //检测必填参数
            if($this->parameters["out_trade_no"] == null)
            {
                throw new Infinitech_Weixinpay_Model_Sdkruntimeexception("缺少统一支付接口必填参数out_trade_no！"."<br>");
                }elseif($this->parameters["body"] == null){
                throw new Infinitech_Weixinpay_Model_Sdkruntimeexception("缺少统一支付接口必填参数body！"."<br>");
            }elseif ($this->parameters["total_fee"] == null ) {
                throw new Infinitech_Weixinpay_Model_Sdkruntimeexception("缺少统一支付接口必填参数total_fee！"."<br>");
            }elseif ($this->parameters["notify_url"] == null) {
                throw new Infinitech_Weixinpay_Model_Sdkruntimeexception("缺少统一支付接口必填参数notify_url！"."<br>");
            }elseif ($this->parameters["trade_type"] == null) {
                throw new Infinitech_Weixinpay_Model_Sdkruntimeexception("缺少统一支付接口必填参数trade_type！"."<br>");
            }elseif ($this->parameters["trade_type"] == "JSAPI" &&
                $this->parameters["openid"] == NULL){
                throw new Infinitech_Weixinpay_Model_Sdkruntimeexception("统一支付接口中，缺少必填参数openid！trade_type为JSAPI时，openid为必填参数！"."<br>");
            }
            $this->parameters["appid"] = Infinitech_Weixinpay_Model_Wxpaypubconfig::getCode("app_id");//公众账号ID
            $this->parameters["mch_id"] = Infinitech_Weixinpay_Model_Wxpaypubconfig::getCode("mch_id");//商户号
            $this->parameters["spbill_create_ip"] = "127.0.0.1";//终端ip
            $this->parameters["nonce_str"] = $this->createNoncestr();//随机字符串
            $this->parameters["sign"] = $this->getSign($this->parameters);//签名
            return  $this->arrayToXml($this->parameters);
        }catch (Infinitech_Weixinpay_Model_Sdkruntimeexception $e)
        {
            die($e->errorMessage());
        }
    }

    /**
     * 获取prepay_id
     */
    function getPrepayId()
    {
        $this->postXml();
        $this->result = $this->xmlToArray($this->response);
        $prepay_id = $this->result["prepay_id"];
        return $prepay_id;
    }

    public function getNotifyUrl(){
        return Mage::getUrl('weixinpay/payment/notify/', array('_secure' => true));
    }

}