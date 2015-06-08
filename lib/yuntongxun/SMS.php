<?php

define('YUN_TONG_XUN_ROOT',  dirname(__FILE__).'/');
require_once YUN_TONG_XUN_ROOT . 'CCPRestSmsSDK.php';

final class YunTongXun_SMS {
    /*
 *  Copyright (c) 2014 The CCP project authors. All Rights Reserved.
 *
 *  Use of this source code is governed by a Beijing Speedtong Information Technology Co.,Ltd license
 *  that can be found in the LICENSE file in the root of the web site.
 *
 *   http://www.yuntongxun.com
 *
 *  An additional intellectual property rights grant can be found
 *  in the file PATENTS.  All contributing project authors may
 *  be found in the AUTHORS file in the root of the source tree.
 */



//主帐号,对应开官网发者主账号下的 ACCOUNT SID
    static private $accountSid= '8a48b5514db9e13d014dbd09869701ea';

//主帐号令牌,对应官网开发者主账号下的 AUTH TOKEN
    static private $accountToken= '1ef5dc6c5a6b4fed8081dc491aaea047';

//应用Id，在官网应用列表中点击应用，对应应用详情中的APP ID
//在开发调试的时候，可以使用官网自动为您分配的测试Demo的APP ID
    static private $appId='aaf98f894dbd42d6014dbd653d7f0042';

//请求地址
//沙盒环境（用于应用开发调试）：sandboxapp.cloopen.com
//生产环境（用户应用上线使用）：app.cloopen.com
    static private $serverIP='sandboxapp.cloopen.com';


//请求端口，生产环境和沙盒环境一致
    static private $serverPort='8883';

//REST版本号，在官网文档REST介绍中获得。
    static private $softVersion='2013-12-26';


    /**
     * 发送模板短信
     * @param to 手机号码集合,用英文逗号分开
     * @param datas 内容数据 格式为数组 例如：array('Marry','Alon')，如不需替换请填 null
     * @param $tempId 模板Id,测试应用和未上线应用使用测试模板请填写1，正式应用上线后填写已申请审核通过的模板ID
     */
    public static function send($to,$datas,$tempId)
    {
        // 初始化REST SDK
//        global $accountSid,$accountToken,$appId,$serverIP,$serverPort,$softVersion;
        $rest = new REST(self::$serverIP,self::$serverPort,self::$softVersion);
        $rest->setAccount(self::$accountSid,self::$accountToken);
        $rest->setAppId(self::$appId);

        // 发送模板短信
        echo "Sending TemplateSMS to $to <br/>";
        $result = $rest->sendTemplateSMS($to,$datas,$tempId);
        if($result == NULL ) {
            echo "result error!";

        }
        if($result->statusCode!=0) {
            echo "error code :" . $result->statusCode . "<br>";
            echo "error msg :" . $result->statusMsg . "<br>";
            //TODO 添加错误处理逻辑
        }else{
            echo "Sendind TemplateSMS success!<br/>";
            // 获取返回信息
            $smsmessage = $result->TemplateSMS;
            echo "dateCreated:".$smsmessage->dateCreated."<br/>";
            echo "smsMessageSid:".$smsmessage->smsMessageSid."<br/>";
            //TODO 添加成功处理逻辑
        }
    }
}