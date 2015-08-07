<?php

require(dirname(__FILE__) . '/lib/Devicom/config.php');

set_time_limit(0); //no timout

$ip = $_SERVER['REMOTE_ADDR'];
// $allowed (array), defined in config.php

$root_dir = dirname(__FILE__);
$catalogLogsDirectory = $root_dir . '/catalog/logs/';
$temporaryDirectory = $root_dir . '/catalog/temporary/';
$receivedDirectory = $root_dir . '/catalog/received/';
$failedDirectory = $root_dir . '/catalog/failed/';

$realTime = realTime();
//Only allow POST from allowed IP addresses
//TODO add && in_array($ip, $allowed) in the future for security
if ($_SERVER['REQUEST_METHOD'] == 'POST' ) {//&& in_array($ip, $allowed)
    //Open request log
    $requestLogHandle = fopen($catalogLogsDirectory . 'request_log', 'a+');
    fwrite($requestLogHandle, "->START TIME      : " . $realTime[5] . '/' . $realTime[4] . '/' . $realTime[3] . ' ' . $realTime[2] . ':' . $realTime[1] . ':' . $realTime[0] . "\n");

    //Create write.lock to stop further processing of incremental and/or full updates until post is written and moved to the received directory
    $writeLockFilename = 'write_' . $realTime[5] . $realTime[4] . $realTime[3] . '_' . $realTime[2] . $realTime[1] . $realTime[0] . '.lock';
    $writeLockHandle = fopen($catalogLogsDirectory . $writeLockFilename, 'w+');
    fclose($writeLockHandle);

    fwrite($requestLogHandle, "  ->LOCK CREATED  : " . $writeLockFilename . "\n");

    try {

        //Store request
        if (!$request = file_get_contents('php://input')) {

            //Write file to failed directory
            $filename = 'unknown_' . $realTime[5] . $realTime[4] . $realTime[3] . '_' . $realTime[2] . $realTime[1] . $realTime[0] . substr(microtime(), 2, 3) . '.xml';
            $failedHandle = fopen($failedDirectory . $filename, 'w');
            fwrite($failedHandle, $request);
            fclose($failedHandle);

            throw new Exception("No data posted in request");
        }

        $json_str = json_decode($request,true);
        if(md5('spalding2015') != $json_str['k']) {
            throw new Exception("MD5 check error");
        }
        if(count($json_str['p']) == 0){
            throw new Exception("No data posted in request");
        }
        $xml_str = json_to_xml($json_str);
        $xml = new SimpleXMLElement($xml_str);

        //Test if root updateType attribute exists
        if (!isset($xml['updateType'])) {
            throw new Exception("Update type not set");
        }

        $updateType = trim($xml['updateType']);

        switch ($updateType) {
            case 0://FULL PRODUCT
                $filename = 'inventory_product_update_'."full_inventory_update_" . $realTime[5] . $realTime[4] . $realTime[3] . '_' . $realTime[2] . $realTime[1] . $realTime[0] . substr(microtime(), 2, 3) . '.xml';
                break;
            case 1://INCREMENTAL PRODUCT
                $filename = 'inventory_product_update_' . $realTime[5] . $realTime[4] . $realTime[3] . '_' . $realTime[2] . $realTime[1] . $realTime[0] . substr(microtime(), 2, 3) . '.xml';
                break;
            default:
                $filename = 'invalid_' . $realTime[5] . $realTime[4] . $realTime[3] . '_' . $realTime[2] . $realTime[1] . $realTime[0] . substr(microtime(), 2, 3) . '.xml';
                $failedHandle = fopen($failedDirectory . $filename, 'w');
                fwrite($failedHandle, $xml_str);
                fclose($failedHandle);
                throw new Exception("Illegal value set for update type");
        }

        //Write file to temporary directory to prevent processing by another script instance
        $temporaryHandle = fopen($temporaryDirectory . $filename, 'w');
        fwrite($temporaryHandle, $xml_str);
        fclose($temporaryHandle);
        fwrite($requestLogHandle, "  ->FILE CREATED  : " . $filename . "\n");

        //Move XML file to received directory
        if (@!rename($temporaryDirectory . $filename, $receivedDirectory . $filename)) {
            throw new Exception("Failed to move file to received directory");
        }

        fwrite($requestLogHandle, "  ->FILE MOVED    : " . $filename . "\n");
        $response = 'Success';
    } catch (Exception $e) {

        if (file_exists($temporaryDirectory . $filename)) {
            rename($temporaryDirectory . $filename, $failedDirectory . $filename);
        }

        $response = 'Failed';

        //Append error to exception log
        $exceptionLogHandle = fopen($catalogLogsDirectory . 'exception_log', 'a');
        if (!$request) {
            fwrite($exceptionLogHandle, 'Request -> ' . $request. "\n");
        }
        fwrite($exceptionLogHandle, '->' . $filename . " - " . $e->getMessage() . "\n");
        fclose($exceptionLogHandle);
    }

    //Remove write.lock to allow processing of incremental and/or full updates
    if (file_exists($catalogLogsDirectory . $writeLockFilename)) {
        unlink($catalogLogsDirectory . $writeLockFilename);
        fwrite($requestLogHandle, "  ->LOCK REMOVED  : " . $writeLockFilename . "\n");
    }

    $endTime = realTime();
    fwrite($requestLogHandle, "->FINISH TIME     : " . $endTime[5] . '/' . $endTime[4] . '/' . $endTime[3] . ' ' . $endTime[2] . ':' . $endTime[1] . ':' . $endTime[0] . "\n");

    //Close request log
    fclose($requestLogHandle);
} else {

    $response = false;

    //Append illegal IP to access log
    $accessLogHandle = fopen($catalogLogsDirectory . 'access_log', 'a');
    fwrite($accessLogHandle, $realTime[5] . $realTime[4] . $realTime[3] . '_' . $realTime[2] . $realTime[1] . $realTime[0] . ' - ' . $ip . "\n");
    fclose($accessLogHandle);
}

if ($response) {

//    //Close connection since request received
//    header('HTTP/1.1 200 OK');
//    header('Content-Length: 0');
//    header('Connection: close');
//    //Apparently required to send response
//    echo 'Close';
//    ob_end_flush();
//    ob_start();
//    ob_flush();
//    flush();
    //Send response
    header("Content-Type:text/xml");
    echo '<?xml version="1.0" encoding="UTF-8"?><root>' . $response . '</root>';
}

function json_to_xml($json_str){

    $xml_str = '<?xml version="1.0" encoding="UTF-8"?><root updateType="';
    $xml_str .= $json_str['t'];
    $xml_str .='"><Simple>';
    foreach($json_str['p'] as $product){
        $xml_str .='<Product><Sku>';
        $xml_str .=$product['s'];
        $xml_str .='</Sku><Quantity>';
        $xml_str .=$product['q'];
        $xml_str .='</Quantity></Product>';

    }
    $xml_str .='</Simple></root>';

    return $xml_str;
}
?>
