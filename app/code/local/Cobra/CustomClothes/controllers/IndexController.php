<?php

class Cobra_CustomClothes_IndexController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
    	$this->loadLayout();
    	$this->renderLayout();
    }

   
    
    public function allAction()
    {
    	header("Content-Type:text/html;charset=utf-8");
    	$customer = Mage::getSingleton('customer/session')->getCustomer();
    	$customerId = $customer->getId();
    	$mainData = Mage::getModel('customclothes/temp')->getDataByCustomerId($customerId);
    	$secondData = Mage::getModel('customclothes/tempInfo')->getDataByCustomerId($customerId);
    	if($mainData){
    		$mainData['fontColor'] = $mainData['font_color'];
    		$mainData['team'] = $mainData['team_name'];
    		$mainData['style'] = $mainData['font_style'];
    	}
    	
    	if($secondData){
    		foreach ($secondData as $key => $data){
    			$data['size'] = $data['clothes_size'];
    			$data['size2'] = $data['pants_size'];
    			$data['player'] = $data['member_name'];
    			$data['num'] = $data['member_number'];
    			$secondData[$key] = $data;
    		}
    	}
    	
    	$return = array('mainData' => $mainData,'secondData' => $secondData);
    	echo json_encode($return);
    	exit;
    }

    
    
    public function deleteAction()
    {
    	if(!Mage::getSingleton( 'customer/session')->isLoggedIn()){
    		Mage::app()->getFrontController()->getResponse()->setRedirect(Mage::getUrl('customer/account'));
    		return;
    	}
    	
    	$customer = Mage::getSingleton('customer/session')->getCustomer();
    	$customerId = $customer->getId();
    	
    	$tempModel = Mage::getModel('customclothes/temp');
    	$tempModel->deleteByCustomerId($customerId);
    	
    	$tempInfoModel = Mage::getModel('customclothes/tempInfo');
    	$tempInfoModel->deleteByCustomerId($customerId);
    	
    	$cart = Mage::getSingleton('checkout/cart');
    	$customClothesModel = Mage::getModel('customclothes/customClothes');
    	$itemsCart = $cart->getItems();
    	if(count($itemsCart) > 0){
    		foreach ($itemsCart as $itemCart) {
    			$itemIdCart = $itemCart->getItemId();
    			$tmpId = $itemCart->getProduct()->getId();
    			$productInCart = Mage::getModel('catalog/product')->load($tmpId);
    			if($customClothesModel->checkCustomClothesByProdcut($productInCart)){
    				$cart->removeItem($itemIdCart);
    			}
    		}
    		$cart->save();
    	}
    	
    	echo "success";
    	exit;
    }

	public function agreeAction()
	{
		Mage::getSingleton('core/session')->setCustomerClothesAgree(1);
	}

}
