<?php
class Devicom_Weixinevent_Block_Event1 extends Mage_Payment_Block_Form
{
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

}