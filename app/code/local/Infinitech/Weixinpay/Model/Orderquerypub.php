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

    function orderQuery($out_trade_no){
        $this->setParameter("out_trade_no",$out_trade_no);
        $xml = $this->postXml();
        //使用通用通知接口
        $notify = Mage::getModel('weixinpay/notifypub');
        $notify->saveData($xml);

        if($notify->checkSign() == TRUE)
        {
            if ($notify->data["return_code"] == "FAIL") {
                //此处应该更新一下订单状态，商户自行增删操作
                mage :: log("【通信出错】:\n".$xml."\n");
                return false;
            }
            elseif($notify->data["result_code"] == "FAIL"){
                //此处应该更新一下订单状态，商户自行增删操作
                mage :: log("【业务出错】:\n".$xml."\n");
                return false;
            }
            else{
                return $notify->data["trade_state"];
            }
        }else{
            mage :: log("【签名失败-error】:\n".$xml."\n");
            return false;
        }
    }
}

?>
