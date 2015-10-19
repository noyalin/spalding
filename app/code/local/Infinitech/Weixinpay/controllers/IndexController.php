<?php
class Infinitech_Weixinpay_IndexController extends Mage_Core_Controller_Front_Action{
    public function indexAction(){
//        $a = Mage::helper('helloworld');
//        echo "YES";
//        $this->loadLayout();
//        $this->renderLayout();
//        $weixinpay = Mage::getModel('weixinpay/pay');
//        echo $weixinpay->getAppId();
        echo Infinitech_Weixinpay_Model_Wxpaypubconfig :: getCode('app_id');
    }

    public function testAction(){
        echo "test";
//        $wxOrderQuery = Mage::getModel('weixinpay/Orderquerypub');
//        $ret = $wxOrderQuery->orderQuery('100007182_1444990829');
//
//        if ($ret == false) {
//            echo "订单查询失败";
//        } else {
//            echo "订单查询成功 ".$ret;
//        }

    }
}