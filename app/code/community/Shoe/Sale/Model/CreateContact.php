<?php

//TODO there is no unit test for this class
class  Shoe_Sale_Model_CreateContact extends Shoe_Sale_Model_UpdateBase{
    //for Create Contact
    public $subscribersDirectory;
    public function __construct(){
        parent::__construct();
        $this->failedDirectory = self::createFolder($this->saleDir . '/subscribers/');
        $this->transactionLogHandle = fopen($this->salesLogsDirectory . 'subscriber_transaction_log', 'a+');
    }

    public function run(){
        // Set your Constant Contact account username and password below
        $cc = new Shoe_Sale_Model_Lib_ConstantContact_CC(self::CREATE_CONTACT_CC_USERNAME, self::CREATE_CONTACT_CC_PASSWORD);

        //Create new XML object
        $rootXmlElement = new SimpleXMLElement($this->contents);

        $storeId = $rootXmlElement->StoreId;
        $customerId = $rootXmlElement->CustomerId;
        $email = (string) $rootXmlElement->Email;
        $firstName = (string) $rootXmlElement->FirstName;
        $lastName = (string) $rootXmlElement->LastName;
        $extra_fields = array(
            'FirstName' => $firstName,
            'LastName' => $lastName,
        );
        $newsList = (int) $rootXmlElement->NewsList;
        $reviewsList = (int) $rootXmlElement->ReviewsList;

        // Make $lists an array
        $lists = array();
        if ($newsList == '1' && $cc->get_list(self::CREATE_CONTACT_LIST_NEWSLETTER)) {
            $lists[] = self::CREATE_CONTACT_LIST_NEWSLETTER;
        }
        if ($reviewsList == '1' && $cc->get_list(self::CREATE_CONTACT_LIST_REVIEWS)) {
            $lists[] = self::CREATE_CONTACT_LIST_REVIEWS;
        }

        $this->contact($cc,$lists,$email,$extra_fields);


        if (self :: DEBUG_MODE) {
            print "REVIEW MEMBERS:\n";
            print_r($cc->get_list_members(self :: CREATE_CONTACT_LIST_REVIEWS));
            print "NEWS MEMBERS:\n";
            print_r($cc->get_list_members(self :: CREATE_CONTACT_LIST_NEWSLETTER));
        }
    }

    public function contact($cc,$lists,$email,$extra_fields){
        // Amount of points to award customer (0)
        $pointsEarned = self::CREATE_CONTACT_POINTS_EARNED;

        // check if the contact exists
        $contact = $cc->query_contacts($email);

        if (count($lists)) {
            if ($contact) {
                // update the contact
                $this->transactionLogHandle( "  ->UPDATING      : " . $email . "\n");

                $status = $cc->update_contact($contact['id'], $email, $lists, $extra_fields);

                if ($status) {
                    $this->transactionLogHandle( "  ->UPDATED       : " . $email . "\n");
                } else {
                    throw new Exception($cc->http_get_response_code_error($cc->http_response_code));
                }
            } else {
                // create the contact
                $this->transactionLogHandle( "  ->CREATING      : " . $email . "\n");
                $new_id = $cc->create_contact($email, $lists, $extra_fields);

                if ($new_id) {
                    $this->transactionLogHandle( "  ->CREATED       : " . $email . "\n");
                    /*
                      if ($pointsEarned > 0 && $newsList == '1') {
                      // Get resource
                      $resource = Mage::getSingleton('core/resource');
                      $writeConnection = $resource->getConnection('core_write');

                      // Add point for subscribing to newsletter
                      $query = "INSERT INTO `rewardpoints_account` (`customer_id`, `store_id`, `order_id`, `points_current`, `points_spent`, `date_start`, `date_end`, `convertion_rate`, `rewardpoints_referral_id`, `quote_id`) VALUES ('" . $customerId . "', '" . $storeId . "', '-200', '" . $pointsEarned . "', 0, NULL, NULL, NULL, NULL, NULL)";
                      $writeConnection->query($query);

                      $this->transactionLogHandle( "  ->ADDED POINTS  : " . $email . "\n");
                      }
                     */
                } else {
                    throw new Exception($cc->http_get_response_code_error($cc->http_response_code));
                }
            }
        }
    }
    public function removeXmlFileWhenFailed(){
        //Move XML file to failed directory
        rename($this->subscribersDirectory . $this->filename, $this->failedDirectory . $this->filename);
    }
    public function removeFile(){
        //Move XML file to failed directory
        rename($this->subscribersDirectory . $this->filename, $this->sentDirectory . $this->filename);
    }
    /**
     * Override
     */
    public function getContents(){
        $this->contents = file_get_contents($this->subscribersDirectory . $this->filename);
    }
    public function validate(){
        if( count(glob($this->subscribersDirectory . 'subscriber_*')) == 0 ){
            echo "order_status_update* file not find. exit... There is no xml file to be executed \n";
            return true;
        }
        $filename = rtrim(basename(shell_exec('ls -t -r ' . $this->subscribersDirectory . 'subscriber_* | head --lines 1')));
        $this->filename = $filename;
        return false;
    }

    /**
     * @param $realTime
     * Override but do nothing.
     * Because this script do not need create lock file
     */
    public function createLockFile($realTime){
        //do nothing
    }

}