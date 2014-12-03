<?php
//Version 1.0 - 3/7/2012

require(dirname(dirname(__FILE__)) . '/lib/Devicom/config.php');

set_time_limit(0);//no timout

$root_dir = dirname(__FILE__);
$salesLogsDirectory = $root_dir . '/logs/';
$receivedDirectory = $root_dir . '/received/';
$failedDirectory = $root_dir . '/failed/';
$processedDirectory = $root_dir . '/processed/';

//Check that no lock exists (indicating file is being written or processed by another script) and, if any, grab the oldest order_status_update to begin processing
if (!rtrim(basename(shell_exec('ls ' . $salesLogsDirectory . '*.lock | head --lines 1')))
	&& $filename = rtrim(basename(shell_exec('ls -t -r ' . $receivedDirectory . '* | head --lines 1')))) {

    if (substr($filename, 0, 19) != 'order_status_update') {
		exit;
    }

    $realTime = realTime();

    //Open transaction log
    $transactionLogHandle = fopen($salesLogsDirectory . 'order_status_update_transaction_log', 'a+');
    fwrite($transactionLogHandle, "->BEGIN PROCESSING: " . $filename . "\n");
    fwrite($transactionLogHandle, "  ->START TIME    : " . $realTime[5] . '/' . $realTime[4] . '/' . $realTime[3] . ' ' . $realTime[2] . ':' . $realTime[1] . ':' . $realTime[0] . "\n");

    //Create process.lock to prevent another sales process from starting
    $processLockFilename = 'process_' . $realTime[5] . $realTime[4] . $realTime[3] . '_' . $realTime[2] . $realTime[1] . $realTime[0] . '.lock';
    $processLockHandle = fopen($salesLogsDirectory . $processLockFilename, 'w+');
    fclose($processLockHandle);

    fwrite($transactionLogHandle, "  ->LOCK CREATED  : " . $processLockFilename . "\n");

    try {

		//Get contents of XML file
		$contents = file_get_contents($receivedDirectory . $filename);

		//Create new XML object
		$xmlOrdersElement = new SimpleXMLElement($contents);
		$elements = $xmlOrdersElement->getName();

		fwrite($transactionLogHandle, "  ->REBUILDING XML:\n");

		// Rebuild XML array into object
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

					$orderUpdates[$orderNumber]['items']['totalQuantityOrdered'] += $xmlItemElement->QuantityOrdered;
					$orderUpdates[$orderNumber]['items']['totalQuantityShipped'] += $xmlItemElement->QuantityShipped;
					$orderUpdates[$orderNumber]['items']['totalQuantityReturned'] += $xmlItemElement->QuantityReturned;
					$orderUpdates[$orderNumber]['items']['totalQuantityBackordered'] += $xmlItemElement->QuantityNeeded;
				}
			}
		}

		fwrite($transactionLogHandle, "  ->REBUILT XML   :\n");

		// Initialize magento environment for 'admin' store
		require_once dirname(dirname(dirname(__FILE__))) . '/app/Mage.php';
		umask(0);
		Mage::app('admin'); //admin defines default value for all stores including the Main Website

		// Get resource
		$resource = Mage::getSingleton('core/resource');
		$readConnection = $resource->getConnection('core_read');
		$writeConnection = $resource->getConnection('core_write');

		if (isset($elements['Orders'])) {

			fwrite($transactionLogHandle, "  ->STATUS UPDATE : STARTED\n");

			foreach ($orderUpdates as $orderUpdate) {

				$order = Mage::getModel('sales/order')->loadByIncrementId($orderUpdate['orderNumber']);

				//Currently checking for larger order number than 100000000 to prevent loading of orders for which are not in system and are sneakerhead
				//This is to allow receiving status updates for sneakerhead orders before migration
				if ($orderUpdate['orderNumber'] > 100000000) {
					if (!$order->getId()) {
						fwrite($transactionLogHandle, "    ->STATUS UPDATE : ORDER NOT FOUND\n");
						// SEND EMAIL NOTIFICATION
						$message = "You are being notified that order number " . $orderUpdate['orderNumber'] . "could not be updated because it could not be found.";
						sendNotification($subject = 'ORDER NOT FOUND', $message);
						continue;
					}
				} else {
					//Yahoo order number
					continue;
				}

				fwrite($transactionLogHandle, "    ->ORDER LOADED: " . $orderUpdate['orderNumber'] . "\n");
				fwrite($transactionLogHandle, "      ->STATUS    : CURRENT    : " . $order->getStatus() . "\n");
				fwrite($transactionLogHandle, "      ->STATE     : CURRENT    : " . $order->getState() . "\n");
				fwrite($transactionLogHandle, "      ->STATUS    : POSTED     : " . $orderUpdate['status'] . "\n");

				//Bail if state is complete and notify
				if ($order->getState() != 'new'
						&& $order->getState() != 'holded'
						&& $order->getState() != 'processing'
						&& $order->getState() != 'payment_review'
						&& $orderUpdate['status'] != 'Returned') {
					fwrite($transactionLogHandle, "      ->STATUS    : NOT CHANGED: IN COMPLETE STATE AND NOT A RETURN\n");
					// SEND EMAIL NOTIFICATION
					$message = "You are being notified that order update " . $filename . " for order number " . $orderUpdate['orderNumber'] . " is in a completed state and cannot be changed to a processing state.";
					sendNotification($subject = 'STATUS NOT CHANGED', $message);
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
				$getOrderResults = $writeConnection->query($query);
				$orderFound = NULL;
				foreach ($getOrderResults as $getOrderResult) {
					$orderFound = 1;
				}

				fwrite($transactionLogHandle, "      ->ORDERITEMS:\n");
				if (is_null($orderFound)) {
					//ADD
					$skuArray = array();
					foreach ($order->getAllItems() as $orderItem) {
						foreach ($orderUpdate['items'] as $itemUpdate) {
							if ($orderItem->getSku() == $itemUpdate['sku'] && !array_key_exists($itemUpdate['sku'], $skuArray) && substr($itemUpdate['sku'], 0, 6) != 'promo-' && substr($itemUpdate['sku'], 0, 3) != 'gc-') {
								$size = end(explode('-', $itemUpdate['sku']));
								$sku = substr($itemUpdate['sku'], 0, -(strlen($size) + 1));
								$product = Mage::getModel('catalog/product')->loadByAttribute('sku', $sku);
								$rewardEligible = (is_null($product->getRewardPoints()) ? 1 : 0);
								$query = "INSERT INTO `devicom_order_item` (`increment_id`, `order_created_at`, `customer_id`, `sku`, `quantity_ordered`, `quantity_shipped`, `quantity_returned`, `quantity_backordered`, `price`, `reward_eligible`, `status`, `carrier`, `tracking_number`, `created_at`) VALUES (" . $orderUpdate['orderNumber'] . ", '" . $orderCreatedAt . "', " . ($customerId ? $customerId : 'NULL') . ", '" . $itemUpdate['sku'] . "', " . $itemUpdate['quantityOrdered'] . ", " . $itemUpdate['quantityShipped'] . ", " . $itemUpdate['quantityReturned'] . ", " . $itemUpdate['quantityBackordered'] . ", '" . $orderItem->getPrice() . "', " . $rewardEligible . ", '" . $orderUpdate['status'] . "', '" . $orderUpdate['carrier'] . "', '" . $orderUpdate['trackingNumber'] . "', CURDATE())";
								$writeConnection->query($query);
								$skuArray[$itemUpdate['sku']] = $itemUpdate['sku'];
								fwrite($transactionLogHandle, "        ->ADDED   : ITEM TO RETURNED TABLE : " . $orderItem->getSku() . "(" . $itemUpdate['quantityReturned'] . ")\n");
								break;
							}
						}
					}
				} else {
					//UPDATE
					$skuArray = array();
					foreach ($order->getAllItems() as $orderItem) {
						foreach ($orderUpdate['items'] as $itemUpdate) {
							if ($orderItem->getSku() == $itemUpdate['sku'] && !array_key_exists($itemUpdate['sku'], $skuArray) && substr($itemUpdate['sku'], 0, 6) != 'promo-' && substr($itemUpdate['sku'], 0, 3) != 'gc-') {
								$query = "UPDATE `devicom_order_item` SET `quantity_ordered` = " . $itemUpdate['quantityOrdered'] . ",`quantity_shipped` = " . $itemUpdate['quantityShipped'] . ",`quantity_returned` = " . $itemUpdate['quantityReturned'] . ",`quantity_backordered` = " . $itemUpdate['quantityBackordered'] . ", `status` = '" . $orderUpdate['status'] . "', `carrier` = '" . $orderUpdate['carrier'] . "', `tracking_number` = '" . $orderUpdate['trackingNumber'] . "', `updated_at` = CURDATE() WHERE `increment_id` = " . $orderUpdate['orderNumber'] . " AND `sku` = '" . $itemUpdate['sku'] . "'";
								$writeConnection->query($query);
								$skuArray[$itemUpdate['sku']] = $itemUpdate['sku'];
								fwrite($transactionLogHandle, "        ->UPDATED : ITEM TO RETURNED TABLE : " . $orderItem->getSku() . "(" . $itemUpdate['quantityReturned'] . ")\n");
								break;
							}
						}
					}
				}

				// Create/Update Shipment regardless of status if tracking number provided
				$shipmentCreated = createOrderShipment($customerId, $order, $orderUpdate, $readConnection, $writeConnection, $transactionLogHandle);

				// Create invoice only if we create a shipment (regardless of status) and haven't already invoiced
				if ($shipmentCreated && !$order->hasInvoices()) {
					createOrderInvoice($order, $orderUpdate, $totalQtyOrdered, $transactionLogHandle);
				}

				switch ($orderUpdate['status']) {
					case 'In Processing':
						try {
							// Update state and status
							$status = 'processing';
							$state = 'processing';
							$log_status = 'IN PROCESSING';
							salesOrderStatusUpdate($orderUpdate, $log_status, $status, $state, $writeConnection, $transactionLogHandle);
						} catch (Exception $e) {
							throw $e;
						}
						fwrite($transactionLogHandle, "    ->ORDER SAVED : " . $orderUpdate['orderNumber'] . "\n");
						if ($orderUpdate['items']['totalQuantityReturned'] > 0 || $orderUpdate['items']['totalQuantityBackordered'] > 0) {
						    // SEND EMAIL NOTIFICATION
						    $message = "You are being notified that order update " . $filename . " for order number " . $orderUpdate['orderNumber'] . " should not contain shipped items, returned items or backordered items.";
							//sendNotification($subject = 'STATUS CHECK', $message);
						}
						break;
					case 'On Hold':
						try {
							// Update state and status
							$status = 'holded';
							$state = 'holded';
							$log_status = 'ON HOLD';
							salesOrderStatusUpdate($orderUpdate, $log_status, $status, $state, $writeConnection, $transactionLogHandle);
						} catch (Exception $e) {
							throw $e;
						}
						fwrite($transactionLogHandle, "    ->ORDER SAVED : " . $orderUpdate['orderNumber'] . "\n");
						if ($orderUpdate['items']['totalQuantityReturned'] > 0) {
							// SEND EMAIL NOTIFICATION
							$message = "You are being notified that order update " . $filename . " for order number " . $orderUpdate['orderNumber'] . " should not contain returned items.";
							sendNotification($subject = 'ON HOLD STATUS CHECK', $message);
						}
						break;
					case 'Backordered':
						try {
							// If customerId, create a gift to offset any spent points for this order
							returnSpentRewardPoints($customerId, $order, $orderUpdate, $readConnection, $writeConnection, $transactionLogHandle);

							//If customerId then add reward validation record if not found
							createDevicomRewardValidation($customerId, $orderUpdate, $readConnection, $writeConnection, $transactionLogHandle);

							// Update state and status
							$status = 'backordered';
							$state = 'processing';
							$log_status = 'BACKORDERED';
							salesOrderStatusUpdate($orderUpdate, $log_status, $status, $state, $writeConnection, $transactionLogHandle);
						} catch (Exception $e) {
							throw $e;
						}
						fwrite($transactionLogHandle, "    ->ORDER SAVED : " . $orderUpdate['orderNumber'] . "\n");
						if ($orderUpdate['items']['totalQuantityShipped'] > 0 || $orderUpdate['items']['totalQuantityReturned'] > 0 || $orderUpdate['items']['totalQuantityBackordered'] != $totalQtyOrdered) {
							// SEND EMAIL NOTIFICATION
							$message = "You are being notified that order update " . $filename . " for order number " . $orderUpdate['orderNumber'] . " should not contain shipped items or returned items.";
							sendNotification($subject = 'BACKORDERED STATUS CHECK', $message);
						}
						break;
					case 'Canceled':
					case 'Canceled (Fraud)':
					case 'Canceled (Customer)':
					case 'Canceled (Test/Invalid)':
						try {
							// If customerId, create a gift to offset any spent points for this order
							returnSpentRewardPoints($customerId, $order, $orderUpdate, $readConnection, $writeConnection, $transactionLogHandle);

							//If customerId then add reward validation record if not found
							createDevicomRewardValidation($customerId, $orderUpdate, $readConnection, $writeConnection, $transactionLogHandle);

							// Update state and status
							$status = 'cancel';
							$state = 'processing';
							$log_status = 'CANCEL';
							salesOrderStatusUpdate($orderUpdate, $log_status, $status, $state, $writeConnection, $transactionLogHandle);
						} catch (Exception $e) {
							throw $e;
						}
						fwrite($transactionLogHandle, "    ->ORDER SAVED : " . $orderUpdate['orderNumber'] . "\n");
						if ($orderUpdate['items']['totalQuantityShipped'] > 0 || $orderUpdate['items']['totalQuantityReturned'] > 0) {
							// SEND EMAIL NOTIFICATION
							$message = "You are being notified that order update " . $filename . " for order number " . $orderUpdate['orderNumber'] . " should not contain shipped items or returned items.";
							sendNotification($subject = 'CANCELED STATUS CHECK', $message);
						}
						break;
					case 'Shipped':
						try {

							// Create invoice if no shipment was created and canInvoice(),
							// to handle virtual products. If a shipment WAS created, it would have already been invoiced.
							if ($order->canInvoice() && !$shipmentCreated) {
								createOrderInvoice($order, $orderUpdate, $totalQtyOrdered, $transactionLogHandle);
							}

							//If customerId then add reward validation record if not found
							createDevicomRewardValidation($customerId, $orderUpdate, $readConnection, $writeConnection, $transactionLogHandle);

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
							salesOrderStatusUpdate($orderUpdate, $log_status, $status, $state, $writeConnection, $transactionLogHandle);
						} catch (Exception $e) {
							$exceptionLogHandle = fopen($salesLogsDirectory . 'exception_log', 'a');
							fwrite($exceptionLogHandle, '->' . $filename . " - " . $orderUpdate['orderNumber'] . " - " . $e->getMessage() . "\n");
							fclose($exceptionLogHandle);
							//continue;
						}

						fwrite($transactionLogHandle, "    ->ORDER SAVED : " . $orderUpdate['orderNumber'] . "\n");

						if ($order->canShip()) {
							if ($totalQtyOrdered == $orderUpdate['items']['totalQuantityShipped']) {
								if ($orderUpdate['trackingNumber'] == '' || $orderUpdate['items']['totalQuantityShipped'] == 0 || $orderUpdate['items']['totalQuantityReturned'] > 0 || $orderUpdate['items']['totalQuantityBackordered'] > 0) {
									// SEND EMAIL NOTIFICATION
									$message = "You are being notified that order update " . $filename . " for order number " . $orderUpdate['orderNumber'] . " should contain tracking data, all items shipped, no backordered items and no returned items.";
									sendNotification($subject = 'SHIPPED STATUS CHECK', $message);
								}
							} else {
								if ($orderUpdate['trackingNumber'] == '' || $orderUpdate['items']['totalQuantityShipped'] == 0 || $orderUpdate['items']['totalQuantityReturned'] > 0) {
									// SEND EMAIL NOTIFICATION
									$message = "You are being notified that order update " . $filename . " for order number " . $orderUpdate['orderNumber'] . " should contain tracking data, some shipped items and no returned items.";
									sendNotification($subject = 'PARTIAL SHIPPED STATUS CHECK', $message);
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
							salesOrderStatusUpdate($orderUpdate, $log_status, $status, $state, $writeConnection, $transactionLogHandle);
						} catch (Exception $e) {
							throw $e;
						}
						fwrite($transactionLogHandle, "    ->ORDER SAVED : " . $orderUpdate['orderNumber'] . "\n");
						if ($orderUpdate['trackingNumber'] == '' || $orderUpdate['items']['totalQuantityShipped'] == 0 || $orderUpdate['items']['totalQuantityReturned'] == 0) {
							// SEND EMAIL NOTIFICATION
							$message = "You are being notified that order update " . $filename . " for order number " . $orderUpdate['orderNumber'] . " should contain tracking data, some shipped items and some returned items.";
							sendNotification($subject = 'RETURNED STATUS CHECK', $message);
						}
						break;
					default:
						// SEND EMAIL NOTIFICATION
						$message = "You are being notified that order update " . $filename . " for order number " . $orderUpdate['orderNumber'] . " requires a new rule to be evaluated.";
						sendNotification($subject = 'INVALID ORDER STATUS', $message);
				}

				//If customerId then update points for order reward transaction
				updateCustomerRewardPoints($customerId, $orderUpdate, $order, $storeId, $transactionLogHandle, $writeConnection);

			}
			fwrite($transactionLogHandle, "  ->STATUS UPDATE : FINISHED\n");
		} else {
			throw new Exception('No Orders Found');
		}

		//Move XML file to processed directory
		rename($receivedDirectory . $filename, $processedDirectory . $filename);

    } catch (Exception $e) {

		fwrite($transactionLogHandle, "  ->ERROR         : See exception_log\n");

		//Append error to exception log file
		$exceptionLogHandle = fopen($salesLogsDirectory . 'exception_log', 'a');
		fwrite($exceptionLogHandle, '->' . $filename . " - " . $e->getMessage() . "\n");
		fclose($exceptionLogHandle);

		//Move XML file to failed directory
		rename($receivedDirectory . $filename, $failedDirectory . $filename);
    }

    //Remove process.lock to allow next cron job to run
    if (file_exists($salesLogsDirectory . $processLockFilename)){
		unlink($salesLogsDirectory . $processLockFilename);
		fwrite($transactionLogHandle, "  ->LOCK REMOVED  : " . $processLockFilename . "\n");
    }

    $endTime = realTime();
    fwrite($transactionLogHandle, "  ->FINISH TIME   : " . $endTime[5] . '/' . $endTime[4] . '/' . $endTime[3] . ' ' . $endTime[2] . ':' . $endTime[1] . ':' . $endTime[0] . "\n");
    fwrite($transactionLogHandle, "->END PROCESSING  : " . $filename . "\n");

    //Close transaction log
    fclose($transactionLogHandle);
}

/*****************************************************************************/
/*****************************************************************************/

function returnSpentRewardPoints($customerId, $order, $orderUpdate, $readConnection, $writeConnection, $transactionLogHandle, $unshippedRewardItems = array()) {

	if ($customerId) {
		fwrite($transactionLogHandle, "      ->RWDPOINTS : CHECKING TRANSACTION RECORDS\n");

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
			fwrite($transactionLogHandle, "        ->POINTS  : NO POINTS SPENT THIS ORDER\n");
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
			fwrite($transactionLogHandle, "        ->POINTS  : $pointsToCredit SPENT POINTS ALREADY RETURNED\n");
			return;
		}

		fwrite($transactionLogHandle, "        ->POINTS  : RETURNING $pointsToCredit SPENT POINTS\n");

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

/*****************************************************************************/
/*****************************************************************************/

function createOrderShipment($customerId, $order, $orderUpdate, $readConnection, $writeConnection, $transactionLogHandle) {

	//Cannot create/update shipment if no tracking data
	if (!$orderUpdate['trackingNumber'] || !$orderUpdate['carrier']) {
		fwrite($transactionLogHandle, "      ->SHIPMENT  : NO TRACKING DATA\n");
		return false;
	}

	// New Shipment
	if ($order->getShipmentsCollection()->count() == 0) {

		$convertor = Mage::getModel('sales/convert_order');
		$shipment = $convertor->toShipment($order);

		fwrite($transactionLogHandle, "      ->SHIPMENT  : CREATING\n");

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
				fwrite($transactionLogHandle, "        ->ADDED   : ITEM " . $orderItem->getSku() . "(" . $qty . ")\n");
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
						fwrite($transactionLogHandle, "        ->ADDED   : ITEM " . $orderItem->getSku() . "(" . $itemUpdate['quantityShipped'] . ")\n");

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

		//Removed per Dennis -- will revisit later
		//$order->setStatus('Complete');
		//$order->addStatusToHistory($order->getStatus(), 'Order Completed And Shipped Automatically via Automation Routines', false);

		$shipment->save();
		fwrite($transactionLogHandle, "      ->SHIPMENT  : CREATED\n");

		if (count($unshippedRewardItems)) {
			fwrite($transactionLogHandle, "      ->RWDPOINTS : CHECKING TRANSACTION RECORDS\n");
			// Create a gift to offset any spent points for unshipped item(s)
			returnSpentRewardPoints($customerId, $order, $orderUpdate, $readConnection, $writeConnection, $transactionLogHandle, $unshippedRewardItems);
		}
		return true;

	// Update Shipment
	} else {
		// Retrieve shipment id
		$shipmentId = 0;
		foreach($order->getShipmentsCollection() as $shipment) {
			$shipmentId = $shipment->getId();
			break;
		}

		fwrite($transactionLogHandle, "      ->SHIPMENT  : UPDATING\n");

		$query = "UPDATE sales_flat_shipment_track SET " .
				"carrier_code = '" . strtolower($orderUpdate['carrier']) . "', " .
				"title = '{$orderUpdate['carrier']}', " .
				"track_number = '{$orderUpdate['trackingNumber']}' " .
				"WHERE parent_id = $shipmentId";

		$writeConnection->query($query);

		fwrite($transactionLogHandle, "        ->UPDATED : TRACKING DATA\n");
		fwrite($transactionLogHandle, "      ->SHIPMENT  : UPDATED\n");

		// Shipment already exists
		return false;
	}
}

/*****************************************************************************/
/*****************************************************************************/

function createDevicomRewardValidation($customerId, $orderUpdate, $readConnection, $writeConnection, $transactionLogHandle, $status = 'pending') {
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

			fwrite($transactionLogHandle, "      ->ADDED     : SHIPPED REWARD VALIDATION STATUS\n");
		}
	}
}

