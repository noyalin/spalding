<?php
/**
 * Analysis folder /var/report
 */
class Task_Tools_Model_Tracker_ErrorTracker extends Task_Tools_Model_Base{
    public $coreDir;
    public $reportLogDirectory;
    public $fileName;
    public $exceptionLogDirectory;
    public function __construct(){
        $this->coreDir = Mage :: getBaseDir();
        $this->reportLogDirectory = $this->coreDir . '/var/report/';
        $this->exceptionLogDirectory = $this->coreDir . '/var/log/';

    }
    public function execute(){
        if($this->validate()){
            //can not execute
            return;
        }
        $this->run();
    }
    public function run(){
        if ( $this->fileName) {
            $message = "You are being notified that an error report occurred.";
            $this->sendNotification( 'A MAGENTO ERROR REPORT OCCURRED', $message );
        }
        $this->checkSystemLog();

        $message = "You are being notified that an exception occurred during a catalog update.";
        $this->checkExceptionLog($this->coreDir . '/devicom_apps/catalog/logs/',$message);
        $this->checkExceptionLog($this->coreDir . '/devicom_apps/catalog/',$message);

        $message = "You are being notified that an exception occurred during order processing.";
        $this->checkExceptionLog($this->coreDir . '/devicom_apps/sales/logs/',$message);
        $this->checkExceptionLog($this->coreDir . '/devicom_apps/sales/',$message);

        $message = "You are being notified that an exception occurred during customer processing.";
        $this->checkExceptionLog($this->coreDir . '/devicom_apps/customer/logs/',$message);
        $this->checkExceptionLog($this->coreDir . '/devicom_apps/customer/',$message);

        $message = "You are being notified that an exception occurred during email processing.";
        $this->checkExceptionLog($this->coreDir . '/devicom_apps/email/logs/',$message);
        $this->checkExceptionLog($this->coreDir . '/devicom_apps/email/',$message);
    }

