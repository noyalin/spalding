<?php
class  Shoe_Sale_Model_OrderStatusUpdate extends Shoe_Sale_Model_UpdateBase{
    public $createLockFile = true;
    public $orderStatusNeedCheck = array( 'new', 'holded','processing','payment_review','Returned');
    public $carrierArr = array(
        '申通' => 'flatrate',
    );
    public function __construct(){
        parent::__construct();
        $this->transactionLogHandle = fopen($this->salesLogsDirectory . 'order_status_update_transaction_log', 'a+');
    }
    public function getContents(){
        if(isset($this->filename))
            $this->contents = file_get_contents($this->receivedDirectory . $this->filename);
    }
    public function run(){
        list($elements,$orderUpdates) = $this->rebuildXml();
        $this->update($elements,$orderUpdates);
    }

    public function rebuildXml(){
        //Create new XML object
        $xmlOrdersElement = new SimpleXMLElement($this->contents);
        $elements = $xmlOrdersElement->getName();
        // Rebuild XML array into object
        $orderUpdates = array();
        foreach ($xmlOrdersElement->Order as $xmlOrderElement) {

            $orderNumber = (string) $xmlOrderElement->OrderNumber;

            $orderUpdates[$orderNumber]['orderNumber'] = $orderNumber;
            $orderUpdates[$orderNumber]['status'] = (string) $xmlOrderElement->Status;
            $orderUpdates[$orderNumber]['carrier'] = (string) $xmlOrderElement->Carrier;
            $orderUpdates[$orderNumber]['trackingNumber'] = (string) $xmlOrderElement->TrackingNumber;

            foreach ($xmlOrderElement->Items as $xmlItemsElement) {
                foreach ($xmlItemsElement->Item as $xmlItemElement) {

                    $sku = (string) $xmlItemElement->Sku;

                    $orderUpdates[$orderNumber]['items'][$sku]['sku'] = $sku;
                    $orderUpdates[$orderNumber]['items'][$sku]['quantityOrdered'] = $xmlItemElement->QuantityOrdered;
                    $orderUpdates[$orderNumber]['items'][$sku]['quantityShipped'] = $xmlItemElement->QuantityShipped;
                    $orderUpdates[$orderNumber]['items'][$sku]['quantityReturned'] = $xmlItemElement->QuantityReturned;
                    $orderUpdates[$orderNumber]['items'][$sku]['quantityBackordered'] = $xmlItemElement->QuantityNeeded;

                    if(isset($orderUpdates[$orderNumber]['items']['totalQuantityOrdered'])){
                        $orderUpdates[$orderNumber]['items']['totalQuantityOrdered'] += $xmlItemElement->QuantityOrdered;
                    }else{
                        $orderUpdates[$orderNumber]['items']['totalQuantityOrdered'] = $xmlItemElement->QuantityOrdered;
                    }

                    if(isset($orderUpdates[$orderNumber]['items']['totalQuantityShipped'])){
                        $orderUpdates[$orderNumber]['items']['totalQuantityShipped'] += $xmlItemElement->QuantityShipped;
                    }else{
                        $orderUpdates[$orderNumber]['items']['totalQuantityShipped'] = $xmlItemElement->QuantityShipped;
                    }

                    if(isset($orderUpdates[$orderNumber]['items']['totalQuantityReturned'])){
                        $orderUpdates[$orderNumber]['items']['totalQuantityReturned'] += $xmlItemElement->QuantityReturned;
                    }else{
                        $orderUpdates[$orderNumber]['items']['totalQuantityReturned'] = $xmlItemElement->QuantityReturned;
                    }

                    if(isset($orderUpdates[$orderNumber]['items']['totalQuantityBackordered'])){
                        $orderUpdates[$orderNumber]['items']['totalQuantityBackordered'] += $xmlItemElement->QuantityNeeded;
                    }else{
                        $orderUpdates[$orderNumber]['items']['totalQuantityBackordered'] = $xmlItemElement->QuantityNeeded;
                    }

//                    $orderUpdates[$orderNumber]['items']['totalQuantityOrdered'] += $xmlItemElement->QuantityOrdered;
//                    $orderUpdates[$orderNumber]['items']['totalQuantityShipped'] += $xmlItemElement->QuantityShipped;
//                    $orderUpdates[$orderNumber]['items']['totalQuantityReturned'] += $xmlItemElement->QuantityReturned;
//                    $orderUpdates[$orderNumber]['items']['totalQuantityBackordered'] += $xmlItemElement->QuantityNeeded;
                }
            }
        }

        $this->transactionLogHandle( "  ->REBUILT XML   : ");

        return array($elements,$orderUpdates);
    }
    public function checkOrderNumber($order,$orderUpdate){
        $return = true;
        if ($orderUpdate['orderNumber'] > 100000000) {
            if (!$order->getId()) {
                $this->transactionLogHandle( "    ->STATUS UPDATE : ORDER NOT FOUND\n");
                // SEND EMAIL NOTIFICATION
                $message = "You are being notified that order number " . $orderUpdate['orderNumber'] . "could not be updated because it could not be found.";
                $this->sendNotification(  'ORDER NOT FOUND', $message);
                $return = false;
            }
        } else {
            //Yahoo order number
            $return = false;
        }
        return $return;
    }

