<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Checkout
 * @copyright   Copyright (c) 2013 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Checkout observer model
 *
 * @category   Mage
 * @package    Mage_Checkout
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Cobra_CustomClothes_Model_CustomClothes
{
	
// 	static $onlyCoat = 1;
// 	static $onlyPants = 2;
// 	static $suit = 3;
 	static $font = array(1 => 'font1',2 => 'font2',3 => 'font3');
	static $fontStr = array(1 => '汉仪楷体',2 => '汉仪粗圆',3 => '宋体');

 	static $fontStyle = array(1 => '常规',2 => '曲线');

 	static $fontStyleImageUrl = array(1 => 's',2 => 'c');

	static $attributeArray = array('M' => 124,'L' => 125, 'XL' => 122,
		'2XL' => 128,'3XL' => 127, '4XL' => 126
	);

	static $attributeId = 2118;

	static $supportProductSku = array(20039,20041);
	
	static $typeArray = array('clothes','pants');
	
	static $exportDir = "/alidata/custommade/customclothes/";
	
	static $template = array(
				'single' => 'http://s7d5.scene7.com/is/image/sneakerhead/form_clothes_stencil?$style_clothes(990x1780)$&',
				'double' => 'http://s7d5.scene7.com/is/image/sneakerhead/form_set_stencil?$style_clothes(990x1780)$&');
	
	public function getTemplate($mainData,$secondData,$productId){
		
		$imageUrl = '';
		$productModel = Mage::getModel('catalog/product');
		$currentProduct = $productModel->load($productId);
		$productSku = $currentProduct->getSku();
		if($mainData->double == 1){
			$imageUrl = self::$template['double'];
		}else{
			$imageUrl = self::$template['single'];
		}
		
		$mainStr = "";
		
		$value = "\$style=".urlencode($this->getFontStyle($mainData->style))."&";
		$mainStr .= $value;
		
		$value = "\$name=".urlencode(trim($mainData->team))."&";
		$mainStr .= $value;
		
		$value = "\$color=".urlencode($mainData->color)."&";
		$mainStr .= $value;
		
		$value = "\$font=".urlencode($this->getFontStr($mainData->font))."&";
		$mainStr .= $value;
		
		$value = "\$font_color=".urlencode($mainData->fontColor)."&";
		$mainStr .= $value;

		$value = "\$total_membership=".count($secondData)."&";
		$mainStr .= $value;
		
		//clothes_front
		$clothesFrontImage = urlencode("img-".$currentProduct->getUrlKey()."-1");
		$value = "\$clothes_front=".$clothesFrontImage."&";
		$mainStr .= $value;
		
		//clothes_back
		$clothesBackImage = urlencode("img-".$currentProduct->getUrlKey()."-2");
		$value = "\$clothes_back=".$clothesBackImage."&";
		$mainStr .= $value;
		
		//font_title
		$fontTitle = $this->getImageUrl($mainData,$secondData[0] ,"team");
		if ($fontTitle) {
			$value = "\$font_title=".$fontTitle."&";
			$mainStr .= $value;
		}
		//font_name
		$fontName = $this->getImageUrl($mainData, $secondData[0], "name");
		if ($fontName) {
			$value = "\$font_name=" . $fontName . "&";
			$mainStr .= $value;
		}
		
		//font_num_1
		$fontNumber = $this->getImageUrl($mainData, $secondData[0], "num", 'clothes');
		if ($fontNumber) {
			$value = "\$font_num_1=" . $fontNumber . "&";
			$mainStr .= $value;
		}
		
		//font_num_2
		$value = "\$font_num_2=".$fontNumber."&";
		$mainStr .= $value;
		
		//price
		$pantsSku = '';
		$pantsProductId = '';
		$pantsPrice = 0;
		
		$clothesPrice = $currentProduct->getPrice();
		if($mainData->double == 1){
			$pantsSku = $currentProduct->getPantsSku();
			$pantsProductId = $productModel->getResource()->getIdBySku($pantsSku);
			$configurableProduct = $productModel->load($pantsProductId);
			$pantsPrice = $configurableProduct->getPrice();
			
			//pants_front
			$pantsFrontImage = urlencode("img-".$configurableProduct->getUrlKey()."-1");
			$value = "\$pants_front=".$pantsFrontImage."&";
			$mainStr .= $value;
			
			//pants_num
			$pantsNumber = $this->getImageUrl($mainData,$secondData[0] ,"num",'pants');
			if ($pantsNumber) {
				$value = "\$pants_num=" . $pantsNumber . "&";
				$mainStr .= $value;
			}
			
		}
		$priceAll = ($clothesPrice + $pantsPrice) * count($secondData);
		$priceAll = number_format($priceAll, 2, '.', '');
		$value = "\$money=".urlencode($priceAll)."&";
		$mainStr .= $value;
		
		$str = '';
		$i = 1;
		foreach ($secondData as $data){
			$value = "\$name".$i."=".urlencode($data->player)."&";
			$str .= $value;
			$value = "\$number".$i."=".$data->num."&";
			$str .= $value;
			$value = "\$size".$i."=".$data->size."&";
			$str .= $value;
			if($data->size2 != ''){
				$value = "\$pants_size".$i."=".$data->size2."&";
				$str .= $value;
			}
			$i++;
		}
		
		$str = substr($str, 0,-1);
		$imageUrl .= $mainStr.$str;

		return $imageUrl;
	}
	
	public function getImageUrl($mainData,$firstData,$titleType,$type = ''){
		
		$url = urlencode($this->getFont($mainData->font));
		$title = "";
		if($titleType == 'team'){
			$title = "title";
		}
		if($titleType == 'name'){
			$title = "name";
		}
		if($titleType == 'num'){
			$title = "num";
		}
		$url.= "-".urlencode($title);
		
		$colorArray = array('白色'=>'white','灰色'=>'gray','黑色'=>'black','黄色'=>'yellow','深蓝'=>'navy','橙色'=>'orange','红色'=>'red');
		$fontColor = $mainData->fontColor;
		$color = $colorArray[$fontColor];
		$url.= "-".urlencode($color);
		
		//font_style
		
		if($titleType == 'num'){
			$style = $this->getFontStyleImageUrl(1);
		}else{
			$style = $this->getFontStyleImageUrl($mainData->style);
		}
		$url.= "-".urlencode($style);
		
		//font_length
		$lengthUrl = "";
		if($titleType == 'team' && $mainData->team){
			$length = mb_strlen(trim($mainData->team),'GB2312');
			$teamFontRangeArray = $this->getTeamFontRangeArray();
			$teamFontImageUrlArray = $this->getTeamFontImageUrl();
			$range = 0;
			foreach ($teamFontRangeArray as $key => $value){
				if($length > $key){
					$range = $value;
					break;
				}
			}
			$lengthUrl = $teamFontImageUrlArray[$range];
		}
		
		if($titleType == 'name' && $firstData->player){
			$length = mb_strlen(trim($firstData->player),'GB2312');
			$memberFontRangeArray = $this->getMemberFontRangeArray();
			$memberFontImageUrlArray = $this->getMemberFontImageUrl();
			$range = 0;
			foreach ($memberFontRangeArray as $key => $value){
				if($length > $key){
					$range = $value;
					break;
				}
			}
			$lengthUrl = $memberFontImageUrlArray[$range];
		}
		
		if($titleType == 'num' && $firstData->num){
			$lengthUrl = strlen(intval($firstData->num));
		}

		if (!$lengthUrl) {
			return "null";
		}

		$url.= "-".urlencode($lengthUrl);
		
		if($type == 'clothes'){
			$url.= "-".urlencode("big");
		}
		
		if($type == 'pants'){
			$url.= "-".urlencode("small");
		}
		
		return urlencode($url);
	}
	
	public function getFont($font){
		return self::$font[$font];
	}

	public function getFontStr($font){
		return self::$fontStr[$font];
	}

	public function getFontStyle($fontStyle){
		return self::$fontStyle[$fontStyle];
	}
	
	public function getFontStyleImageUrl($fontStyle){
		return self::$fontStyleImageUrl[$fontStyle];
	}
	
	public function checkCustomClothes($productId){
		
		$product = Mage::getModel('catalog/product')
		->setStoreId(Mage::app()->getStore()->getId())
		->load($productId);
		
		return $this->checkCustomClothesByProdcut($product);
	}
	
	public function checkCustomClothesByProdcut($product){
	
		$attributeSetModel = Mage::getModel("eav/entity_attribute_set");
		$attributeSetModel->load($product->getAttributeSetId());
		$attributeSetName = $attributeSetModel->getAttributeSetName();
		if($attributeSetName == 'customclothes'){
			return true;
		}
		return false;
	}
	
	public function getCustomClothesType($product){
		$skuArray = explode('-',$product->getSku());
		if(in_array($skuArray[0], self::$supportProductSku)){
			return self::$typeArray[0];
		}
		return self::$typeArray[1];
	}
	
	public function getCustomClothesSizeAttribute($size){
		$return = array();
		$return[self::$attributeId] = self::$attributeArray[$size];
		return $return;
	}
	
	
	public function updateCustomClothesOrderStatus($orderId){
		//get order item;
		$order = Mage::getModel('sales/order')->loadByIncrementId($orderId);
		$orderItems = $order->getItemsCollection();
		$orderClothesSkuArray = array();
		foreach ($orderItems as $item){
			$product = $item->getProduct();
			if(strtolower($product->getTypeId()) == 'simple' && Mage::getModel('customclothes/customClothes')->checkCustomClothesByProdcut($product)){
				$orderClothesSku[] = $product->getSku();
			}
		}
		
		if($orderClothesSku){
			$message = $this->checkOrderSku($orderClothesSku,$orderId);
			$customClothesOrderModel = Mage::getModel('customclothes/order');
			$orderRow = $customClothesOrderModel->loadByIncrementId($orderId);
			if(!$orderRow){
				$message = "The orderId ".$orderId." is not exist";
			}else{
				if($orderRow->getStatus() != 1){
					$message = "The orderId ".$orderId." is not wait for pay";
				}else{
					$id = $orderRow->getId();
					$model = Mage::getModel('customclothes/order')->load($id);
					if($message == ""){
						$model->pay();
					}else{
						$model->error();
					}
				}
			}
			
		}else{
			$message = "";
		}
		return $message;
		
	}
	
	public function checkOrderSku($skuArray,$orderId){

		$message = "";
		$orderInfoModel = Mage::getModel('customclothes/orderInfo');
		$orderInfoData = $orderInfoModel->getDataByOrderId($orderId);
		$missSku = array();
				
		if($orderInfoData){
			foreach ($orderInfoData as $data){
				if($data->pants_sku != '' && !in_array($data->pants_sku, $skuArray)){
					$missSku[] = $data->pants_sku;
				}
				if($data->clothes_sku != '' && !in_array($data->clothes_sku, $skuArray)){
					$missSku[] = $data->clothes_sku;
				}
			}
			if($missSku){
				$message = "The orderId ".$orderId." The Miss sku is ";
				foreach ($missSku as $sku){
					$message.= $sku.",";
				}
				$message = substr($message, 0,-1);
			}
		}else{
			$message = "The orderId ".$orderId." The Miss sku is ";
			foreach ($skuArray as $sku){
				$message.= $sku.",";
			}
			$message = substr($message, 0,-1);
		}
		
		return $message;
	}
	
// 	public function getCustomClothesSku($productId,$customClothesSize = 'L'){
// 		$product = Mage::getModel('catalog/product')
// 		->setStoreId(Mage::app()->getStore()->getId())
// 		->load($productId);
// 		$sku = $product->getSku();
		
// 	    $arr = explode('-', $sku);
//         $size = end($arr);
//         $return = substr($sku, 0, -(strlen($size) + 1)).'-'.$customClothesSize;
		
// 		if($product->getIsCustomClothes() == self::$suit){
// 			$return = array();
// 			$return[0] = $skuArray[0].'-'.$skuArray[2].'-'.$customClothesSize;
// 			$return[1] = $skuArray[1].'-'.$skuArray[2].'-'.$customClothesSize;
// 		}else{
// 			$return = substr($sku, 0, -(strlen($size) + 1)).'-'.$customClothesSize;
// 		}
// 		return $return;
// 	}
	
	
	public function getCustomClothesSimpleProductsBySku($sku){
		$return = array();
		return $return;
	}
    
	public function getProductColorArrayBySku($sku){
		
		$skuArray = explode("-",$sku);
		$productIdArray = array();
		$returnArray = array();
		if($skuArray[0] == '20039'){
			$colorArray = array('20039-02c'=>'白色','20039-01c'=>'黑色','20039-05c'=>'蓝色','20039-19c'=>'粉色');		 
		}elseif($skuArray[0] == '20041'){
			$colorArray = array('20041-01c'=>'黑色','20041-02c'=>'白色','20041-04c'=>'黄色','20041-05c'=>'蓝色',
					'20041-07c'=>'灰色','20041-09c'=>'橙色','20041-11c'=>'深蓝');
		}
		$productModel = Mage::getModel('catalog/product');
		foreach ($colorArray as $skuTmp => $color){
			$id = Mage::getModel('catalog/product')->getResource()->getIdBySku($skuTmp);
			$configurableProduct = $productModel->load($id);
			if($configurableProduct->getStockItem()->getIsInStock() == 1){
				$returnArray[$id]['color'] = $colorArray[$skuTmp];
				$returnArray[$id]['sku'] = $skuTmp;
			}
		}
		
		return $returnArray;
		
	}

	public function getProductSizeOption(){

//		$returnArray = array();
//		$returnArray[0]['value'] = "124";
//		$returnArray[0]['size'] = "M";
//		$returnArray[0]['sizeStr'] = "M";
//
//		$returnArray[1]['value'] = "125";
//		$returnArray[1]['size'] = "L";
//		$returnArray[1]['sizeStr'] = "L";
//
//		$returnArray[2]['value'] = "122";
//		$returnArray[2]['size'] = "XL";
//		$returnArray[2]['sizeStr'] = "XL";
//
//		$returnArray[3]['value'] = "128";
//		$returnArray[3]['size'] = "2XL";
//		$returnArray[3]['sizeStr'] = "2XL";
//
//		$returnArray[4]['value'] = "127";
//		$returnArray[4]['size'] = "3XL";
//		$returnArray[4]['sizeStr'] = "3XL";
//
//		$returnArray[5]['value'] = "126";
//		$returnArray[5]['size'] = "4XL";
//		$returnArray[5]['sizeStr'] = "4XL";

		return self::$attributeArray;

	}
	
	public function getTeamFontImageUrl(){
		return array("12"=>10,"14"=>9,"16"=>8,"18"=>7,"22"=>6,"26"=>5,"32"=>4,"40"=>3);
	}
	
	public function getMemberFontImageUrl(){
		return array("10"=>10,"12"=>9,"14"=>8,"16"=>7,"18"=>6,"20"=>5,"26"=>4,"34"=>3);
	}
	
	public function getTeamFontRangeArray(){
		return array("18"=>12,"16"=>14,"14"=>16,"12"=>18,"10"=>22,"8"=>26,"6"=>32,"0"=>40);
	}

	public function getMemberFontRangeArray(){
		return array("18"=>10,"16"=>12,"14"=>14,"12"=>16,"10"=>18,"8"=>20,"6"=>26,"0"=>34);
	}
}
