<?php

/**
 * @method Cobra_CustomMade_Model_Info setStatus(int $value)
 */
class Cobra_CustomMade_Model_Info extends Mage_Core_Model_Abstract
{
    const STATUS_APPROVED = 1;
    const STATUS_APPROVING = 2;
    const STATUS_CANCEL = 3;

    protected function _construct()
    {
        $this->_init('custommade/info');
    }

    public function approved()
    {
        $this->setStatus(self::STATUS_APPROVED)->save();
    }

    public function approving()
    {
        $this->setStatus(self::STATUS_APPROVING)->save();
    }

    public function cancel()
    {
        $this->setStatus(self::STATUS_CANCEL)->save();
    }

    public function saveCustomMade($orderId)
    {
        $session = Mage::getSingleton('core/session');
        $time = time();
        $this->setOrderId($orderId)
            ->setTypeP1($session->getTypeP1())
            ->setMsg1P1($session->getContent1P1())
            ->setMsg2P1($session->getContent2P1())
            ->setTypeP2($session->getTypeP2())
            ->setMsg1P2($session->getContent1P2())
            ->setMsg2P2($session->getContent2P2())
            ->setCreateTime($time)
            ->setUpdateTime($time)
            ->setStatus(self::STATUS_APPROVED)
            ->save();
        $this->clearSession();
    }

    private function clearSession()
    {
        $session = Mage::getSingleton('core/session');
        $session->setTypeP1(null);
        $session->setTypeP1(null);
        $session->setContent1P1(null);
        $session->setContent2P1(null);
        $session->setTypeP2(null);
        $session->setContent1P2(null);
        $session->setContent2P2(null);
        $session->setPos(null);
        $session->setCustomStatus(null);
    }
}
