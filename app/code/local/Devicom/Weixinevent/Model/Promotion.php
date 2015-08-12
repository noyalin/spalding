<?php

define('WEIXIN_PROMOTION_ACTIVITY_ID',   '10000001');
define('WEIXIN_PROMOTION_START_TIME',   '2015-08-12 00:00:00');
define('WEIXIN_PROMOTION_END_TIME',   '2015-09-01 00:00:00');
define('WEIXIN_PROMOTION_ORDEY_STATUS',   'alipay_wait_buyer_pay');
define('WEIXIN_PROMOTION_ORDEY_EMAIL',   '@voyageone.cn');
define('WEIXIN_PROMOTION_ORDEY_NOTE',   'test');

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

    public function getApidata(){
        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');

        $arr = array();
        $query = "SELECT * from weixin_appset where id = 1";

        $apidata = $readConnection->fetchRow($query);
        return $apidata;
    }

    public function getVar(){
        $resource = Mage::getSingleton('core/resource');
        $writeConnection = $resource->getConnection('core_write');
        $apidata = $this->getApidata();

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
        else {
            $accessToken = $apidata['access_token'];
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

    public function isPromotioned()
    {
        $orderId = Mage::getSingleton('customer/session')->getOrderId();
        $openId = Mage::getSingleton('customer/session')->getOpenId();
        $sql = "select count(*) from `weixin_promotion` where order_id = '" . $orderId . "' and open_id = '" . $openId . "' and sponsor_flag =5";
        $result = $this->readConnection->fetchOne($sql);
        if ($result != 0) {
            return 1;
        }
        return 0;
    }

//    public function getOpenId(){
//        return Mage::getSingleton('customer/session')->getOpenId();
//    }

    public function setPromotionData($flag, $telephone, $clickOrder)
    {
        $orderId = Mage::getSingleton('customer/session')->getOrderId();
        $openId = Mage::getSingleton('customer/session')->getOpenId();
        $actId = Mage::getSingleton('customer/session')->getActId();

        if ($flag == 0) {
            $sponsorTelephone = $this->getSponsorOrderTel($orderId);
            $sql = "insert into weixin_promotion(order_id,open_id,act_id,sponsor_flag,operation,telephone_no) values ('" . $orderId . "','" . $openId . "','" . $actId . "','" . $flag . "','" . $clickOrder . "','" . $sponsorTelephone . "')";
        } else {
            $sql = "insert into weixin_promotion(order_id,open_id,act_id,sponsor_flag,operation,telephone_no) values ('" . $orderId . "','" . $openId . "','" . $actId . "','" . $flag . "','" . $clickOrder . "','" . $telephone . "');";
            $sql .= " update weixin_promotion set telephone_no = '$telephone' where order_id = '" . $orderId . "' and openId = '" . $openId . "' and act_id = '" . $actId . "' and ( sponsor_flag = 0 || sponsor_flag = 1 );";
        }

        $this->writeConnection->query($sql);
    }

    public function updatePromotionData($actId, $orderId)
    {
        $modify_time = date("YmdHis", time());
        $sql = "update weixin_promotion set sponsor_flag = 1,modify_time='" . $modify_time . "' where order_id = '" . $orderId . "' and act_id = '" . $actId . "' and sponsor_flag = 0";
        Mage::log("sql::" . $sql, Zend_Log::DEBUG);
        $result = $this->writeConnection->query($sql);
        Mage::log("result::" . $result, Zend_Log::DEBUG);
    }

    public function isSponsor()
    {
        $orderId = Mage::getSingleton('customer/session')->getOrderId();
        $openId = Mage::getSingleton('customer/session')->getOpenId();
        $sql = "select count(*) from weixin_promotion where open_id = '" . $openId . "' and order_id = '". $orderId ."' and (sponsor_flag = 0 or sponsor_flag = 1)";
        $result = $this->readConnection->fetchOne($sql);
        if ($result != 0) {
            return 1;
        }
        return 0;
    }

    public function hasSponsor()
    {
        $orderId = Mage::getSingleton('customer/session')->getOrderId();
        $sql = "select count(*) from weixin_promotion where order_id = '". $orderId ."' and (sponsor_flag = 0 or sponsor_flag = 1)";
        $result = $this->readConnection->fetchOne($sql);
        if ($result != 0) {
            return 1;
        }
        return 0;
    }

    public function setCaptchaData($telephone)
    {
        $openId = Mage::getSingleton('customer/session')->getOpenId();
        $sql = "select count(*) from weixin_captcha where telephone_no = '" . $telephone . "' and open_id = '" . $openId . "'";
        $result = $this->readConnection->fetchOne($sql);
        if ($result == 0) {
            $sql = "insert into weixin_captcha(open_id,telephone_no) values ('" . $openId . "','" . $telephone . "')";
            $this->writeConnection->query($sql);
            return true;
        }
        return false;
    }


    public function getPromotionCount()
    {
        $orderId = Mage::getSingleton('customer/session')->getOrderId();
        $sql = "select count(*) from weixin_promotion where order_id = '".$orderId."' and sponsor_flag = 5";
        $result = $this->readConnection->fetchOne($sql);
        return $result;
    }

    public function updateCoupon($openId, $types)
    {

        $modify_time = date("YmdHis", time());

        $sql1 = "select id from weixin_coupon where types = '" . $types . "' and status = 0 limit 1 for update";
        $id = $this->readConnection->fetchOne($sql1);

        if ($id) {
            $sql2 = "update weixin_coupon set status = 1 , modify_time = '" . $modify_time . "', uid = '" . $openId . "' where id = '" . $id . "'";
            $this->writeConnection->query($sql2);
            $sql3 = "select * from weixin_coupon where id = '" . $id . "'";
            $record = $this->readConnection->fetchRow($sql3);
            return $record;
        }

        return -1;
    }

//    public function getSponsorId($orderId)
//    {
//        $sql = "select open_id from weixin_promotion where order_id = '" . $orderId . "' and sponsor_flag = 1";
//        $result = $this->readConnection->fetchOne($sql);
//        if ($result) {
//            return 0;
//        }
//        return $result;
//    }
//
    public function getSponsorTel($orderId) {

        $sql = "select telephone_no from weixin_promotion where order_id = '".$orderId."' and (sponsor_flag = 0 || sponsor_flag = 1)";
        $result = $this->readConnection->fetchOne($sql);
        if (!$result) {
            return 0;
        }
        return $result;
    }

    public function getSponsorOrderTel($orderId){

        $orders = Mage::getModel('sales/order')->getCollection();

        $orders->addAttributeToFilter('increment_id', $orderId); //其中 $incrementID为订单号

        $orders->addAttributeToSelect('*');

        $orders->load();

        $alldata = $orders->getData();
        if (!$alldata || count($alldata) < 1 ){
            throw new Exception('checkOrderId 订单不存在。');
        }

        $oid_entity_id = $alldata[0]['entity_id'];
        if ($oid_entity_id == '')  {
            throw new Exception('checkOrderId 订单不存在。');
        }

        $sales_order = Mage::getModel('sales/order')->load($oid_entity_id);
        $billingAddress=$sales_order->getBillingAddress();
        $telephone = $billingAddress->getTelephone();

        return $telephone;
    }

    /**
     * 取得某个用户某次活动的订单验证码
     * @param $oid 订单ID
     * @param $actId 活动ID
     * @return bool|int 验证码
     */
    public function getCheckCode($oid)
    {
        $keyFeature = hexdec(substr(md5('8888948'), 8, 4));

        $actIdFeature = hexdec(substr(md5(WEIXIN_PROMOTION_ACTIVITY_ID), 16, 4));

        $oidFeature = hexdec(substr(md5($oid), 24, 4));

        $sumFeature = $keyFeature + $actIdFeature + $oidFeature;

        Mage::log("getCheckCode = ".$sumFeature);
        return $sumFeature;
    }

    public  function  isPromotionOrderId($incrementID) {
        try {
            $orders = Mage::getModel('sales/order')->getCollection();

            $orders->addAttributeToFilter('increment_id', $incrementID); //其中 $incrementID为订单号

            $orders->addAttributeToSelect('*');

            $orders->load();

            $alldata = $orders->getData();
            if (!$alldata || count($alldata) < 1 ){
                throw new Exception('checkOrderId 订单不存在。');
            }

            $oid_entity_id = $alldata[0]['entity_id'];
            if ($oid_entity_id == '')  {
                throw new Exception('checkOrderId 订单不存在。');
            }

            $sales_order = Mage::getModel('sales/order')->load($oid_entity_id);

            if (strcasecmp(WEIXIN_PROMOTION_ORDEY_STATUS, $sales_order->getStatus()) != 0) {
                return false;
//                throw new Exception('订单状态不正确 : '.$sales_order->getStatus().' != '.WEIXIN_PROMOTION_ORDEY_STATUS);
            }

            if (!(strtotime($sales_order->getCreatedAt())>=strtotime(WEIXIN_PROMOTION_START_TIME) &&
                strtotime($sales_order->getUpdatedAt())>=strtotime(WEIXIN_PROMOTION_START_TIME) &&
                strtotime($sales_order->getCreatedAt())<strtotime(WEIXIN_PROMOTION_END_TIME) &&
                strtotime($sales_order->getUpdatedAt())<strtotime(WEIXIN_PROMOTION_END_TIME))) {
                return false;
//                throw new Exception('订单时间在不正确。');
            }

            if (!strpos($sales_order->getCustomerEmail(),WEIXIN_PROMOTION_ORDEY_EMAIL) ||
                !strstr($sales_order->getCustomerNote(), WEIXIN_PROMOTION_ORDEY_NOTE)) {
                return false;
//                throw new Exception('测试验证不通过。');
            }

            $grand_total = $alldata[0]['grand_total'];
            if ($grand_total < 100) {
                return false;
//                throw new Exception('订单数额不足');
            }
            
    //        $billingAddress=$sales_order->getBillingAddress();
    //
    //        $Email=$sales_order->getData('customer_email'); //客户的邮件
    //
    //        foreach ($sales_order->getAllItems() as $item) {
    //
    //            $option = $item->getProductOptions();
    //
    //            $qty =   $item->getQtyOrdered();
    //        }
            return true;
        } catch (Exception $ex) {
            mage::log("Exception : ".$ex->getMessage(),
                Zend_Log::ERR);
        }
        return false;
    }

}