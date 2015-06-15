<?php
class Devicom_Weixinevent_Block_Event1 extends Mage_Payment_Block_Form
{
    public function getPromotion()
    {
        return Mage::getSingleton('weixinevent/promotion')->getPromotion();
    }

    public function getOpenId(){
        return Mage::getSingleton('weixinevent/promotion')->getOpenId();
    }

    public function getCodeImage(){
        $orderId = Mage::getSingleton('customer/session')->getOrderId();
        $actId = Mage::getSingleton('customer/session')->getActId();
        $url = "http://localhost/spalding/weixinevent/index/index/oid/".$orderId."/aid/".$actId;
        QRCode_Code::createCode($url);
    }

    public function getVar()
    {
        return Mage::getSingleton('weixinevent/promotion')->getVar();
    }
}