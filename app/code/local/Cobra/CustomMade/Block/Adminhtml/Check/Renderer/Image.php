<?php

class Cobra_CustomMade_Block_Adminhtml_Check_Renderer_Image extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        if ($row['type_p1']) {
            $html = '<a ';
            $html .= 'href="' . $row['msg1_p1'] . '">';
            $html .= '<img ';
            $html .= 'id="' . $this->getColumn()->getId() . '" ';
            $html .= 'src="' . $row->getData($this->getColumn()->getIndex()) . '" ';
            $html .= 'style="width:100px"/>';
            $html .= '</a>';
            return $html;
        } else {

        }
    }
}
