<?php

/**
 * Class Mage_Sales_Model_Postmessage
 * 1. 保存 订单信息到 表 `tm_tb_order_info`， 定时脚本 如果send_flg为0 则再次发送
 * 2. 调用webservice 生成shourtUrl
 */
class Mage_Sales_Model_Postmessage extends Mage_Core_Model_Abstract{

    public function __construct() {

    }
    public function saveDataAndSendWebservice($order){

        $billionAddress = $order->getBillingAddress();
        $tid = $order->getIncrementId();
//        $pay_time = date("Y-m-d H:i:s");
        $pay_time =  date("Y-m-d H:i:s",strtotime(now())+8*60*60);
        $receiver_name = $billionAddress->getName();
        $receiver_mobile = $billionAddress->getData('telephone');
        $payment = $order->getGrandTotal();
        $order_channel_id = "001";

        $model = Mage::getModel('sales/jasoncommentbean');
        $model->tid = $tid;
        $model->pay_time = $pay_time;
        $model->receiver_name = $receiver_name;
        $model->receiver_mobile = $receiver_mobile;
        $model->payment = $payment;
        $model->shop_name = "Sneakerhead中国";
        $model->order_channel_id = $order_channel_id;
        $comment = json_encode((array)$model);

        $data = $this->generateJson($comment,$pay_time);
        $returnCurl = $this->curlData($data);
        mage :: log($returnCurl);

        $resource = Mage::getSingleton('core/resource');
        $writeConnection = $resource->getConnection('core_write');
        $readConnection = $resource->getConnection('core_read');
        if($returnCurl['result'] == "OK"){
            //service 提交成功
            $results = $readConnection->fetchAll("SELECT * FROM tm_tb_order_info where tid='$tid'");
            //判断数据是否存在 根据order_id
            if(count($results) > 0){
                //存在
                $query = "update  tm_tb_order_info set send_flg=1 where tid='$tid' ";
            }else{
                //插入数据
                $query = "INSERT INTO `tm_tb_order_info` (`tid`, `ship_phone`, `ship_name`, `comment`, `create_time`,`create_person`,`send_flg`) VALUES
                ('$tid','$receiver_mobile','$receiver_name','$comment',now(),'saveorder','1')";
            }
            $res = $writeConnection->query($query);
        }else{
            //service 提交失败
            $results = $readConnection->fetchAll("SELECT * FROM tm_tb_order_info where tid='$tid'");
            //判断数据是否存在 根据order_id
            if(count($results) > 0){
                //存在
                $query = "update  tm_tb_order_info set send_flg=0 where tid='$tid' ";
            }else{
                //插入数据
                $query = "INSERT INTO `tm_tb_order_info` (`tid`, `ship_phone`, `ship_name`, `comment`, `create_time`,`create_person`,`send_flg`) VALUES
            ('$tid','$receiver_mobile','$receiver_name','$comment',now(),'saveorder','0')";
            }
            $res = $writeConnection->query($query);
        }

    }
    public function curlData($data_string){
        $post_data = array();
        $t = time();
//        $url="http://10.0.0.12:8080/open/shortUrl/shortUrlservice/generateShortUrl";//local env
        $url="http://api.synship.net/open/shortUrl/shortUrlservice/generateShortUrl";
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($data_string))
        );
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);

        $result = curl_exec($ch);
        $result = json_decode($result,true);
        return $result;
    }

    private function checkSignature($timestamp)
    {
        $token = "SynShip";
        $tmpArr = array($token, $timestamp);
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );
        return $tmpStr;
    }

    public function generateJson($data,$timeStamp){
        $model = Mage::getModel('sales/jsonverificationbean');
        $model->jsonData = $data;
        $model->timeStamp = $timeStamp;
        $model->signature = $this->checkSignature($timeStamp);

        return json_encode((array)$model);
    }

}