<?php

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
        if (!$this->checkCustomByOrderId($this->getOrderId(), $this->getSku())) {
            Mage::log('approving Error : id=' . $this->getId() . ', order_id=' . $this->getOrderId() . ', SKU=' . $this->getSku());
            return;
        }
        $this->setStatus(self::STATUS_APPROVING)
            ->setUser1Approve(0)
            ->setUser2Approve(0)
            ->setUser3Approve(0)
            ->setUser4Approve(0)
            ->setUser1Reason(null)
            ->setUser2Reason(null)
            ->setUser3Reason(null)
            ->setUser4Reason(null)
            ->save();
    }

    public function approved()
    {
        if (!$this->checkCustomByOrderId($this->getOrderId(), $this->getSku())) {
            Mage::log('approved Error : id=' . $this->getId() . ', order_id=' . $this->getOrderId() . ', SKU=' . $this->getSku());
            return;
        }
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

    public function loadByConditions($time, $status)
    {
        $items = $this->getResource()
            ->loadByConditions($time, $status);
        return $items;
    }

    public function loadByTime($time1, $time2, $status)
    {
        $items = $this->getResource()
            ->loadByTime($time1, $time2, $status);
        return $items;
    }

    public function saveCustomMade($order)
    {
        $orderId = $order->getRealOrderId();
        $customerId = $order->getCustomerId();
        $customMsg = Mage::getModel('custommade/temp')->loadByCustomerId($customerId);

        if (!$customMsg) {
            return;
        }

        $new_sku = $this->transformSku($customMsg);
        if (!$new_sku) {
            Mage::log('saveCustomMade Error : new_sku=' . $new_sku);
            return;
        }

        if (!$this->checkCustomByOrderId($orderId, $new_sku)) {
            Mage::log('saveCustomMade Error : id=NewId, order_id=' . $orderId . ', SKU=' . $new_sku);
            return;
        }

        try {
            if ($customMsg->getTypeP1() != null ||
                $customMsg->getTypeP2() != null
            ) {
                //保存定制信息
                Mage::getModel('custommade/info')->setOrderId($orderId)
                    ->setSku($new_sku)
                    ->setTypeP1($customMsg->getTypeP1())
                    ->setMsg1P1($customMsg->getMsg1P1())
                    ->setMsg2P1($customMsg->getMsg2P1())
                    ->setMsg3P1($customMsg->getMsg3P1())
                    ->setMsg4P1($customMsg->getMsg4P1())
                    ->setMsg5P1($this->createP1Url($customMsg->getTypeP1(), $customMsg->getMsg1P1(), $customMsg->getMsg2P1(), $customMsg->getMsg3P1(), $customMsg->getMsg4P1(), 'show', $customMsg->getSku()))
                    ->setMsg6P1($this->createP1Url($customMsg->getTypeP1(), $customMsg->getMsg1P1(), $customMsg->getMsg2P1(), $customMsg->getMsg3P1(), $customMsg->getMsg4P1(), 'print', $customMsg->getSku()))
                    ->setTypeP2($customMsg->getTypeP2())
                    ->setMsg1P2($customMsg->getMsg1P2())
                    ->setMsg2P2($customMsg->getMsg2P2())
                    ->setMsg3P2($customMsg->getMsg3P2())
                    ->setMsg4P2($customMsg->getMsg4P2())
                    ->setMsg5P2($this->createP2Url($customMsg->getTypeP2(), $customMsg->getMsg1P2(), $customMsg->getMsg2P2(), $customMsg->getMsg3P2(), $customMsg->getMsg4P2(), 'show', $customMsg->getSku()))
                    ->setMsg6P2($this->createP2Url($customMsg->getTypeP2(), $customMsg->getMsg1P2(), $customMsg->getMsg2P2(), $customMsg->getMsg3P2(), $customMsg->getMsg4P2(), 'print', $customMsg->getSku()))
                    ->setCreateTime(time())
                    ->setStatus(self::STATUS_NON_PAYMENT)
                    ->save();
                Mage::getModel('custommade/temp')->deleteByCustomerId($customerId);
                Mage::log('saveCustomMade------customerId=' . $customerId . ',order id=' . $orderId);
                return;
            } else {
                Mage::log('saveCustomMade Error ( P1 P2 ALL NULL): id=NewId, order_id=' . $orderId . ', SKU=' . $new_sku);
            }

        } catch (Exception $e) {
            Mage::log('Save CustomMade Error,Order Id=' . $orderId . ',Customer Id=' . $customerId);
            Mage::log($e->getMessage());

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
                $url .= 'p1-small_one-' . $imgType . '?$1980pxx544px$&$textone=' . urlencode($content1P1);
                break;
            case 2:
                $url .= 'p1-middle-' . $imgType . '?$1980pxx544px$&$text=' . urlencode($content1P1);
                break;
            case 3:
                $url .= 'p1-big-' . $imgType . '?$1980pxx544px$&$text=' . urlencode($content1P1);
                break;
            case 4:
                $url .= 'p1-small_two-' . $imgType . '?$1980pxx544px$&$texttwo=' . urlencode($content2P1) . '&$textone=' . urlencode($content1P1);
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
                $url .= 'p2-small_one-' . $imgType . '?$1980pxx544px$&$textone=' . urlencode($content1P2);
                break;
            case 2:
                $url .= 'p2-middle-' . $imgType . '?$1980pxx544px$&$text=' . urlencode($content1P2);
                break;
            case 3:
                $url .= 'p2-big-' . $imgType . '?$1980pxx544px$&$text=' . urlencode($content1P2);
                break;
            case 4:
                $url .= 'p2-small_two-' . $imgType . '?$1980pxx544px$&$texttwo=' . urlencode($content2P2) . '&$textone=' . urlencode($content1P2);
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

    private function checkCustomByOrderId($orderId, $customSku)
    {
        $order = Mage::getModel('sales/order');
        $order->loadByIncrementId($orderId);
        if (!$order) {
            return false;
        }
        $_items = $order->getItemsCollection();
        foreach ($_items as $_item) {
            if ($_item->getProductType() == 'simple') {
                if ($_item->getSku() == $customSku) {
                    return true;
                }
            }
        }
        return false;
    }

    private function checkCustomByOrder($order, $customSku)
    {
        if (!$order) {
            return false;
        }
        $_items = $order->getItemsCollection();
        foreach ($_items as $_item) {
            if ($_item->getProductType() == 'simple') {
                if ($_item->getSku() == $customSku) {
                    return true;
                }
            }
        }
        return false;
    }

    private function transformSku($customMsg)
    {
        $sub_sku = $customMsg->getSubSku();
        if ($sub_sku) {
            return $customMsg->getSku() . $sub_sku;
        } else {
            Mage::log('transformSku Error : sub_sku == null');
            return false;
        }
    }
}
