<?php
class CosmoCommerce_Weixinpay_IndexController extends Mage_Core_Controller_Front_Action{
    public function indexAction(){
//        $this->loadLayout();
//        $this->renderLayout();
        echo "Weixinpays".Mage::getBaseUrl();
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
        }else if("alipay_wait_seller_send_goods" == $status){
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