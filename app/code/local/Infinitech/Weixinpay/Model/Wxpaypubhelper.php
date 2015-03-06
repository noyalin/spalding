<?php
/**
 * 微信支付帮助库
 * ====================================================
 * 接口分三种类型：
 * 【请求型接口】--Wxpay_client_
 * 		统一支付接口类--UnifiedOrder
 * 		订单查询接口--OrderQuery
 * 		退款申请接口--Refund
 * 		退款查询接口--RefundQuery
 * 		对账单接口--DownloadBill
 * 		短链接转换接口--ShortUrl
 * 【响应型接口】--Wxpay_server_
 * 		通用通知接口--Notify
 * 		Native支付——请求商家获取商品信息接口--NativeCall
 * 【其他】
 * 		静态链接二维码--NativeLink
 * 		JSAPI支付--JsApi
 * =====================================================
 * 【CommonUtil】常用工具：
 * 		trimString()，设置参数时需要用到的字符处理函数
 * 		createNoncestr()，产生随机字符串，不长于32位
 * 		formatBizQueryParaMap(),格式化参数，签名过程需要用到
 * 		getSign(),生成签名
 * 		arrayToXml(),array转xml
 * 		xmlToArray(),xml转 array
 * 		postXmlCurl(),以post方式提交xml到对应的接口url
 * 		postXmlSSLCurl(),使用证书，以post方式提交xml到对应的接口url
 */
//	include_once("SDKRuntimeException.php");
//	include_once("WxPay.pub.config.php");








/**
 * 订单查询接口
 */
class Infinitech_Weixinpay_Model_Orderquerypub extends Infinitech_Weixinpay_Model_Wxpayclientpub
{
    function __construct()
    {
        //设置接口链接
        $this->url = "https://api.mch.weixin.qq.com/pay/orderquery";
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
            if($this->parameters["out_trade_no"] == null &&
                $this->parameters["transaction_id"] == null)
            {
                throw new SDKRuntimeException("订单查询接口中，out_trade_no、transaction_id至少填一个！"."<br>");
            }
            $this->parameters["appid"] = Infinitech_Weixinpay_Model_Wxpaypubconfig::getCode('app_id');//公众账号ID
            $this->parameters["mch_id"] = Infinitech_Weixinpay_Model_Wxpaypubconfig::getCode('mch_id');//商户号
            $this->parameters["nonce_str"] = $this->createNoncestr();//随机字符串
            $this->parameters["sign"] = $this->getSign($this->parameters);//签名
            return  $this->arrayToXml($this->parameters);
        }catch (SDKRuntimeException $e)
        {
            die($e->errorMessage());
        }
    }

}

/**
 * 退款申请接口
 */
class Infinitech_Weixinpay_Model_Refundpub extends Infinitech_Weixinpay_Model_Wxpayclientpub
{

    function __construct() {
        //设置接口链接
        $this->url = "https://api.mch.weixin.qq.com/secapi/pay/refund";
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
            if($this->parameters["out_trade_no"] == null && $this->parameters["transaction_id"] == null) {
                throw new SDKRuntimeException("退款申请接口中，out_trade_no、transaction_id至少填一个！"."<br>");
            }elseif($this->parameters["out_refund_no"] == null){
                throw new SDKRuntimeException("退款申请接口中，缺少必填参数out_refund_no！"."<br>");
            }elseif($this->parameters["total_fee"] == null){
                throw new SDKRuntimeException("退款申请接口中，缺少必填参数total_fee！"."<br>");
            }elseif($this->parameters["refund_fee"] == null){
                throw new SDKRuntimeException("退款申请接口中，缺少必填参数refund_fee！"."<br>");
            }elseif($this->parameters["op_user_id"] == null){
                throw new SDKRuntimeException("退款申请接口中，缺少必填参数op_user_id！"."<br>");
            }
            $this->parameters["appid"] = Infinitech_Weixinpay_Model_Wxpaypubconfig::getCode('app_id');//公众账号ID
            $this->parameters["mch_id"] = Infinitech_Weixinpay_Model_Wxpaypubconfig::getCode('mch_id');//商户号
            $this->parameters["nonce_str"] = $this->createNoncestr();//随机字符串
            $this->parameters["sign"] = $this->getSign($this->parameters);//签名
            return  $this->arrayToXml($this->parameters);
        }catch (SDKRuntimeException $e)
        {
            die($e->errorMessage());
        }
    }
    /**
     * 	作用：获取结果，使用证书通信
     */
    function getResult()
    {
        $this->postXmlSSL();
        $this->result = $this->xmlToArray($this->response);
        return $this->result;
    }

}


/**
 * 退款查询接口
 */
class Infinitech_Weixinpay_Model_Refundquerypub extends Infinitech_Weixinpay_Model_Wxpayclientpub
{

    function __construct() {
        //设置接口链接
        $this->url = "https://api.mch.weixin.qq.com/pay/refundquery";
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
            if($this->parameters["out_refund_no"] == null &&
                $this->parameters["out_trade_no"] == null &&
                $this->parameters["transaction_id"] == null &&
                $this->parameters["refund_id "] == null)
            {
                throw new SDKRuntimeException("退款查询接口中，out_refund_no、out_trade_no、transaction_id、refund_id四个参数必填一个！"."<br>");
            }
            $this->parameters["appid"] = Infinitech_Weixinpay_Model_Wxpaypubconfig::getCode('app_id');//公众账号ID
            $this->parameters["mch_id"] = Infinitech_Weixinpay_Model_Wxpaypubconfig::getCode('mch_id');//商户号
            $this->parameters["nonce_str"] = $this->createNoncestr();//随机字符串
            $this->parameters["sign"] = $this->getSign($this->parameters);//签名
            return  $this->arrayToXml($this->parameters);
        }catch (SDKRuntimeException $e)
        {
            die($e->errorMessage());
        }
    }

    /**
     * 	作用：获取结果，使用证书通信
     */
    function getResult()
    {
        $this->postXmlSSL();
        $this->result = $this->xmlToArray($this->response);
        return $this->result;
    }

}

