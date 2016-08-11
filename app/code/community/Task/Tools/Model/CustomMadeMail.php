<?php

class Task_Tools_Model_CustomMadeMail extends Task_Tools_Model_Base
{
    public function execute()
    {
        $subject = '斯伯丁官网定制球待审批订单';

        $message = "\r\n审批链接：http://www.spaldingchina.com.cn/index.php/backendspalding\r\n";
        $message .= "SPALDING审批用户：\r\n用户名：spalding1  密码：spalding123\r\n";
        $message .= "\r\n";
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
        $to = 'alan.zhou@fotlinc.com,Jessica.Guo@fotlinc.com,cynthia.zhu@spaldingchina.com.cn';
        $to .= ($cc) ? "," . 'kobe.xin@voyageone.cn,markting-spalding@voyageone.cn,website-development@voyageone.cn' : "";
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