<?php

class Cobra_CustomMade_Model_Session extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        $this->_init('custommade/session');
    }

    public function getSession($customerId, $sku)
    {
        Mage::log("getSession before ($customerId, $sku)");
        if (!$customerId) {
            $customerId = Mage::getModel('custommade/customer')->createCustomer();
        }
        $session = $this->getCollection()
            ->addFieldToFilter('customer_id', $customerId)
            ->addFieldToFilter('sku', $sku)
            ->getFirstItem();
        if ($session->getData() == null) {
            $this->setCustomerId($customerId)
                ->setSku($sku)
                ->save();
            $session = $this->getCollection()
                ->addFieldToFilter('customer_id', $customerId)
                ->addFieldToFilter('sku', $sku)
                ->getFirstItem();
        }
//        Mage::log($session);
        Mage::log("getSession after ($customerId, $sku)");
        return $session;
    }

    public function getOrderSession($customerId, $sku)
    {
        $session = $this->getCollection()
            ->addFieldToFilter('customer_id', $customerId)
            ->addFieldToFilter('sku', $sku)
            ->getFirstItem();
        return $session;
    }

    public function setSession($session)
    {
        $sessionId = $this->getCollection()
            ->addFieldToFilter('customer_id', $session->getCustomerId())
            ->addFieldToFilter('sku', $session->getSku())
            ->getFirstItem()
            ->getSessionId();

        $this->load($sessionId)
            ->setCustomStatus($session->getCustomStatus())
            ->setPos($session->getPos())
            ->setTypeP1($session->getTypeP1())
            ->setContent1P1($session->getContent1P1())
            ->setContent2P1($session->getContent2P1())
            ->setContent3P1($session->getContent3P1())
            ->setContent4P1($session->getContent4P1())
            ->setTypeP2($session->getTypeP2())
            ->setContent1P2($session->getContent1P2())
            ->setContent2P2($session->getContent2P2())
            ->setContent3P2($session->getContent3P2())
            ->setContent4P2($session->getContent4P2())
            ->setTestMode($session->getTestMode())
            ->save();
    }

    public function rewriteSession($customerId, $sku, $userCustomerId)
    {
        Mage::log("rewriteSession before ($customerId, $sku, $userCustomerId)");
        $orderSession = Mage::getModel('custommade/temp')->loadByCustomerId($userCustomerId);
        $session = $this->getSession($customerId, $sku);
        $session->setCustomerId($customerId);
        $session->setSku($sku);
        $session->setPos(1);
        $session->setTypeP1($orderSession->getTypeP1());
        $session->setContent1P1($orderSession->getMsg1P1());
        $session->setContent2P1($orderSession->getMsg2P1());
        $session->setContent3P1($orderSession->getMsg3P1());
        $session->setContent4P1($orderSession->getMsg4P1());
        $session->setTypeP2($orderSession->getTypeP2());
        $session->setContent1P2($orderSession->getMsg1P2());
        $session->setContent2P2($orderSession->getMsg2P2());
        $session->setContent3P2($orderSession->getMsg3P2());
        $session->setContent4P2($orderSession->getMsg4P2());
        $this->setSession($session);
        Mage::log("rewriteSession after ($customerId, $sku, $userCustomerId)");
        return $session;
    }
}