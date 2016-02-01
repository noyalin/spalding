<?php

class Devicom_Product_Helper_Data extends Mage_Core_Helper_Abstract
{
	
	public function getProductImageUrl($product , $tag)
	{
		return Mage::getModel('core/variable')->loadByCode('aliyun_skin_images')->getValue('html') . $product->getSku() . '/' . $product->getUrlKey() . '-'. $tag .'.jpg';
	}
	
}