    /**
     * Add item to table devicom_order_item
     * @param $order
     * @param $orderUpdate
     */
    public function addItemToTableDevicomOrderItem($order,$orderUpdate,$writeConnection){
        $customerId = $order->getCustomerId();
        $storeId = $order->getStoreId();
        $orderCreatedAt = $order->getCreatedAt();
        //ADD
        $skuArray = array();
        foreach ($order->getAllItems() as $orderItem) {
            foreach ($orderUpdate['items'] as $itemUpdate) {
                if ($orderItem->getSku() == $itemUpdate['sku'] &&
                    !array_key_exists($itemUpdate['sku'], $skuArray) &&
                    substr($itemUpdate['sku'], 0, 6) != 'promo-' &&
                    substr($itemUpdate['sku'], 0, 3) != 'gc-') {
                    $size = end(explode('-', $itemUpdate['sku']));
                    $sku = substr($itemUpdate['sku'], 0, -(strlen($size) + 1));
                    $product = Mage::getModel('catalog/product')->loadByAttribute('sku', $sku);
                    $rewardEligible = (is_null($product->getRewardPoints()) ? 1 : 0);
                    $query = "INSERT INTO `devicom_order_item` (`increment_id`, `order_created_at`, `customer_id`, `sku`, `quantity_ordered`,
                    `quantity_shipped`, `quantity_returned`, `quantity_backordered`,
                    `price`, `reward_eligible`, `status`, `carrier`, `tracking_number`, `created_at`) VALUES
                    (" . $orderUpdate['orderNumber'] . ", '" .
                        $orderCreatedAt . "', " .
                        ($customerId ? $customerId : 'NULL') . ", '" .
                        $itemUpdate['sku'] . "', " . $itemUpdate['quantityOrdered'] . ", " .
                        $itemUpdate['quantityShipped'] . ", " . $itemUpdate['quantityReturned'] . ", " .
                        $itemUpdate['quantityBackordered'] . ", '" . $orderItem->getPrice() . "', " . $rewardEligible . ", '" . $orderUpdate['status'] . "', '" . $orderUpdate['carrier'] . "', '" . $orderUpdate['trackingNumber'] . "', CURDATE())";
                    $writeConnection->query($query);
                    $skuArray[$itemUpdate['sku']] = $itemUpdate['sku'];
                    $this->transactionLogHandle( "        ->ADDED   : ITEM TO RETURNED TABLE : " . $orderItem->getSku() . "(" . $itemUpdate['quantityReturned'] . ")\n");
                    break;
                }
            }
        }
    }

