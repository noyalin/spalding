<?php

class Cobra_CustomMade_Block_Adminhtml_Check_Renderer_Content extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        $link = $row->getData($this->getColumn()->getIndex());
        $html = null;
        $message = $this->getMessage($row);
        if($message == null){
            $message = '>>空<<';
        }
        if(strpos($message, 'media/custommade') !== false){
        	$message = "浏览图片";
        }      
        $content = $this->getColumn()->getContent();
        if ($content == 1) {
            $title = '预览图';
        } else {
            $title = '打印图';
        }
        if ($link != null) {
            $html = '<a ';
            $html .= 'target="_blank" ';
            $html .= 'title="' . $title . '"';
            $html .= 'style="text-decoration:none"';
            $html .= 'href="' . $link . '">';
            $html .= $message;
            $html .= '</a>';
            return $html;
        }
    }

    private function getMessage(Varien_Object $row)
    {
        $position = $this->getColumn()->getPosition();
        $content = $this->getColumn()->getContent();
        $message = null;
        if ($position == 1) {
            switch ($content) {
                case 1:
                    $message = $row->getMsg1P1();
                    break;
                case 2:
                    $message = $row->getMsg2P1();
                    break;
                default:
            }
        } else {
            switch ($content) {
                case 1:
                    $message = $row->getMsg1P2();
                    break;
                case 2:
                    $message = $row->getMsg2P2();
                    break;
                default:
            }

        }
        return $message;
    }

}
