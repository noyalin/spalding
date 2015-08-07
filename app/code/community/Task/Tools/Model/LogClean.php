<?php
class Task_Tools_Model_LogClean extends Task_Tools_Model_Base{
    public function run(){
        $realTime = $this->realTime();
        $this->transactionLogHandle( "->BEGIN PROCESSING: " . $this->filename . "\n");
        $this->transactionLogHandle( "  ->START TIME    : " . $realTime[5] . '/' . $realTime[4] . '/' . $realTime[3] . ' ' . $realTime[2] . ':' . $realTime[1] . ':' . $realTime[0] . "\n");
        $this->transactionLogHandle( "  ->ACQUIRING RESOURCE CONNECTION...\n");

        try{
// Get resource
            $resource = Mage::getSingleton('core/resource');
            $writeConnection = $resource->getConnection('core_write');

            $query = "BEGIN";
            $results = $writeConnection->query($query);

            $this->transactionLogHandle( "  ->CLEANING INDEX_EVENT TABLE...\n");
            $query = <<< _QUERY_

	DELETE FROM index_event;
	ALTER TABLE index_event AUTO_INCREMENT = 1;
_QUERY_;
            $results = $writeConnection->query($query);

            $this->transactionLogHandle( "  ->CLEANING INDEX_PROCESS TABLE...\n");
            $query .= <<< _QUERY_
	DELETE FROM index_process_event;
	ALTER TABLE index_process_event AUTO_INCREMENT = 1;
_QUERY_;
            $results = $writeConnection->query($query);

            $this->transactionLogHandle( "  ->CLEANING LOG_CUSTOMER TABLE...\n");
            $query .= <<< _QUERY_
	DELETE FROM log_customer;
	ALTER TABLE log_customer AUTO_INCREMENT = 1;
_QUERY_;
            $results = $writeConnection->query($query);

            $this->transactionLogHandle( "  ->CLEANING LOG_QUOTE TABLE...\n");
            $query .= <<< _QUERY_
	DELETE FROM log_quote;
	ALTER TABLE log_quote AUTO_INCREMENT = 1;
_QUERY_;
            $results = $writeConnection->query($query);

            $this->transactionLogHandle( "  ->CLEANING LOG_URL TABLE...\n");
            $query .= <<< _QUERY_
	DELETE FROM log_url;
	ALTER TABLE log_url AUTO_INCREMENT = 1;
_QUERY_;
            $results = $writeConnection->query($query);

            $this->transactionLogHandle( "  ->CLEANING LOG_URL_INFO TABLE...\n");
            $query .= <<< _QUERY_
	DELETE FROM log_url_info;
	ALTER TABLE log_url_info AUTO_INCREMENT = 1;
_QUERY_;
            $results = $writeConnection->query($query);

            $this->transactionLogHandle( "  ->CLEANING LOG_VISITOR TABLE...\n");
            $query .= <<< _QUERY_
	DELETE FROM log_visitor;
	ALTER TABLE log_visitor AUTO_INCREMENT = 1;
_QUERY_;
            $results = $writeConnection->query($query);

            $this->transactionLogHandle( "  ->CLEANING LOG_VISITOR_INFO TABLE...\n");
            $query .= <<< _QUERY_
	DELETE FROM log_visitor_info;
	ALTER TABLE log_visitor_info AUTO_INCREMENT = 1;
_QUERY_;
            $results = $writeConnection->query($query);

            $this->transactionLogHandle( "  ->CLEANING REPORT_EVENT TABLE...\n");
            $query .= <<< _QUERY_
	DELETE FROM report_event;
	ALTER TABLE report_event AUTO_INCREMENT = 1;
_QUERY_;
            $results = $writeConnection->query($query);

            $this->transactionLogHandle( "  ->CLEANING REPORT_VIEWED_PRODUCT_INDEX TABLE...\n");
            $query .= <<< _QUERY_
	DELETE FROM report_viewed_product_index;
	ALTER TABLE report_viewed_product_index AUTO_INCREMENT = 1;
_QUERY_;
            $results = $writeConnection->query($query);

            $query = "COMMIT";
            $results = $writeConnection->query($query);
        }catch (Exception $e) {

            $this->transactionLogHandle( "  ->ERROR         : See exception_log\n");

            //Append error to exception log file
            $exceptionLogHandle = fopen($this->catalogLogsDirectory . 'exception_log', 'a');
            fwrite($exceptionLogHandle, '->' . $this->filename . " - " . $e->getMessage() . "\n");
            fclose($exceptionLogHandle);
        }

    }


    public function validate(){
        return false;
    }
}