    public function updateTableDevicomOrderItem($order,$orderUpdate,$writeConnection){
        $customerId = $order->getCustomerId();
        $storeId = $order->getStoreId();
        $orderCreatedAt = $order->getCreatedAt();
        $skuArray = array();
        foreach ($order->getAllItems() as $orderItem) {
            foreach ($orderUpdate['items'] as $itemUpdate) {
                if ($orderItem->getSku() == $itemUpdate['sku'] && !array_key_exists($itemUpdate['sku'], $skuArray) && substr($itemUpdate['sku'], 0, 6) != 'promo-' && substr($itemUpdate['sku'], 0, 3) != 'gc-') {
                    $query = "UPDATE `devicom_order_item` SET `quantity_ordered` = " .
                        $itemUpdate['quantityOrdered'] . ",`quantity_shipped` = " .
                        $itemUpdate['quantityShipped'] . ",`quantity_returned` = " .
                        $itemUpdate['quantityReturned'] . ",`quantity_backordered` = " .
                        $itemUpdate['quantityBackordered'] . ", `status` = '" .
                        $orderUpdate['status'] . "', `carrier` = '" .
                        $orderUpdate['carrier'] . "', `tracking_number` = '" .
                        $orderUpdate['trackingNumber'] . "', `updated_at` = CURDATE() WHERE `increment_id` = " .
                        $orderUpdate['orderNumber'] . " AND `sku` = '" .
                        $itemUpdate['sku'] . "'";
                    $writeConnection->query($query);
                    $skuArray[$itemUpdate['sku']] = $itemUpdate['sku'];
                    $this->transactionLogHandle( "        ->UPDATED : ITEM TO RETURNED TABLE : " . $orderItem->getSku() . "(" . $itemUpdate['quantityReturned'] . ")\n");
                    break;
                }
            }
        }
    }
    public function update($elements,$orderUpdates){
        if ( $elements != 'Orders') {
            throw new Exception('No Orders Found');
        }
        // Get resource
        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');
        $writeConnection = $resource->getConnection('core_write');

        $this->transactionLogHandle( "  ->STATUS UPDATE : STARTED\n");

        foreach ($orderUpdates as $orderUpdate) {

            $order = Mage::getModel('sales/order')->loadByIncrementId($orderUpdate['orderNumber']);

            //Currently checking for larger order number than 100000000 to prevent loading of orders for which are not in system and are sneakerhead
            //This is to allow receiving status updates for sneakerhead orders before migration
            if(!$this->checkOrderNumber($order,$orderUpdate)){
                continue;
            }

            $this->transactionLogHandle( "    ->ORDER LOADED: " . $orderUpdate['orderNumber'] . "\n");
            $this->transactionLogHandle( "      ->STATUS    : CURRENT    : " . $order->getStatus() . "\n");
            $this->transactionLogHandle( "      ->STATE     : CURRENT    : " . $order->getState() . "\n");
            $this->transactionLogHandle( "      ->STATUS    : POSTED     : " . $orderUpdate['status'] . "\n");

            //Bail if state is complete and notify
            if ( !in_array($order->getState(),$this->orderStatusNeedCheck) ) {
                $this->transactionLogHandle( "      ->STATUS    : NOT CHANGED: IN COMPLETE STATE AND NOT A RETURN\n");
                // SEND EMAIL NOTIFICATION
                $message = "You are being notified that order update " . $this->filename . " for order number " . $orderUpdate['orderNumber'] . " is in a completed state and cannot be changed to a processing state.";
                $this->sendNotification(  'STATUS NOT CHANGED', $message);
                continue;
            }

            $totalQtyOrdered = 0;
            foreach ($order->getAllVisibleItems() as $orderItem) {
                $totalQtyOrdered = $totalQtyOrdered + $orderItem->getQtyOrdered();
            }
            settype($totalQtyOrdered, 'integer');

            //Set customer and store ids for validation and reward transaction adjustment
            $customerId = $order->getCustomerId();
            $storeId = $order->getStoreId();
            $orderCreatedAt = $order->getCreatedAt();

            //Add all order items to devicom_order_item table
            $query = "SELECT * FROM `devicom_order_item` WHERE `increment_id` = '" . $orderUpdate['orderNumber'] . "'";
            $getOrderResults = $writeConnection->fetchAll($query);
            $orderFound = NULL;
            if(count($getOrderResults) > 0 ){
                $orderFound = 1;
            }

            $this->transactionLogHandle( "      ->ORDERITEMS:\n");
            if (is_null($orderFound)) {
                //ADD
               $this->addItemToTableDevicomOrderItem($order,$orderUpdate,$writeConnection);
            } else {
                //UPDATE
                $this->updateTableDevicomOrderItem($order,$orderUpdate,$writeConnection);
            }

            // Create/Update Shipment regardless of status if tracking number provided
            $shipmentCreated = $this->createOrderShipment($customerId, $order, $orderUpdate, $readConnection, $writeConnection,$totalQtyOrdered);

            // Create invoice only if we create a shipment (regardless of status) and haven't already invoiced
            if ($shipmentCreated && !$order->hasInvoices()) {
                $this->createOrderInvoice($order, $orderUpdate, $totalQtyOrdered);
            }

            $this->checkStatus($orderUpdate,$readConnection,$writeConnection,$customerId,$order,$shipmentCreated,$totalQtyOrdered);

            //If customerId then update points for order reward transaction
            $this->updateCustomerRewardPoints($customerId, $orderUpdate, $order, $storeId, $writeConnection);

        }
        $this->transactionLogHandle( "  ->STATUS UPDATE : FINISHED\n");
    }

