<?php

class Mage_Sales_Model_Postorder extends Mage_Core_Model_Abstract{

    public function __construct() {

    }
    public function post_new_order($newOrder) {
        $html_root = Mage::getBaseDir();
        $postDirectory = $html_root . '/devicom_apps/sales/post/';
        $failedDirectory = $html_root . '/devicom_apps/sales/failed/';
        $salesLogsDirectory = $html_root . '/devicom_apps/sales/logs/';
        $subscribersDirectory = $html_root . '/devicom_apps/sales/subscribers/';
        $inventoryDirectory = $html_root . '/devicom_apps/inventory/product/';

        $realTime = $this->realTime();

        $newOrderFilename = 'new_order_' . $realTime[5] . $realTime[4] . $realTime[3] . '_' . $realTime[2] . $realTime[1] . $realTime[0] . '.xml';

        //Open transaction log
        $transactionLogHandle = fopen($salesLogsDirectory . 'new_order_transaction_log', 'a+');
        fwrite($transactionLogHandle, "->BEGIN PROCESSING: " . $newOrderFilename . "\n");
        fwrite($transactionLogHandle, "  ->START TIME    : " . $realTime[5] . '/' . $realTime[4] . '/' . $realTime[3] . ' ' . $realTime[2] . ':' . $realTime[1] . ':' . $realTime[0] . "\n");

        try {

            // Get connection
            $resource = Mage::getSingleton('core/resource');
            $writeConnection = $resource->getConnection('core_write');

            // Subscribe customer
            $newStoreCode = "sncn";;
            $newStoreId = $newOrder->getStore()->getId();
            $newCustomerId = $newOrder->getCustomerId();
            $newCustomerGroupId = $newOrder->getCustomerGroupId();
            $newEmail = $newOrder->getBillingAddress()->getData('email');
            $newFirstName = $newOrder->getBillingAddress()->getData('firstname');
            $newLastName = $newOrder->getBillingAddress()->getData('lastname');

            fwrite($transactionLogHandle, "    ->NEW ORDER   : " . $newOrder->getIncrementId() . "\n");

            $newsSubscribed = Mage::getSingleton('checkout/session')->getSubscribed();
            $reviewsSubscribed = Mage::getSingleton('checkout/session')->getReviewsSubscribed();

            if ($newsSubscribed == '1' // newsletter
                || $reviewsSubscribed == '1' // reviews
            ) {
                /* Check for unsubscribed. Skip if true */
                require_once $html_root . '/devicom_apps/lib/Devicom/config.php';
                require_once $html_root . '/devicom_apps/lib/ConstantContact/class.cc.php';
                $cc = new cc(CREATE_CONTACT_CC_USERNAME, CREATE_CONTACT_CC_PASSWORD);

                // Retrieve constant contact info for this customer
                $contact = $cc->query_contacts($newEmail); // Needed to retrieve contact ID
                // Customer details
                $contact = $cc->get_contact($contact['id']);

                if (!strcasecmp($contact['Status'], 'do not mail')) {
                    fwrite($transactionLogHandle, "    ->UNSUBSCRIBED: EMAIL ADDRESS " . $newEmail . "\n");
                }

                if (!is_array($contact) // contact doesn't exist or
                    || strcasecmp($contact['Status'], 'do not mail') // exists and status != 'do not mail'
                ) {
                    $subscribedFilename = "subscriber_" . $realTime[5] . $realTime[4] . $realTime[3] . '_' . $realTime[2] . $realTime[1] . $realTime[0] . ".xml";

                    //Creates XML string and XML document from the DOM representation
                    $subscriberDom = new DomDocument('1.0');

                    //$currentStoreId = Mage::app()->getStore()->getId();

                    $subscriberEntityRoot = $subscriberDom->appendChild($subscriberDom->createElement('Root'));
                    $subscriberEntityStoreId = $subscriberEntityRoot->appendChild($subscriberDom->createElement('StoreId'));
                    $subscriberEntityStoreId->appendChild($subscriberDom->createTextNode($newStoreId));
                    $subscriberEntityCustomerId = $subscriberEntityRoot->appendChild($subscriberDom->createElement('CustomerId'));
                    $subscriberEntityCustomerId->appendChild($subscriberDom->createTextNode($newCustomerId));
                    $subscriberEntityEmail = $subscriberEntityRoot->appendChild($subscriberDom->createElement('Email'));
                    $subscriberEntityEmail->appendChild($subscriberDom->createTextNode($newEmail));
                    $subscriberEntityFirstName = $subscriberEntityRoot->appendChild($subscriberDom->createElement('FirstName'));
                    $subscriberEntityFirstName->appendChild($subscriberDom->createTextNode($newFirstName));
                    $subscriberEntityLastName = $subscriberEntityRoot->appendChild($subscriberDom->createElement('LastName'));
                    $subscriberEntityLastName->appendChild($subscriberDom->createTextNode($newLastName));

                    $subscriberEntityNewsList = $subscriberEntityRoot->appendChild($subscriberDom->createElement('NewsList'));
                    $subscriberEntityNewsList->appendChild($subscriberDom->createTextNode($newsSubscribed));

                    $subscriberEntityReviewsList = $subscriberEntityRoot->appendChild($subscriberDom->createElement('ReviewsList'));
                    $subscriberEntityReviewsList->appendChild($subscriberDom->createTextNode($reviewsSubscribed));

                    // Make the output pretty
                    $subscriberDom->formatOutput = true;

                    // Save the XML string
                    $subscriberXml = $subscriberDom->saveXML();

                    //Write file to sent directory
                    $subscriberHandle = fopen($subscribersDirectory . $subscribedFilename, 'w');
                    fwrite($subscriberHandle, $subscriberXml);
                    fclose($subscriberHandle);

                    fwrite($transactionLogHandle, "    ->CREATED     : SUBSCRIBE FILE " . $subscribedFilename . "\n");
                }
            }

            //Log new order points if customer
            if ($newCustomerId && $newCustomerGroupId != 2) {

                $currentPoints = 0;
                foreach ($newOrder->getAllItems() as $orderItem) {
                    if (!$orderItem->getQtyToShip()) {
                        continue;
                    } else {
                        $shippedQty = $orderItem->getQtyToShip();
                    }
                    $orderItemSku = $orderItem->getSku();
                    if (substr($orderItemSku, 0, 6) != 'promo-' && substr($orderItemSku, 0, 3) != 'gc-') {
                        $sizeArray = explode('-', $orderItemSku);
                        $size = end($sizeArray);
                        $sku = substr($orderItemSku, 0, -(strlen($size) + 1));
                        $product = Mage::getModel('catalog/product')->loadByAttribute('sku', $sku);
                        if (is_null($product->getRewardPoints())) {
                            fwrite($transactionLogHandle, "    ->ELIGIBLE    : " . $orderItemSku . " -> " . $shippedQty * round($orderItem->getPrice()) . "\n");
                            $currentPoints += $shippedQty * round($orderItem->getPrice());
                        }
                    }
                }

                //Update points for order reward transaction if it exists
                $query = "INSERT INTO `rewardpoints_account` (`customer_id`, `store_id`, `order_id`, `points_current`, `points_spent`) VALUES (" . $newCustomerId . ", '" . $newStoreId . "', '" . $newOrder->getIncrementId() . "'," . $currentPoints . ", 0)";
                //fwrite($transactionLogHandle, "    ->QUERY       : " . $query . "\n");

                $writeConnection->query($query);

                fwrite($transactionLogHandle, "    ->ADDED       : TRANSACTION RECORD   : " . $currentPoints . "\n");

                //Refresh rewarpoints_flat _account table -- not required on new order reward transaction as it creates a NULL store transaction -- J2T runs the refresh and creates
                //RewardPoints_Model_Observer::processRecordFlatRefresh($newCustomerId, $storeId);
                //fwrite($transactionLogHandle, "    ->REFRESH     : FLAT TABLE REFRESHED : " . $newCustomerId . "\n");
            }

            // New Order
            $newOrderNumber = $newOrder->getIncrementId();

            // Date formatted for reciever
            $convertedDate = $realTime[4] . '-' . $realTime[3] . '-' . $realTime[5] . ' ' . $realTime[2] . ':' . $realTime[1] . ':' . $realTime[0];

            //Creates XML string and XML document from the DOM representation
            $newOrderDom = new DomDocument('1.0');

            $newOrderEntityRoot = $newOrderDom->appendChild($newOrderDom->createElement('Root'));

            $newOrderEntityStoreCode = $newOrderEntityRoot->appendChild($newOrderDom->createElement('StoreCode'));
            $newOrderEntityStoreCode->appendChild($newOrderDom->createTextNode($newStoreCode));

            $newOrderEntityOrder = $newOrderEntityRoot->appendChild($newOrderDom->createElement('Order'));

            $newOrderEntityOrderNumber = $newOrderEntityOrder->appendChild($newOrderDom->createElement('OrderNumber'));
            $newOrderEntityOrderNumber->appendChild($newOrderDom->createTextNode($newOrderNumber));

            $newOrderEntityOrderDate = $newOrderEntityOrder->appendChild($newOrderDom->createElement('OrderDate'));
            $newOrderEntityOrderDate->appendChild($newOrderDom->createTextNode($convertedDate));

            $newOrderEntityProducts = $newOrderEntityOrder->appendChild($newOrderDom->createElement('Products'));

            $items = $newOrder->getAllItems();

            $productFound = NULL;

            foreach ($items as $itemId => $item) {
                if ($item->getQtyToInvoice() > 0) {

                    switch ($item->getSku()) {
                        case 'promo-discount-10':
                            //Check if redemption 10% coupon is being purchased to send code
                            $i = 1;
                            while ($i <= $item->getQtyToInvoice()) {

                                try {
                                    $query = "SELECT COUNT(*) AS `count` FROM `salesrule_coupon` AS `src` WHERE `src`.`rule_id` = 2 AND NOT EXISTS (SELECT * FROM `devicom_coupon_tracker` AS `dct` WHERE `dct`.`code` = `src`.`code`) LIMIT 1";
                                    $results = $writeConnection->query($query);
                                    foreach ($results as $result) {
                                        if ($result['count'] <= 10) {
                                            throw new Exception($newOrderFilename . " : The 10% discount code pool has only 10 or less codes\n");
                                            fwrite($transactionLogHandle, "    ->CODE POOL   : 10% HAS ONLY 10 OR LESS CODES\n");
                                        }
                                    }

                                    $query = "SELECT `src`.`code` AS `code` FROM `salesrule_coupon` AS `src` WHERE `src`.`rule_id` = 2 AND NOT EXISTS (SELECT * FROM `devicom_coupon_tracker` AS `dct` WHERE `dct`.`code` = `src`.`code`) LIMIT 1";
                                    $results = $writeConnection->query($query);

                                    foreach ($results as $result) {

                                        $coupon_code = $result['code'];
                                        $name = $newOrder->getCustomer()->getName();
                                        $email = $newOrder->getCustomer()->getEmail();

                                        $query = "INSERT INTO `devicom_coupon_tracker` (`code`, `rule_id`, `increment_id`, `email`) values ('" . $coupon_code . "', 2, '" . $newOrderNumber . "', '" . $email . "')";

                                        $result = $writeConnection->query($query);

                                        //If successful, then email code
                                        // Transactional Email Template's ID
                                        $templateId = 1;

                                        // Set sender information
                                        $senderName = Mage::getStoreConfig('trans_email/ident_support/name');
                                        $senderEmail = Mage::getStoreConfig('trans_email/ident_support/email');
                                        $sender = array('name' => $senderName,
                                            'email' => $senderEmail);

                                        // Set variables that can be used in email template
                                        $vars = array('name' => $name,
                                            'code' => $coupon_code);

                                        // Added footer_review block to template
                                        $isBackend = true;
                                        $customerEmail = $email;
                                        $orderNumber = $newOrderNumber;
                                        include($html_root . "/app/design/frontend/default/sneakerhead/template/email/footer_review.phtml");
                                        $vars['footer_review'] = $footer_review;

                                        $translate = Mage::getSingleton('core/translate');

                                        // Send Transactional Email
                                        Mage::getModel('core/email_template')
                                            ->sendTransactional($templateId, $sender, $email, $name, $vars, $newStoreId);

                                        $translate->setTranslateInline(true);

                                        fwrite($transactionLogHandle, "    ->SENT COUPON : " . $coupon_code . " \n");
                                    }
                                } catch (Exception $e) {
                                    fwrite($transactionLogHandle, "  ->ERROR         : See exception_log\n");

                                    //Append error to exception log
                                    $exceptionHandle = fopen($salesLogsDirectory . 'exception_log', 'a');
                                    fwrite($exceptionHandle, $e->getMessage());
                                    fclose($exceptionHandle);
                                }
                                $i++;
                            }
                            break;
                        case 'promo-discount-5':
                            //Check if redemption 5% coupon is being purchased to send code
                            $i = 1;
                            while ($i <= $item->getQtyToInvoice()) {

                                try {
                                    $query = "SELECT COUNT(*) AS `count` FROM `salesrule_coupon` AS `src` WHERE `src`.`rule_id` = 3 AND NOT EXISTS (SELECT * FROM `devicom_coupon_tracker` AS `dct` WHERE `dct`.`code` = `src`.`code`) LIMIT 1";
                                    $results = $writeConnection->query($query);
                                    foreach ($results as $result) {
                                        if ($result['count'] <= 0) {
                                            throw new Exception($newOrderFilename . " : The 5% discount code pool has only 10 or less codes\n");
                                            fwrite($transactionLogHandle, "    ->CODE POOL   : 5% HAS IOBLY 10 OR LESS CODES\n");
                                        }
                                    }

                                    $query = "SELECT `src`.`code` AS `code` FROM `salesrule_coupon` AS `src` WHERE `src`.`rule_id` = 3 AND NOT EXISTS (SELECT * FROM `devicom_coupon_tracker` AS `dct` WHERE `dct`.`code` = `src`.`code`) LIMIT 1";
                                    $results = $writeConnection->query($query);

                                    foreach ($results as $result) {

                                        $coupon_code = $result['code'];
                                        $name = $newOrder->getCustomer()->getName();
                                        $email = $newOrder->getCustomer()->getEmail();

                                        $query = "INSERT INTO `devicom_coupon_tracker` (`code`, `rule_id`, `increment_id`, `email`) values ('" . $coupon_code . "', 3, '" . $newOrderNumber . "', '" . $email . "')";

                                        $result = $writeConnection->query($query);

                                        //If successful, then email code
                                        // Transactional Email Template's ID
                                        $templateId = 1;

                                        // Set sender information
                                        $senderName = Mage::getStoreConfig('trans_email/ident_support/name');
                                        $senderEmail = Mage::getStoreConfig('trans_email/ident_support/email');
                                        $sender = array('name' => $senderName,
                                            'email' => $senderEmail);

                                        // Set variables that can be used in email template
                                        $vars = array('name' => $name,
                                            'code' => $coupon_code);

                                        // Added footer_review block to template
                                        $isBackend = true;
                                        $customerEmail = $email;
                                        $orderNumber = $newOrderNumber;
                                        include($html_root . "/app/design/frontend/default/sneakerhead/template/email/footer_review.phtml");
                                        $vars['footer_review'] = $footer_review;

                                        $translate = Mage::getSingleton('core/translate');

                                        // Send Transactional Email
                                        Mage::getModel('core/email_template')
                                            ->sendTransactional($templateId, $sender, $email, $name, $vars, $newStoreId);

                                        $translate->setTranslateInline(true);

                                        fwrite($transactionLogHandle, "  ->SENT COUPON   : " . $coupon_code . " \n");
                                    }
                                } catch (Exception $e) {
                                    fwrite($transactionLogHandle, "  ->ERROR         : See exception_log\n");

                                    //Append error to exception log
                                    $exceptionHandle = fopen($salesLogsDirectory . 'exception_log', 'a');
                                    fwrite($exceptionHandle, $e->getMessage());
                                    fclose($exceptionHandle);
                                }
                                $i++;
                            }
                            break;
                        case 'promo-gc-100-virtual':
                            //Check if redemption 100 dollar gift card is being purchased to send code
                            if ($newOrder->canInvoice()) {

                                $query = "SELECT COUNT(*) AS `count` FROM `enterprise_giftcardaccount_pool` WHERE `status` = 0 LIMIT 1";
                                $results = $writeConnection->query($query);
                                foreach ($results as $result) {
                                    if ($result['count'] <= 10) {
                                        throw new Exception($newOrderFilename . " : The gift card code pool has only 10 or less codes\n");
                                        fwrite($transactionLogHandle, "    ->CODE POOL   : promo-gc-100 GIFT CARD HAS ONLY 10 OR LESS CODES\n");
                                    }
                                }

                                $invoice = Mage::getModel('sales/service_order', $newOrder)->prepareInvoice();

                                if (!$invoice->getTotalQty()) {
                                    Mage::throwException(Mage::helper('core')->__('Cannot create an invoice without products.'));
                                }

                                $invoice->setRequestedCaptureCase(Mage_Sales_Model_Order_Invoice::CAPTURE_OFFLINE); //Note CAPTURE OFFLINE
                                $invoice->register();
                                $transactionSave = Mage::getModel('core/resource_transaction')
                                    ->addObject($invoice)
                                    ->addObject($invoice->getOrder());

                                $transactionSave->save();
                                fwrite($transactionLogHandle, "  ->INVOICED       :\n");

                                // Update state and status because Magento changes it to complete on invoice if only virtual products in order
                                $status = 'processing';
                                $state = 'processing';

                                $query = "UPDATE `sales_flat_order` SET `status` = '" . $status . "', `state` = '" . $state . "' WHERE `increment_id` = '" . $newOrderNumber . "'";
                                $writeConnection->query($query);

                                fwrite($transactionLogHandle, "      ->STATUS    : CHANGED TO : PROCESSING (sales_flat_order)\n");

                                $query = "UPDATE `sales_flat_order_grid` SET `status` = '" . $status . "' WHERE `increment_id` = '" . $newOrderNumber . "'";
                                $writeConnection->query($query);

                                fwrite($transactionLogHandle, "      ->STATUS    : CHANGED TO : PROCESSING (sales_flat_order_grid)\n");
                            }
                            break;
                        case 'promo-gc-50-virtual':
                            //Check if redemption 50 dollar gift card is being purchased to send code
                            if ($newOrder->canInvoice()) {

                                $query = "SELECT COUNT(*) AS `count` FROM `enterprise_giftcardaccount_pool` WHERE `status` = 0 LIMIT 1";
                                $results = $writeConnection->query($query);
                                foreach ($results as $result) {
                                    if ($result['count'] <= 10) {
                                        throw new Exception($newOrderFilename . " : The gift card code pool has only 10 or less codes\n");
                                        fwrite($transactionLogHandle, "    ->CODE POOL   : promo-gc-50 GIFT CARD HAS ONLY 10 OR LESS CODES\n");
                                    }
                                }

                                $invoice = Mage::getModel('sales/service_order', $newOrder)->prepareInvoice();

                                if (!$invoice->getTotalQty()) {
                                    Mage::throwException(Mage::helper('core')->__('Cannot create an invoice without products.'));
                                }

                                $invoice->setRequestedCaptureCase(Mage_Sales_Model_Order_Invoice::CAPTURE_OFFLINE); //Note CAPTURE OFFLINE
                                $invoice->register();
                                $transactionSave = Mage::getModel('core/resource_transaction')
                                    ->addObject($invoice)
                                    ->addObject($invoice->getOrder());

                                $transactionSave->save();
                                fwrite($transactionLogHandle, "  ->INVOICED       :\n");

                                // Update state and status because Magento changes it to complete on invoice if only virtual products in order
                                $status = 'processing';
                                $state = 'processing';

                                $query = "UPDATE `sales_flat_order` SET `status` = '" . $status . "', `state` = '" . $state . "' WHERE `increment_id` = '" . $newOrderNumber . "'";
                                $writeConnection->query($query);

                                fwrite($transactionLogHandle, "      ->STATUS    : CHANGED TO : PROCESSING (sales_flat_order)\n");

                                $query = "UPDATE `sales_flat_order_grid` SET `status` = '" . $status . "' WHERE `increment_id` = '" . $newOrderNumber . "'";
                                $writeConnection->query($query);

                                fwrite($transactionLogHandle, "      ->STATUS    : CHANGED TO : PROCESSING (sales_flat_order_grid)\n");
                            }
                            break;
                        case 'gc-100-virtual':
                            $query = "SELECT COUNT(*) AS `count` FROM `enterprise_giftcardaccount_pool` WHERE `status` = 0 LIMIT 1";
                            $results = $writeConnection->query($query);
                            foreach ($results as $result) {
                                if ($result['count'] <= 10) {
                                    throw new Exception($newOrderFilename . " : The gift card code pool has only 10 or less codes\n");
                                    fwrite($transactionLogHandle, "    ->CODE POOL   : gc-100 GIFT CARD HAS ONLY 10 OR LESS CODES\n");
                                }
                            }
                            // DO NOTHING TO PREVENT INVENTORY CHECK SINCE GIFT CARD IS NOT INVENTORIED
                            break;
                        case 'test-product-OneSize':
                            // DO NOTHING TO PREVENT INVENTORY CHECK SINCE GIFT CARD IS NOT INVENTORIED
                            break;
                        default:
                            $productFound = true;
                            if (substr($item->getSku(), 0, 6) != 'promo-') {

                                //Only add inventoried products
                                $newOrderEntityProduct = $newOrderEntityProducts->appendChild($newOrderDom->createElement('Product'));

                                $newOrderEntitySku = $newOrderEntityProduct->appendChild($newOrderDom->createElement('Sku'));
                                $newOrderEntitySku->appendChild($newOrderDom->createTextNode($item->getSku()));

                                $newOrderEntityQuantity = $newOrderEntityProduct->appendChild($newOrderDom->createElement('Quantity'));
                                $newOrderEntityQuantity->appendChild($newOrderDom->createTextNode($item->getQtyToInvoice()));

                                // Check real inventory and update Magento inventory to 0 if necessary
                                $query = "SELECT `qty` FROM `devicom_inventory` WHERE `sku` = '" . $item->getSku() . "'";

                                if ($results = $writeConnection->query($query)) {
                                    foreach ($results as $result) {
                                        $resultQty = $result['qty'];
                                        $quantity = $resultQty - $item->getQtyToInvoice();
                                        if ($quantity <= 0) {
                                            $query = "UPDATE `cataloginventory_stock_item` AS `csi` INNER JOIN `catalog_product_entity` AS `cpe` ON `csi`.`product_id` = `cpe`.`entity_id`
						INNER JOIN `cataloginventory_stock_status` AS `css` ON `csi`.`product_id` = `css`.`product_id` SET `csi`.`qty` = 0, `csi`.`is_in_stock` = 0, `css`.`qty` = 0, `css`.`stock_status` = 0
						WHERE `cpe`.`sku` = '" . $item->getSku() . "'";
                                            $updateResults = $writeConnection->query($query);
                                            fwrite($transactionLogHandle, "    ->UPDATE STOCK: Set to 0 for " . $item->getSku() . " \n");
                                        }
                                        // Update item in devicom_inventory table
                                        fwrite($transactionLogHandle, "    ->UPDATING    : Inventory    : " . $item->getSku() . "\n");
                                        $query = "UPDATE `devicom_inventory` SET `qty` = '" . $quantity . "' WHERE `sku` = '" . $item->getSku() . "'";
                                        $results = $writeConnection->query($query);
                                        fwrite($transactionLogHandle, "    ->SET         : Quantity to  : " . $quantity . "\n");
                                        fwrite($transactionLogHandle, "    ->UPDATED     : Inventory    : " . $item->getSku() . "\n");
                                    }
                                }

                                $final_sku = $item->getSku();
                            } else {

                                $choppedSku = substr($item->getSku(), 6);
                                fwrite($transactionLogHandle, "    ->UPDATE STOCK: Redemption   " . $item->getSku() . " \n");

                                //Only add inventoried products
                                $newOrderEntityProduct = $newOrderEntityProducts->appendChild($newOrderDom->createElement('Product'));

                                $newOrderEntitySku = $newOrderEntityProduct->appendChild($newOrderDom->createElement('Sku'));
                                $newOrderEntitySku->appendChild($newOrderDom->createTextNode($choppedSku));

                                $newOrderEntityQuantity = $newOrderEntityProduct->appendChild($newOrderDom->createElement('Quantity'));
                                $newOrderEntityQuantity->appendChild($newOrderDom->createTextNode($item->getQtyToInvoice()));

                                // Check real inventory and update Magento inventory to 0 if necessary
                                $query = "SELECT `qty` FROM `devicom_inventory` WHERE `sku` = '" . $choppedSku . "'";

                                if ($results = $writeConnection->query($query)) {
                                    foreach ($results as $result) {
                                        $resultQty = $result['qty'];
                                        $quantity = $resultQty - $item->getQtyToInvoice();
                                        if ($quantity <= 0) {
                                            $query = "UPDATE `cataloginventory_stock_item` AS `csi` INNER JOIN `catalog_product_entity` AS `cpe` ON `csi`.`product_id` = `cpe`.`entity_id`
						INNER JOIN `cataloginventory_stock_status` AS `css` ON `csi`.`product_id` = `css`.`product_id` SET `csi`.`qty` = 0, `csi`.`is_in_stock` = 0, `css`.`qty` = 0, `css`.`stock_status` = 0
						WHERE `cpe`.`sku` = '" . $choppedSku . "'";
                                            $updateResults = $writeConnection->query($query);
                                            fwrite($transactionLogHandle, "    ->UPDATE STOCK: Set to 0 for " . $choppedSku . " \n");
                                        }
                                        // Update item in devicom_inventory table
                                        fwrite($transactionLogHandle, "    ->UPDATING    : Inventory    : " . $choppedSku . "\n");
                                        $query = "UPDATE `devicom_inventory` SET `qty` = '" . $quantity . "' WHERE `sku` = '" . $choppedSku . "'";
                                        $results = $writeConnection->query($query);
                                        fwrite($transactionLogHandle, "    ->SET         : Quantity to  : " . $quantity . "\n");
                                        fwrite($transactionLogHandle, "    ->UPDATED     : Inventory    : " . $choppedSku . "\n");
                                    }
                                }

                                $final_sku = $choppedSku;
                            }

                            /*****************************************************/
                            /* Generate New Inventory XML File                   */
                            /*****************************************************/
                            $sizeArray = explode('-', $final_sku);
                            $size = end($sizeArray);
                            $parent_sku = substr($final_sku, 0, -(strlen($size) + 1));

                            $query = "SELECT * FROM `devicom_inventory` WHERE `qty` > 0 AND `parent_sku` = '" . $parent_sku . "' AND `parent_product_id` IS NOT NULL ORDER BY `sort_order` ASC";
                            $results = $writeConnection->fetchAll($query);

                            // Build array from result set
                            $inventoryFound = null;
                            $inventory = array();
                            foreach ($results as $result) {
                                $inventoryFound = 1;

                                $inventory[$result['parent_sku']][$result['sku']]['sku'] = $result['sku'];
                                $inventory[$result['parent_sku']][$result['sku']]['parent_sku'] = $result['parent_sku'];
                                $inventory[$result['parent_sku']][$result['sku']]['product_id'] = $result['product_id'];
                                $inventory[$result['parent_sku']][$result['sku']]['option_id'] = $result['option_id'];
                                $inventory[$result['parent_sku']][$result['sku']]['label'] = $result['label'];
                                $inventory[$result['parent_sku']][$result['sku']]['qty'] = $result['qty'];
                            }

                            if ($inventoryFound) {

                                $query = "SELECT * FROM `devicom_inventory` WHERE `qty` > 0 AND `parent_sku` = '" . $parent_sku . "' AND `parent_product_id` IS NOT NULL GROUP BY `parent_product_id` ORDER BY `sort_order` ASC";
                                $results = $writeConnection->fetchAll($query);
                                foreach ($results as $result) {
                                    $parentProductId = $result['parent_product_id'];
                                    $basePrice = $result['base_price'];
                                    $oldPrice = $result['old_price'];
                                    $attributeSetName = $result['attribute_set_name'];
                                    $categoryIds = $result['category_ids'];
                                }

                                foreach ($inventory as $configurable => $simples) {

                                    $itemFilename = $configurable . '.xml';

                                    //Creates XML string and XML document from the DOM representation
                                    $inventoryDom = new DomDocument('1.0');
                                    $inventoryEntityProducts = $inventoryDom->appendChild($inventoryDom->createElement('Products'));
                                    $inventoryEntityInfo = $inventoryEntityProducts->appendChild($inventoryDom->createElement('Info'));

                                    $inventoryEntityParent_product_id = $inventoryEntityInfo->appendChild($inventoryDom->createElement('ParentProductId'));
                                    $inventoryEntityParent_product_id->appendChild($inventoryDom->createTextNode($parentProductId));

                                    $inventoryEntityBase_price = $inventoryEntityInfo->appendChild($inventoryDom->createElement('BasePrice'));
                                    $inventoryEntityBase_price->appendChild($inventoryDom->createTextNode($basePrice));

                                    $inventoryEntityOld_price = $inventoryEntityInfo->appendChild($inventoryDom->createElement('OldPrice'));
                                    $inventoryEntityOld_price->appendChild($inventoryDom->createTextNode($oldPrice));

                                    $inventoryEntityAttribute_set_name = $inventoryEntityInfo->appendChild($inventoryDom->createElement('AttributeSetName'));
                                    $inventoryEntityAttribute_set_name->appendChild($inventoryDom->createTextNode($attributeSetName));

                                    $inventoryEntityCategory_ids = $inventoryEntityInfo->appendChild($inventoryDom->createElement('CategoryIds'));
                                    $inventoryEntityCategory_ids->appendChild($inventoryDom->createTextNode($categoryIds));

                                    foreach ($simples as $simple) {
                                        $inventoryEntityProduct = $inventoryEntityProducts->appendChild($inventoryDom->createElement('Product'));

                                        $inventoryEntityProduct_id = $inventoryEntityProduct->appendChild($inventoryDom->createElement('ProductId'));
                                        $inventoryEntityProduct_id->appendChild($inventoryDom->createTextNode($simple['product_id']));

                                        $inventoryEntityOption_id = $inventoryEntityProduct->appendChild($inventoryDom->createElement('OptionId'));
                                        $inventoryEntityOption_id->appendChild($inventoryDom->createTextNode($simple['option_id']));

                                        $inventoryEntityLabel_id = $inventoryEntityProduct->appendChild($inventoryDom->createElement('Label'));
                                        $inventoryEntityLabel_id->appendChild($inventoryDom->createTextNode($simple['label']));

                                        $inventoryEntityQuantity = $inventoryEntityProduct->appendChild($inventoryDom->createElement('Quantity'));
                                        $inventoryEntityQuantity->appendChild($inventoryDom->createTextNode($simple['qty']));
                                    }

                                    // Make the output pretty
                                    $inventoryDom->formatOutput = true;

                                    // Save the XML string
                                    $inventoryXml = $inventoryDom->saveXML();

                                    //Write file to inventory directory
                                    $inventoryHandle = fopen($inventoryDirectory . $itemFilename, 'w');
                                    fwrite($inventoryHandle, $inventoryXml);
                                    fclose($inventoryHandle);
                                    fwrite($transactionLogHandle, "    ->UPDATED XML  : " . $parent_sku . '.xml' . "\n");
                                }
                            } else {
                                if (file_exists($inventoryDirectory . $parent_sku . '.xml')) {
                                    unlink($inventoryDirectory . $parent_sku . '.xml');
                                    fwrite($transactionLogHandle, "    ->REMOVED XML  : " . $parent_sku . '.xml' . "\n");
                                }
                            }
                        /*****************************************************/
                        /*****************************************************/
                    }
                }
            }

            if ($productFound) {
                // Make the output pretty
                $newOrderDom->formatOutput = true;

                // Save the XML string
                $newOrderXml = $newOrderDom->saveXML();

                //Write file to post directory
                $newOrderHandle = fopen($postDirectory . $newOrderFilename, 'w');
                fwrite($newOrderHandle, $newOrderXml);
                fclose($newOrderHandle);

                fwrite($transactionLogHandle, "  ->CREATED       : NEW ORDER POST FILE " . $newOrderFilename . "\n");
            } else {
                fwrite($transactionLogHandle, "  ->NOT CREATED   : ALL VIRTUAL PRODUCTS\n");
            }
        } catch (Exception $e) {

            fwrite($transactionLogHandle, "  ->ERROR         : See exception_log\n");

            //Append error to exception log
            $exceptionHandle = fopen($salesLogsDirectory . 'exception_log', 'a');
            fwrite($exceptionHandle, $e->getMessage());
            fclose($exceptionHandle);

            //Write file to failed directory
            $newOrderHandle = fopen($failedDirectory . $newOrderFilename, 'w');
            fwrite($newOrderHandle, $newOrderXml);
            fclose($newOrderHandle);
        }

        $endTime = $this->realTime();
        fwrite($transactionLogHandle, "  ->FINISH TIME   : " . $endTime[5] . '/' . $endTime[4] . '/' . $endTime[3] . ' ' . $endTime[2] . ':' . $endTime[1] . ':' . $endTime[0] . "\n");
        fwrite($transactionLogHandle, "->END PROCESSING  : " . $newOrderFilename . "\n");

        // Close transaction log
        fclose($transactionLogHandle);

        return $this;
    }
    function realTime($time = null, $isAssociative = false) {

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
        return array_combine(array('tm_sec', 'tm_min', 'tm_hour', 'tm_mday', 'tm_mon', 'tm_year', 'tm_wday', 'tm_yday', 'tm_isdst'), $explodedTime);
    }

}