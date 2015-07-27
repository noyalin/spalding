<?php

class Cobra_CustomMade_Block_View extends Mage_Catalog_Block_Product_View
{
    public function getStep()
    {
        return Mage::getSingleton('core/session')->getData('step');
    }

}