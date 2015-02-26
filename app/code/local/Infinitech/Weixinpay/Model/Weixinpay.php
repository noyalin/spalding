<?php

class Infinitech_Weixinpay_Model_Weixinpay extends Mage_Core_Model_Abstract
{
    public $appid = "wx79873079dca36474";
    public $appsecret = "ba74acc7f680e7bbe62203815df1df41";
    //获取AccessToken
    /*
     *
    CREATE TABLE IF NOT EXISTS `weixin_token` (
  `id` int(11) NOT NULL auto_increment,
  `token` varchar(800) NOT NULL,
  `createtime` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
     */
    public function getAccessToken(){
        $appid = $this->appid;
        $appsecret = $this->appsecret;
        // Get resource
        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');
        $writeConnection = $resource->getConnection('core_write');
        $query = "select token,createtime  FROM weixin_token where id=1";
        $res = $readConnection->fetchAll($query);
        if(!empty($res)){
            $arr = $res[0];
            $createtime = $arr['createtime'];
            if(time()-$createtime < 2*3600){
                return $arr['token'];
            }
        }
        $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$appid&secret=$appsecret";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检查，0表示阻止对证书的合法性的检查。
        @curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 1); // 从证书中检查SSL加密算法是否存在
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0)");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $r = curl_exec($ch);
        $result = json_decode ($r ,true);
        if($result){
            $token = $result['access_token'];
            if(empty($res)){
                $time = time();
                $sql = "insert into weixin_token(token,createtime) values ('$token',$time)";
                $writeConnection->query($sql,array($token));
            }else{
                $time = time();
                $sql = "update weixin_token set token='$token',createtime=$time where id=1";
                $writeConnection->query($sql);
            }
            return $result['access_token'];
        }else{
            return null;
        }

    }

    public function getList(){
        $connection = Mage::getSingleton('core/resource')->getConnection('core_read');
        $select = $connection->select()
            ->from('alipay_log', array('trade_no','type','post_data'))
            ->where(' type="weixinsuccess" ');
        $res = $connection->fetchAll($select);
        foreach($res as $each){
            $post_data = $each['post_data'];
            $arr = explode('|',$post_data);
            $out_trade_no = $arr[0];
            $trade_no = $arr[1];
            $openId = $arr[2];
            
        }
    }
}