    public function validate(){
        $salesLogsDirectory = $this->salesLogsDirectory;
        $receivedDirectory = $this->receivedDirectory;

        if($this->checkFileExist($salesLogsDirectory,'*.lock')){
            echo "Log file exist. exit... There is no xml file to be executed \n";
            return true;
        }
        if( count(glob($receivedDirectory . 'order_status_update*')) == 0 ){
            echo "order_status_update* file not find. exit... There is no xml file to be executed \n";
            return true;
        }
        $filename = rtrim(basename(shell_exec('ls -t -r ' . $receivedDirectory . 'order_status_update* | head --lines 1')));
        $this->filename = $filename;

        return false;
    }
    function createOrderShipment($customerId, $order, $orderUpdate, $readConnection, $writeConnection,$totalQtyOrdered) {

        //Cannot create/update shipment if no tracking data
        if (!$orderUpdate['trackingNumber'] || !$orderUpdate['carrier']) {
            $this->transactionLogHandle( "      ->SHIPMENT  : NO TRACKING DATA\n");
            return false;
        }
        // New Shipment
        if ($order->getShipmentsCollection()->count() == 0) {
            $this->newShipment($order,$orderUpdate,$readConnection,$writeConnection,$totalQtyOrdered,$customerId);
            return true;
            // Update Shipment
        } else {
            $this->updateShipment($order,$orderUpdate,$writeConnection);
            // Shipment already exists
            return false;
        }
    }

    public function newShipment($order,$orderUpdate,$readConnection,$writeConnection,$totalQtyOrdered,$customerId){
        $convertor = Mage::getModel('sales/convert_order');
        $shipment = $convertor->toShipment($order);

        $this->transactionLogHandle( "      ->SHIPMENT  : CREATING\n");

        $email = true;
        $includeComment = false;
        $comment = "Order Completed And Shipped Automatically via Automation Routines";

        // Build list of unshipped items to credit rewardPoints if needed
        $unshippedRewardItems = array();

        // Complete Shipment
        if ($totalQtyOrdered == $orderUpdate['items']['totalQuantityShipped']) {
            foreach ($order->getAllItems() as $orderItem) {
                if (!$orderItem->getQtyToShip()) {
                    continue;
                }
                if ($orderItem->getIsVirtual()) {
                    continue;
                }
                $item = $convertor->itemToShipmentItem($orderItem);
                $qty = $orderItem->getQtyToShip();
                $item->setQty($qty);
                $shipment->addItem($item);
                $this->transactionLogHandle( "        ->ADDED   : ITEM " . $orderItem->getSku() . "(" . $qty . ")\n");
            }
            // Partial Shipment
        } else {

            //Get items with qty greater than zero for shipped
            foreach ($order->getAllItems() as $orderItem) {
                foreach ($orderUpdate['items'] as $itemUpdate) {
                    if ($orderItem->getSku() == $itemUpdate['sku']) {
                        if (!$orderItem->getQtyToShip()) {
                            continue;
                        }
                        if ($orderItem->getIsVirtual()) {
                            continue;
                        }
                        $item = $convertor->itemToShipmentItem($orderItem);
                        $qty = $itemUpdate['quantityShipped'];
                        $item->setQty($qty);
                        $shipment->addItem($item);
                        $this->transactionLogHandle( "        ->ADDED   : ITEM " . $orderItem->getSku() . "(" . $itemUpdate['quantityShipped'] . ")\n");

                        // Add unshipped reward item to list
                        if (preg_match('/promo-/', $itemUpdate['sku'])
                            && (int)$itemUpdate['quantityShipped'] < (int)$itemUpdate['quantityOrdered']) {
                            $unshippedRewardItems[] = $itemUpdate;
                        }

                        break;
                    }
                }
            }
        }

        $data = array();
        $data['carrier_code'] = strtolower($this->carrierArr[$orderUpdate['carrier']]); // will need new logic for converting carriers to Magento codes
        $data['title'] = $orderUpdate['carrier'];// May change
        $data['number'] = $orderUpdate['trackingNumber'];
        $track = Mage::getModel('sales/order_shipment_track')->addData($data);
        $shipment->addTrack($track);

        $this->transactionLogHandle( "        ->ADDED   : TRACKING DATA\n");

        $shipment->register();
        $shipment->addComment($comment, $email && $includeComment);

        $this->transactionLogHandle( "        ->ADDED   : COMMENT\n");

        $shipment->setEmailSent(true);
        $shipment->getOrder()->setIsInProcess(true);

        $transactionSave = Mage::getModel('core/resource_transaction')
            ->addObject($shipment)
            ->addObject($shipment->getOrder())
            ->save();

        $shipment->sendEmail($email, ($includeComment ? $comment : ''));

        $this->transactionLogHandle( "        ->SENT    : EMAIL\n");

        //Removed per Dennis -- will revisit later
        //$order->setStatus('Complete');
        //$order->addStatusToHistory($order->getStatus(), 'Order Completed And Shipped Automatically via Automation Routines', false);

        $shipment->save();
        $this->transactionLogHandle( "      ->SHIPMENT  : CREATED\n");

        if (count($unshippedRewardItems)) {
            $this->transactionLogHandle( "      ->RWDPOINTS : CHECKING TRANSACTION RECORDS\n");
            // Create a gift to offset any spent points for unshipped item(s)
//            $this->returnSpentRewardPoints($customerId, $order, $orderUpdate, $readConnection, $writeConnection, $unshippedRewardItems);
        }
    }
    public function updateShipment($order,$orderUpdate,$writeConnection){
        // Retrieve shipment id
        $shipmentId = 0;
        foreach($order->getShipmentsCollection() as $shipment) {
            $shipmentId = $shipment->getId();
            break;
        }

        $this->transactionLogHandle( "      ->SHIPMENT  : UPDATING\n");

        $query = "UPDATE sales_flat_shipment_track SET " .
            "carrier_code = '" . strtolower($orderUpdate['carrier']) . "', " .
            "title = '{$orderUpdate['carrier']}', " .
            "track_number = '{$orderUpdate['trackingNumber']}' " .
            "WHERE parent_id = $shipmentId";

        $writeConnection->query($query);

        $this->transactionLogHandle( "        ->UPDATED : TRACKING DATA\n");
        $this->transactionLogHandle( "      ->SHIPMENT  : UPDATED\n");
    }
    function createOrderInvoice($order, $orderUpdate, $totalQtyOrdered) {

        $this->transactionLogHandle( "      ->INVOICE   : CREATING\n");

        if ($totalQtyOrdered == $orderUpdate['items']['totalQuantityShipped']) {
            $invoice = Mage::getModel('sales/service_order', $order)->prepareInvoice();
            if (!$invoice->getTotalQty()) {
                Mage::throwException(Mage::helper('core')->__('Cannot create an invoice without products.'));
            }
            $this->transactionLogHandle( "        ->ADDED   : ALL ITEMS (" . $invoice->getTotalQty() . ")\n");
        } else {
            foreach ($order->getAllItems() as $orderItem) {
                foreach ($orderUpdate['items'] as $itemUpdate) {
                    if ($orderItem->getSku() == $itemUpdate['sku']) {
                        $qtys[$orderItem->getId()] = $itemUpdate['quantityShipped'];
                        $this->transactionLogHandle( "        ->ADDED   : ITEM " . $orderItem->getSku() . "(" . $itemUpdate['quantityShipped'] . ")\n");
                        break;
                    }
                }
            }
            $invoice = Mage::getModel('sales/service_order', $order)->prepareInvoice($qtys);
        }

        // Note OFFLINE CAPTURE
        $invoice->setRequestedCaptureCase(Mage_Sales_Model_Order_Invoice::CAPTURE_OFFLINE);
        $invoice->register();
        $invoice->getOrder()->setIsInProcess(true);
        $transactionSave = Mage::getModel('core/resource_transaction')
            ->addObject($invoice)
            ->addObject($invoice->getOrder());

        $transactionSave->save();

        $this->transactionLogHandle( "      ->INVOICE   : CREATED\n");
    }

