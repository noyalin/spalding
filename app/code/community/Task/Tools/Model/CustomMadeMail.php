<?php

class Task_Tools_Model_CustomMadeMail extends Task_Tools_Model_Base
{
    public function execute()
    {
        $subject = '待审批订单';
        $message = '订单号：';
        $orderIds = Mage::getModel('custommade/info')->loadByConditions(date("Y-m-d", time()) . ' 12:00:00', 1);
        foreach ($orderIds as $orderId) {
            $message .= $orderId . '<br />';
        }
        $this->sendNotification($subject, $message);
    }

    public function validate()
    {
        return false;
    }
}