/**
 * 对账单接口
 */
class Infinitech_Weixinpay_Model_Downloadbillpub extends Infinitech_Weixinpay_Model_Wxpayclientpub
{

    function __construct()
    {
        //设置接口链接
        $this->url = "https://api.mch.weixin.qq.com/pay/downloadbill";
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
            if($this->parameters["bill_date"] == null )
            {
                throw new SDKRuntimeException("对账单接口中，缺少必填参数bill_date！"."<br>");
            }
            $this->parameters["appid"] = Infinitech_Weixinpay_Model_Wxpaypubconfig::getCode('app_id');//公众账号ID
            $this->parameters["mch_id"] = Infinitech_Weixinpay_Model_Wxpaypubconfig::getCode('mch_id');//商户号
            $this->parameters["nonce_str"] = $this->createNoncestr();//随机字符串
            $this->parameters["sign"] = $this->getSign($this->parameters);//签名
            return  $this->arrayToXml($this->parameters);
        }catch (SDKRuntimeException $e)
        {
            die($e->errorMessage());
        }
    }

    /**
     * 	作用：获取结果，默认不使用证书
     */
    function getResult()
    {
        $this->postXml();
        $this->result = $this->xmlToArray($this->result_xml);
        return $this->result;
    }



}

/**
 * 短链接转换接口
 */
class Infinitech_Weixinpay_Model_Shorturlpub extends Infinitech_Weixinpay_Model_Wxpayclientpub
{
    function __construct()
    {
        //设置接口链接
        $this->url = "https://api.mch.weixin.qq.com/tools/shorturl";
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
            if($this->parameters["long_url"] == null )
            {
                throw new SDKRuntimeException("短链接转换接口中，缺少必填参数long_url！"."<br>");
            }
            $this->parameters["appid"] = Infinitech_Weixinpay_Model_Wxpaypubconfig::getCode('app_id');//公众账号ID
            $this->parameters["mch_id"] = Infinitech_Weixinpay_Model_Wxpaypubconfig::getCode('mch_id');//商户号
            $this->parameters["nonce_str"] = $this->createNoncestr();//随机字符串
            $this->parameters["sign"] = $this->getSign($this->parameters);//签名
            return  $this->arrayToXml($this->parameters);
        }catch (SDKRuntimeException $e)
        {
            die($e->errorMessage());
        }
    }

    /**
     * 获取prepay_id
     */
    function getShortUrl()
    {
        $this->postXml();
        $prepay_id = $this->result["short_url"];
        return $prepay_id;
    }

}








/**
 * 请求商家获取商品信息接口
 */
class Infinitech_Weixinpay_Model_Nativecallpub extends Infinitech_Weixinpay_Model_Wxpayserverpub
{
    /**
     * 生成接口参数xml
     */
    function createXml()
    {
        if($this->returnParameters["return_code"] == "SUCCESS"){
            $this->returnParameters["appid"] = Infinitech_Weixinpay_Model_Wxpaypubconfig::getCode('app_id');//公众账号ID
            $this->returnParameters["mch_id"] = Infinitech_Weixinpay_Model_Wxpaypubconfig::getCode('mch_id');//商户号
            $this->returnParameters["nonce_str"] = $this->createNoncestr();//随机字符串
            $this->returnParameters["sign"] = $this->getSign($this->returnParameters);//签名
        }
        return $this->arrayToXml($this->returnParameters);
    }

    /**
     * 获取product_id
     */
    function getProductId()
    {
        $product_id = $this->data["product_id"];
        return $product_id;
    }

}

/**
 * 静态链接二维码
 */
class Infinitech_Weixinpay_Model_Nativelinkpub  extends Infinitech_Weixinpay_Model_Commonutilpub
{
    var $parameters;//静态链接参数
    var $url;//静态链接

    function __construct()
    {
    }

    /**
     * 设置参数
     */
    function setParameter($parameter, $parameterValue)
    {
        $this->parameters[$this->trimString($parameter)] = $this->trimString($parameterValue);
    }

    /**
     * 生成Native支付链接二维码
     */
    function createLink()
    {
        try
        {
            if($this->parameters["product_id"] == null)
            {
                throw new SDKRuntimeException("缺少Native支付二维码链接必填参数product_id！"."<br>");
            }
            $this->parameters["appid"] = Infinitech_Weixinpay_Model_Wxpaypubconfig::getCode('app_id');//公众账号ID
            $this->parameters["mch_id"] = Infinitech_Weixinpay_Model_Wxpaypubconfig::getCode('mch_id');//商户号
            $time_stamp = time();
            $this->parameters["time_stamp"] = "$time_stamp";//时间戳
            $this->parameters["nonce_str"] = $this->createNoncestr();//随机字符串
            $this->parameters["sign"] = $this->getSign($this->parameters);//签名
            $bizString = $this->formatBizQueryParaMap($this->parameters, false);
            $this->url = "weixin://wxpay/bizpayurl?".$bizString;
        }catch (SDKRuntimeException $e)
        {
            die($e->errorMessage());
        }
    }

    /**
     * 返回链接
     */
    function getUrl()
    {
        $this->createLink();
        return $this->url;
    }
}





?>
