<?php

class Cobra_CustomMade_Block_Adminhtml_Check_Renderer_Content extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    const TYPE_IMAGE = 1;
    const TYPE_TEXT = 2;
    const POSITION_P1 = 'P1';
    const POSITION_P2 = 'P2';

    public function render(Varien_Object $row)
    {
        $position = $this->getColumn()->getData('position');
        if ($position == self::POSITION_P1) {
            $index = 'type_p1';
        } else {
            $index = 'type_p2';
        }
        $msg = $row->getData($this->getColumn()->getIndex());
        if ($row[$index] == self::TYPE_IMAGE) {
            $html = '<a ';
            $html .= 'target="_blank" ';
            $html .= 'href="' . $msg . '">';
            $html .= '<img ';
            $html .= 'id="' . $this->getColumn()->getId() . '" ';
            $html .= 'src="' . $msg . '" ';
            $html .= 'style="width:100px"/>';
            $html .= '</a>';
        } else {
            $html = '<span>' . $msg . '</span>';
        }
        return $html;
    }
}
