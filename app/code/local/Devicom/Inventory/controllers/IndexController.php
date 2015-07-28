<?php

class Devicom_Inventory_IndexController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
////        $data1 = array('sku'=>'aa','qty'=>11);
//////        $test1 = Mage::getModel('inventory/physical')->setData($data1);
////        $test1 = Mage::getModel('inventory/physical');
////        $test1->setSku('aaa');
////        $test1->setQty(11);
////        $test1->save();
//
////        $test = Mage::getModel('inventory/physical')->updateQtyBySku('bbb', 1234);
//
////        $test = Mage::getModel('inventory/physical')->getCollection();
////        foreach ($test as $data) {
////            echo $data->getSku() . "<br>";
////            echo $data->getQty() . "\n";
////        }
//
//        $order = Mage::getModel('sales/order');
//        $order->loadByIncrementId('100005421');
//
//        $order->setState(Mage_Sales_Model_Order::STATE_PROCESSING);
//        $order->setStatus("alipay_wait_seller_send_goods");
//
//
//        $order->addStatusToHistory(
//            'alipay_wait_seller_send_goods',
//            Mage::helper('alipay')->__('WAIT SELLER SEND GOODS'));
//        $model = Mage::getModel('sales/postorder');
//        $model->post_new_order($order);
//        $order->save();
//
//        $orderUpdate = array();
//
//        $orderUpdate['orderNumber'] = '100002482';
//        $orderUpdate['status'] = 'Shipped';
//        $orderUpdate['carrier'] = 'sf';
//        $orderUpdate['trackingNumber'] = '99999999';
//
//        try {
//            $order = Mage::getModel('sales/order')->loadByIncrementId($orderUpdate['orderNumber']);
//
////            //Currently checking for larger order number than 100000000 to prevent loading of orders for which are not in system and are sneakerhead
////            //This is to allow receiving status updates for sneakerhead orders before migration
////            if(!$this->checkOrderNumber($order,$orderUpdate)){
////                continue;
////            }
////
////            $this->transactionLogHandle( "    ->ORDER LOADED: " . $orderUpdate['orderNumber'] . "\n");
////            $this->transactionLogHandle( "      ->STATUS    : CURRENT    : " . $order->getStatus() . "\n");
////            $this->transactionLogHandle( "      ->STATE     : CURRENT    : " . $order->getState() . "\n");
////            $this->transactionLogHandle( "      ->STATUS    : POSTED     : " . $orderUpdate['status'] . "\n");
//
//            //Bail if state is complete and notify
////            if ( !in_array($order->getState(),$this->orderStatusNeedCheck) ) {
//////                $this->transactionLogHandle( "      ->STATUS    : NOT CHANGED: IN COMPLETE STATE AND NOT A RETURN\n");
////                // SEND EMAIL NOTIFICATION
////                $message = "You are being notified that order update " . $this->filename . " for order number " . $orderUpdate['orderNumber'] . " is in a completed state and cannot be changed to a processing state.";
//////                $this->sendNotification(  'STATUS NOT CHANGED', $message);
////                return;
////            }
//
//            $totalQtyOrdered = 0;
//            foreach ($order->getAllVisibleItems() as $orderItem) {
//                $totalQtyOrdered = $totalQtyOrdered + $orderItem->getQtyOrdered();
//            }
//            settype($totalQtyOrdered, 'integer');
//
//            //Set customer and store ids for validation and reward transaction adjustment
//            $customerId = $order->getCustomerId();
//            $storeId = $order->getStoreId();
//            $orderCreatedAt = $order->getCreatedAt();
//
////            //Add all order items to devicom_order_item table
////            $query = "SELECT * FROM `devicom_order_item` WHERE `increment_id` = '" . $orderUpdate['orderNumber'] . "'";
////            $getOrderResults = $writeConnection->fetchAll($query);
////            $orderFound = NULL;
////            if(count($getOrderResults) > 0 ){
////                $orderFound = 1;
////            }
////
////            $this->transactionLogHandle( "      ->ORDERITEMS:\n");
////            if (is_null($orderFound)) {
////                //ADD
////                $this->addItemToTableDevicomOrderItem($order,$orderUpdate,$writeConnection);
////            } else {
////                //UPDATE
////                $this->updateTableDevicomOrderItem($order,$orderUpdate,$writeConnection);
////            }
//
//            // Create/Update Shipment regardless of status if tracking number provided
//            $shipmentCreated = $this->createOrderShipment($customerId, $order, $orderUpdate, null, null,$totalQtyOrdered);
//
//            // Create invoice only if we create a shipment (regardless of status) and haven't already invoiced
//            if ($shipmentCreated && !$order->hasInvoices()) {
//                $this->createOrderInvoice($order, $orderUpdate, $totalQtyOrdered);
//            }
//
//            $this->checkStatus($orderUpdate,null,null,$customerId,$order,$shipmentCreated,$totalQtyOrdered);
//
//            //If customerId then update points for order reward transaction
//            //            $this->updateCustomerRewardPoints($customerId, $orderUpdate, $order, $storeId, $writeConnection);
//
//        }
//        catch(Exception $ex) {
//            $str = ($orderUpdate['orderNumber']." ( ".$ex->getMessage()." ) \n");
////            $this->transactionLogHandle( "        ->ERROR   : ".$str);
//        }
//
//    }
//
//    function createOrderShipment($customerId, $order, $orderUpdate, $readConnection, $writeConnection,$totalQtyOrdered) {
//
//        //Cannot create/update shipment if no tracking data
//        if (!$orderUpdate['trackingNumber'] || !$orderUpdate['carrier']) {
//            $this->transactionLogHandle( "      ->SHIPMENT  : NO TRACKING DATA\n");
//            return false;
//        }
//        // New Shipment
//        if ($order->getShipmentsCollection()->count() == 0) {
//            $this->newShipment($order,$orderUpdate,$readConnection,$writeConnection,$totalQtyOrdered,$customerId);
//            return true;
//            // Update Shipment
//        } else {
//            $this->updateShipment($order,$orderUpdate,$writeConnection);
//            // Shipment already exists
//            return false;
//        }
//    }
//
//    public function newShipment($order,$orderUpdate,$readConnection,$writeConnection,$totalQtyOrdered,$customerId){
//        $convertor = Mage::getModel('sales/convert_order');
//        $shipment = $convertor->toShipment($order);
//
////        $this->transactionLogHandle( "      ->SHIPMENT  : CREATING\n");
//
//        $email = true;
//        $includeComment = false;
//        $comment = "Order Completed And Shipped Automatically via Automation Routines";
//
//        // Build list of unshipped items to credit rewardPoints if needed
//        $unshippedRewardItems = array();
//
//        // Complete Shipment
//        if ($totalQtyOrdered == $orderUpdate['items']['totalQuantityShipped']) {
//            foreach ($order->getAllItems() as $orderItem) {
//                if (!$orderItem->getQtyToShip()) {
//                    continue;
//                }
//                if ($orderItem->getIsVirtual()) {
//                    continue;
//                }
//                $item = $convertor->itemToShipmentItem($orderItem);
//                $qty = $orderItem->getQtyToShip();
//                $item->setQty($qty);
//                $shipment->addItem($item);
////                $this->transactionLogHandle( "        ->ADDED   : ITEM " . $orderItem->getSku() . "(" . $qty . ")\n");
//            }
//            // Partial Shipment
//        } else {
//
//            //Get items with qty greater than zero for shipped
//            foreach ($order->getAllItems() as $orderItem) {
////                foreach ($orderUpdate['items'] as $itemUpdate) {
////                    if ($orderItem->getSku() == $itemUpdate['sku']) {
////                        if (!$orderItem->getQtyToShip()) {
////                            continue;
////                        }
////                        if ($orderItem->getIsVirtual()) {
////                            continue;
////                        }
//                        $item = $convertor->itemToShipmentItem($orderItem);
////                        $qty = 1;
////                        $item->setQty($qty);
//                        $shipment->addItem($item);
////                        $this->transactionLogHandle( "        ->ADDED   : ITEM " . $orderItem->getSku() . "(" . $itemUpdate['quantityShipped'] . ")\n");
//
//                        // Add unshipped reward item to list
////                        if (preg_match('/promo-/', $itemUpdate['sku'])
////                            && (int)$itemUpdate['quantityShipped'] < (int)$itemUpdate['quantityOrdered']) {
////                            $unshippedRewardItems[] = $itemUpdate;
////                        }
//
////                        break;
////                    }
////                }
//            }
//        }
//
//        $data = array();
//        $data['carrier_code'] = ''; // will need new logic for converting carriers to Magento codes
//        $data['title'] = $orderUpdate['carrier'];// May change
//        $data['number'] = $orderUpdate['trackingNumber'];
//        $track = Mage::getModel('sales/order_shipment_track')->addData($data);
//        $shipment->addTrack($track);
//
////        $this->transactionLogHandle( "        ->ADDED   : TRACKING DATA\n");
//
//        $shipment->register();
//        $shipment->addComment($comment, $email && $includeComment);
//
////        $this->transactionLogHandle( "        ->ADDED   : COMMENT\n");
//
//        $shipment->setEmailSent(true);
//        $shipment->getOrder()->setIsInProcess(true);
//
//        $shipment->save();
//
//        $shipment->getOrder()->save();
////
////        $transactionSave = Mage::getModel('core/resource_transaction')
////            ->addObject($shipment)
////            ->addObject($shipment->getOrder())
////            ->save();
//
////        $shipment->sendEmail($email, ($includeComment ? $comment : ''));
//
////        $this->transactionLogHandle( "        ->SENT    : EMAIL\n");
//
//        //Removed per Dennis -- will revisit later
//        //$order->setStatus('Complete');
//        //$order->addStatusToHistory($order->getStatus(), 'Order Completed And Shipped Automatically via Automation Routines', false);
//
////        $shipment->save();
////        $this->transactionLogHandle( "      ->SHIPMENT  : CREATED\n");
//
//        if (count($unshippedRewardItems)) {
////            $this->transactionLogHandle( "      ->RWDPOINTS : CHECKING TRANSACTION RECORDS\n");
//            // Create a gift to offset any spent points for unshipped item(s)
////            $this->returnSpentRewardPoints($customerId, $order, $orderUpdate, $readConnection, $writeConnection, $unshippedRewardItems);
//        }
//    }
//    public function updateShipment($order,$orderUpdate,$writeConnection){
//        // Retrieve shipment id
//        $shipmentId = 0;
//        foreach($order->getShipmentsCollection() as $shipment) {
//            $shipmentId = $shipment->getId();
//            break;
//        }
//
////        $this->transactionLogHandle( "      ->SHIPMENT  : UPDATING\n");
//
//        $query = "UPDATE sales_flat_shipment_track SET " .
//            "carrier_code = '" . strtolower($orderUpdate['carrier']) . "', " .
//            "title = '{$orderUpdate['carrier']}', " .
//            "track_number = '{$orderUpdate['trackingNumber']}' " .
//            "WHERE parent_id = $shipmentId";
//
//        $writeConnection->query($query);
//
////        $this->transactionLogHandle( "        ->UPDATED : TRACKING DATA\n");
////        $this->transactionLogHandle( "      ->SHIPMENT  : UPDATED\n");
    }
}