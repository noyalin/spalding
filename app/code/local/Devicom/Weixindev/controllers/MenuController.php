<?php
class Devicom_Weixindev_MenuController extends Mage_Core_Controller_Front_Action{
    public function indexAction(){
        $wechatObj = Mage::getModel('weixindev/wxbase');
        $access_token = $wechatObj->getAccessToken();
        $jsonmenu = '{
      "button":
      [
      {
            "name":"我要",
             "sub_button":[
            {
               "type":"click",
               "name":"验真伪",
               "key":"checkcode"
            },
            {
               "type":"click",
               "name":"手机绑定",
                "key":"phone-bind"
            },
            {
               "type":"click",
               "name":"运单查询(手机)",
               "key":"ship-phone"
            },
             {
               "type":"click",
               "name":"运单查询(订单号)",
               "key":"ship-orderid"
            }
            ]
       },
	{
            "name":"买个球",
             "sub_button":[
            {
               "type":"view",
               "name":"商城首页",
                "url":"http://www.m.spaldingchina.com.cn/"
            },
 		   {
               "type":"view",
               "name":"经典篮球",
                "url":"http://www.m.spaldingchina.com.cn/products.html?attribute_set_name=72&product_catena=14"
            },
             {
               "type":"view",
               "name":"运动背包",
                "url":"http://www.m.spaldingchina.com.cn/catalogsearch/result/?q=%E5%8C%85"
            },
 		    {
               "type":"view",
               "name":"专业配件",
                "url":"http://www.m.spaldingchina.com.cn/products/accessories.html"
            },
            {
               "type":"view",
               "name":"专业篮球架",
                "url":"http://www.m.spaldingchina.com.cn/catalogsearch/result/?q=%E7%AF%AE%E7%90%83%E6%9E%B6"
            }
            ]
       },
       {
            "name":"来玩玩",
             "sub_button":[
            {
               "type":"view",
               "name":"互动游戏",
               "url":"http://222.73.202.154:18080/"
             },
             {
               "type":"view",
               "name":"All4Real",
               "url":"http://www.nba.spaldingchina.com.cn"
             }
            ]
       }
    ]
 }';


        $url = "https://api.weixin.qq.com/cgi-bin/menu/create?access_token=".$access_token;
        $result = $this->https_request($url, $jxsonmenu);
        var_dump($result);
    }

    function https_request($url,$data = null){
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        if (!empty($data)){
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($curl);
        curl_close($curl);
        return $output;
    }
}