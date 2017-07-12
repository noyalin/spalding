<?php

class Cobra_CustomClothes_Block_View extends Mage_Catalog_Block_Product_View
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
            Mage::log('CustomClothes initView--aaaaaaaa--customerId='.$this->customerId);
        } else {
            $this->customerId = Mage::getModel('customclothes/customer')->createCustomer();
            Mage::getSingleton('core/session')->setCustomerId($this->customerId);
            Mage::log('CustomClothes initView--bbbbbbb--customerId='.$this->customerId);
        }
//        Mage::getSingleton('core/session')->setCurrentSku($_product->getSku());
        $this->session = Mage::getModel('customclothes/session')->getSession($this->customerId, $_product->getSku());
        Mage::getSingleton('customer/session')->setBeforeAuthUrl(Mage::helper('core/url')->getCurrentUrl());
        Mage::log('CustomClothes initView----customerId='.$this->customerId);
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
            if ($attrName == '球衣型号') {
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

    //P1表示球第一面 1图片 2文字
    public function getTypeP1()
    {
        return $this->session->getTypeP1();
    }

    // 第一行内容
    public function getContent1P1()
    {
        return $this->session->getContent1P1();
    }

    // 双行显示的文字内容
    public function getContent2P1()
    {
        return $this->session->getContent2P1();
    }

    // 字体
    public function getContent3P1()
    {
        return $this->session->getContent3P1();
    }

    // P1字号
    public function getContent4P1()
    {
        return $this->session->getContent4P1();
    }
    //P2表示球第二面文字和pic
    public function getTypeP2()
    {
        return $this->session->getTypeP2();
    }
    // 内容
    public function getContent1P2()
    {
        return $this->session->getContent1P2();
    }
    // 双行显示内容
    public function getContent2P2()
    {
        return $this->session->getContent2P2();
    }
    // 字体类型
    public function getContent3P2()
    {
        return $this->session->getContent3P2();
    }
    //字号
    public function getContent4P2()
    {
        return $this->session->getContent4P2();
    }

    public function getTestMode()
    {
        return Mage::getSingleton('core/session')->getTestMode();
    }

    public function getCustomClothesAgree()
    {
        return Mage::getSingleton('core/session')->getCustomClothesAgree();
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

    public function getP1Status()
    {
        $type_p1 = $this->session->getTypeP1();
        if ($type_p1 == 1 || $type_p1 == 2) {
            return $type_p1;
        } else {
            return 0;
        }
    }

    public function getP2Status()
    {
        $type_p2 = $this->session->getTypeP2();
        if ($type_p2 == 1 || $type_p2 == 2) {
            return $type_p2;
        } else {
            return 0;
        }
    }


    public function getP1Font()
    {
        return $this->getFontString($this->getContent4P1());
    }

    public function getP2Font()
    {
        return $this->getFontString($this->getContent4P2());
    }

    public function getFontString($font){
        if ($font == 0) {
            return 'style="font-family: Conv_CustomGrotesque-Regular;letter-spacing:0"';
        } elseif ($font == 2) {
            return 'style="font-family: Arial;letter-spacing:-4px"';
        }elseif ($font == 3) {
            return 'style="font-family: 宋体;"';
        } elseif ($font == 4) {
            return 'style="font-family: 楷体;"';
        }

    }


    public function getCustomerClothesAgree()
    {
        return Mage::getSingleton('core/session')->getCustomerClothesAgree();
    }

}