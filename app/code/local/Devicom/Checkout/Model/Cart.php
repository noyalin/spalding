<?php

class Devicom_Checkout_Model_Cart extends Mage_Checkout_Model_Cart
{
	
    public function addProduct($productInfo, $requestInfo=null)
    {
        $product = $this->_getProduct($productInfo);
        $request = $this->_getProductRequest($requestInfo);

        $productId = $product->getId();
        $categoryIds = $product->getCategoryIds();

        if ($this->checkCustomMade($product)) {
            $customer_id = Mage::getSingleton('customer/session')->getCustomer()->getId();
            Mage::getModel('custommade/temp')->saveCustomMadeTemp($customer_id, $request->getSubSku());
        }


        if ($product->getStockItem()) {
            $minimumQty = $product->getStockItem()->getMinSaleQty();
            //If product was not found in cart and there is set minimal qty for it
            if ($minimumQty && $minimumQty > 0 && $request->getQty() < $minimumQty
                && !$this->getQuote()->hasProductId($productId)
            ){
                $request->setQty($minimumQty);
            }
        }

        if ($productId) {
            try {
                $result = $this->getQuote()->addProduct($product, $request);
            } catch (Mage_Core_Exception $e) {
                $this->getCheckoutSession()->setUseNotice(false);
                $result = $e->getMessage();
            }
	    
            /**
             * String we can get if prepare process has error
             */
//PCN BEGIN
//Removed to prevent redirect to product detail to show error message
            if (is_string($result)) {
                $redirectUrl = Mage::getUrl('checkout/cart/', array('_secure'=>true));
                $this->getCheckoutSession()->setRedirectUrl($redirectUrl);
                if ($this->getCheckoutSession()->getUseNotice() === null) {
                    $this->getCheckoutSession()->setUseNotice(true);
                }
                Mage::throwException($result);
            }
// PCN END
        } else {
            Mage::throwException(Mage::helper('checkout')->__('The product does not exist.'));
        }

        Mage::dispatchEvent('checkout_cart_product_add_after', array('quote_item' => $result, 'product' => $product));
        $this->getCheckoutSession()->setLastAddedProductId($productId);

        //???????????????????????????
        if ($this->checkCustomMade($product)) {
            //??????????????????????????????
            //remove from cart
            $cartHelper = Mage::helper('checkout/cart');
            $itemsCart = $cartHelper->getCart()->getItems();
            $superAttribute = $requestInfo['super_attribute'];
            if (!empty($itemsCart)) {
                foreach ($itemsCart as $itemCart) {
                    $tmpId = $itemCart->getProduct()->getId();
                    if ($itemCart->getProduct()->getTypeID() == 'simple') {
                        $productTmp = Mage::getModel('catalog/product')->load($tmpId);
                        $optionIdTmp = $productTmp->getCustomBall();
                        if (in_array($optionIdTmp, $superAttribute)) {
                            $itemCart->setQty(1);
                            $cartHelper->getCart()->save();
                            continue;
                        }

                        $parentIds = Mage::getResourceSingleton('catalog/product_type_configurable')
                            ->getParentIdsByChild($tmpId);
                        $productParent = Mage::getModel('catalog/product')->load($parentIds[0]);
                        //$tmpCategoryIdArr = $productParent->getCategoryIds();
                        if ($this->checkCustomMade($productParent)) {
                            $simple_sku = $request->getSku() . $request->getSubSku();
                            if (isset($parentIds[0]) && $parentIds[0] == $productId && $itemCart->getSku() == $simple_sku) {
                                //?????????????????????????????????????????????????????????1
                                $itemCart->setQty(1);
                                $cartHelper->getCart()->save();
                            } else {
                                $itemIdCart = $itemCart->getItemId();
                                mage:: log($itemCart->getItemId() . '  remove id  ');
                                $cartHelper->getCart()->removeItem($itemIdCart)->save();
                                break;
                            }
                        }
                    } else {
                        $productTmp = Mage::getModel('catalog/product')->load($tmpId);
                        //$tmpCategoryIdArr = $productTmp->getCategoryIds();
                        if ($this->checkCustomMade($productTmp)) {
                            if ($tmpId == $productId) {
                                //?????????????????????????????????????????????????????????1
                                $itemCart->setQty(1);
                                $cartHelper->getCart()->save();
                            }
                        }
                    }
                }
            }
        }
        //????????????
        return $this;
    }
    
