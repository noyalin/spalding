<?php

class Cobra_CustomMade_Block_View extends Mage_Catalog_Block_Product_View
{
    public function getStatus()
    {
        return Mage::getSingleton('core/session')->getStatus();
    }

    public function getPos()
    {
        return Mage::getSingleton('core/session')->getPos();
    }

    public function getTypeP1()
    {
        return Mage::getSingleton('core/session')->getTypeP1();
    }

    public function getContent1P1()
    {
        return Mage::getSingleton('core/session')->getContent1P1();
    }

    public function getContent2P1()
    {
        return Mage::getSingleton('core/session')->getContent2P1();
    }

    public function getTypeP2()
    {
        return Mage::getSingleton('core/session')->getTypeP2();
    }

    public function getContent1P2()
    {
        return Mage::getSingleton('core/session')->getContent1P2();
    }

    public function getContent2P2()
    {
        return Mage::getSingleton('core/session')->getContent2P2();
    }

//    public function getImgUrl_P1()
//    {
//        $url = $this->getSkinUrl("images/customMade/imgPer_1.jpg");
//        return $url;
//    }
//
//    public function getImgUrl_P2()
//    {
//        $url = $this->getSkinUrl("images/customMade/imgPer_2.jpg");
//        return $url;
//    }
}