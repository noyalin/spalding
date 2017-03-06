<?php
class Task_Tools_Model_Authentication extends Task_Tools_Model_Base{

    public function __construct(){
        parent::__construct();
    }
    public function execute(){
    	$authenticationFile = $this->catalogLogsDirectory."authentication.csv";
    	$output = false;
    	try {
    		$resource = Mage::getSingleton('core/resource');
    		$writeConnection = $resource->getConnection('core_write');
    		$readConnection = $resource->getConnection('core_read');
    		$sql = "SELECT * FROM `verify_code_data` where `status` = 0";
    		$rows = $readConnection->fetchAll($sql);
    		$subject = "验证码需要联系用户";
    		if(count($rows) != 0){
    			$message = "查看附件，下载表格";
    			header("Content-Type:text/html;charset=utf-8");
    			$ids = array();
    			foreach ($rows as $row){
    				$ids[] = $row['id'];
    			}
    			$firstRow = $rows[0];
    			$keys = array_keys($rows[0]);
				array_unshift($rows,$keys);
    			$fp = fopen($authenticationFile, 'w');
    			fwrite($fp, chr(0xEF).chr(0xBB).chr(0xBF)); // 添加 BOM
    			foreach ($rows as $dataRow)
    			{
    				fputcsv($fp,$dataRow);
    			}
    			fclose($fp);
    			$output = file_get_contents($authenticationFile);
    			
    			$idsStr = "";
    			foreach ($ids as $id){
    				$idsStr.= "$id".",";
    			}
    			$idsStr = substr($idsStr,0,-1);
				$sql = "update `verify_code_data` set `status` = 1 where id in (".$idsStr.");";
				$writeConnection->query($sql);
    			
    		}else{
    			$message = "no data need to contact";
    		}
    	} catch (Exception $e) {
    		$message = $e->getMessage();
    		
    	}
    	$this->sendNotificationSMTP($subject,$message,$output);
    }        
    
    
    public function sendNotificationSMTP($subject, $message,$output = false)
    {
    	$message .= "\r\n\r\n";
    	$config = array (
    			'ssl' => 'ssl',
    			'port' => 465,
    			'auth' => 'login',
    			'username' => 'system@snkh.com.cn',
    			'password' => 'Tmall888'
    	);
    
    	$transport = new Zend_Mail_Transport_Smtp('smtp.exmail.qq.com', $config);
    	$mail = new Zend_Mail("UTF-8");
    	$mail->setBodyText($message);
    	$mail->setFrom('system@snkh.com.cn', 'SpaldingCN');
    	$to = 'website_development@voyageone.cn,markting_spalding@voyageone.cn';
    	$emailArr = explode(',',$to);
    	$toArr = array();
    	foreach ($emailArr as $email){
    		array_push($toArr, $email);
    	}
    	$mail->addTo($toArr);
    	$mail->setSubject($subject);
    	
    	if($output != false){
    		$attachment = $mail->createAttachment($output);
    		$attachment->filename = 'authentication.csv';
    	}
    	$mail->send($transport);
    }
    
//     public function sendNotification($this_subject, $this_message, $cc = false, $subject_override = false)
//     {
//     	$to = 'alan.zhou@fotlinc.com,Jessica.Guo@fotlinc.com,cynthia.zhu@spaldingchina.com.cn';
//     	$to .= ($cc) ? "," . 'kobe.xin@voyageone.cn,markting-spalding@voyageone.cn,website-development@voyageone.cn' : "";
//     	$subject = ($subject_override) ? $this_subject : "Spalding System Notification - $this_subject";
//     	$subject = "=?UTF-8?B?" . base64_encode($subject) . "?=";
    
//     	$message = "$this_message";
//     	$message .= "\r\n\r\n";
    
//     	$headers = 'From: ' . self :: CONF_SYSTEM_NOTIFICATION_FROM_ADDRESS . "\r\n" ;
    
//     	if (self :: CONF_SYSTEM_NOTIFICATION_ENABLED) {
//     		mail($to, $subject, $message, $headers);
//     	}
//     	echo "\n" . $message . "\n";
//     }
    
    
    public function validate(){
        return false;
    }

}