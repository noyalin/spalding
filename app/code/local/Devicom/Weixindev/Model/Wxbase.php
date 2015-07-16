<?php
class Devicom_Weixindev_Model_Wxbase extends Devicom_Weixindev_Model_Dbconn {
    
    public  $appid = "wx79873079dca36474";
    public  $appsecret="ba74acc7f680e7bbe62203815df1df41";

    public $conn ;
    public $postObj;

    public function responseMsg()
    {
        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
        if (!empty($postStr)){
            $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
            $this->postObj = $postObj;
            $fromUsername = trim($postObj->FromUserName);
            $toUsername = trim( $postObj->ToUserName);
            $keyword = trim($postObj->Content);

            //将用户OPENID保存数据库
            $this->saveOpenID(trim($fromUsername));
            $time = time();
            $MsgType = trim($postObj->MsgType) ;//获取消息类型
            if($MsgType == 'event')
            {
                $MsgEvent = $postObj->Event;//获取事件类型
                if($MsgEvent == 'subscribe')
                {
                    $this->saveOpenID(trim($fromUsername));
                    //订阅事件,初次关注发送信息
                    $mesType = "text";
                    $contentStr = "欢迎关注斯伯丁中国。 点击下方菜单进行真伪验证及订单查询，或直接访问斯伯丁中国微商城。您也可以尝试留言，说不定丁丁君会回复你哦~";
                    $this->responseTextMsg($postObj,$contentStr);
                    $this->saveSessionLast("checkcode",$fromUsername);
                }
                if($MsgEvent == 'CLICK')//点击事件
                {
                    $EventKey = strtolower(trim( $postObj->EventKey));//获取自定义菜单时的key值
                    $this->saveSessionLast($EventKey,$fromUsername);
                    switch($EventKey)
                    {
                        case "identity_number":
                            $msgPhone = "亲，您好！请输入订单的收件人相关信息，\n格式为: 收件人姓名+收件人手机号+收件人身份证号 例如 \n 张三+1381XX10630+\n320382XXXX01110737";
                            $this->responseTextMsg($postObj,$msgPhone);
                            $this->saveSessionIdentityNum($fromUsername,json_encode(array(1)));
                            break;
                        case "000000":
                            //点击返回超链接事件
                            $msgType = "text";
                            $contentStr = "<a href='http://sneakerhead.jd.com/'>品牌介绍</a>";
                            $this->responseTextMsg($postObj,$contentStr);
                            break;
                        case "phone-bind":
                            //手机绑定事件
                            $contentStr = "手机绑定是为Spalding顾客特别定制功能，绑定成功后，您将第一时间查到发货物流消息！如需服务，请输入收货人手机号！";

                            $this->responseTextMsg($postObj,$contentStr);
                            break;
                        case "ship-orderid":
                            //订单查询事件
//                            $res = $this->findOne("select orderid  FROM weixin_user where openid=?",array($fromUsername));
//                            if(is_numeric($res[0]))
//                            {
//                                $contentStr = $this->getInfoByOrderNum($res[0]);
//                                $_SESSION['last'] = "";
//                                $this->responseTextMsg($postObj,$contentStr);
//                                break;
//                            }
//                            else
//                            {
                            $contentStr = "首先感谢您对Spalding的支持。请输入天猫订单号获取订单最新物流信息。";
                            $this->responseTextMsg($postObj,$contentStr);
                            break;
//                            }
                        case "ship-phone":
                            //手机查询事件
                            $res = $this->readConnection->fetchRow("select phone  FROM weixin_user where openid='$fromUsername'");
//                            $contentStr =$res[0];
                            if(!empty($res) && isset($res[0]) && is_numeric($res[0]))
                            {
                                $phoneNum = $res[0];
//                                $this->responseTextMsg($postObj,"aaa");
                                $contentStr = $this->getInfoByPhoneNum($phoneNum,$postObj);

                                $this->saveSessionLast('',$fromUsername);
                                if($contentStr){
                                    $this->responseTextMsg($postObj,$contentStr);
                                }else{
                                    $this->responseTextMsg($postObj,"此手机号 $phoneNum 暂无物流信息，请检查手机号是否正确, \n 如果错误，请点击物流查询-手机绑定 重新绑定手机");
                                }

                                break;
                            }
                            else
                            {
                                $this->saveSessionLast('ship-phone',$fromUsername);
                                $contentStr = "首先感谢您对Spalding的支持。请输入收货人手机号获取订单最新物流信息。";
                                $this->responseTextMsg($postObj,$contentStr);
                                break;
                            }
                        case "checkcode":
                            try {
                                $this->saveSessionLast('checkcode',$fromUsername);
                                $contentStr = "请输入防伪码";
                                $this->responseTextMsg($postObj,$contentStr);
                            }catch (Exception $ex) {
                                Mage::log("checkcode Err = ".$ex->getMessage());
                            }
                            break;
                        case "game":
                            $contentStr = "互动游戏即将上线,敬请关注!";
                            $this->responseTextMsg($postObj,$contentStr);
                            break;
                        case "sign-up":
                            if ($this->getSignUpInfo($fromUsername) > 0) {
                                $contentStr = "你已报过名。";
                            } else {
                                $this->saveSessionLast('sign-up', $fromUsername);
                                $contentStr = "呐喊参与口号，回复参加城市和联系方式，幸运儿可能就是你！（如：There's only one Spalding,北京,小明,13888888888）\n";
                                $contentStr .= "温馨提示：会员凭真实姓名和手机号码入场，参与即有惊喜。";
                            }
                            $this->responseTextMsg($postObj, $contentStr);
                            break;
                        default :
                            $contentStr = "无响应事件";
                            $this->responseTextMsg($postObj,$contentStr);
                            break;
                    }
                }
            }else if($MsgType == 'text')
            {

                if(!empty($keyword) &&( ( trim(strtolower(str_replace(" ","",$keyword) ) )  == 'only1ball') || ( trim(strtolower(str_replace(" ","",$keyword) ) )  == 'onlyoneball'))     )
                {

                    $content = array();
                    $content[] = array("Title" =>"斯伯丁校园行来啦",
                        "Description" =>"点击图片进入",
                        "Picurl" =>"http://weixin.spaldingchina.com.cn/weixin/only1ball_weixin.jpg",
                        "Url" =>"http://mp.weixin.qq.com/s?__biz=MzA5MzI0ODMyMA==&mid=204249439&idx=1&sn=4ec01a65d50aeea31db2c0432eda2293#rd");
                    $this->transmitNews($postObj,$content);
//                    $this->responseTextMsg($postObj,"yes");
                }
                //订阅后立即绑定手机或者订单号
                $last = $this->getSessionLast($fromUsername);
                if(!$last){
                    $last = "checkcode";
                }
//                if($keyword == '111111'){
//                    $this->responseTextMsg($postObj,$fromUsername.'  LAST '.$last);
//                }

//               if(!is_numeric($keyword))
//                {
//                    $contentStr = "Spalding感谢您的留言，小编会在收到消息后尽快为您答复。";
//                    $this->responseTextMsg($postObj,"请输入16位数字");
//                }
                if(!empty($keyword) && $last == 'subscribe')
                {
                    $bool = $this->isphone($keyword);
                    if(is_numeric($keyword))
                    {
                        if($bool)
                        {
                            $this->savePhone($fromUsername,$keyword);
                            $contentStr = "手机绑定成功，我们发货的第一时间通知您！";
                            $this->saveSessionLast('',$fromUsername);
                            $this->responseTextMsg($postObj,$contentStr);
                        }
                        else
                        {
                            $this->saveOrdernum($fromUsername,$keyword);
                            $contentStr = "订单号绑定成功，我们将在发货的第一时间通知您！";
                            $this->saveSessionLast('',$fromUsername);
                            $this->responseTextMsg($postObj,$contentStr);
                        }
                    }
                    else
                    {
                        $contentStr = "请输入纯数字正确的手机号或者订单号";
                        $this->saveSessionLast('subscribe',$fromUsername);
                        $this->responseTextMsg($postObj,$contentStr);
                    }
                }
                //手机绑定按钮
                if(!empty($keyword) && $last == 'phone-bind')
                {
                    //判断是否绑定的手机号

                    $bool = $this->isphone($keyword);
                    if($bool)
                    {
                        $this->savePhone($fromUsername,$keyword);
                        $contentStr = "绑定成功，我们将在发货的第一时间通知您！";
                        $this->responseTextMsg($postObj,$contentStr);
                    }
                    else
                    {
                        $contentStr = "您输入的手机号无法查到对应订单，麻烦核实后再次输入。注意：需要提供收货人手机号。";
                        $this->responseTextMsg($postObj,$contentStr);
                    }
                }

                //订单查询
//                $this->responseTextMsg($postObj,"okfirst");
//                $this->responseTextMsg($postObj,  $_SESSION['operation'].$keyword." TOP ". $_SESSION['last'] . "   SESS ".md5( trim($fromUsername).trim($toUsername) ));
                if(!empty($keyword) && $last == 'ship-orderid')
                {
//                    $this->responseTextMsg($postObj,$keyword." T ". $_SESSION['last']);
//                    $this->responseTextMsg($postObj,'yes');
                    $contentStr = $this->getInfoByOrderNum(trim($keyword));
                    $this->responseTextMsg($postObj,$contentStr);
                }
                //手机查询
                if(!empty($keyword) && $last == 'ship-phone')
                {
					//$contentStr = "测试";
                    $contentStr = $this->getInfoByPhoneNum(trim($keyword),$postObj);
                    $this->responseTextMsg($postObj,$contentStr);
                }
                //防伪验证
                if(!empty($keyword) && $last == 'checkcode')
                {

                    $contentStr = $this->checkCode(trim($keyword),$postObj);
//                    $contentStr = "yes";
                    $this->responseTextMsg($postObj,$contentStr);
                }
                //NBA Nation
                if(!empty($keyword) && $last == 'sign-up')
                {
                    $ret = $this->checkSignUpInfo(trim($keyword), $postObj);
                    if ($ret == 4) {
                        $contentStr = "手机号码不正确，请重新输入。";
                    } else if ($ret == 3) {
                        $contentStr = "格式不正确，请重新输入。";
                    } else if ($ret == 2) {
                        $contentStr = "你已经报过名。";
                        $this->saveSessionLast('', $fromUsername);
                    } else if ($ret == 1) {
                        $contentStr = "报名成功。";
                        $this->saveSessionLast('', $fromUsername);
                    } else {
                        $contentStr = "系统异常，请联系客服。";
                    }
                    $this->responseTextMsg($postObj, $contentStr);
                }
                //only 1 ball
//                if(!empty($keyword) &&  (  strtolower(str_replace(" ",'',trim($keyword)))  == 'only1ball')   )



            }

        }else{
            echo "～0～";
            exit;
        }
    }
    /**
     * 回复文本消息
     * @param $to
     * @param $from
     * @param $text
     */
    protected function responseTextMsg($object, $text)
    {
        $msg =<<<EOF
<xml>
<ToUserName><![CDATA[%s]]></ToUserName>
<FromUserName><![CDATA[%s]]></FromUserName>
<CreateTime>12345678</CreateTime>
<MsgType><![CDATA[text]]></MsgType>
<Content><![CDATA[%s]]></Content>
</xml>
EOF;
        $msg = sprintf($msg, $object->FromUserName, $object->ToUserName, $text);

        echo $msg;
    }

