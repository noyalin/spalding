<?php

class Cobra_CustomMade_Block_View extends Mage_Catalog_Block_Product_View
{
    private $sizeValue = 0;
    private $customValue = 0;
    private $price;

    public function __construct(array $args = array())
    {
        $customTypeP1 = '无';
        $customTypeP2 = '无';
        $attributes = null;

        if ($this->getTypeP1() == 1) {
            $customTypeP1 = '图片';
        } elseif ($this->getTypeP1() == 2) {
            $customTypeP1 = '文字';
        }

        if ($this->getTypeP2() == 1) {
            $customTypeP2 = '图片';
        } elseif ($this->getTypeP2() == 2) {
            $customTypeP2 = '文字';
        }
        $customType = $customTypeP1 . '-' . $customTypeP2;

        $_product = $this->getProduct();
        $allowAttributes = $_product->getTypeInstance(true)->getConfigurableAttributes($_product);
        $_attributes = Mage::helper('core')->decorateArray($allowAttributes);

        foreach ($_attributes as $_attribute) {
            $attrName = $_attribute->getLabel();
            $attrValueArr = $_attribute->getPrices();
            $attrId = $_attribute->getAttributeId();
            if ($attrName == '篮球型号') {
                foreach ($attrValueArr as $optionValue) {
                    $this->sizeValue = $optionValue['value_index'];
                }
                $attributes[$attrId] = $this->sizeValue;
            } elseif ($attrName == '定制类型') {
                foreach ($attrValueArr as $optionValue) {
                    if ($optionValue['label'] == $customType) {
                        $this->customValue = $optionValue['value_index'];
                    }
                }
                $attributes[$attrId] = $this->customValue;
            }
        }
        $subProduct = Mage::getSingleton('catalog/product_type_configurable')->getProductByAttributes($attributes, $_product);
        $product = Mage::getModel('catalog/product')->loadByAttribute('sku', $subProduct->getSku());
        $this->price = $product->getPrice();
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

    public function getSize()
    {
        return $this->sizeValue;
    }

    public function getCustomType()
    {
        return $this->customValue;
    }

    public function getPrice()
    {
        return $this->price;
    }

}
