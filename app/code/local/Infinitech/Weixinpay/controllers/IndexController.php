<?php
class Infinitech_Weixinpay_IndexController extends Mage_Core_Controller_Front_Action{
    public function indexAction(){
//        $a = Mage::helper('helloworld');
//        echo "YES";
//        $this->loadLayout();
//        $this->renderLayout();
//        $weixinpay = Mage::getModel('weixinpay/pay');
//        echo $weixinpay->getAppId();
        // echo Infinitech_Weixinpay_Model_Wxpaypubconfig :: getCode('app_id');
    }

    public function testAction(){
        $url = 'http://www.baidu.com/';
        $queryStr = $_SERVER['QUERY_STRING'];
        if($queryStr != ''){
            $url.= "?".$queryStr;
        }
        $this->_redirectUrl($url);
        return;

    }

    public function h5Action(){
        $url = 'http://h5.spalding.com.cn/auth.php';
        $queryStr = $_SERVER['QUERY_STRING'];
        if($queryStr != ''){
            $url.= "?".$queryStr;
        }
        $this->_redirectUrl($url);
        return;
    }
}