    /**
     * @param $object
     * @param $arr_item
     * @param int $flag
     * 图文消息
     */
    protected function transmitNews($object, $arr_item, $flag = 0)
    {
        if(!is_array($arr_item))
            return;

        $itemTpl = "    <item>
        <Title><![CDATA[%s]]></Title>
        <Description><![CDATA[%s]]></Description>
        <PicUrl><![CDATA[%s]]></PicUrl>
        <Url><![CDATA[%s]]></Url>
    </item>
";
        $item_str = "";
        foreach ($arr_item as $item){
            $item['Url'] = str_replace('%','',$item['Url']);
            $item['Url'] = htmlspecialchars($item['Url']);
            $item_str .= sprintf($itemTpl, $item['Title'], $item['Description'], $item['Picurl'], $item['Url']);
        }


        $newsTpl = "<xml>
<ToUserName><![CDATA[%s]]></ToUserName>
<FromUserName><![CDATA[%s]]></FromUserName>
<CreateTime>%s</CreateTime>
<MsgType><![CDATA[news]]></MsgType>
<Content><![CDATA[]]></Content>
<ArticleCount>%s</ArticleCount>
<Articles>
%s</Articles>
<FuncFlag>%s</FuncFlag>
</xml>";

        $resultStr = sprintf($newsTpl, $object->FromUserName, $object->ToUserName, time(), count($arr_item),$item_str, $flag);

        echo $resultStr;
    }
    public function echoMes($str){
        $file = "/data/log/apache2/error.log";
        file_put_contents($file,$str,FILE_APPEND | LOCK_EX);

    }
    public function valid($echoStr)
    {
        if($this->checkSignature()){
            echo $echoStr;
            exit;
        }
    }

