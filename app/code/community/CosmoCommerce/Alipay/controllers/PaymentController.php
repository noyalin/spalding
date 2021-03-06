<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * @category    CosmoCommerce
 * @package     CosmoCommerce_Alipay
 * @copyright   Copyright (c) 2009-2014 CosmoCommerce,LLC. (http://www.cosmocommerce.com)
 * @contact :
 * T: +86-021-66346672
 * L: Shanghai,China
 * M:sales@cosmocommerce.com
 */
class CosmoCommerce_Alipay_PaymentController extends Mage_Core_Controller_Front_Action
{
    /**
     * Order instance
     */
    protected $_order;
	protected $_gateway="https://mapi.alipay.com/gateway.do?";

    /**
     *  Get order
     *
     *  @param    none
     *  @return	  Mage_Sales_Model_Order
     */
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

    /**
     * When a customer chooses Alipay on Checkout/Payment page
     *
     */
     
    public function payAction()
    {
        $session = Mage::getSingleton('checkout/session');
        $orderId = (int) $this->getRequest()->getParam('order_id');
        if ($orderId) {
            $session->setAlipayPaymentOrderId($orderId);
        }else{
            $orderId = $session->getLastOrderId();
            $session->setAlipayPaymentOrderId($orderId);
        }
        $order = $this->getOrder();


        if (!$order)
        {
            return;
        }
        if (!$order->getId())
        {
            return;
        }
        $order->addStatusToHistory(
        $order->getStatus(),
        Mage::helper('alipay')->__('Customer was redirected to payment confirm page')
        );
        $order->save();

        Mage::getModel('custommade/info')->saveCustomMade($order);
        
        Mage::getModel('customclothes/order')->saveCustomClothes($order);
        $storeCode = Mage::app()->getStore()->getCode();
//        if('sneakerhead_cn_mobile' ==  $storeCode){
//            $agent = $_SERVER['HTTP_USER_AGENT'];
//            if(strpos($agent, "MicroMessenger")){
//                $url = mage :: getUrl('weixinpay/payment/wappay', array('_secure'=>true));
//            }else{
//                $url = mage :: getUrl('alipay/payment/wapredirect', array('_secure'=>true));
//            }
//
//            $this->_redirectUrl($url);
//        }
        $this->loadLayout();
        $this->renderLayout();
    }

    
    public function redirectAction()
    {
        $session = Mage::getSingleton('checkout/session');
        $order = $this->getOrder();

        if (!$order->getId())
        {
            $this->norouteAction();
            return;
        }

        $order->addStatusToHistory(
        $order->getStatus(),
        Mage::helper('alipay')->__('Customer was redirected to Alipay')
        );
        $order->save();

        
        $this->getResponse()
        ->setBody($this->getLayout()
        ->createBlock('alipay/redirect')
        ->setOrder($order)
        ->toHtml());

        $session->unsQuoteId();
    }

    public function wapRedirectAction()
    {
        $session = Mage::getSingleton('checkout/session');
        $order = $this->getOrder();

        if (!$order->getId())
        {
            $this->norouteAction();
            return;
        }

        $order->addStatusToHistory(
            $order->getStatus(),
            Mage::helper('alipay')->__('Customer was redirected to Alipay')
        );
        $order->save();


        $this->getResponse()
            ->setBody($this->getLayout()
                ->createBlock('alipay/wapredirect')
                ->setOrder($order)
                ->toHtml());

        $session->unsQuoteId();
    }

    public function notifyAction()
    {
        if ($this->getRequest()->isPost())
        {
            $postData = $this->getRequest()->getPost();
            $method = 'post';


        } else if ($this->getRequest()->isGet())
        {
            $postData = $this->getRequest()->getQuery();
            $method = 'get';

        } else
        {
            return;
        }

		$alipay = Mage::getModel('alipay/payment');
        $partner=$alipay->getConfigData('partner_id');
        $security_code=$alipay->getConfigData('security_code');
//        Mage::log("begin alipay");
//        Mage::log("Method: ".$method. "   trade_no: ".$postData['out_trade_no']);
//        Mage::log(print_r($postData,true));
//        Mage::log("end alipay");
        $sendemail = null;

        //???????????????????????????
            $partner="2088711909431762";
            $security_code="qisngbnvaj4ydshzji1irosklki1y8jb";
            $sendemail =  $postData['seller_email'];


        if( (isset($postData['seller_email']) && ($postData['seller_email'] == 'sc-official@spaldingchina.com.cn')) || (isset($postData['exterface']) &&($postData['exterface'] == 'create_direct_pay_by_user')) ){
            //???????????? ?????? ????????? kobexin_8@126.com
            /*
             * 2014-08-21T02:30:12+00:00 DEBUG (7): begin alipay
2014-08-21T02:30:12+00:00 DEBUG (7): Method: post   trade_no: 800000097
2014-08-21T02:30:12+00:00 DEBUG (7): Array
(
    [discount] => 0.00
    [payment_type] => 1
    [subject] => 800000097
    [trade_no] => 2014082124757283
    [buyer_email] => kehuzijinbu015@alipay.com
    [gmt_create] => 2014-08-21 10:24:57
    [notify_type] => trade_status_sync
    [quantity] => 1
    [out_trade_no] => 800000097
    [seller_id] => 2088711909431762
    [notify_time] => 2014-08-21 10:30:08
    [body] => 800000097
    [trade_status] => TRADE_SUCCESS
    [is_total_fee_adjust] => N
    [total_fee] => 0.10
    [gmt_payment] => 2014-08-21 10:26:05
    [seller_email] => kobexin_8@126.com
    [bank_seq_no] => 6871307099
    [price] => 0.10
    [buyer_id] => 2088502970072835
    [notify_id] => b9862546c845792670be37f6cd1a3b086m
    [use_coupon] => N
    [sign_type] => MD5
    [sign] => 352547d951a737907831854c32f710f9
)

2014-08-21T02:30:12+00:00 DEBUG (7): end alipay

             */
            $partner="2088711909431762";
            $security_code="qisngbnvaj4ydshzji1irosklki1y8jb";
            $sendemail =  $postData['seller_email'];
        }else{
            //come from alipay go to kobe.xin@sneakerhead.com
            /* POST
 * 2014-08-22T07:01:29+00:00 DEBUG (7): begin alipay
2014-08-22T07:01:29+00:00 DEBUG (7): Method: post   trade_no: 800000096
2014-08-22T07:01:29+00:00 DEBUG (7): Array
(
[notify_id] => fba5bd24367b1e24400e8dbae685894052
[notify_type] => trade_status_sync
[sign] => 931f68c8f0a976260dac0eb019840ee8
[trade_no] => 2014082243112155
[total_fee] => 0.02
[out_trade_no] => 800000096
[currency] => USD
[notify_time] => 2014-08-22 15:01:25
[trade_status] => TRADE_FINISHED
[sign_type] => MD5
)
//GET  url http://snkculture.cn/alipay/payment/notify/?currency=USD&total_fee=0.02&out_trade_no=800000097&trade_no=2014082243289755&trade_status=TRADE_FINISHED
2014-08-22T07:01:29+00:00 DEBUG (7): end alipay
 */
            $partner="2088611060046440";
            $security_code="5afxblbtnyu2nzakivis9u9g8ww9m7qc";
        }



        $alipayNotify = Mage::getModel('alipay/alipaynotify',array(
            'partner' => $partner,
            'key' => $security_code
        ));
        $verify_result = $alipayNotify->verifyReturn($postData);
        $order = Mage::getModel('sales/order');
        $order->loadByIncrementId($postData['out_trade_no']);
        if($verify_result){
//        if(true){
            //???????????????
            $out_trade_no = $postData['out_trade_no'];
            //??????????????????
            $trade_no = $postData['trade_no'];
            //????????????
            $trade_status = $postData['trade_status'];


            $this->logTrans($postData,$postData['trade_status']);//????????????
            if($postData['trade_status'] == 'TRADE_FINISHED' || $postData['trade_status'] == 'TRADE_SUCCESS') {
                //????????????????????????????????????????????????????????????
                //?????????????????????????????????????????????out_trade_no????????????????????????????????????????????????????????????????????????????????????????????????
                //??????????????????????????????????????????????????????

                if ($order->getStatus() == 'weixin_wait_buyer_pay' || $order->getState() == 'new' ||
                    $order->getStatus() == 'alipay_wait_buyer_pay') {
                    //$order->setAlipayTradeno($postData['trade_no']);
                    $order->setState(Mage_Sales_Model_Order::STATE_PROCESSING);
                    $order->setStatus("alipay_wait_seller_send_goods");
                    if ($sendemail) {
                        $order->sendOrderUpdateEmail(true, Mage::helper('alipay')->__('TRADE SUCCESS'));
                    }
                    $order->addStatusToHistory(
                        'alipay_wait_seller_send_goods',
                        Mage::helper('alipay')->__('WAIT SELLER SEND GOODS'));
                    //create xml
                    $model = Mage::getModel('sales/postorder');
                    $model->post_new_order($order);
//                    $postMessage = Mage::getModel('sales/postmessage');
//                    $postMessage->saveDataAndSendWebservice($order);

                    $this->SavePaymentInfo($out_trade_no);

                    try {
                        $order->save();
                        
                        $this->savePayLog($order);
                        
                        $message = Mage::getModel('customclothes/customClothes')->updateCustomClothesOrderStatus($postData['out_trade_no']);
                        //$this->sendMail($out_trade_no);
                        if($message){
                        	$this->sendMailForOrder($out_trade_no, $message);
                        }
                        
                        //$this->sendMail($out_trade_no);
                        if ($method == 'get') {
                            $this->_redirect("sales/order/view/order_id/" . $order->getId());
                        } else {
                            echo "success";
                        }
                        return;
                    } catch (Exception $e) {
                        Mage:: log("Erro Message: " . $e->getMessage());
                    }
                } else if ($order->getStatus() == 'canceled') {
                    $this->sendMailForOrder($out_trade_no, "???????????? ?????????????????????????????????????????????canceled??????IT???????????????");
                } else {
                    if($method == 'get'){
                        echo "success";
                        Mage :: log("???????????????".$order->getIncrementId()."??? ????????????????????????");
                        $this->_redirect("sales/order/view/order_id/".$order->getId());
                    }
                }
            }
            else {
                echo "??????????????????????????????...";
            }
        }
        if($method == 'get'){
            //?????? ????????????
//            Mage :: log("??????????????????????????????????????? ?????? SIGN,????????????".$order->getId());
            $this->_redirect("sales/order/view/order_id/".$order->getId());
        }
        return;
    }
    public function sendMail($orderId){
//        $to = "davis.du@voyageone.cn;bob.chen@voyageone.cn";
//        $subject = "lala New order ".$orderId;
//        $message = "New Order come this order id is ".$orderId;
//
//        $headers = 'From: admin@snaekerhead.com '  . "\r\n" .
//            'Reply-To: admin@snaekerhead.com ' . "\r\n" .
//            'X-Mailer: PHP/' . phpversion();
//
//        mail($to, $subject, $message, $headers);

        $message = "New Order come this order id is ".$orderId;
        Mage :: log($message);
    }

    public function sendMailForOrder($orderId, $msg){
        $to = "terry.yao@voyageone.cn;bob.chen@voyageone.cn";
        $subject = "Spalding Order Job : ".$orderId;
        $message = $msg."\r\norder id is ".$orderId;

        $headers = 'From: admin@spaldingchina.com.cn '  . "\r\n" .
            'Content-type: text/html; charset=utf-8' . "\r\n" .
            'Reply-To: admin@spaldingchina.com.cn ' . "\r\n" .
            'X-Mailer: PHP/' . phpversion();

        mail($to, $subject, $message, $headers);
        Mage :: log($message);
    }

	public function get_verify($url,$time_out = "60") {
		$urlarr     = parse_url($url);
		$errno      = "";
		$errstr     = "";
		$transports = "";
		if($urlarr["scheme"] == "https") {
			$transports = "ssl://";
			$urlarr["port"] = "443";
		} else {
			$transports = "tcp://";
			$urlarr["port"] = "80";
		}
		$fp=@fsockopen($transports . $urlarr['host'],$urlarr['port'],$errno,$errstr,$time_out);
		if(!$fp) {
			die("ERROR: $errno - $errstr<br />\n");
		} else {
			fputs($fp, "POST ".$urlarr["path"]." HTTP/1.1\r\n");
			fputs($fp, "Host: ".$urlarr["host"]."\r\n");
			fputs($fp, "Content-type: application/x-www-form-urlencoded\r\n");
			fputs($fp, "Content-length: ".strlen($urlarr["query"])."\r\n");
			fputs($fp, "Connection: close\r\n\r\n");
			fputs($fp, $urlarr["query"] . "\r\n\r\n");
			while(!feof($fp)) {
				$info[]=@fgets($fp, 1024);
			}
			fclose($fp);
			$info = implode(",",$info);
			$arg="";
			while (list ($key, $val) = each ($_POST)) {
				$arg.=$key."=".$val."&";
			}

		return $info;
		}

	}
    /**
     *  Alipay response router
     *
     *  @param    none
     *  @return	  void
     public function notifyAction()
     {
     $model = Mage::getModel('alipay/payment');
     
     if ($this->getRequest()->isPost()) {
     $postData = $this->getRequest()->getPost();
     $method = 'post';
     } else if ($this->getRequest()->isGet()) {
     $postData = $this->getRequest()->getQuery();
     $method = 'get';
     } else {
     $model->generateErrorResponse();
     }
     $order = Mage::getModel('sales/order')
     ->loadByIncrementId($postData['reference']);
     if (!$order->getId()) {
     $model->generateErrorResponse();
     }
     if ($returnedMAC == $correctMAC) {
     if (1) {
     $order->addStatusToHistory(
     $model->getConfigData('order_status_payment_accepted'),
     Mage::helper('alipay')->__('Payment accepted by Alipay')
     );
     
     $order->sendNewOrderEmail();
     if ($this->saveInvoice($order)) {
     //                $order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, true);
     }
     
     } else {
     $order->addStatusToHistory(
     $model->getConfigData('order_status_payment_refused'),
     Mage::helper('alipay')->__('Payment refused by Alipay')
     );
     
     // TODO: customer notification on payment failure
     }
     
     $order->save();
     } else {
     $order->addStatusToHistory(
     Mage_Sales_Model_Order::STATE_CANCELED,//$order->getStatus(),
     Mage::helper('alipay')->__('Returned MAC is invalid. Order cancelled.')
     );
     $order->cancel();
     $order->save();
     $model->generateErrorResponse();
     }
     }
     */
     /**
     *  Save invoice for order
     *
     *  @param    Mage_Sales_Model_Order $order
     *  @return	  boolean Can save invoice or not
     */
    protected function saveInvoice(Mage_Sales_Model_Order $order)
    {
        if ($order->canInvoice())
        {
            $convertor = Mage::getModel('sales/convert_order');
            $invoice = $convertor->toInvoice($order);
            foreach ($order->getAllItems() as $orderItem)
            {
                if (!$orderItem->getQtyToInvoice())
                {
                    continue ;
                }
                $item = $convertor->itemToInvoiceItem($orderItem);
                $item->setQty($orderItem->getQtyToInvoice());
                $invoice->addItem($item);
            }
            $invoice->collectTotals();
            $invoice->register()->capture();
            Mage::getModel('core/resource_transaction')
            ->addObject($invoice)
            ->addObject($invoice->getOrder())
            ->save();
            return true;
        }

        return false;
    }

    /**
     *  Success payment page
     *
     *  @param    none
     *  @return	  void
     */
    public function successAction()
    {
        $session = Mage::getSingleton('checkout/session');
        $session->setQuoteId($session->getAlipayPaymentQuoteId());
        $session->unsAlipayPaymentQuoteId();

        $order = $this->getOrder();

        if (!$order->getId())
        {
            $this->norouteAction();
            return;
        }

        $order->addStatusToHistory(
        $order->getStatus(),
        Mage::helper('alipay')->__('Customer successfully returned from Alipay')
        );

        $order->save();
        echo  $order->getStatus();
//        $this->_redirect('checkout/onepage/success');
    }

    /**
     *  Failure payment page
     *
     *  @param    none
     *  @return	  void
     */
    public function errorAction()
    {
        $session = Mage::getSingleton('checkout/session');
        $errorMsg = Mage::helper('alipay')->__(' There was an error occurred during paying process.');

        $order = $this->getOrder();

        if (!$order->getId())
        {
            $this->norouteAction();
            return;
        }
        if ($order instanceof Mage_Sales_Model_Order && $order->getId())
        {
            $order->addStatusToHistory(
            Mage_Sales_Model_Order::STATE_CANCELED,//$order->getStatus(),
            Mage::helper('alipay')->__('Customer returned from Alipay.').$errorMsg
            );

            $order->save();
        }

        $this->loadLayout();
        $this->renderLayout();
        Mage::getSingleton('checkout/session')->unsLastRealOrderId();
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

    public function wapNotifyAction()
    {
        if ($this->getRequest()->isPost())
        {
            $postData = $this->getRequest()->getPost();
            $method = 'post';
        } else if ($this->getRequest()->isGet())
        {
            $postData = $this->getRequest()->getQuery();
            $method = 'get';
        } else
        {
            return;
        }

//        Mage::log("begin wap alipay");
//        Mage::log("Method: ".$method);
//        Mage::log(print_r($postData,true));
//        Mage::log("end wap alipay");
        $sendemail = null;
        $partner="2088711909431762";
        $security_code="qisngbnvaj4ydshzji1irosklki1y8jb";
        /*
         * 2014-10-28T08:08:36+00:00 DEBUG (7): Array
(
    [out_trade_no] => 810000003
    [request_token] => requestToken
    [result] => success
    [trade_no] => 2014102865074159
    [sign] => G8/NVm9K5MfsWt7zlWYx1OfMxhPFt/nH1dgX1AS8sY4D0Qcxpv0jYH/nSsy9I49aU6rPw/6j4noamtHIapykTJN3jCtYM+IS6l/DX0iR+2WmsResuFTyNJ7hmvRWT09XxaUksqZUiF75JlGh9RH1iB0DknRuJtCJ1+snr85sUw8=
    [sign_type] => 0001
)
         */


        $alipayNotify = Mage::getModel('alipay/wapalipaynotify');
        if($method == "post"){
            $verify_result = $alipayNotify->verifyNotify($postData);
        }else{
            $verify_result = $alipayNotify->verifyReturn($postData);
        }

        $order = Mage::getModel('sales/order');
        $paySuccessFromPost = false;
        if($verify_result){
            if($method == "post"){
                $doc = new DOMDocument();

                $doc->loadXML($alipayNotify->decrypt($postData['notify_data']));
                if( ! empty($doc->getElementsByTagName( "notify" )->item(0)->nodeValue) ) {
                    //???????????????
                    $out_trade_no = $doc->getElementsByTagName( "out_trade_no" )->item(0)->nodeValue;
                    //??????????????????
                    $trade_no = $doc->getElementsByTagName( "trade_no" )->item(0)->nodeValue;
                    //????????????
                    $trade_status = $doc->getElementsByTagName( "trade_status" )->item(0)->nodeValue;
                    $order->loadByIncrementId($out_trade_no);
                    $postDataFromPost['out_trade_no'] = $out_trade_no;
                    $postDataFromPost['trade_no'] = $trade_no;
                    $this->logTrans($postDataFromPost,"success");//????????????
                    if($trade_status == 'TRADE_FINISHED') {
                        //????????????????????????????????????????????????????????????
                        //?????????????????????????????????????????????out_trade_no????????????????????????????????????????????????????????????????????????????????????????????????
                        //??????????????????????????????????????????????????????

                        //?????????
                        //?????????????????????????????????????????????
                        //1?????????????????????????????????????????????????????????
                        //2???????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????

                        //???????????????????????????????????????????????????????????????
                        //logResult("???????????????????????????????????????????????????????????????????????????");
                        $paySuccessFromPost = true;
                        echo "success";		//????????????????????????
                    }
                    else if ($trade_status == 'TRADE_SUCCESS') {
                        //????????????????????????????????????????????????????????????
                        //?????????????????????????????????????????????out_trade_no????????????????????????????????????????????????????????????????????????????????????????????????
                        //??????????????????????????????????????????????????????

                        //?????????
                        //?????????????????????????????????????????????????????????????????????????????????????????????????????????

                        //???????????????????????????????????????????????????????????????
                        //logResult("???????????????????????????????????????????????????????????????????????????");
                        $paySuccessFromPost = true;
                        echo "success";		//????????????????????????
                    }
                }
            }else{
                //???????????????
                $out_trade_no = $postData['out_trade_no'];
                //??????????????????
                $trade_no = $postData['trade_no'];
                //????????????
                $trade_status = $postData['result'];
                $order->loadByIncrementId($postData['out_trade_no']);
                $this->logTrans($postData,"success");//????????????
            }
            if( (isset($postData['result']) && $postData['result'] == 'success') || $paySuccessFromPost) {

                //????????????????????????????????????????????????????????????
                //?????????????????????????????????????????????out_trade_no????????????????????????????????????????????????????????????????????????????????????????????????
                //??????????????????????????????????????????????????????

                if ($order->getStatus() == 'alipay_wait_buyer_pay' || $order->getState() == 'new'  ||
                    $order->getStatus() == 'weixin_wait_buyer_pay') {
                    //$order->setAlipayTradeno($postData['trade_no']);
                    $order->setState(Mage_Sales_Model_Order::STATE_PROCESSING);
                    $order->setStatus("alipay_wait_seller_send_goods");
                    if($sendemail){
                        $order->sendOrderUpdateEmail(true,Mage::helper('alipay')->__('TRADE SUCCESS'));
                    }
                    $order->addStatusToHistory(
                        'alipay_wait_seller_send_goods',
                        Mage::helper('alipay')->__('WAIT SELLER SEND GOODS'));
                    //create xml
                    $model = Mage::getModel('sales/postorder');
                    $model->post_new_order($order);
                    //$postMessage = Mage::getModel('sales/postmessage');
                    //$postMessage->saveDataAndSendWebservice($order);

                    $this->SavePaymentInfo($out_trade_no);

                    try{
                        $order->save();
                        $this->savePayLog($order);
                        $message = Mage::getModel('customclothes/customClothes')->updateCustomClothesOrderStatus($postData['out_trade_no']);
                        if($message){
                        	$this->sendMailForOrder($out_trade_no, $message);
                        }
                        if($method == "get"){
                            $this->_redirect("sales/order/view/order_id/".$order->getId());
                            return;
                        }
                    } catch(Exception $e){
                        Mage :: log( "Erro Message: ".$e->getMessage());
                    }
                } else if ($order->getStatus() == 'canceled') {
                    $this->sendMailForOrder($out_trade_no, "???????????? ?????????????????????????????????????????????canceled??????IT???????????????");
                }else{
                    if($method == "get"){
                        echo "success";
                        Mage :: log("???????????????".$order->getIncrementId()."??? ?????????(wap)???????????????");
                        $this->_redirect("sales/order/view/order_id/".$order->getId());
                    }

                }
            }
            else {
                echo "??????????????????????????????...";
            }
        }
        //?????? ????????????
        if($method == "get"){
//            Mage :: log("??????????????????????????????????????? ?????? SIGN,????????????".$order->getId());
            $this->_redirect("sales/order/view/order_id/".$order->getId());
            return;
        }

    }
    
    private function savePayLog($order){
    	try{
	    	$resource = Mage::getSingleton('core/resource');
	    	$writeConnection = $resource->getConnection('core_write');
	    	$readConnection = $resource->getConnection('core_read');
	    	$now = date('Y-m-d H:i:s');
	    	$status = $order->getStatus();
	    	$entityId = $order->getEntityId();
	    	$storeId = intval($order->getStoreId());
	    	$orderId = $order->getIncrementId();
	    	$sql = "select id from order_pay_log where entity_id = ".$entityId;
	    	$row = $readConnection->fetchOne($sql);
	    	if($row == false){
	    		$sql = "insert into order_pay_log values(null,$storeId,$entityId,$orderId,'$now','$status');";
	    		$writeConnection->query($sql);
	    	}
	    } catch(Exception $e){
	    	Mage :: log( "Order pay log Error Message: ".$e->getMessage());
	    }
    }
    
    private function SavePaymentInfo($orderId)
    {
        try{
            Mage :: log( "SavePaymentInfo: orderId = ".$orderId);
            $orderCustom = Mage::getModel('custommade/info')->loadByIncrementId($orderId);
            if ($orderCustom->getId()) {
                $orderCustom->approving();
                $orderCustom->save();
            }
        } catch(Exception $e){
            Mage :: log( "AliPayment Error Message: orderId = ".$orderId);
            Mage :: log( "AliPayment Error Message: ".$e->getMessage());
//                    $this->sendMail();
        }
    }
    
    
    
}
