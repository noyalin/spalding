<?php

class Cobra_CustomMade_IndexController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
        $test = Mage::getModel('custommade/info')->getCollection();
        foreach ($test as $data) {
            echo $data->getOrderId() . "\n";
            echo $data->getType() . "\n";
        }
    }

    public function updateImageAction()
    {
        $params = Mage::app()->getRequest()->getParams();
        $data = str_replace("data:image/jpeg;base64,", "", $params['originalImg']);
        $data_decode = base64_decode($data);
        file_put_contents("media/tmp/img.jpg", $data_decode);
    }

    public function completeAction()
    {
        Mage::getSingleton('core/session')->setData('step', 1);

//        $this->loadLayout();
//        $this->renderLayout();

        $block = Mage::getBlockSingleton('custommade/view');

        return $block->getChildHtml('custommade_view_options');

    }

    public function resetAction()
    {
        Mage::getSingleton('core/session')->setData('step', 0);
    }
}