<?php

class Cobra_CustomMade_Block_View extends Mage_Catalog_Block_Product_View
{
    private $sizeId = 0;
    private $sizeValue = 0;
//    private $customId = 0;
//    private $customValue = 0;
//    private $price;

    public function initView()
    {
//        $customTypeP1 = '无';
//        $customTypeP2 = '无';
        $attributes = null;

//        if ($this->getTypeP1() == 1) {
//            $customTypeP1 = '图片';
//        } elseif ($this->getTypeP1() == 2) {
//            $customTypeP1 = '文字';
//        }
//
//        if ($this->getTypeP2() == 1) {
//            $customTypeP2 = '图片';
//        } elseif ($this->getTypeP2() == 2) {
//            $customTypeP2 = '文字';
//        }
//        $customType = $customTypeP1 . '-' . $customTypeP2;

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
            }
//            elseif ($attrName == '定制类型') {
//                $this->customId = $_attribute->getAttributeId();
//                foreach ($attrValueArr as $optionValue) {
//                    if ($optionValue['label'] == $customType) {
//                        $this->customValue = $optionValue['value_index'];
//                    }
//                }
//                $attributes[$this->customId] = $this->customValue;
//            }
        }
//        $subProduct = Mage::getSingleton('catalog/product_type_configurable')->getProductByAttributes($attributes, $_product);
//        $product = Mage::getModel('catalog/product')->loadByAttribute('sku', $subProduct->getSku());
//        $this->price = $product->getPrice();
    }

    public function getCustomStatus()
    {
        return Mage::getSingleton('core/session')->getCustomStatus();
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

    public function getContent3P1()
    {
        return Mage::getSingleton('core/session')->getContent3P1();
    }

    public function getContent4P1()
    {
        return Mage::getSingleton('core/session')->getContent4P1();
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

    public function getContent3P2()
    {
        return Mage::getSingleton('core/session')->getContent3P2();
    }

    public function getContent4P2()
    {
        return Mage::getSingleton('core/session')->getContent4P2();
    }

    public function getTestMode()
    {
        return Mage::getSingleton('core/session')->getTestMode();
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

//    public function getCustomId()
//    {
//        return $this->customId;
//    }
//
//    public function getCustomValue()
//    {
//        return $this->customValue;
//    }

//    public function getPrice()
//    {
//        return $this->price;
//    }

}