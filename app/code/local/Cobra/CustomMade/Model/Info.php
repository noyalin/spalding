<?php

/**
 * @method Cobra_CustomMade_Model_Info setStatus(int $value)
 */
class Cobra_CustomMade_Model_Info extends Mage_Core_Model_Abstract
{
    const STATUS_NON_PAYMENT = 0;
    const STATUS_APPROVING = 1;
    const STATUS_APPROVED = 2;
    const STATUS_NOTAPPROVED = 3;
    const STATUS_CANCEL = 4;
    const STATUS_EXPORT = 5;

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
        $this->setStatus(self::STATUS_APPROVING)
            ->setUser1Approve(0)
            ->setUser2Approve(0)
            ->setUser3Approve(0)
            ->setUser4Approve(0)
            ->save();
    }

    public function approved()
    {
        $this->setStatus(self::STATUS_APPROVED)->save();
    }

    public function notapproved()
    {
        $this->setStatus(self::STATUS_NOTAPPROVED)->save();
    }

    public function cancel()
    {
        $this->setStatus(self::STATUS_CANCEL)->save();
    }

    public function export()
    {
        $this->setStatus(self::STATUS_EXPORT)->save();
    }

    public function loadByIncrementId($incrementId)
    {
        $id = $this->getResource()
            ->loadByField('order_id', $incrementId);
        return $this->load($id);
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
                        ->setSku($session->getSku())
                        ->setTypeP1($session->getTypeP1())
                        ->setMsg1P1($session->getContent1P1())
                        ->setMsg2P1($session->getContent2P1())
                        ->setMsg3P1($session->getContent3P1())
                        ->setMsg4P1($session->getContent4P1())
                        ->setMsg5P1($this->createP1Url($session->getTypeP1(), $session->getContent1P1(), $session->getContent2P1(), $session->getContent3P1(), $session->getContent4P1(), 'show', $session->getSku()))
                        ->setMsg6P1($this->createP1Url($session->getTypeP1(), $session->getContent1P1(), $session->getContent2P1(), $session->getContent3P1(), $session->getContent4P1(), 'print', $session->getSku()))
                        ->setTypeP2($session->getTypeP2())
                        ->setMsg1P2($session->getContent1P2())
                        ->setMsg2P2($session->getContent2P2())
                        ->setMsg3P2($session->getContent3P2())
                        ->setMsg4P2($session->getContent4P2())
                        ->setMsg5P2($this->createP2Url($session->getTypeP2(), $session->getContent1P2(), $session->getContent2P2(), $session->getContent3P2(), $session->getContent4P2(), 'show', $session->getSku()))
                        ->setMsg6P2($this->createP2Url($session->getTypeP2(), $session->getContent1P2(), $session->getContent2P2(), $session->getContent3P2(), $session->getContent4P2(), 'print', $session->getSku()))
                        ->setStatus(self::STATUS_NON_PAYMENT)
                        ->save();
                    $this->clearSession($customerId, $sku);
                }
            }
        }
    }

    public function createP1Url($typeP1, $content1P1, $content2P1, $content3P1, $content4P1, $imgType, $sku)
    {
        $url = null;
        switch ($typeP1) {
            case 1:
                break;
            case 2:
                if ($sku == '74-604yc') {
                    $url = 'http://s7d5.scene7.com/is/image/sneakerhead/74-604-spalding-';
                } else {
                    $url = 'http://s7d5.scene7.com/is/image/sneakerhead/spalding-';
                }
                break;
            default;
                return $url;
        }

        switch ($content4P1) {
            case 2:
                $imgType .= '-arial';
                break;
            default:
        }

        switch ($content3P1) {
            case 1:
                $url .= 'p1-small_one-' . $imgType . '?$1980pxx544px$&$textone=' . $content1P1;
                break;
            case 2:
                $url .= 'p1-middle-' . $imgType . '?$1980pxx544px$&$text=' . $content1P1;
                break;
            case 3:
                $url .= 'p1-big-' . $imgType . '?$1980pxx544px$&$text=' . $content1P1;
                break;
            case 4:
                $url .= 'p1-small_two-' . $imgType . '?$1980pxx544px$&$texttwo=' . $content2P1 . '&$textone=' . $content1P1;
                break;
            default:
        }
        return $url;
    }

    public function createP2Url($typeP2, $content1P2, $content2P2, $content3P2, $content4P2, $imgType, $sku)
    {
        $url = null;
        switch ($typeP2) {
            case 1:
                break;
            case 2:
                if ($sku == '74-604yc') {
                    $url = 'http://s7d5.scene7.com/is/image/sneakerhead/74-604-spalding-';
                } else {
                    $url = 'http://s7d5.scene7.com/is/image/sneakerhead/spalding-';
                }
                break;
            default;
                return $url;
        }

        switch ($content4P2) {
            case 2:
                $imgType .= '-arial';
                break;
            default:
        }

        switch ($content3P2) {
            case 1:
                $url .= 'p2-small_one-' . $imgType . '?$1980pxx544px$&$textone=' . $content1P2;
                break;
            case 2:
                $url .= 'p2-middle-' . $imgType . '?$1980pxx544px$&$text=' . $content1P2;
                break;
            case 3:
                $url .= 'p2-big-' . $imgType . '?$1980pxx544px$&$text=' . $content1P2;
                break;
            case 4:
                $url .= 'p2-small_two-' . $imgType . '?$1980pxx544px$&$texttwo=' . $content2P2 . '&$textone=' . $content1P2;
                break;
            default;
        }
        return $url;
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
        Mage::getSingleton('core/session')->setCustomermadeAgree(null);
    }
}
