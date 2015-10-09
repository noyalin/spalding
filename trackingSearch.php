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
    $return = "抱歉！";
} else {
    $xmlArray = (array)$xml;
    $items = array_reverse($xmlArray['row']);
    foreach ($items as $item) {
        $itemArray = (array)$item;
        $return .= '<p>' . $itemArray['trackevent'] . '</p>';
        $return .= '<p>' . $itemArray['trackdatetime'] . '</p><br />';
    }
}
echo $return;