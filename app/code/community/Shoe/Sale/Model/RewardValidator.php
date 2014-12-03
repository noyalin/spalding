<?php

class  Shoe_Sale_Model_RewardValidator extends Shoe_Sale_Model_UpdateBase{
    //for Create Contact
    public function __construct(){
        parent::__construct();
        $this->transactionLogHandle = fopen($this->salesLogsDirectory . 'reward_validator_transaction_log', 'a+');
    }

    public function run(){
        $this->check();
    }

    public function check(){
        // Get resource
        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');
        $writeConnection = $resource->getConnection('core_write');

        // Run query to find all pending rewards and see if 30 days has passed since order shipped date -- if so, validate points
        $query = "SELECT `drv`.`increment_id` FROM `rewardpoints_account` AS `ra`
        INNER JOIN `devicom_reward_validation` AS `drv` ON `ra`.`order_id` = `drv`.`increment_id`
        WHERE `drv`.`created_at` < DATE_SUB(CURDATE(), INTERVAL 5 DAY)
        AND `ra`.`order_id` > '100000000'
        AND `ra`.`rewardpoints_referral_id` IS NULL
        AND (`drv`.`status` != 'complete')";
        $results = $readConnection->query($query);

        $this->transactionLogHandle(  "  ->STATUS CHANGE : STARTED\n");

        foreach ($results as $result) {

            $this->transactionLogHandle( "    ->FOR ORDER   : " . $result['increment_id'] );

            // Get order status
            $query = "SELECT `status`, `increment_id` FROM `sales_flat_order` WHERE `increment_id` = '" . $result['increment_id'] . "'";
            $orderStatusResults = $readConnection->query($query);

            foreach ($orderStatusResults as $orderStatusResult) {
                $currentStatus = $orderStatusResult['status'];
                $this->transactionLogHandle(  "      ->STATUS    : CURRENT    : " . $currentStatus . "\n");

                // Change status to complete for final reward point adjustment if any
                switch ($currentStatus) {
                    case 'shipped':
                        $status = 'shipped_complete';
                        $state = 'complete';
                        break;
                    case 'partially_shipped':
                        $status = 'partially_shipped_complete';
                        $state = 'complete';
                        break;
                    case 'backordered':
                        $status = 'backordered_complete';
                        $state = 'complete';
                        break;
                    case 'cancel':
                        $status = 'cancel_complete';
                        $state = 'complete';
                        break;
                    case 'returned':
                        $status = 'returned_complete';
                        $state = 'complete';
                        break;
                    case 'complete';//partial refund
                        $status = 'returned_complete';
                        $state = 'complete';
                        break;
                    case 'closed';//full refund
                        $status = 'returned_complete';
                        $state = 'complete';
                        break;
                    default:
                        $message = "You are being notified that the status for order number " . $result['increment_id'] . " could not be matched.\r\n\r\n";
                        $this->sendNotification( 'STATUS NOT FOUND', $message);
                        continue;
                }

                if ($status && $state) {
                    $query = "UPDATE `sales_flat_order` SET `status` = '" . $status . "', `state` = '" . $state . "' WHERE `increment_id` = '" . $result['increment_id'] . "'";
                    $writeConnection->query($query);

                    $this->transactionLogHandle(  "      ->STATUS    : CHANGED TO : " . $status . " (sales_flat_order)\n");

                    $query = "UPDATE `sales_flat_order_grid` SET `status` = '" . $status . "' WHERE `increment_id` = '" . $result['increment_id'] . "'";
                    $writeConnection->query($query);

                    $this->transactionLogHandle(  "      ->STATUS    : CHANGED TO : " . $status . " (sales_flat_order_grid)\n");

                    // Change status in devicom_rweard_validation table to indicate adjustment complete
                    $query = "UPDATE `devicom_reward_validation` SET `status` = 'complete', `adjusted_at` = CURDATE() WHERE `increment_id` = '" . $result['increment_id'] . "'";
                    $writeConnection->query($query);

                    $this->transactionLogHandle(  "      ->REWARD    : STATUS     : COMPLETE\n");
                }

                //If customerId then update points for order reward transaction
                $order = Mage::getModel('sales/order')->loadByIncrementId($result['increment_id']);
                $customerId = $order->getCustomerId();
                $storeId = $order->getStoreId();

                if ($customerId) {
                    //Refresh rewarpoints_flat _account table
                    RewardPoints_Model_Observer::processRecordFlatRefresh($customerId, $storeId);
                    $this->transactionLogHandle(  "      ->ADJUSTMENT: FLAT TABLE REFRESHED : " . $customerId . "\n");
                }
            }
        }
        $this->transactionLogHandle(  "  ->STATUS CHANGE : FINISHED\n");
    }

    public function validate(){
        //Check if reward system enabled
        $modules = Mage::getConfig()->getNode('modules')->children();
        $modulesArray = (array)$modules;

        if(!isset($modulesArray['J2t_Rewardproductvalue'])) {
            return true;
        }
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