<?php
/**
 * Analysis folder /devicom_apps/catalog/
 */
class Task_Tools_Model_Tracker_CatalogProcessTracker extends Task_Tools_Model_Base{



    public function execute(){
        $this->checkCategory();
        $this->checkPositionUpdate();
        $this->checkIncremental();
        $this->checkFullUpdate();
    }

    public function checkCategory(){
        $filename = rtrim(basename(shell_exec('ls -t -r -1 ' . $this->catalogLogsDirectory . '*.lock | head --lines 1')));

        if ($filename) {
            $timestamp = time();

            //Get the last modified time of the file --
            $timeFileHasBeenLocked = $timestamp - filemtime($this->catalogLogsDirectory . $filename);

            // Check if file has been locked for over 15 minutes (900 seconds)
            if ($timeFileHasBeenLocked >= 900) {
                $message = "You are being notified that the lock $filename is still active after 15 minutes.\r\n\r\nThis may indicate that either a transaction is still processing a request or an exception occurred causing the script to terminate and did not remove the lock file. You can log in to the admin and view the transaction log to determine if any activity is still taking place. If there is not, then the lock file can be removed by utilizing the lock button in the admin. This will allow future transactions to be processed.";
                $this->sendNotification( 'TRANSACTION LOCK STILL ACTIVE FOR PRODUCT PROCESSING', $message);
            } else {
                echo "Hasn't been 15 minutes yet!\n";
            }
        }
    }
    public function checkPositionUpdate(){
        $filename = rtrim(basename(shell_exec('ls -t -r -1 ' . $this->coreDir . 'received/category_positioning_update* | head --lines 1')));

        if ($filename) {
            $timestamp = time();

            //Get the last modified time of the file --
            $timeFileHasBeenLocked = $timestamp - filemtime($this->catalogReceivedDirectory . $filename);

            // Check if file has been locked for over 15 minutes (900 seconds)
            if ($timeFileHasBeenLocked >= 900) {
                $message = "You are being notified that category positioning update $filename is either waiting for a category update before processing or allowing other updates to process.";
                $this->sendNotification( 'CATEGORY POSITIONING STILL HAS NOT PROCESSED', $message);
            } else {
                echo "Hasn't been 15 minutes yet!\n";
            }
        }
    }

    public function checkIncremental(){
        $filename = rtrim(basename(shell_exec('ls -t -1 -A ' . $this->coreDir . 'processed/incremental_product_update* | head --lines 1')));

        if ($filename) {
            $timestamp = time();
            //Get the last modified time of the file --
            $timeFileHasBeenLocked = $timestamp - filemtime($this->catalogProcessedDirectory . $filename);

            // Check if file is older than 30 minutes (1800 seconds)
            if ($timeFileHasBeenLocked >= 1800) {
                $message = "You are being notified that an incremental product update $filename has not been processed for over 30 minutes.";
                $this->sendNotification( 'IT HAS BEEN OVER 30 MINUTES SINCE AN INCREMENTAL PRODUCT UPDATE HAS BEEN PROCESSED', $message);
            } else {
                echo "Hasn't been 30 minutes yet!\n";
            }
        }
    }

    public function checkFullUpdate(){
        $filename = rtrim(basename(shell_exec('ls -t -1 -A ' . $this->coreDir . 'processed/full_product_update* | head --lines 1')));

        if ($filename) {
            $timestamp = time();

            //Get the last modified time of the file --
            $timeFileHasBeenLocked = $timestamp - filemtime($this->catalogProcessedDirectory . $filename);

            // Check if file is older than 25 hours (90000 seconds)
            if ($timeFileHasBeenLocked >= 90000) {
                $message = "You are being notified that a full product update has not been processed for over 25 hours.";
                $this->sendNotification( 'IT HAS BEEN OVER 25 HOURS SINCE A FULL PRODUCT UPDATE HAS BEEN PROCESSED', $message);
            } else {
                echo "Hasn't been 25 hours yet!\n";
            }
        }
    }
    public function validate(){
        return false;
    }
}