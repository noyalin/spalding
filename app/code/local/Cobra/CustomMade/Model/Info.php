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
}
