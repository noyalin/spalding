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
}