    private function checkSignature()
    {
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];

        $token = TOKEN;
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );
        if( $tmpStr == $signature ){
            return true;
        }else{
            return false;
        }
    }
//获取AccessToken
    public function getAccessToken(){
        $appid = $this->appid;
        $appsecret = $this->appsecret;
        $res = $this->readConnection->fetchRow("select *  FROM weixin_appset where id=1");
        if(!empty($res) && $res['access_token']){
            $createtime = $res['createtime'];
            if(time()-$createtime < 2*3600){
                return $res['access_token'];
            }
        }
        $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$appid&secret=$appsecret";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检查，0表示阻止对证书的合法性的检查。
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 1); // 从证书中检查SSL加密算法是否存在
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0)");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $r = curl_exec($ch);
        $result = json_decode ($r ,true);
        if($result){
            $token = $result['access_token'];
                $time = time();
                $sql = "update weixin_appset set access_token='$token',createtime=$time where id=1";
            $this->writeConnection->query($sql,array($token));
            return $result['access_token'];
        }else{
            return null;
        }

    }
    //获取用户信息
    public function getUserInfo($accessToken,$openId){
        $url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=$accessToken&openid=$openId";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检查，0表示阻止对证书的合法性的检查。
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 1); // 从证书中检查SSL加密算法是否存在
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0)");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $r = curl_exec($ch);
        $result = json_decode ($r ,true);
        if($result){
            return $result;
        }else{
            return null;
        }
    }
    //存储用户头像
    public function saveHeadImage($url = "", $filename = ""){
        if(is_dir(basename($filename))) {
            echo "The Dir was not exits";
            return false;
        }
        //去除URL连接上面可能的引号
        //$url = preg_replace( '/(?:^[\'"]+|[\'"\/]+$)/', '', $url );
        $hander = curl_init();
        $fp = fopen($filename,'wb');
        curl_setopt($hander,CURLOPT_URL,$url);
        curl_setopt($hander,CURLOPT_FILE,$fp);
        curl_setopt($hander,CURLOPT_HEADER,0);
        curl_setopt($hander,CURLOPT_FOLLOWLOCATION,1);
        curl_setopt($hander,CURLOPT_TIMEOUT,60);
        curl_exec($hander);
        curl_close($hander);
        fclose($fp);
        return true;
    }

    //根据订单号获取物流信息,,到时候要将我们数据库中的sf-》shunfeng
    function getInfoByOrderNum($ordernum)
    {
        $strInfo = "";
        $com = array();
        $nu = array();
        $dataInfo = "";
        $result = $this->getOrderInfoByOrderNum($ordernum);
        if(empty($result))
        {
            //$strInfo = "暂无此订单号信息，请检查订单号是否正确！";
            $strInfo = "物流公司暂未提供相关信息，建议您在下单后一天进行查询！或检查订单号是否正确！（已收件信息无法查询）";
        }else
        {
            $product = $result['Order']['Product'] ;
            if(isset($product['ProductName'])){
                $product = array($product);
            }
            for($i = 0;$i < count($product);$i++)
            {
                $com[$i] = strtolower($product[$i]['Carrier']);
                $nu[$i] = $product[$i]['TrackingID'];
                $kuaidiInfo = $this->getKuaiDiInfo($com[$i],$nu[$i]);

                if(is_array($kuaidiInfo))
                {
                    $data = $kuaidiInfo['data'];
                    $compony = $this->kuaidiToCn($kuaidiInfo['com']);
                    $j = $i+1;
                    $data[0]['context'] = str_replace("守门人","Spalding中国仓库",$data[0]['context']);
                    $data[0]['context'] = str_replace("收件人","Spalding中国仓库",$data[0]['context']);
                    $data[0]['context'] = str_replace("前台","Spalding中国仓库",$data[0]['context']);
                    $data[0]['context'] = str_replace("办公室","Spalding中国仓库",$data[0]['context']);
                    $strInfo .= $j."快递公司:".$compony."\n"."运单号:".$kuaidiInfo['nu']."\n".$data[0]['time']."\n".$data[0]['context']."\n";//$kuaidiInfo['com']."运单号".$kuaidiInfo['nu'].
                }else
                {
                    //$strInfo = $kuaidiInfo;
                    $mycom = $this->changeKD($com[$i]);
                    $mycom = $this->kuaidiToCn($mycom);
                    $strInfo = $kuaidiInfo."\n"."快递公司:".$mycom."\n"."运单号:".$nu[$i]."\n";
                }

            }
        }
        //echo $strInfo;
        $strInfo = $strInfo."（以上信息来自第三方物流平台，可能稍有延时，如需更及时物流信息可根据返回的快递公司及单号自行查询！）";
        return $strInfo;
    }


    //根据手机号获取物流信息
    public function getInfoByPhoneNum($phonenum,$postObj)
    {
        $strInfo = "";
        $com = array();
        $nu = array();
        $dataInfo = "";

        $result = $this->getOrderInfoByPhoneNum($phonenum,$postObj);
        if(empty($result))
        {
            //$strInfo = "暂无此手机号信息，请检查手机号是否正确！";
            $strInfo = "物流公司暂未提供相关信息，建议您在下单后一天进行查询！或检查手机号是否正确！（已收件信息无法查询）";
        }else
        {
            $product = $result['Order']['Product'] ;
           // $this->responseTextMsg($postObj,print_r($product,true));
            if(isset($product['ProductName'])){
                $product = array($product);
            }
            for($i = 0;$i < count($product);$i++)
            {
                //$strInfo .= "您的".$result['Order']['Product'][$i]['ProductName']."快递信息如下"."\n";
                $com[$i] = strtolower($product[$i]['Carrier']);
                $nu[$i] = $product[$i]['TrackingID'];
                $kuaidiInfo = $this->getKuaiDiInfo($com[$i],$nu[$i],0,$postObj);
//                $this->responseTextMsg($postObj,print_r( $kuaidiInfo ,true));
                $j = $i+1;
                if(is_array($kuaidiInfo))
                {

                    $data = $kuaidiInfo['data'];
                    $compony = $this->kuaidiToCn($kuaidiInfo['com']);

                    $context =  $data[0]['context'];
                    $context = str_replace("守门人","Spalding中国仓库",$context);
                    $context = str_replace("收件人","Spalding中国仓库",$context);
                    $context = str_replace("前台","Spalding中国仓库",$context);
                    $context = str_replace("办公室","Spalding中国仓库",$context);
                    $number = $kuaidiInfo['nu'];
                    $aHref = "http://m.kuaidi100.com/result.jsp?nu=".$number;
                    $alink = "<a href='".$aHref."'>".$number."</a>";
                    $strInfo .= $j.":快递公司:".$compony."\n"."运单号:".$number."\n".$data[0]['time']."\n".$context."\n";
//                    $this->responseTextMsg($postObj,print_r( $strInfo ,true));
                }else
                {
                    $mycom = $this->changeKD($com[$i]);
                    $mycom = $this->kuaidiToCn($mycom);
                    $strInfo = $kuaidiInfo."\n"."快递公司:".$mycom."\n"."运单号:".$nu[$i]."\n";
                }
            }
        }

        //echo $strInfo;
        $strInfo .= "（以上信息来自第三方物流平台，可能稍有延时，如需更及时物流信息可根据返回的快递公司及单号自行查询！）";
        return $strInfo;
    }
    //将快递100的快递公司名替换成中文
    function kuaidiToCn($com)
    {
        $express="";
        switch(strtolower($com))
        {
            case "shunfeng":
                $express = "顺丰";
                break;
            case "ups":
                $express = "UPS";
                break;
            case "yuantong":
                $express = "圆通";
                break;
            case "shentong":
                $express = "申通";
                break;
            case "yunda":
                $express = "韵达";
                break;
            case "zhongtong":
                $express = "中通";
                break;
            case "ems":
                $express = "EMS";
                break;

        }
        return $express;
    }
    //从我们数据库中获取的快递名称更换为快递100接口的快递名称
    function changeKD($com)
    {
        $baiduKD="";
        switch(strtolower($com))
        {
            case "sf":
                $baiduKD = "shunfeng";
                break;
            case "ups":
                $baiduKD = "ups";
                break;
            case "yto":
                $baiduKD = "yuantong";
                break;
            case "sto":
                $baiduKD = "shentong";
                break;
            case "yunda":
                $baiduKD = "yunda";
                break;
            case "zto":
                $baiduKD = "zhongtong";
                break;
            case "ems":
                $baiduKD = "ems";
                break;

        }
        return $baiduKD;
    }
    //根据订单号从webservice获取订单信息
    function getOrderInfoByOrderNum($orderid)
    {
        $orderId = $orderid;
        if(!$orderId){
            echo"订单号错误，不能为空";die;
        }
        if ($_SERVER['SERVER_NAME'] == 'localhost' || $_SERVER['SERVER_ADDR'] == '127.0.0.1') {
            $url = "http://10.0.0.24:10011/OrderInfoForWeixin.ashx?OrderID=$orderId";
        }else{
            $url = "http://222.73.202.154:10011/OrderInfoForWeixin.ashx?OrderID=$orderId";
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检查，0表示阻止对证书的合法性的检查。
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0); // 从证书中检查SSL加密算法是否存在
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $r = curl_exec($ch);

        $result = json_decode ($r ,true);
        return $result;

    }
    //根据手机号从webservice获取订单信息
    function getOrderInfoByPhoneNum($phone,$postObj)
    {
        //$orderId = $phone;
        $phonenum = $phone;
        $result = null;
        if(!$phonenum){
            echo"手机号错误，不能为空";die;
        }
        if ($_SERVER['SERVER_NAME'] == 'localhost' || $_SERVER['SERVER_ADDR'] == '127.0.0.1') {
            $url = "http://10.0.0.24:10011/OrderInfoForWeixin.ashx?PhoneNumber=$phonenum";
        }else{
            $url = "http://222.73.202.154:10011/OrderInfoForWeixin.ashx?PhoneNumber=$phonenum";
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检查，0表示阻止对证书的合法性的检查。
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0); // 从证书中检查SSL加密算法是否存在
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $r = curl_exec($ch);

        $result = json_decode ($r ,true);
       // $this->responseTextMsg($postObj,print_r($result,true));
        return $result;
    }

    //从快递100获取物流信息
