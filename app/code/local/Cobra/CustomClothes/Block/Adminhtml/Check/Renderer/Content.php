<?php

class Cobra_CustomClothes_Block_Adminhtml_Check_Renderer_Content extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        $link = $row->getData($this->getColumn()->getIndex());
        $html = null;
        $content = $this->getColumn()->getContent();
        $title = '预览图';
        //if ($link != null) {
            $html = '<a ';
            $html .= 'target="_blank" ';
            $html .= 'title="' . $title . '"';
            $html .= 'style="text-decoration:none"';
            $html .= 'href="' . $link . '">';
            $html .= $title;
            $html .= '</a>';
            return $html;
        //}
    }


}
