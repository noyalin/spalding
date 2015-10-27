<?php

class Cobra_CustomMade_Model_Session extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        $this->_init('custommade/session');
    }

    public function getSession($customerId, $sku)
    {
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
        Mage::log("getSession ($customerId, $sku)");
        Mage::log($session);
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
}