<?php

class Cobra_CustomMade_Block_View extends Mage_Catalog_Block_Product_View
{
    public function getStep_P1()
    {
        return Mage::getSingleton('core/session')->getData('step_p1');
    }

    public function getStep_P2()
    {
        return Mage::getSingleton('core/session')->getData('step_p2');
    }

    public function getImgUrl_P1()
    {
        $url = $this->getSkinUrl("images/customMade/imgPer_1.jpg");
        return $url;
    }

    public function getImgUrl_P2()
    {
        $url = $this->getSkinUrl("images/customMade/imgPer_2.jpg");
        return $url;
    }
}