<?php
class Task_Tools_Model_OrderAutoApprove extends Task_Tools_Model_Base{
    public function execute(){
        $subject = '斯伯丁官网定制球审批通过订单号';
        $orderIds = Mage::getModel('custommade/info')->OrderAutoApprove(1,1);//1待审批.1审批通过
        $message='';
         if (empty($orderIds)) {
           echo '无';
        } else {
            foreach ($orderIds as $orderId) {
                $sku= substr($orderId['sku'], -1);
                $id=Mage::getModel('custommade/info')->load($orderId['id']);
                if($sku==1){
                	if((!$orderId['msg6_p1'] && $orderId['msg6_p2']) || ($orderId['msg6_p1'] && !$orderId['msg6_p2'])){
                		 $id->autoApproved();
                         $message .= $orderId['order_id']. "\n";
                	}
                }elseif($sku==2){
                	if($orderId['msg6_p1'] && $orderId['msg6_p2']){
                		$id->autoApproved();
                         $message .= $orderId['order_id']. "\n";
                	}
                }

            }
        }
        //发送邮件
         $this->sendNotification($subject, $message);
	
    }


    public function validate(){
        return false;
    }

    public function sendNotification($this_subject, $this_message)
    {
        $to = self::CONF_SYSTEM_NOTIFICATION_TO_ADDRESS;
        $subject = $this_subject;
        $subject = "=?UTF-8?B?" . base64_encode($subject) . "?=";

        $message = $this_message;

        $headers = 'From: ' . self :: CONF_SYSTEM_NOTIFICATION_FROM_ADDRESS . "\r\n" ;

        if (self :: CONF_SYSTEM_NOTIFICATION_ENABLED) {
            mail($to, $subject, $message, $headers);
        }
        echo "\n" . $message ;
    }
}