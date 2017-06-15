<?php
/**
 * Created by PhpStorm.
 * User: Bale
 * Date: 14-11-21
 * Time: 上午11:12
 */
session_start();
$verifycode = strtolower(trim($_POST['verifycode'])) ;
if($verifycode != strtolower($_SESSION["helloweba_num"])){
    echo '1';
    exit;
}
//exit;
$barcode = trim($_POST['barcode']);
$barcode = str_replace(" ","",$barcode);

//$barcodeLast = substr($barcode, strlen($barcode) - 1,1);
//if($barcodeLast == '1'){
//echo '2';
//exit;
//}else if($barcodeLast == '2'){
//echo '<注意!!> XXXXXXXXXXXX 该商品验证码已超过规定查询次数，谨防假冒！！如有疑问请拨打咨询电话4000801876。';
//exit;
//}else{
//echo '<注意!!> XXXXXXXXXXXX您查询的商品验证码不存在，谨防假冒!!如有疑问请拨打咨询电话4000801876。';
//exit;
//}
$post_data =  array (
    'code' =>$barcode
);
$url='http://query.t-secu.net/frontPages/jsp/customers/spalding.jsp';
//$url='http://api.t3315.com/sbdfw/FwServices.asmx/QueryFw?';//
$o="";
foreach ($post_data as $k=>$v)
{
    $o.= "$k=".urlencode($v)."&";
}
$post_data=substr($o,0,-1);

$handle = fopen('/alidata/www/spalding/var/log/barcodeVerify.log', 'a');
$txt = sprintf("%s\t IP : %s\t post_data : %s \n",date("Y-m-d H:i:s"),Tools::getClientIp(),$post_data);
fwrite($handle, $txt);
fclose($handle);

$ch = curl_init();
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_URL,$url);
curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$r = curl_exec($ch);
curl_close($ch);
$return = (String)$r;
echo $return;
//$arr = explode('|',$return);
//$str = "系统忙，请重新输入";
//var_dump()
//if(!empty($arr)){
//    $str = $arr[17];
//    $str = str_replace("</string>","",$str);
//    if(strstr($str,"4008155999")){
//    $str = str_replace("4008155999"," 4000801876",$str);
//    }
//    if(strstr($str,"4008-155-999")){
//    $str = str_replace("4008-155-999"," 400-080-1876",$str);
//    }
//}
//echo $str ;
exit;
//return $str;

final class Tools {

    //获取客户端IP
    public static function getClientIp()
    {
        if (getenv ( "HTTP_CLIENT_IP" ) && strcasecmp ( getenv ( "HTTP_CLIENT_IP" ), "unknown" ))
            $ip = getenv ( "HTTP_CLIENT_IP" );
        else if (getenv ( "HTTP_X_FORWARDED_FOR" ) && strcasecmp ( getenv ( "HTTP_X_FORWARDED_FOR" ), "unknown" ))
            $ip = getenv ( "HTTP_X_FORWARDED_FOR" );
        else if (getenv ( "REMOTE_ADDR" ) && strcasecmp ( getenv ( "REMOTE_ADDR" ), "unknown" ))
            $ip = getenv ( "REMOTE_ADDR" );
        else if (isset ( $_SERVER ['REMOTE_ADDR'] ) && $_SERVER ['REMOTE_ADDR'] && strcasecmp ( $_SERVER ['REMOTE_ADDR'], "unknown" ))
            $ip = $_SERVER ['REMOTE_ADDR'];
        else
            $ip = "unknown";
        return ($ip);
    }
}