    public function  checkSystemLog(){
        $exceptionLogFilename = $this->getSystemLogFile();
        if(!$exceptionLogFilename){
            return;
        }
        $exceptionLogDirectory = $this->exceptionLogDirectory;
        $file_handle = fopen($exceptionLogDirectory . $exceptionLogFilename, "r");
        while (!feof($file_handle)) {
            $line = fgets($file_handle);

            if (substr($line, 0, 9) == "exception") {
                if (substr($line, 0, 73) == "exception 'Mage_Core_Exception' with message 'Wrong gift card account ID.") {
                    // echo substr($line, 0, 73) . "\n";
                } elseif (substr($line, 0, 63) == "exception 'Mage_Core_Exception' with message 'Gift card account") {
                    // echo substr($line, 0, 63) . "\n";
                } elseif (substr($line, 0, 119) == "exception 'Mage_Core_Exception' with message 'PayPal NVP gateway errors: Instruct the customer to retry the transaction") {//contact customer service
                    // echo substr($line, 0, 119) . "\n";
                } elseif (substr($line, 0, 112) == "exception 'Mage_Core_Exception' with message 'PayPal NVP gateway errors: This transaction couldn't be completed.") {
                    // echo substr($line, 0, 112) . "\n";
                } elseif (substr($line, 0, 140) == "exception 'Mage_Core_Exception' with message 'PayPal NVP gateway errors: A match of the Shipping Address City, State, and Postal Code failed") {//correct address
                    // echo substr($line, 0, 140) . "\n";
                } elseif (substr($line, 0, 129) == "exception 'Mage_Core_Exception' with message 'PayPal NVP gateway errors: There was an error in the Shipping Address Country field") {//correct address
                    // echo substr($line, 0, 129) . "\n";
                } elseif (substr($line, 0, 126) == "exception 'Mage_Core_Exception' with message 'PayPal NVP gateway errors: The Buyer cannot pay with PayPal for this Transaction") {//contact customer service
                    //echo substr($line, 0, 126) . "\n";
                } elseif (substr($line, 0, 137) == "exception 'Mage_Core_Exception' with message 'PayPal NVP gateway errors: The customer must return to PayPal to select new funding sources") {//provide link
                    //echo substr($line, 0, 137) . "\n";
                } elseif (substr($line, 0, 146) == "exception 'Mage_Core_Exception' with message 'PayPal NVP gateway errors: This transaction cannot be processed. The shipping country is not allowed") {//??
                    //echo substr($line, 0, 146) . "\n";
                } elseif (substr($line, 0, 123) == "exception 'Mage_Core_Exception' with message 'PayPal NVP gateway errors: The field Shipping Address Postal Code is required") {//Need to enter postal code
                    //echo substr($line, 0, 123) . "\n";
                } elseif (substr($line, 0, 113) == "exception 'Mage_Core_Exception' with message 'PayPal NVP gateway errors: Internal Error (#10001: Internal Error).") {//Order gets placed - just Magento confusion
                    //echo substr($line, 0, 113) . "\n";
                } elseif (substr($line, 0, 117) == "exception 'Mage_Core_Exception' with message 'PayPal NVP gateway errors: The field Shipping Address State is required") {//correct address
                    //echo substr($line, 0, 117) . "\n";
                } elseif (substr($line, 0, 64) == "exception 'Exception' with message 'PayPal IPN postback failure.") {
                    // echo substr($line, 0, 64) . "\n";
                } elseif (substr($line, 0, 64) == "exception 'Exception' with message 'Cannot handle payment status") {
                    // echo substr($line, 0, 64) . "\n";
                } elseif (substr($line, 0, 80) == "exception 'Mage_Core_Exception' with message 'Maximum amount available to refund") {
                    // echo substr($line, 0, 80) . "\n";
                } elseif (substr($line, 0, 104) == "exception 'Mage_Core_Exception' with message 'Gateway error: A duplicate transaction has been submitted.") {//what 2 minutes before retryinh
                    //echo substr($line, 0, 104) . "\n";
                } elseif (substr($line, 0, 96) == "exception 'Mage_Core_Exception' with message 'Gateway error: This transaction has been declined.") {
                    //echo substr($line, 0, 96) . "\n";
                } elseif (substr($line, 0, 95) == "exception 'Mage_Core_Exception' with message 'Gateway error: The credit card number is invalid.") {
                    //echo substr($line, 0, 95) . "\n";
                } elseif (substr($line, 0, 89) == "exception 'Mage_Core_Exception' with message 'Gateway error: The credit card has expired.") {
                    // echo substr($line, 0, 89) . "\n";
                } else {
                    echo substr($line, 0) . "\n";
                    $message = "$line";
                    $this->sendNotification('AN UNKNOWN MAGENTO EXCEPTION OCCURRED', $message);
                }
            }
        }
        fclose($file_handle);
    }

    public function getSystemLogFile(){
        $exceptionLogDirectory = $this->exceptionLogDirectory;
        if(count(glob($exceptionLogDirectory . 'exception_log')) == 0){
            echo " There is no exception_log to be analysis \n";
            return;
        }
        $exceptionLogFilename = rtrim(basename(shell_exec('ls -t -r -1 ' . $exceptionLogDirectory . 'exception_log | head --lines 1')));
        return $exceptionLogFilename;
    }

    public function checkExceptionLog($directory,$message){
        if(count(glob($directory . 'exception_log')) == 0){
            return;
        }
        $filename = rtrim(basename(shell_exec('ls -t -r -1 ' . $directory . 'exception_log | head --lines 1')));

        if ($filename) {
            echo $message."\n";
            $this->sendNotification( 'A CATALOG LOG EXCEPTION OCCURRED', $message);
        }

    }


    public function validate(){
        if(count(glob($this->reportLogDirectory . '*')) > 0){
            $this->fileName = rtrim(basename(shell_exec('ls -t -r -1 ' . $this->reportLogDirectory . '* | head --lines 1')));
//            echo " There is no file to be executed \n";
//            return true;
        }
        return false;
    }
}