<?php
set_time_limit(0);//no timout
ini_set('memory_limit', '1024M');

//TO DO:
//Need either order number or shipped date to determine preocessing or complete state
//STILL NOT SURE ABOUT THE 60 OR 90 -- NEED TO DISCUSS

//NOTES:
//Full cancelled and full backordered orders will not have any tracking data added if available
//Discount not used for point calculation -- full unit price
//Statuses are to be corrected in Stone Edge to rely on instead of determining status
//Shipment and invoice created if status is RETURNED or SHIPPED

$toolsXmlDirectory = '/chroot/home/rxkicksc/rxkicks.com/html/devicom_apps/tools/xml_files/';
$toolsLogsDirectory = '/chroot/home/rxkicksc/rxkicks.com/html/devicom_apps/tools/logs/';
$processedDirectory = '/chroot/home/rxkicksc/rxkicks.com/html/devicom_apps/tools/processed/';

$realTime = realTime();

if (!rtrim(basename(shell_exec('ls /chroot/home/rxkicksc/rxkicks.com/html/devicom_apps/tools/logs/*.lock | head --lines 1')))&& $filename = rtrim(basename(shell_exec('ls -t -r /chroot/home/rxkicksc/rxkicks.com/html/devicom_apps/tools/xml_files/full_order_status_update.xml | head --lines 1')))) {
    
    //Starting order number for creating reward validation record and setting state to processing instead of adding reward points and setting state to complete
    $lastOrderNumberForProcessing = '600402720';//600355797//

    //Open transaction log
    $transactionLogHandle = fopen($toolsLogsDirectory . 'migration_order_status_update_transaction_log', 'a+');
    fwrite($transactionLogHandle, "->BEGIN PROCESSING: " . $filename . "\n");
    fwrite($transactionLogHandle, "  ->START TIME    : " . $realTime[5] . '/' . $realTime[4] . '/' . $realTime[3] . ' ' . $realTime[2] . ':' . $realTime[1] . ':' . $realTime[0] . "\n");

    //Create process.lock to stop further processing of incremental and/or full updates
    $processLockFilename = 'process_' . $realTime[5] . $realTime[4] . $realTime[3] . '_' . $realTime[2] . $realTime[1] . $realTime[0] . '.lock';
    $processLockHandle = fopen($toolsLogsDirectory . $processLockFilename, 'w+');
    fclose($processLockHandle);
   
    fwrite($transactionLogHandle, "  ->LOCK CREATED  : " . $processLockFilename . "\n");
    
    try {

        //Get contents of XML file
        $contents = file_get_contents($toolsXmlDirectory . $filename);

        //Create new XML object
        $xmlOrdersElement = new SimpleXMLElement($contents);
        $elements = $xmlOrdersElement->getName();

        fwrite($transactionLogHandle, "  ->REBUILDING XML:\n");

        // Rebuild XML array into object
        foreach ($xmlOrdersElement->Order as $xmlOrderElement) {

            $orderNumber = 600000000 + (string) $xmlOrderElement->OrderNumber;
            //Add only orders that are less than or equal to last order number
            if ($orderNumber > $lastOrderNumberForProcessing) {
                continue;
            }

            $orderUpdates[$orderNumber]['orderNumber'] = $orderNumber;
            $orderUpdates[$orderNumber]['status'] = (string) $xmlOrderElement->Status;
            $orderUpdates[$orderNumber]['shipDate'] = (string) $xmlOrderElement->ShipDate;
            $orderUpdates[$orderNumber]['carrier'] = (string) $xmlOrderElement->Carrier;
            $orderUpdates[$orderNumber]['trackingNumber'] = (string) $xmlOrderElement->TrackingNumber;

            foreach ($xmlOrderElement->Items as $xmlItemsElement) {
                foreach ($xmlItemsElement->Item as $xmlItemElement) {

                    $sku = (string) $xmlItemElement->Sku;
                    $pricePerUnit = (string) $xmlItemElement->PricePerUnit;

                    $orderUpdates[$orderNumber]['items'][$sku]['sku'] = $sku;
                    $orderUpdates[$orderNumber]['items'][$sku]['pricePerUnit'] = $pricePerUnit;
                    $orderUpdates[$orderNumber]['items'][$sku]['quantityOrdered'] = $xmlItemElement->QuantityOrdered;
                    $orderUpdates[$orderNumber]['items'][$sku]['quantityShipped'] = $xmlItemElement->QuantityShipped;
                    $orderUpdates[$orderNumber]['items'][$sku]['quantityReturned'] = $xmlItemElement->QuantityReturned;
                    $orderUpdates[$orderNumber]['items'][$sku]['quantityBackordered'] = $xmlItemElement->QuantityNeeded;

                    $orderUpdates[$orderNumber]['items']['totalQuantityOrdered'] += $xmlItemElement->QuantityOrdered;
                    $orderUpdates[$orderNumber]['items']['totalQuantityShipped'] += $xmlItemElement->QuantityShipped;
                    $orderUpdates[$orderNumber]['items']['totalQuantityReturned'] += $xmlItemElement->QuantityReturned;
                    $orderUpdates[$orderNumber]['items']['totalQuantityBackordered'] += $xmlItemElement->QuantityNeeded;
                }
            }
        }

        fwrite($transactionLogHandle, "  ->REBUILT XML   :\n");

        // Initialize magento environment for 'admin' store
        require_once '/chroot/home/rxkicksc/rxkicks.com/html/app/Mage.php';
        umask(0);
        Mage::app('admin'); //admin defines default value for all stores including the Main Website

        // Get resource
        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');
        $writeConnection = $resource->getConnection('core_write');

        if (isset($elements['Orders'])) {

            $numberOfItemsToProcess = 1000;
            $itemCounter = 0;
                $itemCount = 0;

            if (file_exists($toolsLogsDirectory . 'stage')){
                $stageContents = file_get_contents($toolsLogsDirectory . 'stage');
                $stage = substr($stageContents, 0, 1);
                $startItem = substr($stageContents, 2);
                $endItem = $startItem + $numberOfItemsToProcess;
            } else {
                $stage = 0; //Configurable
                $startItem = 0;
                $endItem = $numberOfItemsToProcess;
                //Create file if it does not exist
                $stageHandle = fopen($toolsLogsDirectory . 'stage', 'w+');
                fwrite($transactionLogHandle, "  ->STAGE CREATED :\n");
                fclose($stageHandle);
            }	 

            //Get count
            $itemCount = count($orderUpdates);
            fwrite($transactionLogHandle, "  ->ITEM COUNT    : " . $itemCount . "\n");                    
            
            fwrite($transactionLogHandle, "  ->STATUS UPDATE : STARTED\n");

            foreach ($orderUpdates as $orderUpdate) {

                if ($startItem <= $itemCounter && $endItem >= $itemCounter) {
                    
                    $order = Mage::getModel('sales/order')->loadByIncrementId($orderUpdate['orderNumber']);
                    if (!$order->getId()) {
                        fwrite($transactionLogHandle, "    ->ORDER       : ORDER NOT FOUND\n");
                        continue;
                    }

                    fwrite($transactionLogHandle, "    ->ORDER LOADED: " . $orderUpdate['orderNumber'] . "\n");
                    fwrite($transactionLogHandle, "      ->STATUS    : CURRENT    : " . $order->getStatus() . "\n");
                    fwrite($transactionLogHandle, "      ->STATUS    : POSTED     : " . $orderUpdate['status'] . "\n");

                    $totalQtyOrdered = 0;
                    foreach ($order->getAllVisibleItems() as $orderItem) {
                        $totalQtyOrdered = $totalQtyOrdered + $orderItem->getQtyOrdered();
                    }
                    settype($totalQtyOrdered, 'integer');

                    //For rewards adjustment
                    $customerId = $order->getCustomerId();
                    $orderDate = $order->getCreatedAt();

                    //Change status to complete state for orders greater than or equal to $lastOrderNumberForProcessing
                    if ($orderUpdate['orderNumber'] <= $lastOrderNumberForProcessing) {
                        if ($orderUpdate['items']['totalQuantityShipped'] == 0 && $orderUpdate['items']['totalQuantityReturned'] == 0 && $orderUpdate['items']['totalQuantityBackordered'] == $orderUpdate['items']['totalQuantityOrdered']) {
                            //Backordered
                            $orderUpdate['status'] = 'Backordered';
                        } elseif ($orderUpdate['items']['totalQuantityShipped'] == 0 && $orderUpdate['items']['totalQuantityReturned'] == 0 && $orderUpdate['items']['totalQuantityBackordered'] == 0) {
                            //Canceled
                            $orderUpdate['status'] = 'Canceled';
                        } elseif ($orderUpdate['trackingNumber'] != '' && $orderUpdate['items']['totalQuantityShipped'] > 0 && $orderUpdate['items']['totalQuantityReturned'] == 0) {
                            //Shipped
                            $orderUpdate['status'] = 'Shipped';
                        } elseif ($orderUpdate['trackingNumber'] != '' && $orderUpdate['items']['totalQuantityShipped'] > 0 && $orderUpdate['items']['totalQuantityReturned'] > 0) {
                            //Returned
                            $orderUpdate['status'] = 'Returned';
                        } else {
                            $orderUpdate['status'] = 'On Hold';
                        }
                        fwrite($transactionLogHandle, "      ->MIGRATE   : NEW STATUS -> " . $orderUpdate['status'] . "\n");
                    }

                    switch ($orderUpdate['status']) {
                        case 'In Processing':
                            try {
                                if($order->canUnhold()) {
                                    $order->unhold()->save();
                                    fwrite($transactionLogHandle, "      ->STATUS    : CHANGED TO : UNHOLD\n");
                                }
                                //Always processing
                                $order->setStatus('processing');
                                $order->setState('processing');
                                $order->save();

                                fwrite($transactionLogHandle, "      ->STATUS    : CHANGED TO : IN PROCESSING\n");
                            } catch (Exception $e) { 
                                throw $e; 
                            }
                            fwrite($transactionLogHandle, "    ->ORDER SAVED : " . $orderUpdate['orderNumber'] . "\n");
                            if ($orderUpdate['trackingNumber'] != '' || $orderUpdate['items']['totalQuantityReturned'] > 0 || $orderUpdate['items']['totalQuantityBackordered'] > 0) {
                                fwrite($transactionLogHandle, "    ->WRONG STATUS: " . $orderUpdate['orderNumber'] . "\n");
                            }
                            break;
                        case 'On Hold':
                            try {
                                if($order->canHold()) {
                                    $order->hold()->save();
                                    fwrite($transactionLogHandle, "      ->STATUS    : CHANGED TO : HOLD\n");
                                } else {
                                    fwrite($transactionLogHandle, "      ->STATUS    : NOT CHANGED: ON HOLD\n");
                                }
                            } catch (Exception $e) { 
                                throw $e; 
                            }
                            fwrite($transactionLogHandle, "    ->ORDER SAVED : " . $orderUpdate['orderNumber'] . "\n");
                            if ($orderUpdate['trackingNumber'] != '' || $orderUpdate['items']['totalQuantityReturned'] > 0 || $orderUpdate['items']['totalQuantityBackordered'] > 0) {
                                fwrite($transactionLogHandle, "    ->WRONG STATUS: " . $orderUpdate['orderNumber'] . "\n");
                            }
                            break;
                        case 'Backordered':
                            try {
                                if($order->canUnhold()) {
                                    $order->unhold()->save();
                                    fwrite($transactionLogHandle, "      ->STATUS    : CHANGED TO : UNHOLD\n");
                                }

                                //Add reward adjustment if customer found and if reward exist and adjustment needed
                                if ($orderUpdate['orderNumber'] <= $lastOrderNumberForProcessing) {
//                                    if ($customerId) {
//                                        $pointsSpent = NULL;
//                                        foreach ($orderUpdate['items'] as $itemUpdate) {
//                                            $qtyToAdjust = $itemUpdate['quantityOrdered'];
//                                            if ($qtyToAdjust != 0) {
//                                                $pointsSpent += round($qtyToAdjust * $itemUpdate['pricePerUnit'], 0);
//                                            }
//                                        }
//
//                                        //adjust points
//                                        if (!is_null($pointsSpent)) {
//                                            //Get record and update
//                                            $query = "INSERT INTO `rewardpoints_account` (`customer_id`, `store_id`, `order_id`, `points_current`, `points_spent`, `date_start`, `date_end`, `convertion_rate`, `rewardpoints_referral_id`, `quote_id`) VALUES (" . $customerId . ", '21', '-100', 0, " . $pointsSpent . ", '" . $orderDate . "', NULL, 0, NULL, NULL)";
//                                            $writeConnection->query($query);
//                                            fwrite($transactionLogHandle, "      ->ADJUSTED  : REWARD POINTS FOR Customer ID: " . $customerId . " -> Points: " . -$pointsSpent . "\n");
//					    
//					    //Get existing points
//					    $query = "SELECT * FROM `rewardpoints_flat_account` WHERE `user_id` = " . $customerId . " AND `store_id` = 21";
//					    $getPointsResults = $writeConnection->query($query);
//					    foreach ($getPointsResults as $getPointsResult) {  
//						//Update POINTS COLLECTED, USED, WAITING and POINTS CURRENT
//						$newUsed = $getPointsResult['points_used'] + $pointsSpent;
//						$newCurrent = $getPointsResult['points_current'] - $pointsSpent;
//						$query = "UPDATE `rewardpoints_flat_account` SET `points_used` = " . $newUsed . ", `points_current` = " . $newCurrent . " WHERE `user_id` = " . $customerId . " AND `store_id` = 21";
//						$writeConnection->query($query);
//						fwrite($transactionLogHandle, "      ->UPDATED   : FLAT POINTS FOR Customer ID  : " . $customerId . "\n");
//					    }
//                                        }
//                                    }
                                } else {
                                    $query = "INSERT INTO `devicom_reward_validation` (`increment_id`, `status`, `shipped_on`) VALUES ('" . $orderUpdate['orderNumber'] . "', 'backordered', '" . $orderDate . "')";
                                    $writeConnection->query($query);

                                    fwrite($transactionLogHandle, "      ->ADDED     : BACKORDERED REWARD VALIDATION STATUS\n");

				    //PUT THINGS BACK TO ALLOW REWARD VALIDATOR TO ADJUST
				    if ($customerId) {

					//Get existing points
					$query = "SELECT * FROM `rewardpoints_flat_account` WHERE `user_id` = " . $customerId . " AND `store_id` = 21";
					$getPointsResults = $writeConnection->query($query);
					foreach ($getPointsResults as $getPointsResult) {  
					    //Update POINTS COLLECTED, USED, WAITING and POINTS CURRENT
					    $newCollected = $getPointsResult['points_collected'] - round($order->getBaseSubtotal());
					    $newWaiting = $getPointsResult['points_waiting'] + round($order->getBaseSubtotal());
					    $newCurrent = $getPointsResult['points_current'] - round($order->getBaseSubtotal());
					    $query = "UPDATE `rewardpoints_flat_account` SET `points_collected` = " . $newCollected . ", `points_waiting` = " . $newWaiting . ", `points_current` = " . $newCurrent . " WHERE `user_id` = " . $customerId . " AND `store_id` = 21";
					    $writeConnection->query($query);
					    fwrite($transactionLogHandle, "      ->UPDATED   : FLAT POINTS FOR Customer ID  : " . $customerId . "\n");
					}

				    }
                                }

                                if ($orderUpdate['orderNumber'] <= $lastOrderNumberForProcessing) {
                                    $status = 'backordered_complete';
                                    $state = 'complete';
                                } else {
                                    $status = 'backordered';
                                    $state = 'processing';		    
                                }

                                $query = "UPDATE `sales_flat_order` SET `status` = '" . $status . "', `state` = '" . $state . "' WHERE `increment_id` = '" . $orderUpdate['orderNumber'] . "'";
                                $writeConnection->query($query);

                                fwrite($transactionLogHandle, "      ->STATUS    : CHANGED TO : BACKORDERED (sales_flat_order)\n");

                                $query = "UPDATE `sales_flat_order_grid` SET `status` = '" . $status . "' WHERE `increment_id` = '" . $orderUpdate['orderNumber'] . "'";
                                $writeConnection->query($query);

                                fwrite($transactionLogHandle, "      ->STATUS    : CHANGED TO : BACKORDERED (sales_flat_order_grid)\n");

                            } catch (Exception $e) { 
                                throw $e; 
                            }
                            fwrite($transactionLogHandle, "    ->ORDER SAVED : " . $orderUpdate['orderNumber'] . "\n");

                            if ($orderUpdate['trackingNumber'] != '' || $orderUpdate['items']['totalQuantityShipped'] > 0 || $orderUpdate['items']['totalQuantityReturned'] > 0 || $orderUpdate['items']['totalQuantityBackordered'] != $totalQtyOrdered) {
                                fwrite($transactionLogHandle, "    ->WRONG STATUS: " . $orderUpdate['orderNumber'] . "\n");
                            }
                            break;
                        case 'Canceled':
                            try {
                                if($order->canUnhold()) {
                                    $order->unhold()->save();
                                    fwrite($transactionLogHandle, "      ->STATUS    : CHANGED TO : UNHOLD\n");
                                }

                                //Add reward adjustment if customer found and if reward exist and adjustment needed
                                if ($orderUpdate['orderNumber'] <= $lastOrderNumberForProcessing) {
//                                    if ($customerId) {
//                                        $pointsSpent = NULL;
//                                        foreach ($orderUpdate['items'] as $itemUpdate) {
//                                            $qtyToAdjust = $itemUpdate['quantityOrdered'];
//                                            if ($qtyToAdjust != 0) {
//                                                $pointsSpent += round($qtyToAdjust * $itemUpdate['pricePerUnit'], 0);
//                                            }
//                                        }
//
//                                        //add points
//                                        if (!is_null($pointsSpent)) {
//                                            //Get record and update
//                                            $query = "INSERT INTO `rewardpoints_account` (`customer_id`, `store_id`, `order_id`, `points_current`, `points_spent`, `date_start`, `date_end`, `convertion_rate`, `rewardpoints_referral_id`, `quote_id`) VALUES (" . $customerId . ", '21', '-100', 0, " . $pointsSpent . ", '" . $orderDate . "', NULL, 0, NULL, NULL)";
//                                            $writeConnection->query($query);
//                                            fwrite($transactionLogHandle, "      ->ADJUSTED  : REWARD POINTS FOR Customer ID: " . $customerId . " -> Points: " . -$pointsSpent . "\n");
//
//					    //Get existing points
//					    $query = "SELECT * FROM `rewardpoints_flat_account` WHERE `user_id` = " . $customerId . " AND `store_id` = 21";
//					    $getPointsResults = $writeConnection->query($query);
//					    foreach ($getPointsResults as $getPointsResult) {  
//						//Update POINTS COLLECTED, USED, WAITING and POINTS CURRENT
//						$newUsed = $getPointsResult['points_used'] + $pointsSpent;
//						$newCurrent = $getPointsResult['points_current'] - $pointsSpent;
//						$query = "UPDATE `rewardpoints_flat_account` SET `points_used` = " . $newUsed . ", `points_current` = " . $newCurrent . " WHERE `user_id` = " . $customerId . " AND `store_id` = 21";
//						$writeConnection->query($query);
//						fwrite($transactionLogHandle, "      ->UPDATED   : FLAT POINTS FOR Customer ID  : " . $customerId . "\n");
//					    }
//                                        }
//                                    }
                                } else {
                                    $query = "INSERT INTO `devicom_reward_validation` (`increment_id`, `status`, `shipped_on`) VALUES ('" . $orderUpdate['orderNumber'] . "', 'canceled', '" . $orderDate . "')";
                                    $writeConnection->query($query);

                                    fwrite($transactionLogHandle, "      ->ADDED     : CANCEL REWARD VALIDATION STATUS\n");

				    //PUT THINGS BACK TO ALLOW REWARD VALIDATOR TO ADJUST
				    if ($customerId) {

					//Get existing points
					$query = "SELECT * FROM `rewardpoints_flat_account` WHERE `user_id` = " . $customerId . " AND `store_id` = 21";
					$getPointsResults = $writeConnection->query($query);
					foreach ($getPointsResults as $getPointsResult) {  
					    //Update POINTS COLLECTED, USED, WAITING and POINTS CURRENT
					    $newCollected = $getPointsResult['points_collected'] - round($order->getBaseSubtotal());
					    $newWaiting = $getPointsResult['points_waiting'] + round($order->getBaseSubtotal());
					    $newCurrent = $getPointsResult['points_current'] - round($order->getBaseSubtotal());
					    $query = "UPDATE `rewardpoints_flat_account` SET `points_collected` = " . $newCollected . ", `points_waiting` = " . $newWaiting . ", `points_current` = " . $newCurrent . " WHERE `user_id` = " . $customerId . " AND `store_id` = 21";
					    $writeConnection->query($query);
					    fwrite($transactionLogHandle, "      ->UPDATED   : FLAT POINTS FOR Customer ID  : " . $customerId . "\n");
					}

				    }
                                }

                                // Update state and status
                                if ($orderUpdate['orderNumber'] <= $lastOrderNumberForProcessing) {
                                    $status = 'cancel_complete';
                                    $state = 'complete';
                                } else {
                                    $status = 'cancel';
                                    $state = 'processing';
                                }

                                $query = "UPDATE `sales_flat_order` SET `status` = '" . $status . "', `state` = '" . $state . "' WHERE `increment_id` = '" . $orderUpdate['orderNumber'] . "'";
                                $writeConnection->query($query);

                                fwrite($transactionLogHandle, "      ->STATUS    : CHANGED TO : CANCEL (sales_flat_order)\n");

                                $query = "UPDATE `sales_flat_order_grid` SET `status` = '" . $status . "' WHERE `increment_id` = '" . $orderUpdate['orderNumber'] . "'";
                                $writeConnection->query($query);

                                fwrite($transactionLogHandle, "      ->STATUS    : CHANGED TO : CANCEL (sales_flat_order_grid)\n");

                            } catch (Exception $e) { 
                                throw $e; 
                            }
                            fwrite($transactionLogHandle, "    ->ORDER SAVED : " . $orderUpdate['orderNumber'] . "\n");
                            if ($orderUpdate['trackingNumber'] != '' || $orderUpdate['items']['totalQuantityShipped'] > 0 || $orderUpdate['items']['totalQuantityReturned'] > 0 || $orderUpdate['items']['totalQuantityBackordered'] > 0) {
                                fwrite($transactionLogHandle, "    ->WRONG STATUS: " . $orderUpdate['orderNumber'] . "\n");
                            }
                            break;
                        case 'Shipped':
                            try {
                                if($order->canUnhold()) {
                                    $order->unhold()->save();
                                    fwrite($transactionLogHandle, "      ->STATUS    : CHANGED TO : UNHOLD\n");
                                }
                                if ($order->getShipmentsCollection()->count() == 0) {
                                    if ($order->canShip()) {
                                        //Cannot ship if no tracking data
                                        if (!$orderUpdate['trackingNumber']) {
                                            fwrite($transactionLogHandle, "    ->CANNOT CREATE SHIPMENT: " . $orderUpdate['orderNumber'] . "\n");
                                            continue;
                                        }

                                        fwrite($transactionLogHandle, "      ->SHIPMENT  : CREATING\n");

                                        $email = true;
                                        $includeComment = false;//TEST
                                        $comment = "Order Completed And Shipped Automatically via Automation Routines";

                                        $convertor = Mage::getModel('sales/convert_order');
                                        $shipment = $convertor->toShipment($order);

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

                                                fwrite($transactionLogHandle, "        ->ADDED   : ITEM " . $orderItem->getSku() . "(" . $qty . ")\n");
                                            }
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

                                                        fwrite($transactionLogHandle, "        ->ADDED   : ITEM " . $orderItem->getSku() . "(" . $itemUpdate['quantityShipped'] . ")\n");
                                                        break;
                                                    }
                                                }
                                            }
                                        }

                                        $data = array();
                                        $data['carrier_code'] = strtolower($orderUpdate['carrier']); // will need new logic for converting carriers to Magento codes
                                        $data['title'] = $orderUpdate['carrier'];// May change
                                        $data['number'] = $orderUpdate['trackingNumber'];

                                        $track = Mage::getModel('sales/order_shipment_track')->addData($data);
                                        $shipment->addTrack($track);

                                        fwrite($transactionLogHandle, "        ->ADDED   : TRACKING DATA\n");

                                        $shipment->register();

                                        $shipment->addComment($comment, $email && $includeComment);

                                        fwrite($transactionLogHandle, "        ->ADDED   : COMMENT\n");

                                        $shipment->setEmailSent(true);
                                        $shipment->getOrder()->setIsInProcess(true);

                                        $transactionSave = Mage::getModel('core/resource_transaction')
                                            ->addObject($shipment)
                                            ->addObject($shipment->getOrder())
                                            ->save();

                                        $shipment->sendEmail($email, ($includeComment ? $comment : ''));

                                        fwrite($transactionLogHandle, "        ->SENT    : EMAIL\n");

                                        $shipment->save();

                                        fwrite($transactionLogHandle, "      ->SHIPMENT  : CREATED\n");
                                    }

                                    // Create invoice
                                    if($order->canInvoice()) {
                                        fwrite($transactionLogHandle, "      ->INVOICE   : CREATING\n");

                                        if ($totalQtyOrdered == $orderUpdate['items']['totalQuantityShipped']) {
                                            $invoice = Mage::getModel('sales/service_order', $order)->prepareInvoice();
                                            if (!$invoice->getTotalQty()) {
                                                Mage::throwException(Mage::helper('core')->__('Cannot create an invoice without products.'));
                                            }
                                            fwrite($transactionLogHandle, "        ->ADDED   : ALL ITEMS (" . $invoice->getTotalQty() . ")\n");
                                        } else {
                                            foreach ($order->getAllItems() as $orderItem) {
                                                foreach ($orderUpdate['items'] as $itemUpdate) {
                                                    if ($orderItem->getSku() == $itemUpdate['sku']) {
                                                        $qtys[$orderItem->getId()] = $itemUpdate['quantityShipped'];
                                                        fwrite($transactionLogHandle, "        ->ADDED   : ITEM " . $orderItem->getSku() . "(" . $itemUpdate['quantityShipped'] . ")\n");
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

                                        fwrite($transactionLogHandle, "      ->INVOICE   : CREATED\n");
                                    }
				    
				    //Add reward adjustment if customer found and if reward exist and adjustment needed
				    if ($orderUpdate['orderNumber'] <= $lastOrderNumberForProcessing) {
//					if ($customerId) {
//					    $pointsSpent = NULL;
//					    foreach ($orderUpdate['items'] as $itemUpdate) {
//						$qtyToAdjust = $itemUpdate['quantityOrdered'] - $itemUpdate['quantityShipped'] + $itemUpdate['quantityReturned'];
//						if ($qtyToAdjust != 0) {
//						    $pointsSpent += round($qtyToAdjust * $itemUpdate['pricePerUnit'], 0);
//						}
//					    }
//
//					    //add points
//					    if (!is_null($pointsSpent)) {
//						//Get record and update
//						$query = "INSERT INTO `rewardpoints_account` (`customer_id`, `store_id`, `order_id`, `points_current`, `points_spent`, `date_start`, `date_end`, `convertion_rate`, `rewardpoints_referral_id`, `quote_id`) VALUES (" . $customerId . ", '21', '-100', 0, " . $pointsSpent . ", '" . $orderDate . "', NULL, 0, NULL, NULL)";
//						$writeConnection->query($query);
//						fwrite($transactionLogHandle, "      ->ADJUSTED  : REWARD POINTS FOR Customer ID: " . $customerId . " -> Points: " . -$pointsSpent . "\n");
//
//						//Get existing points
//						$query = "SELECT * FROM `rewardpoints_flat_account` WHERE `user_id` = " . $customerId . " AND `store_id` = 21";
//						$getPointsResults = $writeConnection->query($query);
//						foreach ($getPointsResults as $getPointsResult) {  
//						    //Update POINTS COLLECTED, USED, WAITING and POINTS CURRENT
//						    $newUsed = $getPointsResult['points_used'] + $pointsSpent;
//						    $newCurrent = $getPointsResult['points_current'] - $pointsSpent;
//						    $query = "UPDATE `rewardpoints_flat_account` SET `points_used` = " . $newUsed . ", `points_current` = " . $newCurrent . " WHERE `user_id` = " . $customerId . " AND `store_id` = 21";
//						    $writeConnection->query($query);
//						    fwrite($transactionLogHandle, "      ->UPDATED   : FLAT POINTS FOR Customer ID  : " . $customerId . "\n");
//						}
//					    }
//					}
				    } else {
					$query = "INSERT INTO `devicom_reward_validation` (`increment_id`, `status`, `shipped_on`) VALUES ('" . $orderUpdate['orderNumber'] . "', 'shipped', '" . $orderUpdate['shipDate'] . "')";
					$writeConnection->query($query);

					fwrite($transactionLogHandle, "      ->ADDED     : SHIPPED REWARD VALIDATION STATUS\n");

					//PUT THINGS BACK TO ALLOW REWARD VALIDATOR TO ADJUST
					if ($customerId) {

					    //Get existing points
					    $query = "SELECT * FROM `rewardpoints_flat_account` WHERE `user_id` = " . $customerId . " AND `store_id` = 21";
					    $getPointsResults = $writeConnection->query($query);
					    foreach ($getPointsResults as $getPointsResult) {  
						//Update POINTS COLLECTED, USED, WAITING and POINTS CURRENT
						$newCollected = $getPointsResult['points_collected'] - round($order->getBaseSubtotal());
						$newWaiting = $getPointsResult['points_waiting'] + round($order->getBaseSubtotal());
						$newCurrent = $getPointsResult['points_current'] - round($order->getBaseSubtotal());
						$query = "UPDATE `rewardpoints_flat_account` SET `points_collected` = " . $newCollected . ", `points_waiting` = " . $newWaiting . ", `points_current` = " . $newCurrent . " WHERE `user_id` = " . $customerId . " AND `store_id` = 21";
						$writeConnection->query($query);
						fwrite($transactionLogHandle, "      ->UPDATED   : FLAT POINTS FOR Customer ID  : " . $customerId . "\n");
					    }

					}
				    }
                                }

                                if ($orderUpdate['orderNumber'] <= $lastOrderNumberForProcessing) {
                                    if ($totalQtyOrdered == $orderUpdate['items']['totalQuantityShipped']) {
                                        // Update state and status
                                        $status = 'shipped_complete';			
                                        $state = 'complete';
                                    } else {
                                        // Update state and status
                                        $status = 'partially_shipped_complete';			
                                        $state = 'complete';
                                    }
                                } else {
                                    if ($totalQtyOrdered == $orderUpdate['items']['totalQuantityShipped']) {
                                        // Update state and status
                                        $status = 'shipped';			
                                        $state = 'processing';
                                    } else {
                                        // Update state and status
                                        $status = 'partially_shipped';			
                                        $state = 'processing';
                                    }
                                }

                                $query = "UPDATE `sales_flat_order` SET `status` = '" . $status . "', `state` = '" . $state . "' WHERE `increment_id` = '" . $orderUpdate['orderNumber'] . "'";
                                $writeConnection->query($query);

                                fwrite($transactionLogHandle, "      ->STATUS    : CHANGED TO : " . $status . " (sales_flat_order)\n");

                                $query = "UPDATE `sales_flat_order_grid` SET `status` = '" . $status . "' WHERE `increment_id` = '" . $orderUpdate['orderNumber'] . "'";
                                $writeConnection->query($query);

                                fwrite($transactionLogHandle, "      ->STATUS    : CHANGED TO : " . $status . " (sales_flat_order_grid)\n");

                            } catch (Exception $e) { 
                                throw $e; 
                            }
                            fwrite($transactionLogHandle, "    ->ORDER SAVED : " . $orderUpdate['orderNumber'] . "\n");
                            if ($totalQtyOrdered == $orderUpdate['items']['totalQuantityShipped']) {
                                if ($orderUpdate['trackingNumber'] == '' || $orderUpdate['items']['totalQuantityShipped'] == 0 || $orderUpdate['items']['totalQuantityReturned'] > 0 || $orderUpdate['items']['totalQuantityBackordered'] > 0) {
                                    fwrite($transactionLogHandle, "    ->WRONG STATUS: " . $orderUpdate['orderNumber'] . "\n");
                                }
                            } else {
                                if ($orderUpdate['trackingNumber'] == '' || $orderUpdate['items']['totalQuantityShipped'] == 0 || $orderUpdate['items']['totalQuantityReturned'] > 0) {
                                    fwrite($transactionLogHandle, "    ->WRONG STATUS: " . $orderUpdate['orderNumber'] . "\n");
                                }
                            }
                            break;
                        case 'Returned':
                            try {
                                if($order->canUnhold()) {
                                    $order->unhold()->save();
                                    fwrite($transactionLogHandle, "      ->STATUS    : CHANGED TO : UNHOLD\n");
                                }

                                if ($order->getShipmentsCollection()->count() == 0) {
                                    if ($order->canShip()) {
                                        //Cannot ship if no tracking data
                                        if (!$orderUpdate['trackingNumber']) {
                                            fwrite($transactionLogHandle, "    ->CANNOT CREATE SHIPMENT: " . $orderUpdate['orderNumber'] . "\n");
                                            continue;
                                        }

                                        fwrite($transactionLogHandle, "      ->SHIPMENT  : CREATING\n");

                                        $email = true;
                                        $includeComment = false;//TEST
                                        $comment = "Order Completed And Shipped Automatically via Automation Routines";

                                        $convertor = Mage::getModel('sales/convert_order');
                                        $shipment = $convertor->toShipment($order);

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

                                                fwrite($transactionLogHandle, "        ->ADDED   : ITEM " . $orderItem->getSku() . "(" . $qty . ")\n");
                                            }
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

                                                        fwrite($transactionLogHandle, "        ->ADDED   : ITEM " . $orderItem->getSku() . "(" . $itemUpdate['quantityShipped'] . ")\n");
                                                        break;
                                                    }
                                                }
                                            }
                                        }

                                        $data = array();
                                        $data['carrier_code'] = strtolower($orderUpdate['carrier']); // will need new logic for converting carriers to Magento codes
                                        $data['title'] = $orderUpdate['carrier'];// May change
                                        $data['number'] = $orderUpdate['trackingNumber'];

                                        $track = Mage::getModel('sales/order_shipment_track')->addData($data);
                                        $shipment->addTrack($track);

                                        fwrite($transactionLogHandle, "        ->ADDED   : TRACKING DATA\n");

                                        $shipment->register();

                                        $shipment->addComment($comment, $email && $includeComment);

                                        fwrite($transactionLogHandle, "        ->ADDED   : COMMENT\n");

                                        $shipment->setEmailSent(true);
                                        $shipment->getOrder()->setIsInProcess(true);

                                        $transactionSave = Mage::getModel('core/resource_transaction')
                                            ->addObject($shipment)
                                            ->addObject($shipment->getOrder())
                                            ->save();

                                        $shipment->sendEmail($email, ($includeComment ? $comment : ''));

                                        fwrite($transactionLogHandle, "        ->SENT    : EMAIL\n");

                                        $shipment->save();

                                        fwrite($transactionLogHandle, "      ->SHIPMENT  : CREATED\n");
                                    }

                                    // Create invoice
				    if($order->canInvoice()) {
					fwrite($transactionLogHandle, "      ->INVOICE   : CREATING\n");

					if ($totalQtyOrdered == $orderUpdate['items']['totalQuantityShipped']) {
					    $invoice = Mage::getModel('sales/service_order', $order)->prepareInvoice();
					    if (!$invoice->getTotalQty()) {
						Mage::throwException(Mage::helper('core')->__('Cannot create an invoice without products.'));
					    }
					    fwrite($transactionLogHandle, "        ->ADDED   : ALL ITEMS (" . $invoice->getTotalQty() . ")\n");
					} else {
					    foreach ($order->getAllItems() as $orderItem) {
						foreach ($orderUpdate['items'] as $itemUpdate) {
						    if ($orderItem->getSku() == $itemUpdate['sku']) {
							$qtys[$orderItem->getId()] = $itemUpdate['quantityShipped'];
							fwrite($transactionLogHandle, "        ->ADDED   : ITEM " . $orderItem->getSku() . "(" . $itemUpdate['quantityShipped'] . ")\n");
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

					fwrite($transactionLogHandle, "      ->INVOICE   : CREATED\n");
				    }
				    
				    //Add reward adjustment if customer found and if reward exist and adjustment needed
				    if ($orderUpdate['orderNumber'] <= $lastOrderNumberForProcessing) {
//					if ($customerId) {
//					    $pointsSpent = NULL;
//					    foreach ($orderUpdate['items'] as $itemUpdate) {
//						$qtyToAdjust = $itemUpdate['quantityOrdered'] - $itemUpdate['quantityShipped'] + $itemUpdate['quantityReturned'];
//						if ($qtyToAdjust != 0) {
//						    $pointsSpent += round($qtyToAdjust * $itemUpdate['pricePerUnit'], 0);
//						}
//					    }
//
//					    //add points
//					    if (!is_null($pointsSpent)) {
//						//Get record and update
//						$query = "INSERT INTO `rewardpoints_account` (`customer_id`, `store_id`, `order_id`, `points_current`, `points_spent`, `date_start`, `date_end`, `convertion_rate`, `rewardpoints_referral_id`, `quote_id`) VALUES (" . $customerId . ", '21', '-100', 0, " . $pointsSpent . ", '" . $orderDate . "', NULL, 0, NULL, NULL)";
//						$writeConnection->query($query);
//						fwrite($transactionLogHandle, "      ->ADJUSTED  : REWARD POINTS FOR Customer ID: " . $customerId . " -> Points: " . -$pointsSpent . "\n");
//
//						//Get existing points
//						$query = "SELECT * FROM `rewardpoints_flat_account` WHERE `user_id` = " . $customerId . " AND `store_id` = 21";
//						$getPointsResults = $writeConnection->query($query);
//						foreach ($getPointsResults as $getPointsResult) {  
//						    //Update POINTS COLLECTED, USED, WAITING and POINTS CURRENT
//						    $newUsed = $getPointsResult['points_used'] + $pointsSpent;
//						    $newCurrent = $getPointsResult['points_current'] - $pointsSpent;
//						    $query = "UPDATE `rewardpoints_flat_account` SET `points_used` = " . $newUsed . ", `points_current` = " . $newCurrent . " WHERE `user_id` = " . $customerId . " AND `store_id` = 21";
//						    $writeConnection->query($query);
//						    fwrite($transactionLogHandle, "      ->UPDATED   : FLAT POINTS FOR Customer ID  : " . $customerId . "\n");
//						}
//					    }
//					}
				    } else {
					$query = "INSERT INTO `devicom_reward_validation` (`increment_id`, `status`, `shipped_on`) VALUES ('" . $orderUpdate['orderNumber'] . "', 'shipped', '" . $orderUpdate['shipDate'] . "')";
					$writeConnection->query($query);

					fwrite($transactionLogHandle, "      ->ADDED     : SHIPPED REWARD VALIDATION STATUS\n");

					//PUT THINGS BACK TO ALLOW REWARD VALIDATOR TO ADJUST
					if ($customerId) {

					    //Get existing points
					    $query = "SELECT * FROM `rewardpoints_flat_account` WHERE `user_id` = " . $customerId . " AND `store_id` = 21";
					    $getPointsResults = $writeConnection->query($query);
					    foreach ($getPointsResults as $getPointsResult) {  
						//Update POINTS COLLECTED, USED, WAITING and POINTS CURRENT
						$newCollected = $getPointsResult['points_collected'] - round($order->getBaseSubtotal());
						$newWaiting = $getPointsResult['points_waiting'] + round($order->getBaseSubtotal());
						$newCurrent = $getPointsResult['points_current'] - round($order->getBaseSubtotal());
						$query = "UPDATE `rewardpoints_flat_account` SET `points_collected` = " . $newCollected . ", `points_waiting` = " . $newWaiting . ", `points_current` = " . $newCurrent . " WHERE `user_id` = " . $customerId . " AND `store_id` = 21";
						$writeConnection->query($query);
						fwrite($transactionLogHandle, "      ->UPDATED   : FLAT POINTS FOR Customer ID  : " . $customerId . "\n");
					    }

					}
				    }
                                }

                                // Update state and status
                                if ($orderUpdate['orderNumber'] <= $lastOrderNumberForProcessing) {
                                    $status = 'returned_complete';
                                    $state = 'complete';
                                } else {
                                    $status = 'returned';		
                                    $state = 'processing';
                                }

                                $query = "UPDATE `sales_flat_order` SET `status` = '" . $status . "', `state` = '" . $state . "' WHERE `increment_id` = '" . $orderUpdate['orderNumber'] . "'";
                                $writeConnection->query($query);

                                fwrite($transactionLogHandle, "      ->STATUS    : CHANGED TO : RETURNED (sales_flat_order)\n");

                                $query = "UPDATE `sales_flat_order_grid` SET `status` = '" . $status . "' WHERE `increment_id` = '" . $orderUpdate['orderNumber'] . "'";
                                $writeConnection->query($query);

                                fwrite($transactionLogHandle, "      ->STATUS    : CHANGED TO : RETURNED (sales_flat_order_grid)\n");

                            } catch (Exception $e) { 
                                throw $e; 
                            }
                            fwrite($transactionLogHandle, "    ->ORDER SAVED : " . $orderUpdate['orderNumber'] . "\n");
                            if ($orderUpdate['trackingNumber'] == '' || $orderUpdate['items']['totalQuantityShipped'] == 0 || $orderUpdate['items']['totalQuantityReturned'] == 0) {
                                fwrite($transactionLogHandle, "    ->WRONG STATUS: " . $orderUpdate['orderNumber'] . "\n");
                            }
                            break;
                        default:
                            fwrite($transactionLogHandle, "    ->STATUS NOT FOUND: " . $orderUpdate['orderNumber'] . "\n");
                            break;
                    }
                    fwrite($transactionLogHandle, "  ->ITEM          : " .$itemCounter . "\n");

                } elseif ($startItem <= $itemCounter && $endItem <= $itemCount) {
                    //Update stage start
                    fwrite($transactionLogHandle, "  ->UPDATE START  : " . $itemCounter . "\n");
                    $stageHandle = fopen($toolsLogsDirectory . 'stage', 'w+');
                    fwrite($stageHandle, '0:' . $itemCounter);
                    fclose($stageHandle);
                    break;
                } else {
                    fwrite($transactionLogHandle, "  ->SKIP          : " . $itemCounter . "\n");
                }
                $itemCounter++;
            }
            fwrite($transactionLogHandle, "  ->STATUS UPDATE : FINISHED\n");
            if ($itemCount <= $itemCounter) {
                //Move XML file to processed directory
                rename($toolsXmlDirectory . $filename, $processedDirectory . $filename);
		fwrite($transactionLogHandle, "  ->XML RENAMED   :\n");
		
                // remove stage file
                if (file_exists($toolsLogsDirectory . 'stage')){
                    unlink($toolsLogsDirectory . 'stage');
                    fwrite($transactionLogHandle, "  ->STAGE REMOVED :\n");    
                }
            }
        } else {
            throw new Exception('No Orders Found');
        }


    } catch (Exception $e) {

        fwrite($transactionLogHandle, "  ->ERROR         : See exception_log\n");

        //Append error to exception log file
        $exceptionLogHandle = fopen($toolsLogsDirectory . 'exception_log', 'a');
        fwrite($exceptionLogHandle, '->' . $filename . " - " . $e->getMessage() . "\n");
        fclose($exceptionLogHandle);

    }

    //Remove process.lock to allow processing of incremental and/or full updates
    if (file_exists($toolsLogsDirectory . $processLockFilename)){
	unlink($toolsLogsDirectory . $processLockFilename);
	fwrite($transactionLogHandle, "  ->LOCK REMOVED  : " . $processLockFilename . "\n");    
    }
    
    $endTime = realTime();
    fwrite($transactionLogHandle, "  ->FINISH TIME   : " . $endTime[5] . '/' . $endTime[4] . '/' . $endTime[3] . ' ' . $endTime[2] . ':' . $endTime[1] . ':' . $endTime[0] . "\n");
    fwrite($transactionLogHandle, "->END PROCESSING  : " . $filename . "\n");

    //Close transaction log
    fclose($transactionLogHandle);
}

function realTime($time = null, $isAssociative = false){

    $offsetInHours = +8;
    $offsetInSeconds = $offsetInHours * 60 * 60;
   
    if (is_null($time)) {
	$time = time();
    }

    $pstTime = $time + $offsetInSeconds;

    $explodedTime = explode(',', gmdate('s,i,H,d,m,Y,w,z,I', $pstTime));
    
    if (!$isAssociative) {
	return $explodedTime;
    }
    return array_combine(array('tm_sec','tm_min','tm_hour','tm_mday','tm_mon','tm_year','tm_wday','tm_yday','tm_isdst'), $explodedTime);
}
?>