/*****************************************************************************/
/*****************************************************************************/

function updateCustomerRewardPoints($customerId, $orderUpdate, $order, $storeId, $transactionLogHandle, $writeConnection) {
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
							fwrite($transactionLogHandle, "    ->ELIGIBLE    : " . $itemUpdate['sku'] . " -> " . $shippedQty * round($orderItem->getPrice()) . "\n");
							$currentPoints += $shippedQty * round($orderItem->getPrice());
						}
						break;
					}
				}
			}
		}

		//Log new current points
		fwrite($transactionLogHandle, "    ->ADJUSTMENT  : CURRENT POINTS SET TO: " . $currentPoints . "\n");

		//Update points for order reward transaction if it exists
		$query = "UPDATE `rewardpoints_account` SET `points_current` = " . $currentPoints . " WHERE `customer_id` = " . $customerId . " AND `store_id` = '" . $storeId . "' AND `order_id` = '" . $orderUpdate['orderNumber'] . "'";
		$writeConnection->query($query);

		//Refresh rewarpoints_flat _account table
		RewardPoints_Model_Observer::processRecordFlatRefresh($customerId, $storeId);
		fwrite($transactionLogHandle, "    ->REFRESH     : FLAT TABLE REFRESHED : " . $customerId . "\n");
	}
}

/*****************************************************************************/
/*****************************************************************************/

function salesOrderStatusUpdate($orderUpdate, $log_status, $status, $state, $writeConnection, $transactionLogHandle) {

	$query = "UPDATE `sales_flat_order` SET `status` = '" . $status . "', `state` = '" . $state . "' WHERE `increment_id` = '" . $orderUpdate['orderNumber'] . "'";
	$writeConnection->query($query);

	fwrite($transactionLogHandle, "      ->STATUS    : CHANGED TO : " . $log_status . " (sales_flat_order)\n");

	$query = "UPDATE `sales_flat_order_grid` SET `status` = '" . $status . "' WHERE `increment_id` = '" . $orderUpdate['orderNumber'] . "'";
	$writeConnection->query($query);

	fwrite($transactionLogHandle, "      ->STATUS    : CHANGED TO : " . $log_status . " (sales_flat_order_grid)\n");
}

/*****************************************************************************/
/*****************************************************************************/

function createOrderInvoice($order, $orderUpdate, $totalQtyOrdered, $transactionLogHandle) {

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

/*****************************************************************************/
/*****************************************************************************/

?>