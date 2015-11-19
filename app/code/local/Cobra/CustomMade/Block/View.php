<?php

class Cobra_CustomMade_Block_View extends Mage_Catalog_Block_Product_View
{
    private $sizeId = 0;
    private $sizeValue = 0;
    private $customId = 0;
    private $customValue = null;
//    private $price;
    private $customerId;
    private $session;

    public function initView()
    {
        $_product = $this->getProduct();
        if (Mage::getSingleton('core/session')->getCustomerId()) {
            $this->customerId = Mage::getSingleton('core/session')->getCustomerId();
            Mage::log('CustomMade initView--aaaaaaaa--customerId='.$this->customerId);
        } else {
            $this->customerId = Mage::getModel('custommade/customer')->createCustomer();
            Mage::getSingleton('core/session')->setCustomerId($this->customerId);
            Mage::log('CustomMade initView--bbbbbbb--customerId='.$this->customerId);
        }
//        Mage::getSingleton('core/session')->setCurrentSku($_product->getSku());
        $this->session = Mage::getModel('custommade/session')->getSession($this->customerId, $_product->getSku());
        Mage::getSingleton('customer/session')->setBeforeAuthUrl(Mage::helper('core/url')->getCurrentUrl());
        Mage::log('CustomMade initView----customerId='.$this->customerId);
    }

    public function initOptions()
    {
        $attributes = null;

        $_product = $this->getProduct();
        $allowAttributes = $_product->getTypeInstance(true)->getConfigurableAttributes($_product);
        $_attributes = Mage::helper('core')->decorateArray($allowAttributes);

        //属性设置
        foreach ($_attributes as $_attribute) {
            $attrName = $_attribute->getLabel();
            $attrValueArr = $_attribute->getPrices();
            if ($attrName == '篮球型号') {
                $this->sizeId = $_attribute->getAttributeId();
                foreach ($attrValueArr as $optionValue) {
                    $this->sizeValue = $optionValue['value_index'];
                }
                $attributes[$this->sizeId] = $this->sizeValue;
            } elseif ($attrName == '定制数量') {
                $this->customId = $_attribute->getAttributeId();
                $i = 0;
                foreach ($attrValueArr as $optionValue) {
                    $this->customValue[$i++] = $optionValue['value_index'];
                }
            }
        }
//        $subProduct = Mage::getSingleton('catalog/product_type_configurable')->getProductByAttributes($attributes, $_product);
//        $product = Mage::getModel('catalog/product')->loadByAttribute('sku', $subProduct->getSku());
//        $this->price = $product->getPrice();
    }

    public function getCustomStatus()
    {
        return $this->session->getCustomStatus();
    }

    public function getPos()
    {
        return $this->session->getPos();
    }

    public function getTypeP1()
    {
        return $this->session->getTypeP1();
    }

    public function getContent1P1()
    {
        return $this->session->getContent1P1();
    }

    public function getContent2P1()
    {
        return $this->session->getContent2P1();
    }

    public function getContent3P1()
    {
        return $this->session->getContent3P1();
    }

    public function getContent4P1()
    {
        return $this->session->getContent4P1();
    }

    public function getTypeP2()
    {
        return $this->session->getTypeP2();
    }

    public function getContent1P2()
    {
        return $this->session->getContent1P2();
    }

    public function getContent2P2()
    {
        return $this->session->getContent2P2();
    }

    public function getContent3P2()
    {
        return $this->session->getContent3P2();
    }

    public function getContent4P2()
    {
        return $this->session->getContent4P2();
    }

    public function getTestMode()
    {
        return Mage::getSingleton('core/session')->getTestMode();
    }

    public function getCustommadeAgree()
    {
        return Mage::getSingleton('core/session')->getCustomermadeAgree();
    }

    public function getSizeClassP1()
    {
        $content3 = $this->getContent3P1();
        return $this->getSizeClass($content3);
    }

    public function getSizeClassP2()
    {
        $content3 = $this->getContent3P2();
        return $this->getSizeClass($content3);
    }

    private  function  getSizeClass($content3) {
        if ($content3 == "3") {
            $className = "size_80";
        } elseif ($content3 == "2") {
            $className = "size_60";
        } else {
            $className = "size_40";
        }
        return $className;
    }

    public function getSizeId()
    {
        return $this->sizeId;
    }

    public function getSizeValue()
    {
        return $this->sizeValue;
    }

    public function getCustomId()
    {
        return $this->customId;
    }

    public function getCustomValue()
    {
        return $this->customValue;
    }

//    public function getPrice()
//    {
//        return $this->price;
//    }

}