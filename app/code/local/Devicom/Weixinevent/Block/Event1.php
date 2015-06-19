<?php
class Devicom_Weixinevent_Block_Event1 extends Mage_Payment_Block_Form
{
    public function isPromotioned()
    {
        return Mage::getSingleton('weixinevent/promotion')->isPromotioned();
    }

    public function getPromotionCount()
    {
        return Mage::getSingleton('weixinevent/promotion')->getPromotionCount();
    }

//    public function getOpenId(){
//        return Mage::getSingleton('weixinevent/promotion')->getOpenId();
//    }

    public function getVar()
    {
        return Mage::getSingleton('weixinevent/promotion')->getVar();
    }

    public function isSponsor(){
        return Mage::getSingleton('weixinevent/promotion')->isSponsor();
    }


    public function hasSponsor(){
        return Mage::getSingleton('weixinevent/promotion')->hasSponsor();
    }
}