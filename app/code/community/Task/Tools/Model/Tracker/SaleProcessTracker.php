<?php
/**
 * Analysis folder /devicom_apps/sale/
 */
class Task_Tools_Model_Tracker_SaleProcessTracker extends Task_Tools_Model_Base{
    public $coreDir;
    public $salesLogsDirectory;
    public $salesSentDirectory;

    public function __construct(){
        $this->coreDir = Mage :: getBaseDir()."/devicom_apps/sales/";
        $this->salesLogsDirectory =  $this->coreDir . 'logs/';
        $this->salesSentDirectory =  $this->coreDir . 'sent/';

    }
    public function execute(){
        $this->checkLog();
        $this->checkNewOrder();
    }

    public function checkLog(){
        $filename = rtrim(basename(shell_exec('ls -t -r -1 ' . $this->salesLogsDirectory . '*.lock | head --lines 1')));

        if ($filename) {
            $timestamp = time();

            //Get the last modified time of the file --
            $timeFileHasBeenLocked = $timestamp - filemtime($this->salesLogsDirectory . $filename);

            // Check if file has been locked for over 30 minutes (1800 seconds)
            if ($timeFileHasBeenLocked >= 1800) {

                $message = "You are being notified that the lock $filename is still active after 15 minutes.\r\n\r\nThis may indicate that either a transaction is still processing a request or an exception occurred causing the script to terminate and did not remove the lock file. You can log in to the admin and view the transaction log to determine if any activity is still taking place. If there is not, then the lock file can be removed by utilizing the lock button in the admin. This will allow future transactions to be processed.";
                $this->sendNotification( 'TRANSACTION LOCK STILL ACTIVE FOR PRODUCT PROCESSING', $message);

            } else {
                echo "Hasn't been 15 minutes yet!\n";
            }
        }
    }

    public function checkNewOrder(){
        $filename = rtrim(basename(shell_exec('ls -t -1 -A ' . $this->coreDir . 'sent/new_order_* | head --lines 1')));

        if ($filename) {
            $timestamp = time();

            //Get the last modified time of the file --
            $timeFileHasBeenLocked = $timestamp - filemtime($this->salesSentDirectory . $filename);

            // Check if file is older than 60 minutes (3600 seconds)
            if ($timeFileHasBeenLocked >= 3600) {

                $message = "You are being notified that a new order has not been placed for over 60 minutes.";
                $this->sendNotification( 'IT HAS BEEN OVER 60 MINUTES SINCE A NEW ORDER HAS BEEN PLACED', $message, $cc = true, $override = false);
            } else {
                echo "Hasn't been 60 minutes yet!\n";
            }
        }
    }
    public function validate(){

    }

   }