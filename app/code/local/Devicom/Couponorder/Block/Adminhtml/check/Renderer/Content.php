<?php

class Devicom_Couponorder_Block_Adminhtml_Check_Renderer_Content extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        $id = $row->getData($this->getColumn()->getIndex());
        
        $url = Mage::helper("adminhtml")->getUrl('sales_order/view/',
        		array(
        			'order_id'=> $id,
        		)
        );

        $html = "<a href=".$url.">$id</a>";
        return $html;
    }


}