//    public function getKuaiDiInfo($com,$nu,$count=0,$phonenum=null)
//    {
//        $baiducom = $this->changeKD($com);
// //       $url = sprintf('http://baidu.kuaidi100.com/query?type=%s&postid=%s',$baiducom,$nu);
//        $url = sprintf('http://www.kd250.com/index.php/Home/Index/query?type=%s&postid=%s',$baiducom,$nu);
//        //$url = sprintf('http://api.ickd.cn/?id=%s&secret=%s&com=%s&nu=%s&encode=%s&type=%s',$id,$secret,$com,$nu,$encode,$type);
//        $ch = curl_init($url);
//        curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
//        curl_setopt($ch,CURLOPT_HEADER,false);
//        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0)");
//        $r = curl_exec($ch);
//        $result = json_decode ($r ,true);
////        session_unset();
////        $sessionName = "$phonenum - $com - $nu";
// //       $_SESSION[$sessionName] = $result['status'];
//
//        if($result['status'] != '200')
//        {
//            if($count <= 3){
//                $count++;
//
////                if($phonenum){
////                   // $_SESSION[$sessionName." IN"] = $count." Test";
////                }
//
//                $result =  $this->getKuaiDiInfo($com,$nu,$count,$phonenum);
//            }
//        else{
//                $result =  "快递公司参数异常，单号不存在或者已经过期,请检查后重试！";
//            }
//        }
//        return $result;
//    }


    //向某个用户发送消息
    public function sendMsg($openid,$content)
    {

        $data = '{
        "touser":"'.$openid.'",
        "msgtype":"text",
        "text":
            {
                "content":"'.$content.'"
            }
        }';
        $access_token = $this->getAccessToken();
        $url = "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=".$access_token;
        $res = $this->https_post($url,$data);
        return $res;
    }

    function https_post($url,$data)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($curl);
        //   var_dump($result);
        if (curl_errno($curl)) {
            return 'Errno'.curl_error($curl);
        }
        curl_close($curl);
        return json_decode($result,true);
    }
    //判断是否为电话
    function isphone($num)
    {
        if(is_numeric($num))
        {
            if(strlen($num)==11)
            {
                return true;
            }
            else return false;
        }else return false;
    }

    //根据baidu.kuaidi接口获取快递实时信息
    function  getKuaiDiInfo($com,$nu,$count=0,$postObj=null)
    {
        $baiducom = $this->changeKD($com);
        //$url = sprintf('http://www.kd250.com/index.php/Home/Index/query?type=%s&postid=%s',$baiducom,$nu);
        $url = sprintf('http://baidu.kuaidi100.com/query?type=%s&postid=%s',$baiducom,$nu);
        //$url = sprintf('http://api.ickd.cn/?id=%s&secret=%s&com=%s&nu=%s&encode=%s&type=%s',$id,$secret,$com,$nu,$encode,$type);
        $ch = curl_init($url);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
        curl_setopt($ch,CURLOPT_HEADER,false);
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0)");
        $r = curl_exec($ch);
        $result = json_decode ($r ,true);
        if($result['status'] != '200')
        {
            if($count <= 3){
                $count++;
                $result =  $this->getKuaiDiInfo($com,$nu,$count);
            }else{
                $result = "快递信息获取失败,麻烦根据以下单号自行查询！";
                //return "正在努力取得快递信息，请重试";
            }
            //$result = "快递公司参数异常，单号不存在或者已经过期";
        }
        return $result;
    }

    function isCreditNo($vStr)
    {
        $vCity = array(
            '11','12','13','14','15','21','22',
            '23','31','32','33','34','35','36',
            '37','41','42','43','44','45','46',
            '50','51','52','53','54','61','62',
            '63','64','65','71','81','82','91'
        );

        if (!preg_match('/^([\d]{17}[xX\d]|[\d]{15})$/', $vStr)) return false;

        if (!in_array(substr($vStr, 0, 2), $vCity)) return false;

        $vStr = preg_replace('/[xX]$/i', 'a', $vStr);
        $vLength = strlen($vStr);

        if ($vLength == 18)
        {
            $vBirthday = substr($vStr, 6, 4) . '-' . substr($vStr, 10, 2) . '-' . substr($vStr, 12, 2);
        } else {
            $vBirthday = '19' . substr($vStr, 6, 2) . '-' . substr($vStr, 8, 2) . '-' . substr($vStr, 10, 2);
        }

        if (date('Y-m-d', strtotime($vBirthday)) != $vBirthday) return false;
        if ($vLength == 18)
        {
            $vSum = 0;

            for ($i = 17 ; $i >= 0 ; $i--)
            {
                $vSubStr = substr($vStr, 17 - $i, 1);
                $vSum += (pow(2, $i) % 11) * (($vSubStr == 'a') ? 10 : intval($vSubStr , 11));
            }

            if($vSum % 11 != 1) return false;
        }

        return true;
    }

    /**
     * @param $str
     * @return array
     * 通过正则表达式取得姓名和手机号
     */
    function getPhoneAndNameByRex($str){
        $phone = null;
        $name = null;
        $str = str_replace("-","",$str);
        $str = str_replace("+","",$str);
        $str = str_replace("?","",$str);
        $str = str_replace("/","",$str);
        $str = str_replace(":","",$str);
        $pattern = "/\d+/";
        if(preg_match($pattern, $str, $matches)){
            $phone = $matches[0];
        }
        $name = str_replace($phone,"",$str);
        return array($phone,$name);
    }

    function getPhoneNameAndIdcard($str){
        $str = str_replace("-","",$str);
        $str = str_replace("?","",$str);
        $str = str_replace("/","",$str);
        $str = str_replace(":","",$str);
        $arr = explode("+",$str);
        if(count($arr) == 3){
            return array($arr[0],$arr[1],$arr[2]);
        }else if(count($arr) == 2){
            return array($arr[0],$arr[1],"");
        }else{
            return array($arr[0],"","");
        }

    }

    /**
     * @param $phone
     * @param $username
     * 判断此用户名和手机号是否存在
     */
    function curlSynShip($phone,$username){
        $post_data = array();
        $post_data['shipPhone'] = $phone;
        $post_data['shipName'] = $username;
        $url='http://10.0.0.12:8080/open/idcard/idcardservice/checkIdCardExist';
        $o="";
        foreach ($post_data as $k=>$v)
        {
            $o.= "$k=".urlencode($v)."&";
        }
        $post_data=substr($o,0,-1);
        $ch = curl_init();
        header('Content-type: text/html; charset=UTF-8');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $r = curl_exec($ch);
        $result = json_decode ($r ,true);
        return $result;
    }

    public function curlSynShipCheckExist($phone,$username){
        $post_data = array();
        $t = time();
        $post_data['timeStamp'] = $t;
        $post_data['signature'] = $this->checkSignatureInSynship($t);
        $post_data['shipPhone'] = $phone;
        $post_data['shipName'] = $username;
        //内网测试地址
//        $url='http://10.0.0.12:8080/open/idcard/idcardservice/checkIdCardExist';
        //外网地址
        $url='http://api.synship.net/open/idcard/idcardservice/checkIdCardExist';
        $o="";
        foreach ($post_data as $k=>$v)
        {
            $o.= "$k=".urlencode($v)."&";
        }
        $post_data=substr($o,0,-1);
        $ch = curl_init();
        header('Content-type: text/html; charset=UTF-8');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $r = curl_exec($ch);
        $result = json_decode ($r ,true);
        return $result;
    }
    public function checkSignatureInSynship($timestamp)
    {
        $token = "SynShipIdCard";
        $tmpArr = array($token, $timestamp);
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );
        return $tmpStr;
    }

    public function curlSynShipSaveCredit($phone,$username,$idCard){
        $post_data = array();
        $t = time();
        $post_data['timeStamp'] = $t;
        $post_data['signature'] = $this->checkSignatureInSynship($t);
        $post_data['shipPhone'] = $phone;
        $post_data['shipName'] = $username;
        $post_data['idCard'] = $idCard;
        //内网测试地址
//        $url='http://10.0.0.12:8080/open/idcard/idcardservice/checkIdCardExist';
        //外网地址
        $url='http://api.synship.net/open/idcard/idcardservice/recordNewIdCard';
        $o="";
        foreach ($post_data as $k=>$v)
        {
            $o.= "$k=".urlencode($v)."&";
        }
        $post_data=substr($o,0,-1);
        $ch = curl_init();
        $this_header = array(
            "content-type: application/x-www-form-urlencoded;
            charset=UTF-8"
        );
        curl_setopt($ch,CURLOPT_HTTPHEADER,$this_header);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $r = curl_exec($ch);
        $result = json_decode ($r ,true);
        return $result;
    }
