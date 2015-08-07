<?php
define("TOKEN", "sneakerhead");
class Devicom_Weixindev_IndexController extends Mage_Core_Controller_Front_Action{
    public function indexAction(){
        $wechatObj = Mage::getModel('weixindev/wxbase');
        $str = $this->_request->getParam('echostr');
        if (isset($str)) {
            $wechatObj->valid($str);
        }else{
            $wechatObj->responseMsg();
        }
    }
}