    public function checkStatus($orderUpdate,$readConnection,$writeConnection,$customerId,$order,$shipmentCreated,$totalQtyOrdered){
        switch ($orderUpdate['status']) {
            case 'In Processing':
                try {
                    // Update state and status
                    $status = 'processing';
                    $state = 'processing';
                    $log_status = 'IN PROCESSING';
                    $this->salesOrderStatusUpdate($orderUpdate, $log_status, $status, $state, $writeConnection);
                } catch (Exception $e) {
                    throw $e;
                }
                $this->transactionLogHandle( "    ->ORDER SAVED : " . $orderUpdate['orderNumber'] . "\n");
                if ($orderUpdate['items']['totalQuantityReturned'] > 0 || $orderUpdate['items']['totalQuantityBackordered'] > 0) {
                    // SEND EMAIL NOTIFICATION
                    $message = "You are being notified that order update " . $this->filename . " for order number " . $orderUpdate['orderNumber'] . " should not contain shipped items, returned items or backordered items.";
                    //sendNotification($subject = 'STATUS CHECK', $message);
                }
                break;
            case 'On Hold':
                try {
                    // Update state and status
                    $status = 'holded';
                    $state = 'holded';
                    $log_status = 'ON HOLD';
                    $this->salesOrderStatusUpdate($orderUpdate, $log_status, $status, $state, $writeConnection);
                } catch (Exception $e) {
                    throw $e;
                }
                $this->transactionLogHandle( "    ->ORDER SAVED : " . $orderUpdate['orderNumber'] . "\n");
                if ($orderUpdate['items']['totalQuantityReturned'] > 0) {
                    // SEND EMAIL NOTIFICATION
                    $message = "You are being notified that order update " . $this->filename . " for order number " . $orderUpdate['orderNumber'] . " should not contain returned items.";
                    $this->sendNotification( 'ON HOLD STATUS CHECK', $message);
                }
                break;
            case 'Backordered':
                try {
                    // If customerId, create a gift to offset any spent points for this order
                    $this->returnSpentRewardPoints($customerId, $order, $orderUpdate, $readConnection, $writeConnection);

                    //If customerId then add reward validation record if not found
                    $this->createDevicomRewardValidation($customerId, $orderUpdate, $readConnection, $writeConnection);

                    // Update state and status
                    $status = 'backordered';
                    $state = 'processing';
                    $log_status = 'BACKORDERED';
                    $this->salesOrderStatusUpdate($orderUpdate, $log_status, $status, $state, $writeConnection);
                } catch (Exception $e) {
                    throw $e;
                }
                $this->transactionLogHandle( "    ->ORDER SAVED : " . $orderUpdate['orderNumber'] . "\n");
                if ($orderUpdate['items']['totalQuantityShipped'] > 0 || $orderUpdate['items']['totalQuantityReturned'] > 0 || $orderUpdate['items']['totalQuantityBackordered'] != $totalQtyOrdered) {
                    // SEND EMAIL NOTIFICATION
                    $message = "You are being notified that order update " . $this->filename . " for order number " . $orderUpdate['orderNumber'] . " should not contain shipped items or returned items.";
                    $this->sendNotification( 'BACKORDERED STATUS CHECK', $message);
                }
                break;
            case 'Canceled':
            case 'Canceled (Fraud)':
            case 'Canceled (Customer)':
            case 'Canceled (Test/Invalid)':
                try {
                    // If customerId, create a gift to offset any spent points for this order
                    $this->returnSpentRewardPoints($customerId, $order, $orderUpdate, $readConnection, $writeConnection);

                    //If customerId then add reward validation record if not found
                    $this->createDevicomRewardValidation($customerId, $orderUpdate, $readConnection, $writeConnection);

                    // Update state and status
                    $status = 'cancel';
                    $state = 'processing';
                    $log_status = 'CANCEL';
                    $this->salesOrderStatusUpdate($orderUpdate, $log_status, $status, $state, $writeConnection);
                } catch (Exception $e) {
                    throw $e;
                }
                $this->transactionLogHandle( "    ->ORDER SAVED : " . $orderUpdate['orderNumber'] . "\n");
                if ($orderUpdate['items']['totalQuantityShipped'] > 0 || $orderUpdate['items']['totalQuantityReturned'] > 0) {
                    // SEND EMAIL NOTIFICATION
                    $message = "You are being notified that order update " . $this->filename . " for order number " . $orderUpdate['orderNumber'] . " should not contain shipped items or returned items.";
                    sendNotification($subject = 'CANCELED STATUS CHECK', $message);
                }
                break;
            case 'Shipped':
                try {

                    // Create invoice if no shipment was created and canInvoice(),
                    // to handle virtual products. If a shipment WAS created, it would have already been invoiced.
                    if ($order->canInvoice() && !$shipmentCreated) {
                        $this->createOrderInvoice($order, $orderUpdate, $totalQtyOrdered);
                    }

                    //If customerId then add reward validation record if not found
                    $this->createDevicomRewardValidation($customerId, $orderUpdate, $readConnection, $writeConnection);

                    if ($totalQtyOrdered == $orderUpdate['items']['totalQuantityShipped']) {
                        // Update state and status
                        $status = 'shipped';
                        $state = 'processing';
                    } else {
                        // Update state and status
                        $status = 'partially_shipped';
                        $state = 'processing';
                    }
                    $log_status = 'SHIPPED';
                    $this->salesOrderStatusUpdate($orderUpdate, $log_status, $status, $state, $writeConnection);
                } catch (Exception $e) {
                    $exceptionLogHandle = fopen($this->salesLogsDirectory . 'exception_log', 'a');
                    fwrite($exceptionLogHandle, '->' . $this->filename . " - " . $orderUpdate['orderNumber'] . " - " . $e->getMessage() . "\n");
                    fclose($exceptionLogHandle);
                    //continue;
                }

                $this->transactionLogHandle( "    ->ORDER SAVED : " . $orderUpdate['orderNumber'] . "\n");

                if ($order->canShip()) {
                    if ($totalQtyOrdered == $orderUpdate['items']['totalQuantityShipped']) {
                        if ($orderUpdate['trackingNumber'] == '' || $orderUpdate['items']['totalQuantityShipped'] == 0 || $orderUpdate['items']['totalQuantityReturned'] > 0 || $orderUpdate['items']['totalQuantityBackordered'] > 0) {
                            // SEND EMAIL NOTIFICATION
                            $message = "You are being notified that order update " . $this->filename . " for order number " . $orderUpdate['orderNumber'] . " should contain tracking data, all items shipped, no backordered items and no returned items.";
                            $this->sendNotification( 'SHIPPED STATUS CHECK', $message);
                        }
                    } else {
                        if ($orderUpdate['trackingNumber'] == '' || $orderUpdate['items']['totalQuantityShipped'] == 0 || $orderUpdate['items']['totalQuantityReturned'] > 0) {
                            // SEND EMAIL NOTIFICATION
                            $message = "You are being notified that order update " . $this->filename . " for order number " . $orderUpdate['orderNumber'] . " should contain tracking data, some shipped items and no returned items.";
                            $this->sendNotification( 'PARTIAL SHIPPED STATUS CHECK', $message);
                        }
                    }
                }
                break;
            case 'Returned':
                try {
                    // Update state and status
                    if ($order->getState() != 'complete') {
                        $status = 'returned';
                        $state = 'processing';
                    } else {
                        $status = 'returned_complete';
                        $state = 'complete';
                    }
                    $log_status = 'RETURNED';
                    $this->salesOrderStatusUpdate($orderUpdate, $log_status, $status, $state, $writeConnection);
                } catch (Exception $e) {
                    throw $e;
                }
                $this->transactionLogHandle( "    ->ORDER SAVED : " . $orderUpdate['orderNumber'] . "\n");
                if ($orderUpdate['trackingNumber'] == '' || $orderUpdate['items']['totalQuantityShipped'] == 0 || $orderUpdate['items']['totalQuantityReturned'] == 0) {
                    // SEND EMAIL NOTIFICATION
                    $message = "You are being notified that order update " . $this->filename . " for order number " . $orderUpdate['orderNumber'] . " should contain tracking data, some shipped items and some returned items.";
                    $this->sendNotification( 'RETURNED STATUS CHECK', $message);
                }
                break;
            default:
                // SEND EMAIL NOTIFICATION
                $message = "You are being notified that order update " . $this->filename . " for order number " . $orderUpdate['orderNumber'] . " requires a new rule to be evaluated.";
                sendNotification($subject = 'INVALID ORDER STATUS', $message);
        }
    }
    function salesOrderStatusUpdate($orderUpdate, $log_status, $status, $state, $writeConnection) {

        $query = "UPDATE `sales_flat_order` SET `status` = '" . $status . "', `state` = '" . $state . "' WHERE `increment_id` = '" . $orderUpdate['orderNumber'] . "'";
        $writeConnection->query($query);

        $this->transactionLogHandle( "      ->STATUS    : CHANGED TO : " . $log_status . " (sales_flat_order)\n");

        $query = "UPDATE `sales_flat_order_grid` SET `status` = '" . $status . "' WHERE `increment_id` = '" . $orderUpdate['orderNumber'] . "'";
        $writeConnection->query($query);

        $this->transactionLogHandle( "      ->STATUS    : CHANGED TO : " . $log_status . " (sales_flat_order_grid)\n");
    }

