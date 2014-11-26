<?php

class WP_Quickshopaddtocomparefix_Model_Observer
{
    public function catalogProductCompareAddProduct($observer)
    {
        $referrerUrl = Mage::app()->getRequest()->getServer('HTTP_REFERER');
        if (strpos($referrerUrl, '/quickshop/'))
        {
            Mage::helper('catalog/product_compare')->calculate();
            $product = $observer->getProduct();
            $refererUrl = Mage::helper('catalog/product')->getProductUrl($product);
            Mage::app()->getResponse()->setRedirect($refererUrl);
            Mage::app()->getResponse()->sendResponse();
            die();
        }
    }

    public function checkRefererUrl($observer)
    {
        $lastValidReferrer = Mage::getSingleton('core/session')->getWpLastValidReferrer();
        $referrerUrl = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : Mage::getBaseUrl();
        // ---
        if (strpos($referrerUrl, '/quickshop/')) {
            $_SERVER['HTTP_REFERER'] = $lastValidReferrer;
            Mage::getSingleton('checkout/session')->setContinueShoppingUrl($lastValidReferrer);
        } else {
            Mage::getSingleton('core/session')->setWpLastValidReferrer($referrerUrl);
        }
    }
}
