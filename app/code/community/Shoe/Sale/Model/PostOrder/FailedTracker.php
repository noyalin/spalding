<?php
class Shoe_Sale_Model_PostOrder_FailedTracker extends Shoe_Sale_Model_PostOrder_Base {
    public $newOrders;
    public function __construct(){
        parent::__construct();
        $this->transactionLogHandle = fopen($this->salesLogsDirectory . 'failed_post_tracker_transaction_log', 'a+');
    }

    public function sendXml(){
        $newOrders = $this->newOrders;
        $n = 0;
        foreach ($newOrders as $filename) {
            try {

                $realTime = $this->realTime();
                $this->transactionLogHandle( "    ->PROCESSING FILE : " . $filename . "\n");
                $this->transactionLogHandle( "      ->START TIME    : " . $realTime[5] . '/' . $realTime[4] . '/' . $realTime[3] . ' ' . $realTime[2] . ':' . $realTime[1] . ':' . $realTime[0] . "\n");

                $xml = file_get_contents($this->failedDirectory . $filename);

                $response = $this->sendPost($xml);
                // Determine if successful
                if (strstr($response, 'Success')) {
                    //Move file to sent directory
                    rename($this->failedDirectory . $filename, $this->sentDirectory . $filename);
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

                //send email
                $message = "You are being notified that the new order $filename failed to be posted.\r\n\r\nThis may indicate that there is a temporary problem with the connection or with the remote server.";
                $this->sendNotification( 'NEW ORDER POST FAILED', $message);
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

    public function readFile(){
        $directory = $this->failedDirectory;
        $newOrders = $this->readFileFromDirectory($directory);
        return $newOrders;
    }
    public function createLockFile($realTime){

    }

}