    function returnSpentRewardPoints($customerId, $order, $orderUpdate, $readConnection, $writeConnection, $unshippedRewardItems = array()) {

        if ($customerId) {
            $this->transactionLogHandle( "      ->RWDPOINTS : CHECKING TRANSACTION RECORDS\n");

            $pointsToCredit = 0;

            // Retrieve "spent" transaction entry (if any)
            $query = "SELECT rpa.* FROM rewardpoints_account rpa " .
                "INNER JOIN sales_flat_quote sfq ON (rpa.quote_id = sfq.entity_id) " .
                "WHERE sfq.reserved_order_id = {$orderUpdate['orderNumber']} " .
                "AND rpa.customer_id = $customerId " .
                "AND rpa.order_id = '-10'";

            $pointsSpentData = $readConnection->fetchRow($query);

            // Backordered || Canceled
            if ($orderUpdate['status'] == 'Backordered' || $orderUpdate['status'] == 'Canceled') {
                $pointsToCredit = $pointsSpentData['points_spent'];

                // Partially shipped
            } elseif (count($unshippedRewardItems)) {
                // Get rewardpoints value for each item

                foreach ($unshippedRewardItems as $rewardItem) {
                    // Strip size from sku
                    $sku = explode("-", $rewardItem['sku']);
                    array_pop($sku);
                    $newSku = implode("-", $sku);
                    $product = Mage::getModel('catalog/product')->loadByAttribute('sku', $newSku);
                    $pointsToCredit += ($product->getj2tRewardvalue() * ((int)$rewardItem['quantityOrdered'] - (int)$rewardItem['quantityShipped']));
                }
            }

            // If no points to credit, nothing to do.
            if (!$pointsToCredit) {
                $this->transactionLogHandle( "        ->POINTS  : NO POINTS SPENT THIS ORDER\n");
                return;
            }

            // Check to see if we've already credited the points
            $query = "SELECT COUNT(*) FROM rewardpoints_account rpa " .
                "INNER JOIN sales_flat_quote sfq ON (rpa.quote_id = sfq.entity_id) " .
                "WHERE sfq.reserved_order_id = {$orderUpdate['orderNumber']} " .
                "AND rpa.customer_id = $customerId " .
                "AND rpa.order_id = '-1'";

            $pointsReturned = $readConnection->fetchOne($query);

            if ($pointsReturned) {
                $this->transactionLogHandle( "        ->POINTS  : $pointsToCredit SPENT POINTS ALREADY RETURNED\n");
                return;
            }

            $this->transactionLogHandle( "        ->POINTS  : RETURNING $pointsToCredit SPENT POINTS\n");

            $pointsReason = ($orderUpdate['status'] == 'Backordered')
                ? "Unable to fulfill redemption product(s) for order {$orderUpdate['orderNumber']}"
                : "Redemption product purchase canceled by customer";

            $query = "INSERT INTO rewardpoints_account (" .
                "customer_id, store_id, order_id, points_current, quote_id, points_reason" .
                ") VALUES (" .
                "$customerId, '{$order->getStoreId()}', '-1', $pointsToCredit, {$pointsSpentData['quote_id']}, " .
                "'" . mysql_escape_string($pointsReason) . "')";

            $writeConnection->query($query);
        }
        return;
    }


