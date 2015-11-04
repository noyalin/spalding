<?php

class Task_Tools_Model_CustomMadeMail extends Task_Tools_Model_Base
{
    public function execute()
    {
        $subject = '待审批订单';
        $message = '订单号：';
        $orderIds = Mage::getModel('custommade/info')->loadByConditions(date("Y-m-d", time()) . ' 12:00:00', 1);
        foreach ($orderIds as $orderId) {
            $message .= $orderId['order_id'] . "\r\n\r\n";
        }
        $this->sendNotification($subject, $message, true);
    }

    public function validate()
    {
        return false;
    }

    public function sendNotification($this_subject, $this_message, $cc = false, $subject_override = false)
    {

        $to = self :: CONF_SYSTEM_NOTIFICATION_TO_ADDRESS;
        $to .= ($cc) ? "," . self :: CONF_SYSTEM_NOTIFICATION_CC_ADDRESS : "";
        $subject = ($subject_override) ? $this_subject : "System Notification - $this_subject";
        $message = "This is a system notification.\r\n\r\n";
        $message .= "$this_message";
        $message .= "\r\n\r\n";

        $headers = 'From: ' . self :: CONF_SYSTEM_NOTIFICATION_FROM_ADDRESS . "\r\n" .
            'Reply-To: ' . self :: CONF_SYSTEM_NOTIFICATION_FROM_ADDRESS . "\r\n" .
            'X-Mailer: PHP/' . phpversion()."\r\n";
        $headers .= "Content-type: text/plain; charset=utf-8\r\n";
        $headers .= "Content-Transfer-Encoding: 8bit\r\n";

        if (self :: CONF_SYSTEM_NOTIFICATION_ENABLED) {
            mail($to, $subject, $message, $headers);
        }
        echo "\n" . $message . "\n";
    }
}