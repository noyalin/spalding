<?php
class Task_Tools_Block_List extends Mage_Core_Block_Template
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('tools/list.phtml');
    }

}