//    public function getServer($key = null, $default = null)
//    {
//        if (null === $key) {
//            return $_SERVER;
//        }
//
//        return (isset($_SERVER[$key])) ? $_SERVER[$key] : $default;
//    }
//    public function getClientIp($checkProxy = true)
//    {
//        if ($checkProxy && $this->getServer('HTTP_CLIENT_IP') != null) {
//            $ip = $this->getServer('HTTP_CLIENT_IP');
//        } else if ($checkProxy && $this->getServer('HTTP_X_FORWARDED_FOR') != null) {
//            $ip = $this->getServer('HTTP_X_FORWARDED_FOR');
//        } else {
//            $ip = $this->getServer('REMOTE_ADDR');
//        }
//
//        return $ip;
//    }
    function getContent($file_in){
        $xml = simplexml_load_string($file_in);
        $return = (String)$xml;
        $arr = explode('|',$xml);
        return $arr[17];
    }
    public function checkCode($fwcode,$postObj){
        $fwcode = str_replace(" ","",$fwcode);
        $post_data =  array (
            'QryChannel' => '10000',
            'FwCode' =>$fwcode,
            'CompanyId' =>5150,
            'QueryPwd' =>0000,
            'VerifyCode' =>"",
            'TermIp' =>"127.0.0.1",
            'AddrName' =>"",
        );
        //$url='http://180.166.202.70/kaixinwabao/index.php/api/user/login';
        $url='http://210.51.213.108/sbdfw/FwServices.asmx/QueryFw?';
        $o="";
        foreach ($post_data as $k=>$v)
        {
            $o.= "$k=".urlencode($v)."&";
        }
        $post_data=substr($o,0,-1);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $r = curl_exec($ch);
        $return = (String)$r;
        $arr = explode('|',$return);
        $str = "系统忙，请重新输入";
        if(!empty($arr)){
            if (count($arr) < 17) {
                Mage::log("return = ".$return);
                Mage::log("arr = ");
                Mage::log($arr);
            }
            $str = $arr[17];
            $str = str_replace("</string>","",$str);
            if(strstr($str,"4008155999")){
                $str = str_replace("4008155999"," 4000801876",$str);
            }
	    if(strstr($str,"4008-155-999")){
                $str = str_replace("4008-155-999"," 400-080-1876",$str);
            }
        }

        return $str;
    }

    function checkSignUpInfo($content, $postObj)
    {
        $str = strtr($content, array("'" => "''", "，" => ",","\\" => "\\\\","\"" => "\\\""));
        $array = explode(',', $str);
        if ($array == false || count($array) != 4) {
            // 格式不正确。
            $return = 3;
        } else {
            list($slogan, $city, $username, $telephone) = $array;
            $slogan = trim($slogan);
            $city = trim($city);
            $username = trim($username);
            $telephone = trim($telephone);
            $pattern = "/^0?1[3|4|5|8][0-9]\d{8}$/";
            if ($slogan == "" || $city == "" || $username == "" || $telephone == "") {
                // 格式不正确。
                $return = 3;
            } else if (preg_match($pattern, $telephone) == 0) {
                // 手机号码不正确。
                $return = 4;
            } else {
                $res = $this->saveSignUpInfo($postObj->FromUserName, $slogan, $city, $username, $telephone);
                if ($res) {
                    // 报名成功。
                    $return = 1;
                } else {
                    // 你已经报过名。
                    $return = 2;
                }
            }
        }
        return $return;
    }
}
