<?php

/**
 * @method Cobra_CustomMade_Model_Info setStatus(int $value)
 */
class Cobra_CustomMade_Model_Info extends Mage_Core_Model_Abstract
{
    const STATUS_NON_PAYMENT = 0;
    const STATUS_APPROVING = 1;
    const STATUS_APPROVED = 2;
    const STATUS_CANCEL = 3;

    protected function _construct()
    {
        $this->_init('custommade/info');
    }

    public function nonpayment()
    {
        $this->setStatus(self::STATUS_NON_PAYMENT)->save();
    }

    public function approving()
    {
        $this->setStatus(self::STATUS_APPROVING)->save();
    }

    public function approved()
    {
        $this->setStatus(self::STATUS_APPROVED)->save();
    }

    public function cancel()
    {
        $this->setStatus(self::STATUS_CANCEL)->save();
    }

    public function loadByIncrementId($incrementId)
    {
        return $this->loadByAttribute('order_id', $incrementId);
    }

    public function saveCustomMade($order)
    {
        $customerId = Mage::getSingleton('core/session')->getCustomerId();
        $orderId = $order->getRealOrderId();
        $_items = $order->getItemsCollection();
        foreach ($_items as $_item) {
            if (!$_item->getParentItem()) {
                $sku = $_item->getProduct()->getSku();
                $session = Mage::getModel('custommade/session')->getOrderSession($customerId, $sku);
                if ($session->getTypeP1() != null ||
                    $session->getTypeP2() != null
                ) {
                    //保存定制信息
                    Mage::getModel('custommade/info')->setOrderId($orderId)
                        ->setTypeP1($session->getTypeP1())
                        ->setMsg1P1($session->getContent1P1())
                        ->setMsg2P1($session->getContent2P1())
                        ->setMsg3P1($session->getContent3P1())
                        ->setMsg4P1($session->getContent4P1())
                        ->setTypeP2($session->getTypeP2())
                        ->setMsg1P2($session->getContent1P2())
                        ->setMsg2P2($session->getContent2P2())
                        ->setMsg3P2($session->getContent3P2())
                        ->setMsg4P2($session->getContent4P2())
                        ->setStatus(self::STATUS_NON_PAYMENT)
                        ->save();
                    $this->clearSession($customerId, $sku);
                }
            }
        }
    }

    private function clearSession($customerId, $sku)
    {
        $session = Mage::getModel('custommade/session');
        $sessionId = $session->getCollection()
            ->addFieldToFilter('customer_id', $customerId)
            ->addFieldToFilter('sku', $sku)
            ->getFirstItem()
            ->getSessionId();
        $session->load($sessionId)->delete();

        $item = $session->getCollection()
            ->addFieldToFilter('customer_id', $customerId)
            ->getFirstItem();
        if ($item->getData() == null) {
            Mage::getSingleton('core/session')->setCustomerId(null);
            Mage::getModel('custommade/customer')->load($customerId)->delete();
        }
    }
}
