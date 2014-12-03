<?php
class Shoe_Sale_Model_PostOrder_NewOrder extends Shoe_Sale_Model_PostOrder_Base {
    public $newOrders;

    public function readFile(){
        $postDirectory = $this->postDirectory;
        $newOrders = $this->readFileFromDirectory($postDirectory);
        return $newOrders;
    }

    public function sendXml(){
        $newOrders = $this->newOrders;
        if(empty($newOrders))
                return;
        $n = 0;
        foreach ($newOrders as $filename) {
            try {

                $realTime = $this->realTime();
                $this->transactionLogHandle( "    ->PROCESSING FILE : " . $filename . "\n");
                $this->transactionLogHandle( "      ->START TIME    : " . $realTime[5] . '/' . $realTime[4] . '/' . $realTime[3] . ' ' . $realTime[2] . ':' . $realTime[1] . ':' . $realTime[0] . "\n");

                $xml = file_get_contents($this->postDirectory . $filename);
                $xmlHandle = simplexml_load_string($xml);
                $result = $xmlHandle->xpath("/Root/Order/OrderNumber");
                $number = $result[0];
                $this->transactionLogHandle( "    ->number : " . $number);
                $order = Mage::getModel('sales/order')->loadByIncrementId($number);
                $status = $order->getStatus();
                $this->transactionLogHandle( "    ->status : " . $status);
                $response =  $this->sendPost($xml);

                // Determine if successful
                if (strstr($response, 'Success')) {
                    //Move file to sent directory
                    rename($this->postDirectory . $filename, $this->sentDirectory . $filename);
                    $this->transactionLogHandle( "      ->SUCCESS   : " . $realTime[5] . '/' . $realTime[4] . '/' . $realTime[3] . ' ' . $realTime[2] . ':' . $realTime[1] . ':' . $realTime[0] . "\n");
                } elseif (strstr($response, 'Failed')) {
                    throw new Exception($filename . " : Failed response returned\n");
                } else {
                    throw new Exception($filename . " : Unknown exception\n");
                }

            } catch (Exception $e) {

                $this->transactionLogHandle( "      ->ERROR         : See exception_log\n");

                //Append error to exception log
                $exceptionLogHandle = fopen($this->salesLogsDirectory . 'exception_log', 'a');
                fwrite($exceptionLogHandle, $e->getMessage());
                fclose($exceptionLogHandle);

                //Move XML file to failed directory
                rename($this->postDirectory . $filename, $this->failedDirectory . $filename);
            }

            $endTime = $this->realTime();
            $this->transactionLogHandle( "      ->FINISH TIME   : " . $endTime[5] . '/' . $endTime[4] . '/' . $endTime[3] . ' ' . $endTime[2] . ':' . $endTime[1] . ':' . $endTime[0] . "\n");
            $this->transactionLogHandle( "    ->END PROCESSING  : " . $filename . "\n");

            $n++;

            /* If limit is set and is greater than zero,
             * then only handle that many. Otherwise, process all files. */
            if (self::CONF_NEW_ORDER_POST_LIMIT
                && self::CONF_NEW_ORDER_POST_LIMIT > 0
                && $n >= self::CONF_NEW_ORDER_POST_LIMIT) {
                break;
            }
        }
    }

    public function validate(){
        /* Check for new order process lock. Limit process to one instance only */
        if(count(glob($this->salesLogsDirectory . 'post_new_order_process*.lock')) > 0){
            echo "Log file exist. exit... There is no xml file to be executed \n";
            return true;
        }
        return false;
    }
}