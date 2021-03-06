<?php

class Devicom_Couponorder_Model_Couponorder extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('couponorder/couponorder');
    }
    
    public function updateDataByOrder($order)
    {
    	$orderInfo = Mage::getModel('sales/order')->loadByIncrementId($order->getIncrementId());
    	if(trim($orderInfo->getCouponCode()) != ''){
    		$orderId = $orderInfo->getId();
    		$id = $this->getResource()->loadByField('order_id', $orderId);
    		if(!$id){
    			$this->setOrderId($orderInfo->getId());
    			$this->setOrderIncrementId($orderInfo->getIncrementId());
    			$this->setCouponCode($orderInfo->getCouponCode());
    			$oCoupon = Mage::getModel('salesrule/coupon')->load($orderInfo->getCouponCode(), 'code');
    			$oRule = Mage::getModel('salesrule/rule')->load($oCoupon->getRuleId());
    			$this->setCouponRuleName($orderInfo->getCouponRuleName());
    			$this->setCouponRuleId($oCoupon->getRuleId());
    			$this->setCreateTime($orderInfo->getCreatedAt());
     			$this->save();
    		}
    	}
    }
    
    public function test()
    {
//     	$this->setOrderId(1);
//     	$this->setCouponCode(1);
//     	$this->setCouponRuleName('aaa');
//     	$this->setCouponRuleId(222);
//     	$this->setCreateTime(date('Y-m-d H:i:s'));
//     	$this->save();
    }
    
}