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

    public function getTokenAction(){
        $ret = array();
        $wechatObj = Mage::getModel('weixindev/wxbase');
        $result = $wechatObj->getAccessTokenArray();

        if($result && $result['access_token']){
            $ret['access_token'] = $result['access_token'];
            $ret['create_time'] = $result['createtime'];
        } else {
            $ret = $result;
        }
        
        echo json_encode($ret);
    }
}