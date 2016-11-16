<?php

class Mage_Adminhtml_Block_Sales_Order_Renderer_Content extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
    	$index = $this->getColumn()->getIndex();
    	$orderId = $row->getData('increment_id');
    	$data = Mage::getModel('couponorder/couponorder')->getResource()
            ->loadByField('order_increment_id',$orderId);
    	if($data != false){
    		$rowData = Mage::getModel('couponorder/couponorder')->load($data);
    		if($index == 'coupon'){
    			echo $rowData->coupon_code;
    		}elseif($index == 'coupon_type'){
    			echo $rowData->coupon_rule_name;
    		}	
    	}
    	
    }


}
