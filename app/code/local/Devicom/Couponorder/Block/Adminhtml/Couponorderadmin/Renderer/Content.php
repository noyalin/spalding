<?php

class Devicom_Couponorder_Block_Adminhtml_Couponorderadmin_Renderer_Content extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        $id = $row->getData($this->getColumn()->getIndex());
    	$orderId = $row->getOrderId();
        $url = Mage::getModel('adminhtml/url')->getUrl('backendspalding/sales_order/view/',array('order_id'=>$orderId));
        $html = "<a href=".$url.">$id</a>";
        return $html;
    }


}
