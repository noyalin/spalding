<?php

class Cobra_CustomMade_Block_Adminhtml_Check_Renderer_Content extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        $msg = $row->getData($this->getColumn()->getIndex());
        if ($msg != null) {
            $html = '<a ';
            $html .= 'target="_blank" ';
            $html .= 'href="' . $msg . '">';
            $html .= '<img ';
            $html .= 'id="' . $this->getColumn()->getId() . '" ';
            $html .= 'src="' . $msg . '" ';
            $html .= 'style="width:100px"/>';
            $html .= '</a>';
        }
        return $html;
    }
}
