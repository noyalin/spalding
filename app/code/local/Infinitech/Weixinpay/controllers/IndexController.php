<?php
class Infinitech_Weixinpay_IndexController extends Mage_Core_Controller_Front_Action{
    public function indexAction(){
//        $a = Mage::helper('helloworld');
//        echo "YES";
//        $this->loadLayout();
//        $this->renderLayout();
//        $weixinpay = Mage::getModel('weixinpay/pay');
//        echo $weixinpay->getAppId();
        echo Infinitech_Weixinpay_Model_Wxpaypubconfig :: getAppId('app_id');
    }
}