    /**
     * Update cart items information
     *
     * @param   array $data
     * @return  Mage_Checkout_Model_Cart
     */
    public function updateItems($data)
    {


        Mage::dispatchEvent('checkout_cart_update_items_before', array('cart'=>$this, 'info'=>$data));

        /* @var $messageFactory Mage_Core_Model_Message */
        $messageFactory = Mage::getSingleton('core/message');
        $session = $this->getCheckoutSession();
        $qtyRecalculatedFlag = false;
	
	foreach ($data as $itemId => $itemInfo) {
	    $item = $this->getQuote()->getItemById($itemId);
	    $storage[$item->getProduct()->getId()]['qty'] = 0;
	    $storage[$item->getProduct()->getId()]['notice'] = 0;
	}
	
	foreach ($data as $itemId => $itemInfo) {
	    $item = $this->getQuote()->getItemById($itemId);
	    $storage[$item->getProduct()->getId()]['qty'] += isset($itemInfo['qty']) ? (float) $itemInfo['qty'] : 0;
	}
	
        foreach ($data as $itemId => $itemInfo) {
	    $item = $this->getQuote()->getItemById($itemId);
            if (!$item) {
                continue;
            }

            if (!empty($itemInfo['remove']) || (isset($itemInfo['qty']) && $itemInfo['qty']=='0')) {
                $this->removeItem($itemId);
                continue;
            }

            $qty = isset($itemInfo['qty']) ? (float) $itemInfo['qty'] : false;

            if ($qty > 0) {

		//Limit one
		$productId = $item->getProduct()->getId();
		$product = $this->_getProduct($productId);
		//if ($product->getLimitQuantity()) {
		$productLimitQuantity = null;
		$productMaxQuantity = null;
		if ($product->getLimitQuantity() && $storage[$productId]['qty'] > $product->getLimitQuantity()) {
		    $item->setQty($item->getQty());
		    $productLimitQuantity = 1;
		} elseif (!$product->getLimitQuantity() && $storage[$productId]['qty'] > 99) {
		    $item->setQty($item->getQty());
		    $productMaxQuantity = 1;
		} else {
		    $item->setQty($qty);
		}
		//$item->getQty()
                $itemInQuote = $this->getQuote()->getItemById($item->getId());

		if (!$itemInQuote && $item->getHasError()) {
                    Mage::throwException($item->getMessage());
                }

		if ($productLimitQuantity && $storage[$productId]['notice'] == 0) {
		    $session->addNotice(
			Mage::helper('checkout')->__(Mage::helper('core')->escapeHtml($product->getName()) . ' ??????????????????%s???.', Mage::helper('core')->escapeHtml($product->getLimitQuantity()))
		    );
		    $storage[$productId]['notice'] = 1;
		} elseif ($productMaxQuantity && $storage[$productId]['notice'] == 0) {
		    $session->addNotice(
			Mage::helper('checkout')->__('%s ????????????????????????????????????99???.', Mage::helper('core')->escapeHtml($product->getName()))
		    );
		    $storage[$productId]['notice'] = 1;
		}
		
                if (isset($itemInfo['before_suggest_qty']) && ($itemInfo['before_suggest_qty'] != $qty)) {
                    $qtyRecalculatedFlag = true;
                    $message = $messageFactory->notice(Mage::helper('checkout')->__('Quantity was recalculated from %d to %d', $itemInfo['before_suggest_qty'], $qty));
                    $session->addQuoteItemMessage($item->getId(), $message);
                }
            }
        }

        if ($qtyRecalculatedFlag) {
            $session->addNotice(
                Mage::helper('checkout')->__('Some products quantities were recalculated because of quantity increment mismatch')
            );
        }

        Mage::dispatchEvent('checkout_cart_update_items_after', array('cart'=>$this, 'info'=>$data));
        return $this;
    }
	
    
    private function checkCustomMade($product)
    {
    	if($product->getIsCustom() == 1){
    		return true;
    	}
        return false;
    }
}
