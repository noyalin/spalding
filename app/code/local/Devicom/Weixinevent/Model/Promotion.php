<?php
class Devicom_Weixinevent_Model_Promotion extends Mage_Core_Model_Abstract
{
    private $readConnection;
    private $writeConnection;

    public function  __construct()
    {
        $resource = Mage::getSingleton('core/resource');
        $this->readConnection = $resource->getConnection('core_read');
        $this->writeConnection = $resource->getConnection('core_write');
        parent :: __construct();
    }

    public function getAccessToken($appid,$appsecret){
        $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$appid&secret=$appsecret";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($ch);
        curl_close($ch);
        $jsoninfo = json_decode($output, true);
        return $jsoninfo;
    }

    public function getJsTicket($token){
        $url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token=".$token."&type=jsapi";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($ch);
        curl_close($ch);
        $jsoninfo = json_decode($output, true);
        return $jsoninfo;
    }

    public function getVar(){
        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');
        $writeConnection = $resource->getConnection('core_write');
        $arr = array();
        $query = "SELECT * from weixin_appset where id = 1";

        $apidata = $readConnection->fetchRow($query);



        $ticket = $apidata['ticket'];
        if($apidata['access_token']==null || (time()-1*$apidata['create_time']>7200)){
            //需要重新获取access_token
            $json = $this->getAccessToken($apidata['appid'],$apidata['appsecret']);
            $id = $apidata['id'];
            $accessToken = $json['access_token'];
            $createTime = time();
            $query = "update `weixin_appset` set  access_token = '$accessToken',create_time=$createTime where id = $id";
            $writeConnection->query($query);
        }
        if($apidata['ticket']==null || (time()-1*$apidata['ticket_time']>7200)){
            $token = $accessToken;
            $jsonTicket = $this->getJsTicket($token);
            if($jsonTicket['errmsg'] == 'ok'){
                $id = $apidata['id'];
                $ticket = $jsonTicket['ticket'];
                $ticketTime = time();
                $query = "update `weixin_appset` set  ticket = '$ticket',ticket_time=$ticketTime where id = $id";
                mage :: log($query);

                $writeConnection->query($query);
            }
        }

        $noncestr = "Wm3WZYTPz0wzccnW";
        $timestamp = time();
        $currentUrl = Mage::helper('core/url')->getCurrentUrl();
        $str = 'jsapi_ticket='.$ticket.'&noncestr='.$noncestr.'&timestamp='.$timestamp.'&url='.$currentUrl;
        $signature = sha1($str);
        return array($apidata['appid'],$timestamp,$noncestr,$signature,$currentUrl);
    }

    public function getPromotion()
    {
        $orderId = Mage::getSingleton('customer/session')->getOrderId();
        $openId = Mage::getSingleton('customer/session')->getOpenId();
        $sql = "select count(*) from `weixin_promotion` where order_id = '" . $orderId . "' and open_id = '" . $openId . "'";
        $result = $this->readConnection->fetchOne($sql);
        if ($result != 0) {
            return false;
        }
        return true;
    }

    public function getOpenId(){
        return Mage::getSingleton('customer/session')->getOpenId();
    }
    public function setPromotionData($flag,$clickOrder){
        $orderId = Mage::getSingleton('customer/session')->getOrderId();
        $openId = Mage::getSingleton('customer/session')->getOpenId();
        $actId = Mage::getSingleton('customer/session')->getActId();
        $time = time();

        $sql = "insert into weixin_promotion values (null,'".$orderId."','".$openId."','".$actId."','".$flag."','".$clickOrder."','".$time."')";
        $this->writeConnection->query($sql);
    }

    public function setCaptchaData($telephone)
    {
        $openId = Mage::getSingleton('customer/session')->getOpenId();
        $sql = "select count(*) from weixin_captcha where telephone_no = '" . $telephone . "'";
        $result = $this->readConnection->fetchOne($sql);
        if ($result != 0) {
            return false;
        }
        $sql = "insert into weixin_captcha values (null,'" . $openId . "','" . $telephone . "')";
        $this->writeConnection->query($sql);
        return true;
    }


    public function getPromotionStatus($order_id, $act_id)
    {
        $sql = "select operation from weixin_promotion where order_id = '".$order_id."' and act_id = '".$act_id."' and sponsor_flag = 5";
        $result = $this->readConnection->fetchCol($sql);
        return $result;
    }

}