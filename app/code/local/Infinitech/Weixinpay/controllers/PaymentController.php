<?php
class Infinitech_Weixinpay_PaymentController extends Mage_Core_Controller_Front_Action{
    protected $_order;
    public function payAction(){
//        //使用统一支付接口
//        $unifiedOrder = new UnifiedOrder_pub();
//
//        //设置统一支付接口参数
//        //设置必填参数
//        //appid已填,商户无需重复填写
//        //mch_id已填,商户无需重复填写
//        //noncestr已填,商户无需重复填写
//        //spbill_create_ip已填,商户无需重复填写
//        //sign已填,商户无需重复填写
//        $unifiedOrder->setParameter("body","nike");//商品描述
//        //自定义订单号，此处仅作举例
//        $timeStamp = time();
//        $out_trade_no = WxPayConf_pub::APPID."$timeStamp";
//        $unifiedOrder->setParameter("out_trade_no","$out_trade_no");//商户订单号
//        $unifiedOrder->setParameter("total_fee","1");//总金额
//        $unifiedOrder->setParameter("notify_url",WxPayConf_pub::NOTIFY_URL);//通知地址
//        $unifiedOrder->setParameter("trade_type","NATIVE");//交易类型
//        //非必填参数，商户可根据实际情况选填
//        //$unifiedOrder->setParameter("sub_mch_id","XXXX");//子商户号
//        //$unifiedOrder->setParameter("device_info","XXXX");//设备号
//        //$unifiedOrder->setParameter("attach","XXXX");//附加数据
//        //$unifiedOrder->setParameter("time_start","XXXX");//交易起始时间
//        //$unifiedOrder->setParameter("time_expire","XXXX");//交易结束时间
//        //$unifiedOrder->setParameter("goods_tag","XXXX");//商品标记
//        //$unifiedOrder->setParameter("openid","XXXX");//用户标识
//        //$unifiedOrder->setParameter("product_id","XXXX");//商品ID
//
//        //获取统一支付接口结果
//        $unifiedOrderResult = $unifiedOrder->getResult();
        $session = Mage::getSingleton('checkout/session');
        $order = $this->getOrder();

        if (!$order->getId())
        {
            $this->norouteAction();
            return;
        }
        $order->addStatusToHistory(
            $order->getStatus(),
            Mage::helper('weixinpay')->__('Customer was redirected to Weixinpay')
        );
        $order->save();

        $session->unsQuoteId();
        $this->loadLayout();
        $this->renderLayout();
    }
    public function wappayAction(){
        $session = Mage::getSingleton('checkout/session');
        $order = $this->getOrder();

        if (!$order->getId())
        {
            $this->norouteAction();
            return;
        }
        Mage::getSingleton('core/session')->setOrderId($order->getId());
        $order->addStatusToHistory(
            $order->getStatus(),
            Mage::helper('weixinpay')->__('Customer was redirected to Weixinpay')
        );
        $order->save();
        $session->unsQuoteId();
        $this->loadLayout();
        $this->renderLayout();
    }

