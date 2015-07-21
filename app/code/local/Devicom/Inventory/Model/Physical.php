<?php

/**
 * @method Cobra_CustomMade_Model_Info setStatus(int $value)
 */
class Devicom_Inventory_Model_Physical extends Mage_Core_Model_Abstract
{
    const STATUS_APPROVED = 1;
    const STATUS_APPROVING = 2;
    const STATUS_CANCEL = 3;

    protected function _construct()
    {
        $this->_init('inventory/physical');
    }

    public function loadByField($field, $value)
    {
        $id = $this->getResource()->loadByField($field, $value);
        $this->load($id);
    }

    public function updateQtyBySku($sku, $qty)
    {
        $skuUpper = strtoupper($sku);
        $id = $this->getResource()->loadByField('sku', $skuUpper);
        if ($id) {
            $item = $this->load($id);
            $item->setQty($qty);
            $item->setModifyTime(now());
            $item->save();
        } else {
            $this->setSku($skuUpper);
            $this->setQty($qty);
            $this->save();
        }
    }

    public function getQtyBySku($sku)
    {
        $skuUpper = strtoupper($sku);
        $id = $this->getResource()->loadByField('sku', $skuUpper);
        if ($id) {
            $item = $this->load($id);
            return $item->getQty();
        }
        return 0;
    }

//    public function approved()
//    {
//        $this->setStatus(self::STATUS_APPROVED)->save();
//    }
//
//    public function approving()
//    {
//        $this->setStatus(self::STATUS_APPROVING)->save();
//    }
//
//    public function cancel()
//    {
//        $this->setStatus(self::STATUS_CANCEL)->save();
//    }
}
