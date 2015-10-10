<?php
session_start();
$orderId = trim($_POST['orderId']);
$url = 'http://open.synship.net/tracking/getCNInfo?cwb=' . $orderId;

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$r = curl_exec($ch);
curl_close($ch);

$xml = simplexml_load_string($r);
$return = "";
if ($xml == false) {
    $return = '<p class="checkErrorP">抱歉！未查到此运单<span class="checkError">32316027456537</span>信息，请<span class="checkError">确认运单号码</span>是否正确，订单号码相关信息请咨询155-4622-9782，更多物流动态可咨询95338。</p>';
} else {
    $xmlArray = (array)$xml;
    $items = array_reverse($xmlArray['row']);
    $i = 0;
    $return .= '<ul class="confirmSn">';
    foreach ($items as $item) {
        $itemArray = (array)$item;
        if ($i == 0) {
            $return .= '<li class="conNow">';
        } else {
            $return .= '<li>';
        }
        $return .= '<p>' . $itemArray['trackevent'] . '</p>';
        $return .= '<p>' . $itemArray['trackdatetime'] . '</p>';
        $return .= '</li>';
        $i++;
    } 
    $return .= '</ul>';
}
echo $return;