    public function notifyAction(){
        //使用通用通知接口
        $notify = Mage::getModel('weixinpay/notifypub');
        //存储微信的回调
        $xml = $GLOBALS['HTTP_RAW_POST_DATA'];
        $notify->saveData($xml);

        //验证签名，并回应微信。
        //对后台通知交互时，如果微信收到商户的应答不是成功或超时，微信认为通知失败，
        //微信会通过一定的策略（如30分钟共8次）定期重新发起通知，
        //尽可能提高通知的成功率，但微信不保证通知最终能成功。
        if($notify->checkSign() == FALSE){
            $notify->setReturnParameter("return_code","FAIL");//返回状态码
            $notify->setReturnParameter("return_msg","签名失败");//返回信息
            mage :: log("return_msg 签名失败 NOT GOOD ");
        }else{
            $notify->setReturnParameter("return_code","SUCCESS");//设置返回码
        }
        $returnXml = $notify->returnXml();

        //==商户根据实际情况设置相应的处理流程，此处仅作举例=======

        //以log文件形式记录回调信息
        mage :: log("【接收到的notify通知】:\n".$xml."\n");

        if($notify->checkSign() == TRUE  || $notify->data["return_code"] == "SUCCESS")
        {
            if ($notify->data["return_code"] == "FAIL") {
                //此处应该更新一下订单状态，商户自行增删操作
                mage :: log("【通信出错】:\n".$xml."\n");
            }
            elseif($notify->data["result_code"] == "FAIL"){
                //此处应该更新一下订单状态，商户自行增删操作
                mage :: log("【业务出错】:\n".$xml."\n");
            }
            else{
                //此处应该更新一下订单状态，商户自行增删操作
                mage :: log("【支付成功】:\n".$xml."\n");
                $this->updateOrder($xml);
            }

            //商户自行增加处理流程,
            //例如：更新订单状态
            //例如：数据库操作
            //例如：推送支付完成信息
        }else{
            mage :: log("【签名失败-error】");
        }
    }
    public function updateOrder($str){
        $xml = simplexml_load_string($str);
        $orderId = (string)$xml->out_trade_no;
        $arr = explode('_',$orderId);
        if(!empty($arr)){
            $orderId = $arr[0];
            $order = Mage::getModel('sales/order')->loadByIncrementId($orderId);
            if ($order->getStatus() == 'weixin_wait_buyer_pay' || $order->getState() == 'new' ||$order->getStatus() == 'alipay_wait_buyer_pay' ) {
                //写LOG
                $postDataFromPost['out_trade_no'] = $orderId;
                $postDataFromPost['trade_no'] = (string)$xml->transaction_id;
                $postDataFromPost['open_id'] = (string)$xml->openid;
                $this->logTrans($postDataFromPost,"weixinsuccess");//交易成功

                $order->setState(Mage_Sales_Model_Order::STATE_PROCESSING);
                $order->setStatus("weixin_wait_seller_send_goods");
                //order 相关操作 比如 产生订单XML文件，与其他系统进行交互
//                $model = Mage::getModel('sales/postorder');
//                $model->post_new_order($order);
//                $postMessage = Mage::getModel('sales/postmessage');
//                $postMessage->saveDataAndSendWebservice($order);
                try{
                    $order->save();
//                    $this->sendMail($orderId);
                    Mage :: log("付款成功");
                    echo "success";
                    return;
                } catch(Exception $e){
                    Mage :: log( "Erro Message: ".$e->getMessage());
                }
            }
        }

    }
    public function sendMail($orderId){
        $to = Infinitech_Weixinpay_Model_Wxpaypubconfig::getCode("send_mail_notification");
        $subject = "lala New order ".$orderId;
        $message = "New Order come this order id is ".$orderId;

        $headers = 'From: admin@snaekerhead.com '  . "\r\n" .
            'Reply-To: admin@snaekerhead.com ' . "\r\n" .
            'X-Mailer: PHP/' . phpversion();

        mail($to, $subject, $message, $headers);
    }
    public function logTrans($trans,$type){
        $log = Mage::getModel('alipay/log');
        $log->setLogAt(time());
        $log->setOrderId($trans['out_trade_no']);
        $log->setTradeNo($trans['trade_no']);
        $log->setType($type);
        $log->setPostData(implode('|',$trans));
        $log->save();
    }
    public function getOrder()
    {
        if ($this->_order == null)
        {
            $session = Mage::getSingleton('checkout/session');
            $this->_order = Mage::getModel('sales/order');
            if($orderId=$session->getAlipayPaymentOrderId()){

                $order = Mage::getModel('sales/order')->load($orderId);
                if (!$order->getId())
                {
                    $this->norouteAction();
                    return;
                }
                $order_cid=$order->getCustomerId();
                $current_cid=0;
                if(Mage::helper('customer')->getCustomer()){
                    $current_cid=Mage::helper('customer')->getCustomer()->getId();
                }else{
                    $this->_redirect('customer/account/login');
                    return;
                }

                if ($current_cid!=$order_cid)
                {
                    $this->norouteAction();
                    return;
                }

                $this->_order->load($orderId);
            }else{
                $this->_order->loadByIncrementId($session->getLastRealOrderId());
            }
        }
        return $this->_order;
    }

    public function jsAction(){
        $orderId = $this->_request->getParam('order_id');
        $order = Mage::getModel('sales/order');
        $order->load($orderId);
        $status = null;
        if($order){
            $status = $order->getStatus();
        }
        header('Content-Type: text/javascript;charset=UTF-8');
        if( 'alipay_wait_buyer_pay' == $status ){
            echo 'window.code=408';
        }else if("weixin_wait_seller_send_goods" == $status){
            echo 'window.code=300';
        }else{
            echo 'window.code=400';
        }

    }

    public function getTokenAction(){
        $weixinpay = Mage::getModel('weixinpay/weixinpay');
//        $token = $weixinpay->getAccessToken();
        $list = $weixinpay->getList();
    }
}