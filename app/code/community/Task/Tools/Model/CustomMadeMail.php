<?php

class Task_Tools_Model_CustomMadeMail extends Task_Tools_Model_Base
{
    public function execute()
    {
        $subject = '斯伯丁官网定制球待审批订单';
        $message = "审批链接：http://www.spaldingchina.com.cn/index.php/backendspalding\r\n";
        $message .= "审批用户一：\r\n用户名：spalding1  密码：fgdjbwb11\r\n";
        $message .= "审批用户二：\r\n用户名：spalding2  密码：ngjefhk22\r\n";

        $message .= date("Y年m月d日", time() - 24 * 60 * 60)."尚未审批订单号：\r\n";
        $orderIds = Mage::getModel('custommade/info')->loadByTime(date("Y-m-d", time()) . ' 00:00:00', date("Y-m-d", time() - 24 * 60 * 60) . ' 00:00:00', 1);
        if (empty($orderIds)) {
            $message .= '无' . "\r\n";
        } else {
            foreach ($orderIds as $orderId) {
                $message .= $orderId['order_id'] . "\r\n";
            }
        }

        $message .= "三天前尚未审批订单号：\r\n";
        $orderIdThreeDay = Mage::getModel('custommade/info')->loadByConditions(date("Y-m-d", time() - 72 * 60 * 60) . ' 00:00:00', 1);
        if (empty($orderIdThreeDay)) {
            $message .= '无' . "\r\n";
        } else {
            foreach ($orderIdThreeDay as $orderId) {
                $message .= $orderId['order_id'] . "\r\n";
            }
        }

        $this->sendNotification($subject, $message, true, true);
    }

    public function validate()
    {
        return false;
    }

    public function sendNotification($this_subject, $this_message, $cc = false, $subject_override = false)
    {
//        $to = 'pisces.bian@voyageone.cn';
//        $to .= ($cc) ? "," . 'kobe.xin@voyageone.cn,aaron.deng@voyageone.cn,bob.chen@voyageone.cn,buchun.gu@voyageone.cn' : "";
        $to = 'buchun.gu@voyageone.cn';
        $subject = ($subject_override) ? $this_subject : "Spalding System Notification - $this_subject";
        $subject = "=?UTF-8?B?" . base64_encode($subject) . "?=";

        $message = "$this_message";
        $message .= "\r\n\r\n";

        $headers = 'From: ' . self :: CONF_SYSTEM_NOTIFICATION_FROM_ADDRESS . "\r\n" ;

        if (self :: CONF_SYSTEM_NOTIFICATION_ENABLED) {
            mail($to, $subject, $message, $headers);
        }
        echo "\n" . $message . "\n";
    }
}