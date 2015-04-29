<?php
class Task_Tools_CheckController extends Mage_Core_Controller_Front_Action{

    public function indexAction(){
        echo 'a';
    }

    public function emailAction(){
        if (!$this->getRequest()->isPost()) {
            return;
        }
        $email = $this->getRequest()->getParam('email');
        mage :: log($email);
        $websiteId = Mage::app()->getWebsite()->getId();

        echo $this->IscustomerEmailExists($email).' aaa';
    }
    function IscustomerEmailExists($email, $websiteId = null){
        $customer = Mage::getModel('customer/customer');

        $customer->setWebsiteId(1);
        $customer->loadByEmail($email);
        if ($customer->getId()) {
            return $customer->getId();
        }
        return false;
    }
}