<?php
class Task_Tools_CheckController extends Mage_Core_Controller_Front_Action{

    public function emailAction(){
        if (!$this->getRequest()->isPost()) {
            return;
        }
        $email = $this->getRequest()->getParam('email');
        $websiteId = Mage::app()->getWebsite()->getId();

        echo $this->IscustomerEmailExists($email);
    }
    function IscustomerEmailExists($email, $websiteId = null){
        $customer = Mage::getModel('customer/customer');

        $customer->setWebsiteId(1);
        $customer->loadByEmail($email);
        if ($customer->getId()) {
            return 1;
        }
        return false;
    }
}