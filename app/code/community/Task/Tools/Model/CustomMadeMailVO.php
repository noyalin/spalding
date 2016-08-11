<?php

class Task_Tools_Model_CustomMadeMailVO extends Task_Tools_Model_Base
{
    public function execute()
    {
        $subject = '斯伯丁官网定制球待审批订单 VoyageOne';

        $message = "\r\n审批链接：http://www.spaldingchina.com.cn/index.php/backendspalding\r\n";

        $message .= "审批不通过订单号：\r\n";
        $orderIdoneDay = Mage::getModel('custommade/info')->loadByConditions(date("Y-m-d", time()) . ' 00:00:00', 3);
        if (empty($orderIdoneDay)) {
            $message .= '无' . "\r\n";
        } else {
            foreach ($orderIdoneDay as $orderId) {
                $message .= $orderId['order_id'] . "\r\n";
            }
        }

        $message .= "Spalding审批通过，等待VoyageOne审批的订单号：\r\n";
        $orderIdoneDay = Mage::getModel('custommade/info')->loadByConditions2(date("Y-m-d", time()) . ' 00:00:00',1,1,0);
        if (empty($orderIdoneDay)) {
            $message .= '无' . "\r\n";
        } else {
            foreach ($orderIdoneDay as $orderId) {
                $message .= $orderId['order_id'] . "\r\n";
            }
        }

        $message .= "等待导出的订单号：\r\n";
        $orderIdoneDay = Mage::getModel('custommade/info')->loadByConditions2(date("Y-m-d", time()) . ' 00:00:00',2,1,1);
        if (empty($orderIdoneDay)) {
            $message .= '无' . "\r\n";
        } else {
            foreach ($orderIdoneDay as $orderId) {
                $message .= $orderId['order_id'] . "\r\n";
            }
        }

        $message .= "\r\n";
        $this->sendNotificationSMTP($subject, $message, true, true);
        $this->sendNotification($subject, $message, true, true);
    }

    public function validate()
    {
        return false;
    }

    public function sendNotificationSMTP($this_subject, $this_message, $cc = false, $subject_override = false)
    {
    	
    		$subject = ($subject_override) ? $this_subject : "Spalding System Notification - $this_subject";
    		$subject = "=?UTF-8?B?" . base64_encode($subject) . "?=";
    		$message = "$this_message";
    		$message .= "\r\n\r\n";
    		$config = array (
    			'ssl' => 'ssl',
    			'port' => 465,
    			'auth' => 'login',
    			'username' => 'system@snkh.com.cn',
    			'password' => 'Tmall888'
    		);
    		
    		$transport = new Zend_Mail_Transport_Smtp('smtp.exmail.qq.com', $config);
    		$mail = new Zend_Mail();
    		$mail->setBodyText($message);
    		$mail->setFrom('system@snkh.com.cn', 'SneakerheadCN');
    		$to = 'website-development@voyageone.cn,markting-spalding@voyageone.cn';
    		$emailArr = explode(',',$to);
    		$toArr = array();
    		foreach ($emailArr as $email){
    			array_push($toArr, $email);
    		}
    		$mail->addTo($toArr);
    		$mail->setSubject($subject);
    		$mail->send($transport);
    		echo "\n" . $message . "\n";
    }
    
    public function sendNotification($this_subject, $this_message, $cc = false, $subject_override = false)
    {
        $to = 'website-development@voyageone.cn,markting-spalding@voyageone.cn';
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