    function createDevicomRewardValidation($customerId, $orderUpdate, $readConnection, $writeConnection, $status = 'pending') {
        if ($customerId) {
            // Run query to see if devicom_reward_validation record has already been adjusted
            $query = "SELECT * FROM `devicom_reward_validation` WHERE `increment_id` = '" . $orderUpdate['orderNumber'] . "'";
            $validationResults = $readConnection->query($query);
            $validationRecordNotFound = true;
            foreach ($validationResults as $validationResult) {
                $validationRecordNotFound = NULL;
            }

            if ($validationRecordNotFound) {
                //Add record to devicom_reward_validation if record does not exist
                $query = "INSERT INTO `devicom_reward_validation` (`increment_id`, `status`) VALUES ('" . $orderUpdate['orderNumber'] . "', '" . $status . "')";
                $writeConnection->query($query);

                $this->transactionLogHandle( "      ->ADDED     : SHIPPED REWARD VALIDATION STATUS\n");
            }
        }
    }

    /*****************************************************************************/
    /*****************************************************************************/

    function updateCustomerRewardPoints($customerId, $orderUpdate, $order, $storeId, $writeConnection) {
        if ($customerId) {
            //Determine current reward points for order
            $currentPoints = 0;
            foreach ($orderUpdate['items'] as $itemUpdate) {
                $shippedQty = $itemUpdate['quantityShipped'] - $itemUpdate['quantityReturned'];
                if ($shippedQty > 0) {
                    foreach ($order->getAllItems() as $orderItem) {
                        if ($orderItem->getSku() == $itemUpdate['sku'] && substr($itemUpdate['sku'], 0, 6) != 'promo-' && substr($itemUpdate['sku'], 0, 3) != 'gc-') {
                            $size = end(explode('-', $itemUpdate['sku']));
                            $sku = substr($itemUpdate['sku'], 0, -(strlen($size) + 1));
                            $product = Mage::getModel('catalog/product')->loadByAttribute('sku', $sku);
                            if (is_null($product->getRewardPoints())) {
                                $this->transactionLogHandle( "    ->ELIGIBLE    : " . $itemUpdate['sku'] . " -> " . $shippedQty * round($orderItem->getPrice()) . "\n");
                                $currentPoints += $shippedQty * round($orderItem->getPrice());
                            }
                            break;
                        }
                    }
                }
            }

            //Log new current points
            $this->transactionLogHandle( "    ->ADJUSTMENT  : CURRENT POINTS SET TO: " . $currentPoints . "\n");

            //Update points for order reward transaction if it exists
            $query = "UPDATE `rewardpoints_account` SET `points_current` = " . $currentPoints . " WHERE `customer_id` = " . $customerId . " AND `store_id` = '" . $storeId . "' AND `order_id` = '" . $orderUpdate['orderNumber'] . "'";
            $writeConnection->query($query);

            //Refresh rewarpoints_flat _account table
            RewardPoints_Model_Observer::processRecordFlatRefresh($customerId, $storeId);
            $this->transactionLogHandle( "    ->REFRESH     : FLAT TABLE REFRESHED : " . $customerId . "\n");
        }
    }

    public function removeFile(){
        //Move XML file to failed directory
        if(isset($this->filename) && file_exists($this->receivedDirectory . $this->filename)){
            rename($this->receivedDirectory . $this->filename, $this->processedDirectory . $this->filename);
        }
    }

}