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

    public function completeAction()
    {
        $params = Mage::app()->getRequest()->getParams();
        if ($params[position] == 'P1') {
            Mage::getSingleton('core/session')->setData('step_p1', 1);
        } elseif ($params[position] == 'P2') {
            Mage::getSingleton('core/session')->setData('step_p2', 1);
        }


//        $data = str_replace("data:image/jpeg;base64,", "", $params['originalImg']);
//        $data_decode = base64_decode($data);
//        file_put_contents("media/tmp/img.jpg", $data_decode);

    }

    public function resetAction()
    {
        $params = Mage::app()->getRequest()->getParams();
        if ($params[position] == 'P1') {
            Mage::getSingleton('core/session')->setData('step_p1', 0);
        } elseif ($params[position] == 'P2') {
            Mage::getSingleton('core/session')->setData('step_p2', 0);
        }
    }
}