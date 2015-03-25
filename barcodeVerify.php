<?php
/**
 * Created by PhpStorm.
 * User: Bale
 * Date: 14-11-21
 * Time: 上午11:12
 */
session_start();
$verifycode = trim($_POST['verifycode']) ;
if($verifycode !=$_SESSION["helloweba_num"]){
    echo '1';
    return;
}
$barcode = trim($_POST['barcode']);
$barcode = str_replace(" ","",$barcode);
$post_data =  array (
    'QryChannel' => '10000',
    'FwCode' =>$barcode,
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
    $str = $arr[17];
    $str = str_replace("</string>","",$str);
}
echo $str;
//return $str;