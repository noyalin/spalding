<?php
//TO DO:
//Mapping shipping methods for USPS, free and other custom methods

//NOTES:
//1. Run validator to change to complete for rewards
//2. Status and state to always be Pending -- this will be changed on order status update
//12. No gift card or coupon information available but total due reflects all that is left to be paid

set_time_limit(0);//no timout
ini_set('memory_limit', '1024M');

$toolsXmlDirectory = '/chroot/home/rxkicksc/rxkicks.com/html/devicom_apps/tools/xml_files/';
$toolsLogsDirectory = '/chroot/home/rxkicksc/rxkicks.com/html/devicom_apps/tools/logs/';

// initialize magento environment for 'default' store
require_once '/chroot/home/rxkicksc/rxkicks.com/html/app/Mage.php';
umask(0);
Mage::app('admin'); // Default or your store view name.

//FILE 1
$realTime = realTime();
//Open transaction log
$transactionLogHandle = fopen($toolsLogsDirectory . 'migration_gen_sneakerhead_order_xml_files_transaction_log', 'a+');
fwrite($transactionLogHandle, "->BEGIN PROCESSING\n");
fwrite($transactionLogHandle, "  ->START TIME    : " . $realTime[5] . '/' . $realTime[4] . '/' . $realTime[3] . ' ' . $realTime[2] . ':' . $realTime[1] . ':' . $realTime[0] . "\n");

//ORDERS
fwrite($transactionLogHandle, "  ->GETTING ORDERS\n");

$resource = Mage::getSingleton('core/resource');
$writeConnection = $resource->getConnection('core_write');
//$startDate = '2009-10-00 00:00:00';//>=
$startDate = '2010-03-15 00:00:00';//>=
$endDate = '2010-03-15 00:00:00';//<
//10,000
//$query = "SELECT `users`.`email` AS `user_email`, `users`.`firstName` AS `user_firstname`, `users`.`lastName` AS `user_lastname`, `se_order`.* FROM `se_order` LEFT JOIN `storeorder` ON `se_order`.`yahooOrderIdNumeric` = `storeorder`.`yahooOrderIdNumeric` LEFT JOIN `users` ON `storeorder`.`uid` = `users`.`uid` WHERE `se_order`.`orderCreationDate` > '2009-10-00 00:00:00' AND `se_order`.`orderCreationDate` < '2009-12-14 00:00:00'";
//25,000
//FOLLOWING 8 QUERIES TO BE RUN SEPARATELY TO GENERATE 8 DIFFERENT FILES
$query = "SELECT `users`.`email` AS `user_email`, `users`.`firstName` AS `user_firstname`, `users`.`lastName` AS `user_lastname`, `se_order`.* FROM `se_order` LEFT JOIN `storeorder` ON `se_order`.`yahooOrderIdNumeric` = `storeorder`.`yahooOrderIdNumeric` LEFT JOIN `users` ON `storeorder`.`uid` = `users`.`uid` WHERE `se_order`.`orderCreationDate` >= '2009-10-00 00:00:00' AND `se_order`.`orderCreationDate` < '2010-03-15 00:00:00'";//191298 -> 216253
//$query = "SELECT `users`.`email` AS `user_email`, `users`.`firstName` AS `user_firstname`, `users`.`lastName` AS `user_lastname`, `se_order`.* FROM `se_order` LEFT JOIN `storeorder` ON `se_order`.`yahooOrderIdNumeric` = `storeorder`.`yahooOrderIdNumeric` LEFT JOIN `users` ON `storeorder`.`uid` = `users`.`uid` WHERE `se_order`.`orderCreationDate` >= '2010-03-15 00:00:00' AND `se_order`.`orderCreationDate` < '2010-10-28 00:00:00'";//216254 -> 241203
//$query = "SELECT `users`.`email` AS `user_email`, `users`.`firstName` AS `user_firstname`, `users`.`lastName` AS `user_lastname`, `se_order`.* FROM `se_order` LEFT JOIN `storeorder` ON `se_order`.`yahooOrderIdNumeric` = `storeorder`.`yahooOrderIdNumeric` LEFT JOIN `users` ON `storeorder`.`uid` = `users`.`uid` WHERE `se_order`.`orderCreationDate` >= '2010-10-28 00:00:00' AND `se_order`.`orderCreationDate` < '2011-02-27 00:00:00'";//241204 -> 266066
//$query = "SELECT `users`.`email` AS `user_email`, `users`.`firstName` AS `user_firstname`, `users`.`lastName` AS `user_lastname`, `se_order`.* FROM `se_order` LEFT JOIN `storeorder` ON `se_order`.`yahooOrderIdNumeric` = `storeorder`.`yahooOrderIdNumeric` LEFT JOIN `users` ON `storeorder`.`uid` = `users`.`uid` WHERE `se_order`.`orderCreationDate` >= '2011-02-27 00:00:00' AND `se_order`.`orderCreationDate` < '2011-06-27 00:00:00'";//266067 -> 291019
//$query = "SELECT `users`.`email` AS `user_email`, `users`.`firstName` AS `user_firstname`, `users`.`lastName` AS `user_lastname`, `se_order`.* FROM `se_order` LEFT JOIN `storeorder` ON `se_order`.`yahooOrderIdNumeric` = `storeorder`.`yahooOrderIdNumeric` LEFT JOIN `users` ON `storeorder`.`uid` = `users`.`uid` WHERE `se_order`.`orderCreationDate` >= '2011-06-27 00:00:00' AND `se_order`.`orderCreationDate` < '2011-12-09 00:00:00'";//291020 -> 315244
//$query = "SELECT `users`.`email` AS `user_email`, `users`.`firstName` AS `user_firstname`, `users`.`lastName` AS `user_lastname`, `se_order`.* FROM `se_order` LEFT JOIN `storeorder` ON `se_order`.`yahooOrderIdNumeric` = `storeorder`.`yahooOrderIdNumeric` LEFT JOIN `users` ON `storeorder`.`uid` = `users`.`uid` WHERE `se_order`.`orderCreationDate` >= '2011-12-09 00:00:00' AND `se_order`.`orderCreationDate` < '2012-04-24 00:00:00'";//315245 -> 340092
//$query = "SELECT `users`.`email` AS `user_email`, `users`.`firstName` AS `user_firstname`, `users`.`lastName` AS `user_lastname`, `se_order`.* FROM `se_order` LEFT JOIN `storeorder` ON `se_order`.`yahooOrderIdNumeric` = `storeorder`.`yahooOrderIdNumeric` LEFT JOIN `users` ON `storeorder`.`uid` = `users`.`uid` WHERE `se_order`.`orderCreationDate` >= '2012-04-24 00:00:00' AND `se_order`.`orderCreationDate` < '2012-10-04 00:00:00'";//340093 -> 364330
//$query = "SELECT `users`.`email` AS `user_email`, `users`.`firstName` AS `user_firstname`, `users`.`lastName` AS `user_lastname`, `se_order`.* FROM `se_order` LEFT JOIN `storeorder` ON `se_order`.`yahooOrderIdNumeric` = `storeorder`.`yahooOrderIdNumeric` LEFT JOIN `users` ON `storeorder`.`uid` = `users`.`uid` WHERE `se_order`.`orderCreationDate` >= '2012-10-04 00:00:00' AND `se_order`.`orderCreationDate` < '2013-02-16 00:00:00'";//364331 -> ???
//$query = "SELECT `users`.`email` AS `user_email`, `users`.`firstName` AS `user_firstname`, `users`.`lastName` AS `user_lastname`, `se_order`.* FROM `se_order` LEFT JOIN `storeorder` ON `se_order`.`yahooOrderIdNumeric` = `storeorder`.`yahooOrderIdNumeric` LEFT JOIN `users` ON `storeorder`.`uid` = `users`.`uid` WHERE `se_order`.`orderCreationDate` >= '2013-02-16 00:00:00' AND `se_order`.`orderCreationDate` < '2013-04-23 00:00:00'";//??? -> ???
//$query = "SELECT `users`.`email` AS `user_email`, `users`.`firstName` AS `user_firstname`, `users`.`lastName` AS `user_lastname`, `se_order`.* FROM `se_order` LEFT JOIN `storeorder` ON `se_order`.`yahooOrderIdNumeric` = `storeorder`.`yahooOrderIdNumeric` LEFT JOIN `users` ON `storeorder`.`uid` = `users`.`uid` WHERE `se_order`.`orderCreationDate` >= '2013-04-23 00:00:00'"; //??? -> current (???)

$results = $writeConnection->query($query);
$orderFilename = "order_1_" . $realTime[5] . $realTime[4] . $realTime[3] . '_' . $realTime[2] . $realTime[1] . $realTime[0] . ".xml";
//Creates XML string and XML document from the DOM representation
$dom = new DomDocument('1.0');

$orders = $dom->appendChild($dom->createElement('orders'));
foreach ($results as $result) {
    
    //Add order data
    fwrite($transactionLogHandle, "    ->ADDING ORDER NUMBER   : " . $result['yahooOrderIdNumeric'] . "\n");
    
    // Set some variables
    $base_discount_amountValue = (is_null($result['discount'])) ? '0.0000' : $result['discount'];//appears to be actual total discount
    $base_grand_totalValue = (is_null($result['finalGrandTotal'])) ? '0.0000' : $result['finalGrandTotal'];
    $base_shipping_amountValue = (is_null($result['shippingCost'])) ? '0.0000' : $result['shippingCost'];
    $base_shipping_incl_taxValue = (is_null($result['shippingCost'])) ? '0.0000' : $result['shippingCost'];
    $base_subtotalValue = (is_null($result['finalSubTotal'])) ? '0.0000' : $result['finalSubTotal'];
    $base_subtotal_incl_taxValue = (is_null($result['finalSubTotal'])) ? '0.0000' : $result['finalSubTotal'];
    $base_tax_amountValue = (is_null($result['taxTotal'])) ? '0.0000' : $result['taxTotal'];

    if (!is_null($result['shipCountry']) && $result['shipState'] == 'CA') {
	if (strtolower($result['shipCountry']) == 'united states') {
	    $tax_percentValue = '8.75';
	} else {
	    $tax_percentValue = '0.00';
	}
    } else {
	$tax_percentValue = '0.00';
    }

    $base_total_dueValue = (is_null($result['finalGrandTotal'])) ? '0.0000' : $result['finalGrandTotal'];
    $real_created_atValue = (is_null($result['orderCreationDate'])) ? date("Y-m-d H:i:s") : $result['orderCreationDate'];//current date or order creation date
    $created_at_timestampValue = strtotime($real_created_atValue);//set from created date
    $customer_emailValue = (is_null($result['user_email'])) ? (is_null($result['email'])) ? '' : $result['email'] : $result['user_email'];
    $customer_firstnameValue = (is_null($result['user_firstname'])) ? (is_null($result['firstName'])) ? '' : $result['firstName'] : $result['user_firstname'];
    $customer_lastnameValue = (is_null($result['user_lastname'])) ? (is_null($result['lastName'])) ? '' : $result['lastName'] : $result['user_lastname'];
    if (is_null($result['user_firstname'])) {
	$customer_nameValue = '';
    } else {
	$customer_nameValue = $customer_firstnameValue . ' ' . $customer_lastnameValue;
    }
    $customer_nameValue = $customer_firstnameValue . ' ' . $customer_lastnameValue;
    //Lookup customer
    if ($result['user_email'] == NULL) {
	$customer_group_idValue = 0;
    } else {
	$customerQuery = "SELECT `entity_id` FROM `customer_entity` WHERE `email` = '" . $result['user_email'] . "'";
	$customerResults = $writeConnection->query($customerQuery);
	$customerFound = NULL;
	foreach ($customerResults as $customerResult) {  
	    $customerFound = 1;
	}
	if (!$customerFound) {
	    fwrite($transactionLogHandle, "    ->CUSTOMER NOT FOUND    : " . $result['yahooOrderIdNumeric'] . "\n");	    
	    $customer_group_idValue = 0;
	} else {
	    $customer_group_idValue = 1;
	}
    }
    
    $discount_amountValue = (is_null($result['discount'])) ? '0.0000' : $result['discount'];//appears to be actual total discount
    $grand_totalValue = (is_null($result['finalGrandTotal'])) ? '0.0000' : $result['finalGrandTotal'];
    $increment_idValue = $result['yahooOrderIdNumeric'];//import script adds value to 600000000
    $shipping_amountValue = (is_null($result['shippingCost'])) ? '0.0000' : $result['shippingCost'];
    $shipping_incl_taxValue = (is_null($result['shippingCost'])) ? '0.0000' : $result['shippingCost'];
    switch ($result['shippingMethod']) {
	case 'UPS Ground (3-7 Business Days)':
	    $shipping_methodValue = 'ups_03';
	    break;
	case 'APO & FPO Addresses (5-30 Business Days by USPS)':
	    $shipping_methodValue = 'usps_Priority Mail';
	    break;
	case 'UPS Next Day Air (2-3 Business Days)':
	    $shipping_methodValue = 'ups_01';
	    break;
	case '"Alaska, Hawaii, U.S. Virgin Islands & Puerto Rico':
	    $shipping_methodValue = 'usps_Priority Mail';
	    break;
	case 'UPS 2nd Day Air (3-4 Business Days)':
	    $shipping_methodValue = 'ups_02';
	    break;
	case 'International Express (Shipped with Shoebox)':
	    $shipping_methodValue = 'ups_08';
	    break;
	case 'International Express (Shipped without Shoebox)':
	    $shipping_methodValue = 'ups_08';
	    break;
	case 'USPS Priority Mail (4-5 Business Days)':
	    $shipping_methodValue = 'usps_Priority Mail';
	    break;
	case 'UPS 3 Day Select (4-5 Business Days)':
	    $shipping_methodValue = 'ups_12';
	    break;
	case 'EMS - International':
	    $shipping_methodValue = 'ups_08';
	    break;
	case 'Canada Express (4-7 Business Days)':
	    $shipping_methodValue = 'ups_08';
	    break;
	case 'EMS Canada':
	    $shipping_methodValue = 'ups_08';
	    break;
	case 'Christmas Express (Delivered by Dec. 24th)':
	    $shipping_methodValue = 'ups_03';
	    break;
	case 'COD Ground':
	    $shipping_methodValue = 'ups_03';
	    break;
	case 'COD Overnight':
	    $shipping_methodValue = 'ups_01';
	    break;
	case 'Free Christmas Express (Delivered by Dec. 24th)':
	    $shipping_methodValue = 'ups_03';
	    break;
	case 'New Year Express (Delivered by Dec. 31st)':
	    $shipping_methodValue = 'ups_03';
	    break;
	case 'Free UPS Ground (3-7 Business Days)':
	    $shipping_methodValue = 'ups_03';
	    break;
	case 'COD 2-Day':
	    $shipping_methodValue = 'ups_02';
	    break;
	case 'MSI International Express':
	    $shipping_methodValue = 'ups_08';
	    break;
	case 'Customer Pickup':
	    $shipping_methodValue = 'ups_03';
	    break;
	case 'UPS Ground':
	    $shipping_methodValue = 'ups_03';
	    break;
	case 'UPS 2nd Day Air':
	    $shipping_methodValue = 'ups_02';
	    break;
	case 'APO & FPO Addresses':
	    $shipping_methodValue = 'usps_Priority Mail';
	    break;
	case 'UPS Next Day Air Saver':
	    $shipping_methodValue = 'ups_01';
	    break;
	case 'UPS 3 Day Select':
	    $shipping_methodValue = 'ups_12';
	    break;
	case 'International Express':
	    $shipping_methodValue = 'ups_08';
	    break;
	case 'USPS Priority Mail':
	    $shipping_methodValue = 'usps_Priority Mail';
	    break;
	case 'Canada Express':
	    $shipping_methodValue = 'ups_08';
	    break;
	case 'UPS Next Day Air':
	    $shipping_methodValue = 'ups_01';
	    break;
	case 'Holiday Shipping (Delivered by Dec. 24th)':
	    $shipping_methodValue = 'ups_03';
	    break;
	default://case 'NULL'
	    $shipping_methodValue = '';
	    break;
    }
    
    $stateValue = 'new';//Always new -- will set on order status update
    $statusValue = 'pending';//Always Pending -- will set on order status update
    $subtotalValue = (is_null($result['finalSubTotal'])) ? '0.0000' : $result['finalSubTotal'];
    $subtotal_incl_taxValue = (is_null($result['finalSubTotal'])) ? '0.0000' : $result['finalSubTotal'];
    $tax_amountValue = (is_null($result['taxTotal'])) ? '0.0000' : $result['taxTotal'];
    $total_dueValue = (is_null($result['finalGrandTotal'])) ? '0.0000' : $result['finalGrandTotal'];

    // Get total qty
    $itemsQuery = "SELECT * FROM `se_orderitem` WHERE `yahooOrderIdNumeric` = " . $result['yahooOrderIdNumeric'];
    $itemsResult = $writeConnection->query($itemsQuery);
    $itemCount = 0;
    foreach ($itemsResult as $itemResult) {
	$itemCount += 1;//number of items not quantites
    }
    if ($itemCount == 0) {
	fwrite($transactionLogHandle, "      ->NO ITEMS FOUND      : " . $result['yahooOrderIdNumeric'] . "\n");
    }
    $total_qty_orderedValue = $itemCount . '.0000';//Derived from item qty count
    $updated_atValue = date("Y-m-d H:i:s");
    $updated_at_timestampValue = strtotime($real_created_atValue);
    $weightValue = '0.0000'; //No weight data available

    //Shipping
    $shippingCityValue = (is_null($result['shipCity'])) ? '' : $result['shipCity'];
    $shippingCountryValue = (is_null($result['shipCountry'])) ? '' : $result['shipCountry'];
    $shippingEmailValue = (is_null($result['email'])) ? '' : $result['email'];
    $shippingFirstnameValue = (is_null($result['shipName'])) ? '' : $result['shipName'];
    $shippingLastnameValue = '';
    $shippingNameValue = $result['shipName'];
    $shippingPostcodeValue = (is_null($result['shipZip'])) ? '' : $result['shipZip'];
    if (strtolower($shippingCountryValue) == 'united states') {
	$shippingRegionValue = (is_null($result['shipState'])) ? '' : strtoupper($result['shipState']);
    } else {
	$shippingRegionValue = (is_null($result['shipState'])) ? '' : $result['shipState'];
    }
    $shippingRegion_idValue = '';//Seems to work without conversion
    if ((!is_null($result['shipAddress1']) && $result['shipAddress1'] != '') && (is_null($result['shipAddress2']) || $result['shipAddress2'] == '')) {
	$shippingStreetValue = $result['shipAddress1'];
    } elseif ((!is_null($result['shipAddress1']) && $result['shipAddress1'] != '') && (!is_null($result['shipAddress2']) && $result['shipAddress2'] != '')) {
	$shippingStreetValue = $result['shipAddress2'] . '&#10;' . $result['shipAddress2']; //Include CR/LF
    } elseif ((is_null($result['shipAddress1']) || $result['shipAddress1'] == '') && (!is_null($result['shipAddress2']) && $result['shipAddress2'] != '')) {
	$shippingStreetValue = $result['shipAddress2'];
    } else {
	$shippingStreetValue = '';
    }
    $shippingTelephoneValue = (is_null($result['shipPhone'])) ? '' : $result['shipPhone'];
    
    //Billing
    $billingCityValue = (is_null($result['billCity'])) ? '' : $result['billCity'];
    $billingCountryValue = (is_null($result['billCountry'])) ? '' : $result['billCountry'];
    $billingEmailValue = (is_null($result['email'])) ? '' : $result['email'];
    $billingFirstnameValue = (is_null($result['billName'])) ? '' : $result['billName'];
    $billingLastnameValue = '';
    $billingNameValue = $result['billName'];
    $billingPostcodeValue = (is_null($result['billZip'])) ? '' : $result['billZip'];
    if (strtolower($billingCountryValue) == 'united states') {
	$billingRegionValue = (is_null($result['billState'])) ? '' : strtoupper($result['billState']);
    } else {
	$billingRegionValue = (is_null($result['billState'])) ? '' : $result['billState'];
    }
    $billingRegion_idValue = '';//Seems to work without conversion
    if ((!is_null($result['billAddress1']) && $result['billAddress1'] != '') && (is_null($result['billAddress2']) || $result['billAddress2'] == '')) {
	$billingStreetValue = $result['billAddress1'];
    } elseif ((!is_null($result['billAddress1']) && $result['billAddress1'] != '') && (!is_null($result['billAddress2']) && $result['billAddress2'] != '')) {
	$billingStreetValue = $result['billAddress2'] . '&#10;' . $result['billAddress2']; //Include CR/LF
    } elseif ((is_null($result['billAddress1']) || $result['billAddress1'] == '') && (!is_null($result['billAddress2']) && $result['billAddress2'] != '')) {
	$billingStreetValue = $result['billAddress2'];
    } else {
	$billingStreetValue = '';
    }
    $billingTelephoneValue = (is_null($result['billPhone'])) ? '' : $result['billPhone'];
    
    //Payment
    switch ($result['paymentType']) {
	case 'Visa':
	    $cc_typeValue = 'VI';
            $methodValue = 'authorizenet';
	    break;
	case 'AMEX':
	    $cc_typeValue = 'AE';
            $methodValue = 'authorizenet';
            break;
	case 'Mastercard':
	    $cc_typeValue = 'MC';
            $methodValue = 'authorizenet';
            break;
	case 'Discover':
	    $cc_typeValue = 'DI';
            $methodValue = 'authorizenet';
	    break;
	case 'Paypal':
	    $cc_typeValue = '';
            $methodValue = 'paypal_express';
	    break;
	case 'C.O.D.':
	    $cc_typeValue = '';
            $methodValue = 'free';
	    break;
	case 'GiftCert':
	    //100% payed with giftcard
	    $cc_typeValue = '';
            $methodValue = 'free';
	    break;
	default: //NULL
	    $cc_typeValue = '';
	    $methodValue = 'free';
    }
    $amount_authorizedValue = (is_null($result['finalGrandTotal'])) ? '' : $result['finalGrandTotal'];
    $amount_orderedValue = (is_null($result['finalGrandTotal'])) ? '' : $result['finalGrandTotal'];
    $base_amount_authorizedValue = (is_null($result['finalGrandTotal'])) ? '' : $result['finalGrandTotal'];
    $base_amount_orderedValue = (is_null($result['finalGrandTotal'])) ? '' : $result['finalGrandTotal'];
    $base_shipping_amountValue = (is_null($result['shippingCost'])) ? '' : $result['shippingCost'];
    $cc_approvalValue = (is_null($result['ccApprovalNumber'])) ? '' : $result['ccApprovalNumber'];
    $cc_cid_statusValue = (is_null($result['ccCvvResponse'])) ? '' : $result['ccCvvResponse'];
    $ccExpiration = (is_null($result['ccExpiration'])) ? '' : explode('/', $result['ccExpiration']);
    if (is_null($ccExpiration)) {
        $cc_exp_monthValue = '';
        $cc_exp_yearValue = '';
    } else {
        $cc_exp_monthValue = $ccExpiration[0];
        $cc_exp_yearValue = $ccExpiration[1];
    }
    $cc_last4Value = (is_null($result['ccExpiration'])) ? '' : '****';//data not available
    $anet_trans_methodValue = '';//***
    $cc_avs_statusValue = '';//***
    $cc_trans_idValue = '';//***
    $last_trans_idValue = '';//***
    $shipping_amountValue = (is_null($result['shippingCost'])) ? '' : $result['shippingCost'];

    $order = $orders->appendChild($dom->createElement('order'));

    $adjustment_negative = $order->appendChild($dom->createElement('adjustment_negative'));
    $adjustment_negative->appendChild($dom->createTextNode(''));
    $adjustment_positive = $order->appendChild($dom->createElement('adjustment_positive'));
    $adjustment_positive->appendChild($dom->createTextNode(''));
    $applied_rule_ids = $order->appendChild($dom->createElement('applied_rule_ids'));
    $applied_rule_ids->appendChild($dom->createTextNode(''));//none used -- only used for military until migration complete
    $base_adjustment_negative = $order->appendChild($dom->createElement('base_adjustment_negative'));
    $base_adjustment_negative->appendChild($dom->createTextNode(''));
    $base_adjustment_positive = $order->appendChild($dom->createElement('base_adjustment_positive'));
    $base_adjustment_positive->appendChild($dom->createTextNode(''));
    $base_currency_code = $order->appendChild($dom->createElement('base_currency_code'));
    $base_currency_code->appendChild($dom->createTextNode('USD'));// Always USD
    $base_custbalance_amount = $order->appendChild($dom->createElement('base_custbalance_amount'));
    $base_custbalance_amount->appendChild($dom->createTextNode(''));
    $base_discount_amount = $order->appendChild($dom->createElement('base_discount_amount'));
    $base_discount_amount->appendChild($dom->createTextNode($base_discount_amountValue));
    $base_discount_canceled = $order->appendChild($dom->createElement('base_discount_canceled'));
    $base_discount_canceled->appendChild($dom->createTextNode(''));
    $base_discount_invoiced = $order->appendChild($dom->createElement('base_discount_invoiced'));
    $base_discount_invoiced->appendChild($dom->createTextNode(''));
    $base_discount_refunded = $order->appendChild($dom->createElement('base_discount_refunded'));
    $base_discount_refunded->appendChild($dom->createTextNode(''));
    $base_grand_total = $order->appendChild($dom->createElement('base_grand_total'));
    $base_grand_total->appendChild($dom->createTextNode($base_grand_totalValue));
    $base_hidden_tax_amount = $order->appendChild($dom->createElement('base_hidden_tax_amount'));
    $base_hidden_tax_amount->appendChild($dom->createTextNode(''));
    $base_hidden_tax_invoiced = $order->appendChild($dom->createElement('base_hidden_tax_invoiced'));
    $base_hidden_tax_invoiced->appendChild($dom->createTextNode(''));
    $base_hidden_tax_refunded = $order->appendChild($dom->createElement('base_hidden_tax_refunded'));
    $base_hidden_tax_refunded->appendChild($dom->createTextNode(''));
    $base_shipping_amount = $order->appendChild($dom->createElement('base_shipping_amount'));
    $base_shipping_amount->appendChild($dom->createTextNode($base_shipping_amountValue));
    $base_shipping_canceled = $order->appendChild($dom->createElement('base_shipping_canceled'));
    $base_shipping_canceled->appendChild($dom->createTextNode(''));
    $base_shipping_discount_amount = $order->appendChild($dom->createElement('base_shipping_discount_amount'));
    $base_shipping_discount_amount->appendChild($dom->createTextNode('0.0000'));//Always 0
    $base_shipping_hidden_tax_amount = $order->appendChild($dom->createElement('base_shipping_hidden_tax_amount'));
    $base_shipping_hidden_tax_amount->appendChild($dom->createTextNode(''));
    $base_shipping_incl_tax = $order->appendChild($dom->createElement('base_shipping_incl_tax'));
    $base_shipping_incl_tax->appendChild($dom->createTextNode($base_shipping_incl_taxValue));
    $base_shipping_invoiced = $order->appendChild($dom->createElement('base_shipping_invoiced'));
    $base_shipping_invoiced->appendChild($dom->createTextNode(''));
    $base_shipping_refunded = $order->appendChild($dom->createElement('base_shipping_refunded'));
    $base_shipping_refunded->appendChild($dom->createTextNode(''));
    $base_shipping_tax_amount = $order->appendChild($dom->createElement('base_shipping_tax_amount'));
    $base_shipping_tax_amount->appendChild($dom->createTextNode('0.0000'));//Always 0
    $base_shipping_tax_refunded = $order->appendChild($dom->createElement('base_shipping_tax_refunded'));
    $base_shipping_tax_refunded->appendChild($dom->createTextNode(''));
    $base_subtotal = $order->appendChild($dom->createElement('base_subtotal'));
    $base_subtotal->appendChild($dom->createTextNode($base_subtotalValue));
    $base_subtotal_canceled = $order->appendChild($dom->createElement('base_subtotal_canceled'));
    $base_subtotal_canceled->appendChild($dom->createTextNode(''));
    $base_subtotal_incl_tax = $order->appendChild($dom->createElement('base_subtotal_incl_tax'));
    $base_subtotal_incl_tax->appendChild($dom->createTextNode($base_subtotal_incl_taxValue));
    $base_subtotal_invoiced = $order->appendChild($dom->createElement('base_subtotal_invoiced'));
    $base_subtotal_invoiced->appendChild($dom->createTextNode(''));
    $base_subtotal_refunded = $order->appendChild($dom->createElement('base_subtotal_refunded'));
    $base_subtotal_refunded->appendChild($dom->createTextNode(''));
    $base_tax_amount = $order->appendChild($dom->createElement('base_tax_amount'));
    $base_tax_amount->appendChild($dom->createTextNode($base_tax_amountValue));
    $base_tax_canceled = $order->appendChild($dom->createElement('base_tax_canceled'));
    $base_tax_canceled->appendChild($dom->createTextNode(''));
    $base_tax_invoiced = $order->appendChild($dom->createElement('base_tax_invoiced'));
    $base_tax_invoiced->appendChild($dom->createTextNode(''));
    $base_tax_refunded = $order->appendChild($dom->createElement('base_tax_refunded'));
    $base_tax_refunded->appendChild($dom->createTextNode(''));
    $base_to_global_rate = $order->appendChild($dom->createElement('base_to_global_rate'));
    $base_to_global_rate->appendChild($dom->createTextNode('1'));//Always 1
    $base_to_order_rate = $order->appendChild($dom->createElement('base_to_order_rate'));
    $base_to_order_rate->appendChild($dom->createTextNode('1'));//Always 1
    $base_total_canceled = $order->appendChild($dom->createElement('base_total_canceled'));
    $base_total_canceled->appendChild($dom->createTextNode('0.0000'));
    $base_total_due = $order->appendChild($dom->createElement('base_total_due'));
    $base_total_due->appendChild($dom->createTextNode($base_total_dueValue));
    $base_total_invoiced = $order->appendChild($dom->createElement('base_total_invoiced'));
    $base_total_invoiced->appendChild($dom->createTextNode('0.0000'));
    $base_total_invoiced_cost = $order->appendChild($dom->createElement('base_total_invoiced_cost'));
    $base_total_invoiced_cost->appendChild($dom->createTextNode(''));
    $base_total_offline_refunded = $order->appendChild($dom->createElement('base_total_offline_refunded'));
    $base_total_offline_refunded->appendChild($dom->createTextNode('0.0000'));
    $base_total_online_refunded = $order->appendChild($dom->createElement('base_total_online_refunded'));
    $base_total_online_refunded->appendChild($dom->createTextNode('0.0000'));
    $base_total_paid = $order->appendChild($dom->createElement('base_total_paid'));
    $base_total_paid->appendChild($dom->createTextNode('0.0000'));
    $base_total_qty_ordered = $order->appendChild($dom->createElement('base_total_qty_ordered'));
    $base_total_qty_ordered->appendChild($dom->createTextNode(''));//Always NULL
    $base_total_refunded = $order->appendChild($dom->createElement('base_total_refunded'));
    $base_total_refunded->appendChild($dom->createTextNode('0.0000'));
    $can_ship_partially = $order->appendChild($dom->createElement('can_ship_partially'));
    $can_ship_partially->appendChild($dom->createTextNode(''));
    $can_ship_partially_item = $order->appendChild($dom->createElement('can_ship_partially_item'));
    $can_ship_partially_item->appendChild($dom->createTextNode(''));
    $coupon_code = $order->appendChild($dom->createElement('coupon_code'));
    $coupon_code->appendChild($dom->createTextNode(''));
    $real_created_at = $order->appendChild($dom->createElement('real_created_at'));
    $real_created_at->appendChild($dom->createTextNode($real_created_atValue));
    $created_at_timestamp = $order->appendChild($dom->createElement('created_at_timestamp'));
    $created_at_timestamp->appendChild($dom->createTextNode($created_at_timestampValue));
    $custbalance_amount = $order->appendChild($dom->createElement('custbalance_amount'));
    $custbalance_amount->appendChild($dom->createTextNode(''));
    $customer_dob = $order->appendChild($dom->createElement('customer_dob'));
    $customer_dob->appendChild($dom->createTextNode(''));
    $customer_email = $order->appendChild($dom->createElement('customer_email'));
    $customer_email->appendChild($dom->createTextNode($customer_emailValue));
    $customer_firstname = $order->appendChild($dom->createElement('customer_firstname'));
    $customer_firstname->appendChild($dom->createTextNode($customer_firstnameValue));
    $customer_gender = $order->appendChild($dom->createElement('customer_gender'));
    $customer_gender->appendChild($dom->createTextNode(''));
    $customer_group_id = $order->appendChild($dom->createElement('customer_group_id'));
    $customer_group_id->appendChild($dom->createTextNode($customer_group_idValue));
    $customer_lastname = $order->appendChild($dom->createElement('customer_lastname'));
    $customer_lastname->appendChild($dom->createTextNode($customer_lastnameValue));
    $customer_middlename = $order->appendChild($dom->createElement('customer_middlename'));
    $customer_middlename->appendChild($dom->createTextNode(''));
    $customer_name = $order->appendChild($dom->createElement('customer_name'));
    $customer_name->appendChild($dom->createTextNode($customer_nameValue));
    $customer_note = $order->appendChild($dom->createElement('customer_note'));
    $customer_note->appendChild($dom->createTextNode(''));
    $customer_note_notify = $order->appendChild($dom->createElement('customer_note_notify'));
    $customer_note_notify->appendChild($dom->createTextNode('1'));
    $customer_prefix = $order->appendChild($dom->createElement('customer_prefix'));
    $customer_prefix->appendChild($dom->createTextNode(''));
    $customer_suffix = $order->appendChild($dom->createElement('customer_suffix'));
    $customer_suffix->appendChild($dom->createTextNode(''));
    $customer_taxvat = $order->appendChild($dom->createElement('customer_taxvat'));
    $customer_taxvat->appendChild($dom->createTextNode(''));
    $discount_amount = $order->appendChild($dom->createElement('discount_amount'));
    $discount_amount->appendChild($dom->createTextNode($discount_amountValue));
    $discount_canceled = $order->appendChild($dom->createElement('discount_canceled'));
    $discount_canceled->appendChild($dom->createTextNode(''));
    $discount_invoiced = $order->appendChild($dom->createElement('discount_invoiced'));
    $discount_invoiced->appendChild($dom->createTextNode(''));
    $discount_refunded = $order->appendChild($dom->createElement('discount_refunded'));
    $discount_refunded->appendChild($dom->createTextNode(''));
    $email_sent = $order->appendChild($dom->createElement('email_sent'));
    $email_sent->appendChild($dom->createTextNode('1'));//Always 1
    $ext_customer_id = $order->appendChild($dom->createElement('ext_customer_id'));
    $ext_customer_id->appendChild($dom->createTextNode(''));
    $ext_order_id = $order->appendChild($dom->createElement('ext_order_id'));
    $ext_order_id->appendChild($dom->createTextNode(''));
    $forced_do_shipment_with_invoice = $order->appendChild($dom->createElement('forced_do_shipment_with_invoice'));
    $forced_do_shipment_with_invoice->appendChild($dom->createTextNode(''));
    $global_currency_code = $order->appendChild($dom->createElement('global_currency_code'));
    $global_currency_code->appendChild($dom->createTextNode('USD'));
    $grand_total = $order->appendChild($dom->createElement('grand_total'));
    $grand_total->appendChild($dom->createTextNode($grand_totalValue));
    $hidden_tax_amount = $order->appendChild($dom->createElement('hidden_tax_amount'));
    $hidden_tax_amount->appendChild($dom->createTextNode(''));
    $hidden_tax_invoiced = $order->appendChild($dom->createElement('hidden_tax_invoiced'));
    $hidden_tax_invoiced->appendChild($dom->createTextNode(''));
    $hidden_tax_refunded = $order->appendChild($dom->createElement('hidden_tax_refunded'));
    $hidden_tax_refunded->appendChild($dom->createTextNode(''));
    $hold_before_state = $order->appendChild($dom->createElement('hold_before_state'));
    $hold_before_state->appendChild($dom->createTextNode(''));
    $hold_before_status = $order->appendChild($dom->createElement('hold_before_status'));
    $hold_before_status->appendChild($dom->createTextNode(''));
    $increment_id = $order->appendChild($dom->createElement('increment_id'));
    $increment_id->appendChild($dom->createTextNode($increment_idValue));
    $is_hold = $order->appendChild($dom->createElement('is_hold'));
    $is_hold->appendChild($dom->createTextNode(''));
    $is_multi_payment = $order->appendChild($dom->createElement('is_multi_payment'));
    $is_multi_payment->appendChild($dom->createTextNode(''));
    $is_virtual = $order->appendChild($dom->createElement('is_virtual'));
    $is_virtual->appendChild($dom->createTextNode('0'));//Always 0
    $order_currency_code = $order->appendChild($dom->createElement('order_currency_code'));
    $order_currency_code->appendChild($dom->createTextNode('USD'));
    $payment_authorization_amount = $order->appendChild($dom->createElement('payment_authorization_amount'));
    $payment_authorization_amount->appendChild($dom->createTextNode(''));
    $payment_authorization_expiration = $order->appendChild($dom->createElement('payment_authorization_expiration'));
    $payment_authorization_expiration->appendChild($dom->createTextNode(''));
    $paypal_ipn_customer_notified = $order->appendChild($dom->createElement('paypal_ipn_customer_notified'));
    $paypal_ipn_customer_notified->appendChild($dom->createTextNode(''));
    $real_order_id = $order->appendChild($dom->createElement('real_order_id'));
    $real_order_id->appendChild($dom->createTextNode(''));
    $remote_ip = $order->appendChild($dom->createElement('remote_ip'));
    $remote_ip->appendChild($dom->createTextNode(''));
    $shipping_amount = $order->appendChild($dom->createElement('shipping_amount'));
    $shipping_amount->appendChild($dom->createTextNode($shipping_amountValue));
    $shipping_canceled = $order->appendChild($dom->createElement('shipping_canceled'));
    $shipping_canceled->appendChild($dom->createTextNode(''));
    $shipping_discount_amount = $order->appendChild($dom->createElement('shipping_discount_amount'));
    $shipping_discount_amount->appendChild($dom->createTextNode('0.0000'));
    $shipping_hidden_tax_amount = $order->appendChild($dom->createElement('shipping_hidden_tax_amount'));
    $shipping_hidden_tax_amount->appendChild($dom->createTextNode(''));
    $shipping_incl_tax = $order->appendChild($dom->createElement('shipping_incl_tax'));
    $shipping_incl_tax->appendChild($dom->createTextNode($shipping_incl_taxValue));
    $shipping_invoiced = $order->appendChild($dom->createElement('shipping_invoiced'));
    $shipping_invoiced->appendChild($dom->createTextNode(''));
    $shipping_method = $order->appendChild($dom->createElement('shipping_method'));
    $shipping_method->appendChild($dom->createTextNode($shipping_methodValue));
    $shipping_refunded = $order->appendChild($dom->createElement('shipping_refunded'));
    $shipping_refunded->appendChild($dom->createTextNode(''));
    $shipping_tax_amount = $order->appendChild($dom->createElement('shipping_tax_amount'));
    $shipping_tax_amount->appendChild($dom->createTextNode('0.0000'));
    $shipping_tax_refunded = $order->appendChild($dom->createElement('shipping_tax_refunded'));
    $shipping_tax_refunded->appendChild($dom->createTextNode(''));
    $state = $order->appendChild($dom->createElement('state'));
    $state->appendChild($dom->createTextNode($stateValue));
    $status = $order->appendChild($dom->createElement('status'));
    $status->appendChild($dom->createTextNode($statusValue));
    $store = $order->appendChild($dom->createElement('store'));
    $store->appendChild($dom->createTextNode('sneakerhead_cn'));
    $subtotal = $order->appendChild($dom->createElement('subTotal'));
    $subtotal->appendChild($dom->createTextNode($subtotalValue));
    $subtotal_canceled = $order->appendChild($dom->createElement('subtotal_canceled'));
    $subtotal_canceled->appendChild($dom->createTextNode(''));
    $subtotal_incl_tax = $order->appendChild($dom->createElement('subtotal_incl_tax'));
    $subtotal_incl_tax->appendChild($dom->createTextNode($subtotal_incl_taxValue));
    $subtotal_invoiced = $order->appendChild($dom->createElement('subtotal_invoiced'));
    $subtotal_invoiced->appendChild($dom->createTextNode(''));
    $subtotal_refunded = $order->appendChild($dom->createElement('subtotal_refunded'));
    $subtotal_refunded->appendChild($dom->createTextNode(''));
    $tax_amount = $order->appendChild($dom->createElement('tax_amount'));
    $tax_amount->appendChild($dom->createTextNode($tax_amountValue));
    $tax_canceled = $order->appendChild($dom->createElement('tax_canceled'));
    $tax_canceled->appendChild($dom->createTextNode(''));
    $tax_invoiced = $order->appendChild($dom->createElement('tax_invoiced'));
    $tax_invoiced->appendChild($dom->createTextNode(''));
    $tax_percent = $order->appendChild($dom->createElement('tax_percent'));
    $tax_percent->appendChild($dom->createTextNode($tax_percentValue));
    $tax_refunded = $order->appendChild($dom->createElement('tax_refunded'));
    $tax_refunded->appendChild($dom->createTextNode(''));
    $total_canceled = $order->appendChild($dom->createElement('total_canceled'));
    $total_canceled->appendChild($dom->createTextNode('0.0000'));
    $total_due = $order->appendChild($dom->createElement('total_due'));
    $total_due->appendChild($dom->createTextNode($total_dueValue));
    $total_invoiced = $order->appendChild($dom->createElement('total_invoiced'));
    $total_invoiced->appendChild($dom->createTextNode('0.0000'));
    $total_item_count = $order->appendChild($dom->createElement('total_item_count'));
    $total_item_count->appendChild($dom->createTextNode(''));
    $total_offline_refunded = $order->appendChild($dom->createElement('total_offline_refunded'));
    $total_offline_refunded->appendChild($dom->createTextNode('0.0000'));
    $total_online_refunded = $order->appendChild($dom->createElement('total_online_refunded'));
    $total_online_refunded->appendChild($dom->createTextNode('0.0000'));
    $total_paid = $order->appendChild($dom->createElement('total_paid'));
    $total_paid->appendChild($dom->createTextNode('0.0000'));
    $total_qty_ordered = $order->appendChild($dom->createElement('total_qty_ordered'));
    $total_qty_ordered->appendChild($dom->createTextNode($total_qty_orderedValue));
    $total_refunded = $order->appendChild($dom->createElement('total_refunded'));
    $total_refunded->appendChild($dom->createTextNode('0.0000'));
    $tracking_numbers = $order->appendChild($dom->createElement('tracking_numbers'));
    $tracking_numbers->appendChild($dom->createTextNode(''));
    $updated_at = $order->appendChild($dom->createElement('updated_at'));
    $updated_at->appendChild($dom->createTextNode($updated_atValue));
    $updated_at_timestamp = $order->appendChild($dom->createElement('updated_at_timestamp'));
    $updated_at_timestamp->appendChild($dom->createTextNode($updated_at_timestampValue));
    $weight = $order->appendChild($dom->createElement('weight'));
    $weight->appendChild($dom->createTextNode($weightValue));
    $x_forwarded_for = $order->appendChild($dom->createElement('x_forwarded_for'));
    $x_forwarded_for->appendChild($dom->createTextNode(''));

    //Build shipping
    $shipping_address = $order->appendChild($dom->createElement('shipping_address'));
    
    $shippingCity = $shipping_address->appendChild($dom->createElement('city'));
    $shippingCity->appendChild($dom->createTextNode($shippingCityValue));
    $shippingCompany = $shipping_address->appendChild($dom->createElement('company'));
    $shippingCompany->appendChild($dom->createTextNode(''));
    $shippingCountry = $shipping_address->appendChild($dom->createElement('country'));
    $shippingCountry->appendChild($dom->createTextNode($shippingCountryValue));
    $shippingCountry_id = $shipping_address->appendChild($dom->createElement('country_id'));
    $shippingCountry_id->appendChild($dom->createTextNode(''));
    $shippingCountry_iso2 = $shipping_address->appendChild($dom->createElement('country_iso2'));
    $shippingCountry_iso2->appendChild($dom->createTextNode(''));
    $shippingCountry_iso3 = $shipping_address->appendChild($dom->createElement('country_iso3'));
    $shippingCountry_iso3->appendChild($dom->createTextNode(''));
    $shippingEmail = $shipping_address->appendChild($dom->createElement('email'));
    $shippingEmail->appendChild($dom->createTextNode($shippingEmailValue));
    $shippingFax = $shipping_address->appendChild($dom->createElement('fax'));
    $shippingFax->appendChild($dom->createTextNode(''));
    $shippingFirstname = $shipping_address->appendChild($dom->createElement('firstname'));
    $shippingFirstname->appendChild($dom->createTextNode($shippingFirstnameValue));
    $shippingLastname = $shipping_address->appendChild($dom->createElement('lastname'));
    $shippingLastname->appendChild($dom->createTextNode($shippingLastnameValue));
    $shippingMiddlename = $shipping_address->appendChild($dom->createElement('middlename'));
    $shippingMiddlename->appendChild($dom->createTextNode(''));
    $shippingName = $shipping_address->appendChild($dom->createElement('name'));
    $shippingName->appendChild($dom->createTextNode($shippingNameValue));
    $shippingPostcode = $shipping_address->appendChild($dom->createElement('postcode'));
    $shippingPostcode->appendChild($dom->createTextNode($shippingPostcodeValue));
    $shippingPrefix = $shipping_address->appendChild($dom->createElement('prefix'));
    $shippingPrefix->appendChild($dom->createTextNode(''));
    $shippingRegion = $shipping_address->appendChild($dom->createElement('region'));
    $shippingRegion->appendChild($dom->createTextNode($shippingRegionValue));
    $shippingRegion_id = $shipping_address->appendChild($dom->createElement('region_id'));
    $shippingRegion_id->appendChild($dom->createTextNode($shippingRegion_idValue));
    $shippingRegion_iso2 = $shipping_address->appendChild($dom->createElement('region_iso2'));
    $shippingRegion_iso2->appendChild($dom->createTextNode(''));
    $shippingStreet = $shipping_address->appendChild($dom->createElement('street'));
    $shippingStreet->appendChild($dom->createTextNode($shippingStreetValue));
    $shippingSuffix = $shipping_address->appendChild($dom->createElement('suffix'));
    $shippingSuffix->appendChild($dom->createTextNode(''));
    $shippingTelephone = $shipping_address->appendChild($dom->createElement('telephone'));
    $shippingTelephone->appendChild($dom->createTextNode($shippingTelephoneValue));

    // Build billing
    $billing_address = $order->appendChild($dom->createElement('billing_address'));
    
    $billingCity = $billing_address->appendChild($dom->createElement('city'));
    $billingCity->appendChild($dom->createTextNode($billingCityValue));
    $billingCompany = $billing_address->appendChild($dom->createElement('company'));
    $billingCompany->appendChild($dom->createTextNode(''));
    $billingCountry = $billing_address->appendChild($dom->createElement('country'));
    $billingCountry->appendChild($dom->createTextNode($billingCountryValue));
    $billingCountry_id = $billing_address->appendChild($dom->createElement('country_id'));
    $billingCountry_id->appendChild($dom->createTextNode(''));
    $billingCountry_iso2 = $billing_address->appendChild($dom->createElement('country_iso2'));
    $billingCountry_iso2->appendChild($dom->createTextNode(''));
    $billingCountry_iso3 = $billing_address->appendChild($dom->createElement('country_iso3'));
    $billingCountry_iso3->appendChild($dom->createTextNode(''));
    $billingEmail = $billing_address->appendChild($dom->createElement('email'));
    $billingEmail->appendChild($dom->createTextNode($billingEmailValue));
    $billingFax = $billing_address->appendChild($dom->createElement('fax'));
    $billingFax->appendChild($dom->createTextNode(''));
    $billingFirstname = $billing_address->appendChild($dom->createElement('firstname'));
    $billingFirstname->appendChild($dom->createTextNode($billingFirstnameValue));
    $billingLastname = $billing_address->appendChild($dom->createElement('lastname'));
    $billingLastname->appendChild($dom->createTextNode($billingLastnameValue));
    $billingMiddlename = $billing_address->appendChild($dom->createElement('middlename'));
    $billingMiddlename->appendChild($dom->createTextNode(''));
    $billingName = $billing_address->appendChild($dom->createElement('name'));
    $billingName->appendChild($dom->createTextNode($billingNameValue));
    $billingPostcode = $billing_address->appendChild($dom->createElement('postcode'));
    $billingPostcode->appendChild($dom->createTextNode($billingPostcodeValue));
    $billingPrefix = $billing_address->appendChild($dom->createElement('prefix'));
    $billingPrefix->appendChild($dom->createTextNode(''));
    $billingRegion = $billing_address->appendChild($dom->createElement('region'));
    $billingRegion->appendChild($dom->createTextNode($billingRegionValue));
    $billingRegion_id = $billing_address->appendChild($dom->createElement('region_id'));
    $billingRegion_id->appendChild($dom->createTextNode($billingRegion_idValue));
    $billingRegion_iso2 = $billing_address->appendChild($dom->createElement('region_iso2'));
    $billingRegion_iso2->appendChild($dom->createTextNode(''));
    $billingStreet = $billing_address->appendChild($dom->createElement('street'));
    $billingStreet->appendChild($dom->createTextNode($billingStreetValue));
    $billingSuffix = $billing_address->appendChild($dom->createElement('suffix'));
    $billingSuffix->appendChild($dom->createTextNode(''));
    $billingTelephone = $billing_address->appendChild($dom->createElement('telephone'));
    $billingTelephone->appendChild($dom->createTextNode($billingTelephoneValue));
    
    // Build payment

    $payment = $order->appendChild($dom->createElement('payment'));

    $account_status = $payment->appendChild($dom->createElement('account_status'));
    $account_status->appendChild($dom->createTextNode(''));
    $address_status = $payment->appendChild($dom->createElement('address_status'));
    $address_status->appendChild($dom->createTextNode(''));
    $amount = $payment->appendChild($dom->createElement('amount'));
    $amount->appendChild($dom->createTextNode(''));
    $amount_authorized = $payment->appendChild($dom->createElement('amount_authorized'));
    $amount_authorized->appendChild($dom->createTextNode($amount_authorizedValue));
    $amount_canceled = $payment->appendChild($dom->createElement('amount_canceled'));
    $amount_canceled->appendChild($dom->createTextNode(''));
    $amount_ordered = $payment->appendChild($dom->createElement('amount_ordered'));
    $amount_ordered->appendChild($dom->createTextNode($amount_orderedValue));
    $amount_paid = $payment->appendChild($dom->createElement('amount_paid'));
    $amount_paid->appendChild($dom->createTextNode(''));
    $amount_refunded = $payment->appendChild($dom->createElement('amount_refunded'));
    $amount_refunded->appendChild($dom->createTextNode(''));
    $anet_trans_method = $payment->appendChild($dom->createElement('anet_trans_method'));
    $anet_trans_method->appendChild($dom->createTextNode($anet_trans_methodValue));
    $base_amount_authorized = $payment->appendChild($dom->createElement('base_amount_authorized'));
    $base_amount_authorized->appendChild($dom->createTextNode($base_amount_authorizedValue));
    $base_amount_canceled = $payment->appendChild($dom->createElement('base_amount_canceled'));
    $base_amount_canceled->appendChild($dom->createTextNode(''));
    $base_amount_ordered = $payment->appendChild($dom->createElement('base_amount_ordered'));
    $base_amount_ordered->appendChild($dom->createTextNode($base_amount_orderedValue));
    $base_amount_paid = $payment->appendChild($dom->createElement('base_amount_paid'));
    $base_amount_paid->appendChild($dom->createTextNode(''));
    $base_amount_paid_online = $payment->appendChild($dom->createElement('base_amount_paid_online'));
    $base_amount_paid_online->appendChild($dom->createTextNode(''));
    $base_amount_refunded = $payment->appendChild($dom->createElement('base_amount_refunded'));
    $base_amount_refunded->appendChild($dom->createTextNode(''));
    $base_amount_refunded_online = $payment->appendChild($dom->createElement('base_amount_refunded_online'));
    $base_amount_refunded_online->appendChild($dom->createTextNode(''));
    $base_shipping_amount = $payment->appendChild($dom->createElement('base_shipping_amount'));
    $base_shipping_amount->appendChild($dom->createTextNode($base_shipping_amountValue));
    $base_shipping_captured = $payment->appendChild($dom->createElement('base_shipping_captured'));
    $base_shipping_captured->appendChild($dom->createTextNode(''));
    $base_shipping_refunded = $payment->appendChild($dom->createElement('base_shipping_refunded'));
    $base_shipping_refunded->appendChild($dom->createTextNode(''));
    $cc_approval = $payment->appendChild($dom->createElement('cc_approval'));
    $cc_approval->appendChild($dom->createTextNode($cc_approvalValue));
    $cc_avs_status = $payment->appendChild($dom->createElement('cc_avs_status'));
    $cc_avs_status->appendChild($dom->createTextNode($cc_avs_statusValue));
    $cc_cid_status = $payment->appendChild($dom->createElement('cc_cid_status'));
    $cc_cid_status->appendChild($dom->createTextNode($cc_cid_statusValue));
    $cc_debug_request_body = $payment->appendChild($dom->createElement('cc_debug_request_body'));
    $cc_debug_request_body->appendChild($dom->createTextNode(''));
    $cc_debug_response_body = $payment->appendChild($dom->createElement('cc_debug_response_body'));
    $cc_debug_response_body->appendChild($dom->createTextNode(''));
    $cc_debug_response_serialized = $payment->appendChild($dom->createElement('cc_debug_response_serialized'));
    $cc_debug_response_serialized->appendChild($dom->createTextNode(''));
    $cc_exp_month = $payment->appendChild($dom->createElement('cc_exp_month'));
    $cc_exp_month->appendChild($dom->createTextNode($cc_exp_monthValue));
    $cc_exp_year = $payment->appendChild($dom->createElement('cc_exp_year'));
    $cc_exp_year->appendChild($dom->createTextNode($cc_exp_yearValue));
    $cc_last4 = $payment->appendChild($dom->createElement('cc_last4'));
    $cc_last4->appendChild($dom->createTextNode($cc_last4Value));
    $cc_number_enc = $payment->appendChild($dom->createElement('cc_number_enc'));
    $cc_number_enc->appendChild($dom->createTextNode(''));
    $cc_owner = $payment->appendChild($dom->createElement('cc_owner'));
    $cc_owner->appendChild($dom->createTextNode(''));
    $cc_raw_request = $payment->appendChild($dom->createElement('cc_raw_request'));
    $cc_raw_request->appendChild($dom->createTextNode(''));
    $cc_raw_response = $payment->appendChild($dom->createElement('cc_raw_response'));
    $cc_raw_response->appendChild($dom->createTextNode(''));
    $cc_secure_verify = $payment->appendChild($dom->createElement('cc_secure_verify'));
    $cc_secure_verify->appendChild($dom->createTextNode(''));
    $cc_ss_issue = $payment->appendChild($dom->createElement('cc_ss_issue'));
    $cc_ss_issue->appendChild($dom->createTextNode(''));
    $cc_ss_start_month = $payment->appendChild($dom->createElement('cc_ss_start_month'));
    $cc_ss_start_month->appendChild($dom->createTextNode('0'));//appears to be 0 since not used
    $cc_ss_start_year = $payment->appendChild($dom->createElement('cc_ss_start_year'));
    $cc_ss_start_year->appendChild($dom->createTextNode('0'));//appears to be 0 since not used
    $cc_status = $payment->appendChild($dom->createElement('cc_status'));
    $cc_status->appendChild($dom->createTextNode(''));
    $cc_status_description = $payment->appendChild($dom->createElement('cc_status_description'));
    $cc_status_description->appendChild($dom->createTextNode(''));
    $cc_trans_id = $payment->appendChild($dom->createElement('cc_trans_id'));
    $cc_trans_id->appendChild($dom->createTextNode($cc_trans_idValue));
    $cc_type = $payment->appendChild($dom->createElement('cc_type'));
    $cc_type->appendChild($dom->createTextNode($cc_typeValue));
    $cybersource_token = $payment->appendChild($dom->createElement('cybersource_token'));
    $cybersource_token->appendChild($dom->createTextNode(''));
    $echeck_account_name = $payment->appendChild($dom->createElement('echeck_account_name'));
    $echeck_account_name->appendChild($dom->createTextNode(''));
    $echeck_account_type = $payment->appendChild($dom->createElement('echeck_account_type'));
    $echeck_account_type->appendChild($dom->createTextNode(''));
    $echeck_bank_name = $payment->appendChild($dom->createElement('echeck_bank_name'));
    $echeck_bank_name->appendChild($dom->createTextNode(''));
    $echeck_routing_number = $payment->appendChild($dom->createElement('echeck_routing_number'));
    $echeck_routing_number->appendChild($dom->createTextNode(''));
    $echeck_type = $payment->appendChild($dom->createElement('echeck_type'));
    $echeck_type->appendChild($dom->createTextNode(''));
    $flo2cash_account_id = $payment->appendChild($dom->createElement('flo2cash_account_id'));
    $flo2cash_account_id->appendChild($dom->createTextNode(''));
    $ideal_issuer_id = $payment->appendChild($dom->createElement('ideal_issuer_id'));
    $ideal_issuer_id->appendChild($dom->createTextNode(''));
    $ideal_issuer_title = $payment->appendChild($dom->createElement('ideal_issuer_title'));
    $ideal_issuer_title->appendChild($dom->createTextNode(''));
    $ideal_transaction_checked = $payment->appendChild($dom->createElement('ideal_transaction_checked'));
    $ideal_transaction_checked->appendChild($dom->createTextNode(''));
    $last_trans_id = $payment->appendChild($dom->createElement('last_trans_id'));
    $last_trans_id->appendChild($dom->createTextNode($last_trans_idValue));
    $method = $payment->appendChild($dom->createElement('method'));
    $method->appendChild($dom->createTextNode($methodValue));
    $paybox_question_number = $payment->appendChild($dom->createElement('paybox_question_number'));
    $paybox_question_number->appendChild($dom->createTextNode(''));
    $paybox_request_number = $payment->appendChild($dom->createElement('paybox_request_number'));
    $paybox_request_number->appendChild($dom->createTextNode(''));
    $po_number = $payment->appendChild($dom->createElement('po_number'));
    $po_number->appendChild($dom->createTextNode(''));
    $protection_eligibility = $payment->appendChild($dom->createElement('protection_eligibility'));
    $protection_eligibility->appendChild($dom->createTextNode(''));
    $shipping_amount = $payment->appendChild($dom->createElement('shipping_amount'));
    $shipping_amount->appendChild($dom->createTextNode($shipping_amountValue));
    $shipping_captured = $payment->appendChild($dom->createElement('shipping_captured'));
    $shipping_captured->appendChild($dom->createTextNode(''));
    $shipping_refunded = $payment->appendChild($dom->createElement('shipping_refunded'));
    $shipping_refunded->appendChild($dom->createTextNode(''));

    // Build Items
    $items = $order->appendChild($dom->createElement('items'));
    $itemsQuery = "SELECT * FROM `se_orderitem` WHERE `yahooOrderIdNumeric` = " . $result['yahooOrderIdNumeric'];
    $itemsResult = $writeConnection->query($itemsQuery);
    $itemNumber = 1;
    foreach ($itemsResult as $itemResult) {
        $item = $items->appendChild($dom->createElement('item'));

	//Set variables
	$base_original_priceValue = $itemResult['unitPrice'];
	$base_priceValue = $itemResult['unitPrice'];
	$base_row_totalValue = $itemResult['qtyOrdered'] * $itemResult['unitPrice'];
	$real_nameValue = $itemResult['lineItemDescription'];
	$nameValue = $itemResult['lineItemDescription'];
	$original_priceValue = $itemResult['unitPrice'];
	$priceValue = $itemResult['unitPrice'];
	$qty_orderedValue = $itemResult['qtyOrdered'];
	$row_totalValue = $itemResult['qtyOrdered'] * $itemResult['unitPrice'];
	$length = strlen(end(explode('-', $itemResult['productCode'])));
	$real_skuValue = substr($itemResult['productCode'], 0, -($length + 1));
        
	fwrite($transactionLogHandle, "      ->ADDING CONFIGURABLE : " . $itemNumber . " -> " . $real_skuValue . "\n");

	$skuValue = 'Product ' . $itemNumber;
	if (!is_null($result['shipCountry']) && $result['shipState'] == 'CA') {
	    if (strtolower($result['shipCountry']) == 'united states') {
		$tax_percentCalcValue = '0.0875';
		$tax_percentValue = '8.75';
		$base_price_incl_taxValue = round($priceValue + ($priceValue * $tax_percentCalcValue), 4);//
		$base_row_total_incl_taxValue = round($qty_orderedValue * ($priceValue + ($priceValue * $tax_percentCalcValue)), 4);//
		$base_tax_amountValue = round($priceValue * $tax_percentCalcValue, 4);//THIS MAY BE WRONG -- QTY or ONE
		$price_incl_taxValue = round($priceValue + ($priceValue * $tax_percentCalcValue), 4);//
		$row_total_incl_taxValue = round($qty_orderedValue * ($priceValue + ($priceValue * $tax_percentCalcValue)), 4);//
		$tax_amountValue = round($priceValue * $tax_percentCalcValue, 4);//
	    } else {
		$tax_percentValue = '0.00';
		$base_price_incl_taxValue = $priceValue;
		$base_row_total_incl_taxValue = $qty_orderedValue * $priceValue;
		$base_tax_amountValue = '0.00';
		$price_incl_taxValue = $priceValue;
		$row_total_incl_taxValue = $qty_orderedValue * $priceValue;
		$tax_amountValue = '0.00';		
	    }
	} else {
	    $tax_percentValue = '0.00';
	    $base_price_incl_taxValue = $priceValue;
	    $base_row_total_incl_taxValue = $qty_orderedValue * $priceValue;
	    $base_tax_amountValue = '0.00';
	    $price_incl_taxValue = $priceValue;
	    $row_total_incl_taxValue = $qty_orderedValue * $priceValue;
	    $tax_amountValue = '0.00';	
	}

	//Create line item
	$amount_refunded = $item->appendChild($dom->createElement('amount_refunded'));
	$amount_refunded->appendChild($dom->createTextNode('0')); //Always 0
	$applied_rule_ids = $item->appendChild($dom->createElement('applied_rule_ids'));
	$applied_rule_ids->appendChild($dom->createTextNode(''));
	$base_amount_refunded = $item->appendChild($dom->createElement('base_amount_refunded'));
	$base_amount_refunded->appendChild($dom->createTextNode('0')); //Always 0
	$base_cost = $item->appendChild($dom->createElement('base_cost'));
	$base_cost->appendChild($dom->createTextNode(''));
	$base_discount_amount = $item->appendChild($dom->createElement('base_discount_amount'));
	$base_discount_amount->appendChild($dom->createTextNode('0')); //Always 0
	$base_discount_invoiced = $item->appendChild($dom->createElement('base_discount_invoiced'));
	$base_discount_invoiced->appendChild($dom->createTextNode('0')); //Always 0
	$base_hidden_tax_amount = $item->appendChild($dom->createElement('base_hidden_tax_amount'));
	$base_hidden_tax_amount->appendChild($dom->createTextNode(''));
	$base_hidden_tax_invoiced = $item->appendChild($dom->createElement('base_hidden_tax_invoiced'));
	$base_hidden_tax_invoiced->appendChild($dom->createTextNode(''));
	$base_hidden_tax_refunded = $item->appendChild($dom->createElement('base_hidden_tax_refunded'));
	$base_hidden_tax_refunded->appendChild($dom->createTextNode(''));
	$base_original_price = $item->appendChild($dom->createElement('base_original_price'));
	$base_original_price->appendChild($dom->createTextNode($base_original_priceValue));
	$base_price = $item->appendChild($dom->createElement('base_price'));
	$base_price->appendChild($dom->createTextNode($base_priceValue));
	$base_price_incl_tax = $item->appendChild($dom->createElement('base_price_incl_tax'));
	$base_price_incl_tax->appendChild($dom->createTextNode($base_price_incl_taxValue));
	$base_row_invoiced = $item->appendChild($dom->createElement('base_row_invoiced'));
	$base_row_invoiced->appendChild($dom->createTextNode('0'));
	$base_row_total = $item->appendChild($dom->createElement('base_row_total'));
	$base_row_total->appendChild($dom->createTextNode($base_row_totalValue));
	$base_row_total_incl_tax = $item->appendChild($dom->createElement('base_row_total_incl_tax'));
	$base_row_total_incl_tax->appendChild($dom->createTextNode($base_row_total_incl_taxValue));
	$base_tax_amount = $item->appendChild($dom->createElement('base_tax_amount'));
	$base_tax_amount->appendChild($dom->createTextNode($base_tax_amountValue));
	$base_tax_before_discount = $item->appendChild($dom->createElement('base_tax_before_discount'));
	$base_tax_before_discount->appendChild($dom->createTextNode(''));
	$base_tax_invoiced = $item->appendChild($dom->createElement('base_tax_invoiced'));
	$base_tax_invoiced->appendChild($dom->createTextNode('0')); //Always 0
	$base_weee_tax_applied_amount = $item->appendChild($dom->createElement('base_weee_tax_applied_amount'));
	$base_weee_tax_applied_amount->appendChild($dom->createTextNode('0')); //Always 0
	$base_weee_tax_applied_row_amount = $item->appendChild($dom->createElement('base_weee_tax_applied_row_amount'));
	$base_weee_tax_applied_row_amount->appendChild($dom->createTextNode('0')); //Always 0
	$base_weee_tax_disposition = $item->appendChild($dom->createElement('base_weee_tax_disposition'));
	$base_weee_tax_disposition->appendChild($dom->createTextNode('0')); //Always 0
	$base_weee_tax_row_disposition = $item->appendChild($dom->createElement('base_weee_tax_row_disposition'));
	$base_weee_tax_row_disposition->appendChild($dom->createTextNode('0')); //Always 0
	$description = $item->appendChild($dom->createElement('description'));
	$description->appendChild($dom->createTextNode(''));
	$discount_amount = $item->appendChild($dom->createElement('discount_amount'));
	$discount_amount->appendChild($dom->createTextNode('0')); //Always 0
	$discount_invoiced = $item->appendChild($dom->createElement('discount_invoiced'));
	$discount_invoiced->appendChild($dom->createTextNode('0')); //Always 0
	$discount_percent = $item->appendChild($dom->createElement('discount_percent'));
	$discount_percent->appendChild($dom->createTextNode('0')); //Always 0
	$free_shipping = $item->appendChild($dom->createElement('free_shipping'));
	$free_shipping->appendChild($dom->createTextNode('0')); //Always 0
	$hidden_tax_amount = $item->appendChild($dom->createElement('hidden_tax_amount'));
	$hidden_tax_amount->appendChild($dom->createTextNode(''));
	$hidden_tax_canceled = $item->appendChild($dom->createElement('hidden_tax_canceled'));
	$hidden_tax_canceled->appendChild($dom->createTextNode(''));
	$hidden_tax_invoiced = $item->appendChild($dom->createElement('hidden_tax_invoiced'));
	$hidden_tax_invoiced->appendChild($dom->createTextNode(''));
	$hidden_tax_refunded = $item->appendChild($dom->createElement('hidden_tax_refunded'));
	$hidden_tax_refunded->appendChild($dom->createTextNode(''));
	$is_nominal = $item->appendChild($dom->createElement('is_nominal'));
	$is_nominal->appendChild($dom->createTextNode('0')); //Always 0
	$is_qty_decimal = $item->appendChild($dom->createElement('is_qty_decimal'));
	$is_qty_decimal->appendChild($dom->createTextNode('0')); //Always 0
	$is_virtual = $item->appendChild($dom->createElement('is_virtual'));
	$is_virtual->appendChild($dom->createTextNode('0')); //Always 0
	$real_name = $item->appendChild($dom->createElement('real_name'));
	$real_name->appendChild($dom->createTextNode($real_nameValue)); //Always 0
	$name = $item->appendChild($dom->createElement('name'));
	$name->appendChild($dom->createTextNode($nameValue)); //Always 0
	$no_discount = $item->appendChild($dom->createElement('no_discount'));
	$no_discount->appendChild($dom->createTextNode('0')); //Always 0
	$original_price = $item->appendChild($dom->createElement('original_price'));
	$original_price->appendChild($dom->createTextNode($original_priceValue));
	$price = $item->appendChild($dom->createElement('price'));
	$price->appendChild($dom->createTextNode($priceValue));
	$price_incl_tax = $item->appendChild($dom->createElement('price_incl_tax'));
	$price_incl_tax->appendChild($dom->createTextNode($price_incl_taxValue));
	$qty_backordered = $item->appendChild($dom->createElement('qty_backordered'));
	$qty_backordered->appendChild($dom->createTextNode(''));
	$qty_canceled = $item->appendChild($dom->createElement('qty_canceled'));
	$qty_canceled->appendChild($dom->createTextNode('0')); //Always 0
	$qty_invoiced = $item->appendChild($dom->createElement('qty_invoiced'));
	$qty_invoiced->appendChild($dom->createTextNode('0')); //Always 0
	$qty_ordered = $item->appendChild($dom->createElement('qty_ordered'));
	$qty_ordered->appendChild($dom->createTextNode($qty_orderedValue)); //Always 0
	$qty_refunded = $item->appendChild($dom->createElement('qty_refunded'));
	$qty_refunded->appendChild($dom->createTextNode('0')); //Always 0
	$qty_shipped = $item->appendChild($dom->createElement('qty_shipped'));
	$qty_shipped->appendChild($dom->createTextNode('0')); //Always 0
	$row_invoiced = $item->appendChild($dom->createElement('row_invoiced'));
	$row_invoiced->appendChild($dom->createTextNode('0')); //Always 0
	$row_total = $item->appendChild($dom->createElement('row_total'));
	$row_total->appendChild($dom->createTextNode($row_totalValue));
	$row_total_incl_tax = $item->appendChild($dom->createElement('row_total_incl_tax'));
	$row_total_incl_tax->appendChild($dom->createTextNode($row_total_incl_taxValue));
	$row_weight = $item->appendChild($dom->createElement('row_weight'));
	$row_weight->appendChild($dom->createTextNode('0'));
	$real_sku = $item->appendChild($dom->createElement('real_sku'));
	$real_sku->appendChild($dom->createTextNode($real_skuValue));
	$sku = $item->appendChild($dom->createElement('sku'));
	$sku->appendChild($dom->createTextNode($skuValue));
	$tax_amount = $item->appendChild($dom->createElement('tax_amount'));
	$tax_amount->appendChild($dom->createTextNode($tax_amountValue));
	$tax_before_discount = $item->appendChild($dom->createElement('tax_before_discount'));
	$tax_before_discount->appendChild($dom->createTextNode(''));
	$tax_canceled = $item->appendChild($dom->createElement('tax_canceled'));
	$tax_canceled->appendChild($dom->createTextNode(''));
	$tax_invoiced = $item->appendChild($dom->createElement('tax_invoiced'));
	$tax_invoiced->appendChild($dom->createTextNode('0'));
	$tax_percent = $item->appendChild($dom->createElement('tax_percent'));
	$tax_percent->appendChild($dom->createTextNode($tax_percentValue));
	$tax_refunded = $item->appendChild($dom->createElement('tax_refunded'));
	$tax_refunded->appendChild($dom->createTextNode(''));
	$weee_tax_applied = $item->appendChild($dom->createElement('weee_tax_applied'));
	$weee_tax_applied->appendChild($dom->createTextNode('a:0:{}')); //Always 0
	$weee_tax_applied_amount = $item->appendChild($dom->createElement('weee_tax_applied_amount'));
	$weee_tax_applied_amount->appendChild($dom->createTextNode('0')); //Always 0
	$weee_tax_applied_row_amount = $item->appendChild($dom->createElement('weee_tax_applied_row_amount'));
	$weee_tax_applied_row_amount->appendChild($dom->createTextNode('0')); //Always 0
	$weee_tax_disposition = $item->appendChild($dom->createElement('weee_tax_disposition'));
	$weee_tax_disposition->appendChild($dom->createTextNode('0')); //Always 0
	$weee_tax_row_disposition = $item->appendChild($dom->createElement('weee_tax_row_disposition'));
	$weee_tax_row_disposition->appendChild($dom->createTextNode('0')); //Always 0
	$weight = $item->appendChild($dom->createElement('weight'));
	$weight->appendChild($dom->createTextNode('0'));

	//Add simple
	$item = $items->appendChild($dom->createElement('item'));
	
	//Set variables
	$base_original_priceValue = '0.0000';
	$base_priceValue = '0.0000';
	$base_row_totalValue = '0.0000';
	$real_nameValue = $itemResult['lineItemDescription'];
	$nameValue = $itemResult['lineItemDescription'];
	$original_priceValue = '0.0000';
	$priceValue = '0.0000';
	$qty_orderedValue = $itemResult['qtyOrdered'];
	$row_totalValue = '0.0000';
	$real_skuValue = $itemResult['productCode'];
	$skuValue = "Product " . $itemNumber . "-OFFLINE";
	$parent_skuValue = 'Product ' . $itemNumber;//Just for simple
	
	fwrite($transactionLogHandle, "      ->ADDING SIMPLE       : " . $itemNumber . " -> " . $real_skuValue . "\n");

	$tax_percentValue = '0.00';
	$base_price_incl_taxValue = '0.0000';
	$base_row_total_incl_taxValue = '0.0000';
	$base_tax_amountValue = '0.0000';
	$price_incl_taxValue = '0.0000';
	$row_total_incl_taxValue = '0.0000';
	$tax_amountValue = '0.0000';

	//Create line item
	$amount_refunded = $item->appendChild($dom->createElement('amount_refunded'));
	$amount_refunded->appendChild($dom->createTextNode('0')); //Always 0
	$applied_rule_ids = $item->appendChild($dom->createElement('applied_rule_ids'));
	$applied_rule_ids->appendChild($dom->createTextNode(''));
	$base_amount_refunded = $item->appendChild($dom->createElement('base_amount_refunded'));
	$base_amount_refunded->appendChild($dom->createTextNode('0')); //Always 0
	$base_cost = $item->appendChild($dom->createElement('base_cost'));
	$base_cost->appendChild($dom->createTextNode(''));
	$base_discount_amount = $item->appendChild($dom->createElement('base_discount_amount'));
	$base_discount_amount->appendChild($dom->createTextNode('0')); //Always 0
	$base_discount_invoiced = $item->appendChild($dom->createElement('base_discount_invoiced'));
	$base_discount_invoiced->appendChild($dom->createTextNode('0')); //Always 0
	$base_hidden_tax_amount = $item->appendChild($dom->createElement('base_hidden_tax_amount'));
	$base_hidden_tax_amount->appendChild($dom->createTextNode(''));
	$base_hidden_tax_invoiced = $item->appendChild($dom->createElement('base_hidden_tax_invoiced'));
	$base_hidden_tax_invoiced->appendChild($dom->createTextNode(''));
	$base_hidden_tax_refunded = $item->appendChild($dom->createElement('base_hidden_tax_refunded'));
	$base_hidden_tax_refunded->appendChild($dom->createTextNode(''));
	$base_original_price = $item->appendChild($dom->createElement('base_original_price'));
	$base_original_price->appendChild($dom->createTextNode($base_original_priceValue));
	$base_price = $item->appendChild($dom->createElement('base_price'));
	$base_price->appendChild($dom->createTextNode($base_priceValue));
	$base_price_incl_tax = $item->appendChild($dom->createElement('base_price_incl_tax'));
	$base_price_incl_tax->appendChild($dom->createTextNode($base_price_incl_taxValue));
	$base_row_invoiced = $item->appendChild($dom->createElement('base_row_invoiced'));
	$base_row_invoiced->appendChild($dom->createTextNode('0'));
	$base_row_total = $item->appendChild($dom->createElement('base_row_total'));
	$base_row_total->appendChild($dom->createTextNode($base_row_totalValue));
	$base_row_total_incl_tax = $item->appendChild($dom->createElement('base_row_total_incl_tax'));
	$base_row_total_incl_tax->appendChild($dom->createTextNode($base_row_total_incl_taxValue));
	$base_tax_amount = $item->appendChild($dom->createElement('base_tax_amount'));
	$base_tax_amount->appendChild($dom->createTextNode($base_tax_amountValue));
	$base_tax_before_discount = $item->appendChild($dom->createElement('base_tax_before_discount'));
	$base_tax_before_discount->appendChild($dom->createTextNode(''));
	$base_tax_invoiced = $item->appendChild($dom->createElement('base_tax_invoiced'));
	$base_tax_invoiced->appendChild($dom->createTextNode('0')); //Always 0
	$base_weee_tax_applied_amount = $item->appendChild($dom->createElement('base_weee_tax_applied_amount'));
	$base_weee_tax_applied_amount->appendChild($dom->createTextNode('0')); //Always 0
	$base_weee_tax_applied_row_amount = $item->appendChild($dom->createElement('base_weee_tax_applied_row_amount'));
	$base_weee_tax_applied_row_amount->appendChild($dom->createTextNode('0')); //Always 0
	$base_weee_tax_disposition = $item->appendChild($dom->createElement('base_weee_tax_disposition'));
	$base_weee_tax_disposition->appendChild($dom->createTextNode('0')); //Always 0
	$base_weee_tax_row_disposition = $item->appendChild($dom->createElement('base_weee_tax_row_disposition'));
	$base_weee_tax_row_disposition->appendChild($dom->createTextNode('0')); //Always 0
	$description = $item->appendChild($dom->createElement('description'));
	$description->appendChild($dom->createTextNode(''));
	$discount_amount = $item->appendChild($dom->createElement('discount_amount'));
	$discount_amount->appendChild($dom->createTextNode('0')); //Always 0
	$discount_invoiced = $item->appendChild($dom->createElement('discount_invoiced'));
	$discount_invoiced->appendChild($dom->createTextNode('0')); //Always 0
	$discount_percent = $item->appendChild($dom->createElement('discount_percent'));
	$discount_percent->appendChild($dom->createTextNode('0')); //Always 0
	$free_shipping = $item->appendChild($dom->createElement('free_shipping'));
	$free_shipping->appendChild($dom->createTextNode('0')); //Always 0
	$hidden_tax_amount = $item->appendChild($dom->createElement('hidden_tax_amount'));
	$hidden_tax_amount->appendChild($dom->createTextNode(''));
	$hidden_tax_canceled = $item->appendChild($dom->createElement('hidden_tax_canceled'));
	$hidden_tax_canceled->appendChild($dom->createTextNode(''));
	$hidden_tax_invoiced = $item->appendChild($dom->createElement('hidden_tax_invoiced'));
	$hidden_tax_invoiced->appendChild($dom->createTextNode(''));
	$hidden_tax_refunded = $item->appendChild($dom->createElement('hidden_tax_refunded'));
	$hidden_tax_refunded->appendChild($dom->createTextNode(''));
	$is_nominal = $item->appendChild($dom->createElement('is_nominal'));
	$is_nominal->appendChild($dom->createTextNode('0')); //Always 0
	$is_qty_decimal = $item->appendChild($dom->createElement('is_qty_decimal'));
	$is_qty_decimal->appendChild($dom->createTextNode('0')); //Always 0
	$is_virtual = $item->appendChild($dom->createElement('is_virtual'));
	$is_virtual->appendChild($dom->createTextNode('0')); //Always 0
	$real_name = $item->appendChild($dom->createElement('real_name'));
	$real_name->appendChild($dom->createTextNode($real_nameValue)); //Always 0
	$name = $item->appendChild($dom->createElement('nameValue'));
	$name->appendChild($dom->createTextNode($nameValue)); //Always 0
	$no_discount = $item->appendChild($dom->createElement('no_discount'));
	$no_discount->appendChild($dom->createTextNode('0')); //Always 0
	$original_price = $item->appendChild($dom->createElement('original_price'));
	$original_price->appendChild($dom->createTextNode($original_priceValue));
	$parent_sku = $item->appendChild($dom->createElement('parent_sku'));
	$parent_sku->appendChild($dom->createTextNode($parent_skuValue));
	$price = $item->appendChild($dom->createElement('price'));
	$price->appendChild($dom->createTextNode($priceValue));
	$price_incl_tax = $item->appendChild($dom->createElement('price_incl_tax'));
	$price_incl_tax->appendChild($dom->createTextNode($price_incl_taxValue));
	$qty_backordered = $item->appendChild($dom->createElement('qty_backordered'));
	$qty_backordered->appendChild($dom->createTextNode(''));
	$qty_canceled = $item->appendChild($dom->createElement('qty_canceled'));
	$qty_canceled->appendChild($dom->createTextNode('0')); //Always 0
	$qty_invoiced = $item->appendChild($dom->createElement('qty_invoiced'));
	$qty_invoiced->appendChild($dom->createTextNode('0')); //Always 0
	$qty_ordered = $item->appendChild($dom->createElement('qty_ordered'));
	$qty_ordered->appendChild($dom->createTextNode($qty_orderedValue)); //Always 0
	$qty_refunded = $item->appendChild($dom->createElement('qty_refunded'));
	$qty_refunded->appendChild($dom->createTextNode('0')); //Always 0
	$qty_shipped = $item->appendChild($dom->createElement('qty_shipped'));
	$qty_shipped->appendChild($dom->createTextNode('0')); //Always 0
	$row_invoiced = $item->appendChild($dom->createElement('row_invoiced'));
	$row_invoiced->appendChild($dom->createTextNode('0')); //Always 0
	$row_total = $item->appendChild($dom->createElement('row_total'));
	$row_total->appendChild($dom->createTextNode($row_totalValue));
	$row_total_incl_tax = $item->appendChild($dom->createElement('row_total_incl_tax'));
	$row_total_incl_tax->appendChild($dom->createTextNode($row_total_incl_taxValue));
	$row_weight = $item->appendChild($dom->createElement('row_weight'));
	$row_weight->appendChild($dom->createTextNode('0'));
	$real_sku = $item->appendChild($dom->createElement('real_sku'));
	$real_sku->appendChild($dom->createTextNode($real_skuValue));
	$sku = $item->appendChild($dom->createElement('sku'));
	$sku->appendChild($dom->createTextNode($skuValue));
	$tax_amount = $item->appendChild($dom->createElement('tax_amount'));
	$tax_amount->appendChild($dom->createTextNode($tax_amountValue));
	$tax_before_discount = $item->appendChild($dom->createElement('tax_before_discount'));
	$tax_before_discount->appendChild($dom->createTextNode(''));
	$tax_canceled = $item->appendChild($dom->createElement('tax_canceled'));
	$tax_canceled->appendChild($dom->createTextNode(''));
	$tax_invoiced = $item->appendChild($dom->createElement('tax_invoiced'));
	$tax_invoiced->appendChild($dom->createTextNode('0'));
	$tax_percent = $item->appendChild($dom->createElement('tax_percent'));
	$tax_percent->appendChild($dom->createTextNode($tax_percentValue));
	$tax_refunded = $item->appendChild($dom->createElement('tax_refunded'));
	$tax_refunded->appendChild($dom->createTextNode(''));
	$weee_tax_applied = $item->appendChild($dom->createElement('weee_tax_applied'));
	$weee_tax_applied->appendChild($dom->createTextNode('a:0:{}')); //Always 0
	$weee_tax_applied_amount = $item->appendChild($dom->createElement('weee_tax_applied_amount'));
	$weee_tax_applied_amount->appendChild($dom->createTextNode('0')); //Always 0
	$weee_tax_applied_row_amount = $item->appendChild($dom->createElement('weee_tax_applied_row_amount'));
	$weee_tax_applied_row_amount->appendChild($dom->createTextNode('0')); //Always 0
	$weee_tax_disposition = $item->appendChild($dom->createElement('weee_tax_disposition'));
	$weee_tax_disposition->appendChild($dom->createTextNode('0')); //Always 0
	$weee_tax_row_disposition = $item->appendChild($dom->createElement('weee_tax_row_disposition'));
	$weee_tax_row_disposition->appendChild($dom->createTextNode('0')); //Always 0
	$weight = $item->appendChild($dom->createElement('weight'));
	$weight->appendChild($dom->createTextNode('0'));
	
        $itemNumber++;
    }
}
    
// Make the output pretty
$dom->formatOutput = true;

// Save the XML string
$xml = $dom->saveXML();

//Write file to post directory
$handle = fopen($toolsXmlDirectory . $orderFilename, 'w');
fwrite($handle, $xml);
fclose($handle);

fwrite($transactionLogHandle, "  ->FINISH TIME   : " . $endTime[5] . '/' . $endTime[4] . '/' . $endTime[3] . ' ' . $endTime[2] . ':' . $endTime[1] . ':' . $endTime[0] . "\n");
fwrite($transactionLogHandle, "  ->CREATED       : ORDER FILE 1 " . $orderFilename . "\n");

fwrite($transactionLogHandle, "->END PROCESSING\n");
//Close transaction log
fclose($transactionLogHandle);

//FILE 2
$realTime = realTime();
//Open transaction log
$transactionLogHandle = fopen($toolsLogsDirectory . 'migration_gen_sneakerhead_order_xml_files_transaction_log', 'a+');
fwrite($transactionLogHandle, "->BEGIN PROCESSING\n");
fwrite($transactionLogHandle, "  ->START TIME    : " . $realTime[5] . '/' . $realTime[4] . '/' . $realTime[3] . ' ' . $realTime[2] . ':' . $realTime[1] . ':' . $realTime[0] . "\n");

//ORDERS
fwrite($transactionLogHandle, "  ->GETTING ORDERS\n");

$resource = Mage::getSingleton('core/resource');
$writeConnection = $resource->getConnection('core_write');
//$startDate = '2009-10-00 00:00:00';//>=
$startDate = '2010-03-15 00:00:00';//>=
$endDate = '2010-03-15 00:00:00';//<
//10,000
//$query = "SELECT `users`.`email` AS `user_email`, `users`.`firstName` AS `user_firstname`, `users`.`lastName` AS `user_lastname`, `se_order`.* FROM `se_order` LEFT JOIN `storeorder` ON `se_order`.`yahooOrderIdNumeric` = `storeorder`.`yahooOrderIdNumeric` LEFT JOIN `users` ON `storeorder`.`uid` = `users`.`uid` WHERE `se_order`.`orderCreationDate` > '2009-10-00 00:00:00' AND `se_order`.`orderCreationDate` < '2009-12-14 00:00:00'";
//25,000
//FOLLOWING 8 QUERIES TO BE RUN SEPARATELY TO GENERATE 8 DIFFERENT FILES
//$query = "SELECT `users`.`email` AS `user_email`, `users`.`firstName` AS `user_firstname`, `users`.`lastName` AS `user_lastname`, `se_order`.* FROM `se_order` LEFT JOIN `storeorder` ON `se_order`.`yahooOrderIdNumeric` = `storeorder`.`yahooOrderIdNumeric` LEFT JOIN `users` ON `storeorder`.`uid` = `users`.`uid` WHERE `se_order`.`orderCreationDate` >= '2009-10-00 00:00:00' AND `se_order`.`orderCreationDate` < '2010-03-15 00:00:00'";//191298 -> 216253
$query = "SELECT `users`.`email` AS `user_email`, `users`.`firstName` AS `user_firstname`, `users`.`lastName` AS `user_lastname`, `se_order`.* FROM `se_order` LEFT JOIN `storeorder` ON `se_order`.`yahooOrderIdNumeric` = `storeorder`.`yahooOrderIdNumeric` LEFT JOIN `users` ON `storeorder`.`uid` = `users`.`uid` WHERE `se_order`.`orderCreationDate` >= '2010-03-15 00:00:00' AND `se_order`.`orderCreationDate` < '2010-10-28 00:00:00'";//216254 -> 241203
//$query = "SELECT `users`.`email` AS `user_email`, `users`.`firstName` AS `user_firstname`, `users`.`lastName` AS `user_lastname`, `se_order`.* FROM `se_order` LEFT JOIN `storeorder` ON `se_order`.`yahooOrderIdNumeric` = `storeorder`.`yahooOrderIdNumeric` LEFT JOIN `users` ON `storeorder`.`uid` = `users`.`uid` WHERE `se_order`.`orderCreationDate` >= '2010-10-28 00:00:00' AND `se_order`.`orderCreationDate` < '2011-02-27 00:00:00'";//241204 -> 266066
//$query = "SELECT `users`.`email` AS `user_email`, `users`.`firstName` AS `user_firstname`, `users`.`lastName` AS `user_lastname`, `se_order`.* FROM `se_order` LEFT JOIN `storeorder` ON `se_order`.`yahooOrderIdNumeric` = `storeorder`.`yahooOrderIdNumeric` LEFT JOIN `users` ON `storeorder`.`uid` = `users`.`uid` WHERE `se_order`.`orderCreationDate` >= '2011-02-27 00:00:00' AND `se_order`.`orderCreationDate` < '2011-06-27 00:00:00'";//266067 -> 291019
//$query = "SELECT `users`.`email` AS `user_email`, `users`.`firstName` AS `user_firstname`, `users`.`lastName` AS `user_lastname`, `se_order`.* FROM `se_order` LEFT JOIN `storeorder` ON `se_order`.`yahooOrderIdNumeric` = `storeorder`.`yahooOrderIdNumeric` LEFT JOIN `users` ON `storeorder`.`uid` = `users`.`uid` WHERE `se_order`.`orderCreationDate` >= '2011-06-27 00:00:00' AND `se_order`.`orderCreationDate` < '2011-12-09 00:00:00'";//291020 -> 315244
//$query = "SELECT `users`.`email` AS `user_email`, `users`.`firstName` AS `user_firstname`, `users`.`lastName` AS `user_lastname`, `se_order`.* FROM `se_order` LEFT JOIN `storeorder` ON `se_order`.`yahooOrderIdNumeric` = `storeorder`.`yahooOrderIdNumeric` LEFT JOIN `users` ON `storeorder`.`uid` = `users`.`uid` WHERE `se_order`.`orderCreationDate` >= '2011-12-09 00:00:00' AND `se_order`.`orderCreationDate` < '2012-04-24 00:00:00'";//315245 -> 340092
//$query = "SELECT `users`.`email` AS `user_email`, `users`.`firstName` AS `user_firstname`, `users`.`lastName` AS `user_lastname`, `se_order`.* FROM `se_order` LEFT JOIN `storeorder` ON `se_order`.`yahooOrderIdNumeric` = `storeorder`.`yahooOrderIdNumeric` LEFT JOIN `users` ON `storeorder`.`uid` = `users`.`uid` WHERE `se_order`.`orderCreationDate` >= '2012-04-24 00:00:00' AND `se_order`.`orderCreationDate` < '2012-10-04 00:00:00'";//340093 -> 364330
//$query = "SELECT `users`.`email` AS `user_email`, `users`.`firstName` AS `user_firstname`, `users`.`lastName` AS `user_lastname`, `se_order`.* FROM `se_order` LEFT JOIN `storeorder` ON `se_order`.`yahooOrderIdNumeric` = `storeorder`.`yahooOrderIdNumeric` LEFT JOIN `users` ON `storeorder`.`uid` = `users`.`uid` WHERE `se_order`.`orderCreationDate` >= '2012-10-04 00:00:00' AND `se_order`.`orderCreationDate` < '2013-02-16 00:00:00'";//364331 -> ???
//$query = "SELECT `users`.`email` AS `user_email`, `users`.`firstName` AS `user_firstname`, `users`.`lastName` AS `user_lastname`, `se_order`.* FROM `se_order` LEFT JOIN `storeorder` ON `se_order`.`yahooOrderIdNumeric` = `storeorder`.`yahooOrderIdNumeric` LEFT JOIN `users` ON `storeorder`.`uid` = `users`.`uid` WHERE `se_order`.`orderCreationDate` >= '2013-02-16 00:00:00' AND `se_order`.`orderCreationDate` < '2013-04-23 00:00:00'";//??? -> ???
//$query = "SELECT `users`.`email` AS `user_email`, `users`.`firstName` AS `user_firstname`, `users`.`lastName` AS `user_lastname`, `se_order`.* FROM `se_order` LEFT JOIN `storeorder` ON `se_order`.`yahooOrderIdNumeric` = `storeorder`.`yahooOrderIdNumeric` LEFT JOIN `users` ON `storeorder`.`uid` = `users`.`uid` WHERE `se_order`.`orderCreationDate` >= '2013-04-23 00:00:00'"; //??? -> current (???)

$results = $writeConnection->query($query);
$orderFilename = "order_2_" . $realTime[5] . $realTime[4] . $realTime[3] . '_' . $realTime[2] . $realTime[1] . $realTime[0] . ".xml";
//Creates XML string and XML document from the DOM representation
$dom = new DomDocument('1.0');

$orders = $dom->appendChild($dom->createElement('orders'));
foreach ($results as $result) {
    
    //Add order data
    fwrite($transactionLogHandle, "    ->ADDING ORDER NUMBER   : " . $result['yahooOrderIdNumeric'] . "\n");
    
    // Set some variables
    $base_discount_amountValue = (is_null($result['discount'])) ? '0.0000' : $result['discount'];//appears to be actual total discount
    $base_grand_totalValue = (is_null($result['finalGrandTotal'])) ? '0.0000' : $result['finalGrandTotal'];
    $base_shipping_amountValue = (is_null($result['shippingCost'])) ? '0.0000' : $result['shippingCost'];
    $base_shipping_incl_taxValue = (is_null($result['shippingCost'])) ? '0.0000' : $result['shippingCost'];
    $base_subtotalValue = (is_null($result['finalSubTotal'])) ? '0.0000' : $result['finalSubTotal'];
    $base_subtotal_incl_taxValue = (is_null($result['finalSubTotal'])) ? '0.0000' : $result['finalSubTotal'];
    $base_tax_amountValue = (is_null($result['taxTotal'])) ? '0.0000' : $result['taxTotal'];

    if (!is_null($result['shipCountry']) && $result['shipState'] == 'CA') {
	if (strtolower($result['shipCountry']) == 'united states') {
	    $tax_percentValue = '8.75';
	} else {
	    $tax_percentValue = '0.00';
	}
    } else {
	$tax_percentValue = '0.00';
    }

    $base_total_dueValue = (is_null($result['finalGrandTotal'])) ? '0.0000' : $result['finalGrandTotal'];
    $real_created_atValue = (is_null($result['orderCreationDate'])) ? date("Y-m-d H:i:s") : $result['orderCreationDate'];//current date or order creation date
    $created_at_timestampValue = strtotime($real_created_atValue);//set from created date
    $customer_emailValue = (is_null($result['user_email'])) ? (is_null($result['email'])) ? '' : $result['email'] : $result['user_email'];
    $customer_firstnameValue = (is_null($result['user_firstname'])) ? (is_null($result['firstName'])) ? '' : $result['firstName'] : $result['user_firstname'];
    $customer_lastnameValue = (is_null($result['user_lastname'])) ? (is_null($result['lastName'])) ? '' : $result['lastName'] : $result['user_lastname'];
    if (is_null($result['user_firstname'])) {
	$customer_nameValue = '';
    } else {
	$customer_nameValue = $customer_firstnameValue . ' ' . $customer_lastnameValue;
    }
    $customer_nameValue = $customer_firstnameValue . ' ' . $customer_lastnameValue;
    //Lookup customer
    if ($result['user_email'] == NULL) {
	$customer_group_idValue = 0;
    } else {
	$customerQuery = "SELECT `entity_id` FROM `customer_entity` WHERE `email` = '" . $result['user_email'] . "'";
	$customerResults = $writeConnection->query($customerQuery);
	$customerFound = NULL;
	foreach ($customerResults as $customerResult) {  
	    $customerFound = 1;
	}
	if (!$customerFound) {
	    fwrite($transactionLogHandle, "    ->CUSTOMER NOT FOUND    : " . $result['yahooOrderIdNumeric'] . "\n");	    
	    $customer_group_idValue = 0;
	} else {
	    $customer_group_idValue = 1;
	}
    }
    
    $discount_amountValue = (is_null($result['discount'])) ? '0.0000' : $result['discount'];//appears to be actual total discount
    $grand_totalValue = (is_null($result['finalGrandTotal'])) ? '0.0000' : $result['finalGrandTotal'];
    $increment_idValue = $result['yahooOrderIdNumeric'];//import script adds value to 600000000
    $shipping_amountValue = (is_null($result['shippingCost'])) ? '0.0000' : $result['shippingCost'];
    $shipping_incl_taxValue = (is_null($result['shippingCost'])) ? '0.0000' : $result['shippingCost'];
    switch ($result['shippingMethod']) {
	case 'UPS Ground (3-7 Business Days)':
	    $shipping_methodValue = 'ups_03';
	    break;
	case 'APO & FPO Addresses (5-30 Business Days by USPS)':
	    $shipping_methodValue = 'usps_Priority Mail';
	    break;
	case 'UPS Next Day Air (2-3 Business Days)':
	    $shipping_methodValue = 'ups_01';
	    break;
	case '"Alaska, Hawaii, U.S. Virgin Islands & Puerto Rico':
	    $shipping_methodValue = 'usps_Priority Mail';
	    break;
	case 'UPS 2nd Day Air (3-4 Business Days)':
	    $shipping_methodValue = 'ups_02';
	    break;
	case 'International Express (Shipped with Shoebox)':
	    $shipping_methodValue = 'ups_08';
	    break;
	case 'International Express (Shipped without Shoebox)':
	    $shipping_methodValue = 'ups_08';
	    break;
	case 'USPS Priority Mail (4-5 Business Days)':
	    $shipping_methodValue = 'usps_Priority Mail';
	    break;
	case 'UPS 3 Day Select (4-5 Business Days)':
	    $shipping_methodValue = 'ups_12';
	    break;
	case 'EMS - International':
	    $shipping_methodValue = 'ups_08';
	    break;
	case 'Canada Express (4-7 Business Days)':
	    $shipping_methodValue = 'ups_08';
	    break;
	case 'EMS Canada':
	    $shipping_methodValue = 'ups_08';
	    break;
	case 'Christmas Express (Delivered by Dec. 24th)':
	    $shipping_methodValue = 'ups_03';
	    break;
	case 'COD Ground':
	    $shipping_methodValue = 'ups_03';
	    break;
	case 'COD Overnight':
	    $shipping_methodValue = 'ups_01';
	    break;
	case 'Free Christmas Express (Delivered by Dec. 24th)':
	    $shipping_methodValue = 'ups_03';
	    break;
	case 'New Year Express (Delivered by Dec. 31st)':
	    $shipping_methodValue = 'ups_03';
	    break;
	case 'Free UPS Ground (3-7 Business Days)':
	    $shipping_methodValue = 'ups_03';
	    break;
	case 'COD 2-Day':
	    $shipping_methodValue = 'ups_02';
	    break;
	case 'MSI International Express':
	    $shipping_methodValue = 'ups_08';
	    break;
	case 'Customer Pickup':
	    $shipping_methodValue = 'ups_03';
	    break;
	case 'UPS Ground':
	    $shipping_methodValue = 'ups_03';
	    break;
	case 'UPS 2nd Day Air':
	    $shipping_methodValue = 'ups_02';
	    break;
	case 'APO & FPO Addresses':
	    $shipping_methodValue = 'usps_Priority Mail';
	    break;
	case 'UPS Next Day Air Saver':
	    $shipping_methodValue = 'ups_01';
	    break;
	case 'UPS 3 Day Select':
	    $shipping_methodValue = 'ups_12';
	    break;
	case 'International Express':
	    $shipping_methodValue = 'ups_08';
	    break;
	case 'USPS Priority Mail':
	    $shipping_methodValue = 'usps_Priority Mail';
	    break;
	case 'Canada Express':
	    $shipping_methodValue = 'ups_08';
	    break;
	case 'UPS Next Day Air':
	    $shipping_methodValue = 'ups_01';
	    break;
	case 'Holiday Shipping (Delivered by Dec. 24th)':
	    $shipping_methodValue = 'ups_03';
	    break;
	default://case 'NULL'
	    $shipping_methodValue = '';
	    break;
    }
    
    $stateValue = 'new';//Always new -- will set on order status update
    $statusValue = 'pending';//Always Pending -- will set on order status update
    $subtotalValue = (is_null($result['finalSubTotal'])) ? '0.0000' : $result['finalSubTotal'];
    $subtotal_incl_taxValue = (is_null($result['finalSubTotal'])) ? '0.0000' : $result['finalSubTotal'];
    $tax_amountValue = (is_null($result['taxTotal'])) ? '0.0000' : $result['taxTotal'];
    $total_dueValue = (is_null($result['finalGrandTotal'])) ? '0.0000' : $result['finalGrandTotal'];

    // Get total qty
    $itemsQuery = "SELECT * FROM `se_orderitem` WHERE `yahooOrderIdNumeric` = " . $result['yahooOrderIdNumeric'];
    $itemsResult = $writeConnection->query($itemsQuery);
    $itemCount = 0;
    foreach ($itemsResult as $itemResult) {
	$itemCount += 1;//number of items not quantites
    }
    if ($itemCount == 0) {
	fwrite($transactionLogHandle, "      ->NO ITEMS FOUND      : " . $result['yahooOrderIdNumeric'] . "\n");
    }
    $total_qty_orderedValue = $itemCount . '.0000';//Derived from item qty count
    $updated_atValue = date("Y-m-d H:i:s");
    $updated_at_timestampValue = strtotime($real_created_atValue);
    $weightValue = '0.0000'; //No weight data available

    //Shipping
    $shippingCityValue = (is_null($result['shipCity'])) ? '' : $result['shipCity'];
    $shippingCountryValue = (is_null($result['shipCountry'])) ? '' : $result['shipCountry'];
    $shippingEmailValue = (is_null($result['email'])) ? '' : $result['email'];
    $shippingFirstnameValue = (is_null($result['shipName'])) ? '' : $result['shipName'];
    $shippingLastnameValue = '';
    $shippingNameValue = $result['shipName'];
    $shippingPostcodeValue = (is_null($result['shipZip'])) ? '' : $result['shipZip'];
    if (strtolower($shippingCountryValue) == 'united states') {
	$shippingRegionValue = (is_null($result['shipState'])) ? '' : strtoupper($result['shipState']);
    } else {
	$shippingRegionValue = (is_null($result['shipState'])) ? '' : $result['shipState'];
    }
    $shippingRegion_idValue = '';//Seems to work without conversion
    if ((!is_null($result['shipAddress1']) && $result['shipAddress1'] != '') && (is_null($result['shipAddress2']) || $result['shipAddress2'] == '')) {
	$shippingStreetValue = $result['shipAddress1'];
    } elseif ((!is_null($result['shipAddress1']) && $result['shipAddress1'] != '') && (!is_null($result['shipAddress2']) && $result['shipAddress2'] != '')) {
	$shippingStreetValue = $result['shipAddress2'] . '&#10;' . $result['shipAddress2']; //Include CR/LF
    } elseif ((is_null($result['shipAddress1']) || $result['shipAddress1'] == '') && (!is_null($result['shipAddress2']) && $result['shipAddress2'] != '')) {
	$shippingStreetValue = $result['shipAddress2'];
    } else {
	$shippingStreetValue = '';
    }
    $shippingTelephoneValue = (is_null($result['shipPhone'])) ? '' : $result['shipPhone'];
    
    //Billing
    $billingCityValue = (is_null($result['billCity'])) ? '' : $result['billCity'];
    $billingCountryValue = (is_null($result['billCountry'])) ? '' : $result['billCountry'];
    $billingEmailValue = (is_null($result['email'])) ? '' : $result['email'];
    $billingFirstnameValue = (is_null($result['billName'])) ? '' : $result['billName'];
    $billingLastnameValue = '';
    $billingNameValue = $result['billName'];
    $billingPostcodeValue = (is_null($result['billZip'])) ? '' : $result['billZip'];
    if (strtolower($billingCountryValue) == 'united states') {
	$billingRegionValue = (is_null($result['billState'])) ? '' : strtoupper($result['billState']);
    } else {
	$billingRegionValue = (is_null($result['billState'])) ? '' : $result['billState'];
    }
    $billingRegion_idValue = '';//Seems to work without conversion
    if ((!is_null($result['billAddress1']) && $result['billAddress1'] != '') && (is_null($result['billAddress2']) || $result['billAddress2'] == '')) {
	$billingStreetValue = $result['billAddress1'];
    } elseif ((!is_null($result['billAddress1']) && $result['billAddress1'] != '') && (!is_null($result['billAddress2']) && $result['billAddress2'] != '')) {
	$billingStreetValue = $result['billAddress2'] . '&#10;' . $result['billAddress2']; //Include CR/LF
    } elseif ((is_null($result['billAddress1']) || $result['billAddress1'] == '') && (!is_null($result['billAddress2']) && $result['billAddress2'] != '')) {
	$billingStreetValue = $result['billAddress2'];
    } else {
	$billingStreetValue = '';
    }
    $billingTelephoneValue = (is_null($result['billPhone'])) ? '' : $result['billPhone'];
    
    //Payment
    switch ($result['paymentType']) {
	case 'Visa':
	    $cc_typeValue = 'VI';
            $methodValue = 'authorizenet';
	    break;
	case 'AMEX':
	    $cc_typeValue = 'AE';
            $methodValue = 'authorizenet';
            break;
	case 'Mastercard':
	    $cc_typeValue = 'MC';
            $methodValue = 'authorizenet';
            break;
	case 'Discover':
	    $cc_typeValue = 'DI';
            $methodValue = 'authorizenet';
	    break;
	case 'Paypal':
	    $cc_typeValue = '';
            $methodValue = 'paypal_express';
	    break;
	case 'C.O.D.':
	    $cc_typeValue = '';
            $methodValue = 'free';
	    break;
	case 'GiftCert':
	    //100% payed with giftcard
	    $cc_typeValue = '';
            $methodValue = 'free';
	    break;
	default: //NULL
	    $cc_typeValue = '';
	    $methodValue = 'free';
    }
    $amount_authorizedValue = (is_null($result['finalGrandTotal'])) ? '' : $result['finalGrandTotal'];
    $amount_orderedValue = (is_null($result['finalGrandTotal'])) ? '' : $result['finalGrandTotal'];
    $base_amount_authorizedValue = (is_null($result['finalGrandTotal'])) ? '' : $result['finalGrandTotal'];
    $base_amount_orderedValue = (is_null($result['finalGrandTotal'])) ? '' : $result['finalGrandTotal'];
    $base_shipping_amountValue = (is_null($result['shippingCost'])) ? '' : $result['shippingCost'];
    $cc_approvalValue = (is_null($result['ccApprovalNumber'])) ? '' : $result['ccApprovalNumber'];
    $cc_cid_statusValue = (is_null($result['ccCvvResponse'])) ? '' : $result['ccCvvResponse'];
    $ccExpiration = (is_null($result['ccExpiration'])) ? '' : explode('/', $result['ccExpiration']);
    if (is_null($ccExpiration)) {
        $cc_exp_monthValue = '';
        $cc_exp_yearValue = '';
    } else {
        $cc_exp_monthValue = $ccExpiration[0];
        $cc_exp_yearValue = $ccExpiration[1];
    }
    $cc_last4Value = (is_null($result['ccExpiration'])) ? '' : '****';//data not available
    $anet_trans_methodValue = '';//***
    $cc_avs_statusValue = '';//***
    $cc_trans_idValue = '';//***
    $last_trans_idValue = '';//***
    $shipping_amountValue = (is_null($result['shippingCost'])) ? '' : $result['shippingCost'];

    $order = $orders->appendChild($dom->createElement('order'));

    $adjustment_negative = $order->appendChild($dom->createElement('adjustment_negative'));
    $adjustment_negative->appendChild($dom->createTextNode(''));
    $adjustment_positive = $order->appendChild($dom->createElement('adjustment_positive'));
    $adjustment_positive->appendChild($dom->createTextNode(''));
    $applied_rule_ids = $order->appendChild($dom->createElement('applied_rule_ids'));
    $applied_rule_ids->appendChild($dom->createTextNode(''));//none used -- only used for military until migration complete
    $base_adjustment_negative = $order->appendChild($dom->createElement('base_adjustment_negative'));
    $base_adjustment_negative->appendChild($dom->createTextNode(''));
    $base_adjustment_positive = $order->appendChild($dom->createElement('base_adjustment_positive'));
    $base_adjustment_positive->appendChild($dom->createTextNode(''));
    $base_currency_code = $order->appendChild($dom->createElement('base_currency_code'));
    $base_currency_code->appendChild($dom->createTextNode('USD'));// Always USD
    $base_custbalance_amount = $order->appendChild($dom->createElement('base_custbalance_amount'));
    $base_custbalance_amount->appendChild($dom->createTextNode(''));
    $base_discount_amount = $order->appendChild($dom->createElement('base_discount_amount'));
    $base_discount_amount->appendChild($dom->createTextNode($base_discount_amountValue));
    $base_discount_canceled = $order->appendChild($dom->createElement('base_discount_canceled'));
    $base_discount_canceled->appendChild($dom->createTextNode(''));
    $base_discount_invoiced = $order->appendChild($dom->createElement('base_discount_invoiced'));
    $base_discount_invoiced->appendChild($dom->createTextNode(''));
    $base_discount_refunded = $order->appendChild($dom->createElement('base_discount_refunded'));
    $base_discount_refunded->appendChild($dom->createTextNode(''));
    $base_grand_total = $order->appendChild($dom->createElement('base_grand_total'));
    $base_grand_total->appendChild($dom->createTextNode($base_grand_totalValue));
    $base_hidden_tax_amount = $order->appendChild($dom->createElement('base_hidden_tax_amount'));
    $base_hidden_tax_amount->appendChild($dom->createTextNode(''));
    $base_hidden_tax_invoiced = $order->appendChild($dom->createElement('base_hidden_tax_invoiced'));
    $base_hidden_tax_invoiced->appendChild($dom->createTextNode(''));
    $base_hidden_tax_refunded = $order->appendChild($dom->createElement('base_hidden_tax_refunded'));
    $base_hidden_tax_refunded->appendChild($dom->createTextNode(''));
    $base_shipping_amount = $order->appendChild($dom->createElement('base_shipping_amount'));
    $base_shipping_amount->appendChild($dom->createTextNode($base_shipping_amountValue));
    $base_shipping_canceled = $order->appendChild($dom->createElement('base_shipping_canceled'));
    $base_shipping_canceled->appendChild($dom->createTextNode(''));
    $base_shipping_discount_amount = $order->appendChild($dom->createElement('base_shipping_discount_amount'));
    $base_shipping_discount_amount->appendChild($dom->createTextNode('0.0000'));//Always 0
    $base_shipping_hidden_tax_amount = $order->appendChild($dom->createElement('base_shipping_hidden_tax_amount'));
    $base_shipping_hidden_tax_amount->appendChild($dom->createTextNode(''));
    $base_shipping_incl_tax = $order->appendChild($dom->createElement('base_shipping_incl_tax'));
    $base_shipping_incl_tax->appendChild($dom->createTextNode($base_shipping_incl_taxValue));
    $base_shipping_invoiced = $order->appendChild($dom->createElement('base_shipping_invoiced'));
    $base_shipping_invoiced->appendChild($dom->createTextNode(''));
    $base_shipping_refunded = $order->appendChild($dom->createElement('base_shipping_refunded'));
    $base_shipping_refunded->appendChild($dom->createTextNode(''));
    $base_shipping_tax_amount = $order->appendChild($dom->createElement('base_shipping_tax_amount'));
    $base_shipping_tax_amount->appendChild($dom->createTextNode('0.0000'));//Always 0
    $base_shipping_tax_refunded = $order->appendChild($dom->createElement('base_shipping_tax_refunded'));
    $base_shipping_tax_refunded->appendChild($dom->createTextNode(''));
    $base_subtotal = $order->appendChild($dom->createElement('base_subtotal'));
    $base_subtotal->appendChild($dom->createTextNode($base_subtotalValue));
    $base_subtotal_canceled = $order->appendChild($dom->createElement('base_subtotal_canceled'));
    $base_subtotal_canceled->appendChild($dom->createTextNode(''));
    $base_subtotal_incl_tax = $order->appendChild($dom->createElement('base_subtotal_incl_tax'));
    $base_subtotal_incl_tax->appendChild($dom->createTextNode($base_subtotal_incl_taxValue));
    $base_subtotal_invoiced = $order->appendChild($dom->createElement('base_subtotal_invoiced'));
    $base_subtotal_invoiced->appendChild($dom->createTextNode(''));
    $base_subtotal_refunded = $order->appendChild($dom->createElement('base_subtotal_refunded'));
    $base_subtotal_refunded->appendChild($dom->createTextNode(''));
    $base_tax_amount = $order->appendChild($dom->createElement('base_tax_amount'));
    $base_tax_amount->appendChild($dom->createTextNode($base_tax_amountValue));
    $base_tax_canceled = $order->appendChild($dom->createElement('base_tax_canceled'));
    $base_tax_canceled->appendChild($dom->createTextNode(''));
    $base_tax_invoiced = $order->appendChild($dom->createElement('base_tax_invoiced'));
    $base_tax_invoiced->appendChild($dom->createTextNode(''));
    $base_tax_refunded = $order->appendChild($dom->createElement('base_tax_refunded'));
    $base_tax_refunded->appendChild($dom->createTextNode(''));
    $base_to_global_rate = $order->appendChild($dom->createElement('base_to_global_rate'));
    $base_to_global_rate->appendChild($dom->createTextNode('1'));//Always 1
    $base_to_order_rate = $order->appendChild($dom->createElement('base_to_order_rate'));
    $base_to_order_rate->appendChild($dom->createTextNode('1'));//Always 1
    $base_total_canceled = $order->appendChild($dom->createElement('base_total_canceled'));
    $base_total_canceled->appendChild($dom->createTextNode('0.0000'));
    $base_total_due = $order->appendChild($dom->createElement('base_total_due'));
    $base_total_due->appendChild($dom->createTextNode($base_total_dueValue));
    $base_total_invoiced = $order->appendChild($dom->createElement('base_total_invoiced'));
    $base_total_invoiced->appendChild($dom->createTextNode('0.0000'));
    $base_total_invoiced_cost = $order->appendChild($dom->createElement('base_total_invoiced_cost'));
    $base_total_invoiced_cost->appendChild($dom->createTextNode(''));
    $base_total_offline_refunded = $order->appendChild($dom->createElement('base_total_offline_refunded'));
    $base_total_offline_refunded->appendChild($dom->createTextNode('0.0000'));
    $base_total_online_refunded = $order->appendChild($dom->createElement('base_total_online_refunded'));
    $base_total_online_refunded->appendChild($dom->createTextNode('0.0000'));
    $base_total_paid = $order->appendChild($dom->createElement('base_total_paid'));
    $base_total_paid->appendChild($dom->createTextNode('0.0000'));
    $base_total_qty_ordered = $order->appendChild($dom->createElement('base_total_qty_ordered'));
    $base_total_qty_ordered->appendChild($dom->createTextNode(''));//Always NULL
    $base_total_refunded = $order->appendChild($dom->createElement('base_total_refunded'));
    $base_total_refunded->appendChild($dom->createTextNode('0.0000'));
    $can_ship_partially = $order->appendChild($dom->createElement('can_ship_partially'));
    $can_ship_partially->appendChild($dom->createTextNode(''));
    $can_ship_partially_item = $order->appendChild($dom->createElement('can_ship_partially_item'));
    $can_ship_partially_item->appendChild($dom->createTextNode(''));
    $coupon_code = $order->appendChild($dom->createElement('coupon_code'));
    $coupon_code->appendChild($dom->createTextNode(''));
    $real_created_at = $order->appendChild($dom->createElement('real_created_at'));
    $real_created_at->appendChild($dom->createTextNode($real_created_atValue));
    $created_at_timestamp = $order->appendChild($dom->createElement('created_at_timestamp'));
    $created_at_timestamp->appendChild($dom->createTextNode($created_at_timestampValue));
    $custbalance_amount = $order->appendChild($dom->createElement('custbalance_amount'));
    $custbalance_amount->appendChild($dom->createTextNode(''));
    $customer_dob = $order->appendChild($dom->createElement('customer_dob'));
    $customer_dob->appendChild($dom->createTextNode(''));
    $customer_email = $order->appendChild($dom->createElement('customer_email'));
    $customer_email->appendChild($dom->createTextNode($customer_emailValue));
    $customer_firstname = $order->appendChild($dom->createElement('customer_firstname'));
    $customer_firstname->appendChild($dom->createTextNode($customer_firstnameValue));
    $customer_gender = $order->appendChild($dom->createElement('customer_gender'));
    $customer_gender->appendChild($dom->createTextNode(''));
    $customer_group_id = $order->appendChild($dom->createElement('customer_group_id'));
    $customer_group_id->appendChild($dom->createTextNode($customer_group_idValue));
    $customer_lastname = $order->appendChild($dom->createElement('customer_lastname'));
    $customer_lastname->appendChild($dom->createTextNode($customer_lastnameValue));
    $customer_middlename = $order->appendChild($dom->createElement('customer_middlename'));
    $customer_middlename->appendChild($dom->createTextNode(''));
    $customer_name = $order->appendChild($dom->createElement('customer_name'));
    $customer_name->appendChild($dom->createTextNode($customer_nameValue));
    $customer_note = $order->appendChild($dom->createElement('customer_note'));
    $customer_note->appendChild($dom->createTextNode(''));
    $customer_note_notify = $order->appendChild($dom->createElement('customer_note_notify'));
    $customer_note_notify->appendChild($dom->createTextNode('1'));
    $customer_prefix = $order->appendChild($dom->createElement('customer_prefix'));
    $customer_prefix->appendChild($dom->createTextNode(''));
    $customer_suffix = $order->appendChild($dom->createElement('customer_suffix'));
    $customer_suffix->appendChild($dom->createTextNode(''));
    $customer_taxvat = $order->appendChild($dom->createElement('customer_taxvat'));
    $customer_taxvat->appendChild($dom->createTextNode(''));
    $discount_amount = $order->appendChild($dom->createElement('discount_amount'));
    $discount_amount->appendChild($dom->createTextNode($discount_amountValue));
    $discount_canceled = $order->appendChild($dom->createElement('discount_canceled'));
    $discount_canceled->appendChild($dom->createTextNode(''));
    $discount_invoiced = $order->appendChild($dom->createElement('discount_invoiced'));
    $discount_invoiced->appendChild($dom->createTextNode(''));
    $discount_refunded = $order->appendChild($dom->createElement('discount_refunded'));
    $discount_refunded->appendChild($dom->createTextNode(''));
    $email_sent = $order->appendChild($dom->createElement('email_sent'));
    $email_sent->appendChild($dom->createTextNode('1'));//Always 1
    $ext_customer_id = $order->appendChild($dom->createElement('ext_customer_id'));
    $ext_customer_id->appendChild($dom->createTextNode(''));
    $ext_order_id = $order->appendChild($dom->createElement('ext_order_id'));
    $ext_order_id->appendChild($dom->createTextNode(''));
    $forced_do_shipment_with_invoice = $order->appendChild($dom->createElement('forced_do_shipment_with_invoice'));
    $forced_do_shipment_with_invoice->appendChild($dom->createTextNode(''));
    $global_currency_code = $order->appendChild($dom->createElement('global_currency_code'));
    $global_currency_code->appendChild($dom->createTextNode('USD'));
    $grand_total = $order->appendChild($dom->createElement('grand_total'));
    $grand_total->appendChild($dom->createTextNode($grand_totalValue));
    $hidden_tax_amount = $order->appendChild($dom->createElement('hidden_tax_amount'));
    $hidden_tax_amount->appendChild($dom->createTextNode(''));
    $hidden_tax_invoiced = $order->appendChild($dom->createElement('hidden_tax_invoiced'));
    $hidden_tax_invoiced->appendChild($dom->createTextNode(''));
    $hidden_tax_refunded = $order->appendChild($dom->createElement('hidden_tax_refunded'));
    $hidden_tax_refunded->appendChild($dom->createTextNode(''));
    $hold_before_state = $order->appendChild($dom->createElement('hold_before_state'));
    $hold_before_state->appendChild($dom->createTextNode(''));
    $hold_before_status = $order->appendChild($dom->createElement('hold_before_status'));
    $hold_before_status->appendChild($dom->createTextNode(''));
    $increment_id = $order->appendChild($dom->createElement('increment_id'));
    $increment_id->appendChild($dom->createTextNode($increment_idValue));
    $is_hold = $order->appendChild($dom->createElement('is_hold'));
    $is_hold->appendChild($dom->createTextNode(''));
    $is_multi_payment = $order->appendChild($dom->createElement('is_multi_payment'));
    $is_multi_payment->appendChild($dom->createTextNode(''));
    $is_virtual = $order->appendChild($dom->createElement('is_virtual'));
    $is_virtual->appendChild($dom->createTextNode('0'));//Always 0
    $order_currency_code = $order->appendChild($dom->createElement('order_currency_code'));
    $order_currency_code->appendChild($dom->createTextNode('USD'));
    $payment_authorization_amount = $order->appendChild($dom->createElement('payment_authorization_amount'));
    $payment_authorization_amount->appendChild($dom->createTextNode(''));
    $payment_authorization_expiration = $order->appendChild($dom->createElement('payment_authorization_expiration'));
    $payment_authorization_expiration->appendChild($dom->createTextNode(''));
    $paypal_ipn_customer_notified = $order->appendChild($dom->createElement('paypal_ipn_customer_notified'));
    $paypal_ipn_customer_notified->appendChild($dom->createTextNode(''));
    $real_order_id = $order->appendChild($dom->createElement('real_order_id'));
    $real_order_id->appendChild($dom->createTextNode(''));
    $remote_ip = $order->appendChild($dom->createElement('remote_ip'));
    $remote_ip->appendChild($dom->createTextNode(''));
    $shipping_amount = $order->appendChild($dom->createElement('shipping_amount'));
    $shipping_amount->appendChild($dom->createTextNode($shipping_amountValue));
    $shipping_canceled = $order->appendChild($dom->createElement('shipping_canceled'));
    $shipping_canceled->appendChild($dom->createTextNode(''));
    $shipping_discount_amount = $order->appendChild($dom->createElement('shipping_discount_amount'));
    $shipping_discount_amount->appendChild($dom->createTextNode('0.0000'));
    $shipping_hidden_tax_amount = $order->appendChild($dom->createElement('shipping_hidden_tax_amount'));
    $shipping_hidden_tax_amount->appendChild($dom->createTextNode(''));
    $shipping_incl_tax = $order->appendChild($dom->createElement('shipping_incl_tax'));
    $shipping_incl_tax->appendChild($dom->createTextNode($shipping_incl_taxValue));
    $shipping_invoiced = $order->appendChild($dom->createElement('shipping_invoiced'));
    $shipping_invoiced->appendChild($dom->createTextNode(''));
    $shipping_method = $order->appendChild($dom->createElement('shipping_method'));
    $shipping_method->appendChild($dom->createTextNode($shipping_methodValue));
    $shipping_refunded = $order->appendChild($dom->createElement('shipping_refunded'));
    $shipping_refunded->appendChild($dom->createTextNode(''));
    $shipping_tax_amount = $order->appendChild($dom->createElement('shipping_tax_amount'));
    $shipping_tax_amount->appendChild($dom->createTextNode('0.0000'));
    $shipping_tax_refunded = $order->appendChild($dom->createElement('shipping_tax_refunded'));
    $shipping_tax_refunded->appendChild($dom->createTextNode(''));
    $state = $order->appendChild($dom->createElement('state'));
    $state->appendChild($dom->createTextNode($stateValue));
    $status = $order->appendChild($dom->createElement('status'));
    $status->appendChild($dom->createTextNode($statusValue));
    $store = $order->appendChild($dom->createElement('store'));
    $store->appendChild($dom->createTextNode('sneakerhead_cn'));
    $subtotal = $order->appendChild($dom->createElement('subTotal'));
    $subtotal->appendChild($dom->createTextNode($subtotalValue));
    $subtotal_canceled = $order->appendChild($dom->createElement('subtotal_canceled'));
    $subtotal_canceled->appendChild($dom->createTextNode(''));
    $subtotal_incl_tax = $order->appendChild($dom->createElement('subtotal_incl_tax'));
    $subtotal_incl_tax->appendChild($dom->createTextNode($subtotal_incl_taxValue));
    $subtotal_invoiced = $order->appendChild($dom->createElement('subtotal_invoiced'));
    $subtotal_invoiced->appendChild($dom->createTextNode(''));
    $subtotal_refunded = $order->appendChild($dom->createElement('subtotal_refunded'));
    $subtotal_refunded->appendChild($dom->createTextNode(''));
    $tax_amount = $order->appendChild($dom->createElement('tax_amount'));
    $tax_amount->appendChild($dom->createTextNode($tax_amountValue));
    $tax_canceled = $order->appendChild($dom->createElement('tax_canceled'));
    $tax_canceled->appendChild($dom->createTextNode(''));
    $tax_invoiced = $order->appendChild($dom->createElement('tax_invoiced'));
    $tax_invoiced->appendChild($dom->createTextNode(''));
    $tax_percent = $order->appendChild($dom->createElement('tax_percent'));
    $tax_percent->appendChild($dom->createTextNode($tax_percentValue));
    $tax_refunded = $order->appendChild($dom->createElement('tax_refunded'));
    $tax_refunded->appendChild($dom->createTextNode(''));
    $total_canceled = $order->appendChild($dom->createElement('total_canceled'));
    $total_canceled->appendChild($dom->createTextNode('0.0000'));
    $total_due = $order->appendChild($dom->createElement('total_due'));
    $total_due->appendChild($dom->createTextNode($total_dueValue));
    $total_invoiced = $order->appendChild($dom->createElement('total_invoiced'));
    $total_invoiced->appendChild($dom->createTextNode('0.0000'));
    $total_item_count = $order->appendChild($dom->createElement('total_item_count'));
    $total_item_count->appendChild($dom->createTextNode(''));
    $total_offline_refunded = $order->appendChild($dom->createElement('total_offline_refunded'));
    $total_offline_refunded->appendChild($dom->createTextNode('0.0000'));
    $total_online_refunded = $order->appendChild($dom->createElement('total_online_refunded'));
    $total_online_refunded->appendChild($dom->createTextNode('0.0000'));
    $total_paid = $order->appendChild($dom->createElement('total_paid'));
    $total_paid->appendChild($dom->createTextNode('0.0000'));
    $total_qty_ordered = $order->appendChild($dom->createElement('total_qty_ordered'));
    $total_qty_ordered->appendChild($dom->createTextNode($total_qty_orderedValue));
    $total_refunded = $order->appendChild($dom->createElement('total_refunded'));
    $total_refunded->appendChild($dom->createTextNode('0.0000'));
    $tracking_numbers = $order->appendChild($dom->createElement('tracking_numbers'));
    $tracking_numbers->appendChild($dom->createTextNode(''));
    $updated_at = $order->appendChild($dom->createElement('updated_at'));
    $updated_at->appendChild($dom->createTextNode($updated_atValue));
    $updated_at_timestamp = $order->appendChild($dom->createElement('updated_at_timestamp'));
    $updated_at_timestamp->appendChild($dom->createTextNode($updated_at_timestampValue));
    $weight = $order->appendChild($dom->createElement('weight'));
    $weight->appendChild($dom->createTextNode($weightValue));
    $x_forwarded_for = $order->appendChild($dom->createElement('x_forwarded_for'));
    $x_forwarded_for->appendChild($dom->createTextNode(''));

    //Build shipping
    $shipping_address = $order->appendChild($dom->createElement('shipping_address'));
    
    $shippingCity = $shipping_address->appendChild($dom->createElement('city'));
    $shippingCity->appendChild($dom->createTextNode($shippingCityValue));
    $shippingCompany = $shipping_address->appendChild($dom->createElement('company'));
    $shippingCompany->appendChild($dom->createTextNode(''));
    $shippingCountry = $shipping_address->appendChild($dom->createElement('country'));
    $shippingCountry->appendChild($dom->createTextNode($shippingCountryValue));
    $shippingCountry_id = $shipping_address->appendChild($dom->createElement('country_id'));
    $shippingCountry_id->appendChild($dom->createTextNode(''));
    $shippingCountry_iso2 = $shipping_address->appendChild($dom->createElement('country_iso2'));
    $shippingCountry_iso2->appendChild($dom->createTextNode(''));
    $shippingCountry_iso3 = $shipping_address->appendChild($dom->createElement('country_iso3'));
    $shippingCountry_iso3->appendChild($dom->createTextNode(''));
    $shippingEmail = $shipping_address->appendChild($dom->createElement('email'));
    $shippingEmail->appendChild($dom->createTextNode($shippingEmailValue));
    $shippingFax = $shipping_address->appendChild($dom->createElement('fax'));
    $shippingFax->appendChild($dom->createTextNode(''));
    $shippingFirstname = $shipping_address->appendChild($dom->createElement('firstname'));
    $shippingFirstname->appendChild($dom->createTextNode($shippingFirstnameValue));
    $shippingLastname = $shipping_address->appendChild($dom->createElement('lastname'));
    $shippingLastname->appendChild($dom->createTextNode($shippingLastnameValue));
    $shippingMiddlename = $shipping_address->appendChild($dom->createElement('middlename'));
    $shippingMiddlename->appendChild($dom->createTextNode(''));
    $shippingName = $shipping_address->appendChild($dom->createElement('name'));
    $shippingName->appendChild($dom->createTextNode($shippingNameValue));
    $shippingPostcode = $shipping_address->appendChild($dom->createElement('postcode'));
    $shippingPostcode->appendChild($dom->createTextNode($shippingPostcodeValue));
    $shippingPrefix = $shipping_address->appendChild($dom->createElement('prefix'));
    $shippingPrefix->appendChild($dom->createTextNode(''));
    $shippingRegion = $shipping_address->appendChild($dom->createElement('region'));
    $shippingRegion->appendChild($dom->createTextNode($shippingRegionValue));
    $shippingRegion_id = $shipping_address->appendChild($dom->createElement('region_id'));
    $shippingRegion_id->appendChild($dom->createTextNode($shippingRegion_idValue));
    $shippingRegion_iso2 = $shipping_address->appendChild($dom->createElement('region_iso2'));
    $shippingRegion_iso2->appendChild($dom->createTextNode(''));
    $shippingStreet = $shipping_address->appendChild($dom->createElement('street'));
    $shippingStreet->appendChild($dom->createTextNode($shippingStreetValue));
    $shippingSuffix = $shipping_address->appendChild($dom->createElement('suffix'));
    $shippingSuffix->appendChild($dom->createTextNode(''));
    $shippingTelephone = $shipping_address->appendChild($dom->createElement('telephone'));
    $shippingTelephone->appendChild($dom->createTextNode($shippingTelephoneValue));

    // Build billing
    $billing_address = $order->appendChild($dom->createElement('billing_address'));
    
    $billingCity = $billing_address->appendChild($dom->createElement('city'));
    $billingCity->appendChild($dom->createTextNode($billingCityValue));
    $billingCompany = $billing_address->appendChild($dom->createElement('company'));
    $billingCompany->appendChild($dom->createTextNode(''));
    $billingCountry = $billing_address->appendChild($dom->createElement('country'));
    $billingCountry->appendChild($dom->createTextNode($billingCountryValue));
    $billingCountry_id = $billing_address->appendChild($dom->createElement('country_id'));
    $billingCountry_id->appendChild($dom->createTextNode(''));
    $billingCountry_iso2 = $billing_address->appendChild($dom->createElement('country_iso2'));
    $billingCountry_iso2->appendChild($dom->createTextNode(''));
    $billingCountry_iso3 = $billing_address->appendChild($dom->createElement('country_iso3'));
    $billingCountry_iso3->appendChild($dom->createTextNode(''));
    $billingEmail = $billing_address->appendChild($dom->createElement('email'));
    $billingEmail->appendChild($dom->createTextNode($billingEmailValue));
    $billingFax = $billing_address->appendChild($dom->createElement('fax'));
    $billingFax->appendChild($dom->createTextNode(''));
    $billingFirstname = $billing_address->appendChild($dom->createElement('firstname'));
    $billingFirstname->appendChild($dom->createTextNode($billingFirstnameValue));
    $billingLastname = $billing_address->appendChild($dom->createElement('lastname'));
    $billingLastname->appendChild($dom->createTextNode($billingLastnameValue));
    $billingMiddlename = $billing_address->appendChild($dom->createElement('middlename'));
    $billingMiddlename->appendChild($dom->createTextNode(''));
    $billingName = $billing_address->appendChild($dom->createElement('name'));
    $billingName->appendChild($dom->createTextNode($billingNameValue));
    $billingPostcode = $billing_address->appendChild($dom->createElement('postcode'));
    $billingPostcode->appendChild($dom->createTextNode($billingPostcodeValue));
    $billingPrefix = $billing_address->appendChild($dom->createElement('prefix'));
    $billingPrefix->appendChild($dom->createTextNode(''));
    $billingRegion = $billing_address->appendChild($dom->createElement('region'));
    $billingRegion->appendChild($dom->createTextNode($billingRegionValue));
    $billingRegion_id = $billing_address->appendChild($dom->createElement('region_id'));
    $billingRegion_id->appendChild($dom->createTextNode($billingRegion_idValue));
    $billingRegion_iso2 = $billing_address->appendChild($dom->createElement('region_iso2'));
    $billingRegion_iso2->appendChild($dom->createTextNode(''));
    $billingStreet = $billing_address->appendChild($dom->createElement('street'));
    $billingStreet->appendChild($dom->createTextNode($billingStreetValue));
    $billingSuffix = $billing_address->appendChild($dom->createElement('suffix'));
    $billingSuffix->appendChild($dom->createTextNode(''));
    $billingTelephone = $billing_address->appendChild($dom->createElement('telephone'));
    $billingTelephone->appendChild($dom->createTextNode($billingTelephoneValue));
    
    // Build payment

    $payment = $order->appendChild($dom->createElement('payment'));

    $account_status = $payment->appendChild($dom->createElement('account_status'));
    $account_status->appendChild($dom->createTextNode(''));
    $address_status = $payment->appendChild($dom->createElement('address_status'));
    $address_status->appendChild($dom->createTextNode(''));
    $amount = $payment->appendChild($dom->createElement('amount'));
    $amount->appendChild($dom->createTextNode(''));
    $amount_authorized = $payment->appendChild($dom->createElement('amount_authorized'));
    $amount_authorized->appendChild($dom->createTextNode($amount_authorizedValue));
    $amount_canceled = $payment->appendChild($dom->createElement('amount_canceled'));
    $amount_canceled->appendChild($dom->createTextNode(''));
    $amount_ordered = $payment->appendChild($dom->createElement('amount_ordered'));
    $amount_ordered->appendChild($dom->createTextNode($amount_orderedValue));
    $amount_paid = $payment->appendChild($dom->createElement('amount_paid'));
    $amount_paid->appendChild($dom->createTextNode(''));
    $amount_refunded = $payment->appendChild($dom->createElement('amount_refunded'));
    $amount_refunded->appendChild($dom->createTextNode(''));
    $anet_trans_method = $payment->appendChild($dom->createElement('anet_trans_method'));
    $anet_trans_method->appendChild($dom->createTextNode($anet_trans_methodValue));
    $base_amount_authorized = $payment->appendChild($dom->createElement('base_amount_authorized'));
    $base_amount_authorized->appendChild($dom->createTextNode($base_amount_authorizedValue));
    $base_amount_canceled = $payment->appendChild($dom->createElement('base_amount_canceled'));
    $base_amount_canceled->appendChild($dom->createTextNode(''));
    $base_amount_ordered = $payment->appendChild($dom->createElement('base_amount_ordered'));
    $base_amount_ordered->appendChild($dom->createTextNode($base_amount_orderedValue));
    $base_amount_paid = $payment->appendChild($dom->createElement('base_amount_paid'));
    $base_amount_paid->appendChild($dom->createTextNode(''));
    $base_amount_paid_online = $payment->appendChild($dom->createElement('base_amount_paid_online'));
    $base_amount_paid_online->appendChild($dom->createTextNode(''));
    $base_amount_refunded = $payment->appendChild($dom->createElement('base_amount_refunded'));
    $base_amount_refunded->appendChild($dom->createTextNode(''));
    $base_amount_refunded_online = $payment->appendChild($dom->createElement('base_amount_refunded_online'));
    $base_amount_refunded_online->appendChild($dom->createTextNode(''));
    $base_shipping_amount = $payment->appendChild($dom->createElement('base_shipping_amount'));
    $base_shipping_amount->appendChild($dom->createTextNode($base_shipping_amountValue));
    $base_shipping_captured = $payment->appendChild($dom->createElement('base_shipping_captured'));
    $base_shipping_captured->appendChild($dom->createTextNode(''));
    $base_shipping_refunded = $payment->appendChild($dom->createElement('base_shipping_refunded'));
    $base_shipping_refunded->appendChild($dom->createTextNode(''));
    $cc_approval = $payment->appendChild($dom->createElement('cc_approval'));
    $cc_approval->appendChild($dom->createTextNode($cc_approvalValue));
    $cc_avs_status = $payment->appendChild($dom->createElement('cc_avs_status'));
    $cc_avs_status->appendChild($dom->createTextNode($cc_avs_statusValue));
    $cc_cid_status = $payment->appendChild($dom->createElement('cc_cid_status'));
    $cc_cid_status->appendChild($dom->createTextNode($cc_cid_statusValue));
    $cc_debug_request_body = $payment->appendChild($dom->createElement('cc_debug_request_body'));
    $cc_debug_request_body->appendChild($dom->createTextNode(''));
    $cc_debug_response_body = $payment->appendChild($dom->createElement('cc_debug_response_body'));
    $cc_debug_response_body->appendChild($dom->createTextNode(''));
    $cc_debug_response_serialized = $payment->appendChild($dom->createElement('cc_debug_response_serialized'));
    $cc_debug_response_serialized->appendChild($dom->createTextNode(''));
    $cc_exp_month = $payment->appendChild($dom->createElement('cc_exp_month'));
    $cc_exp_month->appendChild($dom->createTextNode($cc_exp_monthValue));
    $cc_exp_year = $payment->appendChild($dom->createElement('cc_exp_year'));
    $cc_exp_year->appendChild($dom->createTextNode($cc_exp_yearValue));
    $cc_last4 = $payment->appendChild($dom->createElement('cc_last4'));
    $cc_last4->appendChild($dom->createTextNode($cc_last4Value));
    $cc_number_enc = $payment->appendChild($dom->createElement('cc_number_enc'));
    $cc_number_enc->appendChild($dom->createTextNode(''));
    $cc_owner = $payment->appendChild($dom->createElement('cc_owner'));
    $cc_owner->appendChild($dom->createTextNode(''));
    $cc_raw_request = $payment->appendChild($dom->createElement('cc_raw_request'));
    $cc_raw_request->appendChild($dom->createTextNode(''));
    $cc_raw_response = $payment->appendChild($dom->createElement('cc_raw_response'));
    $cc_raw_response->appendChild($dom->createTextNode(''));
    $cc_secure_verify = $payment->appendChild($dom->createElement('cc_secure_verify'));
    $cc_secure_verify->appendChild($dom->createTextNode(''));
    $cc_ss_issue = $payment->appendChild($dom->createElement('cc_ss_issue'));
    $cc_ss_issue->appendChild($dom->createTextNode(''));
    $cc_ss_start_month = $payment->appendChild($dom->createElement('cc_ss_start_month'));
    $cc_ss_start_month->appendChild($dom->createTextNode('0'));//appears to be 0 since not used
    $cc_ss_start_year = $payment->appendChild($dom->createElement('cc_ss_start_year'));
    $cc_ss_start_year->appendChild($dom->createTextNode('0'));//appears to be 0 since not used
    $cc_status = $payment->appendChild($dom->createElement('cc_status'));
    $cc_status->appendChild($dom->createTextNode(''));
    $cc_status_description = $payment->appendChild($dom->createElement('cc_status_description'));
    $cc_status_description->appendChild($dom->createTextNode(''));
    $cc_trans_id = $payment->appendChild($dom->createElement('cc_trans_id'));
    $cc_trans_id->appendChild($dom->createTextNode($cc_trans_idValue));
    $cc_type = $payment->appendChild($dom->createElement('cc_type'));
    $cc_type->appendChild($dom->createTextNode($cc_typeValue));
    $cybersource_token = $payment->appendChild($dom->createElement('cybersource_token'));
    $cybersource_token->appendChild($dom->createTextNode(''));
    $echeck_account_name = $payment->appendChild($dom->createElement('echeck_account_name'));
    $echeck_account_name->appendChild($dom->createTextNode(''));
    $echeck_account_type = $payment->appendChild($dom->createElement('echeck_account_type'));
    $echeck_account_type->appendChild($dom->createTextNode(''));
    $echeck_bank_name = $payment->appendChild($dom->createElement('echeck_bank_name'));
    $echeck_bank_name->appendChild($dom->createTextNode(''));
    $echeck_routing_number = $payment->appendChild($dom->createElement('echeck_routing_number'));
    $echeck_routing_number->appendChild($dom->createTextNode(''));
    $echeck_type = $payment->appendChild($dom->createElement('echeck_type'));
    $echeck_type->appendChild($dom->createTextNode(''));
    $flo2cash_account_id = $payment->appendChild($dom->createElement('flo2cash_account_id'));
    $flo2cash_account_id->appendChild($dom->createTextNode(''));
    $ideal_issuer_id = $payment->appendChild($dom->createElement('ideal_issuer_id'));
    $ideal_issuer_id->appendChild($dom->createTextNode(''));
    $ideal_issuer_title = $payment->appendChild($dom->createElement('ideal_issuer_title'));
    $ideal_issuer_title->appendChild($dom->createTextNode(''));
    $ideal_transaction_checked = $payment->appendChild($dom->createElement('ideal_transaction_checked'));
    $ideal_transaction_checked->appendChild($dom->createTextNode(''));
    $last_trans_id = $payment->appendChild($dom->createElement('last_trans_id'));
    $last_trans_id->appendChild($dom->createTextNode($last_trans_idValue));
    $method = $payment->appendChild($dom->createElement('method'));
    $method->appendChild($dom->createTextNode($methodValue));
    $paybox_question_number = $payment->appendChild($dom->createElement('paybox_question_number'));
    $paybox_question_number->appendChild($dom->createTextNode(''));
    $paybox_request_number = $payment->appendChild($dom->createElement('paybox_request_number'));
    $paybox_request_number->appendChild($dom->createTextNode(''));
    $po_number = $payment->appendChild($dom->createElement('po_number'));
    $po_number->appendChild($dom->createTextNode(''));
    $protection_eligibility = $payment->appendChild($dom->createElement('protection_eligibility'));
    $protection_eligibility->appendChild($dom->createTextNode(''));
    $shipping_amount = $payment->appendChild($dom->createElement('shipping_amount'));
    $shipping_amount->appendChild($dom->createTextNode($shipping_amountValue));
    $shipping_captured = $payment->appendChild($dom->createElement('shipping_captured'));
    $shipping_captured->appendChild($dom->createTextNode(''));
    $shipping_refunded = $payment->appendChild($dom->createElement('shipping_refunded'));
    $shipping_refunded->appendChild($dom->createTextNode(''));

    // Build Items
    $items = $order->appendChild($dom->createElement('items'));
    $itemsQuery = "SELECT * FROM `se_orderitem` WHERE `yahooOrderIdNumeric` = " . $result['yahooOrderIdNumeric'];
    $itemsResult = $writeConnection->query($itemsQuery);
    $itemNumber = 1;
    foreach ($itemsResult as $itemResult) {
        $item = $items->appendChild($dom->createElement('item'));

	//Set variables
	$base_original_priceValue = $itemResult['unitPrice'];
	$base_priceValue = $itemResult['unitPrice'];
	$base_row_totalValue = $itemResult['qtyOrdered'] * $itemResult['unitPrice'];
	$real_nameValue = $itemResult['lineItemDescription'];
	$nameValue = $itemResult['lineItemDescription'];
	$original_priceValue = $itemResult['unitPrice'];
	$priceValue = $itemResult['unitPrice'];
	$qty_orderedValue = $itemResult['qtyOrdered'];
	$row_totalValue = $itemResult['qtyOrdered'] * $itemResult['unitPrice'];
	$length = strlen(end(explode('-', $itemResult['productCode'])));
	$real_skuValue = substr($itemResult['productCode'], 0, -($length + 1));
        
	fwrite($transactionLogHandle, "      ->ADDING CONFIGURABLE : " . $itemNumber . " -> " . $real_skuValue . "\n");

	$skuValue = 'Product ' . $itemNumber;
	if (!is_null($result['shipCountry']) && $result['shipState'] == 'CA') {
	    if (strtolower($result['shipCountry']) == 'united states') {
		$tax_percentCalcValue = '0.0875';
		$tax_percentValue = '8.75';
		$base_price_incl_taxValue = round($priceValue + ($priceValue * $tax_percentCalcValue), 4);//
		$base_row_total_incl_taxValue = round($qty_orderedValue * ($priceValue + ($priceValue * $tax_percentCalcValue)), 4);//
		$base_tax_amountValue = round($priceValue * $tax_percentCalcValue, 4);//THIS MAY BE WRONG -- QTY or ONE
		$price_incl_taxValue = round($priceValue + ($priceValue * $tax_percentCalcValue), 4);//
		$row_total_incl_taxValue = round($qty_orderedValue * ($priceValue + ($priceValue * $tax_percentCalcValue)), 4);//
		$tax_amountValue = round($priceValue * $tax_percentCalcValue, 4);//
	    } else {
		$tax_percentValue = '0.00';
		$base_price_incl_taxValue = $priceValue;
		$base_row_total_incl_taxValue = $qty_orderedValue * $priceValue;
		$base_tax_amountValue = '0.00';
		$price_incl_taxValue = $priceValue;
		$row_total_incl_taxValue = $qty_orderedValue * $priceValue;
		$tax_amountValue = '0.00';		
	    }
	} else {
	    $tax_percentValue = '0.00';
	    $base_price_incl_taxValue = $priceValue;
	    $base_row_total_incl_taxValue = $qty_orderedValue * $priceValue;
	    $base_tax_amountValue = '0.00';
	    $price_incl_taxValue = $priceValue;
	    $row_total_incl_taxValue = $qty_orderedValue * $priceValue;
	    $tax_amountValue = '0.00';	
	}

	//Create line item
	$amount_refunded = $item->appendChild($dom->createElement('amount_refunded'));
	$amount_refunded->appendChild($dom->createTextNode('0')); //Always 0
	$applied_rule_ids = $item->appendChild($dom->createElement('applied_rule_ids'));
	$applied_rule_ids->appendChild($dom->createTextNode(''));
	$base_amount_refunded = $item->appendChild($dom->createElement('base_amount_refunded'));
	$base_amount_refunded->appendChild($dom->createTextNode('0')); //Always 0
	$base_cost = $item->appendChild($dom->createElement('base_cost'));
	$base_cost->appendChild($dom->createTextNode(''));
	$base_discount_amount = $item->appendChild($dom->createElement('base_discount_amount'));
	$base_discount_amount->appendChild($dom->createTextNode('0')); //Always 0
	$base_discount_invoiced = $item->appendChild($dom->createElement('base_discount_invoiced'));
	$base_discount_invoiced->appendChild($dom->createTextNode('0')); //Always 0
	$base_hidden_tax_amount = $item->appendChild($dom->createElement('base_hidden_tax_amount'));
	$base_hidden_tax_amount->appendChild($dom->createTextNode(''));
	$base_hidden_tax_invoiced = $item->appendChild($dom->createElement('base_hidden_tax_invoiced'));
	$base_hidden_tax_invoiced->appendChild($dom->createTextNode(''));
	$base_hidden_tax_refunded = $item->appendChild($dom->createElement('base_hidden_tax_refunded'));
	$base_hidden_tax_refunded->appendChild($dom->createTextNode(''));
	$base_original_price = $item->appendChild($dom->createElement('base_original_price'));
	$base_original_price->appendChild($dom->createTextNode($base_original_priceValue));
	$base_price = $item->appendChild($dom->createElement('base_price'));
	$base_price->appendChild($dom->createTextNode($base_priceValue));
	$base_price_incl_tax = $item->appendChild($dom->createElement('base_price_incl_tax'));
	$base_price_incl_tax->appendChild($dom->createTextNode($base_price_incl_taxValue));
	$base_row_invoiced = $item->appendChild($dom->createElement('base_row_invoiced'));
	$base_row_invoiced->appendChild($dom->createTextNode('0'));
	$base_row_total = $item->appendChild($dom->createElement('base_row_total'));
	$base_row_total->appendChild($dom->createTextNode($base_row_totalValue));
	$base_row_total_incl_tax = $item->appendChild($dom->createElement('base_row_total_incl_tax'));
	$base_row_total_incl_tax->appendChild($dom->createTextNode($base_row_total_incl_taxValue));
	$base_tax_amount = $item->appendChild($dom->createElement('base_tax_amount'));
	$base_tax_amount->appendChild($dom->createTextNode($base_tax_amountValue));
	$base_tax_before_discount = $item->appendChild($dom->createElement('base_tax_before_discount'));
	$base_tax_before_discount->appendChild($dom->createTextNode(''));
	$base_tax_invoiced = $item->appendChild($dom->createElement('base_tax_invoiced'));
	$base_tax_invoiced->appendChild($dom->createTextNode('0')); //Always 0
	$base_weee_tax_applied_amount = $item->appendChild($dom->createElement('base_weee_tax_applied_amount'));
	$base_weee_tax_applied_amount->appendChild($dom->createTextNode('0')); //Always 0
	$base_weee_tax_applied_row_amount = $item->appendChild($dom->createElement('base_weee_tax_applied_row_amount'));
	$base_weee_tax_applied_row_amount->appendChild($dom->createTextNode('0')); //Always 0
	$base_weee_tax_disposition = $item->appendChild($dom->createElement('base_weee_tax_disposition'));
	$base_weee_tax_disposition->appendChild($dom->createTextNode('0')); //Always 0
	$base_weee_tax_row_disposition = $item->appendChild($dom->createElement('base_weee_tax_row_disposition'));
	$base_weee_tax_row_disposition->appendChild($dom->createTextNode('0')); //Always 0
	$description = $item->appendChild($dom->createElement('description'));
	$description->appendChild($dom->createTextNode(''));
	$discount_amount = $item->appendChild($dom->createElement('discount_amount'));
	$discount_amount->appendChild($dom->createTextNode('0')); //Always 0
	$discount_invoiced = $item->appendChild($dom->createElement('discount_invoiced'));
	$discount_invoiced->appendChild($dom->createTextNode('0')); //Always 0
	$discount_percent = $item->appendChild($dom->createElement('discount_percent'));
	$discount_percent->appendChild($dom->createTextNode('0')); //Always 0
	$free_shipping = $item->appendChild($dom->createElement('free_shipping'));
	$free_shipping->appendChild($dom->createTextNode('0')); //Always 0
	$hidden_tax_amount = $item->appendChild($dom->createElement('hidden_tax_amount'));
	$hidden_tax_amount->appendChild($dom->createTextNode(''));
	$hidden_tax_canceled = $item->appendChild($dom->createElement('hidden_tax_canceled'));
	$hidden_tax_canceled->appendChild($dom->createTextNode(''));
	$hidden_tax_invoiced = $item->appendChild($dom->createElement('hidden_tax_invoiced'));
	$hidden_tax_invoiced->appendChild($dom->createTextNode(''));
	$hidden_tax_refunded = $item->appendChild($dom->createElement('hidden_tax_refunded'));
	$hidden_tax_refunded->appendChild($dom->createTextNode(''));
	$is_nominal = $item->appendChild($dom->createElement('is_nominal'));
	$is_nominal->appendChild($dom->createTextNode('0')); //Always 0
	$is_qty_decimal = $item->appendChild($dom->createElement('is_qty_decimal'));
	$is_qty_decimal->appendChild($dom->createTextNode('0')); //Always 0
	$is_virtual = $item->appendChild($dom->createElement('is_virtual'));
	$is_virtual->appendChild($dom->createTextNode('0')); //Always 0
	$real_name = $item->appendChild($dom->createElement('real_name'));
	$real_name->appendChild($dom->createTextNode($real_nameValue)); //Always 0
	$name = $item->appendChild($dom->createElement('name'));
	$name->appendChild($dom->createTextNode($nameValue)); //Always 0
	$no_discount = $item->appendChild($dom->createElement('no_discount'));
	$no_discount->appendChild($dom->createTextNode('0')); //Always 0
	$original_price = $item->appendChild($dom->createElement('original_price'));
	$original_price->appendChild($dom->createTextNode($original_priceValue));
	$price = $item->appendChild($dom->createElement('price'));
	$price->appendChild($dom->createTextNode($priceValue));
	$price_incl_tax = $item->appendChild($dom->createElement('price_incl_tax'));
	$price_incl_tax->appendChild($dom->createTextNode($price_incl_taxValue));
	$qty_backordered = $item->appendChild($dom->createElement('qty_backordered'));
	$qty_backordered->appendChild($dom->createTextNode(''));
	$qty_canceled = $item->appendChild($dom->createElement('qty_canceled'));
	$qty_canceled->appendChild($dom->createTextNode('0')); //Always 0
	$qty_invoiced = $item->appendChild($dom->createElement('qty_invoiced'));
	$qty_invoiced->appendChild($dom->createTextNode('0')); //Always 0
	$qty_ordered = $item->appendChild($dom->createElement('qty_ordered'));
	$qty_ordered->appendChild($dom->createTextNode($qty_orderedValue)); //Always 0
	$qty_refunded = $item->appendChild($dom->createElement('qty_refunded'));
	$qty_refunded->appendChild($dom->createTextNode('0')); //Always 0
	$qty_shipped = $item->appendChild($dom->createElement('qty_shipped'));
	$qty_shipped->appendChild($dom->createTextNode('0')); //Always 0
	$row_invoiced = $item->appendChild($dom->createElement('row_invoiced'));
	$row_invoiced->appendChild($dom->createTextNode('0')); //Always 0
	$row_total = $item->appendChild($dom->createElement('row_total'));
	$row_total->appendChild($dom->createTextNode($row_totalValue));
	$row_total_incl_tax = $item->appendChild($dom->createElement('row_total_incl_tax'));
	$row_total_incl_tax->appendChild($dom->createTextNode($row_total_incl_taxValue));
	$row_weight = $item->appendChild($dom->createElement('row_weight'));
	$row_weight->appendChild($dom->createTextNode('0'));
	$real_sku = $item->appendChild($dom->createElement('real_sku'));
	$real_sku->appendChild($dom->createTextNode($real_skuValue));
	$sku = $item->appendChild($dom->createElement('sku'));
	$sku->appendChild($dom->createTextNode($skuValue));
	$tax_amount = $item->appendChild($dom->createElement('tax_amount'));
	$tax_amount->appendChild($dom->createTextNode($tax_amountValue));
	$tax_before_discount = $item->appendChild($dom->createElement('tax_before_discount'));
	$tax_before_discount->appendChild($dom->createTextNode(''));
	$tax_canceled = $item->appendChild($dom->createElement('tax_canceled'));
	$tax_canceled->appendChild($dom->createTextNode(''));
	$tax_invoiced = $item->appendChild($dom->createElement('tax_invoiced'));
	$tax_invoiced->appendChild($dom->createTextNode('0'));
	$tax_percent = $item->appendChild($dom->createElement('tax_percent'));
	$tax_percent->appendChild($dom->createTextNode($tax_percentValue));
	$tax_refunded = $item->appendChild($dom->createElement('tax_refunded'));
	$tax_refunded->appendChild($dom->createTextNode(''));
	$weee_tax_applied = $item->appendChild($dom->createElement('weee_tax_applied'));
	$weee_tax_applied->appendChild($dom->createTextNode('a:0:{}')); //Always 0
	$weee_tax_applied_amount = $item->appendChild($dom->createElement('weee_tax_applied_amount'));
	$weee_tax_applied_amount->appendChild($dom->createTextNode('0')); //Always 0
	$weee_tax_applied_row_amount = $item->appendChild($dom->createElement('weee_tax_applied_row_amount'));
	$weee_tax_applied_row_amount->appendChild($dom->createTextNode('0')); //Always 0
	$weee_tax_disposition = $item->appendChild($dom->createElement('weee_tax_disposition'));
	$weee_tax_disposition->appendChild($dom->createTextNode('0')); //Always 0
	$weee_tax_row_disposition = $item->appendChild($dom->createElement('weee_tax_row_disposition'));
	$weee_tax_row_disposition->appendChild($dom->createTextNode('0')); //Always 0
	$weight = $item->appendChild($dom->createElement('weight'));
	$weight->appendChild($dom->createTextNode('0'));

	//Add simple
	$item = $items->appendChild($dom->createElement('item'));
	
	//Set variables
	$base_original_priceValue = '0.0000';
	$base_priceValue = '0.0000';
	$base_row_totalValue = '0.0000';
	$real_nameValue = $itemResult['lineItemDescription'];
	$nameValue = $itemResult['lineItemDescription'];
	$original_priceValue = '0.0000';
	$priceValue = '0.0000';
	$qty_orderedValue = $itemResult['qtyOrdered'];
	$row_totalValue = '0.0000';
	$real_skuValue = $itemResult['productCode'];
	$skuValue = "Product " . $itemNumber . "-OFFLINE";
	$parent_skuValue = 'Product ' . $itemNumber;//Just for simple
	
	fwrite($transactionLogHandle, "      ->ADDING SIMPLE       : " . $itemNumber . " -> " . $real_skuValue . "\n");

	$tax_percentValue = '0.00';
	$base_price_incl_taxValue = '0.0000';
	$base_row_total_incl_taxValue = '0.0000';
	$base_tax_amountValue = '0.0000';
	$price_incl_taxValue = '0.0000';
	$row_total_incl_taxValue = '0.0000';
	$tax_amountValue = '0.0000';

	//Create line item
	$amount_refunded = $item->appendChild($dom->createElement('amount_refunded'));
	$amount_refunded->appendChild($dom->createTextNode('0')); //Always 0
	$applied_rule_ids = $item->appendChild($dom->createElement('applied_rule_ids'));
	$applied_rule_ids->appendChild($dom->createTextNode(''));
	$base_amount_refunded = $item->appendChild($dom->createElement('base_amount_refunded'));
	$base_amount_refunded->appendChild($dom->createTextNode('0')); //Always 0
	$base_cost = $item->appendChild($dom->createElement('base_cost'));
	$base_cost->appendChild($dom->createTextNode(''));
	$base_discount_amount = $item->appendChild($dom->createElement('base_discount_amount'));
	$base_discount_amount->appendChild($dom->createTextNode('0')); //Always 0
	$base_discount_invoiced = $item->appendChild($dom->createElement('base_discount_invoiced'));
	$base_discount_invoiced->appendChild($dom->createTextNode('0')); //Always 0
	$base_hidden_tax_amount = $item->appendChild($dom->createElement('base_hidden_tax_amount'));
	$base_hidden_tax_amount->appendChild($dom->createTextNode(''));
	$base_hidden_tax_invoiced = $item->appendChild($dom->createElement('base_hidden_tax_invoiced'));
	$base_hidden_tax_invoiced->appendChild($dom->createTextNode(''));
	$base_hidden_tax_refunded = $item->appendChild($dom->createElement('base_hidden_tax_refunded'));
	$base_hidden_tax_refunded->appendChild($dom->createTextNode(''));
	$base_original_price = $item->appendChild($dom->createElement('base_original_price'));
	$base_original_price->appendChild($dom->createTextNode($base_original_priceValue));
	$base_price = $item->appendChild($dom->createElement('base_price'));
	$base_price->appendChild($dom->createTextNode($base_priceValue));
	$base_price_incl_tax = $item->appendChild($dom->createElement('base_price_incl_tax'));
	$base_price_incl_tax->appendChild($dom->createTextNode($base_price_incl_taxValue));
	$base_row_invoiced = $item->appendChild($dom->createElement('base_row_invoiced'));
	$base_row_invoiced->appendChild($dom->createTextNode('0'));
	$base_row_total = $item->appendChild($dom->createElement('base_row_total'));
	$base_row_total->appendChild($dom->createTextNode($base_row_totalValue));
	$base_row_total_incl_tax = $item->appendChild($dom->createElement('base_row_total_incl_tax'));
	$base_row_total_incl_tax->appendChild($dom->createTextNode($base_row_total_incl_taxValue));
	$base_tax_amount = $item->appendChild($dom->createElement('base_tax_amount'));
	$base_tax_amount->appendChild($dom->createTextNode($base_tax_amountValue));
	$base_tax_before_discount = $item->appendChild($dom->createElement('base_tax_before_discount'));
	$base_tax_before_discount->appendChild($dom->createTextNode(''));
	$base_tax_invoiced = $item->appendChild($dom->createElement('base_tax_invoiced'));
	$base_tax_invoiced->appendChild($dom->createTextNode('0')); //Always 0
	$base_weee_tax_applied_amount = $item->appendChild($dom->createElement('base_weee_tax_applied_amount'));
	$base_weee_tax_applied_amount->appendChild($dom->createTextNode('0')); //Always 0
	$base_weee_tax_applied_row_amount = $item->appendChild($dom->createElement('base_weee_tax_applied_row_amount'));
	$base_weee_tax_applied_row_amount->appendChild($dom->createTextNode('0')); //Always 0
	$base_weee_tax_disposition = $item->appendChild($dom->createElement('base_weee_tax_disposition'));
	$base_weee_tax_disposition->appendChild($dom->createTextNode('0')); //Always 0
	$base_weee_tax_row_disposition = $item->appendChild($dom->createElement('base_weee_tax_row_disposition'));
	$base_weee_tax_row_disposition->appendChild($dom->createTextNode('0')); //Always 0
	$description = $item->appendChild($dom->createElement('description'));
	$description->appendChild($dom->createTextNode(''));
	$discount_amount = $item->appendChild($dom->createElement('discount_amount'));
	$discount_amount->appendChild($dom->createTextNode('0')); //Always 0
	$discount_invoiced = $item->appendChild($dom->createElement('discount_invoiced'));
	$discount_invoiced->appendChild($dom->createTextNode('0')); //Always 0
	$discount_percent = $item->appendChild($dom->createElement('discount_percent'));
	$discount_percent->appendChild($dom->createTextNode('0')); //Always 0
	$free_shipping = $item->appendChild($dom->createElement('free_shipping'));
	$free_shipping->appendChild($dom->createTextNode('0')); //Always 0
	$hidden_tax_amount = $item->appendChild($dom->createElement('hidden_tax_amount'));
	$hidden_tax_amount->appendChild($dom->createTextNode(''));
	$hidden_tax_canceled = $item->appendChild($dom->createElement('hidden_tax_canceled'));
	$hidden_tax_canceled->appendChild($dom->createTextNode(''));
	$hidden_tax_invoiced = $item->appendChild($dom->createElement('hidden_tax_invoiced'));
	$hidden_tax_invoiced->appendChild($dom->createTextNode(''));
	$hidden_tax_refunded = $item->appendChild($dom->createElement('hidden_tax_refunded'));
	$hidden_tax_refunded->appendChild($dom->createTextNode(''));
	$is_nominal = $item->appendChild($dom->createElement('is_nominal'));
	$is_nominal->appendChild($dom->createTextNode('0')); //Always 0
	$is_qty_decimal = $item->appendChild($dom->createElement('is_qty_decimal'));
	$is_qty_decimal->appendChild($dom->createTextNode('0')); //Always 0
	$is_virtual = $item->appendChild($dom->createElement('is_virtual'));
	$is_virtual->appendChild($dom->createTextNode('0')); //Always 0
	$real_name = $item->appendChild($dom->createElement('real_name'));
	$real_name->appendChild($dom->createTextNode($real_nameValue)); //Always 0
	$name = $item->appendChild($dom->createElement('nameValue'));
	$name->appendChild($dom->createTextNode($nameValue)); //Always 0
	$no_discount = $item->appendChild($dom->createElement('no_discount'));
	$no_discount->appendChild($dom->createTextNode('0')); //Always 0
	$original_price = $item->appendChild($dom->createElement('original_price'));
	$original_price->appendChild($dom->createTextNode($original_priceValue));
	$parent_sku = $item->appendChild($dom->createElement('parent_sku'));
	$parent_sku->appendChild($dom->createTextNode($parent_skuValue));
	$price = $item->appendChild($dom->createElement('price'));
	$price->appendChild($dom->createTextNode($priceValue));
	$price_incl_tax = $item->appendChild($dom->createElement('price_incl_tax'));
	$price_incl_tax->appendChild($dom->createTextNode($price_incl_taxValue));
	$qty_backordered = $item->appendChild($dom->createElement('qty_backordered'));
	$qty_backordered->appendChild($dom->createTextNode(''));
	$qty_canceled = $item->appendChild($dom->createElement('qty_canceled'));
	$qty_canceled->appendChild($dom->createTextNode('0')); //Always 0
	$qty_invoiced = $item->appendChild($dom->createElement('qty_invoiced'));
	$qty_invoiced->appendChild($dom->createTextNode('0')); //Always 0
	$qty_ordered = $item->appendChild($dom->createElement('qty_ordered'));
	$qty_ordered->appendChild($dom->createTextNode($qty_orderedValue)); //Always 0
	$qty_refunded = $item->appendChild($dom->createElement('qty_refunded'));
	$qty_refunded->appendChild($dom->createTextNode('0')); //Always 0
	$qty_shipped = $item->appendChild($dom->createElement('qty_shipped'));
	$qty_shipped->appendChild($dom->createTextNode('0')); //Always 0
	$row_invoiced = $item->appendChild($dom->createElement('row_invoiced'));
	$row_invoiced->appendChild($dom->createTextNode('0')); //Always 0
	$row_total = $item->appendChild($dom->createElement('row_total'));
	$row_total->appendChild($dom->createTextNode($row_totalValue));
	$row_total_incl_tax = $item->appendChild($dom->createElement('row_total_incl_tax'));
	$row_total_incl_tax->appendChild($dom->createTextNode($row_total_incl_taxValue));
	$row_weight = $item->appendChild($dom->createElement('row_weight'));
	$row_weight->appendChild($dom->createTextNode('0'));
	$real_sku = $item->appendChild($dom->createElement('real_sku'));
	$real_sku->appendChild($dom->createTextNode($real_skuValue));
	$sku = $item->appendChild($dom->createElement('sku'));
	$sku->appendChild($dom->createTextNode($skuValue));
	$tax_amount = $item->appendChild($dom->createElement('tax_amount'));
	$tax_amount->appendChild($dom->createTextNode($tax_amountValue));
	$tax_before_discount = $item->appendChild($dom->createElement('tax_before_discount'));
	$tax_before_discount->appendChild($dom->createTextNode(''));
	$tax_canceled = $item->appendChild($dom->createElement('tax_canceled'));
	$tax_canceled->appendChild($dom->createTextNode(''));
	$tax_invoiced = $item->appendChild($dom->createElement('tax_invoiced'));
	$tax_invoiced->appendChild($dom->createTextNode('0'));
	$tax_percent = $item->appendChild($dom->createElement('tax_percent'));
	$tax_percent->appendChild($dom->createTextNode($tax_percentValue));
	$tax_refunded = $item->appendChild($dom->createElement('tax_refunded'));
	$tax_refunded->appendChild($dom->createTextNode(''));
	$weee_tax_applied = $item->appendChild($dom->createElement('weee_tax_applied'));
	$weee_tax_applied->appendChild($dom->createTextNode('a:0:{}')); //Always 0
	$weee_tax_applied_amount = $item->appendChild($dom->createElement('weee_tax_applied_amount'));
	$weee_tax_applied_amount->appendChild($dom->createTextNode('0')); //Always 0
	$weee_tax_applied_row_amount = $item->appendChild($dom->createElement('weee_tax_applied_row_amount'));
	$weee_tax_applied_row_amount->appendChild($dom->createTextNode('0')); //Always 0
	$weee_tax_disposition = $item->appendChild($dom->createElement('weee_tax_disposition'));
	$weee_tax_disposition->appendChild($dom->createTextNode('0')); //Always 0
	$weee_tax_row_disposition = $item->appendChild($dom->createElement('weee_tax_row_disposition'));
	$weee_tax_row_disposition->appendChild($dom->createTextNode('0')); //Always 0
	$weight = $item->appendChild($dom->createElement('weight'));
	$weight->appendChild($dom->createTextNode('0'));
	
        $itemNumber++;
    }
}
    
// Make the output pretty
$dom->formatOutput = true;

// Save the XML string
$xml = $dom->saveXML();

//Write file to post directory
$handle = fopen($toolsXmlDirectory . $orderFilename, 'w');
fwrite($handle, $xml);
fclose($handle);

fwrite($transactionLogHandle, "  ->FINISH TIME   : " . $endTime[5] . '/' . $endTime[4] . '/' . $endTime[3] . ' ' . $endTime[2] . ':' . $endTime[1] . ':' . $endTime[0] . "\n");
fwrite($transactionLogHandle, "  ->CREATED       : ORDER FILE 2 " . $orderFilename . "\n");

fwrite($transactionLogHandle, "->END PROCESSING\n");
//Close transaction log
fclose($transactionLogHandle);

//FILE 3
$realTime = realTime();
//Open transaction log
$transactionLogHandle = fopen($toolsLogsDirectory . 'migration_gen_sneakerhead_order_xml_files_transaction_log', 'a+');
fwrite($transactionLogHandle, "->BEGIN PROCESSING\n");
fwrite($transactionLogHandle, "  ->START TIME    : " . $realTime[5] . '/' . $realTime[4] . '/' . $realTime[3] . ' ' . $realTime[2] . ':' . $realTime[1] . ':' . $realTime[0] . "\n");

//ORDERS
fwrite($transactionLogHandle, "  ->GETTING ORDERS\n");

$resource = Mage::getSingleton('core/resource');
$writeConnection = $resource->getConnection('core_write');
//$startDate = '2009-10-00 00:00:00';//>=
$startDate = '2010-03-15 00:00:00';//>=
$endDate = '2010-03-15 00:00:00';//<
//10,000
//$query = "SELECT `users`.`email` AS `user_email`, `users`.`firstName` AS `user_firstname`, `users`.`lastName` AS `user_lastname`, `se_order`.* FROM `se_order` LEFT JOIN `storeorder` ON `se_order`.`yahooOrderIdNumeric` = `storeorder`.`yahooOrderIdNumeric` LEFT JOIN `users` ON `storeorder`.`uid` = `users`.`uid` WHERE `se_order`.`orderCreationDate` > '2009-10-00 00:00:00' AND `se_order`.`orderCreationDate` < '2009-12-14 00:00:00'";
//25,000
//FOLLOWING 8 QUERIES TO BE RUN SEPARATELY TO GENERATE 8 DIFFERENT FILES
//$query = "SELECT `users`.`email` AS `user_email`, `users`.`firstName` AS `user_firstname`, `users`.`lastName` AS `user_lastname`, `se_order`.* FROM `se_order` LEFT JOIN `storeorder` ON `se_order`.`yahooOrderIdNumeric` = `storeorder`.`yahooOrderIdNumeric` LEFT JOIN `users` ON `storeorder`.`uid` = `users`.`uid` WHERE `se_order`.`orderCreationDate` >= '2009-10-00 00:00:00' AND `se_order`.`orderCreationDate` < '2010-03-15 00:00:00'";//191298 -> 216253
//$query = "SELECT `users`.`email` AS `user_email`, `users`.`firstName` AS `user_firstname`, `users`.`lastName` AS `user_lastname`, `se_order`.* FROM `se_order` LEFT JOIN `storeorder` ON `se_order`.`yahooOrderIdNumeric` = `storeorder`.`yahooOrderIdNumeric` LEFT JOIN `users` ON `storeorder`.`uid` = `users`.`uid` WHERE `se_order`.`orderCreationDate` >= '2010-03-15 00:00:00' AND `se_order`.`orderCreationDate` < '2010-10-28 00:00:00'";//216254 -> 241203
$query = "SELECT `users`.`email` AS `user_email`, `users`.`firstName` AS `user_firstname`, `users`.`lastName` AS `user_lastname`, `se_order`.* FROM `se_order` LEFT JOIN `storeorder` ON `se_order`.`yahooOrderIdNumeric` = `storeorder`.`yahooOrderIdNumeric` LEFT JOIN `users` ON `storeorder`.`uid` = `users`.`uid` WHERE `se_order`.`orderCreationDate` >= '2010-10-28 00:00:00' AND `se_order`.`orderCreationDate` < '2011-02-27 00:00:00'";//241204 -> 266066
//$query = "SELECT `users`.`email` AS `user_email`, `users`.`firstName` AS `user_firstname`, `users`.`lastName` AS `user_lastname`, `se_order`.* FROM `se_order` LEFT JOIN `storeorder` ON `se_order`.`yahooOrderIdNumeric` = `storeorder`.`yahooOrderIdNumeric` LEFT JOIN `users` ON `storeorder`.`uid` = `users`.`uid` WHERE `se_order`.`orderCreationDate` >= '2011-02-27 00:00:00' AND `se_order`.`orderCreationDate` < '2011-06-27 00:00:00'";//266067 -> 291019
//$query = "SELECT `users`.`email` AS `user_email`, `users`.`firstName` AS `user_firstname`, `users`.`lastName` AS `user_lastname`, `se_order`.* FROM `se_order` LEFT JOIN `storeorder` ON `se_order`.`yahooOrderIdNumeric` = `storeorder`.`yahooOrderIdNumeric` LEFT JOIN `users` ON `storeorder`.`uid` = `users`.`uid` WHERE `se_order`.`orderCreationDate` >= '2011-06-27 00:00:00' AND `se_order`.`orderCreationDate` < '2011-12-09 00:00:00'";//291020 -> 315244
//$query = "SELECT `users`.`email` AS `user_email`, `users`.`firstName` AS `user_firstname`, `users`.`lastName` AS `user_lastname`, `se_order`.* FROM `se_order` LEFT JOIN `storeorder` ON `se_order`.`yahooOrderIdNumeric` = `storeorder`.`yahooOrderIdNumeric` LEFT JOIN `users` ON `storeorder`.`uid` = `users`.`uid` WHERE `se_order`.`orderCreationDate` >= '2011-12-09 00:00:00' AND `se_order`.`orderCreationDate` < '2012-04-24 00:00:00'";//315245 -> 340092
//$query = "SELECT `users`.`email` AS `user_email`, `users`.`firstName` AS `user_firstname`, `users`.`lastName` AS `user_lastname`, `se_order`.* FROM `se_order` LEFT JOIN `storeorder` ON `se_order`.`yahooOrderIdNumeric` = `storeorder`.`yahooOrderIdNumeric` LEFT JOIN `users` ON `storeorder`.`uid` = `users`.`uid` WHERE `se_order`.`orderCreationDate` >= '2012-04-24 00:00:00' AND `se_order`.`orderCreationDate` < '2012-10-04 00:00:00'";//340093 -> 364330
//$query = "SELECT `users`.`email` AS `user_email`, `users`.`firstName` AS `user_firstname`, `users`.`lastName` AS `user_lastname`, `se_order`.* FROM `se_order` LEFT JOIN `storeorder` ON `se_order`.`yahooOrderIdNumeric` = `storeorder`.`yahooOrderIdNumeric` LEFT JOIN `users` ON `storeorder`.`uid` = `users`.`uid` WHERE `se_order`.`orderCreationDate` >= '2012-10-04 00:00:00' AND `se_order`.`orderCreationDate` < '2013-02-16 00:00:00'";//364331 -> ???
//$query = "SELECT `users`.`email` AS `user_email`, `users`.`firstName` AS `user_firstname`, `users`.`lastName` AS `user_lastname`, `se_order`.* FROM `se_order` LEFT JOIN `storeorder` ON `se_order`.`yahooOrderIdNumeric` = `storeorder`.`yahooOrderIdNumeric` LEFT JOIN `users` ON `storeorder`.`uid` = `users`.`uid` WHERE `se_order`.`orderCreationDate` >= '2013-02-16 00:00:00' AND `se_order`.`orderCreationDate` < '2013-04-23 00:00:00'";//??? -> ???
//$query = "SELECT `users`.`email` AS `user_email`, `users`.`firstName` AS `user_firstname`, `users`.`lastName` AS `user_lastname`, `se_order`.* FROM `se_order` LEFT JOIN `storeorder` ON `se_order`.`yahooOrderIdNumeric` = `storeorder`.`yahooOrderIdNumeric` LEFT JOIN `users` ON `storeorder`.`uid` = `users`.`uid` WHERE `se_order`.`orderCreationDate` >= '2013-04-23 00:00:00'"; //??? -> current (???)

$results = $writeConnection->query($query);
$orderFilename = "order_3_" . $realTime[5] . $realTime[4] . $realTime[3] . '_' . $realTime[2] . $realTime[1] . $realTime[0] . ".xml";
//Creates XML string and XML document from the DOM representation
$dom = new DomDocument('1.0');

$orders = $dom->appendChild($dom->createElement('orders'));
foreach ($results as $result) {
    
    //Add order data
    fwrite($transactionLogHandle, "    ->ADDING ORDER NUMBER   : " . $result['yahooOrderIdNumeric'] . "\n");
    
    // Set some variables
    $base_discount_amountValue = (is_null($result['discount'])) ? '0.0000' : $result['discount'];//appears to be actual total discount
    $base_grand_totalValue = (is_null($result['finalGrandTotal'])) ? '0.0000' : $result['finalGrandTotal'];
    $base_shipping_amountValue = (is_null($result['shippingCost'])) ? '0.0000' : $result['shippingCost'];
    $base_shipping_incl_taxValue = (is_null($result['shippingCost'])) ? '0.0000' : $result['shippingCost'];
    $base_subtotalValue = (is_null($result['finalSubTotal'])) ? '0.0000' : $result['finalSubTotal'];
    $base_subtotal_incl_taxValue = (is_null($result['finalSubTotal'])) ? '0.0000' : $result['finalSubTotal'];
    $base_tax_amountValue = (is_null($result['taxTotal'])) ? '0.0000' : $result['taxTotal'];

    if (!is_null($result['shipCountry']) && $result['shipState'] == 'CA') {
	if (strtolower($result['shipCountry']) == 'united states') {
	    $tax_percentValue = '8.75';
	} else {
	    $tax_percentValue = '0.00';
	}
    } else {
	$tax_percentValue = '0.00';
    }

    $base_total_dueValue = (is_null($result['finalGrandTotal'])) ? '0.0000' : $result['finalGrandTotal'];
    $real_created_atValue = (is_null($result['orderCreationDate'])) ? date("Y-m-d H:i:s") : $result['orderCreationDate'];//current date or order creation date
    $created_at_timestampValue = strtotime($real_created_atValue);//set from created date
    $customer_emailValue = (is_null($result['user_email'])) ? (is_null($result['email'])) ? '' : $result['email'] : $result['user_email'];
    $customer_firstnameValue = (is_null($result['user_firstname'])) ? (is_null($result['firstName'])) ? '' : $result['firstName'] : $result['user_firstname'];
    $customer_lastnameValue = (is_null($result['user_lastname'])) ? (is_null($result['lastName'])) ? '' : $result['lastName'] : $result['user_lastname'];
    if (is_null($result['user_firstname'])) {
	$customer_nameValue = '';
    } else {
	$customer_nameValue = $customer_firstnameValue . ' ' . $customer_lastnameValue;
    }
    $customer_nameValue = $customer_firstnameValue . ' ' . $customer_lastnameValue;
    //Lookup customer
    if ($result['user_email'] == NULL) {
	$customer_group_idValue = 0;
    } else {
	$customerQuery = "SELECT `entity_id` FROM `customer_entity` WHERE `email` = '" . $result['user_email'] . "'";
	$customerResults = $writeConnection->query($customerQuery);
	$customerFound = NULL;
	foreach ($customerResults as $customerResult) {  
	    $customerFound = 1;
	}
	if (!$customerFound) {
	    fwrite($transactionLogHandle, "    ->CUSTOMER NOT FOUND    : " . $result['yahooOrderIdNumeric'] . "\n");	    
	    $customer_group_idValue = 0;
	} else {
	    $customer_group_idValue = 1;
	}
    }
    
    $discount_amountValue = (is_null($result['discount'])) ? '0.0000' : $result['discount'];//appears to be actual total discount
    $grand_totalValue = (is_null($result['finalGrandTotal'])) ? '0.0000' : $result['finalGrandTotal'];
    $increment_idValue = $result['yahooOrderIdNumeric'];//import script adds value to 600000000
    $shipping_amountValue = (is_null($result['shippingCost'])) ? '0.0000' : $result['shippingCost'];
    $shipping_incl_taxValue = (is_null($result['shippingCost'])) ? '0.0000' : $result['shippingCost'];
    switch ($result['shippingMethod']) {
	case 'UPS Ground (3-7 Business Days)':
	    $shipping_methodValue = 'ups_03';
	    break;
	case 'APO & FPO Addresses (5-30 Business Days by USPS)':
	    $shipping_methodValue = 'usps_Priority Mail';
	    break;
	case 'UPS Next Day Air (2-3 Business Days)':
	    $shipping_methodValue = 'ups_01';
	    break;
	case '"Alaska, Hawaii, U.S. Virgin Islands & Puerto Rico':
	    $shipping_methodValue = 'usps_Priority Mail';
	    break;
	case 'UPS 2nd Day Air (3-4 Business Days)':
	    $shipping_methodValue = 'ups_02';
	    break;
	case 'International Express (Shipped with Shoebox)':
	    $shipping_methodValue = 'ups_08';
	    break;
	case 'International Express (Shipped without Shoebox)':
	    $shipping_methodValue = 'ups_08';
	    break;
	case 'USPS Priority Mail (4-5 Business Days)':
	    $shipping_methodValue = 'usps_Priority Mail';
	    break;
	case 'UPS 3 Day Select (4-5 Business Days)':
	    $shipping_methodValue = 'ups_12';
	    break;
	case 'EMS - International':
	    $shipping_methodValue = 'ups_08';
	    break;
	case 'Canada Express (4-7 Business Days)':
	    $shipping_methodValue = 'ups_08';
	    break;
	case 'EMS Canada':
	    $shipping_methodValue = 'ups_08';
	    break;
	case 'Christmas Express (Delivered by Dec. 24th)':
	    $shipping_methodValue = 'ups_03';
	    break;
	case 'COD Ground':
	    $shipping_methodValue = 'ups_03';
	    break;
	case 'COD Overnight':
	    $shipping_methodValue = 'ups_01';
	    break;
	case 'Free Christmas Express (Delivered by Dec. 24th)':
	    $shipping_methodValue = 'ups_03';
	    break;
	case 'New Year Express (Delivered by Dec. 31st)':
	    $shipping_methodValue = 'ups_03';
	    break;
	case 'Free UPS Ground (3-7 Business Days)':
	    $shipping_methodValue = 'ups_03';
	    break;
	case 'COD 2-Day':
	    $shipping_methodValue = 'ups_02';
	    break;
	case 'MSI International Express':
	    $shipping_methodValue = 'ups_08';
	    break;
	case 'Customer Pickup':
	    $shipping_methodValue = 'ups_03';
	    break;
	case 'UPS Ground':
	    $shipping_methodValue = 'ups_03';
	    break;
	case 'UPS 2nd Day Air':
	    $shipping_methodValue = 'ups_02';
	    break;
	case 'APO & FPO Addresses':
	    $shipping_methodValue = 'usps_Priority Mail';
	    break;
	case 'UPS Next Day Air Saver':
	    $shipping_methodValue = 'ups_01';
	    break;
	case 'UPS 3 Day Select':
	    $shipping_methodValue = 'ups_12';
	    break;
	case 'International Express':
	    $shipping_methodValue = 'ups_08';
	    break;
	case 'USPS Priority Mail':
	    $shipping_methodValue = 'usps_Priority Mail';
	    break;
	case 'Canada Express':
	    $shipping_methodValue = 'ups_08';
	    break;
	case 'UPS Next Day Air':
	    $shipping_methodValue = 'ups_01';
	    break;
	case 'Holiday Shipping (Delivered by Dec. 24th)':
	    $shipping_methodValue = 'ups_03';
	    break;
	default://case 'NULL'
	    $shipping_methodValue = '';
	    break;
    }
    
    $stateValue = 'new';//Always new -- will set on order status update
    $statusValue = 'pending';//Always Pending -- will set on order status update
    $subtotalValue = (is_null($result['finalSubTotal'])) ? '0.0000' : $result['finalSubTotal'];
    $subtotal_incl_taxValue = (is_null($result['finalSubTotal'])) ? '0.0000' : $result['finalSubTotal'];
    $tax_amountValue = (is_null($result['taxTotal'])) ? '0.0000' : $result['taxTotal'];
    $total_dueValue = (is_null($result['finalGrandTotal'])) ? '0.0000' : $result['finalGrandTotal'];

    // Get total qty
    $itemsQuery = "SELECT * FROM `se_orderitem` WHERE `yahooOrderIdNumeric` = " . $result['yahooOrderIdNumeric'];
    $itemsResult = $writeConnection->query($itemsQuery);
    $itemCount = 0;
    foreach ($itemsResult as $itemResult) {
	$itemCount += 1;//number of items not quantites
    }
    if ($itemCount == 0) {
	fwrite($transactionLogHandle, "      ->NO ITEMS FOUND      : " . $result['yahooOrderIdNumeric'] . "\n");
    }
    $total_qty_orderedValue = $itemCount . '.0000';//Derived from item qty count
    $updated_atValue = date("Y-m-d H:i:s");
    $updated_at_timestampValue = strtotime($real_created_atValue);
    $weightValue = '0.0000'; //No weight data available

    //Shipping
    $shippingCityValue = (is_null($result['shipCity'])) ? '' : $result['shipCity'];
    $shippingCountryValue = (is_null($result['shipCountry'])) ? '' : $result['shipCountry'];
    $shippingEmailValue = (is_null($result['email'])) ? '' : $result['email'];
    $shippingFirstnameValue = (is_null($result['shipName'])) ? '' : $result['shipName'];
    $shippingLastnameValue = '';
    $shippingNameValue = $result['shipName'];
    $shippingPostcodeValue = (is_null($result['shipZip'])) ? '' : $result['shipZip'];
    if (strtolower($shippingCountryValue) == 'united states') {
	$shippingRegionValue = (is_null($result['shipState'])) ? '' : strtoupper($result['shipState']);
    } else {
	$shippingRegionValue = (is_null($result['shipState'])) ? '' : $result['shipState'];
    }
    $shippingRegion_idValue = '';//Seems to work without conversion
    if ((!is_null($result['shipAddress1']) && $result['shipAddress1'] != '') && (is_null($result['shipAddress2']) || $result['shipAddress2'] == '')) {
	$shippingStreetValue = $result['shipAddress1'];
    } elseif ((!is_null($result['shipAddress1']) && $result['shipAddress1'] != '') && (!is_null($result['shipAddress2']) && $result['shipAddress2'] != '')) {
	$shippingStreetValue = $result['shipAddress2'] . '&#10;' . $result['shipAddress2']; //Include CR/LF
    } elseif ((is_null($result['shipAddress1']) || $result['shipAddress1'] == '') && (!is_null($result['shipAddress2']) && $result['shipAddress2'] != '')) {
	$shippingStreetValue = $result['shipAddress2'];
    } else {
	$shippingStreetValue = '';
    }
    $shippingTelephoneValue = (is_null($result['shipPhone'])) ? '' : $result['shipPhone'];
    
    //Billing
    $billingCityValue = (is_null($result['billCity'])) ? '' : $result['billCity'];
    $billingCountryValue = (is_null($result['billCountry'])) ? '' : $result['billCountry'];
    $billingEmailValue = (is_null($result['email'])) ? '' : $result['email'];
    $billingFirstnameValue = (is_null($result['billName'])) ? '' : $result['billName'];
    $billingLastnameValue = '';
    $billingNameValue = $result['billName'];
    $billingPostcodeValue = (is_null($result['billZip'])) ? '' : $result['billZip'];
    if (strtolower($billingCountryValue) == 'united states') {
	$billingRegionValue = (is_null($result['billState'])) ? '' : strtoupper($result['billState']);
    } else {
	$billingRegionValue = (is_null($result['billState'])) ? '' : $result['billState'];
    }
    $billingRegion_idValue = '';//Seems to work without conversion
    if ((!is_null($result['billAddress1']) && $result['billAddress1'] != '') && (is_null($result['billAddress2']) || $result['billAddress2'] == '')) {
	$billingStreetValue = $result['billAddress1'];
    } elseif ((!is_null($result['billAddress1']) && $result['billAddress1'] != '') && (!is_null($result['billAddress2']) && $result['billAddress2'] != '')) {
	$billingStreetValue = $result['billAddress2'] . '&#10;' . $result['billAddress2']; //Include CR/LF
    } elseif ((is_null($result['billAddress1']) || $result['billAddress1'] == '') && (!is_null($result['billAddress2']) && $result['billAddress2'] != '')) {
	$billingStreetValue = $result['billAddress2'];
    } else {
	$billingStreetValue = '';
    }
    $billingTelephoneValue = (is_null($result['billPhone'])) ? '' : $result['billPhone'];
    
    //Payment
    switch ($result['paymentType']) {
	case 'Visa':
	    $cc_typeValue = 'VI';
            $methodValue = 'authorizenet';
	    break;
	case 'AMEX':
	    $cc_typeValue = 'AE';
            $methodValue = 'authorizenet';
            break;
	case 'Mastercard':
	    $cc_typeValue = 'MC';
            $methodValue = 'authorizenet';
            break;
	case 'Discover':
	    $cc_typeValue = 'DI';
            $methodValue = 'authorizenet';
	    break;
	case 'Paypal':
	    $cc_typeValue = '';
            $methodValue = 'paypal_express';
	    break;
	case 'C.O.D.':
	    $cc_typeValue = '';
            $methodValue = 'free';
	    break;
	case 'GiftCert':
	    //100% payed with giftcard
	    $cc_typeValue = '';
            $methodValue = 'free';
	    break;
	default: //NULL
	    $cc_typeValue = '';
	    $methodValue = 'free';
    }
    $amount_authorizedValue = (is_null($result['finalGrandTotal'])) ? '' : $result['finalGrandTotal'];
    $amount_orderedValue = (is_null($result['finalGrandTotal'])) ? '' : $result['finalGrandTotal'];
    $base_amount_authorizedValue = (is_null($result['finalGrandTotal'])) ? '' : $result['finalGrandTotal'];
    $base_amount_orderedValue = (is_null($result['finalGrandTotal'])) ? '' : $result['finalGrandTotal'];
    $base_shipping_amountValue = (is_null($result['shippingCost'])) ? '' : $result['shippingCost'];
    $cc_approvalValue = (is_null($result['ccApprovalNumber'])) ? '' : $result['ccApprovalNumber'];
    $cc_cid_statusValue = (is_null($result['ccCvvResponse'])) ? '' : $result['ccCvvResponse'];
    $ccExpiration = (is_null($result['ccExpiration'])) ? '' : explode('/', $result['ccExpiration']);
    if (is_null($ccExpiration)) {
        $cc_exp_monthValue = '';
        $cc_exp_yearValue = '';
    } else {
        $cc_exp_monthValue = $ccExpiration[0];
        $cc_exp_yearValue = $ccExpiration[1];
    }
    $cc_last4Value = (is_null($result['ccExpiration'])) ? '' : '****';//data not available
    $anet_trans_methodValue = '';//***
    $cc_avs_statusValue = '';//***
    $cc_trans_idValue = '';//***
    $last_trans_idValue = '';//***
    $shipping_amountValue = (is_null($result['shippingCost'])) ? '' : $result['shippingCost'];

    $order = $orders->appendChild($dom->createElement('order'));

    $adjustment_negative = $order->appendChild($dom->createElement('adjustment_negative'));
    $adjustment_negative->appendChild($dom->createTextNode(''));
    $adjustment_positive = $order->appendChild($dom->createElement('adjustment_positive'));
    $adjustment_positive->appendChild($dom->createTextNode(''));
    $applied_rule_ids = $order->appendChild($dom->createElement('applied_rule_ids'));
    $applied_rule_ids->appendChild($dom->createTextNode(''));//none used -- only used for military until migration complete
    $base_adjustment_negative = $order->appendChild($dom->createElement('base_adjustment_negative'));
    $base_adjustment_negative->appendChild($dom->createTextNode(''));
    $base_adjustment_positive = $order->appendChild($dom->createElement('base_adjustment_positive'));
    $base_adjustment_positive->appendChild($dom->createTextNode(''));
    $base_currency_code = $order->appendChild($dom->createElement('base_currency_code'));
    $base_currency_code->appendChild($dom->createTextNode('USD'));// Always USD
    $base_custbalance_amount = $order->appendChild($dom->createElement('base_custbalance_amount'));
    $base_custbalance_amount->appendChild($dom->createTextNode(''));
    $base_discount_amount = $order->appendChild($dom->createElement('base_discount_amount'));
    $base_discount_amount->appendChild($dom->createTextNode($base_discount_amountValue));
    $base_discount_canceled = $order->appendChild($dom->createElement('base_discount_canceled'));
    $base_discount_canceled->appendChild($dom->createTextNode(''));
    $base_discount_invoiced = $order->appendChild($dom->createElement('base_discount_invoiced'));
    $base_discount_invoiced->appendChild($dom->createTextNode(''));
    $base_discount_refunded = $order->appendChild($dom->createElement('base_discount_refunded'));
    $base_discount_refunded->appendChild($dom->createTextNode(''));
    $base_grand_total = $order->appendChild($dom->createElement('base_grand_total'));
    $base_grand_total->appendChild($dom->createTextNode($base_grand_totalValue));
    $base_hidden_tax_amount = $order->appendChild($dom->createElement('base_hidden_tax_amount'));
    $base_hidden_tax_amount->appendChild($dom->createTextNode(''));
    $base_hidden_tax_invoiced = $order->appendChild($dom->createElement('base_hidden_tax_invoiced'));
    $base_hidden_tax_invoiced->appendChild($dom->createTextNode(''));
    $base_hidden_tax_refunded = $order->appendChild($dom->createElement('base_hidden_tax_refunded'));
    $base_hidden_tax_refunded->appendChild($dom->createTextNode(''));
    $base_shipping_amount = $order->appendChild($dom->createElement('base_shipping_amount'));
    $base_shipping_amount->appendChild($dom->createTextNode($base_shipping_amountValue));
    $base_shipping_canceled = $order->appendChild($dom->createElement('base_shipping_canceled'));
    $base_shipping_canceled->appendChild($dom->createTextNode(''));
    $base_shipping_discount_amount = $order->appendChild($dom->createElement('base_shipping_discount_amount'));
    $base_shipping_discount_amount->appendChild($dom->createTextNode('0.0000'));//Always 0
    $base_shipping_hidden_tax_amount = $order->appendChild($dom->createElement('base_shipping_hidden_tax_amount'));
    $base_shipping_hidden_tax_amount->appendChild($dom->createTextNode(''));
    $base_shipping_incl_tax = $order->appendChild($dom->createElement('base_shipping_incl_tax'));
    $base_shipping_incl_tax->appendChild($dom->createTextNode($base_shipping_incl_taxValue));
    $base_shipping_invoiced = $order->appendChild($dom->createElement('base_shipping_invoiced'));
    $base_shipping_invoiced->appendChild($dom->createTextNode(''));
    $base_shipping_refunded = $order->appendChild($dom->createElement('base_shipping_refunded'));
    $base_shipping_refunded->appendChild($dom->createTextNode(''));
    $base_shipping_tax_amount = $order->appendChild($dom->createElement('base_shipping_tax_amount'));
    $base_shipping_tax_amount->appendChild($dom->createTextNode('0.0000'));//Always 0
    $base_shipping_tax_refunded = $order->appendChild($dom->createElement('base_shipping_tax_refunded'));
    $base_shipping_tax_refunded->appendChild($dom->createTextNode(''));
    $base_subtotal = $order->appendChild($dom->createElement('base_subtotal'));
    $base_subtotal->appendChild($dom->createTextNode($base_subtotalValue));
    $base_subtotal_canceled = $order->appendChild($dom->createElement('base_subtotal_canceled'));
    $base_subtotal_canceled->appendChild($dom->createTextNode(''));
    $base_subtotal_incl_tax = $order->appendChild($dom->createElement('base_subtotal_incl_tax'));
    $base_subtotal_incl_tax->appendChild($dom->createTextNode($base_subtotal_incl_taxValue));
    $base_subtotal_invoiced = $order->appendChild($dom->createElement('base_subtotal_invoiced'));
    $base_subtotal_invoiced->appendChild($dom->createTextNode(''));
    $base_subtotal_refunded = $order->appendChild($dom->createElement('base_subtotal_refunded'));
    $base_subtotal_refunded->appendChild($dom->createTextNode(''));
    $base_tax_amount = $order->appendChild($dom->createElement('base_tax_amount'));
    $base_tax_amount->appendChild($dom->createTextNode($base_tax_amountValue));
    $base_tax_canceled = $order->appendChild($dom->createElement('base_tax_canceled'));
    $base_tax_canceled->appendChild($dom->createTextNode(''));
    $base_tax_invoiced = $order->appendChild($dom->createElement('base_tax_invoiced'));
    $base_tax_invoiced->appendChild($dom->createTextNode(''));
    $base_tax_refunded = $order->appendChild($dom->createElement('base_tax_refunded'));
    $base_tax_refunded->appendChild($dom->createTextNode(''));
    $base_to_global_rate = $order->appendChild($dom->createElement('base_to_global_rate'));
    $base_to_global_rate->appendChild($dom->createTextNode('1'));//Always 1
    $base_to_order_rate = $order->appendChild($dom->createElement('base_to_order_rate'));
    $base_to_order_rate->appendChild($dom->createTextNode('1'));//Always 1
    $base_total_canceled = $order->appendChild($dom->createElement('base_total_canceled'));
    $base_total_canceled->appendChild($dom->createTextNode('0.0000'));
    $base_total_due = $order->appendChild($dom->createElement('base_total_due'));
    $base_total_due->appendChild($dom->createTextNode($base_total_dueValue));
    $base_total_invoiced = $order->appendChild($dom->createElement('base_total_invoiced'));
    $base_total_invoiced->appendChild($dom->createTextNode('0.0000'));
    $base_total_invoiced_cost = $order->appendChild($dom->createElement('base_total_invoiced_cost'));
    $base_total_invoiced_cost->appendChild($dom->createTextNode(''));
    $base_total_offline_refunded = $order->appendChild($dom->createElement('base_total_offline_refunded'));
    $base_total_offline_refunded->appendChild($dom->createTextNode('0.0000'));
    $base_total_online_refunded = $order->appendChild($dom->createElement('base_total_online_refunded'));
    $base_total_online_refunded->appendChild($dom->createTextNode('0.0000'));
    $base_total_paid = $order->appendChild($dom->createElement('base_total_paid'));
    $base_total_paid->appendChild($dom->createTextNode('0.0000'));
    $base_total_qty_ordered = $order->appendChild($dom->createElement('base_total_qty_ordered'));
    $base_total_qty_ordered->appendChild($dom->createTextNode(''));//Always NULL
    $base_total_refunded = $order->appendChild($dom->createElement('base_total_refunded'));
    $base_total_refunded->appendChild($dom->createTextNode('0.0000'));
    $can_ship_partially = $order->appendChild($dom->createElement('can_ship_partially'));
    $can_ship_partially->appendChild($dom->createTextNode(''));
    $can_ship_partially_item = $order->appendChild($dom->createElement('can_ship_partially_item'));
    $can_ship_partially_item->appendChild($dom->createTextNode(''));
    $coupon_code = $order->appendChild($dom->createElement('coupon_code'));
    $coupon_code->appendChild($dom->createTextNode(''));
    $real_created_at = $order->appendChild($dom->createElement('real_created_at'));
    $real_created_at->appendChild($dom->createTextNode($real_created_atValue));
    $created_at_timestamp = $order->appendChild($dom->createElement('created_at_timestamp'));
    $created_at_timestamp->appendChild($dom->createTextNode($created_at_timestampValue));
    $custbalance_amount = $order->appendChild($dom->createElement('custbalance_amount'));
    $custbalance_amount->appendChild($dom->createTextNode(''));
    $customer_dob = $order->appendChild($dom->createElement('customer_dob'));
    $customer_dob->appendChild($dom->createTextNode(''));
    $customer_email = $order->appendChild($dom->createElement('customer_email'));
    $customer_email->appendChild($dom->createTextNode($customer_emailValue));
    $customer_firstname = $order->appendChild($dom->createElement('customer_firstname'));
    $customer_firstname->appendChild($dom->createTextNode($customer_firstnameValue));
    $customer_gender = $order->appendChild($dom->createElement('customer_gender'));
    $customer_gender->appendChild($dom->createTextNode(''));
    $customer_group_id = $order->appendChild($dom->createElement('customer_group_id'));
    $customer_group_id->appendChild($dom->createTextNode($customer_group_idValue));
    $customer_lastname = $order->appendChild($dom->createElement('customer_lastname'));
    $customer_lastname->appendChild($dom->createTextNode($customer_lastnameValue));
    $customer_middlename = $order->appendChild($dom->createElement('customer_middlename'));
    $customer_middlename->appendChild($dom->createTextNode(''));
    $customer_name = $order->appendChild($dom->createElement('customer_name'));
    $customer_name->appendChild($dom->createTextNode($customer_nameValue));
    $customer_note = $order->appendChild($dom->createElement('customer_note'));
    $customer_note->appendChild($dom->createTextNode(''));
    $customer_note_notify = $order->appendChild($dom->createElement('customer_note_notify'));
    $customer_note_notify->appendChild($dom->createTextNode('1'));
    $customer_prefix = $order->appendChild($dom->createElement('customer_prefix'));
    $customer_prefix->appendChild($dom->createTextNode(''));
    $customer_suffix = $order->appendChild($dom->createElement('customer_suffix'));
    $customer_suffix->appendChild($dom->createTextNode(''));
    $customer_taxvat = $order->appendChild($dom->createElement('customer_taxvat'));
    $customer_taxvat->appendChild($dom->createTextNode(''));
    $discount_amount = $order->appendChild($dom->createElement('discount_amount'));
    $discount_amount->appendChild($dom->createTextNode($discount_amountValue));
    $discount_canceled = $order->appendChild($dom->createElement('discount_canceled'));
    $discount_canceled->appendChild($dom->createTextNode(''));
    $discount_invoiced = $order->appendChild($dom->createElement('discount_invoiced'));
    $discount_invoiced->appendChild($dom->createTextNode(''));
    $discount_refunded = $order->appendChild($dom->createElement('discount_refunded'));
    $discount_refunded->appendChild($dom->createTextNode(''));
    $email_sent = $order->appendChild($dom->createElement('email_sent'));
    $email_sent->appendChild($dom->createTextNode('1'));//Always 1
    $ext_customer_id = $order->appendChild($dom->createElement('ext_customer_id'));
    $ext_customer_id->appendChild($dom->createTextNode(''));
    $ext_order_id = $order->appendChild($dom->createElement('ext_order_id'));
    $ext_order_id->appendChild($dom->createTextNode(''));
    $forced_do_shipment_with_invoice = $order->appendChild($dom->createElement('forced_do_shipment_with_invoice'));
    $forced_do_shipment_with_invoice->appendChild($dom->createTextNode(''));
    $global_currency_code = $order->appendChild($dom->createElement('global_currency_code'));
    $global_currency_code->appendChild($dom->createTextNode('USD'));
    $grand_total = $order->appendChild($dom->createElement('grand_total'));
    $grand_total->appendChild($dom->createTextNode($grand_totalValue));
    $hidden_tax_amount = $order->appendChild($dom->createElement('hidden_tax_amount'));
    $hidden_tax_amount->appendChild($dom->createTextNode(''));
    $hidden_tax_invoiced = $order->appendChild($dom->createElement('hidden_tax_invoiced'));
    $hidden_tax_invoiced->appendChild($dom->createTextNode(''));
    $hidden_tax_refunded = $order->appendChild($dom->createElement('hidden_tax_refunded'));
    $hidden_tax_refunded->appendChild($dom->createTextNode(''));
    $hold_before_state = $order->appendChild($dom->createElement('hold_before_state'));
    $hold_before_state->appendChild($dom->createTextNode(''));
    $hold_before_status = $order->appendChild($dom->createElement('hold_before_status'));
    $hold_before_status->appendChild($dom->createTextNode(''));
    $increment_id = $order->appendChild($dom->createElement('increment_id'));
    $increment_id->appendChild($dom->createTextNode($increment_idValue));
    $is_hold = $order->appendChild($dom->createElement('is_hold'));
    $is_hold->appendChild($dom->createTextNode(''));
    $is_multi_payment = $order->appendChild($dom->createElement('is_multi_payment'));
    $is_multi_payment->appendChild($dom->createTextNode(''));
    $is_virtual = $order->appendChild($dom->createElement('is_virtual'));
    $is_virtual->appendChild($dom->createTextNode('0'));//Always 0
    $order_currency_code = $order->appendChild($dom->createElement('order_currency_code'));
    $order_currency_code->appendChild($dom->createTextNode('USD'));
    $payment_authorization_amount = $order->appendChild($dom->createElement('payment_authorization_amount'));
    $payment_authorization_amount->appendChild($dom->createTextNode(''));
    $payment_authorization_expiration = $order->appendChild($dom->createElement('payment_authorization_expiration'));
    $payment_authorization_expiration->appendChild($dom->createTextNode(''));
    $paypal_ipn_customer_notified = $order->appendChild($dom->createElement('paypal_ipn_customer_notified'));
    $paypal_ipn_customer_notified->appendChild($dom->createTextNode(''));
    $real_order_id = $order->appendChild($dom->createElement('real_order_id'));
    $real_order_id->appendChild($dom->createTextNode(''));
    $remote_ip = $order->appendChild($dom->createElement('remote_ip'));
    $remote_ip->appendChild($dom->createTextNode(''));
    $shipping_amount = $order->appendChild($dom->createElement('shipping_amount'));
    $shipping_amount->appendChild($dom->createTextNode($shipping_amountValue));
    $shipping_canceled = $order->appendChild($dom->createElement('shipping_canceled'));
    $shipping_canceled->appendChild($dom->createTextNode(''));
    $shipping_discount_amount = $order->appendChild($dom->createElement('shipping_discount_amount'));
    $shipping_discount_amount->appendChild($dom->createTextNode('0.0000'));
    $shipping_hidden_tax_amount = $order->appendChild($dom->createElement('shipping_hidden_tax_amount'));
    $shipping_hidden_tax_amount->appendChild($dom->createTextNode(''));
    $shipping_incl_tax = $order->appendChild($dom->createElement('shipping_incl_tax'));
    $shipping_incl_tax->appendChild($dom->createTextNode($shipping_incl_taxValue));
    $shipping_invoiced = $order->appendChild($dom->createElement('shipping_invoiced'));
    $shipping_invoiced->appendChild($dom->createTextNode(''));
    $shipping_method = $order->appendChild($dom->createElement('shipping_method'));
    $shipping_method->appendChild($dom->createTextNode($shipping_methodValue));
    $shipping_refunded = $order->appendChild($dom->createElement('shipping_refunded'));
    $shipping_refunded->appendChild($dom->createTextNode(''));
    $shipping_tax_amount = $order->appendChild($dom->createElement('shipping_tax_amount'));
    $shipping_tax_amount->appendChild($dom->createTextNode('0.0000'));
    $shipping_tax_refunded = $order->appendChild($dom->createElement('shipping_tax_refunded'));
    $shipping_tax_refunded->appendChild($dom->createTextNode(''));
    $state = $order->appendChild($dom->createElement('state'));
    $state->appendChild($dom->createTextNode($stateValue));
    $status = $order->appendChild($dom->createElement('status'));
    $status->appendChild($dom->createTextNode($statusValue));
    $store = $order->appendChild($dom->createElement('store'));
    $store->appendChild($dom->createTextNode('sneakerhead_cn'));
    $subtotal = $order->appendChild($dom->createElement('subTotal'));
    $subtotal->appendChild($dom->createTextNode($subtotalValue));
    $subtotal_canceled = $order->appendChild($dom->createElement('subtotal_canceled'));
    $subtotal_canceled->appendChild($dom->createTextNode(''));
    $subtotal_incl_tax = $order->appendChild($dom->createElement('subtotal_incl_tax'));
    $subtotal_incl_tax->appendChild($dom->createTextNode($subtotal_incl_taxValue));
    $subtotal_invoiced = $order->appendChild($dom->createElement('subtotal_invoiced'));
    $subtotal_invoiced->appendChild($dom->createTextNode(''));
    $subtotal_refunded = $order->appendChild($dom->createElement('subtotal_refunded'));
    $subtotal_refunded->appendChild($dom->createTextNode(''));
    $tax_amount = $order->appendChild($dom->createElement('tax_amount'));
    $tax_amount->appendChild($dom->createTextNode($tax_amountValue));
    $tax_canceled = $order->appendChild($dom->createElement('tax_canceled'));
    $tax_canceled->appendChild($dom->createTextNode(''));
    $tax_invoiced = $order->appendChild($dom->createElement('tax_invoiced'));
    $tax_invoiced->appendChild($dom->createTextNode(''));
    $tax_percent = $order->appendChild($dom->createElement('tax_percent'));
    $tax_percent->appendChild($dom->createTextNode($tax_percentValue));
    $tax_refunded = $order->appendChild($dom->createElement('tax_refunded'));
    $tax_refunded->appendChild($dom->createTextNode(''));
    $total_canceled = $order->appendChild($dom->createElement('total_canceled'));
    $total_canceled->appendChild($dom->createTextNode('0.0000'));
    $total_due = $order->appendChild($dom->createElement('total_due'));
    $total_due->appendChild($dom->createTextNode($total_dueValue));
    $total_invoiced = $order->appendChild($dom->createElement('total_invoiced'));
    $total_invoiced->appendChild($dom->createTextNode('0.0000'));
    $total_item_count = $order->appendChild($dom->createElement('total_item_count'));
    $total_item_count->appendChild($dom->createTextNode(''));
    $total_offline_refunded = $order->appendChild($dom->createElement('total_offline_refunded'));
    $total_offline_refunded->appendChild($dom->createTextNode('0.0000'));
    $total_online_refunded = $order->appendChild($dom->createElement('total_online_refunded'));
    $total_online_refunded->appendChild($dom->createTextNode('0.0000'));
    $total_paid = $order->appendChild($dom->createElement('total_paid'));
    $total_paid->appendChild($dom->createTextNode('0.0000'));
    $total_qty_ordered = $order->appendChild($dom->createElement('total_qty_ordered'));
    $total_qty_ordered->appendChild($dom->createTextNode($total_qty_orderedValue));
    $total_refunded = $order->appendChild($dom->createElement('total_refunded'));
    $total_refunded->appendChild($dom->createTextNode('0.0000'));
    $tracking_numbers = $order->appendChild($dom->createElement('tracking_numbers'));
    $tracking_numbers->appendChild($dom->createTextNode(''));
    $updated_at = $order->appendChild($dom->createElement('updated_at'));
    $updated_at->appendChild($dom->createTextNode($updated_atValue));
    $updated_at_timestamp = $order->appendChild($dom->createElement('updated_at_timestamp'));
    $updated_at_timestamp->appendChild($dom->createTextNode($updated_at_timestampValue));
    $weight = $order->appendChild($dom->createElement('weight'));
    $weight->appendChild($dom->createTextNode($weightValue));
    $x_forwarded_for = $order->appendChild($dom->createElement('x_forwarded_for'));
    $x_forwarded_for->appendChild($dom->createTextNode(''));

    //Build shipping
    $shipping_address = $order->appendChild($dom->createElement('shipping_address'));
    
    $shippingCity = $shipping_address->appendChild($dom->createElement('city'));
    $shippingCity->appendChild($dom->createTextNode($shippingCityValue));
    $shippingCompany = $shipping_address->appendChild($dom->createElement('company'));
    $shippingCompany->appendChild($dom->createTextNode(''));
    $shippingCountry = $shipping_address->appendChild($dom->createElement('country'));
    $shippingCountry->appendChild($dom->createTextNode($shippingCountryValue));
    $shippingCountry_id = $shipping_address->appendChild($dom->createElement('country_id'));
    $shippingCountry_id->appendChild($dom->createTextNode(''));
    $shippingCountry_iso2 = $shipping_address->appendChild($dom->createElement('country_iso2'));
    $shippingCountry_iso2->appendChild($dom->createTextNode(''));
    $shippingCountry_iso3 = $shipping_address->appendChild($dom->createElement('country_iso3'));
    $shippingCountry_iso3->appendChild($dom->createTextNode(''));
    $shippingEmail = $shipping_address->appendChild($dom->createElement('email'));
    $shippingEmail->appendChild($dom->createTextNode($shippingEmailValue));
    $shippingFax = $shipping_address->appendChild($dom->createElement('fax'));
    $shippingFax->appendChild($dom->createTextNode(''));
    $shippingFirstname = $shipping_address->appendChild($dom->createElement('firstname'));
    $shippingFirstname->appendChild($dom->createTextNode($shippingFirstnameValue));
    $shippingLastname = $shipping_address->appendChild($dom->createElement('lastname'));
    $shippingLastname->appendChild($dom->createTextNode($shippingLastnameValue));
    $shippingMiddlename = $shipping_address->appendChild($dom->createElement('middlename'));
    $shippingMiddlename->appendChild($dom->createTextNode(''));
    $shippingName = $shipping_address->appendChild($dom->createElement('name'));
    $shippingName->appendChild($dom->createTextNode($shippingNameValue));
    $shippingPostcode = $shipping_address->appendChild($dom->createElement('postcode'));
    $shippingPostcode->appendChild($dom->createTextNode($shippingPostcodeValue));
    $shippingPrefix = $shipping_address->appendChild($dom->createElement('prefix'));
    $shippingPrefix->appendChild($dom->createTextNode(''));
    $shippingRegion = $shipping_address->appendChild($dom->createElement('region'));
    $shippingRegion->appendChild($dom->createTextNode($shippingRegionValue));
    $shippingRegion_id = $shipping_address->appendChild($dom->createElement('region_id'));
    $shippingRegion_id->appendChild($dom->createTextNode($shippingRegion_idValue));
    $shippingRegion_iso2 = $shipping_address->appendChild($dom->createElement('region_iso2'));
    $shippingRegion_iso2->appendChild($dom->createTextNode(''));
    $shippingStreet = $shipping_address->appendChild($dom->createElement('street'));
    $shippingStreet->appendChild($dom->createTextNode($shippingStreetValue));
    $shippingSuffix = $shipping_address->appendChild($dom->createElement('suffix'));
    $shippingSuffix->appendChild($dom->createTextNode(''));
    $shippingTelephone = $shipping_address->appendChild($dom->createElement('telephone'));
    $shippingTelephone->appendChild($dom->createTextNode($shippingTelephoneValue));

    // Build billing
    $billing_address = $order->appendChild($dom->createElement('billing_address'));
    
    $billingCity = $billing_address->appendChild($dom->createElement('city'));
    $billingCity->appendChild($dom->createTextNode($billingCityValue));
    $billingCompany = $billing_address->appendChild($dom->createElement('company'));
    $billingCompany->appendChild($dom->createTextNode(''));
    $billingCountry = $billing_address->appendChild($dom->createElement('country'));
    $billingCountry->appendChild($dom->createTextNode($billingCountryValue));
    $billingCountry_id = $billing_address->appendChild($dom->createElement('country_id'));
    $billingCountry_id->appendChild($dom->createTextNode(''));
    $billingCountry_iso2 = $billing_address->appendChild($dom->createElement('country_iso2'));
    $billingCountry_iso2->appendChild($dom->createTextNode(''));
    $billingCountry_iso3 = $billing_address->appendChild($dom->createElement('country_iso3'));
    $billingCountry_iso3->appendChild($dom->createTextNode(''));
    $billingEmail = $billing_address->appendChild($dom->createElement('email'));
    $billingEmail->appendChild($dom->createTextNode($billingEmailValue));
    $billingFax = $billing_address->appendChild($dom->createElement('fax'));
    $billingFax->appendChild($dom->createTextNode(''));
    $billingFirstname = $billing_address->appendChild($dom->createElement('firstname'));
    $billingFirstname->appendChild($dom->createTextNode($billingFirstnameValue));
    $billingLastname = $billing_address->appendChild($dom->createElement('lastname'));
    $billingLastname->appendChild($dom->createTextNode($billingLastnameValue));
    $billingMiddlename = $billing_address->appendChild($dom->createElement('middlename'));
    $billingMiddlename->appendChild($dom->createTextNode(''));
    $billingName = $billing_address->appendChild($dom->createElement('name'));
    $billingName->appendChild($dom->createTextNode($billingNameValue));
    $billingPostcode = $billing_address->appendChild($dom->createElement('postcode'));
    $billingPostcode->appendChild($dom->createTextNode($billingPostcodeValue));
    $billingPrefix = $billing_address->appendChild($dom->createElement('prefix'));
    $billingPrefix->appendChild($dom->createTextNode(''));
    $billingRegion = $billing_address->appendChild($dom->createElement('region'));
    $billingRegion->appendChild($dom->createTextNode($billingRegionValue));
    $billingRegion_id = $billing_address->appendChild($dom->createElement('region_id'));
    $billingRegion_id->appendChild($dom->createTextNode($billingRegion_idValue));
    $billingRegion_iso2 = $billing_address->appendChild($dom->createElement('region_iso2'));
    $billingRegion_iso2->appendChild($dom->createTextNode(''));
    $billingStreet = $billing_address->appendChild($dom->createElement('street'));
    $billingStreet->appendChild($dom->createTextNode($billingStreetValue));
    $billingSuffix = $billing_address->appendChild($dom->createElement('suffix'));
    $billingSuffix->appendChild($dom->createTextNode(''));
    $billingTelephone = $billing_address->appendChild($dom->createElement('telephone'));
    $billingTelephone->appendChild($dom->createTextNode($billingTelephoneValue));
    
    // Build payment

    $payment = $order->appendChild($dom->createElement('payment'));

    $account_status = $payment->appendChild($dom->createElement('account_status'));
    $account_status->appendChild($dom->createTextNode(''));
    $address_status = $payment->appendChild($dom->createElement('address_status'));
    $address_status->appendChild($dom->createTextNode(''));
    $amount = $payment->appendChild($dom->createElement('amount'));
    $amount->appendChild($dom->createTextNode(''));
    $amount_authorized = $payment->appendChild($dom->createElement('amount_authorized'));
    $amount_authorized->appendChild($dom->createTextNode($amount_authorizedValue));
    $amount_canceled = $payment->appendChild($dom->createElement('amount_canceled'));
    $amount_canceled->appendChild($dom->createTextNode(''));
    $amount_ordered = $payment->appendChild($dom->createElement('amount_ordered'));
    $amount_ordered->appendChild($dom->createTextNode($amount_orderedValue));
    $amount_paid = $payment->appendChild($dom->createElement('amount_paid'));
    $amount_paid->appendChild($dom->createTextNode(''));
    $amount_refunded = $payment->appendChild($dom->createElement('amount_refunded'));
    $amount_refunded->appendChild($dom->createTextNode(''));
    $anet_trans_method = $payment->appendChild($dom->createElement('anet_trans_method'));
    $anet_trans_method->appendChild($dom->createTextNode($anet_trans_methodValue));
    $base_amount_authorized = $payment->appendChild($dom->createElement('base_amount_authorized'));
    $base_amount_authorized->appendChild($dom->createTextNode($base_amount_authorizedValue));
    $base_amount_canceled = $payment->appendChild($dom->createElement('base_amount_canceled'));
    $base_amount_canceled->appendChild($dom->createTextNode(''));
    $base_amount_ordered = $payment->appendChild($dom->createElement('base_amount_ordered'));
    $base_amount_ordered->appendChild($dom->createTextNode($base_amount_orderedValue));
    $base_amount_paid = $payment->appendChild($dom->createElement('base_amount_paid'));
    $base_amount_paid->appendChild($dom->createTextNode(''));
    $base_amount_paid_online = $payment->appendChild($dom->createElement('base_amount_paid_online'));
    $base_amount_paid_online->appendChild($dom->createTextNode(''));
    $base_amount_refunded = $payment->appendChild($dom->createElement('base_amount_refunded'));
    $base_amount_refunded->appendChild($dom->createTextNode(''));
    $base_amount_refunded_online = $payment->appendChild($dom->createElement('base_amount_refunded_online'));
    $base_amount_refunded_online->appendChild($dom->createTextNode(''));
    $base_shipping_amount = $payment->appendChild($dom->createElement('base_shipping_amount'));
    $base_shipping_amount->appendChild($dom->createTextNode($base_shipping_amountValue));
    $base_shipping_captured = $payment->appendChild($dom->createElement('base_shipping_captured'));
    $base_shipping_captured->appendChild($dom->createTextNode(''));
    $base_shipping_refunded = $payment->appendChild($dom->createElement('base_shipping_refunded'));
    $base_shipping_refunded->appendChild($dom->createTextNode(''));
    $cc_approval = $payment->appendChild($dom->createElement('cc_approval'));
    $cc_approval->appendChild($dom->createTextNode($cc_approvalValue));
    $cc_avs_status = $payment->appendChild($dom->createElement('cc_avs_status'));
    $cc_avs_status->appendChild($dom->createTextNode($cc_avs_statusValue));
    $cc_cid_status = $payment->appendChild($dom->createElement('cc_cid_status'));
    $cc_cid_status->appendChild($dom->createTextNode($cc_cid_statusValue));
    $cc_debug_request_body = $payment->appendChild($dom->createElement('cc_debug_request_body'));
    $cc_debug_request_body->appendChild($dom->createTextNode(''));
    $cc_debug_response_body = $payment->appendChild($dom->createElement('cc_debug_response_body'));
    $cc_debug_response_body->appendChild($dom->createTextNode(''));
    $cc_debug_response_serialized = $payment->appendChild($dom->createElement('cc_debug_response_serialized'));
    $cc_debug_response_serialized->appendChild($dom->createTextNode(''));
    $cc_exp_month = $payment->appendChild($dom->createElement('cc_exp_month'));
    $cc_exp_month->appendChild($dom->createTextNode($cc_exp_monthValue));
    $cc_exp_year = $payment->appendChild($dom->createElement('cc_exp_year'));
    $cc_exp_year->appendChild($dom->createTextNode($cc_exp_yearValue));
    $cc_last4 = $payment->appendChild($dom->createElement('cc_last4'));
    $cc_last4->appendChild($dom->createTextNode($cc_last4Value));
    $cc_number_enc = $payment->appendChild($dom->createElement('cc_number_enc'));
    $cc_number_enc->appendChild($dom->createTextNode(''));
    $cc_owner = $payment->appendChild($dom->createElement('cc_owner'));
    $cc_owner->appendChild($dom->createTextNode(''));
    $cc_raw_request = $payment->appendChild($dom->createElement('cc_raw_request'));
    $cc_raw_request->appendChild($dom->createTextNode(''));
    $cc_raw_response = $payment->appendChild($dom->createElement('cc_raw_response'));
    $cc_raw_response->appendChild($dom->createTextNode(''));
    $cc_secure_verify = $payment->appendChild($dom->createElement('cc_secure_verify'));
    $cc_secure_verify->appendChild($dom->createTextNode(''));
    $cc_ss_issue = $payment->appendChild($dom->createElement('cc_ss_issue'));
    $cc_ss_issue->appendChild($dom->createTextNode(''));
    $cc_ss_start_month = $payment->appendChild($dom->createElement('cc_ss_start_month'));
    $cc_ss_start_month->appendChild($dom->createTextNode('0'));//appears to be 0 since not used
    $cc_ss_start_year = $payment->appendChild($dom->createElement('cc_ss_start_year'));
    $cc_ss_start_year->appendChild($dom->createTextNode('0'));//appears to be 0 since not used
    $cc_status = $payment->appendChild($dom->createElement('cc_status'));
    $cc_status->appendChild($dom->createTextNode(''));
    $cc_status_description = $payment->appendChild($dom->createElement('cc_status_description'));
    $cc_status_description->appendChild($dom->createTextNode(''));
    $cc_trans_id = $payment->appendChild($dom->createElement('cc_trans_id'));
    $cc_trans_id->appendChild($dom->createTextNode($cc_trans_idValue));
    $cc_type = $payment->appendChild($dom->createElement('cc_type'));
    $cc_type->appendChild($dom->createTextNode($cc_typeValue));
    $cybersource_token = $payment->appendChild($dom->createElement('cybersource_token'));
    $cybersource_token->appendChild($dom->createTextNode(''));
    $echeck_account_name = $payment->appendChild($dom->createElement('echeck_account_name'));
    $echeck_account_name->appendChild($dom->createTextNode(''));
    $echeck_account_type = $payment->appendChild($dom->createElement('echeck_account_type'));
    $echeck_account_type->appendChild($dom->createTextNode(''));
    $echeck_bank_name = $payment->appendChild($dom->createElement('echeck_bank_name'));
    $echeck_bank_name->appendChild($dom->createTextNode(''));
    $echeck_routing_number = $payment->appendChild($dom->createElement('echeck_routing_number'));
    $echeck_routing_number->appendChild($dom->createTextNode(''));
    $echeck_type = $payment->appendChild($dom->createElement('echeck_type'));
    $echeck_type->appendChild($dom->createTextNode(''));
    $flo2cash_account_id = $payment->appendChild($dom->createElement('flo2cash_account_id'));
    $flo2cash_account_id->appendChild($dom->createTextNode(''));
    $ideal_issuer_id = $payment->appendChild($dom->createElement('ideal_issuer_id'));
    $ideal_issuer_id->appendChild($dom->createTextNode(''));
    $ideal_issuer_title = $payment->appendChild($dom->createElement('ideal_issuer_title'));
    $ideal_issuer_title->appendChild($dom->createTextNode(''));
    $ideal_transaction_checked = $payment->appendChild($dom->createElement('ideal_transaction_checked'));
    $ideal_transaction_checked->appendChild($dom->createTextNode(''));
    $last_trans_id = $payment->appendChild($dom->createElement('last_trans_id'));
    $last_trans_id->appendChild($dom->createTextNode($last_trans_idValue));
    $method = $payment->appendChild($dom->createElement('method'));
    $method->appendChild($dom->createTextNode($methodValue));
    $paybox_question_number = $payment->appendChild($dom->createElement('paybox_question_number'));
    $paybox_question_number->appendChild($dom->createTextNode(''));
    $paybox_request_number = $payment->appendChild($dom->createElement('paybox_request_number'));
    $paybox_request_number->appendChild($dom->createTextNode(''));
    $po_number = $payment->appendChild($dom->createElement('po_number'));
    $po_number->appendChild($dom->createTextNode(''));
    $protection_eligibility = $payment->appendChild($dom->createElement('protection_eligibility'));
    $protection_eligibility->appendChild($dom->createTextNode(''));
    $shipping_amount = $payment->appendChild($dom->createElement('shipping_amount'));
    $shipping_amount->appendChild($dom->createTextNode($shipping_amountValue));
    $shipping_captured = $payment->appendChild($dom->createElement('shipping_captured'));
    $shipping_captured->appendChild($dom->createTextNode(''));
    $shipping_refunded = $payment->appendChild($dom->createElement('shipping_refunded'));
    $shipping_refunded->appendChild($dom->createTextNode(''));

    // Build Items
    $items = $order->appendChild($dom->createElement('items'));
    $itemsQuery = "SELECT * FROM `se_orderitem` WHERE `yahooOrderIdNumeric` = " . $result['yahooOrderIdNumeric'];
    $itemsResult = $writeConnection->query($itemsQuery);
    $itemNumber = 1;
    foreach ($itemsResult as $itemResult) {
        $item = $items->appendChild($dom->createElement('item'));

	//Set variables
	$base_original_priceValue = $itemResult['unitPrice'];
	$base_priceValue = $itemResult['unitPrice'];
	$base_row_totalValue = $itemResult['qtyOrdered'] * $itemResult['unitPrice'];
	$real_nameValue = $itemResult['lineItemDescription'];
	$nameValue = $itemResult['lineItemDescription'];
	$original_priceValue = $itemResult['unitPrice'];
	$priceValue = $itemResult['unitPrice'];
	$qty_orderedValue = $itemResult['qtyOrdered'];
	$row_totalValue = $itemResult['qtyOrdered'] * $itemResult['unitPrice'];
	$length = strlen(end(explode('-', $itemResult['productCode'])));
	$real_skuValue = substr($itemResult['productCode'], 0, -($length + 1));
        
	fwrite($transactionLogHandle, "      ->ADDING CONFIGURABLE : " . $itemNumber . " -> " . $real_skuValue . "\n");

	$skuValue = 'Product ' . $itemNumber;
	if (!is_null($result['shipCountry']) && $result['shipState'] == 'CA') {
	    if (strtolower($result['shipCountry']) == 'united states') {
		$tax_percentCalcValue = '0.0875';
		$tax_percentValue = '8.75';
		$base_price_incl_taxValue = round($priceValue + ($priceValue * $tax_percentCalcValue), 4);//
		$base_row_total_incl_taxValue = round($qty_orderedValue * ($priceValue + ($priceValue * $tax_percentCalcValue)), 4);//
		$base_tax_amountValue = round($priceValue * $tax_percentCalcValue, 4);//THIS MAY BE WRONG -- QTY or ONE
		$price_incl_taxValue = round($priceValue + ($priceValue * $tax_percentCalcValue), 4);//
		$row_total_incl_taxValue = round($qty_orderedValue * ($priceValue + ($priceValue * $tax_percentCalcValue)), 4);//
		$tax_amountValue = round($priceValue * $tax_percentCalcValue, 4);//
	    } else {
		$tax_percentValue = '0.00';
		$base_price_incl_taxValue = $priceValue;
		$base_row_total_incl_taxValue = $qty_orderedValue * $priceValue;
		$base_tax_amountValue = '0.00';
		$price_incl_taxValue = $priceValue;
		$row_total_incl_taxValue = $qty_orderedValue * $priceValue;
		$tax_amountValue = '0.00';		
	    }
	} else {
	    $tax_percentValue = '0.00';
	    $base_price_incl_taxValue = $priceValue;
	    $base_row_total_incl_taxValue = $qty_orderedValue * $priceValue;
	    $base_tax_amountValue = '0.00';
	    $price_incl_taxValue = $priceValue;
	    $row_total_incl_taxValue = $qty_orderedValue * $priceValue;
	    $tax_amountValue = '0.00';	
	}

	//Create line item
	$amount_refunded = $item->appendChild($dom->createElement('amount_refunded'));
	$amount_refunded->appendChild($dom->createTextNode('0')); //Always 0
	$applied_rule_ids = $item->appendChild($dom->createElement('applied_rule_ids'));
	$applied_rule_ids->appendChild($dom->createTextNode(''));
	$base_amount_refunded = $item->appendChild($dom->createElement('base_amount_refunded'));
	$base_amount_refunded->appendChild($dom->createTextNode('0')); //Always 0
	$base_cost = $item->appendChild($dom->createElement('base_cost'));
	$base_cost->appendChild($dom->createTextNode(''));
	$base_discount_amount = $item->appendChild($dom->createElement('base_discount_amount'));
	$base_discount_amount->appendChild($dom->createTextNode('0')); //Always 0
	$base_discount_invoiced = $item->appendChild($dom->createElement('base_discount_invoiced'));
	$base_discount_invoiced->appendChild($dom->createTextNode('0')); //Always 0
	$base_hidden_tax_amount = $item->appendChild($dom->createElement('base_hidden_tax_amount'));
	$base_hidden_tax_amount->appendChild($dom->createTextNode(''));
	$base_hidden_tax_invoiced = $item->appendChild($dom->createElement('base_hidden_tax_invoiced'));
	$base_hidden_tax_invoiced->appendChild($dom->createTextNode(''));
	$base_hidden_tax_refunded = $item->appendChild($dom->createElement('base_hidden_tax_refunded'));
	$base_hidden_tax_refunded->appendChild($dom->createTextNode(''));
	$base_original_price = $item->appendChild($dom->createElement('base_original_price'));
	$base_original_price->appendChild($dom->createTextNode($base_original_priceValue));
	$base_price = $item->appendChild($dom->createElement('base_price'));
	$base_price->appendChild($dom->createTextNode($base_priceValue));
	$base_price_incl_tax = $item->appendChild($dom->createElement('base_price_incl_tax'));
	$base_price_incl_tax->appendChild($dom->createTextNode($base_price_incl_taxValue));
	$base_row_invoiced = $item->appendChild($dom->createElement('base_row_invoiced'));
	$base_row_invoiced->appendChild($dom->createTextNode('0'));
	$base_row_total = $item->appendChild($dom->createElement('base_row_total'));
	$base_row_total->appendChild($dom->createTextNode($base_row_totalValue));
	$base_row_total_incl_tax = $item->appendChild($dom->createElement('base_row_total_incl_tax'));
	$base_row_total_incl_tax->appendChild($dom->createTextNode($base_row_total_incl_taxValue));
	$base_tax_amount = $item->appendChild($dom->createElement('base_tax_amount'));
	$base_tax_amount->appendChild($dom->createTextNode($base_tax_amountValue));
	$base_tax_before_discount = $item->appendChild($dom->createElement('base_tax_before_discount'));
	$base_tax_before_discount->appendChild($dom->createTextNode(''));
	$base_tax_invoiced = $item->appendChild($dom->createElement('base_tax_invoiced'));
	$base_tax_invoiced->appendChild($dom->createTextNode('0')); //Always 0
	$base_weee_tax_applied_amount = $item->appendChild($dom->createElement('base_weee_tax_applied_amount'));
	$base_weee_tax_applied_amount->appendChild($dom->createTextNode('0')); //Always 0
	$base_weee_tax_applied_row_amount = $item->appendChild($dom->createElement('base_weee_tax_applied_row_amount'));
	$base_weee_tax_applied_row_amount->appendChild($dom->createTextNode('0')); //Always 0
	$base_weee_tax_disposition = $item->appendChild($dom->createElement('base_weee_tax_disposition'));
	$base_weee_tax_disposition->appendChild($dom->createTextNode('0')); //Always 0
	$base_weee_tax_row_disposition = $item->appendChild($dom->createElement('base_weee_tax_row_disposition'));
	$base_weee_tax_row_disposition->appendChild($dom->createTextNode('0')); //Always 0
	$description = $item->appendChild($dom->createElement('description'));
	$description->appendChild($dom->createTextNode(''));
	$discount_amount = $item->appendChild($dom->createElement('discount_amount'));
	$discount_amount->appendChild($dom->createTextNode('0')); //Always 0
	$discount_invoiced = $item->appendChild($dom->createElement('discount_invoiced'));
	$discount_invoiced->appendChild($dom->createTextNode('0')); //Always 0
	$discount_percent = $item->appendChild($dom->createElement('discount_percent'));
	$discount_percent->appendChild($dom->createTextNode('0')); //Always 0
	$free_shipping = $item->appendChild($dom->createElement('free_shipping'));
	$free_shipping->appendChild($dom->createTextNode('0')); //Always 0
	$hidden_tax_amount = $item->appendChild($dom->createElement('hidden_tax_amount'));
	$hidden_tax_amount->appendChild($dom->createTextNode(''));
	$hidden_tax_canceled = $item->appendChild($dom->createElement('hidden_tax_canceled'));
	$hidden_tax_canceled->appendChild($dom->createTextNode(''));
	$hidden_tax_invoiced = $item->appendChild($dom->createElement('hidden_tax_invoiced'));
	$hidden_tax_invoiced->appendChild($dom->createTextNode(''));
	$hidden_tax_refunded = $item->appendChild($dom->createElement('hidden_tax_refunded'));
	$hidden_tax_refunded->appendChild($dom->createTextNode(''));
	$is_nominal = $item->appendChild($dom->createElement('is_nominal'));
	$is_nominal->appendChild($dom->createTextNode('0')); //Always 0
	$is_qty_decimal = $item->appendChild($dom->createElement('is_qty_decimal'));
	$is_qty_decimal->appendChild($dom->createTextNode('0')); //Always 0
	$is_virtual = $item->appendChild($dom->createElement('is_virtual'));
	$is_virtual->appendChild($dom->createTextNode('0')); //Always 0
	$real_name = $item->appendChild($dom->createElement('real_name'));
	$real_name->appendChild($dom->createTextNode($real_nameValue)); //Always 0
	$name = $item->appendChild($dom->createElement('name'));
	$name->appendChild($dom->createTextNode($nameValue)); //Always 0
	$no_discount = $item->appendChild($dom->createElement('no_discount'));
	$no_discount->appendChild($dom->createTextNode('0')); //Always 0
	$original_price = $item->appendChild($dom->createElement('original_price'));
	$original_price->appendChild($dom->createTextNode($original_priceValue));
	$price = $item->appendChild($dom->createElement('price'));
	$price->appendChild($dom->createTextNode($priceValue));
	$price_incl_tax = $item->appendChild($dom->createElement('price_incl_tax'));
	$price_incl_tax->appendChild($dom->createTextNode($price_incl_taxValue));
	$qty_backordered = $item->appendChild($dom->createElement('qty_backordered'));
	$qty_backordered->appendChild($dom->createTextNode(''));
	$qty_canceled = $item->appendChild($dom->createElement('qty_canceled'));
	$qty_canceled->appendChild($dom->createTextNode('0')); //Always 0
	$qty_invoiced = $item->appendChild($dom->createElement('qty_invoiced'));
	$qty_invoiced->appendChild($dom->createTextNode('0')); //Always 0
	$qty_ordered = $item->appendChild($dom->createElement('qty_ordered'));
	$qty_ordered->appendChild($dom->createTextNode($qty_orderedValue)); //Always 0
	$qty_refunded = $item->appendChild($dom->createElement('qty_refunded'));
	$qty_refunded->appendChild($dom->createTextNode('0')); //Always 0
	$qty_shipped = $item->appendChild($dom->createElement('qty_shipped'));
	$qty_shipped->appendChild($dom->createTextNode('0')); //Always 0
	$row_invoiced = $item->appendChild($dom->createElement('row_invoiced'));
	$row_invoiced->appendChild($dom->createTextNode('0')); //Always 0
	$row_total = $item->appendChild($dom->createElement('row_total'));
	$row_total->appendChild($dom->createTextNode($row_totalValue));
	$row_total_incl_tax = $item->appendChild($dom->createElement('row_total_incl_tax'));
	$row_total_incl_tax->appendChild($dom->createTextNode($row_total_incl_taxValue));
	$row_weight = $item->appendChild($dom->createElement('row_weight'));
	$row_weight->appendChild($dom->createTextNode('0'));
	$real_sku = $item->appendChild($dom->createElement('real_sku'));
	$real_sku->appendChild($dom->createTextNode($real_skuValue));
	$sku = $item->appendChild($dom->createElement('sku'));
	$sku->appendChild($dom->createTextNode($skuValue));
	$tax_amount = $item->appendChild($dom->createElement('tax_amount'));
	$tax_amount->appendChild($dom->createTextNode($tax_amountValue));
	$tax_before_discount = $item->appendChild($dom->createElement('tax_before_discount'));
	$tax_before_discount->appendChild($dom->createTextNode(''));
	$tax_canceled = $item->appendChild($dom->createElement('tax_canceled'));
	$tax_canceled->appendChild($dom->createTextNode(''));
	$tax_invoiced = $item->appendChild($dom->createElement('tax_invoiced'));
	$tax_invoiced->appendChild($dom->createTextNode('0'));
	$tax_percent = $item->appendChild($dom->createElement('tax_percent'));
	$tax_percent->appendChild($dom->createTextNode($tax_percentValue));
	$tax_refunded = $item->appendChild($dom->createElement('tax_refunded'));
	$tax_refunded->appendChild($dom->createTextNode(''));
	$weee_tax_applied = $item->appendChild($dom->createElement('weee_tax_applied'));
	$weee_tax_applied->appendChild($dom->createTextNode('a:0:{}')); //Always 0
	$weee_tax_applied_amount = $item->appendChild($dom->createElement('weee_tax_applied_amount'));
	$weee_tax_applied_amount->appendChild($dom->createTextNode('0')); //Always 0
	$weee_tax_applied_row_amount = $item->appendChild($dom->createElement('weee_tax_applied_row_amount'));
	$weee_tax_applied_row_amount->appendChild($dom->createTextNode('0')); //Always 0
	$weee_tax_disposition = $item->appendChild($dom->createElement('weee_tax_disposition'));
	$weee_tax_disposition->appendChild($dom->createTextNode('0')); //Always 0
	$weee_tax_row_disposition = $item->appendChild($dom->createElement('weee_tax_row_disposition'));
	$weee_tax_row_disposition->appendChild($dom->createTextNode('0')); //Always 0
	$weight = $item->appendChild($dom->createElement('weight'));
	$weight->appendChild($dom->createTextNode('0'));

	//Add simple
	$item = $items->appendChild($dom->createElement('item'));
	
	//Set variables
	$base_original_priceValue = '0.0000';
	$base_priceValue = '0.0000';
	$base_row_totalValue = '0.0000';
	$real_nameValue = $itemResult['lineItemDescription'];
	$nameValue = $itemResult['lineItemDescription'];
	$original_priceValue = '0.0000';
	$priceValue = '0.0000';
	$qty_orderedValue = $itemResult['qtyOrdered'];
	$row_totalValue = '0.0000';
	$real_skuValue = $itemResult['productCode'];
	$skuValue = "Product " . $itemNumber . "-OFFLINE";
	$parent_skuValue = 'Product ' . $itemNumber;//Just for simple
	
	fwrite($transactionLogHandle, "      ->ADDING SIMPLE       : " . $itemNumber . " -> " . $real_skuValue . "\n");

	$tax_percentValue = '0.00';
	$base_price_incl_taxValue = '0.0000';
	$base_row_total_incl_taxValue = '0.0000';
	$base_tax_amountValue = '0.0000';
	$price_incl_taxValue = '0.0000';
	$row_total_incl_taxValue = '0.0000';
	$tax_amountValue = '0.0000';

	//Create line item
	$amount_refunded = $item->appendChild($dom->createElement('amount_refunded'));
	$amount_refunded->appendChild($dom->createTextNode('0')); //Always 0
	$applied_rule_ids = $item->appendChild($dom->createElement('applied_rule_ids'));
	$applied_rule_ids->appendChild($dom->createTextNode(''));
	$base_amount_refunded = $item->appendChild($dom->createElement('base_amount_refunded'));
	$base_amount_refunded->appendChild($dom->createTextNode('0')); //Always 0
	$base_cost = $item->appendChild($dom->createElement('base_cost'));
	$base_cost->appendChild($dom->createTextNode(''));
	$base_discount_amount = $item->appendChild($dom->createElement('base_discount_amount'));
	$base_discount_amount->appendChild($dom->createTextNode('0')); //Always 0
	$base_discount_invoiced = $item->appendChild($dom->createElement('base_discount_invoiced'));
	$base_discount_invoiced->appendChild($dom->createTextNode('0')); //Always 0
	$base_hidden_tax_amount = $item->appendChild($dom->createElement('base_hidden_tax_amount'));
	$base_hidden_tax_amount->appendChild($dom->createTextNode(''));
	$base_hidden_tax_invoiced = $item->appendChild($dom->createElement('base_hidden_tax_invoiced'));
	$base_hidden_tax_invoiced->appendChild($dom->createTextNode(''));
	$base_hidden_tax_refunded = $item->appendChild($dom->createElement('base_hidden_tax_refunded'));
	$base_hidden_tax_refunded->appendChild($dom->createTextNode(''));
	$base_original_price = $item->appendChild($dom->createElement('base_original_price'));
	$base_original_price->appendChild($dom->createTextNode($base_original_priceValue));
	$base_price = $item->appendChild($dom->createElement('base_price'));
	$base_price->appendChild($dom->createTextNode($base_priceValue));
	$base_price_incl_tax = $item->appendChild($dom->createElement('base_price_incl_tax'));
	$base_price_incl_tax->appendChild($dom->createTextNode($base_price_incl_taxValue));
	$base_row_invoiced = $item->appendChild($dom->createElement('base_row_invoiced'));
	$base_row_invoiced->appendChild($dom->createTextNode('0'));
	$base_row_total = $item->appendChild($dom->createElement('base_row_total'));
	$base_row_total->appendChild($dom->createTextNode($base_row_totalValue));
	$base_row_total_incl_tax = $item->appendChild($dom->createElement('base_row_total_incl_tax'));
	$base_row_total_incl_tax->appendChild($dom->createTextNode($base_row_total_incl_taxValue));
	$base_tax_amount = $item->appendChild($dom->createElement('base_tax_amount'));
	$base_tax_amount->appendChild($dom->createTextNode($base_tax_amountValue));
	$base_tax_before_discount = $item->appendChild($dom->createElement('base_tax_before_discount'));
	$base_tax_before_discount->appendChild($dom->createTextNode(''));
	$base_tax_invoiced = $item->appendChild($dom->createElement('base_tax_invoiced'));
	$base_tax_invoiced->appendChild($dom->createTextNode('0')); //Always 0
	$base_weee_tax_applied_amount = $item->appendChild($dom->createElement('base_weee_tax_applied_amount'));
	$base_weee_tax_applied_amount->appendChild($dom->createTextNode('0')); //Always 0
	$base_weee_tax_applied_row_amount = $item->appendChild($dom->createElement('base_weee_tax_applied_row_amount'));
	$base_weee_tax_applied_row_amount->appendChild($dom->createTextNode('0')); //Always 0
	$base_weee_tax_disposition = $item->appendChild($dom->createElement('base_weee_tax_disposition'));
	$base_weee_tax_disposition->appendChild($dom->createTextNode('0')); //Always 0
	$base_weee_tax_row_disposition = $item->appendChild($dom->createElement('base_weee_tax_row_disposition'));
	$base_weee_tax_row_disposition->appendChild($dom->createTextNode('0')); //Always 0
	$description = $item->appendChild($dom->createElement('description'));
	$description->appendChild($dom->createTextNode(''));
	$discount_amount = $item->appendChild($dom->createElement('discount_amount'));
	$discount_amount->appendChild($dom->createTextNode('0')); //Always 0
	$discount_invoiced = $item->appendChild($dom->createElement('discount_invoiced'));
	$discount_invoiced->appendChild($dom->createTextNode('0')); //Always 0
	$discount_percent = $item->appendChild($dom->createElement('discount_percent'));
	$discount_percent->appendChild($dom->createTextNode('0')); //Always 0
	$free_shipping = $item->appendChild($dom->createElement('free_shipping'));
	$free_shipping->appendChild($dom->createTextNode('0')); //Always 0
	$hidden_tax_amount = $item->appendChild($dom->createElement('hidden_tax_amount'));
	$hidden_tax_amount->appendChild($dom->createTextNode(''));
	$hidden_tax_canceled = $item->appendChild($dom->createElement('hidden_tax_canceled'));
	$hidden_tax_canceled->appendChild($dom->createTextNode(''));
	$hidden_tax_invoiced = $item->appendChild($dom->createElement('hidden_tax_invoiced'));
	$hidden_tax_invoiced->appendChild($dom->createTextNode(''));
	$hidden_tax_refunded = $item->appendChild($dom->createElement('hidden_tax_refunded'));
	$hidden_tax_refunded->appendChild($dom->createTextNode(''));
	$is_nominal = $item->appendChild($dom->createElement('is_nominal'));
	$is_nominal->appendChild($dom->createTextNode('0')); //Always 0
	$is_qty_decimal = $item->appendChild($dom->createElement('is_qty_decimal'));
	$is_qty_decimal->appendChild($dom->createTextNode('0')); //Always 0
	$is_virtual = $item->appendChild($dom->createElement('is_virtual'));
	$is_virtual->appendChild($dom->createTextNode('0')); //Always 0
	$real_name = $item->appendChild($dom->createElement('real_name'));
	$real_name->appendChild($dom->createTextNode($real_nameValue)); //Always 0
	$name = $item->appendChild($dom->createElement('nameValue'));
	$name->appendChild($dom->createTextNode($nameValue)); //Always 0
	$no_discount = $item->appendChild($dom->createElement('no_discount'));
	$no_discount->appendChild($dom->createTextNode('0')); //Always 0
	$original_price = $item->appendChild($dom->createElement('original_price'));
	$original_price->appendChild($dom->createTextNode($original_priceValue));
	$parent_sku = $item->appendChild($dom->createElement('parent_sku'));
	$parent_sku->appendChild($dom->createTextNode($parent_skuValue));
	$price = $item->appendChild($dom->createElement('price'));
	$price->appendChild($dom->createTextNode($priceValue));
	$price_incl_tax = $item->appendChild($dom->createElement('price_incl_tax'));
	$price_incl_tax->appendChild($dom->createTextNode($price_incl_taxValue));
	$qty_backordered = $item->appendChild($dom->createElement('qty_backordered'));
	$qty_backordered->appendChild($dom->createTextNode(''));
	$qty_canceled = $item->appendChild($dom->createElement('qty_canceled'));
	$qty_canceled->appendChild($dom->createTextNode('0')); //Always 0
	$qty_invoiced = $item->appendChild($dom->createElement('qty_invoiced'));
	$qty_invoiced->appendChild($dom->createTextNode('0')); //Always 0
	$qty_ordered = $item->appendChild($dom->createElement('qty_ordered'));
	$qty_ordered->appendChild($dom->createTextNode($qty_orderedValue)); //Always 0
	$qty_refunded = $item->appendChild($dom->createElement('qty_refunded'));
	$qty_refunded->appendChild($dom->createTextNode('0')); //Always 0
	$qty_shipped = $item->appendChild($dom->createElement('qty_shipped'));
	$qty_shipped->appendChild($dom->createTextNode('0')); //Always 0
	$row_invoiced = $item->appendChild($dom->createElement('row_invoiced'));
	$row_invoiced->appendChild($dom->createTextNode('0')); //Always 0
	$row_total = $item->appendChild($dom->createElement('row_total'));
	$row_total->appendChild($dom->createTextNode($row_totalValue));
	$row_total_incl_tax = $item->appendChild($dom->createElement('row_total_incl_tax'));
	$row_total_incl_tax->appendChild($dom->createTextNode($row_total_incl_taxValue));
	$row_weight = $item->appendChild($dom->createElement('row_weight'));
	$row_weight->appendChild($dom->createTextNode('0'));
	$real_sku = $item->appendChild($dom->createElement('real_sku'));
	$real_sku->appendChild($dom->createTextNode($real_skuValue));
	$sku = $item->appendChild($dom->createElement('sku'));
	$sku->appendChild($dom->createTextNode($skuValue));
	$tax_amount = $item->appendChild($dom->createElement('tax_amount'));
	$tax_amount->appendChild($dom->createTextNode($tax_amountValue));
	$tax_before_discount = $item->appendChild($dom->createElement('tax_before_discount'));
	$tax_before_discount->appendChild($dom->createTextNode(''));
	$tax_canceled = $item->appendChild($dom->createElement('tax_canceled'));
	$tax_canceled->appendChild($dom->createTextNode(''));
	$tax_invoiced = $item->appendChild($dom->createElement('tax_invoiced'));
	$tax_invoiced->appendChild($dom->createTextNode('0'));
	$tax_percent = $item->appendChild($dom->createElement('tax_percent'));
	$tax_percent->appendChild($dom->createTextNode($tax_percentValue));
	$tax_refunded = $item->appendChild($dom->createElement('tax_refunded'));
	$tax_refunded->appendChild($dom->createTextNode(''));
	$weee_tax_applied = $item->appendChild($dom->createElement('weee_tax_applied'));
	$weee_tax_applied->appendChild($dom->createTextNode('a:0:{}')); //Always 0
	$weee_tax_applied_amount = $item->appendChild($dom->createElement('weee_tax_applied_amount'));
	$weee_tax_applied_amount->appendChild($dom->createTextNode('0')); //Always 0
	$weee_tax_applied_row_amount = $item->appendChild($dom->createElement('weee_tax_applied_row_amount'));
	$weee_tax_applied_row_amount->appendChild($dom->createTextNode('0')); //Always 0
	$weee_tax_disposition = $item->appendChild($dom->createElement('weee_tax_disposition'));
	$weee_tax_disposition->appendChild($dom->createTextNode('0')); //Always 0
	$weee_tax_row_disposition = $item->appendChild($dom->createElement('weee_tax_row_disposition'));
	$weee_tax_row_disposition->appendChild($dom->createTextNode('0')); //Always 0
	$weight = $item->appendChild($dom->createElement('weight'));
	$weight->appendChild($dom->createTextNode('0'));
	
        $itemNumber++;
    }
}

// Make the output pretty
$dom->formatOutput = true;

// Save the XML string
$xml = $dom->saveXML();

//Write file to post directory
$handle = fopen($toolsXmlDirectory . $orderFilename, 'w');
fwrite($handle, $xml);
fclose($handle);

fwrite($transactionLogHandle, "  ->FINISH TIME   : " . $endTime[5] . '/' . $endTime[4] . '/' . $endTime[3] . ' ' . $endTime[2] . ':' . $endTime[1] . ':' . $endTime[0] . "\n");
fwrite($transactionLogHandle, "  ->CREATED       : ORDER FILE 3 " . $orderFilename . "\n");

fwrite($transactionLogHandle, "->END PROCESSING\n");
//Close transaction log
fclose($transactionLogHandle);

//FILE 4
$realTime = realTime();
//Open transaction log
$transactionLogHandle = fopen($toolsLogsDirectory . 'migration_gen_sneakerhead_order_xml_files_transaction_log', 'a+');
fwrite($transactionLogHandle, "->BEGIN PROCESSING\n");
fwrite($transactionLogHandle, "  ->START TIME    : " . $realTime[5] . '/' . $realTime[4] . '/' . $realTime[3] . ' ' . $realTime[2] . ':' . $realTime[1] . ':' . $realTime[0] . "\n");

//ORDERS
fwrite($transactionLogHandle, "  ->GETTING ORDERS\n");

$resource = Mage::getSingleton('core/resource');
$writeConnection = $resource->getConnection('core_write');
//$startDate = '2009-10-00 00:00:00';//>=
$startDate = '2010-03-15 00:00:00';//>=
$endDate = '2010-03-15 00:00:00';//<
//10,000
//$query = "SELECT `users`.`email` AS `user_email`, `users`.`firstName` AS `user_firstname`, `users`.`lastName` AS `user_lastname`, `se_order`.* FROM `se_order` LEFT JOIN `storeorder` ON `se_order`.`yahooOrderIdNumeric` = `storeorder`.`yahooOrderIdNumeric` LEFT JOIN `users` ON `storeorder`.`uid` = `users`.`uid` WHERE `se_order`.`orderCreationDate` > '2009-10-00 00:00:00' AND `se_order`.`orderCreationDate` < '2009-12-14 00:00:00'";
//25,000
//FOLLOWING 8 QUERIES TO BE RUN SEPARATELY TO GENERATE 8 DIFFERENT FILES
//$query = "SELECT `users`.`email` AS `user_email`, `users`.`firstName` AS `user_firstname`, `users`.`lastName` AS `user_lastname`, `se_order`.* FROM `se_order` LEFT JOIN `storeorder` ON `se_order`.`yahooOrderIdNumeric` = `storeorder`.`yahooOrderIdNumeric` LEFT JOIN `users` ON `storeorder`.`uid` = `users`.`uid` WHERE `se_order`.`orderCreationDate` >= '2009-10-00 00:00:00' AND `se_order`.`orderCreationDate` < '2010-03-15 00:00:00'";//191298 -> 216253
//$query = "SELECT `users`.`email` AS `user_email`, `users`.`firstName` AS `user_firstname`, `users`.`lastName` AS `user_lastname`, `se_order`.* FROM `se_order` LEFT JOIN `storeorder` ON `se_order`.`yahooOrderIdNumeric` = `storeorder`.`yahooOrderIdNumeric` LEFT JOIN `users` ON `storeorder`.`uid` = `users`.`uid` WHERE `se_order`.`orderCreationDate` >= '2010-03-15 00:00:00' AND `se_order`.`orderCreationDate` < '2010-10-28 00:00:00'";//216254 -> 241203
//$query = "SELECT `users`.`email` AS `user_email`, `users`.`firstName` AS `user_firstname`, `users`.`lastName` AS `user_lastname`, `se_order`.* FROM `se_order` LEFT JOIN `storeorder` ON `se_order`.`yahooOrderIdNumeric` = `storeorder`.`yahooOrderIdNumeric` LEFT JOIN `users` ON `storeorder`.`uid` = `users`.`uid` WHERE `se_order`.`orderCreationDate` >= '2010-10-28 00:00:00' AND `se_order`.`orderCreationDate` < '2011-02-27 00:00:00'";//241204 -> 266066
$query = "SELECT `users`.`email` AS `user_email`, `users`.`firstName` AS `user_firstname`, `users`.`lastName` AS `user_lastname`, `se_order`.* FROM `se_order` LEFT JOIN `storeorder` ON `se_order`.`yahooOrderIdNumeric` = `storeorder`.`yahooOrderIdNumeric` LEFT JOIN `users` ON `storeorder`.`uid` = `users`.`uid` WHERE `se_order`.`orderCreationDate` >= '2011-02-27 00:00:00' AND `se_order`.`orderCreationDate` < '2011-06-27 00:00:00'";//266067 -> 291019
//$query = "SELECT `users`.`email` AS `user_email`, `users`.`firstName` AS `user_firstname`, `users`.`lastName` AS `user_lastname`, `se_order`.* FROM `se_order` LEFT JOIN `storeorder` ON `se_order`.`yahooOrderIdNumeric` = `storeorder`.`yahooOrderIdNumeric` LEFT JOIN `users` ON `storeorder`.`uid` = `users`.`uid` WHERE `se_order`.`orderCreationDate` >= '2011-06-27 00:00:00' AND `se_order`.`orderCreationDate` < '2011-12-09 00:00:00'";//291020 -> 315244
//$query = "SELECT `users`.`email` AS `user_email`, `users`.`firstName` AS `user_firstname`, `users`.`lastName` AS `user_lastname`, `se_order`.* FROM `se_order` LEFT JOIN `storeorder` ON `se_order`.`yahooOrderIdNumeric` = `storeorder`.`yahooOrderIdNumeric` LEFT JOIN `users` ON `storeorder`.`uid` = `users`.`uid` WHERE `se_order`.`orderCreationDate` >= '2011-12-09 00:00:00' AND `se_order`.`orderCreationDate` < '2012-04-24 00:00:00'";//315245 -> 340092
//$query = "SELECT `users`.`email` AS `user_email`, `users`.`firstName` AS `user_firstname`, `users`.`lastName` AS `user_lastname`, `se_order`.* FROM `se_order` LEFT JOIN `storeorder` ON `se_order`.`yahooOrderIdNumeric` = `storeorder`.`yahooOrderIdNumeric` LEFT JOIN `users` ON `storeorder`.`uid` = `users`.`uid` WHERE `se_order`.`orderCreationDate` >= '2012-04-24 00:00:00' AND `se_order`.`orderCreationDate` < '2012-10-04 00:00:00'";//340093 -> 364330
//$query = "SELECT `users`.`email` AS `user_email`, `users`.`firstName` AS `user_firstname`, `users`.`lastName` AS `user_lastname`, `se_order`.* FROM `se_order` LEFT JOIN `storeorder` ON `se_order`.`yahooOrderIdNumeric` = `storeorder`.`yahooOrderIdNumeric` LEFT JOIN `users` ON `storeorder`.`uid` = `users`.`uid` WHERE `se_order`.`orderCreationDate` >= '2012-10-04 00:00:00' AND `se_order`.`orderCreationDate` < '2013-02-16 00:00:00'";//364331 -> ???
//$query = "SELECT `users`.`email` AS `user_email`, `users`.`firstName` AS `user_firstname`, `users`.`lastName` AS `user_lastname`, `se_order`.* FROM `se_order` LEFT JOIN `storeorder` ON `se_order`.`yahooOrderIdNumeric` = `storeorder`.`yahooOrderIdNumeric` LEFT JOIN `users` ON `storeorder`.`uid` = `users`.`uid` WHERE `se_order`.`orderCreationDate` >= '2013-02-16 00:00:00' AND `se_order`.`orderCreationDate` < '2013-04-23 00:00:00'";//??? -> ???
//$query = "SELECT `users`.`email` AS `user_email`, `users`.`firstName` AS `user_firstname`, `users`.`lastName` AS `user_lastname`, `se_order`.* FROM `se_order` LEFT JOIN `storeorder` ON `se_order`.`yahooOrderIdNumeric` = `storeorder`.`yahooOrderIdNumeric` LEFT JOIN `users` ON `storeorder`.`uid` = `users`.`uid` WHERE `se_order`.`orderCreationDate` >= '2013-04-23 00:00:00'"; //??? -> current (???)

$results = $writeConnection->query($query);
$orderFilename = "order_4_" . $realTime[5] . $realTime[4] . $realTime[3] . '_' . $realTime[2] . $realTime[1] . $realTime[0] . ".xml";
//Creates XML string and XML document from the DOM representation
$dom = new DomDocument('1.0');

$orders = $dom->appendChild($dom->createElement('orders'));
foreach ($results as $result) {
    
    //Add order data
    fwrite($transactionLogHandle, "    ->ADDING ORDER NUMBER   : " . $result['yahooOrderIdNumeric'] . "\n");
    
    // Set some variables
    $base_discount_amountValue = (is_null($result['discount'])) ? '0.0000' : $result['discount'];//appears to be actual total discount
    $base_grand_totalValue = (is_null($result['finalGrandTotal'])) ? '0.0000' : $result['finalGrandTotal'];
    $base_shipping_amountValue = (is_null($result['shippingCost'])) ? '0.0000' : $result['shippingCost'];
    $base_shipping_incl_taxValue = (is_null($result['shippingCost'])) ? '0.0000' : $result['shippingCost'];
    $base_subtotalValue = (is_null($result['finalSubTotal'])) ? '0.0000' : $result['finalSubTotal'];
    $base_subtotal_incl_taxValue = (is_null($result['finalSubTotal'])) ? '0.0000' : $result['finalSubTotal'];
    $base_tax_amountValue = (is_null($result['taxTotal'])) ? '0.0000' : $result['taxTotal'];

    if (!is_null($result['shipCountry']) && $result['shipState'] == 'CA') {
	if (strtolower($result['shipCountry']) == 'united states') {
	    $tax_percentValue = '8.75';
	} else {
	    $tax_percentValue = '0.00';
	}
    } else {
	$tax_percentValue = '0.00';
    }

    $base_total_dueValue = (is_null($result['finalGrandTotal'])) ? '0.0000' : $result['finalGrandTotal'];
    $real_created_atValue = (is_null($result['orderCreationDate'])) ? date("Y-m-d H:i:s") : $result['orderCreationDate'];//current date or order creation date
    $created_at_timestampValue = strtotime($real_created_atValue);//set from created date
    $customer_emailValue = (is_null($result['user_email'])) ? (is_null($result['email'])) ? '' : $result['email'] : $result['user_email'];
    $customer_firstnameValue = (is_null($result['user_firstname'])) ? (is_null($result['firstName'])) ? '' : $result['firstName'] : $result['user_firstname'];
    $customer_lastnameValue = (is_null($result['user_lastname'])) ? (is_null($result['lastName'])) ? '' : $result['lastName'] : $result['user_lastname'];
    if (is_null($result['user_firstname'])) {
	$customer_nameValue = '';
    } else {
	$customer_nameValue = $customer_firstnameValue . ' ' . $customer_lastnameValue;
    }
    $customer_nameValue = $customer_firstnameValue . ' ' . $customer_lastnameValue;
    //Lookup customer
    if ($result['user_email'] == NULL) {
	$customer_group_idValue = 0;
    } else {
	$customerQuery = "SELECT `entity_id` FROM `customer_entity` WHERE `email` = '" . $result['user_email'] . "'";
	$customerResults = $writeConnection->query($customerQuery);
	$customerFound = NULL;
	foreach ($customerResults as $customerResult) {  
	    $customerFound = 1;
	}
	if (!$customerFound) {
	    fwrite($transactionLogHandle, "    ->CUSTOMER NOT FOUND    : " . $result['yahooOrderIdNumeric'] . "\n");	    
	    $customer_group_idValue = 0;
	} else {
	    $customer_group_idValue = 1;
	}
    }
    
    $discount_amountValue = (is_null($result['discount'])) ? '0.0000' : $result['discount'];//appears to be actual total discount
    $grand_totalValue = (is_null($result['finalGrandTotal'])) ? '0.0000' : $result['finalGrandTotal'];
    $increment_idValue = $result['yahooOrderIdNumeric'];//import script adds value to 600000000
    $shipping_amountValue = (is_null($result['shippingCost'])) ? '0.0000' : $result['shippingCost'];
    $shipping_incl_taxValue = (is_null($result['shippingCost'])) ? '0.0000' : $result['shippingCost'];
    switch ($result['shippingMethod']) {
	case 'UPS Ground (3-7 Business Days)':
	    $shipping_methodValue = 'ups_03';
	    break;
	case 'APO & FPO Addresses (5-30 Business Days by USPS)':
	    $shipping_methodValue = 'usps_Priority Mail';
	    break;
	case 'UPS Next Day Air (2-3 Business Days)':
	    $shipping_methodValue = 'ups_01';
	    break;
	case '"Alaska, Hawaii, U.S. Virgin Islands & Puerto Rico':
	    $shipping_methodValue = 'usps_Priority Mail';
	    break;
	case 'UPS 2nd Day Air (3-4 Business Days)':
	    $shipping_methodValue = 'ups_02';
	    break;
	case 'International Express (Shipped with Shoebox)':
	    $shipping_methodValue = 'ups_08';
	    break;
	case 'International Express (Shipped without Shoebox)':
	    $shipping_methodValue = 'ups_08';
	    break;
	case 'USPS Priority Mail (4-5 Business Days)':
	    $shipping_methodValue = 'usps_Priority Mail';
	    break;
	case 'UPS 3 Day Select (4-5 Business Days)':
	    $shipping_methodValue = 'ups_12';
	    break;
	case 'EMS - International':
	    $shipping_methodValue = 'ups_08';
	    break;
	case 'Canada Express (4-7 Business Days)':
	    $shipping_methodValue = 'ups_08';
	    break;
	case 'EMS Canada':
	    $shipping_methodValue = 'ups_08';
	    break;
	case 'Christmas Express (Delivered by Dec. 24th)':
	    $shipping_methodValue = 'ups_03';
	    break;
	case 'COD Ground':
	    $shipping_methodValue = 'ups_03';
	    break;
	case 'COD Overnight':
	    $shipping_methodValue = 'ups_01';
	    break;
	case 'Free Christmas Express (Delivered by Dec. 24th)':
	    $shipping_methodValue = 'ups_03';
	    break;
	case 'New Year Express (Delivered by Dec. 31st)':
	    $shipping_methodValue = 'ups_03';
	    break;
	case 'Free UPS Ground (3-7 Business Days)':
	    $shipping_methodValue = 'ups_03';
	    break;
	case 'COD 2-Day':
	    $shipping_methodValue = 'ups_02';
	    break;
	case 'MSI International Express':
	    $shipping_methodValue = 'ups_08';
	    break;
	case 'Customer Pickup':
	    $shipping_methodValue = 'ups_03';
	    break;
	case 'UPS Ground':
	    $shipping_methodValue = 'ups_03';
	    break;
	case 'UPS 2nd Day Air':
	    $shipping_methodValue = 'ups_02';
	    break;
	case 'APO & FPO Addresses':
	    $shipping_methodValue = 'usps_Priority Mail';
	    break;
	case 'UPS Next Day Air Saver':
	    $shipping_methodValue = 'ups_01';
	    break;
	case 'UPS 3 Day Select':
	    $shipping_methodValue = 'ups_12';
	    break;
	case 'International Express':
	    $shipping_methodValue = 'ups_08';
	    break;
	case 'USPS Priority Mail':
	    $shipping_methodValue = 'usps_Priority Mail';
	    break;
	case 'Canada Express':
	    $shipping_methodValue = 'ups_08';
	    break;
	case 'UPS Next Day Air':
	    $shipping_methodValue = 'ups_01';
	    break;
	case 'Holiday Shipping (Delivered by Dec. 24th)':
	    $shipping_methodValue = 'ups_03';
	    break;
	default://case 'NULL'
	    $shipping_methodValue = '';
	    break;
    }
    
    $stateValue = 'new';//Always new -- will set on order status update
    $statusValue = 'pending';//Always Pending -- will set on order status update
    $subtotalValue = (is_null($result['finalSubTotal'])) ? '0.0000' : $result['finalSubTotal'];
    $subtotal_incl_taxValue = (is_null($result['finalSubTotal'])) ? '0.0000' : $result['finalSubTotal'];
    $tax_amountValue = (is_null($result['taxTotal'])) ? '0.0000' : $result['taxTotal'];
    $total_dueValue = (is_null($result['finalGrandTotal'])) ? '0.0000' : $result['finalGrandTotal'];

    // Get total qty
    $itemsQuery = "SELECT * FROM `se_orderitem` WHERE `yahooOrderIdNumeric` = " . $result['yahooOrderIdNumeric'];
    $itemsResult = $writeConnection->query($itemsQuery);
    $itemCount = 0;
    foreach ($itemsResult as $itemResult) {
	$itemCount += 1;//number of items not quantites
    }
    if ($itemCount == 0) {
	fwrite($transactionLogHandle, "      ->NO ITEMS FOUND      : " . $result['yahooOrderIdNumeric'] . "\n");
    }
    $total_qty_orderedValue = $itemCount . '.0000';//Derived from item qty count
    $updated_atValue = date("Y-m-d H:i:s");
    $updated_at_timestampValue = strtotime($real_created_atValue);
    $weightValue = '0.0000'; //No weight data available

    //Shipping
    $shippingCityValue = (is_null($result['shipCity'])) ? '' : $result['shipCity'];
    $shippingCountryValue = (is_null($result['shipCountry'])) ? '' : $result['shipCountry'];
    $shippingEmailValue = (is_null($result['email'])) ? '' : $result['email'];
    $shippingFirstnameValue = (is_null($result['shipName'])) ? '' : $result['shipName'];
    $shippingLastnameValue = '';
    $shippingNameValue = $result['shipName'];
    $shippingPostcodeValue = (is_null($result['shipZip'])) ? '' : $result['shipZip'];
    if (strtolower($shippingCountryValue) == 'united states') {
	$shippingRegionValue = (is_null($result['shipState'])) ? '' : strtoupper($result['shipState']);
    } else {
	$shippingRegionValue = (is_null($result['shipState'])) ? '' : $result['shipState'];
    }
    $shippingRegion_idValue = '';//Seems to work without conversion
    if ((!is_null($result['shipAddress1']) && $result['shipAddress1'] != '') && (is_null($result['shipAddress2']) || $result['shipAddress2'] == '')) {
	$shippingStreetValue = $result['shipAddress1'];
    } elseif ((!is_null($result['shipAddress1']) && $result['shipAddress1'] != '') && (!is_null($result['shipAddress2']) && $result['shipAddress2'] != '')) {
	$shippingStreetValue = $result['shipAddress2'] . '&#10;' . $result['shipAddress2']; //Include CR/LF
    } elseif ((is_null($result['shipAddress1']) || $result['shipAddress1'] == '') && (!is_null($result['shipAddress2']) && $result['shipAddress2'] != '')) {
	$shippingStreetValue = $result['shipAddress2'];
    } else {
	$shippingStreetValue = '';
    }
    $shippingTelephoneValue = (is_null($result['shipPhone'])) ? '' : $result['shipPhone'];
    
    //Billing
    $billingCityValue = (is_null($result['billCity'])) ? '' : $result['billCity'];
    $billingCountryValue = (is_null($result['billCountry'])) ? '' : $result['billCountry'];
    $billingEmailValue = (is_null($result['email'])) ? '' : $result['email'];
    $billingFirstnameValue = (is_null($result['billName'])) ? '' : $result['billName'];
    $billingLastnameValue = '';
    $billingNameValue = $result['billName'];
    $billingPostcodeValue = (is_null($result['billZip'])) ? '' : $result['billZip'];
    if (strtolower($billingCountryValue) == 'united states') {
	$billingRegionValue = (is_null($result['billState'])) ? '' : strtoupper($result['billState']);
    } else {
	$billingRegionValue = (is_null($result['billState'])) ? '' : $result['billState'];
    }
    $billingRegion_idValue = '';//Seems to work without conversion
    if ((!is_null($result['billAddress1']) && $result['billAddress1'] != '') && (is_null($result['billAddress2']) || $result['billAddress2'] == '')) {
	$billingStreetValue = $result['billAddress1'];
    } elseif ((!is_null($result['billAddress1']) && $result['billAddress1'] != '') && (!is_null($result['billAddress2']) && $result['billAddress2'] != '')) {
	$billingStreetValue = $result['billAddress2'] . '&#10;' . $result['billAddress2']; //Include CR/LF
    } elseif ((is_null($result['billAddress1']) || $result['billAddress1'] == '') && (!is_null($result['billAddress2']) && $result['billAddress2'] != '')) {
	$billingStreetValue = $result['billAddress2'];
    } else {
	$billingStreetValue = '';
    }
    $billingTelephoneValue = (is_null($result['billPhone'])) ? '' : $result['billPhone'];
    
    //Payment
    switch ($result['paymentType']) {
	case 'Visa':
	    $cc_typeValue = 'VI';
            $methodValue = 'authorizenet';
	    break;
	case 'AMEX':
	    $cc_typeValue = 'AE';
            $methodValue = 'authorizenet';
            break;
	case 'Mastercard':
	    $cc_typeValue = 'MC';
            $methodValue = 'authorizenet';
            break;
	case 'Discover':
	    $cc_typeValue = 'DI';
            $methodValue = 'authorizenet';
	    break;
	case 'Paypal':
	    $cc_typeValue = '';
            $methodValue = 'paypal_express';
	    break;
	case 'C.O.D.':
	    $cc_typeValue = '';
            $methodValue = 'free';
	    break;
	case 'GiftCert':
	    //100% payed with giftcard
	    $cc_typeValue = '';
            $methodValue = 'free';
	    break;
	default: //NULL
	    $cc_typeValue = '';
	    $methodValue = 'free';
    }
    $amount_authorizedValue = (is_null($result['finalGrandTotal'])) ? '' : $result['finalGrandTotal'];
    $amount_orderedValue = (is_null($result['finalGrandTotal'])) ? '' : $result['finalGrandTotal'];
    $base_amount_authorizedValue = (is_null($result['finalGrandTotal'])) ? '' : $result['finalGrandTotal'];
    $base_amount_orderedValue = (is_null($result['finalGrandTotal'])) ? '' : $result['finalGrandTotal'];
    $base_shipping_amountValue = (is_null($result['shippingCost'])) ? '' : $result['shippingCost'];
    $cc_approvalValue = (is_null($result['ccApprovalNumber'])) ? '' : $result['ccApprovalNumber'];
    $cc_cid_statusValue = (is_null($result['ccCvvResponse'])) ? '' : $result['ccCvvResponse'];
    $ccExpiration = (is_null($result['ccExpiration'])) ? '' : explode('/', $result['ccExpiration']);
    if (is_null($ccExpiration)) {
        $cc_exp_monthValue = '';
        $cc_exp_yearValue = '';
    } else {
        $cc_exp_monthValue = $ccExpiration[0];
        $cc_exp_yearValue = $ccExpiration[1];
    }
    $cc_last4Value = (is_null($result['ccExpiration'])) ? '' : '****';//data not available
    $anet_trans_methodValue = '';//***
    $cc_avs_statusValue = '';//***
    $cc_trans_idValue = '';//***
    $last_trans_idValue = '';//***
    $shipping_amountValue = (is_null($result['shippingCost'])) ? '' : $result['shippingCost'];

    $order = $orders->appendChild($dom->createElement('order'));

    $adjustment_negative = $order->appendChild($dom->createElement('adjustment_negative'));
    $adjustment_negative->appendChild($dom->createTextNode(''));
    $adjustment_positive = $order->appendChild($dom->createElement('adjustment_positive'));
    $adjustment_positive->appendChild($dom->createTextNode(''));
    $applied_rule_ids = $order->appendChild($dom->createElement('applied_rule_ids'));
    $applied_rule_ids->appendChild($dom->createTextNode(''));//none used -- only used for military until migration complete
    $base_adjustment_negative = $order->appendChild($dom->createElement('base_adjustment_negative'));
    $base_adjustment_negative->appendChild($dom->createTextNode(''));
    $base_adjustment_positive = $order->appendChild($dom->createElement('base_adjustment_positive'));
    $base_adjustment_positive->appendChild($dom->createTextNode(''));
    $base_currency_code = $order->appendChild($dom->createElement('base_currency_code'));
    $base_currency_code->appendChild($dom->createTextNode('USD'));// Always USD
    $base_custbalance_amount = $order->appendChild($dom->createElement('base_custbalance_amount'));
    $base_custbalance_amount->appendChild($dom->createTextNode(''));
    $base_discount_amount = $order->appendChild($dom->createElement('base_discount_amount'));
    $base_discount_amount->appendChild($dom->createTextNode($base_discount_amountValue));
    $base_discount_canceled = $order->appendChild($dom->createElement('base_discount_canceled'));
    $base_discount_canceled->appendChild($dom->createTextNode(''));
    $base_discount_invoiced = $order->appendChild($dom->createElement('base_discount_invoiced'));
    $base_discount_invoiced->appendChild($dom->createTextNode(''));
    $base_discount_refunded = $order->appendChild($dom->createElement('base_discount_refunded'));
    $base_discount_refunded->appendChild($dom->createTextNode(''));
    $base_grand_total = $order->appendChild($dom->createElement('base_grand_total'));
    $base_grand_total->appendChild($dom->createTextNode($base_grand_totalValue));
    $base_hidden_tax_amount = $order->appendChild($dom->createElement('base_hidden_tax_amount'));
    $base_hidden_tax_amount->appendChild($dom->createTextNode(''));
    $base_hidden_tax_invoiced = $order->appendChild($dom->createElement('base_hidden_tax_invoiced'));
    $base_hidden_tax_invoiced->appendChild($dom->createTextNode(''));
    $base_hidden_tax_refunded = $order->appendChild($dom->createElement('base_hidden_tax_refunded'));
    $base_hidden_tax_refunded->appendChild($dom->createTextNode(''));
    $base_shipping_amount = $order->appendChild($dom->createElement('base_shipping_amount'));
    $base_shipping_amount->appendChild($dom->createTextNode($base_shipping_amountValue));
    $base_shipping_canceled = $order->appendChild($dom->createElement('base_shipping_canceled'));
    $base_shipping_canceled->appendChild($dom->createTextNode(''));
    $base_shipping_discount_amount = $order->appendChild($dom->createElement('base_shipping_discount_amount'));
    $base_shipping_discount_amount->appendChild($dom->createTextNode('0.0000'));//Always 0
    $base_shipping_hidden_tax_amount = $order->appendChild($dom->createElement('base_shipping_hidden_tax_amount'));
    $base_shipping_hidden_tax_amount->appendChild($dom->createTextNode(''));
    $base_shipping_incl_tax = $order->appendChild($dom->createElement('base_shipping_incl_tax'));
    $base_shipping_incl_tax->appendChild($dom->createTextNode($base_shipping_incl_taxValue));
    $base_shipping_invoiced = $order->appendChild($dom->createElement('base_shipping_invoiced'));
    $base_shipping_invoiced->appendChild($dom->createTextNode(''));
    $base_shipping_refunded = $order->appendChild($dom->createElement('base_shipping_refunded'));
    $base_shipping_refunded->appendChild($dom->createTextNode(''));
    $base_shipping_tax_amount = $order->appendChild($dom->createElement('base_shipping_tax_amount'));
    $base_shipping_tax_amount->appendChild($dom->createTextNode('0.0000'));//Always 0
    $base_shipping_tax_refunded = $order->appendChild($dom->createElement('base_shipping_tax_refunded'));
    $base_shipping_tax_refunded->appendChild($dom->createTextNode(''));
    $base_subtotal = $order->appendChild($dom->createElement('base_subtotal'));
    $base_subtotal->appendChild($dom->createTextNode($base_subtotalValue));
    $base_subtotal_canceled = $order->appendChild($dom->createElement('base_subtotal_canceled'));
    $base_subtotal_canceled->appendChild($dom->createTextNode(''));
    $base_subtotal_incl_tax = $order->appendChild($dom->createElement('base_subtotal_incl_tax'));
    $base_subtotal_incl_tax->appendChild($dom->createTextNode($base_subtotal_incl_taxValue));
    $base_subtotal_invoiced = $order->appendChild($dom->createElement('base_subtotal_invoiced'));
    $base_subtotal_invoiced->appendChild($dom->createTextNode(''));
    $base_subtotal_refunded = $order->appendChild($dom->createElement('base_subtotal_refunded'));
    $base_subtotal_refunded->appendChild($dom->createTextNode(''));
    $base_tax_amount = $order->appendChild($dom->createElement('base_tax_amount'));
    $base_tax_amount->appendChild($dom->createTextNode($base_tax_amountValue));
    $base_tax_canceled = $order->appendChild($dom->createElement('base_tax_canceled'));
    $base_tax_canceled->appendChild($dom->createTextNode(''));
    $base_tax_invoiced = $order->appendChild($dom->createElement('base_tax_invoiced'));
    $base_tax_invoiced->appendChild($dom->createTextNode(''));
    $base_tax_refunded = $order->appendChild($dom->createElement('base_tax_refunded'));
    $base_tax_refunded->appendChild($dom->createTextNode(''));
    $base_to_global_rate = $order->appendChild($dom->createElement('base_to_global_rate'));
    $base_to_global_rate->appendChild($dom->createTextNode('1'));//Always 1
    $base_to_order_rate = $order->appendChild($dom->createElement('base_to_order_rate'));
    $base_to_order_rate->appendChild($dom->createTextNode('1'));//Always 1
    $base_total_canceled = $order->appendChild($dom->createElement('base_total_canceled'));
    $base_total_canceled->appendChild($dom->createTextNode('0.0000'));
    $base_total_due = $order->appendChild($dom->createElement('base_total_due'));
    $base_total_due->appendChild($dom->createTextNode($base_total_dueValue));
    $base_total_invoiced = $order->appendChild($dom->createElement('base_total_invoiced'));
    $base_total_invoiced->appendChild($dom->createTextNode('0.0000'));
    $base_total_invoiced_cost = $order->appendChild($dom->createElement('base_total_invoiced_cost'));
    $base_total_invoiced_cost->appendChild($dom->createTextNode(''));
    $base_total_offline_refunded = $order->appendChild($dom->createElement('base_total_offline_refunded'));
    $base_total_offline_refunded->appendChild($dom->createTextNode('0.0000'));
    $base_total_online_refunded = $order->appendChild($dom->createElement('base_total_online_refunded'));
    $base_total_online_refunded->appendChild($dom->createTextNode('0.0000'));
    $base_total_paid = $order->appendChild($dom->createElement('base_total_paid'));
    $base_total_paid->appendChild($dom->createTextNode('0.0000'));
    $base_total_qty_ordered = $order->appendChild($dom->createElement('base_total_qty_ordered'));
    $base_total_qty_ordered->appendChild($dom->createTextNode(''));//Always NULL
    $base_total_refunded = $order->appendChild($dom->createElement('base_total_refunded'));
    $base_total_refunded->appendChild($dom->createTextNode('0.0000'));
    $can_ship_partially = $order->appendChild($dom->createElement('can_ship_partially'));
    $can_ship_partially->appendChild($dom->createTextNode(''));
    $can_ship_partially_item = $order->appendChild($dom->createElement('can_ship_partially_item'));
    $can_ship_partially_item->appendChild($dom->createTextNode(''));
    $coupon_code = $order->appendChild($dom->createElement('coupon_code'));
    $coupon_code->appendChild($dom->createTextNode(''));
    $real_created_at = $order->appendChild($dom->createElement('real_created_at'));
    $real_created_at->appendChild($dom->createTextNode($real_created_atValue));
    $created_at_timestamp = $order->appendChild($dom->createElement('created_at_timestamp'));
    $created_at_timestamp->appendChild($dom->createTextNode($created_at_timestampValue));
    $custbalance_amount = $order->appendChild($dom->createElement('custbalance_amount'));
    $custbalance_amount->appendChild($dom->createTextNode(''));
    $customer_dob = $order->appendChild($dom->createElement('customer_dob'));
    $customer_dob->appendChild($dom->createTextNode(''));
    $customer_email = $order->appendChild($dom->createElement('customer_email'));
    $customer_email->appendChild($dom->createTextNode($customer_emailValue));
    $customer_firstname = $order->appendChild($dom->createElement('customer_firstname'));
    $customer_firstname->appendChild($dom->createTextNode($customer_firstnameValue));
    $customer_gender = $order->appendChild($dom->createElement('customer_gender'));
    $customer_gender->appendChild($dom->createTextNode(''));
    $customer_group_id = $order->appendChild($dom->createElement('customer_group_id'));
    $customer_group_id->appendChild($dom->createTextNode($customer_group_idValue));
    $customer_lastname = $order->appendChild($dom->createElement('customer_lastname'));
    $customer_lastname->appendChild($dom->createTextNode($customer_lastnameValue));
    $customer_middlename = $order->appendChild($dom->createElement('customer_middlename'));
    $customer_middlename->appendChild($dom->createTextNode(''));
    $customer_name = $order->appendChild($dom->createElement('customer_name'));
    $customer_name->appendChild($dom->createTextNode($customer_nameValue));
    $customer_note = $order->appendChild($dom->createElement('customer_note'));
    $customer_note->appendChild($dom->createTextNode(''));
    $customer_note_notify = $order->appendChild($dom->createElement('customer_note_notify'));
    $customer_note_notify->appendChild($dom->createTextNode('1'));
    $customer_prefix = $order->appendChild($dom->createElement('customer_prefix'));
    $customer_prefix->appendChild($dom->createTextNode(''));
    $customer_suffix = $order->appendChild($dom->createElement('customer_suffix'));
    $customer_suffix->appendChild($dom->createTextNode(''));
    $customer_taxvat = $order->appendChild($dom->createElement('customer_taxvat'));
    $customer_taxvat->appendChild($dom->createTextNode(''));
    $discount_amount = $order->appendChild($dom->createElement('discount_amount'));
    $discount_amount->appendChild($dom->createTextNode($discount_amountValue));
    $discount_canceled = $order->appendChild($dom->createElement('discount_canceled'));
    $discount_canceled->appendChild($dom->createTextNode(''));
    $discount_invoiced = $order->appendChild($dom->createElement('discount_invoiced'));
    $discount_invoiced->appendChild($dom->createTextNode(''));
    $discount_refunded = $order->appendChild($dom->createElement('discount_refunded'));
    $discount_refunded->appendChild($dom->createTextNode(''));
    $email_sent = $order->appendChild($dom->createElement('email_sent'));
    $email_sent->appendChild($dom->createTextNode('1'));//Always 1
    $ext_customer_id = $order->appendChild($dom->createElement('ext_customer_id'));
    $ext_customer_id->appendChild($dom->createTextNode(''));
    $ext_order_id = $order->appendChild($dom->createElement('ext_order_id'));
    $ext_order_id->appendChild($dom->createTextNode(''));
    $forced_do_shipment_with_invoice = $order->appendChild($dom->createElement('forced_do_shipment_with_invoice'));
    $forced_do_shipment_with_invoice->appendChild($dom->createTextNode(''));
    $global_currency_code = $order->appendChild($dom->createElement('global_currency_code'));
    $global_currency_code->appendChild($dom->createTextNode('USD'));
    $grand_total = $order->appendChild($dom->createElement('grand_total'));
    $grand_total->appendChild($dom->createTextNode($grand_totalValue));
    $hidden_tax_amount = $order->appendChild($dom->createElement('hidden_tax_amount'));
    $hidden_tax_amount->appendChild($dom->createTextNode(''));
    $hidden_tax_invoiced = $order->appendChild($dom->createElement('hidden_tax_invoiced'));
    $hidden_tax_invoiced->appendChild($dom->createTextNode(''));
    $hidden_tax_refunded = $order->appendChild($dom->createElement('hidden_tax_refunded'));
    $hidden_tax_refunded->appendChild($dom->createTextNode(''));
    $hold_before_state = $order->appendChild($dom->createElement('hold_before_state'));
    $hold_before_state->appendChild($dom->createTextNode(''));
    $hold_before_status = $order->appendChild($dom->createElement('hold_before_status'));
    $hold_before_status->appendChild($dom->createTextNode(''));
    $increment_id = $order->appendChild($dom->createElement('increment_id'));
    $increment_id->appendChild($dom->createTextNode($increment_idValue));
    $is_hold = $order->appendChild($dom->createElement('is_hold'));
    $is_hold->appendChild($dom->createTextNode(''));
    $is_multi_payment = $order->appendChild($dom->createElement('is_multi_payment'));
    $is_multi_payment->appendChild($dom->createTextNode(''));
    $is_virtual = $order->appendChild($dom->createElement('is_virtual'));
    $is_virtual->appendChild($dom->createTextNode('0'));//Always 0
    $order_currency_code = $order->appendChild($dom->createElement('order_currency_code'));
    $order_currency_code->appendChild($dom->createTextNode('USD'));
    $payment_authorization_amount = $order->appendChild($dom->createElement('payment_authorization_amount'));
    $payment_authorization_amount->appendChild($dom->createTextNode(''));
    $payment_authorization_expiration = $order->appendChild($dom->createElement('payment_authorization_expiration'));
    $payment_authorization_expiration->appendChild($dom->createTextNode(''));
    $paypal_ipn_customer_notified = $order->appendChild($dom->createElement('paypal_ipn_customer_notified'));
    $paypal_ipn_customer_notified->appendChild($dom->createTextNode(''));
    $real_order_id = $order->appendChild($dom->createElement('real_order_id'));
    $real_order_id->appendChild($dom->createTextNode(''));
    $remote_ip = $order->appendChild($dom->createElement('remote_ip'));
    $remote_ip->appendChild($dom->createTextNode(''));
    $shipping_amount = $order->appendChild($dom->createElement('shipping_amount'));
    $shipping_amount->appendChild($dom->createTextNode($shipping_amountValue));
    $shipping_canceled = $order->appendChild($dom->createElement('shipping_canceled'));
    $shipping_canceled->appendChild($dom->createTextNode(''));
    $shipping_discount_amount = $order->appendChild($dom->createElement('shipping_discount_amount'));
    $shipping_discount_amount->appendChild($dom->createTextNode('0.0000'));
    $shipping_hidden_tax_amount = $order->appendChild($dom->createElement('shipping_hidden_tax_amount'));
    $shipping_hidden_tax_amount->appendChild($dom->createTextNode(''));
    $shipping_incl_tax = $order->appendChild($dom->createElement('shipping_incl_tax'));
    $shipping_incl_tax->appendChild($dom->createTextNode($shipping_incl_taxValue));
    $shipping_invoiced = $order->appendChild($dom->createElement('shipping_invoiced'));
    $shipping_invoiced->appendChild($dom->createTextNode(''));
    $shipping_method = $order->appendChild($dom->createElement('shipping_method'));
    $shipping_method->appendChild($dom->createTextNode($shipping_methodValue));
    $shipping_refunded = $order->appendChild($dom->createElement('shipping_refunded'));
    $shipping_refunded->appendChild($dom->createTextNode(''));
    $shipping_tax_amount = $order->appendChild($dom->createElement('shipping_tax_amount'));
    $shipping_tax_amount->appendChild($dom->createTextNode('0.0000'));
    $shipping_tax_refunded = $order->appendChild($dom->createElement('shipping_tax_refunded'));
    $shipping_tax_refunded->appendChild($dom->createTextNode(''));
    $state = $order->appendChild($dom->createElement('state'));
    $state->appendChild($dom->createTextNode($stateValue));
    $status = $order->appendChild($dom->createElement('status'));
    $status->appendChild($dom->createTextNode($statusValue));
    $store = $order->appendChild($dom->createElement('store'));
    $store->appendChild($dom->createTextNode('sneakerhead_cn'));
    $subtotal = $order->appendChild($dom->createElement('subTotal'));
    $subtotal->appendChild($dom->createTextNode($subtotalValue));
    $subtotal_canceled = $order->appendChild($dom->createElement('subtotal_canceled'));
    $subtotal_canceled->appendChild($dom->createTextNode(''));
    $subtotal_incl_tax = $order->appendChild($dom->createElement('subtotal_incl_tax'));
    $subtotal_incl_tax->appendChild($dom->createTextNode($subtotal_incl_taxValue));
    $subtotal_invoiced = $order->appendChild($dom->createElement('subtotal_invoiced'));
    $subtotal_invoiced->appendChild($dom->createTextNode(''));
    $subtotal_refunded = $order->appendChild($dom->createElement('subtotal_refunded'));
    $subtotal_refunded->appendChild($dom->createTextNode(''));
    $tax_amount = $order->appendChild($dom->createElement('tax_amount'));
    $tax_amount->appendChild($dom->createTextNode($tax_amountValue));
    $tax_canceled = $order->appendChild($dom->createElement('tax_canceled'));
    $tax_canceled->appendChild($dom->createTextNode(''));
    $tax_invoiced = $order->appendChild($dom->createElement('tax_invoiced'));
    $tax_invoiced->appendChild($dom->createTextNode(''));
    $tax_percent = $order->appendChild($dom->createElement('tax_percent'));
    $tax_percent->appendChild($dom->createTextNode($tax_percentValue));
    $tax_refunded = $order->appendChild($dom->createElement('tax_refunded'));
    $tax_refunded->appendChild($dom->createTextNode(''));
    $total_canceled = $order->appendChild($dom->createElement('total_canceled'));
    $total_canceled->appendChild($dom->createTextNode('0.0000'));
    $total_due = $order->appendChild($dom->createElement('total_due'));
    $total_due->appendChild($dom->createTextNode($total_dueValue));
    $total_invoiced = $order->appendChild($dom->createElement('total_invoiced'));
    $total_invoiced->appendChild($dom->createTextNode('0.0000'));
    $total_item_count = $order->appendChild($dom->createElement('total_item_count'));
    $total_item_count->appendChild($dom->createTextNode(''));
    $total_offline_refunded = $order->appendChild($dom->createElement('total_offline_refunded'));
    $total_offline_refunded->appendChild($dom->createTextNode('0.0000'));
    $total_online_refunded = $order->appendChild($dom->createElement('total_online_refunded'));
    $total_online_refunded->appendChild($dom->createTextNode('0.0000'));
    $total_paid = $order->appendChild($dom->createElement('total_paid'));
    $total_paid->appendChild($dom->createTextNode('0.0000'));
    $total_qty_ordered = $order->appendChild($dom->createElement('total_qty_ordered'));
    $total_qty_ordered->appendChild($dom->createTextNode($total_qty_orderedValue));
    $total_refunded = $order->appendChild($dom->createElement('total_refunded'));
    $total_refunded->appendChild($dom->createTextNode('0.0000'));
    $tracking_numbers = $order->appendChild($dom->createElement('tracking_numbers'));
    $tracking_numbers->appendChild($dom->createTextNode(''));
    $updated_at = $order->appendChild($dom->createElement('updated_at'));
    $updated_at->appendChild($dom->createTextNode($updated_atValue));
    $updated_at_timestamp = $order->appendChild($dom->createElement('updated_at_timestamp'));
    $updated_at_timestamp->appendChild($dom->createTextNode($updated_at_timestampValue));
    $weight = $order->appendChild($dom->createElement('weight'));
    $weight->appendChild($dom->createTextNode($weightValue));
    $x_forwarded_for = $order->appendChild($dom->createElement('x_forwarded_for'));
    $x_forwarded_for->appendChild($dom->createTextNode(''));

    //Build shipping
    $shipping_address = $order->appendChild($dom->createElement('shipping_address'));
    
    $shippingCity = $shipping_address->appendChild($dom->createElement('city'));
    $shippingCity->appendChild($dom->createTextNode($shippingCityValue));
    $shippingCompany = $shipping_address->appendChild($dom->createElement('company'));
    $shippingCompany->appendChild($dom->createTextNode(''));
    $shippingCountry = $shipping_address->appendChild($dom->createElement('country'));
    $shippingCountry->appendChild($dom->createTextNode($shippingCountryValue));
    $shippingCountry_id = $shipping_address->appendChild($dom->createElement('country_id'));
    $shippingCountry_id->appendChild($dom->createTextNode(''));
    $shippingCountry_iso2 = $shipping_address->appendChild($dom->createElement('country_iso2'));
    $shippingCountry_iso2->appendChild($dom->createTextNode(''));
    $shippingCountry_iso3 = $shipping_address->appendChild($dom->createElement('country_iso3'));
    $shippingCountry_iso3->appendChild($dom->createTextNode(''));
    $shippingEmail = $shipping_address->appendChild($dom->createElement('email'));
    $shippingEmail->appendChild($dom->createTextNode($shippingEmailValue));
    $shippingFax = $shipping_address->appendChild($dom->createElement('fax'));
    $shippingFax->appendChild($dom->createTextNode(''));
    $shippingFirstname = $shipping_address->appendChild($dom->createElement('firstname'));
    $shippingFirstname->appendChild($dom->createTextNode($shippingFirstnameValue));
    $shippingLastname = $shipping_address->appendChild($dom->createElement('lastname'));
    $shippingLastname->appendChild($dom->createTextNode($shippingLastnameValue));
    $shippingMiddlename = $shipping_address->appendChild($dom->createElement('middlename'));
    $shippingMiddlename->appendChild($dom->createTextNode(''));
    $shippingName = $shipping_address->appendChild($dom->createElement('name'));
    $shippingName->appendChild($dom->createTextNode($shippingNameValue));
    $shippingPostcode = $shipping_address->appendChild($dom->createElement('postcode'));
    $shippingPostcode->appendChild($dom->createTextNode($shippingPostcodeValue));
    $shippingPrefix = $shipping_address->appendChild($dom->createElement('prefix'));
    $shippingPrefix->appendChild($dom->createTextNode(''));
    $shippingRegion = $shipping_address->appendChild($dom->createElement('region'));
    $shippingRegion->appendChild($dom->createTextNode($shippingRegionValue));
    $shippingRegion_id = $shipping_address->appendChild($dom->createElement('region_id'));
    $shippingRegion_id->appendChild($dom->createTextNode($shippingRegion_idValue));
    $shippingRegion_iso2 = $shipping_address->appendChild($dom->createElement('region_iso2'));
    $shippingRegion_iso2->appendChild($dom->createTextNode(''));
    $shippingStreet = $shipping_address->appendChild($dom->createElement('street'));
    $shippingStreet->appendChild($dom->createTextNode($shippingStreetValue));
    $shippingSuffix = $shipping_address->appendChild($dom->createElement('suffix'));
    $shippingSuffix->appendChild($dom->createTextNode(''));
    $shippingTelephone = $shipping_address->appendChild($dom->createElement('telephone'));
    $shippingTelephone->appendChild($dom->createTextNode($shippingTelephoneValue));

    // Build billing
    $billing_address = $order->appendChild($dom->createElement('billing_address'));
    
    $billingCity = $billing_address->appendChild($dom->createElement('city'));
    $billingCity->appendChild($dom->createTextNode($billingCityValue));
    $billingCompany = $billing_address->appendChild($dom->createElement('company'));
    $billingCompany->appendChild($dom->createTextNode(''));
    $billingCountry = $billing_address->appendChild($dom->createElement('country'));
    $billingCountry->appendChild($dom->createTextNode($billingCountryValue));
    $billingCountry_id = $billing_address->appendChild($dom->createElement('country_id'));
    $billingCountry_id->appendChild($dom->createTextNode(''));
    $billingCountry_iso2 = $billing_address->appendChild($dom->createElement('country_iso2'));
    $billingCountry_iso2->appendChild($dom->createTextNode(''));
    $billingCountry_iso3 = $billing_address->appendChild($dom->createElement('country_iso3'));
    $billingCountry_iso3->appendChild($dom->createTextNode(''));
    $billingEmail = $billing_address->appendChild($dom->createElement('email'));
    $billingEmail->appendChild($dom->createTextNode($billingEmailValue));
    $billingFax = $billing_address->appendChild($dom->createElement('fax'));
    $billingFax->appendChild($dom->createTextNode(''));
    $billingFirstname = $billing_address->appendChild($dom->createElement('firstname'));
    $billingFirstname->appendChild($dom->createTextNode($billingFirstnameValue));
    $billingLastname = $billing_address->appendChild($dom->createElement('lastname'));
    $billingLastname->appendChild($dom->createTextNode($billingLastnameValue));
    $billingMiddlename = $billing_address->appendChild($dom->createElement('middlename'));
    $billingMiddlename->appendChild($dom->createTextNode(''));
    $billingName = $billing_address->appendChild($dom->createElement('name'));
    $billingName->appendChild($dom->createTextNode($billingNameValue));
    $billingPostcode = $billing_address->appendChild($dom->createElement('postcode'));
    $billingPostcode->appendChild($dom->createTextNode($billingPostcodeValue));
    $billingPrefix = $billing_address->appendChild($dom->createElement('prefix'));
    $billingPrefix->appendChild($dom->createTextNode(''));
    $billingRegion = $billing_address->appendChild($dom->createElement('region'));
    $billingRegion->appendChild($dom->createTextNode($billingRegionValue));
    $billingRegion_id = $billing_address->appendChild($dom->createElement('region_id'));
    $billingRegion_id->appendChild($dom->createTextNode($billingRegion_idValue));
    $billingRegion_iso2 = $billing_address->appendChild($dom->createElement('region_iso2'));
    $billingRegion_iso2->appendChild($dom->createTextNode(''));
    $billingStreet = $billing_address->appendChild($dom->createElement('street'));
    $billingStreet->appendChild($dom->createTextNode($billingStreetValue));
    $billingSuffix = $billing_address->appendChild($dom->createElement('suffix'));
    $billingSuffix->appendChild($dom->createTextNode(''));
    $billingTelephone = $billing_address->appendChild($dom->createElement('telephone'));
    $billingTelephone->appendChild($dom->createTextNode($billingTelephoneValue));
    
    // Build payment

    $payment = $order->appendChild($dom->createElement('payment'));

    $account_status = $payment->appendChild($dom->createElement('account_status'));
    $account_status->appendChild($dom->createTextNode(''));
    $address_status = $payment->appendChild($dom->createElement('address_status'));
    $address_status->appendChild($dom->createTextNode(''));
    $amount = $payment->appendChild($dom->createElement('amount'));
    $amount->appendChild($dom->createTextNode(''));
    $amount_authorized = $payment->appendChild($dom->createElement('amount_authorized'));
    $amount_authorized->appendChild($dom->createTextNode($amount_authorizedValue));
    $amount_canceled = $payment->appendChild($dom->createElement('amount_canceled'));
    $amount_canceled->appendChild($dom->createTextNode(''));
    $amount_ordered = $payment->appendChild($dom->createElement('amount_ordered'));
    $amount_ordered->appendChild($dom->createTextNode($amount_orderedValue));
    $amount_paid = $payment->appendChild($dom->createElement('amount_paid'));
    $amount_paid->appendChild($dom->createTextNode(''));
    $amount_refunded = $payment->appendChild($dom->createElement('amount_refunded'));
    $amount_refunded->appendChild($dom->createTextNode(''));
    $anet_trans_method = $payment->appendChild($dom->createElement('anet_trans_method'));
    $anet_trans_method->appendChild($dom->createTextNode($anet_trans_methodValue));
    $base_amount_authorized = $payment->appendChild($dom->createElement('base_amount_authorized'));
    $base_amount_authorized->appendChild($dom->createTextNode($base_amount_authorizedValue));
    $base_amount_canceled = $payment->appendChild($dom->createElement('base_amount_canceled'));
    $base_amount_canceled->appendChild($dom->createTextNode(''));
    $base_amount_ordered = $payment->appendChild($dom->createElement('base_amount_ordered'));
    $base_amount_ordered->appendChild($dom->createTextNode($base_amount_orderedValue));
    $base_amount_paid = $payment->appendChild($dom->createElement('base_amount_paid'));
    $base_amount_paid->appendChild($dom->createTextNode(''));
    $base_amount_paid_online = $payment->appendChild($dom->createElement('base_amount_paid_online'));
    $base_amount_paid_online->appendChild($dom->createTextNode(''));
    $base_amount_refunded = $payment->appendChild($dom->createElement('base_amount_refunded'));
    $base_amount_refunded->appendChild($dom->createTextNode(''));
    $base_amount_refunded_online = $payment->appendChild($dom->createElement('base_amount_refunded_online'));
    $base_amount_refunded_online->appendChild($dom->createTextNode(''));
    $base_shipping_amount = $payment->appendChild($dom->createElement('base_shipping_amount'));
    $base_shipping_amount->appendChild($dom->createTextNode($base_shipping_amountValue));
    $base_shipping_captured = $payment->appendChild($dom->createElement('base_shipping_captured'));
    $base_shipping_captured->appendChild($dom->createTextNode(''));
    $base_shipping_refunded = $payment->appendChild($dom->createElement('base_shipping_refunded'));
    $base_shipping_refunded->appendChild($dom->createTextNode(''));
    $cc_approval = $payment->appendChild($dom->createElement('cc_approval'));
    $cc_approval->appendChild($dom->createTextNode($cc_approvalValue));
    $cc_avs_status = $payment->appendChild($dom->createElement('cc_avs_status'));
    $cc_avs_status->appendChild($dom->createTextNode($cc_avs_statusValue));
    $cc_cid_status = $payment->appendChild($dom->createElement('cc_cid_status'));
    $cc_cid_status->appendChild($dom->createTextNode($cc_cid_statusValue));
    $cc_debug_request_body = $payment->appendChild($dom->createElement('cc_debug_request_body'));
    $cc_debug_request_body->appendChild($dom->createTextNode(''));
    $cc_debug_response_body = $payment->appendChild($dom->createElement('cc_debug_response_body'));
    $cc_debug_response_body->appendChild($dom->createTextNode(''));
    $cc_debug_response_serialized = $payment->appendChild($dom->createElement('cc_debug_response_serialized'));
    $cc_debug_response_serialized->appendChild($dom->createTextNode(''));
    $cc_exp_month = $payment->appendChild($dom->createElement('cc_exp_month'));
    $cc_exp_month->appendChild($dom->createTextNode($cc_exp_monthValue));
    $cc_exp_year = $payment->appendChild($dom->createElement('cc_exp_year'));
    $cc_exp_year->appendChild($dom->createTextNode($cc_exp_yearValue));
    $cc_last4 = $payment->appendChild($dom->createElement('cc_last4'));
    $cc_last4->appendChild($dom->createTextNode($cc_last4Value));
    $cc_number_enc = $payment->appendChild($dom->createElement('cc_number_enc'));
    $cc_number_enc->appendChild($dom->createTextNode(''));
    $cc_owner = $payment->appendChild($dom->createElement('cc_owner'));
    $cc_owner->appendChild($dom->createTextNode(''));
    $cc_raw_request = $payment->appendChild($dom->createElement('cc_raw_request'));
    $cc_raw_request->appendChild($dom->createTextNode(''));
    $cc_raw_response = $payment->appendChild($dom->createElement('cc_raw_response'));
    $cc_raw_response->appendChild($dom->createTextNode(''));
    $cc_secure_verify = $payment->appendChild($dom->createElement('cc_secure_verify'));
    $cc_secure_verify->appendChild($dom->createTextNode(''));
    $cc_ss_issue = $payment->appendChild($dom->createElement('cc_ss_issue'));
    $cc_ss_issue->appendChild($dom->createTextNode(''));
    $cc_ss_start_month = $payment->appendChild($dom->createElement('cc_ss_start_month'));
    $cc_ss_start_month->appendChild($dom->createTextNode('0'));//appears to be 0 since not used
    $cc_ss_start_year = $payment->appendChild($dom->createElement('cc_ss_start_year'));
    $cc_ss_start_year->appendChild($dom->createTextNode('0'));//appears to be 0 since not used
    $cc_status = $payment->appendChild($dom->createElement('cc_status'));
    $cc_status->appendChild($dom->createTextNode(''));
    $cc_status_description = $payment->appendChild($dom->createElement('cc_status_description'));
    $cc_status_description->appendChild($dom->createTextNode(''));
    $cc_trans_id = $payment->appendChild($dom->createElement('cc_trans_id'));
    $cc_trans_id->appendChild($dom->createTextNode($cc_trans_idValue));
    $cc_type = $payment->appendChild($dom->createElement('cc_type'));
    $cc_type->appendChild($dom->createTextNode($cc_typeValue));
    $cybersource_token = $payment->appendChild($dom->createElement('cybersource_token'));
    $cybersource_token->appendChild($dom->createTextNode(''));
    $echeck_account_name = $payment->appendChild($dom->createElement('echeck_account_name'));
    $echeck_account_name->appendChild($dom->createTextNode(''));
    $echeck_account_type = $payment->appendChild($dom->createElement('echeck_account_type'));
    $echeck_account_type->appendChild($dom->createTextNode(''));
    $echeck_bank_name = $payment->appendChild($dom->createElement('echeck_bank_name'));
    $echeck_bank_name->appendChild($dom->createTextNode(''));
    $echeck_routing_number = $payment->appendChild($dom->createElement('echeck_routing_number'));
    $echeck_routing_number->appendChild($dom->createTextNode(''));
    $echeck_type = $payment->appendChild($dom->createElement('echeck_type'));
    $echeck_type->appendChild($dom->createTextNode(''));
    $flo2cash_account_id = $payment->appendChild($dom->createElement('flo2cash_account_id'));
    $flo2cash_account_id->appendChild($dom->createTextNode(''));
    $ideal_issuer_id = $payment->appendChild($dom->createElement('ideal_issuer_id'));
    $ideal_issuer_id->appendChild($dom->createTextNode(''));
    $ideal_issuer_title = $payment->appendChild($dom->createElement('ideal_issuer_title'));
    $ideal_issuer_title->appendChild($dom->createTextNode(''));
    $ideal_transaction_checked = $payment->appendChild($dom->createElement('ideal_transaction_checked'));
    $ideal_transaction_checked->appendChild($dom->createTextNode(''));
    $last_trans_id = $payment->appendChild($dom->createElement('last_trans_id'));
    $last_trans_id->appendChild($dom->createTextNode($last_trans_idValue));
    $method = $payment->appendChild($dom->createElement('method'));
    $method->appendChild($dom->createTextNode($methodValue));
    $paybox_question_number = $payment->appendChild($dom->createElement('paybox_question_number'));
    $paybox_question_number->appendChild($dom->createTextNode(''));
    $paybox_request_number = $payment->appendChild($dom->createElement('paybox_request_number'));
    $paybox_request_number->appendChild($dom->createTextNode(''));
    $po_number = $payment->appendChild($dom->createElement('po_number'));
    $po_number->appendChild($dom->createTextNode(''));
    $protection_eligibility = $payment->appendChild($dom->createElement('protection_eligibility'));
    $protection_eligibility->appendChild($dom->createTextNode(''));
    $shipping_amount = $payment->appendChild($dom->createElement('shipping_amount'));
    $shipping_amount->appendChild($dom->createTextNode($shipping_amountValue));
    $shipping_captured = $payment->appendChild($dom->createElement('shipping_captured'));
    $shipping_captured->appendChild($dom->createTextNode(''));
    $shipping_refunded = $payment->appendChild($dom->createElement('shipping_refunded'));
    $shipping_refunded->appendChild($dom->createTextNode(''));

    // Build Items
    $items = $order->appendChild($dom->createElement('items'));
    $itemsQuery = "SELECT * FROM `se_orderitem` WHERE `yahooOrderIdNumeric` = " . $result['yahooOrderIdNumeric'];
    $itemsResult = $writeConnection->query($itemsQuery);
    $itemNumber = 1;
    foreach ($itemsResult as $itemResult) {
        $item = $items->appendChild($dom->createElement('item'));

	//Set variables
	$base_original_priceValue = $itemResult['unitPrice'];
	$base_priceValue = $itemResult['unitPrice'];
	$base_row_totalValue = $itemResult['qtyOrdered'] * $itemResult['unitPrice'];
	$real_nameValue = $itemResult['lineItemDescription'];
	$nameValue = $itemResult['lineItemDescription'];
	$original_priceValue = $itemResult['unitPrice'];
	$priceValue = $itemResult['unitPrice'];
	$qty_orderedValue = $itemResult['qtyOrdered'];
	$row_totalValue = $itemResult['qtyOrdered'] * $itemResult['unitPrice'];
	$length = strlen(end(explode('-', $itemResult['productCode'])));
	$real_skuValue = substr($itemResult['productCode'], 0, -($length + 1));
        
	fwrite($transactionLogHandle, "      ->ADDING CONFIGURABLE : " . $itemNumber . " -> " . $real_skuValue . "\n");

	$skuValue = 'Product ' . $itemNumber;
	if (!is_null($result['shipCountry']) && $result['shipState'] == 'CA') {
	    if (strtolower($result['shipCountry']) == 'united states') {
		$tax_percentCalcValue = '0.0875';
		$tax_percentValue = '8.75';
		$base_price_incl_taxValue = round($priceValue + ($priceValue * $tax_percentCalcValue), 4);//
		$base_row_total_incl_taxValue = round($qty_orderedValue * ($priceValue + ($priceValue * $tax_percentCalcValue)), 4);//
		$base_tax_amountValue = round($priceValue * $tax_percentCalcValue, 4);//THIS MAY BE WRONG -- QTY or ONE
		$price_incl_taxValue = round($priceValue + ($priceValue * $tax_percentCalcValue), 4);//
		$row_total_incl_taxValue = round($qty_orderedValue * ($priceValue + ($priceValue * $tax_percentCalcValue)), 4);//
		$tax_amountValue = round($priceValue * $tax_percentCalcValue, 4);//
	    } else {
		$tax_percentValue = '0.00';
		$base_price_incl_taxValue = $priceValue;
		$base_row_total_incl_taxValue = $qty_orderedValue * $priceValue;
		$base_tax_amountValue = '0.00';
		$price_incl_taxValue = $priceValue;
		$row_total_incl_taxValue = $qty_orderedValue * $priceValue;
		$tax_amountValue = '0.00';		
	    }
	} else {
	    $tax_percentValue = '0.00';
	    $base_price_incl_taxValue = $priceValue;
	    $base_row_total_incl_taxValue = $qty_orderedValue * $priceValue;
	    $base_tax_amountValue = '0.00';
	    $price_incl_taxValue = $priceValue;
	    $row_total_incl_taxValue = $qty_orderedValue * $priceValue;
	    $tax_amountValue = '0.00';	
	}

	//Create line item
	$amount_refunded = $item->appendChild($dom->createElement('amount_refunded'));
	$amount_refunded->appendChild($dom->createTextNode('0')); //Always 0
	$applied_rule_ids = $item->appendChild($dom->createElement('applied_rule_ids'));
	$applied_rule_ids->appendChild($dom->createTextNode(''));
	$base_amount_refunded = $item->appendChild($dom->createElement('base_amount_refunded'));
	$base_amount_refunded->appendChild($dom->createTextNode('0')); //Always 0
	$base_cost = $item->appendChild($dom->createElement('base_cost'));
	$base_cost->appendChild($dom->createTextNode(''));
	$base_discount_amount = $item->appendChild($dom->createElement('base_discount_amount'));
	$base_discount_amount->appendChild($dom->createTextNode('0')); //Always 0
	$base_discount_invoiced = $item->appendChild($dom->createElement('base_discount_invoiced'));
	$base_discount_invoiced->appendChild($dom->createTextNode('0')); //Always 0
	$base_hidden_tax_amount = $item->appendChild($dom->createElement('base_hidden_tax_amount'));
	$base_hidden_tax_amount->appendChild($dom->createTextNode(''));
	$base_hidden_tax_invoiced = $item->appendChild($dom->createElement('base_hidden_tax_invoiced'));
	$base_hidden_tax_invoiced->appendChild($dom->createTextNode(''));
	$base_hidden_tax_refunded = $item->appendChild($dom->createElement('base_hidden_tax_refunded'));
	$base_hidden_tax_refunded->appendChild($dom->createTextNode(''));
	$base_original_price = $item->appendChild($dom->createElement('base_original_price'));
	$base_original_price->appendChild($dom->createTextNode($base_original_priceValue));
	$base_price = $item->appendChild($dom->createElement('base_price'));
	$base_price->appendChild($dom->createTextNode($base_priceValue));
	$base_price_incl_tax = $item->appendChild($dom->createElement('base_price_incl_tax'));
	$base_price_incl_tax->appendChild($dom->createTextNode($base_price_incl_taxValue));
	$base_row_invoiced = $item->appendChild($dom->createElement('base_row_invoiced'));
	$base_row_invoiced->appendChild($dom->createTextNode('0'));
	$base_row_total = $item->appendChild($dom->createElement('base_row_total'));
	$base_row_total->appendChild($dom->createTextNode($base_row_totalValue));
	$base_row_total_incl_tax = $item->appendChild($dom->createElement('base_row_total_incl_tax'));
	$base_row_total_incl_tax->appendChild($dom->createTextNode($base_row_total_incl_taxValue));
	$base_tax_amount = $item->appendChild($dom->createElement('base_tax_amount'));
	$base_tax_amount->appendChild($dom->createTextNode($base_tax_amountValue));
	$base_tax_before_discount = $item->appendChild($dom->createElement('base_tax_before_discount'));
	$base_tax_before_discount->appendChild($dom->createTextNode(''));
	$base_tax_invoiced = $item->appendChild($dom->createElement('base_tax_invoiced'));
	$base_tax_invoiced->appendChild($dom->createTextNode('0')); //Always 0
	$base_weee_tax_applied_amount = $item->appendChild($dom->createElement('base_weee_tax_applied_amount'));
	$base_weee_tax_applied_amount->appendChild($dom->createTextNode('0')); //Always 0
	$base_weee_tax_applied_row_amount = $item->appendChild($dom->createElement('base_weee_tax_applied_row_amount'));
	$base_weee_tax_applied_row_amount->appendChild($dom->createTextNode('0')); //Always 0
	$base_weee_tax_disposition = $item->appendChild($dom->createElement('base_weee_tax_disposition'));
	$base_weee_tax_disposition->appendChild($dom->createTextNode('0')); //Always 0
	$base_weee_tax_row_disposition = $item->appendChild($dom->createElement('base_weee_tax_row_disposition'));
	$base_weee_tax_row_disposition->appendChild($dom->createTextNode('0')); //Always 0
	$description = $item->appendChild($dom->createElement('description'));
	$description->appendChild($dom->createTextNode(''));
	$discount_amount = $item->appendChild($dom->createElement('discount_amount'));
	$discount_amount->appendChild($dom->createTextNode('0')); //Always 0
	$discount_invoiced = $item->appendChild($dom->createElement('discount_invoiced'));
	$discount_invoiced->appendChild($dom->createTextNode('0')); //Always 0
	$discount_percent = $item->appendChild($dom->createElement('discount_percent'));
	$discount_percent->appendChild($dom->createTextNode('0')); //Always 0
	$free_shipping = $item->appendChild($dom->createElement('free_shipping'));
	$free_shipping->appendChild($dom->createTextNode('0')); //Always 0
	$hidden_tax_amount = $item->appendChild($dom->createElement('hidden_tax_amount'));
	$hidden_tax_amount->appendChild($dom->createTextNode(''));
	$hidden_tax_canceled = $item->appendChild($dom->createElement('hidden_tax_canceled'));
	$hidden_tax_canceled->appendChild($dom->createTextNode(''));
	$hidden_tax_invoiced = $item->appendChild($dom->createElement('hidden_tax_invoiced'));
	$hidden_tax_invoiced->appendChild($dom->createTextNode(''));
	$hidden_tax_refunded = $item->appendChild($dom->createElement('hidden_tax_refunded'));
	$hidden_tax_refunded->appendChild($dom->createTextNode(''));
	$is_nominal = $item->appendChild($dom->createElement('is_nominal'));
	$is_nominal->appendChild($dom->createTextNode('0')); //Always 0
	$is_qty_decimal = $item->appendChild($dom->createElement('is_qty_decimal'));
	$is_qty_decimal->appendChild($dom->createTextNode('0')); //Always 0
	$is_virtual = $item->appendChild($dom->createElement('is_virtual'));
	$is_virtual->appendChild($dom->createTextNode('0')); //Always 0
	$real_name = $item->appendChild($dom->createElement('real_name'));
	$real_name->appendChild($dom->createTextNode($real_nameValue)); //Always 0
	$name = $item->appendChild($dom->createElement('name'));
	$name->appendChild($dom->createTextNode($nameValue)); //Always 0
	$no_discount = $item->appendChild($dom->createElement('no_discount'));
	$no_discount->appendChild($dom->createTextNode('0')); //Always 0
	$original_price = $item->appendChild($dom->createElement('original_price'));
	$original_price->appendChild($dom->createTextNode($original_priceValue));
	$price = $item->appendChild($dom->createElement('price'));
	$price->appendChild($dom->createTextNode($priceValue));
	$price_incl_tax = $item->appendChild($dom->createElement('price_incl_tax'));
	$price_incl_tax->appendChild($dom->createTextNode($price_incl_taxValue));
	$qty_backordered = $item->appendChild($dom->createElement('qty_backordered'));
	$qty_backordered->appendChild($dom->createTextNode(''));
	$qty_canceled = $item->appendChild($dom->createElement('qty_canceled'));
	$qty_canceled->appendChild($dom->createTextNode('0')); //Always 0
	$qty_invoiced = $item->appendChild($dom->createElement('qty_invoiced'));
	$qty_invoiced->appendChild($dom->createTextNode('0')); //Always 0
	$qty_ordered = $item->appendChild($dom->createElement('qty_ordered'));
	$qty_ordered->appendChild($dom->createTextNode($qty_orderedValue)); //Always 0
	$qty_refunded = $item->appendChild($dom->createElement('qty_refunded'));
	$qty_refunded->appendChild($dom->createTextNode('0')); //Always 0
	$qty_shipped = $item->appendChild($dom->createElement('qty_shipped'));
	$qty_shipped->appendChild($dom->createTextNode('0')); //Always 0
	$row_invoiced = $item->appendChild($dom->createElement('row_invoiced'));
	$row_invoiced->appendChild($dom->createTextNode('0')); //Always 0
	$row_total = $item->appendChild($dom->createElement('row_total'));
	$row_total->appendChild($dom->createTextNode($row_totalValue));
	$row_total_incl_tax = $item->appendChild($dom->createElement('row_total_incl_tax'));
	$row_total_incl_tax->appendChild($dom->createTextNode($row_total_incl_taxValue));
	$row_weight = $item->appendChild($dom->createElement('row_weight'));
	$row_weight->appendChild($dom->createTextNode('0'));
	$real_sku = $item->appendChild($dom->createElement('real_sku'));
	$real_sku->appendChild($dom->createTextNode($real_skuValue));
	$sku = $item->appendChild($dom->createElement('sku'));
	$sku->appendChild($dom->createTextNode($skuValue));
	$tax_amount = $item->appendChild($dom->createElement('tax_amount'));
	$tax_amount->appendChild($dom->createTextNode($tax_amountValue));
	$tax_before_discount = $item->appendChild($dom->createElement('tax_before_discount'));
	$tax_before_discount->appendChild($dom->createTextNode(''));
	$tax_canceled = $item->appendChild($dom->createElement('tax_canceled'));
	$tax_canceled->appendChild($dom->createTextNode(''));
	$tax_invoiced = $item->appendChild($dom->createElement('tax_invoiced'));
	$tax_invoiced->appendChild($dom->createTextNode('0'));
	$tax_percent = $item->appendChild($dom->createElement('tax_percent'));
	$tax_percent->appendChild($dom->createTextNode($tax_percentValue));
	$tax_refunded = $item->appendChild($dom->createElement('tax_refunded'));
	$tax_refunded->appendChild($dom->createTextNode(''));
	$weee_tax_applied = $item->appendChild($dom->createElement('weee_tax_applied'));
	$weee_tax_applied->appendChild($dom->createTextNode('a:0:{}')); //Always 0
	$weee_tax_applied_amount = $item->appendChild($dom->createElement('weee_tax_applied_amount'));
	$weee_tax_applied_amount->appendChild($dom->createTextNode('0')); //Always 0
	$weee_tax_applied_row_amount = $item->appendChild($dom->createElement('weee_tax_applied_row_amount'));
	$weee_tax_applied_row_amount->appendChild($dom->createTextNode('0')); //Always 0
	$weee_tax_disposition = $item->appendChild($dom->createElement('weee_tax_disposition'));
	$weee_tax_disposition->appendChild($dom->createTextNode('0')); //Always 0
	$weee_tax_row_disposition = $item->appendChild($dom->createElement('weee_tax_row_disposition'));
	$weee_tax_row_disposition->appendChild($dom->createTextNode('0')); //Always 0
	$weight = $item->appendChild($dom->createElement('weight'));
	$weight->appendChild($dom->createTextNode('0'));

	//Add simple
	$item = $items->appendChild($dom->createElement('item'));
	
	//Set variables
	$base_original_priceValue = '0.0000';
	$base_priceValue = '0.0000';
	$base_row_totalValue = '0.0000';
	$real_nameValue = $itemResult['lineItemDescription'];
	$nameValue = $itemResult['lineItemDescription'];
	$original_priceValue = '0.0000';
	$priceValue = '0.0000';
	$qty_orderedValue = $itemResult['qtyOrdered'];
	$row_totalValue = '0.0000';
	$real_skuValue = $itemResult['productCode'];
	$skuValue = "Product " . $itemNumber . "-OFFLINE";
	$parent_skuValue = 'Product ' . $itemNumber;//Just for simple
	
	fwrite($transactionLogHandle, "      ->ADDING SIMPLE       : " . $itemNumber . " -> " . $real_skuValue . "\n");

	$tax_percentValue = '0.00';
	$base_price_incl_taxValue = '0.0000';
	$base_row_total_incl_taxValue = '0.0000';
	$base_tax_amountValue = '0.0000';
	$price_incl_taxValue = '0.0000';
	$row_total_incl_taxValue = '0.0000';
	$tax_amountValue = '0.0000';

	//Create line item
	$amount_refunded = $item->appendChild($dom->createElement('amount_refunded'));
	$amount_refunded->appendChild($dom->createTextNode('0')); //Always 0
	$applied_rule_ids = $item->appendChild($dom->createElement('applied_rule_ids'));
	$applied_rule_ids->appendChild($dom->createTextNode(''));
	$base_amount_refunded = $item->appendChild($dom->createElement('base_amount_refunded'));
	$base_amount_refunded->appendChild($dom->createTextNode('0')); //Always 0
	$base_cost = $item->appendChild($dom->createElement('base_cost'));
	$base_cost->appendChild($dom->createTextNode(''));
	$base_discount_amount = $item->appendChild($dom->createElement('base_discount_amount'));
	$base_discount_amount->appendChild($dom->createTextNode('0')); //Always 0
	$base_discount_invoiced = $item->appendChild($dom->createElement('base_discount_invoiced'));
	$base_discount_invoiced->appendChild($dom->createTextNode('0')); //Always 0
	$base_hidden_tax_amount = $item->appendChild($dom->createElement('base_hidden_tax_amount'));
	$base_hidden_tax_amount->appendChild($dom->createTextNode(''));
	$base_hidden_tax_invoiced = $item->appendChild($dom->createElement('base_hidden_tax_invoiced'));
	$base_hidden_tax_invoiced->appendChild($dom->createTextNode(''));
	$base_hidden_tax_refunded = $item->appendChild($dom->createElement('base_hidden_tax_refunded'));
	$base_hidden_tax_refunded->appendChild($dom->createTextNode(''));
	$base_original_price = $item->appendChild($dom->createElement('base_original_price'));
	$base_original_price->appendChild($dom->createTextNode($base_original_priceValue));
	$base_price = $item->appendChild($dom->createElement('base_price'));
	$base_price->appendChild($dom->createTextNode($base_priceValue));
	$base_price_incl_tax = $item->appendChild($dom->createElement('base_price_incl_tax'));
	$base_price_incl_tax->appendChild($dom->createTextNode($base_price_incl_taxValue));
	$base_row_invoiced = $item->appendChild($dom->createElement('base_row_invoiced'));
	$base_row_invoiced->appendChild($dom->createTextNode('0'));
	$base_row_total = $item->appendChild($dom->createElement('base_row_total'));
	$base_row_total->appendChild($dom->createTextNode($base_row_totalValue));
	$base_row_total_incl_tax = $item->appendChild($dom->createElement('base_row_total_incl_tax'));
	$base_row_total_incl_tax->appendChild($dom->createTextNode($base_row_total_incl_taxValue));
	$base_tax_amount = $item->appendChild($dom->createElement('base_tax_amount'));
	$base_tax_amount->appendChild($dom->createTextNode($base_tax_amountValue));
	$base_tax_before_discount = $item->appendChild($dom->createElement('base_tax_before_discount'));
	$base_tax_before_discount->appendChild($dom->createTextNode(''));
	$base_tax_invoiced = $item->appendChild($dom->createElement('base_tax_invoiced'));
	$base_tax_invoiced->appendChild($dom->createTextNode('0')); //Always 0
	$base_weee_tax_applied_amount = $item->appendChild($dom->createElement('base_weee_tax_applied_amount'));
	$base_weee_tax_applied_amount->appendChild($dom->createTextNode('0')); //Always 0
	$base_weee_tax_applied_row_amount = $item->appendChild($dom->createElement('base_weee_tax_applied_row_amount'));
	$base_weee_tax_applied_row_amount->appendChild($dom->createTextNode('0')); //Always 0
	$base_weee_tax_disposition = $item->appendChild($dom->createElement('base_weee_tax_disposition'));
	$base_weee_tax_disposition->appendChild($dom->createTextNode('0')); //Always 0
	$base_weee_tax_row_disposition = $item->appendChild($dom->createElement('base_weee_tax_row_disposition'));
	$base_weee_tax_row_disposition->appendChild($dom->createTextNode('0')); //Always 0
	$description = $item->appendChild($dom->createElement('description'));
	$description->appendChild($dom->createTextNode(''));
	$discount_amount = $item->appendChild($dom->createElement('discount_amount'));
	$discount_amount->appendChild($dom->createTextNode('0')); //Always 0
	$discount_invoiced = $item->appendChild($dom->createElement('discount_invoiced'));
	$discount_invoiced->appendChild($dom->createTextNode('0')); //Always 0
	$discount_percent = $item->appendChild($dom->createElement('discount_percent'));
	$discount_percent->appendChild($dom->createTextNode('0')); //Always 0
	$free_shipping = $item->appendChild($dom->createElement('free_shipping'));
	$free_shipping->appendChild($dom->createTextNode('0')); //Always 0
	$hidden_tax_amount = $item->appendChild($dom->createElement('hidden_tax_amount'));
	$hidden_tax_amount->appendChild($dom->createTextNode(''));
	$hidden_tax_canceled = $item->appendChild($dom->createElement('hidden_tax_canceled'));
	$hidden_tax_canceled->appendChild($dom->createTextNode(''));
	$hidden_tax_invoiced = $item->appendChild($dom->createElement('hidden_tax_invoiced'));
	$hidden_tax_invoiced->appendChild($dom->createTextNode(''));
	$hidden_tax_refunded = $item->appendChild($dom->createElement('hidden_tax_refunded'));
	$hidden_tax_refunded->appendChild($dom->createTextNode(''));
	$is_nominal = $item->appendChild($dom->createElement('is_nominal'));
	$is_nominal->appendChild($dom->createTextNode('0')); //Always 0
	$is_qty_decimal = $item->appendChild($dom->createElement('is_qty_decimal'));
	$is_qty_decimal->appendChild($dom->createTextNode('0')); //Always 0
	$is_virtual = $item->appendChild($dom->createElement('is_virtual'));
	$is_virtual->appendChild($dom->createTextNode('0')); //Always 0
	$real_name = $item->appendChild($dom->createElement('real_name'));
	$real_name->appendChild($dom->createTextNode($real_nameValue)); //Always 0
	$name = $item->appendChild($dom->createElement('nameValue'));
	$name->appendChild($dom->createTextNode($nameValue)); //Always 0
	$no_discount = $item->appendChild($dom->createElement('no_discount'));
	$no_discount->appendChild($dom->createTextNode('0')); //Always 0
	$original_price = $item->appendChild($dom->createElement('original_price'));
	$original_price->appendChild($dom->createTextNode($original_priceValue));
	$parent_sku = $item->appendChild($dom->createElement('parent_sku'));
	$parent_sku->appendChild($dom->createTextNode($parent_skuValue));
	$price = $item->appendChild($dom->createElement('price'));
	$price->appendChild($dom->createTextNode($priceValue));
	$price_incl_tax = $item->appendChild($dom->createElement('price_incl_tax'));
	$price_incl_tax->appendChild($dom->createTextNode($price_incl_taxValue));
	$qty_backordered = $item->appendChild($dom->createElement('qty_backordered'));
	$qty_backordered->appendChild($dom->createTextNode(''));
	$qty_canceled = $item->appendChild($dom->createElement('qty_canceled'));
	$qty_canceled->appendChild($dom->createTextNode('0')); //Always 0
	$qty_invoiced = $item->appendChild($dom->createElement('qty_invoiced'));
	$qty_invoiced->appendChild($dom->createTextNode('0')); //Always 0
	$qty_ordered = $item->appendChild($dom->createElement('qty_ordered'));
	$qty_ordered->appendChild($dom->createTextNode($qty_orderedValue)); //Always 0
	$qty_refunded = $item->appendChild($dom->createElement('qty_refunded'));
	$qty_refunded->appendChild($dom->createTextNode('0')); //Always 0
	$qty_shipped = $item->appendChild($dom->createElement('qty_shipped'));
	$qty_shipped->appendChild($dom->createTextNode('0')); //Always 0
	$row_invoiced = $item->appendChild($dom->createElement('row_invoiced'));
	$row_invoiced->appendChild($dom->createTextNode('0')); //Always 0
	$row_total = $item->appendChild($dom->createElement('row_total'));
	$row_total->appendChild($dom->createTextNode($row_totalValue));
	$row_total_incl_tax = $item->appendChild($dom->createElement('row_total_incl_tax'));
	$row_total_incl_tax->appendChild($dom->createTextNode($row_total_incl_taxValue));
	$row_weight = $item->appendChild($dom->createElement('row_weight'));
	$row_weight->appendChild($dom->createTextNode('0'));
	$real_sku = $item->appendChild($dom->createElement('real_sku'));
	$real_sku->appendChild($dom->createTextNode($real_skuValue));
	$sku = $item->appendChild($dom->createElement('sku'));
	$sku->appendChild($dom->createTextNode($skuValue));
	$tax_amount = $item->appendChild($dom->createElement('tax_amount'));
	$tax_amount->appendChild($dom->createTextNode($tax_amountValue));
	$tax_before_discount = $item->appendChild($dom->createElement('tax_before_discount'));
	$tax_before_discount->appendChild($dom->createTextNode(''));
	$tax_canceled = $item->appendChild($dom->createElement('tax_canceled'));
	$tax_canceled->appendChild($dom->createTextNode(''));
	$tax_invoiced = $item->appendChild($dom->createElement('tax_invoiced'));
	$tax_invoiced->appendChild($dom->createTextNode('0'));
	$tax_percent = $item->appendChild($dom->createElement('tax_percent'));
	$tax_percent->appendChild($dom->createTextNode($tax_percentValue));
	$tax_refunded = $item->appendChild($dom->createElement('tax_refunded'));
	$tax_refunded->appendChild($dom->createTextNode(''));
	$weee_tax_applied = $item->appendChild($dom->createElement('weee_tax_applied'));
	$weee_tax_applied->appendChild($dom->createTextNode('a:0:{}')); //Always 0
	$weee_tax_applied_amount = $item->appendChild($dom->createElement('weee_tax_applied_amount'));
	$weee_tax_applied_amount->appendChild($dom->createTextNode('0')); //Always 0
	$weee_tax_applied_row_amount = $item->appendChild($dom->createElement('weee_tax_applied_row_amount'));
	$weee_tax_applied_row_amount->appendChild($dom->createTextNode('0')); //Always 0
	$weee_tax_disposition = $item->appendChild($dom->createElement('weee_tax_disposition'));
	$weee_tax_disposition->appendChild($dom->createTextNode('0')); //Always 0
	$weee_tax_row_disposition = $item->appendChild($dom->createElement('weee_tax_row_disposition'));
	$weee_tax_row_disposition->appendChild($dom->createTextNode('0')); //Always 0
	$weight = $item->appendChild($dom->createElement('weight'));
	$weight->appendChild($dom->createTextNode('0'));
	
        $itemNumber++;
    }
}

// Make the output pretty
$dom->formatOutput = true;

// Save the XML string
$xml = $dom->saveXML();

//Write file to post directory
$handle = fopen($toolsXmlDirectory . $orderFilename, 'w');
fwrite($handle, $xml);
fclose($handle);

fwrite($transactionLogHandle, "  ->FINISH TIME   : " . $endTime[5] . '/' . $endTime[4] . '/' . $endTime[3] . ' ' . $endTime[2] . ':' . $endTime[1] . ':' . $endTime[0] . "\n");
fwrite($transactionLogHandle, "  ->CREATED       : ORDER FILE 4 " . $orderFilename . "\n");

fwrite($transactionLogHandle, "->END PROCESSING\n");
//Close transaction log
fclose($transactionLogHandle);

//FILE 5
$realTime = realTime();
//Open transaction log
$transactionLogHandle = fopen($toolsLogsDirectory . 'migration_gen_sneakerhead_order_xml_files_transaction_log', 'a+');
fwrite($transactionLogHandle, "->BEGIN PROCESSING\n");
fwrite($transactionLogHandle, "  ->START TIME    : " . $realTime[5] . '/' . $realTime[4] . '/' . $realTime[3] . ' ' . $realTime[2] . ':' . $realTime[1] . ':' . $realTime[0] . "\n");

//ORDERS
fwrite($transactionLogHandle, "  ->GETTING ORDERS\n");

$resource = Mage::getSingleton('core/resource');
$writeConnection = $resource->getConnection('core_write');
//$startDate = '2009-10-00 00:00:00';//>=
$startDate = '2010-03-15 00:00:00';//>=
$endDate = '2010-03-15 00:00:00';//<
//10,000
//$query = "SELECT `users`.`email` AS `user_email`, `users`.`firstName` AS `user_firstname`, `users`.`lastName` AS `user_lastname`, `se_order`.* FROM `se_order` LEFT JOIN `storeorder` ON `se_order`.`yahooOrderIdNumeric` = `storeorder`.`yahooOrderIdNumeric` LEFT JOIN `users` ON `storeorder`.`uid` = `users`.`uid` WHERE `se_order`.`orderCreationDate` > '2009-10-00 00:00:00' AND `se_order`.`orderCreationDate` < '2009-12-14 00:00:00'";
//25,000
//FOLLOWING 8 QUERIES TO BE RUN SEPARATELY TO GENERATE 8 DIFFERENT FILES
//$query = "SELECT `users`.`email` AS `user_email`, `users`.`firstName` AS `user_firstname`, `users`.`lastName` AS `user_lastname`, `se_order`.* FROM `se_order` LEFT JOIN `storeorder` ON `se_order`.`yahooOrderIdNumeric` = `storeorder`.`yahooOrderIdNumeric` LEFT JOIN `users` ON `storeorder`.`uid` = `users`.`uid` WHERE `se_order`.`orderCreationDate` >= '2009-10-00 00:00:00' AND `se_order`.`orderCreationDate` < '2010-03-15 00:00:00'";//191298 -> 216253
//$query = "SELECT `users`.`email` AS `user_email`, `users`.`firstName` AS `user_firstname`, `users`.`lastName` AS `user_lastname`, `se_order`.* FROM `se_order` LEFT JOIN `storeorder` ON `se_order`.`yahooOrderIdNumeric` = `storeorder`.`yahooOrderIdNumeric` LEFT JOIN `users` ON `storeorder`.`uid` = `users`.`uid` WHERE `se_order`.`orderCreationDate` >= '2010-03-15 00:00:00' AND `se_order`.`orderCreationDate` < '2010-10-28 00:00:00'";//216254 -> 241203
//$query = "SELECT `users`.`email` AS `user_email`, `users`.`firstName` AS `user_firstname`, `users`.`lastName` AS `user_lastname`, `se_order`.* FROM `se_order` LEFT JOIN `storeorder` ON `se_order`.`yahooOrderIdNumeric` = `storeorder`.`yahooOrderIdNumeric` LEFT JOIN `users` ON `storeorder`.`uid` = `users`.`uid` WHERE `se_order`.`orderCreationDate` >= '2010-10-28 00:00:00' AND `se_order`.`orderCreationDate` < '2011-02-27 00:00:00'";//241204 -> 266066
//$query = "SELECT `users`.`email` AS `user_email`, `users`.`firstName` AS `user_firstname`, `users`.`lastName` AS `user_lastname`, `se_order`.* FROM `se_order` LEFT JOIN `storeorder` ON `se_order`.`yahooOrderIdNumeric` = `storeorder`.`yahooOrderIdNumeric` LEFT JOIN `users` ON `storeorder`.`uid` = `users`.`uid` WHERE `se_order`.`orderCreationDate` >= '2011-02-27 00:00:00' AND `se_order`.`orderCreationDate` < '2011-06-27 00:00:00'";//266067 -> 291019
$query = "SELECT `users`.`email` AS `user_email`, `users`.`firstName` AS `user_firstname`, `users`.`lastName` AS `user_lastname`, `se_order`.* FROM `se_order` LEFT JOIN `storeorder` ON `se_order`.`yahooOrderIdNumeric` = `storeorder`.`yahooOrderIdNumeric` LEFT JOIN `users` ON `storeorder`.`uid` = `users`.`uid` WHERE `se_order`.`orderCreationDate` >= '2011-06-27 00:00:00' AND `se_order`.`orderCreationDate` < '2011-12-09 00:00:00'";//291020 -> 315244
//$query = "SELECT `users`.`email` AS `user_email`, `users`.`firstName` AS `user_firstname`, `users`.`lastName` AS `user_lastname`, `se_order`.* FROM `se_order` LEFT JOIN `storeorder` ON `se_order`.`yahooOrderIdNumeric` = `storeorder`.`yahooOrderIdNumeric` LEFT JOIN `users` ON `storeorder`.`uid` = `users`.`uid` WHERE `se_order`.`orderCreationDate` >= '2011-12-09 00:00:00' AND `se_order`.`orderCreationDate` < '2012-04-24 00:00:00'";//315245 -> 340092
//$query = "SELECT `users`.`email` AS `user_email`, `users`.`firstName` AS `user_firstname`, `users`.`lastName` AS `user_lastname`, `se_order`.* FROM `se_order` LEFT JOIN `storeorder` ON `se_order`.`yahooOrderIdNumeric` = `storeorder`.`yahooOrderIdNumeric` LEFT JOIN `users` ON `storeorder`.`uid` = `users`.`uid` WHERE `se_order`.`orderCreationDate` >= '2012-04-24 00:00:00' AND `se_order`.`orderCreationDate` < '2012-10-04 00:00:00'";//340093 -> 364330
//$query = "SELECT `users`.`email` AS `user_email`, `users`.`firstName` AS `user_firstname`, `users`.`lastName` AS `user_lastname`, `se_order`.* FROM `se_order` LEFT JOIN `storeorder` ON `se_order`.`yahooOrderIdNumeric` = `storeorder`.`yahooOrderIdNumeric` LEFT JOIN `users` ON `storeorder`.`uid` = `users`.`uid` WHERE `se_order`.`orderCreationDate` >= '2012-10-04 00:00:00' AND `se_order`.`orderCreationDate` < '2013-02-16 00:00:00'";//364331 -> ???
//$query = "SELECT `users`.`email` AS `user_email`, `users`.`firstName` AS `user_firstname`, `users`.`lastName` AS `user_lastname`, `se_order`.* FROM `se_order` LEFT JOIN `storeorder` ON `se_order`.`yahooOrderIdNumeric` = `storeorder`.`yahooOrderIdNumeric` LEFT JOIN `users` ON `storeorder`.`uid` = `users`.`uid` WHERE `se_order`.`orderCreationDate` >= '2013-02-16 00:00:00' AND `se_order`.`orderCreationDate` < '2013-04-23 00:00:00'";//??? -> ???
//$query = "SELECT `users`.`email` AS `user_email`, `users`.`firstName` AS `user_firstname`, `users`.`lastName` AS `user_lastname`, `se_order`.* FROM `se_order` LEFT JOIN `storeorder` ON `se_order`.`yahooOrderIdNumeric` = `storeorder`.`yahooOrderIdNumeric` LEFT JOIN `users` ON `storeorder`.`uid` = `users`.`uid` WHERE `se_order`.`orderCreationDate` >= '2013-04-23 00:00:00'"; //??? -> current (???)

$results = $writeConnection->query($query);
$orderFilename = "order_5_" . $realTime[5] . $realTime[4] . $realTime[3] . '_' . $realTime[2] . $realTime[1] . $realTime[0] . ".xml";
//Creates XML string and XML document from the DOM representation
$dom = new DomDocument('1.0');

$orders = $dom->appendChild($dom->createElement('orders'));
foreach ($results as $result) {
    
    //Add order data
    fwrite($transactionLogHandle, "    ->ADDING ORDER NUMBER   : " . $result['yahooOrderIdNumeric'] . "\n");
    
    // Set some variables
    $base_discount_amountValue = (is_null($result['discount'])) ? '0.0000' : $result['discount'];//appears to be actual total discount
    $base_grand_totalValue = (is_null($result['finalGrandTotal'])) ? '0.0000' : $result['finalGrandTotal'];
    $base_shipping_amountValue = (is_null($result['shippingCost'])) ? '0.0000' : $result['shippingCost'];
    $base_shipping_incl_taxValue = (is_null($result['shippingCost'])) ? '0.0000' : $result['shippingCost'];
    $base_subtotalValue = (is_null($result['finalSubTotal'])) ? '0.0000' : $result['finalSubTotal'];
    $base_subtotal_incl_taxValue = (is_null($result['finalSubTotal'])) ? '0.0000' : $result['finalSubTotal'];
    $base_tax_amountValue = (is_null($result['taxTotal'])) ? '0.0000' : $result['taxTotal'];

    if (!is_null($result['shipCountry']) && $result['shipState'] == 'CA') {
	if (strtolower($result['shipCountry']) == 'united states') {
	    $tax_percentValue = '8.75';
	} else {
	    $tax_percentValue = '0.00';
	}
    } else {
	$tax_percentValue = '0.00';
    }

    $base_total_dueValue = (is_null($result['finalGrandTotal'])) ? '0.0000' : $result['finalGrandTotal'];
    $real_created_atValue = (is_null($result['orderCreationDate'])) ? date("Y-m-d H:i:s") : $result['orderCreationDate'];//current date or order creation date
    $created_at_timestampValue = strtotime($real_created_atValue);//set from created date
    $customer_emailValue = (is_null($result['user_email'])) ? (is_null($result['email'])) ? '' : $result['email'] : $result['user_email'];
    $customer_firstnameValue = (is_null($result['user_firstname'])) ? (is_null($result['firstName'])) ? '' : $result['firstName'] : $result['user_firstname'];
    $customer_lastnameValue = (is_null($result['user_lastname'])) ? (is_null($result['lastName'])) ? '' : $result['lastName'] : $result['user_lastname'];
    if (is_null($result['user_firstname'])) {
	$customer_nameValue = '';
    } else {
	$customer_nameValue = $customer_firstnameValue . ' ' . $customer_lastnameValue;
    }
    $customer_nameValue = $customer_firstnameValue . ' ' . $customer_lastnameValue;
    //Lookup customer
    if ($result['user_email'] == NULL) {
	$customer_group_idValue = 0;
    } else {
	$customerQuery = "SELECT `entity_id` FROM `customer_entity` WHERE `email` = '" . $result['user_email'] . "'";
	$customerResults = $writeConnection->query($customerQuery);
	$customerFound = NULL;
	foreach ($customerResults as $customerResult) {  
	    $customerFound = 1;
	}
	if (!$customerFound) {
	    fwrite($transactionLogHandle, "    ->CUSTOMER NOT FOUND    : " . $result['yahooOrderIdNumeric'] . "\n");	    
	    $customer_group_idValue = 0;
	} else {
	    $customer_group_idValue = 1;
	}
    }
    
    $discount_amountValue = (is_null($result['discount'])) ? '0.0000' : $result['discount'];//appears to be actual total discount
    $grand_totalValue = (is_null($result['finalGrandTotal'])) ? '0.0000' : $result['finalGrandTotal'];
    $increment_idValue = $result['yahooOrderIdNumeric'];//import script adds value to 600000000
    $shipping_amountValue = (is_null($result['shippingCost'])) ? '0.0000' : $result['shippingCost'];
    $shipping_incl_taxValue = (is_null($result['shippingCost'])) ? '0.0000' : $result['shippingCost'];
    switch ($result['shippingMethod']) {
	case 'UPS Ground (3-7 Business Days)':
	    $shipping_methodValue = 'ups_03';
	    break;
	case 'APO & FPO Addresses (5-30 Business Days by USPS)':
	    $shipping_methodValue = 'usps_Priority Mail';
	    break;
	case 'UPS Next Day Air (2-3 Business Days)':
	    $shipping_methodValue = 'ups_01';
	    break;
	case '"Alaska, Hawaii, U.S. Virgin Islands & Puerto Rico':
	    $shipping_methodValue = 'usps_Priority Mail';
	    break;
	case 'UPS 2nd Day Air (3-4 Business Days)':
	    $shipping_methodValue = 'ups_02';
	    break;
	case 'International Express (Shipped with Shoebox)':
	    $shipping_methodValue = 'ups_08';
	    break;
	case 'International Express (Shipped without Shoebox)':
	    $shipping_methodValue = 'ups_08';
	    break;
	case 'USPS Priority Mail (4-5 Business Days)':
	    $shipping_methodValue = 'usps_Priority Mail';
	    break;
	case 'UPS 3 Day Select (4-5 Business Days)':
	    $shipping_methodValue = 'ups_12';
	    break;
	case 'EMS - International':
	    $shipping_methodValue = 'ups_08';
	    break;
	case 'Canada Express (4-7 Business Days)':
	    $shipping_methodValue = 'ups_08';
	    break;
	case 'EMS Canada':
	    $shipping_methodValue = 'ups_08';
	    break;
	case 'Christmas Express (Delivered by Dec. 24th)':
	    $shipping_methodValue = 'ups_03';
	    break;
	case 'COD Ground':
	    $shipping_methodValue = 'ups_03';
	    break;
	case 'COD Overnight':
	    $shipping_methodValue = 'ups_01';
	    break;
	case 'Free Christmas Express (Delivered by Dec. 24th)':
	    $shipping_methodValue = 'ups_03';
	    break;
	case 'New Year Express (Delivered by Dec. 31st)':
	    $shipping_methodValue = 'ups_03';
	    break;
	case 'Free UPS Ground (3-7 Business Days)':
	    $shipping_methodValue = 'ups_03';
	    break;
	case 'COD 2-Day':
	    $shipping_methodValue = 'ups_02';
	    break;
	case 'MSI International Express':
	    $shipping_methodValue = 'ups_08';
	    break;
	case 'Customer Pickup':
	    $shipping_methodValue = 'ups_03';
	    break;
	case 'UPS Ground':
	    $shipping_methodValue = 'ups_03';
	    break;
	case 'UPS 2nd Day Air':
	    $shipping_methodValue = 'ups_02';
	    break;
	case 'APO & FPO Addresses':
	    $shipping_methodValue = 'usps_Priority Mail';
	    break;
	case 'UPS Next Day Air Saver':
	    $shipping_methodValue = 'ups_01';
	    break;
	case 'UPS 3 Day Select':
	    $shipping_methodValue = 'ups_12';
	    break;
	case 'International Express':
	    $shipping_methodValue = 'ups_08';
	    break;
	case 'USPS Priority Mail':
	    $shipping_methodValue = 'usps_Priority Mail';
	    break;
	case 'Canada Express':
	    $shipping_methodValue = 'ups_08';
	    break;
	case 'UPS Next Day Air':
	    $shipping_methodValue = 'ups_01';
	    break;
	case 'Holiday Shipping (Delivered by Dec. 24th)':
	    $shipping_methodValue = 'ups_03';
	    break;
	default://case 'NULL'
	    $shipping_methodValue = '';
	    break;
    }
    
    $stateValue = 'new';//Always new -- will set on order status update
    $statusValue = 'pending';//Always Pending -- will set on order status update
    $subtotalValue = (is_null($result['finalSubTotal'])) ? '0.0000' : $result['finalSubTotal'];
    $subtotal_incl_taxValue = (is_null($result['finalSubTotal'])) ? '0.0000' : $result['finalSubTotal'];
    $tax_amountValue = (is_null($result['taxTotal'])) ? '0.0000' : $result['taxTotal'];
    $total_dueValue = (is_null($result['finalGrandTotal'])) ? '0.0000' : $result['finalGrandTotal'];

    // Get total qty
    $itemsQuery = "SELECT * FROM `se_orderitem` WHERE `yahooOrderIdNumeric` = " . $result['yahooOrderIdNumeric'];
    $itemsResult = $writeConnection->query($itemsQuery);
    $itemCount = 0;
    foreach ($itemsResult as $itemResult) {
	$itemCount += 1;//number of items not quantites
    }
    if ($itemCount == 0) {
	fwrite($transactionLogHandle, "      ->NO ITEMS FOUND      : " . $result['yahooOrderIdNumeric'] . "\n");
    }
    $total_qty_orderedValue = $itemCount . '.0000';//Derived from item qty count
    $updated_atValue = date("Y-m-d H:i:s");
    $updated_at_timestampValue = strtotime($real_created_atValue);
    $weightValue = '0.0000'; //No weight data available

    //Shipping
    $shippingCityValue = (is_null($result['shipCity'])) ? '' : $result['shipCity'];
    $shippingCountryValue = (is_null($result['shipCountry'])) ? '' : $result['shipCountry'];
    $shippingEmailValue = (is_null($result['email'])) ? '' : $result['email'];
    $shippingFirstnameValue = (is_null($result['shipName'])) ? '' : $result['shipName'];
    $shippingLastnameValue = '';
    $shippingNameValue = $result['shipName'];
    $shippingPostcodeValue = (is_null($result['shipZip'])) ? '' : $result['shipZip'];
    if (strtolower($shippingCountryValue) == 'united states') {
	$shippingRegionValue = (is_null($result['shipState'])) ? '' : strtoupper($result['shipState']);
    } else {
	$shippingRegionValue = (is_null($result['shipState'])) ? '' : $result['shipState'];
    }
    $shippingRegion_idValue = '';//Seems to work without conversion
    if ((!is_null($result['shipAddress1']) && $result['shipAddress1'] != '') && (is_null($result['shipAddress2']) || $result['shipAddress2'] == '')) {
	$shippingStreetValue = $result['shipAddress1'];
    } elseif ((!is_null($result['shipAddress1']) && $result['shipAddress1'] != '') && (!is_null($result['shipAddress2']) && $result['shipAddress2'] != '')) {
	$shippingStreetValue = $result['shipAddress2'] . '&#10;' . $result['shipAddress2']; //Include CR/LF
    } elseif ((is_null($result['shipAddress1']) || $result['shipAddress1'] == '') && (!is_null($result['shipAddress2']) && $result['shipAddress2'] != '')) {
	$shippingStreetValue = $result['shipAddress2'];
    } else {
	$shippingStreetValue = '';
    }
    $shippingTelephoneValue = (is_null($result['shipPhone'])) ? '' : $result['shipPhone'];
    
    //Billing
    $billingCityValue = (is_null($result['billCity'])) ? '' : $result['billCity'];
    $billingCountryValue = (is_null($result['billCountry'])) ? '' : $result['billCountry'];
    $billingEmailValue = (is_null($result['email'])) ? '' : $result['email'];
    $billingFirstnameValue = (is_null($result['billName'])) ? '' : $result['billName'];
    $billingLastnameValue = '';
    $billingNameValue = $result['billName'];
    $billingPostcodeValue = (is_null($result['billZip'])) ? '' : $result['billZip'];
    if (strtolower($billingCountryValue) == 'united states') {
	$billingRegionValue = (is_null($result['billState'])) ? '' : strtoupper($result['billState']);
    } else {
	$billingRegionValue = (is_null($result['billState'])) ? '' : $result['billState'];
    }
    $billingRegion_idValue = '';//Seems to work without conversion
    if ((!is_null($result['billAddress1']) && $result['billAddress1'] != '') && (is_null($result['billAddress2']) || $result['billAddress2'] == '')) {
	$billingStreetValue = $result['billAddress1'];
    } elseif ((!is_null($result['billAddress1']) && $result['billAddress1'] != '') && (!is_null($result['billAddress2']) && $result['billAddress2'] != '')) {
	$billingStreetValue = $result['billAddress2'] . '&#10;' . $result['billAddress2']; //Include CR/LF
    } elseif ((is_null($result['billAddress1']) || $result['billAddress1'] == '') && (!is_null($result['billAddress2']) && $result['billAddress2'] != '')) {
	$billingStreetValue = $result['billAddress2'];
    } else {
	$billingStreetValue = '';
    }
    $billingTelephoneValue = (is_null($result['billPhone'])) ? '' : $result['billPhone'];
    
    //Payment
    switch ($result['paymentType']) {
	case 'Visa':
	    $cc_typeValue = 'VI';
            $methodValue = 'authorizenet';
	    break;
	case 'AMEX':
	    $cc_typeValue = 'AE';
            $methodValue = 'authorizenet';
            break;
	case 'Mastercard':
	    $cc_typeValue = 'MC';
            $methodValue = 'authorizenet';
            break;
	case 'Discover':
	    $cc_typeValue = 'DI';
            $methodValue = 'authorizenet';
	    break;
	case 'Paypal':
	    $cc_typeValue = '';
            $methodValue = 'paypal_express';
	    break;
	case 'C.O.D.':
	    $cc_typeValue = '';
            $methodValue = 'free';
	    break;
	case 'GiftCert':
	    //100% payed with giftcard
	    $cc_typeValue = '';
            $methodValue = 'free';
	    break;
	default: //NULL
	    $cc_typeValue = '';
	    $methodValue = 'free';
    }
    $amount_authorizedValue = (is_null($result['finalGrandTotal'])) ? '' : $result['finalGrandTotal'];
    $amount_orderedValue = (is_null($result['finalGrandTotal'])) ? '' : $result['finalGrandTotal'];
    $base_amount_authorizedValue = (is_null($result['finalGrandTotal'])) ? '' : $result['finalGrandTotal'];
    $base_amount_orderedValue = (is_null($result['finalGrandTotal'])) ? '' : $result['finalGrandTotal'];
    $base_shipping_amountValue = (is_null($result['shippingCost'])) ? '' : $result['shippingCost'];
    $cc_approvalValue = (is_null($result['ccApprovalNumber'])) ? '' : $result['ccApprovalNumber'];
    $cc_cid_statusValue = (is_null($result['ccCvvResponse'])) ? '' : $result['ccCvvResponse'];
    $ccExpiration = (is_null($result['ccExpiration'])) ? '' : explode('/', $result['ccExpiration']);
    if (is_null($ccExpiration)) {
        $cc_exp_monthValue = '';
        $cc_exp_yearValue = '';
    } else {
        $cc_exp_monthValue = $ccExpiration[0];
        $cc_exp_yearValue = $ccExpiration[1];
    }
    $cc_last4Value = (is_null($result['ccExpiration'])) ? '' : '****';//data not available
    $anet_trans_methodValue = '';//***
    $cc_avs_statusValue = '';//***
    $cc_trans_idValue = '';//***
    $last_trans_idValue = '';//***
    $shipping_amountValue = (is_null($result['shippingCost'])) ? '' : $result['shippingCost'];

    $order = $orders->appendChild($dom->createElement('order'));

    $adjustment_negative = $order->appendChild($dom->createElement('adjustment_negative'));
    $adjustment_negative->appendChild($dom->createTextNode(''));
    $adjustment_positive = $order->appendChild($dom->createElement('adjustment_positive'));
    $adjustment_positive->appendChild($dom->createTextNode(''));
    $applied_rule_ids = $order->appendChild($dom->createElement('applied_rule_ids'));
    $applied_rule_ids->appendChild($dom->createTextNode(''));//none used -- only used for military until migration complete
    $base_adjustment_negative = $order->appendChild($dom->createElement('base_adjustment_negative'));
    $base_adjustment_negative->appendChild($dom->createTextNode(''));
    $base_adjustment_positive = $order->appendChild($dom->createElement('base_adjustment_positive'));
    $base_adjustment_positive->appendChild($dom->createTextNode(''));
    $base_currency_code = $order->appendChild($dom->createElement('base_currency_code'));
    $base_currency_code->appendChild($dom->createTextNode('USD'));// Always USD
    $base_custbalance_amount = $order->appendChild($dom->createElement('base_custbalance_amount'));
    $base_custbalance_amount->appendChild($dom->createTextNode(''));
    $base_discount_amount = $order->appendChild($dom->createElement('base_discount_amount'));
    $base_discount_amount->appendChild($dom->createTextNode($base_discount_amountValue));
    $base_discount_canceled = $order->appendChild($dom->createElement('base_discount_canceled'));
    $base_discount_canceled->appendChild($dom->createTextNode(''));
    $base_discount_invoiced = $order->appendChild($dom->createElement('base_discount_invoiced'));
    $base_discount_invoiced->appendChild($dom->createTextNode(''));
    $base_discount_refunded = $order->appendChild($dom->createElement('base_discount_refunded'));
    $base_discount_refunded->appendChild($dom->createTextNode(''));
    $base_grand_total = $order->appendChild($dom->createElement('base_grand_total'));
    $base_grand_total->appendChild($dom->createTextNode($base_grand_totalValue));
    $base_hidden_tax_amount = $order->appendChild($dom->createElement('base_hidden_tax_amount'));
    $base_hidden_tax_amount->appendChild($dom->createTextNode(''));
    $base_hidden_tax_invoiced = $order->appendChild($dom->createElement('base_hidden_tax_invoiced'));
    $base_hidden_tax_invoiced->appendChild($dom->createTextNode(''));
    $base_hidden_tax_refunded = $order->appendChild($dom->createElement('base_hidden_tax_refunded'));
    $base_hidden_tax_refunded->appendChild($dom->createTextNode(''));
    $base_shipping_amount = $order->appendChild($dom->createElement('base_shipping_amount'));
    $base_shipping_amount->appendChild($dom->createTextNode($base_shipping_amountValue));
    $base_shipping_canceled = $order->appendChild($dom->createElement('base_shipping_canceled'));
    $base_shipping_canceled->appendChild($dom->createTextNode(''));
    $base_shipping_discount_amount = $order->appendChild($dom->createElement('base_shipping_discount_amount'));
    $base_shipping_discount_amount->appendChild($dom->createTextNode('0.0000'));//Always 0
    $base_shipping_hidden_tax_amount = $order->appendChild($dom->createElement('base_shipping_hidden_tax_amount'));
    $base_shipping_hidden_tax_amount->appendChild($dom->createTextNode(''));
    $base_shipping_incl_tax = $order->appendChild($dom->createElement('base_shipping_incl_tax'));
    $base_shipping_incl_tax->appendChild($dom->createTextNode($base_shipping_incl_taxValue));
    $base_shipping_invoiced = $order->appendChild($dom->createElement('base_shipping_invoiced'));
    $base_shipping_invoiced->appendChild($dom->createTextNode(''));
    $base_shipping_refunded = $order->appendChild($dom->createElement('base_shipping_refunded'));
    $base_shipping_refunded->appendChild($dom->createTextNode(''));
    $base_shipping_tax_amount = $order->appendChild($dom->createElement('base_shipping_tax_amount'));
    $base_shipping_tax_amount->appendChild($dom->createTextNode('0.0000'));//Always 0
    $base_shipping_tax_refunded = $order->appendChild($dom->createElement('base_shipping_tax_refunded'));
    $base_shipping_tax_refunded->appendChild($dom->createTextNode(''));
    $base_subtotal = $order->appendChild($dom->createElement('base_subtotal'));
    $base_subtotal->appendChild($dom->createTextNode($base_subtotalValue));
    $base_subtotal_canceled = $order->appendChild($dom->createElement('base_subtotal_canceled'));
    $base_subtotal_canceled->appendChild($dom->createTextNode(''));
    $base_subtotal_incl_tax = $order->appendChild($dom->createElement('base_subtotal_incl_tax'));
    $base_subtotal_incl_tax->appendChild($dom->createTextNode($base_subtotal_incl_taxValue));
    $base_subtotal_invoiced = $order->appendChild($dom->createElement('base_subtotal_invoiced'));
    $base_subtotal_invoiced->appendChild($dom->createTextNode(''));
    $base_subtotal_refunded = $order->appendChild($dom->createElement('base_subtotal_refunded'));
    $base_subtotal_refunded->appendChild($dom->createTextNode(''));
    $base_tax_amount = $order->appendChild($dom->createElement('base_tax_amount'));
    $base_tax_amount->appendChild($dom->createTextNode($base_tax_amountValue));
    $base_tax_canceled = $order->appendChild($dom->createElement('base_tax_canceled'));
    $base_tax_canceled->appendChild($dom->createTextNode(''));
    $base_tax_invoiced = $order->appendChild($dom->createElement('base_tax_invoiced'));
    $base_tax_invoiced->appendChild($dom->createTextNode(''));
    $base_tax_refunded = $order->appendChild($dom->createElement('base_tax_refunded'));
    $base_tax_refunded->appendChild($dom->createTextNode(''));
    $base_to_global_rate = $order->appendChild($dom->createElement('base_to_global_rate'));
    $base_to_global_rate->appendChild($dom->createTextNode('1'));//Always 1
    $base_to_order_rate = $order->appendChild($dom->createElement('base_to_order_rate'));
    $base_to_order_rate->appendChild($dom->createTextNode('1'));//Always 1
    $base_total_canceled = $order->appendChild($dom->createElement('base_total_canceled'));
    $base_total_canceled->appendChild($dom->createTextNode('0.0000'));
    $base_total_due = $order->appendChild($dom->createElement('base_total_due'));
    $base_total_due->appendChild($dom->createTextNode($base_total_dueValue));
    $base_total_invoiced = $order->appendChild($dom->createElement('base_total_invoiced'));
    $base_total_invoiced->appendChild($dom->createTextNode('0.0000'));
    $base_total_invoiced_cost = $order->appendChild($dom->createElement('base_total_invoiced_cost'));
    $base_total_invoiced_cost->appendChild($dom->createTextNode(''));
    $base_total_offline_refunded = $order->appendChild($dom->createElement('base_total_offline_refunded'));
    $base_total_offline_refunded->appendChild($dom->createTextNode('0.0000'));
    $base_total_online_refunded = $order->appendChild($dom->createElement('base_total_online_refunded'));
    $base_total_online_refunded->appendChild($dom->createTextNode('0.0000'));
    $base_total_paid = $order->appendChild($dom->createElement('base_total_paid'));
    $base_total_paid->appendChild($dom->createTextNode('0.0000'));
    $base_total_qty_ordered = $order->appendChild($dom->createElement('base_total_qty_ordered'));
    $base_total_qty_ordered->appendChild($dom->createTextNode(''));//Always NULL
    $base_total_refunded = $order->appendChild($dom->createElement('base_total_refunded'));
    $base_total_refunded->appendChild($dom->createTextNode('0.0000'));
    $can_ship_partially = $order->appendChild($dom->createElement('can_ship_partially'));
    $can_ship_partially->appendChild($dom->createTextNode(''));
    $can_ship_partially_item = $order->appendChild($dom->createElement('can_ship_partially_item'));
    $can_ship_partially_item->appendChild($dom->createTextNode(''));
    $coupon_code = $order->appendChild($dom->createElement('coupon_code'));
    $coupon_code->appendChild($dom->createTextNode(''));
    $real_created_at = $order->appendChild($dom->createElement('real_created_at'));
    $real_created_at->appendChild($dom->createTextNode($real_created_atValue));
    $created_at_timestamp = $order->appendChild($dom->createElement('created_at_timestamp'));
    $created_at_timestamp->appendChild($dom->createTextNode($created_at_timestampValue));
    $custbalance_amount = $order->appendChild($dom->createElement('custbalance_amount'));
    $custbalance_amount->appendChild($dom->createTextNode(''));
    $customer_dob = $order->appendChild($dom->createElement('customer_dob'));
    $customer_dob->appendChild($dom->createTextNode(''));
    $customer_email = $order->appendChild($dom->createElement('customer_email'));
    $customer_email->appendChild($dom->createTextNode($customer_emailValue));
    $customer_firstname = $order->appendChild($dom->createElement('customer_firstname'));
    $customer_firstname->appendChild($dom->createTextNode($customer_firstnameValue));
    $customer_gender = $order->appendChild($dom->createElement('customer_gender'));
    $customer_gender->appendChild($dom->createTextNode(''));
    $customer_group_id = $order->appendChild($dom->createElement('customer_group_id'));
    $customer_group_id->appendChild($dom->createTextNode($customer_group_idValue));
    $customer_lastname = $order->appendChild($dom->createElement('customer_lastname'));
    $customer_lastname->appendChild($dom->createTextNode($customer_lastnameValue));
    $customer_middlename = $order->appendChild($dom->createElement('customer_middlename'));
    $customer_middlename->appendChild($dom->createTextNode(''));
    $customer_name = $order->appendChild($dom->createElement('customer_name'));
    $customer_name->appendChild($dom->createTextNode($customer_nameValue));
    $customer_note = $order->appendChild($dom->createElement('customer_note'));
    $customer_note->appendChild($dom->createTextNode(''));
    $customer_note_notify = $order->appendChild($dom->createElement('customer_note_notify'));
    $customer_note_notify->appendChild($dom->createTextNode('1'));
    $customer_prefix = $order->appendChild($dom->createElement('customer_prefix'));
    $customer_prefix->appendChild($dom->createTextNode(''));
    $customer_suffix = $order->appendChild($dom->createElement('customer_suffix'));
    $customer_suffix->appendChild($dom->createTextNode(''));
    $customer_taxvat = $order->appendChild($dom->createElement('customer_taxvat'));
    $customer_taxvat->appendChild($dom->createTextNode(''));
    $discount_amount = $order->appendChild($dom->createElement('discount_amount'));
    $discount_amount->appendChild($dom->createTextNode($discount_amountValue));
    $discount_canceled = $order->appendChild($dom->createElement('discount_canceled'));
    $discount_canceled->appendChild($dom->createTextNode(''));
    $discount_invoiced = $order->appendChild($dom->createElement('discount_invoiced'));
    $discount_invoiced->appendChild($dom->createTextNode(''));
    $discount_refunded = $order->appendChild($dom->createElement('discount_refunded'));
    $discount_refunded->appendChild($dom->createTextNode(''));
    $email_sent = $order->appendChild($dom->createElement('email_sent'));
    $email_sent->appendChild($dom->createTextNode('1'));//Always 1
    $ext_customer_id = $order->appendChild($dom->createElement('ext_customer_id'));
    $ext_customer_id->appendChild($dom->createTextNode(''));
    $ext_order_id = $order->appendChild($dom->createElement('ext_order_id'));
    $ext_order_id->appendChild($dom->createTextNode(''));
    $forced_do_shipment_with_invoice = $order->appendChild($dom->createElement('forced_do_shipment_with_invoice'));
    $forced_do_shipment_with_invoice->appendChild($dom->createTextNode(''));
    $global_currency_code = $order->appendChild($dom->createElement('global_currency_code'));
    $global_currency_code->appendChild($dom->createTextNode('USD'));
    $grand_total = $order->appendChild($dom->createElement('grand_total'));
    $grand_total->appendChild($dom->createTextNode($grand_totalValue));
    $hidden_tax_amount = $order->appendChild($dom->createElement('hidden_tax_amount'));
    $hidden_tax_amount->appendChild($dom->createTextNode(''));
    $hidden_tax_invoiced = $order->appendChild($dom->createElement('hidden_tax_invoiced'));
    $hidden_tax_invoiced->appendChild($dom->createTextNode(''));
    $hidden_tax_refunded = $order->appendChild($dom->createElement('hidden_tax_refunded'));
    $hidden_tax_refunded->appendChild($dom->createTextNode(''));
    $hold_before_state = $order->appendChild($dom->createElement('hold_before_state'));
    $hold_before_state->appendChild($dom->createTextNode(''));
    $hold_before_status = $order->appendChild($dom->createElement('hold_before_status'));
    $hold_before_status->appendChild($dom->createTextNode(''));
    $increment_id = $order->appendChild($dom->createElement('increment_id'));
    $increment_id->appendChild($dom->createTextNode($increment_idValue));
    $is_hold = $order->appendChild($dom->createElement('is_hold'));
    $is_hold->appendChild($dom->createTextNode(''));
    $is_multi_payment = $order->appendChild($dom->createElement('is_multi_payment'));
    $is_multi_payment->appendChild($dom->createTextNode(''));
    $is_virtual = $order->appendChild($dom->createElement('is_virtual'));
    $is_virtual->appendChild($dom->createTextNode('0'));//Always 0
    $order_currency_code = $order->appendChild($dom->createElement('order_currency_code'));
    $order_currency_code->appendChild($dom->createTextNode('USD'));
    $payment_authorization_amount = $order->appendChild($dom->createElement('payment_authorization_amount'));
    $payment_authorization_amount->appendChild($dom->createTextNode(''));
    $payment_authorization_expiration = $order->appendChild($dom->createElement('payment_authorization_expiration'));
    $payment_authorization_expiration->appendChild($dom->createTextNode(''));
    $paypal_ipn_customer_notified = $order->appendChild($dom->createElement('paypal_ipn_customer_notified'));
    $paypal_ipn_customer_notified->appendChild($dom->createTextNode(''));
    $real_order_id = $order->appendChild($dom->createElement('real_order_id'));
    $real_order_id->appendChild($dom->createTextNode(''));
    $remote_ip = $order->appendChild($dom->createElement('remote_ip'));
    $remote_ip->appendChild($dom->createTextNode(''));
    $shipping_amount = $order->appendChild($dom->createElement('shipping_amount'));
    $shipping_amount->appendChild($dom->createTextNode($shipping_amountValue));
    $shipping_canceled = $order->appendChild($dom->createElement('shipping_canceled'));
    $shipping_canceled->appendChild($dom->createTextNode(''));
    $shipping_discount_amount = $order->appendChild($dom->createElement('shipping_discount_amount'));
    $shipping_discount_amount->appendChild($dom->createTextNode('0.0000'));
    $shipping_hidden_tax_amount = $order->appendChild($dom->createElement('shipping_hidden_tax_amount'));
    $shipping_hidden_tax_amount->appendChild($dom->createTextNode(''));
    $shipping_incl_tax = $order->appendChild($dom->createElement('shipping_incl_tax'));
    $shipping_incl_tax->appendChild($dom->createTextNode($shipping_incl_taxValue));
    $shipping_invoiced = $order->appendChild($dom->createElement('shipping_invoiced'));
    $shipping_invoiced->appendChild($dom->createTextNode(''));
    $shipping_method = $order->appendChild($dom->createElement('shipping_method'));
    $shipping_method->appendChild($dom->createTextNode($shipping_methodValue));
    $shipping_refunded = $order->appendChild($dom->createElement('shipping_refunded'));
    $shipping_refunded->appendChild($dom->createTextNode(''));
    $shipping_tax_amount = $order->appendChild($dom->createElement('shipping_tax_amount'));
    $shipping_tax_amount->appendChild($dom->createTextNode('0.0000'));
    $shipping_tax_refunded = $order->appendChild($dom->createElement('shipping_tax_refunded'));
    $shipping_tax_refunded->appendChild($dom->createTextNode(''));
    $state = $order->appendChild($dom->createElement('state'));
    $state->appendChild($dom->createTextNode($stateValue));
    $status = $order->appendChild($dom->createElement('status'));
    $status->appendChild($dom->createTextNode($statusValue));
    $store = $order->appendChild($dom->createElement('store'));
    $store->appendChild($dom->createTextNode('sneakerhead_cn'));
    $subtotal = $order->appendChild($dom->createElement('subTotal'));
    $subtotal->appendChild($dom->createTextNode($subtotalValue));
    $subtotal_canceled = $order->appendChild($dom->createElement('subtotal_canceled'));
    $subtotal_canceled->appendChild($dom->createTextNode(''));
    $subtotal_incl_tax = $order->appendChild($dom->createElement('subtotal_incl_tax'));
    $subtotal_incl_tax->appendChild($dom->createTextNode($subtotal_incl_taxValue));
    $subtotal_invoiced = $order->appendChild($dom->createElement('subtotal_invoiced'));
    $subtotal_invoiced->appendChild($dom->createTextNode(''));
    $subtotal_refunded = $order->appendChild($dom->createElement('subtotal_refunded'));
    $subtotal_refunded->appendChild($dom->createTextNode(''));
    $tax_amount = $order->appendChild($dom->createElement('tax_amount'));
    $tax_amount->appendChild($dom->createTextNode($tax_amountValue));
    $tax_canceled = $order->appendChild($dom->createElement('tax_canceled'));
    $tax_canceled->appendChild($dom->createTextNode(''));
    $tax_invoiced = $order->appendChild($dom->createElement('tax_invoiced'));
    $tax_invoiced->appendChild($dom->createTextNode(''));
    $tax_percent = $order->appendChild($dom->createElement('tax_percent'));
    $tax_percent->appendChild($dom->createTextNode($tax_percentValue));
    $tax_refunded = $order->appendChild($dom->createElement('tax_refunded'));
    $tax_refunded->appendChild($dom->createTextNode(''));
    $total_canceled = $order->appendChild($dom->createElement('total_canceled'));
    $total_canceled->appendChild($dom->createTextNode('0.0000'));
    $total_due = $order->appendChild($dom->createElement('total_due'));
    $total_due->appendChild($dom->createTextNode($total_dueValue));
    $total_invoiced = $order->appendChild($dom->createElement('total_invoiced'));
    $total_invoiced->appendChild($dom->createTextNode('0.0000'));
    $total_item_count = $order->appendChild($dom->createElement('total_item_count'));
    $total_item_count->appendChild($dom->createTextNode(''));
    $total_offline_refunded = $order->appendChild($dom->createElement('total_offline_refunded'));
    $total_offline_refunded->appendChild($dom->createTextNode('0.0000'));
    $total_online_refunded = $order->appendChild($dom->createElement('total_online_refunded'));
    $total_online_refunded->appendChild($dom->createTextNode('0.0000'));
    $total_paid = $order->appendChild($dom->createElement('total_paid'));
    $total_paid->appendChild($dom->createTextNode('0.0000'));
    $total_qty_ordered = $order->appendChild($dom->createElement('total_qty_ordered'));
    $total_qty_ordered->appendChild($dom->createTextNode($total_qty_orderedValue));
    $total_refunded = $order->appendChild($dom->createElement('total_refunded'));
    $total_refunded->appendChild($dom->createTextNode('0.0000'));
    $tracking_numbers = $order->appendChild($dom->createElement('tracking_numbers'));
    $tracking_numbers->appendChild($dom->createTextNode(''));
    $updated_at = $order->appendChild($dom->createElement('updated_at'));
    $updated_at->appendChild($dom->createTextNode($updated_atValue));
    $updated_at_timestamp = $order->appendChild($dom->createElement('updated_at_timestamp'));
    $updated_at_timestamp->appendChild($dom->createTextNode($updated_at_timestampValue));
    $weight = $order->appendChild($dom->createElement('weight'));
    $weight->appendChild($dom->createTextNode($weightValue));
    $x_forwarded_for = $order->appendChild($dom->createElement('x_forwarded_for'));
    $x_forwarded_for->appendChild($dom->createTextNode(''));

    //Build shipping
    $shipping_address = $order->appendChild($dom->createElement('shipping_address'));
    
    $shippingCity = $shipping_address->appendChild($dom->createElement('city'));
    $shippingCity->appendChild($dom->createTextNode($shippingCityValue));
    $shippingCompany = $shipping_address->appendChild($dom->createElement('company'));
    $shippingCompany->appendChild($dom->createTextNode(''));
    $shippingCountry = $shipping_address->appendChild($dom->createElement('country'));
    $shippingCountry->appendChild($dom->createTextNode($shippingCountryValue));
    $shippingCountry_id = $shipping_address->appendChild($dom->createElement('country_id'));
    $shippingCountry_id->appendChild($dom->createTextNode(''));
    $shippingCountry_iso2 = $shipping_address->appendChild($dom->createElement('country_iso2'));
    $shippingCountry_iso2->appendChild($dom->createTextNode(''));
    $shippingCountry_iso3 = $shipping_address->appendChild($dom->createElement('country_iso3'));
    $shippingCountry_iso3->appendChild($dom->createTextNode(''));
    $shippingEmail = $shipping_address->appendChild($dom->createElement('email'));
    $shippingEmail->appendChild($dom->createTextNode($shippingEmailValue));
    $shippingFax = $shipping_address->appendChild($dom->createElement('fax'));
    $shippingFax->appendChild($dom->createTextNode(''));
    $shippingFirstname = $shipping_address->appendChild($dom->createElement('firstname'));
    $shippingFirstname->appendChild($dom->createTextNode($shippingFirstnameValue));
    $shippingLastname = $shipping_address->appendChild($dom->createElement('lastname'));
    $shippingLastname->appendChild($dom->createTextNode($shippingLastnameValue));
    $shippingMiddlename = $shipping_address->appendChild($dom->createElement('middlename'));
    $shippingMiddlename->appendChild($dom->createTextNode(''));
    $shippingName = $shipping_address->appendChild($dom->createElement('name'));
    $shippingName->appendChild($dom->createTextNode($shippingNameValue));
    $shippingPostcode = $shipping_address->appendChild($dom->createElement('postcode'));
    $shippingPostcode->appendChild($dom->createTextNode($shippingPostcodeValue));
    $shippingPrefix = $shipping_address->appendChild($dom->createElement('prefix'));
    $shippingPrefix->appendChild($dom->createTextNode(''));
    $shippingRegion = $shipping_address->appendChild($dom->createElement('region'));
    $shippingRegion->appendChild($dom->createTextNode($shippingRegionValue));
    $shippingRegion_id = $shipping_address->appendChild($dom->createElement('region_id'));
    $shippingRegion_id->appendChild($dom->createTextNode($shippingRegion_idValue));
    $shippingRegion_iso2 = $shipping_address->appendChild($dom->createElement('region_iso2'));
    $shippingRegion_iso2->appendChild($dom->createTextNode(''));
    $shippingStreet = $shipping_address->appendChild($dom->createElement('street'));
    $shippingStreet->appendChild($dom->createTextNode($shippingStreetValue));
    $shippingSuffix = $shipping_address->appendChild($dom->createElement('suffix'));
    $shippingSuffix->appendChild($dom->createTextNode(''));
    $shippingTelephone = $shipping_address->appendChild($dom->createElement('telephone'));
    $shippingTelephone->appendChild($dom->createTextNode($shippingTelephoneValue));

    // Build billing
    $billing_address = $order->appendChild($dom->createElement('billing_address'));
    
    $billingCity = $billing_address->appendChild($dom->createElement('city'));
    $billingCity->appendChild($dom->createTextNode($billingCityValue));
    $billingCompany = $billing_address->appendChild($dom->createElement('company'));
    $billingCompany->appendChild($dom->createTextNode(''));
    $billingCountry = $billing_address->appendChild($dom->createElement('country'));
    $billingCountry->appendChild($dom->createTextNode($billingCountryValue));
    $billingCountry_id = $billing_address->appendChild($dom->createElement('country_id'));
    $billingCountry_id->appendChild($dom->createTextNode(''));
    $billingCountry_iso2 = $billing_address->appendChild($dom->createElement('country_iso2'));
    $billingCountry_iso2->appendChild($dom->createTextNode(''));
    $billingCountry_iso3 = $billing_address->appendChild($dom->createElement('country_iso3'));
    $billingCountry_iso3->appendChild($dom->createTextNode(''));
    $billingEmail = $billing_address->appendChild($dom->createElement('email'));
    $billingEmail->appendChild($dom->createTextNode($billingEmailValue));
    $billingFax = $billing_address->appendChild($dom->createElement('fax'));
    $billingFax->appendChild($dom->createTextNode(''));
    $billingFirstname = $billing_address->appendChild($dom->createElement('firstname'));
    $billingFirstname->appendChild($dom->createTextNode($billingFirstnameValue));
    $billingLastname = $billing_address->appendChild($dom->createElement('lastname'));
    $billingLastname->appendChild($dom->createTextNode($billingLastnameValue));
    $billingMiddlename = $billing_address->appendChild($dom->createElement('middlename'));
    $billingMiddlename->appendChild($dom->createTextNode(''));
    $billingName = $billing_address->appendChild($dom->createElement('name'));
    $billingName->appendChild($dom->createTextNode($billingNameValue));
    $billingPostcode = $billing_address->appendChild($dom->createElement('postcode'));
    $billingPostcode->appendChild($dom->createTextNode($billingPostcodeValue));
    $billingPrefix = $billing_address->appendChild($dom->createElement('prefix'));
    $billingPrefix->appendChild($dom->createTextNode(''));
    $billingRegion = $billing_address->appendChild($dom->createElement('region'));
    $billingRegion->appendChild($dom->createTextNode($billingRegionValue));
    $billingRegion_id = $billing_address->appendChild($dom->createElement('region_id'));
    $billingRegion_id->appendChild($dom->createTextNode($billingRegion_idValue));
    $billingRegion_iso2 = $billing_address->appendChild($dom->createElement('region_iso2'));
    $billingRegion_iso2->appendChild($dom->createTextNode(''));
    $billingStreet = $billing_address->appendChild($dom->createElement('street'));
    $billingStreet->appendChild($dom->createTextNode($billingStreetValue));
    $billingSuffix = $billing_address->appendChild($dom->createElement('suffix'));
    $billingSuffix->appendChild($dom->createTextNode(''));
    $billingTelephone = $billing_address->appendChild($dom->createElement('telephone'));
    $billingTelephone->appendChild($dom->createTextNode($billingTelephoneValue));
    
    // Build payment

    $payment = $order->appendChild($dom->createElement('payment'));

    $account_status = $payment->appendChild($dom->createElement('account_status'));
    $account_status->appendChild($dom->createTextNode(''));
    $address_status = $payment->appendChild($dom->createElement('address_status'));
    $address_status->appendChild($dom->createTextNode(''));
    $amount = $payment->appendChild($dom->createElement('amount'));
    $amount->appendChild($dom->createTextNode(''));
    $amount_authorized = $payment->appendChild($dom->createElement('amount_authorized'));
    $amount_authorized->appendChild($dom->createTextNode($amount_authorizedValue));
    $amount_canceled = $payment->appendChild($dom->createElement('amount_canceled'));
    $amount_canceled->appendChild($dom->createTextNode(''));
    $amount_ordered = $payment->appendChild($dom->createElement('amount_ordered'));
    $amount_ordered->appendChild($dom->createTextNode($amount_orderedValue));
    $amount_paid = $payment->appendChild($dom->createElement('amount_paid'));
    $amount_paid->appendChild($dom->createTextNode(''));
    $amount_refunded = $payment->appendChild($dom->createElement('amount_refunded'));
    $amount_refunded->appendChild($dom->createTextNode(''));
    $anet_trans_method = $payment->appendChild($dom->createElement('anet_trans_method'));
    $anet_trans_method->appendChild($dom->createTextNode($anet_trans_methodValue));
    $base_amount_authorized = $payment->appendChild($dom->createElement('base_amount_authorized'));
    $base_amount_authorized->appendChild($dom->createTextNode($base_amount_authorizedValue));
    $base_amount_canceled = $payment->appendChild($dom->createElement('base_amount_canceled'));
    $base_amount_canceled->appendChild($dom->createTextNode(''));
    $base_amount_ordered = $payment->appendChild($dom->createElement('base_amount_ordered'));
    $base_amount_ordered->appendChild($dom->createTextNode($base_amount_orderedValue));
    $base_amount_paid = $payment->appendChild($dom->createElement('base_amount_paid'));
    $base_amount_paid->appendChild($dom->createTextNode(''));
    $base_amount_paid_online = $payment->appendChild($dom->createElement('base_amount_paid_online'));
    $base_amount_paid_online->appendChild($dom->createTextNode(''));
    $base_amount_refunded = $payment->appendChild($dom->createElement('base_amount_refunded'));
    $base_amount_refunded->appendChild($dom->createTextNode(''));
    $base_amount_refunded_online = $payment->appendChild($dom->createElement('base_amount_refunded_online'));
    $base_amount_refunded_online->appendChild($dom->createTextNode(''));
    $base_shipping_amount = $payment->appendChild($dom->createElement('base_shipping_amount'));
    $base_shipping_amount->appendChild($dom->createTextNode($base_shipping_amountValue));
    $base_shipping_captured = $payment->appendChild($dom->createElement('base_shipping_captured'));
    $base_shipping_captured->appendChild($dom->createTextNode(''));
    $base_shipping_refunded = $payment->appendChild($dom->createElement('base_shipping_refunded'));
    $base_shipping_refunded->appendChild($dom->createTextNode(''));
    $cc_approval = $payment->appendChild($dom->createElement('cc_approval'));
    $cc_approval->appendChild($dom->createTextNode($cc_approvalValue));
    $cc_avs_status = $payment->appendChild($dom->createElement('cc_avs_status'));
    $cc_avs_status->appendChild($dom->createTextNode($cc_avs_statusValue));
    $cc_cid_status = $payment->appendChild($dom->createElement('cc_cid_status'));
    $cc_cid_status->appendChild($dom->createTextNode($cc_cid_statusValue));
    $cc_debug_request_body = $payment->appendChild($dom->createElement('cc_debug_request_body'));
    $cc_debug_request_body->appendChild($dom->createTextNode(''));
    $cc_debug_response_body = $payment->appendChild($dom->createElement('cc_debug_response_body'));
    $cc_debug_response_body->appendChild($dom->createTextNode(''));
    $cc_debug_response_serialized = $payment->appendChild($dom->createElement('cc_debug_response_serialized'));
    $cc_debug_response_serialized->appendChild($dom->createTextNode(''));
    $cc_exp_month = $payment->appendChild($dom->createElement('cc_exp_month'));
    $cc_exp_month->appendChild($dom->createTextNode($cc_exp_monthValue));
    $cc_exp_year = $payment->appendChild($dom->createElement('cc_exp_year'));
    $cc_exp_year->appendChild($dom->createTextNode($cc_exp_yearValue));
    $cc_last4 = $payment->appendChild($dom->createElement('cc_last4'));
    $cc_last4->appendChild($dom->createTextNode($cc_last4Value));
    $cc_number_enc = $payment->appendChild($dom->createElement('cc_number_enc'));
    $cc_number_enc->appendChild($dom->createTextNode(''));
    $cc_owner = $payment->appendChild($dom->createElement('cc_owner'));
    $cc_owner->appendChild($dom->createTextNode(''));
    $cc_raw_request = $payment->appendChild($dom->createElement('cc_raw_request'));
    $cc_raw_request->appendChild($dom->createTextNode(''));
    $cc_raw_response = $payment->appendChild($dom->createElement('cc_raw_response'));
    $cc_raw_response->appendChild($dom->createTextNode(''));
    $cc_secure_verify = $payment->appendChild($dom->createElement('cc_secure_verify'));
    $cc_secure_verify->appendChild($dom->createTextNode(''));
    $cc_ss_issue = $payment->appendChild($dom->createElement('cc_ss_issue'));
    $cc_ss_issue->appendChild($dom->createTextNode(''));
    $cc_ss_start_month = $payment->appendChild($dom->createElement('cc_ss_start_month'));
    $cc_ss_start_month->appendChild($dom->createTextNode('0'));//appears to be 0 since not used
    $cc_ss_start_year = $payment->appendChild($dom->createElement('cc_ss_start_year'));
    $cc_ss_start_year->appendChild($dom->createTextNode('0'));//appears to be 0 since not used
    $cc_status = $payment->appendChild($dom->createElement('cc_status'));
    $cc_status->appendChild($dom->createTextNode(''));
    $cc_status_description = $payment->appendChild($dom->createElement('cc_status_description'));
    $cc_status_description->appendChild($dom->createTextNode(''));
    $cc_trans_id = $payment->appendChild($dom->createElement('cc_trans_id'));
    $cc_trans_id->appendChild($dom->createTextNode($cc_trans_idValue));
    $cc_type = $payment->appendChild($dom->createElement('cc_type'));
    $cc_type->appendChild($dom->createTextNode($cc_typeValue));
    $cybersource_token = $payment->appendChild($dom->createElement('cybersource_token'));
    $cybersource_token->appendChild($dom->createTextNode(''));
    $echeck_account_name = $payment->appendChild($dom->createElement('echeck_account_name'));
    $echeck_account_name->appendChild($dom->createTextNode(''));
    $echeck_account_type = $payment->appendChild($dom->createElement('echeck_account_type'));
    $echeck_account_type->appendChild($dom->createTextNode(''));
    $echeck_bank_name = $payment->appendChild($dom->createElement('echeck_bank_name'));
    $echeck_bank_name->appendChild($dom->createTextNode(''));
    $echeck_routing_number = $payment->appendChild($dom->createElement('echeck_routing_number'));
    $echeck_routing_number->appendChild($dom->createTextNode(''));
    $echeck_type = $payment->appendChild($dom->createElement('echeck_type'));
    $echeck_type->appendChild($dom->createTextNode(''));
    $flo2cash_account_id = $payment->appendChild($dom->createElement('flo2cash_account_id'));
    $flo2cash_account_id->appendChild($dom->createTextNode(''));
    $ideal_issuer_id = $payment->appendChild($dom->createElement('ideal_issuer_id'));
    $ideal_issuer_id->appendChild($dom->createTextNode(''));
    $ideal_issuer_title = $payment->appendChild($dom->createElement('ideal_issuer_title'));
    $ideal_issuer_title->appendChild($dom->createTextNode(''));
    $ideal_transaction_checked = $payment->appendChild($dom->createElement('ideal_transaction_checked'));
    $ideal_transaction_checked->appendChild($dom->createTextNode(''));
    $last_trans_id = $payment->appendChild($dom->createElement('last_trans_id'));
    $last_trans_id->appendChild($dom->createTextNode($last_trans_idValue));
    $method = $payment->appendChild($dom->createElement('method'));
    $method->appendChild($dom->createTextNode($methodValue));
    $paybox_question_number = $payment->appendChild($dom->createElement('paybox_question_number'));
    $paybox_question_number->appendChild($dom->createTextNode(''));
    $paybox_request_number = $payment->appendChild($dom->createElement('paybox_request_number'));
    $paybox_request_number->appendChild($dom->createTextNode(''));
    $po_number = $payment->appendChild($dom->createElement('po_number'));
    $po_number->appendChild($dom->createTextNode(''));
    $protection_eligibility = $payment->appendChild($dom->createElement('protection_eligibility'));
    $protection_eligibility->appendChild($dom->createTextNode(''));
    $shipping_amount = $payment->appendChild($dom->createElement('shipping_amount'));
    $shipping_amount->appendChild($dom->createTextNode($shipping_amountValue));
    $shipping_captured = $payment->appendChild($dom->createElement('shipping_captured'));
    $shipping_captured->appendChild($dom->createTextNode(''));
    $shipping_refunded = $payment->appendChild($dom->createElement('shipping_refunded'));
    $shipping_refunded->appendChild($dom->createTextNode(''));

    // Build Items
    $items = $order->appendChild($dom->createElement('items'));
    $itemsQuery = "SELECT * FROM `se_orderitem` WHERE `yahooOrderIdNumeric` = " . $result['yahooOrderIdNumeric'];
    $itemsResult = $writeConnection->query($itemsQuery);
    $itemNumber = 1;
    foreach ($itemsResult as $itemResult) {
        $item = $items->appendChild($dom->createElement('item'));

	//Set variables
	$base_original_priceValue = $itemResult['unitPrice'];
	$base_priceValue = $itemResult['unitPrice'];
	$base_row_totalValue = $itemResult['qtyOrdered'] * $itemResult['unitPrice'];
	$real_nameValue = $itemResult['lineItemDescription'];
	$nameValue = $itemResult['lineItemDescription'];
	$original_priceValue = $itemResult['unitPrice'];
	$priceValue = $itemResult['unitPrice'];
	$qty_orderedValue = $itemResult['qtyOrdered'];
	$row_totalValue = $itemResult['qtyOrdered'] * $itemResult['unitPrice'];
	$length = strlen(end(explode('-', $itemResult['productCode'])));
	$real_skuValue = substr($itemResult['productCode'], 0, -($length + 1));
        
	fwrite($transactionLogHandle, "      ->ADDING CONFIGURABLE : " . $itemNumber . " -> " . $real_skuValue . "\n");

	$skuValue = 'Product ' . $itemNumber;
	if (!is_null($result['shipCountry']) && $result['shipState'] == 'CA') {
	    if (strtolower($result['shipCountry']) == 'united states') {
		$tax_percentCalcValue = '0.0875';
		$tax_percentValue = '8.75';
		$base_price_incl_taxValue = round($priceValue + ($priceValue * $tax_percentCalcValue), 4);//
		$base_row_total_incl_taxValue = round($qty_orderedValue * ($priceValue + ($priceValue * $tax_percentCalcValue)), 4);//
		$base_tax_amountValue = round($priceValue * $tax_percentCalcValue, 4);//THIS MAY BE WRONG -- QTY or ONE
		$price_incl_taxValue = round($priceValue + ($priceValue * $tax_percentCalcValue), 4);//
		$row_total_incl_taxValue = round($qty_orderedValue * ($priceValue + ($priceValue * $tax_percentCalcValue)), 4);//
		$tax_amountValue = round($priceValue * $tax_percentCalcValue, 4);//
	    } else {
		$tax_percentValue = '0.00';
		$base_price_incl_taxValue = $priceValue;
		$base_row_total_incl_taxValue = $qty_orderedValue * $priceValue;
		$base_tax_amountValue = '0.00';
		$price_incl_taxValue = $priceValue;
		$row_total_incl_taxValue = $qty_orderedValue * $priceValue;
		$tax_amountValue = '0.00';		
	    }
	} else {
	    $tax_percentValue = '0.00';
	    $base_price_incl_taxValue = $priceValue;
	    $base_row_total_incl_taxValue = $qty_orderedValue * $priceValue;
	    $base_tax_amountValue = '0.00';
	    $price_incl_taxValue = $priceValue;
	    $row_total_incl_taxValue = $qty_orderedValue * $priceValue;
	    $tax_amountValue = '0.00';	
	}

	//Create line item
	$amount_refunded = $item->appendChild($dom->createElement('amount_refunded'));
	$amount_refunded->appendChild($dom->createTextNode('0')); //Always 0
	$applied_rule_ids = $item->appendChild($dom->createElement('applied_rule_ids'));
	$applied_rule_ids->appendChild($dom->createTextNode(''));
	$base_amount_refunded = $item->appendChild($dom->createElement('base_amount_refunded'));
	$base_amount_refunded->appendChild($dom->createTextNode('0')); //Always 0
	$base_cost = $item->appendChild($dom->createElement('base_cost'));
	$base_cost->appendChild($dom->createTextNode(''));
	$base_discount_amount = $item->appendChild($dom->createElement('base_discount_amount'));
	$base_discount_amount->appendChild($dom->createTextNode('0')); //Always 0
	$base_discount_invoiced = $item->appendChild($dom->createElement('base_discount_invoiced'));
	$base_discount_invoiced->appendChild($dom->createTextNode('0')); //Always 0
	$base_hidden_tax_amount = $item->appendChild($dom->createElement('base_hidden_tax_amount'));
	$base_hidden_tax_amount->appendChild($dom->createTextNode(''));
	$base_hidden_tax_invoiced = $item->appendChild($dom->createElement('base_hidden_tax_invoiced'));
	$base_hidden_tax_invoiced->appendChild($dom->createTextNode(''));
	$base_hidden_tax_refunded = $item->appendChild($dom->createElement('base_hidden_tax_refunded'));
	$base_hidden_tax_refunded->appendChild($dom->createTextNode(''));
	$base_original_price = $item->appendChild($dom->createElement('base_original_price'));
	$base_original_price->appendChild($dom->createTextNode($base_original_priceValue));
	$base_price = $item->appendChild($dom->createElement('base_price'));
	$base_price->appendChild($dom->createTextNode($base_priceValue));
	$base_price_incl_tax = $item->appendChild($dom->createElement('base_price_incl_tax'));
	$base_price_incl_tax->appendChild($dom->createTextNode($base_price_incl_taxValue));
	$base_row_invoiced = $item->appendChild($dom->createElement('base_row_invoiced'));
	$base_row_invoiced->appendChild($dom->createTextNode('0'));
	$base_row_total = $item->appendChild($dom->createElement('base_row_total'));
	$base_row_total->appendChild($dom->createTextNode($base_row_totalValue));
	$base_row_total_incl_tax = $item->appendChild($dom->createElement('base_row_total_incl_tax'));
	$base_row_total_incl_tax->appendChild($dom->createTextNode($base_row_total_incl_taxValue));
	$base_tax_amount = $item->appendChild($dom->createElement('base_tax_amount'));
	$base_tax_amount->appendChild($dom->createTextNode($base_tax_amountValue));
	$base_tax_before_discount = $item->appendChild($dom->createElement('base_tax_before_discount'));
	$base_tax_before_discount->appendChild($dom->createTextNode(''));
	$base_tax_invoiced = $item->appendChild($dom->createElement('base_tax_invoiced'));
	$base_tax_invoiced->appendChild($dom->createTextNode('0')); //Always 0
	$base_weee_tax_applied_amount = $item->appendChild($dom->createElement('base_weee_tax_applied_amount'));
	$base_weee_tax_applied_amount->appendChild($dom->createTextNode('0')); //Always 0
	$base_weee_tax_applied_row_amount = $item->appendChild($dom->createElement('base_weee_tax_applied_row_amount'));
	$base_weee_tax_applied_row_amount->appendChild($dom->createTextNode('0')); //Always 0
	$base_weee_tax_disposition = $item->appendChild($dom->createElement('base_weee_tax_disposition'));
	$base_weee_tax_disposition->appendChild($dom->createTextNode('0')); //Always 0
	$base_weee_tax_row_disposition = $item->appendChild($dom->createElement('base_weee_tax_row_disposition'));
	$base_weee_tax_row_disposition->appendChild($dom->createTextNode('0')); //Always 0
	$description = $item->appendChild($dom->createElement('description'));
	$description->appendChild($dom->createTextNode(''));
	$discount_amount = $item->appendChild($dom->createElement('discount_amount'));
	$discount_amount->appendChild($dom->createTextNode('0')); //Always 0
	$discount_invoiced = $item->appendChild($dom->createElement('discount_invoiced'));
	$discount_invoiced->appendChild($dom->createTextNode('0')); //Always 0
	$discount_percent = $item->appendChild($dom->createElement('discount_percent'));
	$discount_percent->appendChild($dom->createTextNode('0')); //Always 0
	$free_shipping = $item->appendChild($dom->createElement('free_shipping'));
	$free_shipping->appendChild($dom->createTextNode('0')); //Always 0
	$hidden_tax_amount = $item->appendChild($dom->createElement('hidden_tax_amount'));
	$hidden_tax_amount->appendChild($dom->createTextNode(''));
	$hidden_tax_canceled = $item->appendChild($dom->createElement('hidden_tax_canceled'));
	$hidden_tax_canceled->appendChild($dom->createTextNode(''));
	$hidden_tax_invoiced = $item->appendChild($dom->createElement('hidden_tax_invoiced'));
	$hidden_tax_invoiced->appendChild($dom->createTextNode(''));
	$hidden_tax_refunded = $item->appendChild($dom->createElement('hidden_tax_refunded'));
	$hidden_tax_refunded->appendChild($dom->createTextNode(''));
	$is_nominal = $item->appendChild($dom->createElement('is_nominal'));
	$is_nominal->appendChild($dom->createTextNode('0')); //Always 0
	$is_qty_decimal = $item->appendChild($dom->createElement('is_qty_decimal'));
	$is_qty_decimal->appendChild($dom->createTextNode('0')); //Always 0
	$is_virtual = $item->appendChild($dom->createElement('is_virtual'));
	$is_virtual->appendChild($dom->createTextNode('0')); //Always 0
	$real_name = $item->appendChild($dom->createElement('real_name'));
	$real_name->appendChild($dom->createTextNode($real_nameValue)); //Always 0
	$name = $item->appendChild($dom->createElement('name'));
	$name->appendChild($dom->createTextNode($nameValue)); //Always 0
	$no_discount = $item->appendChild($dom->createElement('no_discount'));
	$no_discount->appendChild($dom->createTextNode('0')); //Always 0
	$original_price = $item->appendChild($dom->createElement('original_price'));
	$original_price->appendChild($dom->createTextNode($original_priceValue));
	$price = $item->appendChild($dom->createElement('price'));
	$price->appendChild($dom->createTextNode($priceValue));
	$price_incl_tax = $item->appendChild($dom->createElement('price_incl_tax'));
	$price_incl_tax->appendChild($dom->createTextNode($price_incl_taxValue));
	$qty_backordered = $item->appendChild($dom->createElement('qty_backordered'));
	$qty_backordered->appendChild($dom->createTextNode(''));
	$qty_canceled = $item->appendChild($dom->createElement('qty_canceled'));
	$qty_canceled->appendChild($dom->createTextNode('0')); //Always 0
	$qty_invoiced = $item->appendChild($dom->createElement('qty_invoiced'));
	$qty_invoiced->appendChild($dom->createTextNode('0')); //Always 0
	$qty_ordered = $item->appendChild($dom->createElement('qty_ordered'));
	$qty_ordered->appendChild($dom->createTextNode($qty_orderedValue)); //Always 0
	$qty_refunded = $item->appendChild($dom->createElement('qty_refunded'));
	$qty_refunded->appendChild($dom->createTextNode('0')); //Always 0
	$qty_shipped = $item->appendChild($dom->createElement('qty_shipped'));
	$qty_shipped->appendChild($dom->createTextNode('0')); //Always 0
	$row_invoiced = $item->appendChild($dom->createElement('row_invoiced'));
	$row_invoiced->appendChild($dom->createTextNode('0')); //Always 0
	$row_total = $item->appendChild($dom->createElement('row_total'));
	$row_total->appendChild($dom->createTextNode($row_totalValue));
	$row_total_incl_tax = $item->appendChild($dom->createElement('row_total_incl_tax'));
	$row_total_incl_tax->appendChild($dom->createTextNode($row_total_incl_taxValue));
	$row_weight = $item->appendChild($dom->createElement('row_weight'));
	$row_weight->appendChild($dom->createTextNode('0'));
	$real_sku = $item->appendChild($dom->createElement('real_sku'));
	$real_sku->appendChild($dom->createTextNode($real_skuValue));
	$sku = $item->appendChild($dom->createElement('sku'));
	$sku->appendChild($dom->createTextNode($skuValue));
	$tax_amount = $item->appendChild($dom->createElement('tax_amount'));
	$tax_amount->appendChild($dom->createTextNode($tax_amountValue));
	$tax_before_discount = $item->appendChild($dom->createElement('tax_before_discount'));
	$tax_before_discount->appendChild($dom->createTextNode(''));
	$tax_canceled = $item->appendChild($dom->createElement('tax_canceled'));
	$tax_canceled->appendChild($dom->createTextNode(''));
	$tax_invoiced = $item->appendChild($dom->createElement('tax_invoiced'));
	$tax_invoiced->appendChild($dom->createTextNode('0'));
	$tax_percent = $item->appendChild($dom->createElement('tax_percent'));
	$tax_percent->appendChild($dom->createTextNode($tax_percentValue));
	$tax_refunded = $item->appendChild($dom->createElement('tax_refunded'));
	$tax_refunded->appendChild($dom->createTextNode(''));
	$weee_tax_applied = $item->appendChild($dom->createElement('weee_tax_applied'));
	$weee_tax_applied->appendChild($dom->createTextNode('a:0:{}')); //Always 0
	$weee_tax_applied_amount = $item->appendChild($dom->createElement('weee_tax_applied_amount'));
	$weee_tax_applied_amount->appendChild($dom->createTextNode('0')); //Always 0
	$weee_tax_applied_row_amount = $item->appendChild($dom->createElement('weee_tax_applied_row_amount'));
	$weee_tax_applied_row_amount->appendChild($dom->createTextNode('0')); //Always 0
	$weee_tax_disposition = $item->appendChild($dom->createElement('weee_tax_disposition'));
	$weee_tax_disposition->appendChild($dom->createTextNode('0')); //Always 0
	$weee_tax_row_disposition = $item->appendChild($dom->createElement('weee_tax_row_disposition'));
	$weee_tax_row_disposition->appendChild($dom->createTextNode('0')); //Always 0
	$weight = $item->appendChild($dom->createElement('weight'));
	$weight->appendChild($dom->createTextNode('0'));

	//Add simple
	$item = $items->appendChild($dom->createElement('item'));
	
	//Set variables
	$base_original_priceValue = '0.0000';
	$base_priceValue = '0.0000';
	$base_row_totalValue = '0.0000';
	$real_nameValue = $itemResult['lineItemDescription'];
	$nameValue = $itemResult['lineItemDescription'];
	$original_priceValue = '0.0000';
	$priceValue = '0.0000';
	$qty_orderedValue = $itemResult['qtyOrdered'];
	$row_totalValue = '0.0000';
	$real_skuValue = $itemResult['productCode'];
	$skuValue = "Product " . $itemNumber . "-OFFLINE";
	$parent_skuValue = 'Product ' . $itemNumber;//Just for simple
	
	fwrite($transactionLogHandle, "      ->ADDING SIMPLE       : " . $itemNumber . " -> " . $real_skuValue . "\n");

	$tax_percentValue = '0.00';
	$base_price_incl_taxValue = '0.0000';
	$base_row_total_incl_taxValue = '0.0000';
	$base_tax_amountValue = '0.0000';
	$price_incl_taxValue = '0.0000';
	$row_total_incl_taxValue = '0.0000';
	$tax_amountValue = '0.0000';

	//Create line item
	$amount_refunded = $item->appendChild($dom->createElement('amount_refunded'));
	$amount_refunded->appendChild($dom->createTextNode('0')); //Always 0
	$applied_rule_ids = $item->appendChild($dom->createElement('applied_rule_ids'));
	$applied_rule_ids->appendChild($dom->createTextNode(''));
	$base_amount_refunded = $item->appendChild($dom->createElement('base_amount_refunded'));
	$base_amount_refunded->appendChild($dom->createTextNode('0')); //Always 0
	$base_cost = $item->appendChild($dom->createElement('base_cost'));
	$base_cost->appendChild($dom->createTextNode(''));
	$base_discount_amount = $item->appendChild($dom->createElement('base_discount_amount'));
	$base_discount_amount->appendChild($dom->createTextNode('0')); //Always 0
	$base_discount_invoiced = $item->appendChild($dom->createElement('base_discount_invoiced'));
	$base_discount_invoiced->appendChild($dom->createTextNode('0')); //Always 0
	$base_hidden_tax_amount = $item->appendChild($dom->createElement('base_hidden_tax_amount'));
	$base_hidden_tax_amount->appendChild($dom->createTextNode(''));
	$base_hidden_tax_invoiced = $item->appendChild($dom->createElement('base_hidden_tax_invoiced'));
	$base_hidden_tax_invoiced->appendChild($dom->createTextNode(''));
	$base_hidden_tax_refunded = $item->appendChild($dom->createElement('base_hidden_tax_refunded'));
	$base_hidden_tax_refunded->appendChild($dom->createTextNode(''));
	$base_original_price = $item->appendChild($dom->createElement('base_original_price'));
	$base_original_price->appendChild($dom->createTextNode($base_original_priceValue));
	$base_price = $item->appendChild($dom->createElement('base_price'));
	$base_price->appendChild($dom->createTextNode($base_priceValue));
	$base_price_incl_tax = $item->appendChild($dom->createElement('base_price_incl_tax'));
	$base_price_incl_tax->appendChild($dom->createTextNode($base_price_incl_taxValue));
	$base_row_invoiced = $item->appendChild($dom->createElement('base_row_invoiced'));
	$base_row_invoiced->appendChild($dom->createTextNode('0'));
	$base_row_total = $item->appendChild($dom->createElement('base_row_total'));
	$base_row_total->appendChild($dom->createTextNode($base_row_totalValue));
	$base_row_total_incl_tax = $item->appendChild($dom->createElement('base_row_total_incl_tax'));
	$base_row_total_incl_tax->appendChild($dom->createTextNode($base_row_total_incl_taxValue));
	$base_tax_amount = $item->appendChild($dom->createElement('base_tax_amount'));
	$base_tax_amount->appendChild($dom->createTextNode($base_tax_amountValue));
	$base_tax_before_discount = $item->appendChild($dom->createElement('base_tax_before_discount'));
	$base_tax_before_discount->appendChild($dom->createTextNode(''));
	$base_tax_invoiced = $item->appendChild($dom->createElement('base_tax_invoiced'));
	$base_tax_invoiced->appendChild($dom->createTextNode('0')); //Always 0
	$base_weee_tax_applied_amount = $item->appendChild($dom->createElement('base_weee_tax_applied_amount'));
	$base_weee_tax_applied_amount->appendChild($dom->createTextNode('0')); //Always 0
	$base_weee_tax_applied_row_amount = $item->appendChild($dom->createElement('base_weee_tax_applied_row_amount'));
	$base_weee_tax_applied_row_amount->appendChild($dom->createTextNode('0')); //Always 0
	$base_weee_tax_disposition = $item->appendChild($dom->createElement('base_weee_tax_disposition'));
	$base_weee_tax_disposition->appendChild($dom->createTextNode('0')); //Always 0
	$base_weee_tax_row_disposition = $item->appendChild($dom->createElement('base_weee_tax_row_disposition'));
	$base_weee_tax_row_disposition->appendChild($dom->createTextNode('0')); //Always 0
	$description = $item->appendChild($dom->createElement('description'));
	$description->appendChild($dom->createTextNode(''));
	$discount_amount = $item->appendChild($dom->createElement('discount_amount'));
	$discount_amount->appendChild($dom->createTextNode('0')); //Always 0
	$discount_invoiced = $item->appendChild($dom->createElement('discount_invoiced'));
	$discount_invoiced->appendChild($dom->createTextNode('0')); //Always 0
	$discount_percent = $item->appendChild($dom->createElement('discount_percent'));
	$discount_percent->appendChild($dom->createTextNode('0')); //Always 0
	$free_shipping = $item->appendChild($dom->createElement('free_shipping'));
	$free_shipping->appendChild($dom->createTextNode('0')); //Always 0
	$hidden_tax_amount = $item->appendChild($dom->createElement('hidden_tax_amount'));
	$hidden_tax_amount->appendChild($dom->createTextNode(''));
	$hidden_tax_canceled = $item->appendChild($dom->createElement('hidden_tax_canceled'));
	$hidden_tax_canceled->appendChild($dom->createTextNode(''));
	$hidden_tax_invoiced = $item->appendChild($dom->createElement('hidden_tax_invoiced'));
	$hidden_tax_invoiced->appendChild($dom->createTextNode(''));
	$hidden_tax_refunded = $item->appendChild($dom->createElement('hidden_tax_refunded'));
	$hidden_tax_refunded->appendChild($dom->createTextNode(''));
	$is_nominal = $item->appendChild($dom->createElement('is_nominal'));
	$is_nominal->appendChild($dom->createTextNode('0')); //Always 0
	$is_qty_decimal = $item->appendChild($dom->createElement('is_qty_decimal'));
	$is_qty_decimal->appendChild($dom->createTextNode('0')); //Always 0
	$is_virtual = $item->appendChild($dom->createElement('is_virtual'));
	$is_virtual->appendChild($dom->createTextNode('0')); //Always 0
	$real_name = $item->appendChild($dom->createElement('real_name'));
	$real_name->appendChild($dom->createTextNode($real_nameValue)); //Always 0
	$name = $item->appendChild($dom->createElement('nameValue'));
	$name->appendChild($dom->createTextNode($nameValue)); //Always 0
	$no_discount = $item->appendChild($dom->createElement('no_discount'));
	$no_discount->appendChild($dom->createTextNode('0')); //Always 0
	$original_price = $item->appendChild($dom->createElement('original_price'));
	$original_price->appendChild($dom->createTextNode($original_priceValue));
	$parent_sku = $item->appendChild($dom->createElement('parent_sku'));
	$parent_sku->appendChild($dom->createTextNode($parent_skuValue));
	$price = $item->appendChild($dom->createElement('price'));
	$price->appendChild($dom->createTextNode($priceValue));
	$price_incl_tax = $item->appendChild($dom->createElement('price_incl_tax'));
	$price_incl_tax->appendChild($dom->createTextNode($price_incl_taxValue));
	$qty_backordered = $item->appendChild($dom->createElement('qty_backordered'));
	$qty_backordered->appendChild($dom->createTextNode(''));
	$qty_canceled = $item->appendChild($dom->createElement('qty_canceled'));
	$qty_canceled->appendChild($dom->createTextNode('0')); //Always 0
	$qty_invoiced = $item->appendChild($dom->createElement('qty_invoiced'));
	$qty_invoiced->appendChild($dom->createTextNode('0')); //Always 0
	$qty_ordered = $item->appendChild($dom->createElement('qty_ordered'));
	$qty_ordered->appendChild($dom->createTextNode($qty_orderedValue)); //Always 0
	$qty_refunded = $item->appendChild($dom->createElement('qty_refunded'));
	$qty_refunded->appendChild($dom->createTextNode('0')); //Always 0
	$qty_shipped = $item->appendChild($dom->createElement('qty_shipped'));
	$qty_shipped->appendChild($dom->createTextNode('0')); //Always 0
	$row_invoiced = $item->appendChild($dom->createElement('row_invoiced'));
	$row_invoiced->appendChild($dom->createTextNode('0')); //Always 0
	$row_total = $item->appendChild($dom->createElement('row_total'));
	$row_total->appendChild($dom->createTextNode($row_totalValue));
	$row_total_incl_tax = $item->appendChild($dom->createElement('row_total_incl_tax'));
	$row_total_incl_tax->appendChild($dom->createTextNode($row_total_incl_taxValue));
	$row_weight = $item->appendChild($dom->createElement('row_weight'));
	$row_weight->appendChild($dom->createTextNode('0'));
	$real_sku = $item->appendChild($dom->createElement('real_sku'));
	$real_sku->appendChild($dom->createTextNode($real_skuValue));
	$sku = $item->appendChild($dom->createElement('sku'));
	$sku->appendChild($dom->createTextNode($skuValue));
	$tax_amount = $item->appendChild($dom->createElement('tax_amount'));
	$tax_amount->appendChild($dom->createTextNode($tax_amountValue));
	$tax_before_discount = $item->appendChild($dom->createElement('tax_before_discount'));
	$tax_before_discount->appendChild($dom->createTextNode(''));
	$tax_canceled = $item->appendChild($dom->createElement('tax_canceled'));
	$tax_canceled->appendChild($dom->createTextNode(''));
	$tax_invoiced = $item->appendChild($dom->createElement('tax_invoiced'));
	$tax_invoiced->appendChild($dom->createTextNode('0'));
	$tax_percent = $item->appendChild($dom->createElement('tax_percent'));
	$tax_percent->appendChild($dom->createTextNode($tax_percentValue));
	$tax_refunded = $item->appendChild($dom->createElement('tax_refunded'));
	$tax_refunded->appendChild($dom->createTextNode(''));
	$weee_tax_applied = $item->appendChild($dom->createElement('weee_tax_applied'));
	$weee_tax_applied->appendChild($dom->createTextNode('a:0:{}')); //Always 0
	$weee_tax_applied_amount = $item->appendChild($dom->createElement('weee_tax_applied_amount'));
	$weee_tax_applied_amount->appendChild($dom->createTextNode('0')); //Always 0
	$weee_tax_applied_row_amount = $item->appendChild($dom->createElement('weee_tax_applied_row_amount'));
	$weee_tax_applied_row_amount->appendChild($dom->createTextNode('0')); //Always 0
	$weee_tax_disposition = $item->appendChild($dom->createElement('weee_tax_disposition'));
	$weee_tax_disposition->appendChild($dom->createTextNode('0')); //Always 0
	$weee_tax_row_disposition = $item->appendChild($dom->createElement('weee_tax_row_disposition'));
	$weee_tax_row_disposition->appendChild($dom->createTextNode('0')); //Always 0
	$weight = $item->appendChild($dom->createElement('weight'));
	$weight->appendChild($dom->createTextNode('0'));
	
        $itemNumber++;
    }
}

// Make the output pretty
$dom->formatOutput = true;

// Save the XML string
$xml = $dom->saveXML();

//Write file to post directory
$handle = fopen($toolsXmlDirectory . $orderFilename, 'w');
fwrite($handle, $xml);
fclose($handle);

fwrite($transactionLogHandle, "  ->FINISH TIME   : " . $endTime[5] . '/' . $endTime[4] . '/' . $endTime[3] . ' ' . $endTime[2] . ':' . $endTime[1] . ':' . $endTime[0] . "\n");
fwrite($transactionLogHandle, "  ->CREATED       : ORDER FILE 5 " . $orderFilename . "\n");

fwrite($transactionLogHandle, "->END PROCESSING\n");
//Close transaction log
fclose($transactionLogHandle);

//FILE 6
$realTime = realTime();
//Open transaction log
$transactionLogHandle = fopen($toolsLogsDirectory . 'migration_gen_sneakerhead_order_xml_files_transaction_log', 'a+');
fwrite($transactionLogHandle, "->BEGIN PROCESSING\n");
fwrite($transactionLogHandle, "  ->START TIME    : " . $realTime[5] . '/' . $realTime[4] . '/' . $realTime[3] . ' ' . $realTime[2] . ':' . $realTime[1] . ':' . $realTime[0] . "\n");

//ORDERS
fwrite($transactionLogHandle, "  ->GETTING ORDERS\n");

$resource = Mage::getSingleton('core/resource');
$writeConnection = $resource->getConnection('core_write');
//$startDate = '2009-10-00 00:00:00';//>=
$startDate = '2010-03-15 00:00:00';//>=
$endDate = '2010-03-15 00:00:00';//<
//10,000
//$query = "SELECT `users`.`email` AS `user_email`, `users`.`firstName` AS `user_firstname`, `users`.`lastName` AS `user_lastname`, `se_order`.* FROM `se_order` LEFT JOIN `storeorder` ON `se_order`.`yahooOrderIdNumeric` = `storeorder`.`yahooOrderIdNumeric` LEFT JOIN `users` ON `storeorder`.`uid` = `users`.`uid` WHERE `se_order`.`orderCreationDate` > '2009-10-00 00:00:00' AND `se_order`.`orderCreationDate` < '2009-12-14 00:00:00'";
//25,000
//FOLLOWING 8 QUERIES TO BE RUN SEPARATELY TO GENERATE 8 DIFFERENT FILES
//$query = "SELECT `users`.`email` AS `user_email`, `users`.`firstName` AS `user_firstname`, `users`.`lastName` AS `user_lastname`, `se_order`.* FROM `se_order` LEFT JOIN `storeorder` ON `se_order`.`yahooOrderIdNumeric` = `storeorder`.`yahooOrderIdNumeric` LEFT JOIN `users` ON `storeorder`.`uid` = `users`.`uid` WHERE `se_order`.`orderCreationDate` >= '2009-10-00 00:00:00' AND `se_order`.`orderCreationDate` < '2010-03-15 00:00:00'";//191298 -> 216253
//$query = "SELECT `users`.`email` AS `user_email`, `users`.`firstName` AS `user_firstname`, `users`.`lastName` AS `user_lastname`, `se_order`.* FROM `se_order` LEFT JOIN `storeorder` ON `se_order`.`yahooOrderIdNumeric` = `storeorder`.`yahooOrderIdNumeric` LEFT JOIN `users` ON `storeorder`.`uid` = `users`.`uid` WHERE `se_order`.`orderCreationDate` >= '2010-03-15 00:00:00' AND `se_order`.`orderCreationDate` < '2010-10-28 00:00:00'";//216254 -> 241203
//$query = "SELECT `users`.`email` AS `user_email`, `users`.`firstName` AS `user_firstname`, `users`.`lastName` AS `user_lastname`, `se_order`.* FROM `se_order` LEFT JOIN `storeorder` ON `se_order`.`yahooOrderIdNumeric` = `storeorder`.`yahooOrderIdNumeric` LEFT JOIN `users` ON `storeorder`.`uid` = `users`.`uid` WHERE `se_order`.`orderCreationDate` >= '2010-10-28 00:00:00' AND `se_order`.`orderCreationDate` < '2011-02-27 00:00:00'";//241204 -> 266066
//$query = "SELECT `users`.`email` AS `user_email`, `users`.`firstName` AS `user_firstname`, `users`.`lastName` AS `user_lastname`, `se_order`.* FROM `se_order` LEFT JOIN `storeorder` ON `se_order`.`yahooOrderIdNumeric` = `storeorder`.`yahooOrderIdNumeric` LEFT JOIN `users` ON `storeorder`.`uid` = `users`.`uid` WHERE `se_order`.`orderCreationDate` >= '2011-02-27 00:00:00' AND `se_order`.`orderCreationDate` < '2011-06-27 00:00:00'";//266067 -> 291019
//$query = "SELECT `users`.`email` AS `user_email`, `users`.`firstName` AS `user_firstname`, `users`.`lastName` AS `user_lastname`, `se_order`.* FROM `se_order` LEFT JOIN `storeorder` ON `se_order`.`yahooOrderIdNumeric` = `storeorder`.`yahooOrderIdNumeric` LEFT JOIN `users` ON `storeorder`.`uid` = `users`.`uid` WHERE `se_order`.`orderCreationDate` >= '2011-06-27 00:00:00' AND `se_order`.`orderCreationDate` < '2011-12-09 00:00:00'";//291020 -> 315244
$query = "SELECT `users`.`email` AS `user_email`, `users`.`firstName` AS `user_firstname`, `users`.`lastName` AS `user_lastname`, `se_order`.* FROM `se_order` LEFT JOIN `storeorder` ON `se_order`.`yahooOrderIdNumeric` = `storeorder`.`yahooOrderIdNumeric` LEFT JOIN `users` ON `storeorder`.`uid` = `users`.`uid` WHERE `se_order`.`orderCreationDate` >= '2011-12-09 00:00:00' AND `se_order`.`orderCreationDate` < '2012-04-24 00:00:00'";//315245 -> 340092
//$query = "SELECT `users`.`email` AS `user_email`, `users`.`firstName` AS `user_firstname`, `users`.`lastName` AS `user_lastname`, `se_order`.* FROM `se_order` LEFT JOIN `storeorder` ON `se_order`.`yahooOrderIdNumeric` = `storeorder`.`yahooOrderIdNumeric` LEFT JOIN `users` ON `storeorder`.`uid` = `users`.`uid` WHERE `se_order`.`orderCreationDate` >= '2012-04-24 00:00:00' AND `se_order`.`orderCreationDate` < '2012-10-04 00:00:00'";//340093 -> 364330
//$query = "SELECT `users`.`email` AS `user_email`, `users`.`firstName` AS `user_firstname`, `users`.`lastName` AS `user_lastname`, `se_order`.* FROM `se_order` LEFT JOIN `storeorder` ON `se_order`.`yahooOrderIdNumeric` = `storeorder`.`yahooOrderIdNumeric` LEFT JOIN `users` ON `storeorder`.`uid` = `users`.`uid` WHERE `se_order`.`orderCreationDate` >= '2012-10-04 00:00:00' AND `se_order`.`orderCreationDate` < '2013-02-16 00:00:00'";//364331 -> ???
//$query = "SELECT `users`.`email` AS `user_email`, `users`.`firstName` AS `user_firstname`, `users`.`lastName` AS `user_lastname`, `se_order`.* FROM `se_order` LEFT JOIN `storeorder` ON `se_order`.`yahooOrderIdNumeric` = `storeorder`.`yahooOrderIdNumeric` LEFT JOIN `users` ON `storeorder`.`uid` = `users`.`uid` WHERE `se_order`.`orderCreationDate` >= '2013-02-16 00:00:00' AND `se_order`.`orderCreationDate` < '2013-04-23 00:00:00'";//??? -> ???
//$query = "SELECT `users`.`email` AS `user_email`, `users`.`firstName` AS `user_firstname`, `users`.`lastName` AS `user_lastname`, `se_order`.* FROM `se_order` LEFT JOIN `storeorder` ON `se_order`.`yahooOrderIdNumeric` = `storeorder`.`yahooOrderIdNumeric` LEFT JOIN `users` ON `storeorder`.`uid` = `users`.`uid` WHERE `se_order`.`orderCreationDate` >= '2013-04-23 00:00:00'"; //??? -> current (???)

$results = $writeConnection->query($query);
$orderFilename = "order_6_" . $realTime[5] . $realTime[4] . $realTime[3] . '_' . $realTime[2] . $realTime[1] . $realTime[0] . ".xml";
//Creates XML string and XML document from the DOM representation
$dom = new DomDocument('1.0');

$orders = $dom->appendChild($dom->createElement('orders'));
foreach ($results as $result) {
    
    //Add order data
    fwrite($transactionLogHandle, "    ->ADDING ORDER NUMBER   : " . $result['yahooOrderIdNumeric'] . "\n");
    
    // Set some variables
    $base_discount_amountValue = (is_null($result['discount'])) ? '0.0000' : $result['discount'];//appears to be actual total discount
    $base_grand_totalValue = (is_null($result['finalGrandTotal'])) ? '0.0000' : $result['finalGrandTotal'];
    $base_shipping_amountValue = (is_null($result['shippingCost'])) ? '0.0000' : $result['shippingCost'];
    $base_shipping_incl_taxValue = (is_null($result['shippingCost'])) ? '0.0000' : $result['shippingCost'];
    $base_subtotalValue = (is_null($result['finalSubTotal'])) ? '0.0000' : $result['finalSubTotal'];
    $base_subtotal_incl_taxValue = (is_null($result['finalSubTotal'])) ? '0.0000' : $result['finalSubTotal'];
    $base_tax_amountValue = (is_null($result['taxTotal'])) ? '0.0000' : $result['taxTotal'];

    if (!is_null($result['shipCountry']) && $result['shipState'] == 'CA') {
	if (strtolower($result['shipCountry']) == 'united states') {
	    $tax_percentValue = '8.75';
	} else {
	    $tax_percentValue = '0.00';
	}
    } else {
	$tax_percentValue = '0.00';
    }

    $base_total_dueValue = (is_null($result['finalGrandTotal'])) ? '0.0000' : $result['finalGrandTotal'];
    $real_created_atValue = (is_null($result['orderCreationDate'])) ? date("Y-m-d H:i:s") : $result['orderCreationDate'];//current date or order creation date
    $created_at_timestampValue = strtotime($real_created_atValue);//set from created date
    $customer_emailValue = (is_null($result['user_email'])) ? (is_null($result['email'])) ? '' : $result['email'] : $result['user_email'];
    $customer_firstnameValue = (is_null($result['user_firstname'])) ? (is_null($result['firstName'])) ? '' : $result['firstName'] : $result['user_firstname'];
    $customer_lastnameValue = (is_null($result['user_lastname'])) ? (is_null($result['lastName'])) ? '' : $result['lastName'] : $result['user_lastname'];
    if (is_null($result['user_firstname'])) {
	$customer_nameValue = '';
    } else {
	$customer_nameValue = $customer_firstnameValue . ' ' . $customer_lastnameValue;
    }
    $customer_nameValue = $customer_firstnameValue . ' ' . $customer_lastnameValue;
    //Lookup customer
    if ($result['user_email'] == NULL) {
	$customer_group_idValue = 0;
    } else {
	$customerQuery = "SELECT `entity_id` FROM `customer_entity` WHERE `email` = '" . $result['user_email'] . "'";
	$customerResults = $writeConnection->query($customerQuery);
	$customerFound = NULL;
	foreach ($customerResults as $customerResult) {  
	    $customerFound = 1;
	}
	if (!$customerFound) {
	    fwrite($transactionLogHandle, "    ->CUSTOMER NOT FOUND    : " . $result['yahooOrderIdNumeric'] . "\n");	    
	    $customer_group_idValue = 0;
	} else {
	    $customer_group_idValue = 1;
	}
    }
    
    $discount_amountValue = (is_null($result['discount'])) ? '0.0000' : $result['discount'];//appears to be actual total discount
    $grand_totalValue = (is_null($result['finalGrandTotal'])) ? '0.0000' : $result['finalGrandTotal'];
    $increment_idValue = $result['yahooOrderIdNumeric'];//import script adds value to 600000000
    $shipping_amountValue = (is_null($result['shippingCost'])) ? '0.0000' : $result['shippingCost'];
    $shipping_incl_taxValue = (is_null($result['shippingCost'])) ? '0.0000' : $result['shippingCost'];
    switch ($result['shippingMethod']) {
	case 'UPS Ground (3-7 Business Days)':
	    $shipping_methodValue = 'ups_03';
	    break;
	case 'APO & FPO Addresses (5-30 Business Days by USPS)':
	    $shipping_methodValue = 'usps_Priority Mail';
	    break;
	case 'UPS Next Day Air (2-3 Business Days)':
	    $shipping_methodValue = 'ups_01';
	    break;
	case '"Alaska, Hawaii, U.S. Virgin Islands & Puerto Rico':
	    $shipping_methodValue = 'usps_Priority Mail';
	    break;
	case 'UPS 2nd Day Air (3-4 Business Days)':
	    $shipping_methodValue = 'ups_02';
	    break;
	case 'International Express (Shipped with Shoebox)':
	    $shipping_methodValue = 'ups_08';
	    break;
	case 'International Express (Shipped without Shoebox)':
	    $shipping_methodValue = 'ups_08';
	    break;
	case 'USPS Priority Mail (4-5 Business Days)':
	    $shipping_methodValue = 'usps_Priority Mail';
	    break;
	case 'UPS 3 Day Select (4-5 Business Days)':
	    $shipping_methodValue = 'ups_12';
	    break;
	case 'EMS - International':
	    $shipping_methodValue = 'ups_08';
	    break;
	case 'Canada Express (4-7 Business Days)':
	    $shipping_methodValue = 'ups_08';
	    break;
	case 'EMS Canada':
	    $shipping_methodValue = 'ups_08';
	    break;
	case 'Christmas Express (Delivered by Dec. 24th)':
	    $shipping_methodValue = 'ups_03';
	    break;
	case 'COD Ground':
	    $shipping_methodValue = 'ups_03';
	    break;
	case 'COD Overnight':
	    $shipping_methodValue = 'ups_01';
	    break;
	case 'Free Christmas Express (Delivered by Dec. 24th)':
	    $shipping_methodValue = 'ups_03';
	    break;
	case 'New Year Express (Delivered by Dec. 31st)':
	    $shipping_methodValue = 'ups_03';
	    break;
	case 'Free UPS Ground (3-7 Business Days)':
	    $shipping_methodValue = 'ups_03';
	    break;
	case 'COD 2-Day':
	    $shipping_methodValue = 'ups_02';
	    break;
	case 'MSI International Express':
	    $shipping_methodValue = 'ups_08';
	    break;
	case 'Customer Pickup':
	    $shipping_methodValue = 'ups_03';
	    break;
	case 'UPS Ground':
	    $shipping_methodValue = 'ups_03';
	    break;
	case 'UPS 2nd Day Air':
	    $shipping_methodValue = 'ups_02';
	    break;
	case 'APO & FPO Addresses':
	    $shipping_methodValue = 'usps_Priority Mail';
	    break;
	case 'UPS Next Day Air Saver':
	    $shipping_methodValue = 'ups_01';
	    break;
	case 'UPS 3 Day Select':
	    $shipping_methodValue = 'ups_12';
	    break;
	case 'International Express':
	    $shipping_methodValue = 'ups_08';
	    break;
	case 'USPS Priority Mail':
	    $shipping_methodValue = 'usps_Priority Mail';
	    break;
	case 'Canada Express':
	    $shipping_methodValue = 'ups_08';
	    break;
	case 'UPS Next Day Air':
	    $shipping_methodValue = 'ups_01';
	    break;
	case 'Holiday Shipping (Delivered by Dec. 24th)':
	    $shipping_methodValue = 'ups_03';
	    break;
	default://case 'NULL'
	    $shipping_methodValue = '';
	    break;
    }
    
    $stateValue = 'new';//Always new -- will set on order status update
    $statusValue = 'pending';//Always Pending -- will set on order status update
    $subtotalValue = (is_null($result['finalSubTotal'])) ? '0.0000' : $result['finalSubTotal'];
    $subtotal_incl_taxValue = (is_null($result['finalSubTotal'])) ? '0.0000' : $result['finalSubTotal'];
    $tax_amountValue = (is_null($result['taxTotal'])) ? '0.0000' : $result['taxTotal'];
    $total_dueValue = (is_null($result['finalGrandTotal'])) ? '0.0000' : $result['finalGrandTotal'];

    // Get total qty
    $itemsQuery = "SELECT * FROM `se_orderitem` WHERE `yahooOrderIdNumeric` = " . $result['yahooOrderIdNumeric'];
    $itemsResult = $writeConnection->query($itemsQuery);
    $itemCount = 0;
    foreach ($itemsResult as $itemResult) {
	$itemCount += 1;//number of items not quantites
    }
    if ($itemCount == 0) {
	fwrite($transactionLogHandle, "      ->NO ITEMS FOUND      : " . $result['yahooOrderIdNumeric'] . "\n");
    }
    $total_qty_orderedValue = $itemCount . '.0000';//Derived from item qty count
    $updated_atValue = date("Y-m-d H:i:s");
    $updated_at_timestampValue = strtotime($real_created_atValue);
    $weightValue = '0.0000'; //No weight data available

    //Shipping
    $shippingCityValue = (is_null($result['shipCity'])) ? '' : $result['shipCity'];
    $shippingCountryValue = (is_null($result['shipCountry'])) ? '' : $result['shipCountry'];
    $shippingEmailValue = (is_null($result['email'])) ? '' : $result['email'];
    $shippingFirstnameValue = (is_null($result['shipName'])) ? '' : $result['shipName'];
    $shippingLastnameValue = '';
    $shippingNameValue = $result['shipName'];
    $shippingPostcodeValue = (is_null($result['shipZip'])) ? '' : $result['shipZip'];
    if (strtolower($shippingCountryValue) == 'united states') {
	$shippingRegionValue = (is_null($result['shipState'])) ? '' : strtoupper($result['shipState']);
    } else {
	$shippingRegionValue = (is_null($result['shipState'])) ? '' : $result['shipState'];
    }
    $shippingRegion_idValue = '';//Seems to work without conversion
    if ((!is_null($result['shipAddress1']) && $result['shipAddress1'] != '') && (is_null($result['shipAddress2']) || $result['shipAddress2'] == '')) {
	$shippingStreetValue = $result['shipAddress1'];
    } elseif ((!is_null($result['shipAddress1']) && $result['shipAddress1'] != '') && (!is_null($result['shipAddress2']) && $result['shipAddress2'] != '')) {
	$shippingStreetValue = $result['shipAddress2'] . '&#10;' . $result['shipAddress2']; //Include CR/LF
    } elseif ((is_null($result['shipAddress1']) || $result['shipAddress1'] == '') && (!is_null($result['shipAddress2']) && $result['shipAddress2'] != '')) {
	$shippingStreetValue = $result['shipAddress2'];
    } else {
	$shippingStreetValue = '';
    }
    $shippingTelephoneValue = (is_null($result['shipPhone'])) ? '' : $result['shipPhone'];
    
    //Billing
    $billingCityValue = (is_null($result['billCity'])) ? '' : $result['billCity'];
    $billingCountryValue = (is_null($result['billCountry'])) ? '' : $result['billCountry'];
    $billingEmailValue = (is_null($result['email'])) ? '' : $result['email'];
    $billingFirstnameValue = (is_null($result['billName'])) ? '' : $result['billName'];
    $billingLastnameValue = '';
    $billingNameValue = $result['billName'];
    $billingPostcodeValue = (is_null($result['billZip'])) ? '' : $result['billZip'];
    if (strtolower($billingCountryValue) == 'united states') {
	$billingRegionValue = (is_null($result['billState'])) ? '' : strtoupper($result['billState']);
    } else {
	$billingRegionValue = (is_null($result['billState'])) ? '' : $result['billState'];
    }
    $billingRegion_idValue = '';//Seems to work without conversion
    if ((!is_null($result['billAddress1']) && $result['billAddress1'] != '') && (is_null($result['billAddress2']) || $result['billAddress2'] == '')) {
	$billingStreetValue = $result['billAddress1'];
    } elseif ((!is_null($result['billAddress1']) && $result['billAddress1'] != '') && (!is_null($result['billAddress2']) && $result['billAddress2'] != '')) {
	$billingStreetValue = $result['billAddress2'] . '&#10;' . $result['billAddress2']; //Include CR/LF
    } elseif ((is_null($result['billAddress1']) || $result['billAddress1'] == '') && (!is_null($result['billAddress2']) && $result['billAddress2'] != '')) {
	$billingStreetValue = $result['billAddress2'];
    } else {
	$billingStreetValue = '';
    }
    $billingTelephoneValue = (is_null($result['billPhone'])) ? '' : $result['billPhone'];
    
    //Payment
    switch ($result['paymentType']) {
	case 'Visa':
	    $cc_typeValue = 'VI';
            $methodValue = 'authorizenet';
	    break;
	case 'AMEX':
	    $cc_typeValue = 'AE';
            $methodValue = 'authorizenet';
            break;
	case 'Mastercard':
	    $cc_typeValue = 'MC';
            $methodValue = 'authorizenet';
            break;
	case 'Discover':
	    $cc_typeValue = 'DI';
            $methodValue = 'authorizenet';
	    break;
	case 'Paypal':
	    $cc_typeValue = '';
            $methodValue = 'paypal_express';
	    break;
	case 'C.O.D.':
	    $cc_typeValue = '';
            $methodValue = 'free';
	    break;
	case 'GiftCert':
	    //100% payed with giftcard
	    $cc_typeValue = '';
            $methodValue = 'free';
	    break;
	default: //NULL
	    $cc_typeValue = '';
	    $methodValue = 'free';
    }
    $amount_authorizedValue = (is_null($result['finalGrandTotal'])) ? '' : $result['finalGrandTotal'];
    $amount_orderedValue = (is_null($result['finalGrandTotal'])) ? '' : $result['finalGrandTotal'];
    $base_amount_authorizedValue = (is_null($result['finalGrandTotal'])) ? '' : $result['finalGrandTotal'];
    $base_amount_orderedValue = (is_null($result['finalGrandTotal'])) ? '' : $result['finalGrandTotal'];
    $base_shipping_amountValue = (is_null($result['shippingCost'])) ? '' : $result['shippingCost'];
    $cc_approvalValue = (is_null($result['ccApprovalNumber'])) ? '' : $result['ccApprovalNumber'];
    $cc_cid_statusValue = (is_null($result['ccCvvResponse'])) ? '' : $result['ccCvvResponse'];
    $ccExpiration = (is_null($result['ccExpiration'])) ? '' : explode('/', $result['ccExpiration']);
    if (is_null($ccExpiration)) {
        $cc_exp_monthValue = '';
        $cc_exp_yearValue = '';
    } else {
        $cc_exp_monthValue = $ccExpiration[0];
        $cc_exp_yearValue = $ccExpiration[1];
    }
    $cc_last4Value = (is_null($result['ccExpiration'])) ? '' : '****';//data not available
    $anet_trans_methodValue = '';//***
    $cc_avs_statusValue = '';//***
    $cc_trans_idValue = '';//***
    $last_trans_idValue = '';//***
    $shipping_amountValue = (is_null($result['shippingCost'])) ? '' : $result['shippingCost'];

    $order = $orders->appendChild($dom->createElement('order'));

    $adjustment_negative = $order->appendChild($dom->createElement('adjustment_negative'));
    $adjustment_negative->appendChild($dom->createTextNode(''));
    $adjustment_positive = $order->appendChild($dom->createElement('adjustment_positive'));
    $adjustment_positive->appendChild($dom->createTextNode(''));
    $applied_rule_ids = $order->appendChild($dom->createElement('applied_rule_ids'));
    $applied_rule_ids->appendChild($dom->createTextNode(''));//none used -- only used for military until migration complete
    $base_adjustment_negative = $order->appendChild($dom->createElement('base_adjustment_negative'));
    $base_adjustment_negative->appendChild($dom->createTextNode(''));
    $base_adjustment_positive = $order->appendChild($dom->createElement('base_adjustment_positive'));
    $base_adjustment_positive->appendChild($dom->createTextNode(''));
    $base_currency_code = $order->appendChild($dom->createElement('base_currency_code'));
    $base_currency_code->appendChild($dom->createTextNode('USD'));// Always USD
    $base_custbalance_amount = $order->appendChild($dom->createElement('base_custbalance_amount'));
    $base_custbalance_amount->appendChild($dom->createTextNode(''));
    $base_discount_amount = $order->appendChild($dom->createElement('base_discount_amount'));
    $base_discount_amount->appendChild($dom->createTextNode($base_discount_amountValue));
    $base_discount_canceled = $order->appendChild($dom->createElement('base_discount_canceled'));
    $base_discount_canceled->appendChild($dom->createTextNode(''));
    $base_discount_invoiced = $order->appendChild($dom->createElement('base_discount_invoiced'));
    $base_discount_invoiced->appendChild($dom->createTextNode(''));
    $base_discount_refunded = $order->appendChild($dom->createElement('base_discount_refunded'));
    $base_discount_refunded->appendChild($dom->createTextNode(''));
    $base_grand_total = $order->appendChild($dom->createElement('base_grand_total'));
    $base_grand_total->appendChild($dom->createTextNode($base_grand_totalValue));
    $base_hidden_tax_amount = $order->appendChild($dom->createElement('base_hidden_tax_amount'));
    $base_hidden_tax_amount->appendChild($dom->createTextNode(''));
    $base_hidden_tax_invoiced = $order->appendChild($dom->createElement('base_hidden_tax_invoiced'));
    $base_hidden_tax_invoiced->appendChild($dom->createTextNode(''));
    $base_hidden_tax_refunded = $order->appendChild($dom->createElement('base_hidden_tax_refunded'));
    $base_hidden_tax_refunded->appendChild($dom->createTextNode(''));
    $base_shipping_amount = $order->appendChild($dom->createElement('base_shipping_amount'));
    $base_shipping_amount->appendChild($dom->createTextNode($base_shipping_amountValue));
    $base_shipping_canceled = $order->appendChild($dom->createElement('base_shipping_canceled'));
    $base_shipping_canceled->appendChild($dom->createTextNode(''));
    $base_shipping_discount_amount = $order->appendChild($dom->createElement('base_shipping_discount_amount'));
    $base_shipping_discount_amount->appendChild($dom->createTextNode('0.0000'));//Always 0
    $base_shipping_hidden_tax_amount = $order->appendChild($dom->createElement('base_shipping_hidden_tax_amount'));
    $base_shipping_hidden_tax_amount->appendChild($dom->createTextNode(''));
    $base_shipping_incl_tax = $order->appendChild($dom->createElement('base_shipping_incl_tax'));
    $base_shipping_incl_tax->appendChild($dom->createTextNode($base_shipping_incl_taxValue));
    $base_shipping_invoiced = $order->appendChild($dom->createElement('base_shipping_invoiced'));
    $base_shipping_invoiced->appendChild($dom->createTextNode(''));
    $base_shipping_refunded = $order->appendChild($dom->createElement('base_shipping_refunded'));
    $base_shipping_refunded->appendChild($dom->createTextNode(''));
    $base_shipping_tax_amount = $order->appendChild($dom->createElement('base_shipping_tax_amount'));
    $base_shipping_tax_amount->appendChild($dom->createTextNode('0.0000'));//Always 0
    $base_shipping_tax_refunded = $order->appendChild($dom->createElement('base_shipping_tax_refunded'));
    $base_shipping_tax_refunded->appendChild($dom->createTextNode(''));
    $base_subtotal = $order->appendChild($dom->createElement('base_subtotal'));
    $base_subtotal->appendChild($dom->createTextNode($base_subtotalValue));
    $base_subtotal_canceled = $order->appendChild($dom->createElement('base_subtotal_canceled'));
    $base_subtotal_canceled->appendChild($dom->createTextNode(''));
    $base_subtotal_incl_tax = $order->appendChild($dom->createElement('base_subtotal_incl_tax'));
    $base_subtotal_incl_tax->appendChild($dom->createTextNode($base_subtotal_incl_taxValue));
    $base_subtotal_invoiced = $order->appendChild($dom->createElement('base_subtotal_invoiced'));
    $base_subtotal_invoiced->appendChild($dom->createTextNode(''));
    $base_subtotal_refunded = $order->appendChild($dom->createElement('base_subtotal_refunded'));
    $base_subtotal_refunded->appendChild($dom->createTextNode(''));
    $base_tax_amount = $order->appendChild($dom->createElement('base_tax_amount'));
    $base_tax_amount->appendChild($dom->createTextNode($base_tax_amountValue));
    $base_tax_canceled = $order->appendChild($dom->createElement('base_tax_canceled'));
    $base_tax_canceled->appendChild($dom->createTextNode(''));
    $base_tax_invoiced = $order->appendChild($dom->createElement('base_tax_invoiced'));
    $base_tax_invoiced->appendChild($dom->createTextNode(''));
    $base_tax_refunded = $order->appendChild($dom->createElement('base_tax_refunded'));
    $base_tax_refunded->appendChild($dom->createTextNode(''));
    $base_to_global_rate = $order->appendChild($dom->createElement('base_to_global_rate'));
    $base_to_global_rate->appendChild($dom->createTextNode('1'));//Always 1
    $base_to_order_rate = $order->appendChild($dom->createElement('base_to_order_rate'));
    $base_to_order_rate->appendChild($dom->createTextNode('1'));//Always 1
    $base_total_canceled = $order->appendChild($dom->createElement('base_total_canceled'));
    $base_total_canceled->appendChild($dom->createTextNode('0.0000'));
    $base_total_due = $order->appendChild($dom->createElement('base_total_due'));
    $base_total_due->appendChild($dom->createTextNode($base_total_dueValue));
    $base_total_invoiced = $order->appendChild($dom->createElement('base_total_invoiced'));
    $base_total_invoiced->appendChild($dom->createTextNode('0.0000'));
    $base_total_invoiced_cost = $order->appendChild($dom->createElement('base_total_invoiced_cost'));
    $base_total_invoiced_cost->appendChild($dom->createTextNode(''));
    $base_total_offline_refunded = $order->appendChild($dom->createElement('base_total_offline_refunded'));
    $base_total_offline_refunded->appendChild($dom->createTextNode('0.0000'));
    $base_total_online_refunded = $order->appendChild($dom->createElement('base_total_online_refunded'));
    $base_total_online_refunded->appendChild($dom->createTextNode('0.0000'));
    $base_total_paid = $order->appendChild($dom->createElement('base_total_paid'));
    $base_total_paid->appendChild($dom->createTextNode('0.0000'));
    $base_total_qty_ordered = $order->appendChild($dom->createElement('base_total_qty_ordered'));
    $base_total_qty_ordered->appendChild($dom->createTextNode(''));//Always NULL
    $base_total_refunded = $order->appendChild($dom->createElement('base_total_refunded'));
    $base_total_refunded->appendChild($dom->createTextNode('0.0000'));
    $can_ship_partially = $order->appendChild($dom->createElement('can_ship_partially'));
    $can_ship_partially->appendChild($dom->createTextNode(''));
    $can_ship_partially_item = $order->appendChild($dom->createElement('can_ship_partially_item'));
    $can_ship_partially_item->appendChild($dom->createTextNode(''));
    $coupon_code = $order->appendChild($dom->createElement('coupon_code'));
    $coupon_code->appendChild($dom->createTextNode(''));
    $real_created_at = $order->appendChild($dom->createElement('real_created_at'));
    $real_created_at->appendChild($dom->createTextNode($real_created_atValue));
    $created_at_timestamp = $order->appendChild($dom->createElement('created_at_timestamp'));
    $created_at_timestamp->appendChild($dom->createTextNode($created_at_timestampValue));
    $custbalance_amount = $order->appendChild($dom->createElement('custbalance_amount'));
    $custbalance_amount->appendChild($dom->createTextNode(''));
    $customer_dob = $order->appendChild($dom->createElement('customer_dob'));
    $customer_dob->appendChild($dom->createTextNode(''));
    $customer_email = $order->appendChild($dom->createElement('customer_email'));
    $customer_email->appendChild($dom->createTextNode($customer_emailValue));
    $customer_firstname = $order->appendChild($dom->createElement('customer_firstname'));
    $customer_firstname->appendChild($dom->createTextNode($customer_firstnameValue));
    $customer_gender = $order->appendChild($dom->createElement('customer_gender'));
    $customer_gender->appendChild($dom->createTextNode(''));
    $customer_group_id = $order->appendChild($dom->createElement('customer_group_id'));
    $customer_group_id->appendChild($dom->createTextNode($customer_group_idValue));
    $customer_lastname = $order->appendChild($dom->createElement('customer_lastname'));
    $customer_lastname->appendChild($dom->createTextNode($customer_lastnameValue));
    $customer_middlename = $order->appendChild($dom->createElement('customer_middlename'));
    $customer_middlename->appendChild($dom->createTextNode(''));
    $customer_name = $order->appendChild($dom->createElement('customer_name'));
    $customer_name->appendChild($dom->createTextNode($customer_nameValue));
    $customer_note = $order->appendChild($dom->createElement('customer_note'));
    $customer_note->appendChild($dom->createTextNode(''));
    $customer_note_notify = $order->appendChild($dom->createElement('customer_note_notify'));
    $customer_note_notify->appendChild($dom->createTextNode('1'));
    $customer_prefix = $order->appendChild($dom->createElement('customer_prefix'));
    $customer_prefix->appendChild($dom->createTextNode(''));
    $customer_suffix = $order->appendChild($dom->createElement('customer_suffix'));
    $customer_suffix->appendChild($dom->createTextNode(''));
    $customer_taxvat = $order->appendChild($dom->createElement('customer_taxvat'));
    $customer_taxvat->appendChild($dom->createTextNode(''));
    $discount_amount = $order->appendChild($dom->createElement('discount_amount'));
    $discount_amount->appendChild($dom->createTextNode($discount_amountValue));
    $discount_canceled = $order->appendChild($dom->createElement('discount_canceled'));
    $discount_canceled->appendChild($dom->createTextNode(''));
    $discount_invoiced = $order->appendChild($dom->createElement('discount_invoiced'));
    $discount_invoiced->appendChild($dom->createTextNode(''));
    $discount_refunded = $order->appendChild($dom->createElement('discount_refunded'));
    $discount_refunded->appendChild($dom->createTextNode(''));
    $email_sent = $order->appendChild($dom->createElement('email_sent'));
    $email_sent->appendChild($dom->createTextNode('1'));//Always 1
    $ext_customer_id = $order->appendChild($dom->createElement('ext_customer_id'));
    $ext_customer_id->appendChild($dom->createTextNode(''));
    $ext_order_id = $order->appendChild($dom->createElement('ext_order_id'));
    $ext_order_id->appendChild($dom->createTextNode(''));
    $forced_do_shipment_with_invoice = $order->appendChild($dom->createElement('forced_do_shipment_with_invoice'));
    $forced_do_shipment_with_invoice->appendChild($dom->createTextNode(''));
    $global_currency_code = $order->appendChild($dom->createElement('global_currency_code'));
    $global_currency_code->appendChild($dom->createTextNode('USD'));
    $grand_total = $order->appendChild($dom->createElement('grand_total'));
    $grand_total->appendChild($dom->createTextNode($grand_totalValue));
    $hidden_tax_amount = $order->appendChild($dom->createElement('hidden_tax_amount'));
    $hidden_tax_amount->appendChild($dom->createTextNode(''));
    $hidden_tax_invoiced = $order->appendChild($dom->createElement('hidden_tax_invoiced'));
    $hidden_tax_invoiced->appendChild($dom->createTextNode(''));
    $hidden_tax_refunded = $order->appendChild($dom->createElement('hidden_tax_refunded'));
    $hidden_tax_refunded->appendChild($dom->createTextNode(''));
    $hold_before_state = $order->appendChild($dom->createElement('hold_before_state'));
    $hold_before_state->appendChild($dom->createTextNode(''));
    $hold_before_status = $order->appendChild($dom->createElement('hold_before_status'));
    $hold_before_status->appendChild($dom->createTextNode(''));
    $increment_id = $order->appendChild($dom->createElement('increment_id'));
    $increment_id->appendChild($dom->createTextNode($increment_idValue));
    $is_hold = $order->appendChild($dom->createElement('is_hold'));
    $is_hold->appendChild($dom->createTextNode(''));
    $is_multi_payment = $order->appendChild($dom->createElement('is_multi_payment'));
    $is_multi_payment->appendChild($dom->createTextNode(''));
    $is_virtual = $order->appendChild($dom->createElement('is_virtual'));
    $is_virtual->appendChild($dom->createTextNode('0'));//Always 0
    $order_currency_code = $order->appendChild($dom->createElement('order_currency_code'));
    $order_currency_code->appendChild($dom->createTextNode('USD'));
    $payment_authorization_amount = $order->appendChild($dom->createElement('payment_authorization_amount'));
    $payment_authorization_amount->appendChild($dom->createTextNode(''));
    $payment_authorization_expiration = $order->appendChild($dom->createElement('payment_authorization_expiration'));
    $payment_authorization_expiration->appendChild($dom->createTextNode(''));
    $paypal_ipn_customer_notified = $order->appendChild($dom->createElement('paypal_ipn_customer_notified'));
    $paypal_ipn_customer_notified->appendChild($dom->createTextNode(''));
    $real_order_id = $order->appendChild($dom->createElement('real_order_id'));
    $real_order_id->appendChild($dom->createTextNode(''));
    $remote_ip = $order->appendChild($dom->createElement('remote_ip'));
    $remote_ip->appendChild($dom->createTextNode(''));
    $shipping_amount = $order->appendChild($dom->createElement('shipping_amount'));
    $shipping_amount->appendChild($dom->createTextNode($shipping_amountValue));
    $shipping_canceled = $order->appendChild($dom->createElement('shipping_canceled'));
    $shipping_canceled->appendChild($dom->createTextNode(''));
    $shipping_discount_amount = $order->appendChild($dom->createElement('shipping_discount_amount'));
    $shipping_discount_amount->appendChild($dom->createTextNode('0.0000'));
    $shipping_hidden_tax_amount = $order->appendChild($dom->createElement('shipping_hidden_tax_amount'));
    $shipping_hidden_tax_amount->appendChild($dom->createTextNode(''));
    $shipping_incl_tax = $order->appendChild($dom->createElement('shipping_incl_tax'));
    $shipping_incl_tax->appendChild($dom->createTextNode($shipping_incl_taxValue));
    $shipping_invoiced = $order->appendChild($dom->createElement('shipping_invoiced'));
    $shipping_invoiced->appendChild($dom->createTextNode(''));
    $shipping_method = $order->appendChild($dom->createElement('shipping_method'));
    $shipping_method->appendChild($dom->createTextNode($shipping_methodValue));
    $shipping_refunded = $order->appendChild($dom->createElement('shipping_refunded'));
    $shipping_refunded->appendChild($dom->createTextNode(''));
    $shipping_tax_amount = $order->appendChild($dom->createElement('shipping_tax_amount'));
    $shipping_tax_amount->appendChild($dom->createTextNode('0.0000'));
    $shipping_tax_refunded = $order->appendChild($dom->createElement('shipping_tax_refunded'));
    $shipping_tax_refunded->appendChild($dom->createTextNode(''));
    $state = $order->appendChild($dom->createElement('state'));
    $state->appendChild($dom->createTextNode($stateValue));
    $status = $order->appendChild($dom->createElement('status'));
    $status->appendChild($dom->createTextNode($statusValue));
    $store = $order->appendChild($dom->createElement('store'));
    $store->appendChild($dom->createTextNode('sneakerhead_cn'));
    $subtotal = $order->appendChild($dom->createElement('subTotal'));
    $subtotal->appendChild($dom->createTextNode($subtotalValue));
    $subtotal_canceled = $order->appendChild($dom->createElement('subtotal_canceled'));
    $subtotal_canceled->appendChild($dom->createTextNode(''));
    $subtotal_incl_tax = $order->appendChild($dom->createElement('subtotal_incl_tax'));
    $subtotal_incl_tax->appendChild($dom->createTextNode($subtotal_incl_taxValue));
    $subtotal_invoiced = $order->appendChild($dom->createElement('subtotal_invoiced'));
    $subtotal_invoiced->appendChild($dom->createTextNode(''));
    $subtotal_refunded = $order->appendChild($dom->createElement('subtotal_refunded'));
    $subtotal_refunded->appendChild($dom->createTextNode(''));
    $tax_amount = $order->appendChild($dom->createElement('tax_amount'));
    $tax_amount->appendChild($dom->createTextNode($tax_amountValue));
    $tax_canceled = $order->appendChild($dom->createElement('tax_canceled'));
    $tax_canceled->appendChild($dom->createTextNode(''));
    $tax_invoiced = $order->appendChild($dom->createElement('tax_invoiced'));
    $tax_invoiced->appendChild($dom->createTextNode(''));
    $tax_percent = $order->appendChild($dom->createElement('tax_percent'));
    $tax_percent->appendChild($dom->createTextNode($tax_percentValue));
    $tax_refunded = $order->appendChild($dom->createElement('tax_refunded'));
    $tax_refunded->appendChild($dom->createTextNode(''));
    $total_canceled = $order->appendChild($dom->createElement('total_canceled'));
    $total_canceled->appendChild($dom->createTextNode('0.0000'));
    $total_due = $order->appendChild($dom->createElement('total_due'));
    $total_due->appendChild($dom->createTextNode($total_dueValue));
    $total_invoiced = $order->appendChild($dom->createElement('total_invoiced'));
    $total_invoiced->appendChild($dom->createTextNode('0.0000'));
    $total_item_count = $order->appendChild($dom->createElement('total_item_count'));
    $total_item_count->appendChild($dom->createTextNode(''));
    $total_offline_refunded = $order->appendChild($dom->createElement('total_offline_refunded'));
    $total_offline_refunded->appendChild($dom->createTextNode('0.0000'));
    $total_online_refunded = $order->appendChild($dom->createElement('total_online_refunded'));
    $total_online_refunded->appendChild($dom->createTextNode('0.0000'));
    $total_paid = $order->appendChild($dom->createElement('total_paid'));
    $total_paid->appendChild($dom->createTextNode('0.0000'));
    $total_qty_ordered = $order->appendChild($dom->createElement('total_qty_ordered'));
    $total_qty_ordered->appendChild($dom->createTextNode($total_qty_orderedValue));
    $total_refunded = $order->appendChild($dom->createElement('total_refunded'));
    $total_refunded->appendChild($dom->createTextNode('0.0000'));
    $tracking_numbers = $order->appendChild($dom->createElement('tracking_numbers'));
    $tracking_numbers->appendChild($dom->createTextNode(''));
    $updated_at = $order->appendChild($dom->createElement('updated_at'));
    $updated_at->appendChild($dom->createTextNode($updated_atValue));
    $updated_at_timestamp = $order->appendChild($dom->createElement('updated_at_timestamp'));
    $updated_at_timestamp->appendChild($dom->createTextNode($updated_at_timestampValue));
    $weight = $order->appendChild($dom->createElement('weight'));
    $weight->appendChild($dom->createTextNode($weightValue));
    $x_forwarded_for = $order->appendChild($dom->createElement('x_forwarded_for'));
    $x_forwarded_for->appendChild($dom->createTextNode(''));

    //Build shipping
    $shipping_address = $order->appendChild($dom->createElement('shipping_address'));
    
    $shippingCity = $shipping_address->appendChild($dom->createElement('city'));
    $shippingCity->appendChild($dom->createTextNode($shippingCityValue));
    $shippingCompany = $shipping_address->appendChild($dom->createElement('company'));
    $shippingCompany->appendChild($dom->createTextNode(''));
    $shippingCountry = $shipping_address->appendChild($dom->createElement('country'));
    $shippingCountry->appendChild($dom->createTextNode($shippingCountryValue));
    $shippingCountry_id = $shipping_address->appendChild($dom->createElement('country_id'));
    $shippingCountry_id->appendChild($dom->createTextNode(''));
    $shippingCountry_iso2 = $shipping_address->appendChild($dom->createElement('country_iso2'));
    $shippingCountry_iso2->appendChild($dom->createTextNode(''));
    $shippingCountry_iso3 = $shipping_address->appendChild($dom->createElement('country_iso3'));
    $shippingCountry_iso3->appendChild($dom->createTextNode(''));
    $shippingEmail = $shipping_address->appendChild($dom->createElement('email'));
    $shippingEmail->appendChild($dom->createTextNode($shippingEmailValue));
    $shippingFax = $shipping_address->appendChild($dom->createElement('fax'));
    $shippingFax->appendChild($dom->createTextNode(''));
    $shippingFirstname = $shipping_address->appendChild($dom->createElement('firstname'));
    $shippingFirstname->appendChild($dom->createTextNode($shippingFirstnameValue));
    $shippingLastname = $shipping_address->appendChild($dom->createElement('lastname'));
    $shippingLastname->appendChild($dom->createTextNode($shippingLastnameValue));
    $shippingMiddlename = $shipping_address->appendChild($dom->createElement('middlename'));
    $shippingMiddlename->appendChild($dom->createTextNode(''));
    $shippingName = $shipping_address->appendChild($dom->createElement('name'));
    $shippingName->appendChild($dom->createTextNode($shippingNameValue));
    $shippingPostcode = $shipping_address->appendChild($dom->createElement('postcode'));
    $shippingPostcode->appendChild($dom->createTextNode($shippingPostcodeValue));
    $shippingPrefix = $shipping_address->appendChild($dom->createElement('prefix'));
    $shippingPrefix->appendChild($dom->createTextNode(''));
    $shippingRegion = $shipping_address->appendChild($dom->createElement('region'));
    $shippingRegion->appendChild($dom->createTextNode($shippingRegionValue));
    $shippingRegion_id = $shipping_address->appendChild($dom->createElement('region_id'));
    $shippingRegion_id->appendChild($dom->createTextNode($shippingRegion_idValue));
    $shippingRegion_iso2 = $shipping_address->appendChild($dom->createElement('region_iso2'));
    $shippingRegion_iso2->appendChild($dom->createTextNode(''));
    $shippingStreet = $shipping_address->appendChild($dom->createElement('street'));
    $shippingStreet->appendChild($dom->createTextNode($shippingStreetValue));
    $shippingSuffix = $shipping_address->appendChild($dom->createElement('suffix'));
    $shippingSuffix->appendChild($dom->createTextNode(''));
    $shippingTelephone = $shipping_address->appendChild($dom->createElement('telephone'));
    $shippingTelephone->appendChild($dom->createTextNode($shippingTelephoneValue));

    // Build billing
    $billing_address = $order->appendChild($dom->createElement('billing_address'));
    
    $billingCity = $billing_address->appendChild($dom->createElement('city'));
    $billingCity->appendChild($dom->createTextNode($billingCityValue));
    $billingCompany = $billing_address->appendChild($dom->createElement('company'));
    $billingCompany->appendChild($dom->createTextNode(''));
    $billingCountry = $billing_address->appendChild($dom->createElement('country'));
    $billingCountry->appendChild($dom->createTextNode($billingCountryValue));
    $billingCountry_id = $billing_address->appendChild($dom->createElement('country_id'));
    $billingCountry_id->appendChild($dom->createTextNode(''));
    $billingCountry_iso2 = $billing_address->appendChild($dom->createElement('country_iso2'));
    $billingCountry_iso2->appendChild($dom->createTextNode(''));
    $billingCountry_iso3 = $billing_address->appendChild($dom->createElement('country_iso3'));
    $billingCountry_iso3->appendChild($dom->createTextNode(''));
    $billingEmail = $billing_address->appendChild($dom->createElement('email'));
    $billingEmail->appendChild($dom->createTextNode($billingEmailValue));
    $billingFax = $billing_address->appendChild($dom->createElement('fax'));
    $billingFax->appendChild($dom->createTextNode(''));
    $billingFirstname = $billing_address->appendChild($dom->createElement('firstname'));
    $billingFirstname->appendChild($dom->createTextNode($billingFirstnameValue));
    $billingLastname = $billing_address->appendChild($dom->createElement('lastname'));
    $billingLastname->appendChild($dom->createTextNode($billingLastnameValue));
    $billingMiddlename = $billing_address->appendChild($dom->createElement('middlename'));
    $billingMiddlename->appendChild($dom->createTextNode(''));
    $billingName = $billing_address->appendChild($dom->createElement('name'));
    $billingName->appendChild($dom->createTextNode($billingNameValue));
    $billingPostcode = $billing_address->appendChild($dom->createElement('postcode'));
    $billingPostcode->appendChild($dom->createTextNode($billingPostcodeValue));
    $billingPrefix = $billing_address->appendChild($dom->createElement('prefix'));
    $billingPrefix->appendChild($dom->createTextNode(''));
    $billingRegion = $billing_address->appendChild($dom->createElement('region'));
    $billingRegion->appendChild($dom->createTextNode($billingRegionValue));
    $billingRegion_id = $billing_address->appendChild($dom->createElement('region_id'));
    $billingRegion_id->appendChild($dom->createTextNode($billingRegion_idValue));
    $billingRegion_iso2 = $billing_address->appendChild($dom->createElement('region_iso2'));
    $billingRegion_iso2->appendChild($dom->createTextNode(''));
    $billingStreet = $billing_address->appendChild($dom->createElement('street'));
    $billingStreet->appendChild($dom->createTextNode($billingStreetValue));
    $billingSuffix = $billing_address->appendChild($dom->createElement('suffix'));
    $billingSuffix->appendChild($dom->createTextNode(''));
    $billingTelephone = $billing_address->appendChild($dom->createElement('telephone'));
    $billingTelephone->appendChild($dom->createTextNode($billingTelephoneValue));
    
    // Build payment

    $payment = $order->appendChild($dom->createElement('payment'));

    $account_status = $payment->appendChild($dom->createElement('account_status'));
    $account_status->appendChild($dom->createTextNode(''));
    $address_status = $payment->appendChild($dom->createElement('address_status'));
    $address_status->appendChild($dom->createTextNode(''));
    $amount = $payment->appendChild($dom->createElement('amount'));
    $amount->appendChild($dom->createTextNode(''));
    $amount_authorized = $payment->appendChild($dom->createElement('amount_authorized'));
    $amount_authorized->appendChild($dom->createTextNode($amount_authorizedValue));
    $amount_canceled = $payment->appendChild($dom->createElement('amount_canceled'));
    $amount_canceled->appendChild($dom->createTextNode(''));
    $amount_ordered = $payment->appendChild($dom->createElement('amount_ordered'));
    $amount_ordered->appendChild($dom->createTextNode($amount_orderedValue));
    $amount_paid = $payment->appendChild($dom->createElement('amount_paid'));
    $amount_paid->appendChild($dom->createTextNode(''));
    $amount_refunded = $payment->appendChild($dom->createElement('amount_refunded'));
    $amount_refunded->appendChild($dom->createTextNode(''));
    $anet_trans_method = $payment->appendChild($dom->createElement('anet_trans_method'));
    $anet_trans_method->appendChild($dom->createTextNode($anet_trans_methodValue));
    $base_amount_authorized = $payment->appendChild($dom->createElement('base_amount_authorized'));
    $base_amount_authorized->appendChild($dom->createTextNode($base_amount_authorizedValue));
    $base_amount_canceled = $payment->appendChild($dom->createElement('base_amount_canceled'));
    $base_amount_canceled->appendChild($dom->createTextNode(''));
    $base_amount_ordered = $payment->appendChild($dom->createElement('base_amount_ordered'));
    $base_amount_ordered->appendChild($dom->createTextNode($base_amount_orderedValue));
    $base_amount_paid = $payment->appendChild($dom->createElement('base_amount_paid'));
    $base_amount_paid->appendChild($dom->createTextNode(''));
    $base_amount_paid_online = $payment->appendChild($dom->createElement('base_amount_paid_online'));
    $base_amount_paid_online->appendChild($dom->createTextNode(''));
    $base_amount_refunded = $payment->appendChild($dom->createElement('base_amount_refunded'));
    $base_amount_refunded->appendChild($dom->createTextNode(''));
    $base_amount_refunded_online = $payment->appendChild($dom->createElement('base_amount_refunded_online'));
    $base_amount_refunded_online->appendChild($dom->createTextNode(''));
    $base_shipping_amount = $payment->appendChild($dom->createElement('base_shipping_amount'));
    $base_shipping_amount->appendChild($dom->createTextNode($base_shipping_amountValue));
    $base_shipping_captured = $payment->appendChild($dom->createElement('base_shipping_captured'));
    $base_shipping_captured->appendChild($dom->createTextNode(''));
    $base_shipping_refunded = $payment->appendChild($dom->createElement('base_shipping_refunded'));
    $base_shipping_refunded->appendChild($dom->createTextNode(''));
    $cc_approval = $payment->appendChild($dom->createElement('cc_approval'));
    $cc_approval->appendChild($dom->createTextNode($cc_approvalValue));
    $cc_avs_status = $payment->appendChild($dom->createElement('cc_avs_status'));
    $cc_avs_status->appendChild($dom->createTextNode($cc_avs_statusValue));
    $cc_cid_status = $payment->appendChild($dom->createElement('cc_cid_status'));
    $cc_cid_status->appendChild($dom->createTextNode($cc_cid_statusValue));
    $cc_debug_request_body = $payment->appendChild($dom->createElement('cc_debug_request_body'));
    $cc_debug_request_body->appendChild($dom->createTextNode(''));
    $cc_debug_response_body = $payment->appendChild($dom->createElement('cc_debug_response_body'));
    $cc_debug_response_body->appendChild($dom->createTextNode(''));
    $cc_debug_response_serialized = $payment->appendChild($dom->createElement('cc_debug_response_serialized'));
    $cc_debug_response_serialized->appendChild($dom->createTextNode(''));
    $cc_exp_month = $payment->appendChild($dom->createElement('cc_exp_month'));
    $cc_exp_month->appendChild($dom->createTextNode($cc_exp_monthValue));
    $cc_exp_year = $payment->appendChild($dom->createElement('cc_exp_year'));
    $cc_exp_year->appendChild($dom->createTextNode($cc_exp_yearValue));
    $cc_last4 = $payment->appendChild($dom->createElement('cc_last4'));
    $cc_last4->appendChild($dom->createTextNode($cc_last4Value));
    $cc_number_enc = $payment->appendChild($dom->createElement('cc_number_enc'));
    $cc_number_enc->appendChild($dom->createTextNode(''));
    $cc_owner = $payment->appendChild($dom->createElement('cc_owner'));
    $cc_owner->appendChild($dom->createTextNode(''));
    $cc_raw_request = $payment->appendChild($dom->createElement('cc_raw_request'));
    $cc_raw_request->appendChild($dom->createTextNode(''));
    $cc_raw_response = $payment->appendChild($dom->createElement('cc_raw_response'));
    $cc_raw_response->appendChild($dom->createTextNode(''));
    $cc_secure_verify = $payment->appendChild($dom->createElement('cc_secure_verify'));
    $cc_secure_verify->appendChild($dom->createTextNode(''));
    $cc_ss_issue = $payment->appendChild($dom->createElement('cc_ss_issue'));
    $cc_ss_issue->appendChild($dom->createTextNode(''));
    $cc_ss_start_month = $payment->appendChild($dom->createElement('cc_ss_start_month'));
    $cc_ss_start_month->appendChild($dom->createTextNode('0'));//appears to be 0 since not used
    $cc_ss_start_year = $payment->appendChild($dom->createElement('cc_ss_start_year'));
    $cc_ss_start_year->appendChild($dom->createTextNode('0'));//appears to be 0 since not used
    $cc_status = $payment->appendChild($dom->createElement('cc_status'));
    $cc_status->appendChild($dom->createTextNode(''));
    $cc_status_description = $payment->appendChild($dom->createElement('cc_status_description'));
    $cc_status_description->appendChild($dom->createTextNode(''));
    $cc_trans_id = $payment->appendChild($dom->createElement('cc_trans_id'));
    $cc_trans_id->appendChild($dom->createTextNode($cc_trans_idValue));
    $cc_type = $payment->appendChild($dom->createElement('cc_type'));
    $cc_type->appendChild($dom->createTextNode($cc_typeValue));
    $cybersource_token = $payment->appendChild($dom->createElement('cybersource_token'));
    $cybersource_token->appendChild($dom->createTextNode(''));
    $echeck_account_name = $payment->appendChild($dom->createElement('echeck_account_name'));
    $echeck_account_name->appendChild($dom->createTextNode(''));
    $echeck_account_type = $payment->appendChild($dom->createElement('echeck_account_type'));
    $echeck_account_type->appendChild($dom->createTextNode(''));
    $echeck_bank_name = $payment->appendChild($dom->createElement('echeck_bank_name'));
    $echeck_bank_name->appendChild($dom->createTextNode(''));
    $echeck_routing_number = $payment->appendChild($dom->createElement('echeck_routing_number'));
    $echeck_routing_number->appendChild($dom->createTextNode(''));
    $echeck_type = $payment->appendChild($dom->createElement('echeck_type'));
    $echeck_type->appendChild($dom->createTextNode(''));
    $flo2cash_account_id = $payment->appendChild($dom->createElement('flo2cash_account_id'));
    $flo2cash_account_id->appendChild($dom->createTextNode(''));
    $ideal_issuer_id = $payment->appendChild($dom->createElement('ideal_issuer_id'));
    $ideal_issuer_id->appendChild($dom->createTextNode(''));
    $ideal_issuer_title = $payment->appendChild($dom->createElement('ideal_issuer_title'));
    $ideal_issuer_title->appendChild($dom->createTextNode(''));
    $ideal_transaction_checked = $payment->appendChild($dom->createElement('ideal_transaction_checked'));
    $ideal_transaction_checked->appendChild($dom->createTextNode(''));
    $last_trans_id = $payment->appendChild($dom->createElement('last_trans_id'));
    $last_trans_id->appendChild($dom->createTextNode($last_trans_idValue));
    $method = $payment->appendChild($dom->createElement('method'));
    $method->appendChild($dom->createTextNode($methodValue));
    $paybox_question_number = $payment->appendChild($dom->createElement('paybox_question_number'));
    $paybox_question_number->appendChild($dom->createTextNode(''));
    $paybox_request_number = $payment->appendChild($dom->createElement('paybox_request_number'));
    $paybox_request_number->appendChild($dom->createTextNode(''));
    $po_number = $payment->appendChild($dom->createElement('po_number'));
    $po_number->appendChild($dom->createTextNode(''));
    $protection_eligibility = $payment->appendChild($dom->createElement('protection_eligibility'));
    $protection_eligibility->appendChild($dom->createTextNode(''));
    $shipping_amount = $payment->appendChild($dom->createElement('shipping_amount'));
    $shipping_amount->appendChild($dom->createTextNode($shipping_amountValue));
    $shipping_captured = $payment->appendChild($dom->createElement('shipping_captured'));
    $shipping_captured->appendChild($dom->createTextNode(''));
    $shipping_refunded = $payment->appendChild($dom->createElement('shipping_refunded'));
    $shipping_refunded->appendChild($dom->createTextNode(''));

    // Build Items
    $items = $order->appendChild($dom->createElement('items'));
    $itemsQuery = "SELECT * FROM `se_orderitem` WHERE `yahooOrderIdNumeric` = " . $result['yahooOrderIdNumeric'];
    $itemsResult = $writeConnection->query($itemsQuery);
    $itemNumber = 1;
    foreach ($itemsResult as $itemResult) {
        $item = $items->appendChild($dom->createElement('item'));

	//Set variables
	$base_original_priceValue = $itemResult['unitPrice'];
	$base_priceValue = $itemResult['unitPrice'];
	$base_row_totalValue = $itemResult['qtyOrdered'] * $itemResult['unitPrice'];
	$real_nameValue = $itemResult['lineItemDescription'];
	$nameValue = $itemResult['lineItemDescription'];
	$original_priceValue = $itemResult['unitPrice'];
	$priceValue = $itemResult['unitPrice'];
	$qty_orderedValue = $itemResult['qtyOrdered'];
	$row_totalValue = $itemResult['qtyOrdered'] * $itemResult['unitPrice'];
	$length = strlen(end(explode('-', $itemResult['productCode'])));
	$real_skuValue = substr($itemResult['productCode'], 0, -($length + 1));
        
	fwrite($transactionLogHandle, "      ->ADDING CONFIGURABLE : " . $itemNumber . " -> " . $real_skuValue . "\n");

	$skuValue = 'Product ' . $itemNumber;
	if (!is_null($result['shipCountry']) && $result['shipState'] == 'CA') {
	    if (strtolower($result['shipCountry']) == 'united states') {
		$tax_percentCalcValue = '0.0875';
		$tax_percentValue = '8.75';
		$base_price_incl_taxValue = round($priceValue + ($priceValue * $tax_percentCalcValue), 4);//
		$base_row_total_incl_taxValue = round($qty_orderedValue * ($priceValue + ($priceValue * $tax_percentCalcValue)), 4);//
		$base_tax_amountValue = round($priceValue * $tax_percentCalcValue, 4);//THIS MAY BE WRONG -- QTY or ONE
		$price_incl_taxValue = round($priceValue + ($priceValue * $tax_percentCalcValue), 4);//
		$row_total_incl_taxValue = round($qty_orderedValue * ($priceValue + ($priceValue * $tax_percentCalcValue)), 4);//
		$tax_amountValue = round($priceValue * $tax_percentCalcValue, 4);//
	    } else {
		$tax_percentValue = '0.00';
		$base_price_incl_taxValue = $priceValue;
		$base_row_total_incl_taxValue = $qty_orderedValue * $priceValue;
		$base_tax_amountValue = '0.00';
		$price_incl_taxValue = $priceValue;
		$row_total_incl_taxValue = $qty_orderedValue * $priceValue;
		$tax_amountValue = '0.00';		
	    }
	} else {
	    $tax_percentValue = '0.00';
	    $base_price_incl_taxValue = $priceValue;
	    $base_row_total_incl_taxValue = $qty_orderedValue * $priceValue;
	    $base_tax_amountValue = '0.00';
	    $price_incl_taxValue = $priceValue;
	    $row_total_incl_taxValue = $qty_orderedValue * $priceValue;
	    $tax_amountValue = '0.00';	
	}

	//Create line item
	$amount_refunded = $item->appendChild($dom->createElement('amount_refunded'));
	$amount_refunded->appendChild($dom->createTextNode('0')); //Always 0
	$applied_rule_ids = $item->appendChild($dom->createElement('applied_rule_ids'));
	$applied_rule_ids->appendChild($dom->createTextNode(''));
	$base_amount_refunded = $item->appendChild($dom->createElement('base_amount_refunded'));
	$base_amount_refunded->appendChild($dom->createTextNode('0')); //Always 0
	$base_cost = $item->appendChild($dom->createElement('base_cost'));
	$base_cost->appendChild($dom->createTextNode(''));
	$base_discount_amount = $item->appendChild($dom->createElement('base_discount_amount'));
	$base_discount_amount->appendChild($dom->createTextNode('0')); //Always 0
	$base_discount_invoiced = $item->appendChild($dom->createElement('base_discount_invoiced'));
	$base_discount_invoiced->appendChild($dom->createTextNode('0')); //Always 0
	$base_hidden_tax_amount = $item->appendChild($dom->createElement('base_hidden_tax_amount'));
	$base_hidden_tax_amount->appendChild($dom->createTextNode(''));
	$base_hidden_tax_invoiced = $item->appendChild($dom->createElement('base_hidden_tax_invoiced'));
	$base_hidden_tax_invoiced->appendChild($dom->createTextNode(''));
	$base_hidden_tax_refunded = $item->appendChild($dom->createElement('base_hidden_tax_refunded'));
	$base_hidden_tax_refunded->appendChild($dom->createTextNode(''));
	$base_original_price = $item->appendChild($dom->createElement('base_original_price'));
	$base_original_price->appendChild($dom->createTextNode($base_original_priceValue));
	$base_price = $item->appendChild($dom->createElement('base_price'));
	$base_price->appendChild($dom->createTextNode($base_priceValue));
	$base_price_incl_tax = $item->appendChild($dom->createElement('base_price_incl_tax'));
	$base_price_incl_tax->appendChild($dom->createTextNode($base_price_incl_taxValue));
	$base_row_invoiced = $item->appendChild($dom->createElement('base_row_invoiced'));
	$base_row_invoiced->appendChild($dom->createTextNode('0'));
	$base_row_total = $item->appendChild($dom->createElement('base_row_total'));
	$base_row_total->appendChild($dom->createTextNode($base_row_totalValue));
	$base_row_total_incl_tax = $item->appendChild($dom->createElement('base_row_total_incl_tax'));
	$base_row_total_incl_tax->appendChild($dom->createTextNode($base_row_total_incl_taxValue));
	$base_tax_amount = $item->appendChild($dom->createElement('base_tax_amount'));
	$base_tax_amount->appendChild($dom->createTextNode($base_tax_amountValue));
	$base_tax_before_discount = $item->appendChild($dom->createElement('base_tax_before_discount'));
	$base_tax_before_discount->appendChild($dom->createTextNode(''));
	$base_tax_invoiced = $item->appendChild($dom->createElement('base_tax_invoiced'));
	$base_tax_invoiced->appendChild($dom->createTextNode('0')); //Always 0
	$base_weee_tax_applied_amount = $item->appendChild($dom->createElement('base_weee_tax_applied_amount'));
	$base_weee_tax_applied_amount->appendChild($dom->createTextNode('0')); //Always 0
	$base_weee_tax_applied_row_amount = $item->appendChild($dom->createElement('base_weee_tax_applied_row_amount'));
	$base_weee_tax_applied_row_amount->appendChild($dom->createTextNode('0')); //Always 0
	$base_weee_tax_disposition = $item->appendChild($dom->createElement('base_weee_tax_disposition'));
	$base_weee_tax_disposition->appendChild($dom->createTextNode('0')); //Always 0
	$base_weee_tax_row_disposition = $item->appendChild($dom->createElement('base_weee_tax_row_disposition'));
	$base_weee_tax_row_disposition->appendChild($dom->createTextNode('0')); //Always 0
	$description = $item->appendChild($dom->createElement('description'));
	$description->appendChild($dom->createTextNode(''));
	$discount_amount = $item->appendChild($dom->createElement('discount_amount'));
	$discount_amount->appendChild($dom->createTextNode('0')); //Always 0
	$discount_invoiced = $item->appendChild($dom->createElement('discount_invoiced'));
	$discount_invoiced->appendChild($dom->createTextNode('0')); //Always 0
	$discount_percent = $item->appendChild($dom->createElement('discount_percent'));
	$discount_percent->appendChild($dom->createTextNode('0')); //Always 0
	$free_shipping = $item->appendChild($dom->createElement('free_shipping'));
	$free_shipping->appendChild($dom->createTextNode('0')); //Always 0
	$hidden_tax_amount = $item->appendChild($dom->createElement('hidden_tax_amount'));
	$hidden_tax_amount->appendChild($dom->createTextNode(''));
	$hidden_tax_canceled = $item->appendChild($dom->createElement('hidden_tax_canceled'));
	$hidden_tax_canceled->appendChild($dom->createTextNode(''));
	$hidden_tax_invoiced = $item->appendChild($dom->createElement('hidden_tax_invoiced'));
	$hidden_tax_invoiced->appendChild($dom->createTextNode(''));
	$hidden_tax_refunded = $item->appendChild($dom->createElement('hidden_tax_refunded'));
	$hidden_tax_refunded->appendChild($dom->createTextNode(''));
	$is_nominal = $item->appendChild($dom->createElement('is_nominal'));
	$is_nominal->appendChild($dom->createTextNode('0')); //Always 0
	$is_qty_decimal = $item->appendChild($dom->createElement('is_qty_decimal'));
	$is_qty_decimal->appendChild($dom->createTextNode('0')); //Always 0
	$is_virtual = $item->appendChild($dom->createElement('is_virtual'));
	$is_virtual->appendChild($dom->createTextNode('0')); //Always 0
	$real_name = $item->appendChild($dom->createElement('real_name'));
	$real_name->appendChild($dom->createTextNode($real_nameValue)); //Always 0
	$name = $item->appendChild($dom->createElement('name'));
	$name->appendChild($dom->createTextNode($nameValue)); //Always 0
	$no_discount = $item->appendChild($dom->createElement('no_discount'));
	$no_discount->appendChild($dom->createTextNode('0')); //Always 0
	$original_price = $item->appendChild($dom->createElement('original_price'));
	$original_price->appendChild($dom->createTextNode($original_priceValue));
	$price = $item->appendChild($dom->createElement('price'));
	$price->appendChild($dom->createTextNode($priceValue));
	$price_incl_tax = $item->appendChild($dom->createElement('price_incl_tax'));
	$price_incl_tax->appendChild($dom->createTextNode($price_incl_taxValue));
	$qty_backordered = $item->appendChild($dom->createElement('qty_backordered'));
	$qty_backordered->appendChild($dom->createTextNode(''));
	$qty_canceled = $item->appendChild($dom->createElement('qty_canceled'));
	$qty_canceled->appendChild($dom->createTextNode('0')); //Always 0
	$qty_invoiced = $item->appendChild($dom->createElement('qty_invoiced'));
	$qty_invoiced->appendChild($dom->createTextNode('0')); //Always 0
	$qty_ordered = $item->appendChild($dom->createElement('qty_ordered'));
	$qty_ordered->appendChild($dom->createTextNode($qty_orderedValue)); //Always 0
	$qty_refunded = $item->appendChild($dom->createElement('qty_refunded'));
	$qty_refunded->appendChild($dom->createTextNode('0')); //Always 0
	$qty_shipped = $item->appendChild($dom->createElement('qty_shipped'));
	$qty_shipped->appendChild($dom->createTextNode('0')); //Always 0
	$row_invoiced = $item->appendChild($dom->createElement('row_invoiced'));
	$row_invoiced->appendChild($dom->createTextNode('0')); //Always 0
	$row_total = $item->appendChild($dom->createElement('row_total'));
	$row_total->appendChild($dom->createTextNode($row_totalValue));
	$row_total_incl_tax = $item->appendChild($dom->createElement('row_total_incl_tax'));
	$row_total_incl_tax->appendChild($dom->createTextNode($row_total_incl_taxValue));
	$row_weight = $item->appendChild($dom->createElement('row_weight'));
	$row_weight->appendChild($dom->createTextNode('0'));
	$real_sku = $item->appendChild($dom->createElement('real_sku'));
	$real_sku->appendChild($dom->createTextNode($real_skuValue));
	$sku = $item->appendChild($dom->createElement('sku'));
	$sku->appendChild($dom->createTextNode($skuValue));
	$tax_amount = $item->appendChild($dom->createElement('tax_amount'));
	$tax_amount->appendChild($dom->createTextNode($tax_amountValue));
	$tax_before_discount = $item->appendChild($dom->createElement('tax_before_discount'));
	$tax_before_discount->appendChild($dom->createTextNode(''));
	$tax_canceled = $item->appendChild($dom->createElement('tax_canceled'));
	$tax_canceled->appendChild($dom->createTextNode(''));
	$tax_invoiced = $item->appendChild($dom->createElement('tax_invoiced'));
	$tax_invoiced->appendChild($dom->createTextNode('0'));
	$tax_percent = $item->appendChild($dom->createElement('tax_percent'));
	$tax_percent->appendChild($dom->createTextNode($tax_percentValue));
	$tax_refunded = $item->appendChild($dom->createElement('tax_refunded'));
	$tax_refunded->appendChild($dom->createTextNode(''));
	$weee_tax_applied = $item->appendChild($dom->createElement('weee_tax_applied'));
	$weee_tax_applied->appendChild($dom->createTextNode('a:0:{}')); //Always 0
	$weee_tax_applied_amount = $item->appendChild($dom->createElement('weee_tax_applied_amount'));
	$weee_tax_applied_amount->appendChild($dom->createTextNode('0')); //Always 0
	$weee_tax_applied_row_amount = $item->appendChild($dom->createElement('weee_tax_applied_row_amount'));
	$weee_tax_applied_row_amount->appendChild($dom->createTextNode('0')); //Always 0
	$weee_tax_disposition = $item->appendChild($dom->createElement('weee_tax_disposition'));
	$weee_tax_disposition->appendChild($dom->createTextNode('0')); //Always 0
	$weee_tax_row_disposition = $item->appendChild($dom->createElement('weee_tax_row_disposition'));
	$weee_tax_row_disposition->appendChild($dom->createTextNode('0')); //Always 0
	$weight = $item->appendChild($dom->createElement('weight'));
	$weight->appendChild($dom->createTextNode('0'));

	//Add simple
	$item = $items->appendChild($dom->createElement('item'));
	
	//Set variables
	$base_original_priceValue = '0.0000';
	$base_priceValue = '0.0000';
	$base_row_totalValue = '0.0000';
	$real_nameValue = $itemResult['lineItemDescription'];
	$nameValue = $itemResult['lineItemDescription'];
	$original_priceValue = '0.0000';
	$priceValue = '0.0000';
	$qty_orderedValue = $itemResult['qtyOrdered'];
	$row_totalValue = '0.0000';
	$real_skuValue = $itemResult['productCode'];
	$skuValue = "Product " . $itemNumber . "-OFFLINE";
	$parent_skuValue = 'Product ' . $itemNumber;//Just for simple
	
	fwrite($transactionLogHandle, "      ->ADDING SIMPLE       : " . $itemNumber . " -> " . $real_skuValue . "\n");

	$tax_percentValue = '0.00';
	$base_price_incl_taxValue = '0.0000';
	$base_row_total_incl_taxValue = '0.0000';
	$base_tax_amountValue = '0.0000';
	$price_incl_taxValue = '0.0000';
	$row_total_incl_taxValue = '0.0000';
	$tax_amountValue = '0.0000';

	//Create line item
	$amount_refunded = $item->appendChild($dom->createElement('amount_refunded'));
	$amount_refunded->appendChild($dom->createTextNode('0')); //Always 0
	$applied_rule_ids = $item->appendChild($dom->createElement('applied_rule_ids'));
	$applied_rule_ids->appendChild($dom->createTextNode(''));
	$base_amount_refunded = $item->appendChild($dom->createElement('base_amount_refunded'));
	$base_amount_refunded->appendChild($dom->createTextNode('0')); //Always 0
	$base_cost = $item->appendChild($dom->createElement('base_cost'));
	$base_cost->appendChild($dom->createTextNode(''));
	$base_discount_amount = $item->appendChild($dom->createElement('base_discount_amount'));
	$base_discount_amount->appendChild($dom->createTextNode('0')); //Always 0
	$base_discount_invoiced = $item->appendChild($dom->createElement('base_discount_invoiced'));
	$base_discount_invoiced->appendChild($dom->createTextNode('0')); //Always 0
	$base_hidden_tax_amount = $item->appendChild($dom->createElement('base_hidden_tax_amount'));
	$base_hidden_tax_amount->appendChild($dom->createTextNode(''));
	$base_hidden_tax_invoiced = $item->appendChild($dom->createElement('base_hidden_tax_invoiced'));
	$base_hidden_tax_invoiced->appendChild($dom->createTextNode(''));
	$base_hidden_tax_refunded = $item->appendChild($dom->createElement('base_hidden_tax_refunded'));
	$base_hidden_tax_refunded->appendChild($dom->createTextNode(''));
	$base_original_price = $item->appendChild($dom->createElement('base_original_price'));
	$base_original_price->appendChild($dom->createTextNode($base_original_priceValue));
	$base_price = $item->appendChild($dom->createElement('base_price'));
	$base_price->appendChild($dom->createTextNode($base_priceValue));
	$base_price_incl_tax = $item->appendChild($dom->createElement('base_price_incl_tax'));
	$base_price_incl_tax->appendChild($dom->createTextNode($base_price_incl_taxValue));
	$base_row_invoiced = $item->appendChild($dom->createElement('base_row_invoiced'));
	$base_row_invoiced->appendChild($dom->createTextNode('0'));
	$base_row_total = $item->appendChild($dom->createElement('base_row_total'));
	$base_row_total->appendChild($dom->createTextNode($base_row_totalValue));
	$base_row_total_incl_tax = $item->appendChild($dom->createElement('base_row_total_incl_tax'));
	$base_row_total_incl_tax->appendChild($dom->createTextNode($base_row_total_incl_taxValue));
	$base_tax_amount = $item->appendChild($dom->createElement('base_tax_amount'));
	$base_tax_amount->appendChild($dom->createTextNode($base_tax_amountValue));
	$base_tax_before_discount = $item->appendChild($dom->createElement('base_tax_before_discount'));
	$base_tax_before_discount->appendChild($dom->createTextNode(''));
	$base_tax_invoiced = $item->appendChild($dom->createElement('base_tax_invoiced'));
	$base_tax_invoiced->appendChild($dom->createTextNode('0')); //Always 0
	$base_weee_tax_applied_amount = $item->appendChild($dom->createElement('base_weee_tax_applied_amount'));
	$base_weee_tax_applied_amount->appendChild($dom->createTextNode('0')); //Always 0
	$base_weee_tax_applied_row_amount = $item->appendChild($dom->createElement('base_weee_tax_applied_row_amount'));
	$base_weee_tax_applied_row_amount->appendChild($dom->createTextNode('0')); //Always 0
	$base_weee_tax_disposition = $item->appendChild($dom->createElement('base_weee_tax_disposition'));
	$base_weee_tax_disposition->appendChild($dom->createTextNode('0')); //Always 0
	$base_weee_tax_row_disposition = $item->appendChild($dom->createElement('base_weee_tax_row_disposition'));
	$base_weee_tax_row_disposition->appendChild($dom->createTextNode('0')); //Always 0
	$description = $item->appendChild($dom->createElement('description'));
	$description->appendChild($dom->createTextNode(''));
	$discount_amount = $item->appendChild($dom->createElement('discount_amount'));
	$discount_amount->appendChild($dom->createTextNode('0')); //Always 0
	$discount_invoiced = $item->appendChild($dom->createElement('discount_invoiced'));
	$discount_invoiced->appendChild($dom->createTextNode('0')); //Always 0
	$discount_percent = $item->appendChild($dom->createElement('discount_percent'));
	$discount_percent->appendChild($dom->createTextNode('0')); //Always 0
	$free_shipping = $item->appendChild($dom->createElement('free_shipping'));
	$free_shipping->appendChild($dom->createTextNode('0')); //Always 0
	$hidden_tax_amount = $item->appendChild($dom->createElement('hidden_tax_amount'));
	$hidden_tax_amount->appendChild($dom->createTextNode(''));
	$hidden_tax_canceled = $item->appendChild($dom->createElement('hidden_tax_canceled'));
	$hidden_tax_canceled->appendChild($dom->createTextNode(''));
	$hidden_tax_invoiced = $item->appendChild($dom->createElement('hidden_tax_invoiced'));
	$hidden_tax_invoiced->appendChild($dom->createTextNode(''));
	$hidden_tax_refunded = $item->appendChild($dom->createElement('hidden_tax_refunded'));
	$hidden_tax_refunded->appendChild($dom->createTextNode(''));
	$is_nominal = $item->appendChild($dom->createElement('is_nominal'));
	$is_nominal->appendChild($dom->createTextNode('0')); //Always 0
	$is_qty_decimal = $item->appendChild($dom->createElement('is_qty_decimal'));
	$is_qty_decimal->appendChild($dom->createTextNode('0')); //Always 0
	$is_virtual = $item->appendChild($dom->createElement('is_virtual'));
	$is_virtual->appendChild($dom->createTextNode('0')); //Always 0
	$real_name = $item->appendChild($dom->createElement('real_name'));
	$real_name->appendChild($dom->createTextNode($real_nameValue)); //Always 0
	$name = $item->appendChild($dom->createElement('nameValue'));
	$name->appendChild($dom->createTextNode($nameValue)); //Always 0
	$no_discount = $item->appendChild($dom->createElement('no_discount'));
	$no_discount->appendChild($dom->createTextNode('0')); //Always 0
	$original_price = $item->appendChild($dom->createElement('original_price'));
	$original_price->appendChild($dom->createTextNode($original_priceValue));
	$parent_sku = $item->appendChild($dom->createElement('parent_sku'));
	$parent_sku->appendChild($dom->createTextNode($parent_skuValue));
	$price = $item->appendChild($dom->createElement('price'));
	$price->appendChild($dom->createTextNode($priceValue));
	$price_incl_tax = $item->appendChild($dom->createElement('price_incl_tax'));
	$price_incl_tax->appendChild($dom->createTextNode($price_incl_taxValue));
	$qty_backordered = $item->appendChild($dom->createElement('qty_backordered'));
	$qty_backordered->appendChild($dom->createTextNode(''));
	$qty_canceled = $item->appendChild($dom->createElement('qty_canceled'));
	$qty_canceled->appendChild($dom->createTextNode('0')); //Always 0
	$qty_invoiced = $item->appendChild($dom->createElement('qty_invoiced'));
	$qty_invoiced->appendChild($dom->createTextNode('0')); //Always 0
	$qty_ordered = $item->appendChild($dom->createElement('qty_ordered'));
	$qty_ordered->appendChild($dom->createTextNode($qty_orderedValue)); //Always 0
	$qty_refunded = $item->appendChild($dom->createElement('qty_refunded'));
	$qty_refunded->appendChild($dom->createTextNode('0')); //Always 0
	$qty_shipped = $item->appendChild($dom->createElement('qty_shipped'));
	$qty_shipped->appendChild($dom->createTextNode('0')); //Always 0
	$row_invoiced = $item->appendChild($dom->createElement('row_invoiced'));
	$row_invoiced->appendChild($dom->createTextNode('0')); //Always 0
	$row_total = $item->appendChild($dom->createElement('row_total'));
	$row_total->appendChild($dom->createTextNode($row_totalValue));
	$row_total_incl_tax = $item->appendChild($dom->createElement('row_total_incl_tax'));
	$row_total_incl_tax->appendChild($dom->createTextNode($row_total_incl_taxValue));
	$row_weight = $item->appendChild($dom->createElement('row_weight'));
	$row_weight->appendChild($dom->createTextNode('0'));
	$real_sku = $item->appendChild($dom->createElement('real_sku'));
	$real_sku->appendChild($dom->createTextNode($real_skuValue));
	$sku = $item->appendChild($dom->createElement('sku'));
	$sku->appendChild($dom->createTextNode($skuValue));
	$tax_amount = $item->appendChild($dom->createElement('tax_amount'));
	$tax_amount->appendChild($dom->createTextNode($tax_amountValue));
	$tax_before_discount = $item->appendChild($dom->createElement('tax_before_discount'));
	$tax_before_discount->appendChild($dom->createTextNode(''));
	$tax_canceled = $item->appendChild($dom->createElement('tax_canceled'));
	$tax_canceled->appendChild($dom->createTextNode(''));
	$tax_invoiced = $item->appendChild($dom->createElement('tax_invoiced'));
	$tax_invoiced->appendChild($dom->createTextNode('0'));
	$tax_percent = $item->appendChild($dom->createElement('tax_percent'));
	$tax_percent->appendChild($dom->createTextNode($tax_percentValue));
	$tax_refunded = $item->appendChild($dom->createElement('tax_refunded'));
	$tax_refunded->appendChild($dom->createTextNode(''));
	$weee_tax_applied = $item->appendChild($dom->createElement('weee_tax_applied'));
	$weee_tax_applied->appendChild($dom->createTextNode('a:0:{}')); //Always 0
	$weee_tax_applied_amount = $item->appendChild($dom->createElement('weee_tax_applied_amount'));
	$weee_tax_applied_amount->appendChild($dom->createTextNode('0')); //Always 0
	$weee_tax_applied_row_amount = $item->appendChild($dom->createElement('weee_tax_applied_row_amount'));
	$weee_tax_applied_row_amount->appendChild($dom->createTextNode('0')); //Always 0
	$weee_tax_disposition = $item->appendChild($dom->createElement('weee_tax_disposition'));
	$weee_tax_disposition->appendChild($dom->createTextNode('0')); //Always 0
	$weee_tax_row_disposition = $item->appendChild($dom->createElement('weee_tax_row_disposition'));
	$weee_tax_row_disposition->appendChild($dom->createTextNode('0')); //Always 0
	$weight = $item->appendChild($dom->createElement('weight'));
	$weight->appendChild($dom->createTextNode('0'));
	
        $itemNumber++;
    }
}

// Make the output pretty
$dom->formatOutput = true;

// Save the XML string
$xml = $dom->saveXML();

//Write file to post directory
$handle = fopen($toolsXmlDirectory . $orderFilename, 'w');
fwrite($handle, $xml);
fclose($handle);

fwrite($transactionLogHandle, "  ->FINISH TIME   : " . $endTime[5] . '/' . $endTime[4] . '/' . $endTime[3] . ' ' . $endTime[2] . ':' . $endTime[1] . ':' . $endTime[0] . "\n");
fwrite($transactionLogHandle, "  ->CREATED       : ORDER FILE 6 " . $orderFilename . "\n");

fwrite($transactionLogHandle, "->END PROCESSING\n");
//Close transaction log
fclose($transactionLogHandle);

//FILE 7
$realTime = realTime();
//Open transaction log
$transactionLogHandle = fopen($toolsLogsDirectory . 'migration_gen_sneakerhead_order_xml_files_transaction_log', 'a+');
fwrite($transactionLogHandle, "->BEGIN PROCESSING\n");
fwrite($transactionLogHandle, "  ->START TIME    : " . $realTime[5] . '/' . $realTime[4] . '/' . $realTime[3] . ' ' . $realTime[2] . ':' . $realTime[1] . ':' . $realTime[0] . "\n");

//ORDERS
fwrite($transactionLogHandle, "  ->GETTING ORDERS\n");

$resource = Mage::getSingleton('core/resource');
$writeConnection = $resource->getConnection('core_write');
//$startDate = '2009-10-00 00:00:00';//>=
$startDate = '2010-03-15 00:00:00';//>=
$endDate = '2010-03-15 00:00:00';//<
//10,000
//$query = "SELECT `users`.`email` AS `user_email`, `users`.`firstName` AS `user_firstname`, `users`.`lastName` AS `user_lastname`, `se_order`.* FROM `se_order` LEFT JOIN `storeorder` ON `se_order`.`yahooOrderIdNumeric` = `storeorder`.`yahooOrderIdNumeric` LEFT JOIN `users` ON `storeorder`.`uid` = `users`.`uid` WHERE `se_order`.`orderCreationDate` > '2009-10-00 00:00:00' AND `se_order`.`orderCreationDate` < '2009-12-14 00:00:00'";
//25,000
//FOLLOWING 8 QUERIES TO BE RUN SEPARATELY TO GENERATE 8 DIFFERENT FILES
//$query = "SELECT `users`.`email` AS `user_email`, `users`.`firstName` AS `user_firstname`, `users`.`lastName` AS `user_lastname`, `se_order`.* FROM `se_order` LEFT JOIN `storeorder` ON `se_order`.`yahooOrderIdNumeric` = `storeorder`.`yahooOrderIdNumeric` LEFT JOIN `users` ON `storeorder`.`uid` = `users`.`uid` WHERE `se_order`.`orderCreationDate` >= '2009-10-00 00:00:00' AND `se_order`.`orderCreationDate` < '2010-03-15 00:00:00'";//191298 -> 216253
//$query = "SELECT `users`.`email` AS `user_email`, `users`.`firstName` AS `user_firstname`, `users`.`lastName` AS `user_lastname`, `se_order`.* FROM `se_order` LEFT JOIN `storeorder` ON `se_order`.`yahooOrderIdNumeric` = `storeorder`.`yahooOrderIdNumeric` LEFT JOIN `users` ON `storeorder`.`uid` = `users`.`uid` WHERE `se_order`.`orderCreationDate` >= '2010-03-15 00:00:00' AND `se_order`.`orderCreationDate` < '2010-10-28 00:00:00'";//216254 -> 241203
//$query = "SELECT `users`.`email` AS `user_email`, `users`.`firstName` AS `user_firstname`, `users`.`lastName` AS `user_lastname`, `se_order`.* FROM `se_order` LEFT JOIN `storeorder` ON `se_order`.`yahooOrderIdNumeric` = `storeorder`.`yahooOrderIdNumeric` LEFT JOIN `users` ON `storeorder`.`uid` = `users`.`uid` WHERE `se_order`.`orderCreationDate` >= '2010-10-28 00:00:00' AND `se_order`.`orderCreationDate` < '2011-02-27 00:00:00'";//241204 -> 266066
//$query = "SELECT `users`.`email` AS `user_email`, `users`.`firstName` AS `user_firstname`, `users`.`lastName` AS `user_lastname`, `se_order`.* FROM `se_order` LEFT JOIN `storeorder` ON `se_order`.`yahooOrderIdNumeric` = `storeorder`.`yahooOrderIdNumeric` LEFT JOIN `users` ON `storeorder`.`uid` = `users`.`uid` WHERE `se_order`.`orderCreationDate` >= '2011-02-27 00:00:00' AND `se_order`.`orderCreationDate` < '2011-06-27 00:00:00'";//266067 -> 291019
//$query = "SELECT `users`.`email` AS `user_email`, `users`.`firstName` AS `user_firstname`, `users`.`lastName` AS `user_lastname`, `se_order`.* FROM `se_order` LEFT JOIN `storeorder` ON `se_order`.`yahooOrderIdNumeric` = `storeorder`.`yahooOrderIdNumeric` LEFT JOIN `users` ON `storeorder`.`uid` = `users`.`uid` WHERE `se_order`.`orderCreationDate` >= '2011-06-27 00:00:00' AND `se_order`.`orderCreationDate` < '2011-12-09 00:00:00'";//291020 -> 315244
//$query = "SELECT `users`.`email` AS `user_email`, `users`.`firstName` AS `user_firstname`, `users`.`lastName` AS `user_lastname`, `se_order`.* FROM `se_order` LEFT JOIN `storeorder` ON `se_order`.`yahooOrderIdNumeric` = `storeorder`.`yahooOrderIdNumeric` LEFT JOIN `users` ON `storeorder`.`uid` = `users`.`uid` WHERE `se_order`.`orderCreationDate` >= '2011-12-09 00:00:00' AND `se_order`.`orderCreationDate` < '2012-04-24 00:00:00'";//315245 -> 340092
$query = "SELECT `users`.`email` AS `user_email`, `users`.`firstName` AS `user_firstname`, `users`.`lastName` AS `user_lastname`, `se_order`.* FROM `se_order` LEFT JOIN `storeorder` ON `se_order`.`yahooOrderIdNumeric` = `storeorder`.`yahooOrderIdNumeric` LEFT JOIN `users` ON `storeorder`.`uid` = `users`.`uid` WHERE `se_order`.`orderCreationDate` >= '2012-04-24 00:00:00' AND `se_order`.`orderCreationDate` < '2012-10-04 00:00:00'";//340093 -> 364330
//$query = "SELECT `users`.`email` AS `user_email`, `users`.`firstName` AS `user_firstname`, `users`.`lastName` AS `user_lastname`, `se_order`.* FROM `se_order` LEFT JOIN `storeorder` ON `se_order`.`yahooOrderIdNumeric` = `storeorder`.`yahooOrderIdNumeric` LEFT JOIN `users` ON `storeorder`.`uid` = `users`.`uid` WHERE `se_order`.`orderCreationDate` >= '2012-10-04 00:00:00' AND `se_order`.`orderCreationDate` < '2013-02-16 00:00:00'";//364331 -> ???
//$query = "SELECT `users`.`email` AS `user_email`, `users`.`firstName` AS `user_firstname`, `users`.`lastName` AS `user_lastname`, `se_order`.* FROM `se_order` LEFT JOIN `storeorder` ON `se_order`.`yahooOrderIdNumeric` = `storeorder`.`yahooOrderIdNumeric` LEFT JOIN `users` ON `storeorder`.`uid` = `users`.`uid` WHERE `se_order`.`orderCreationDate` >= '2013-02-16 00:00:00' AND `se_order`.`orderCreationDate` < '2013-04-23 00:00:00'";//??? -> ???
//$query = "SELECT `users`.`email` AS `user_email`, `users`.`firstName` AS `user_firstname`, `users`.`lastName` AS `user_lastname`, `se_order`.* FROM `se_order` LEFT JOIN `storeorder` ON `se_order`.`yahooOrderIdNumeric` = `storeorder`.`yahooOrderIdNumeric` LEFT JOIN `users` ON `storeorder`.`uid` = `users`.`uid` WHERE `se_order`.`orderCreationDate` >= '2013-04-23 00:00:00'"; //??? -> current (???)

$results = $writeConnection->query($query);
$orderFilename = "order_7_" . $realTime[5] . $realTime[4] . $realTime[3] . '_' . $realTime[2] . $realTime[1] . $realTime[0] . ".xml";
//Creates XML string and XML document from the DOM representation
$dom = new DomDocument('1.0');

$orders = $dom->appendChild($dom->createElement('orders'));
foreach ($results as $result) {
    
    //Add order data
    fwrite($transactionLogHandle, "    ->ADDING ORDER NUMBER   : " . $result['yahooOrderIdNumeric'] . "\n");
    
    // Set some variables
    $base_discount_amountValue = (is_null($result['discount'])) ? '0.0000' : $result['discount'];//appears to be actual total discount
    $base_grand_totalValue = (is_null($result['finalGrandTotal'])) ? '0.0000' : $result['finalGrandTotal'];
    $base_shipping_amountValue = (is_null($result['shippingCost'])) ? '0.0000' : $result['shippingCost'];
    $base_shipping_incl_taxValue = (is_null($result['shippingCost'])) ? '0.0000' : $result['shippingCost'];
    $base_subtotalValue = (is_null($result['finalSubTotal'])) ? '0.0000' : $result['finalSubTotal'];
    $base_subtotal_incl_taxValue = (is_null($result['finalSubTotal'])) ? '0.0000' : $result['finalSubTotal'];
    $base_tax_amountValue = (is_null($result['taxTotal'])) ? '0.0000' : $result['taxTotal'];

    if (!is_null($result['shipCountry']) && $result['shipState'] == 'CA') {
	if (strtolower($result['shipCountry']) == 'united states') {
	    $tax_percentValue = '8.75';
	} else {
	    $tax_percentValue = '0.00';
	}
    } else {
	$tax_percentValue = '0.00';
    }

    $base_total_dueValue = (is_null($result['finalGrandTotal'])) ? '0.0000' : $result['finalGrandTotal'];
    $real_created_atValue = (is_null($result['orderCreationDate'])) ? date("Y-m-d H:i:s") : $result['orderCreationDate'];//current date or order creation date
    $created_at_timestampValue = strtotime($real_created_atValue);//set from created date
    $customer_emailValue = (is_null($result['user_email'])) ? (is_null($result['email'])) ? '' : $result['email'] : $result['user_email'];
    $customer_firstnameValue = (is_null($result['user_firstname'])) ? (is_null($result['firstName'])) ? '' : $result['firstName'] : $result['user_firstname'];
    $customer_lastnameValue = (is_null($result['user_lastname'])) ? (is_null($result['lastName'])) ? '' : $result['lastName'] : $result['user_lastname'];
    if (is_null($result['user_firstname'])) {
	$customer_nameValue = '';
    } else {
	$customer_nameValue = $customer_firstnameValue . ' ' . $customer_lastnameValue;
    }
    $customer_nameValue = $customer_firstnameValue . ' ' . $customer_lastnameValue;
    //Lookup customer
    if ($result['user_email'] == NULL) {
	$customer_group_idValue = 0;
    } else {
	$customerQuery = "SELECT `entity_id` FROM `customer_entity` WHERE `email` = '" . $result['user_email'] . "'";
	$customerResults = $writeConnection->query($customerQuery);
	$customerFound = NULL;
	foreach ($customerResults as $customerResult) {  
	    $customerFound = 1;
	}
	if (!$customerFound) {
	    fwrite($transactionLogHandle, "    ->CUSTOMER NOT FOUND    : " . $result['yahooOrderIdNumeric'] . "\n");	    
	    $customer_group_idValue = 0;
	} else {
	    $customer_group_idValue = 1;
	}
    }
    
    $discount_amountValue = (is_null($result['discount'])) ? '0.0000' : $result['discount'];//appears to be actual total discount
    $grand_totalValue = (is_null($result['finalGrandTotal'])) ? '0.0000' : $result['finalGrandTotal'];
    $increment_idValue = $result['yahooOrderIdNumeric'];//import script adds value to 600000000
    $shipping_amountValue = (is_null($result['shippingCost'])) ? '0.0000' : $result['shippingCost'];
    $shipping_incl_taxValue = (is_null($result['shippingCost'])) ? '0.0000' : $result['shippingCost'];
    switch ($result['shippingMethod']) {
	case 'UPS Ground (3-7 Business Days)':
	    $shipping_methodValue = 'ups_03';
	    break;
	case 'APO & FPO Addresses (5-30 Business Days by USPS)':
	    $shipping_methodValue = 'usps_Priority Mail';
	    break;
	case 'UPS Next Day Air (2-3 Business Days)':
	    $shipping_methodValue = 'ups_01';
	    break;
	case '"Alaska, Hawaii, U.S. Virgin Islands & Puerto Rico':
	    $shipping_methodValue = 'usps_Priority Mail';
	    break;
	case 'UPS 2nd Day Air (3-4 Business Days)':
	    $shipping_methodValue = 'ups_02';
	    break;
	case 'International Express (Shipped with Shoebox)':
	    $shipping_methodValue = 'ups_08';
	    break;
	case 'International Express (Shipped without Shoebox)':
	    $shipping_methodValue = 'ups_08';
	    break;
	case 'USPS Priority Mail (4-5 Business Days)':
	    $shipping_methodValue = 'usps_Priority Mail';
	    break;
	case 'UPS 3 Day Select (4-5 Business Days)':
	    $shipping_methodValue = 'ups_12';
	    break;
	case 'EMS - International':
	    $shipping_methodValue = 'ups_08';
	    break;
	case 'Canada Express (4-7 Business Days)':
	    $shipping_methodValue = 'ups_08';
	    break;
	case 'EMS Canada':
	    $shipping_methodValue = 'ups_08';
	    break;
	case 'Christmas Express (Delivered by Dec. 24th)':
	    $shipping_methodValue = 'ups_03';
	    break;
	case 'COD Ground':
	    $shipping_methodValue = 'ups_03';
	    break;
	case 'COD Overnight':
	    $shipping_methodValue = 'ups_01';
	    break;
	case 'Free Christmas Express (Delivered by Dec. 24th)':
	    $shipping_methodValue = 'ups_03';
	    break;
	case 'New Year Express (Delivered by Dec. 31st)':
	    $shipping_methodValue = 'ups_03';
	    break;
	case 'Free UPS Ground (3-7 Business Days)':
	    $shipping_methodValue = 'ups_03';
	    break;
	case 'COD 2-Day':
	    $shipping_methodValue = 'ups_02';
	    break;
	case 'MSI International Express':
	    $shipping_methodValue = 'ups_08';
	    break;
	case 'Customer Pickup':
	    $shipping_methodValue = 'ups_03';
	    break;
	case 'UPS Ground':
	    $shipping_methodValue = 'ups_03';
	    break;
	case 'UPS 2nd Day Air':
	    $shipping_methodValue = 'ups_02';
	    break;
	case 'APO & FPO Addresses':
	    $shipping_methodValue = 'usps_Priority Mail';
	    break;
	case 'UPS Next Day Air Saver':
	    $shipping_methodValue = 'ups_01';
	    break;
	case 'UPS 3 Day Select':
	    $shipping_methodValue = 'ups_12';
	    break;
	case 'International Express':
	    $shipping_methodValue = 'ups_08';
	    break;
	case 'USPS Priority Mail':
	    $shipping_methodValue = 'usps_Priority Mail';
	    break;
	case 'Canada Express':
	    $shipping_methodValue = 'ups_08';
	    break;
	case 'UPS Next Day Air':
	    $shipping_methodValue = 'ups_01';
	    break;
	case 'Holiday Shipping (Delivered by Dec. 24th)':
	    $shipping_methodValue = 'ups_03';
	    break;
	default://case 'NULL'
	    $shipping_methodValue = '';
	    break;
    }
    
    $stateValue = 'new';//Always new -- will set on order status update
    $statusValue = 'pending';//Always Pending -- will set on order status update
    $subtotalValue = (is_null($result['finalSubTotal'])) ? '0.0000' : $result['finalSubTotal'];
    $subtotal_incl_taxValue = (is_null($result['finalSubTotal'])) ? '0.0000' : $result['finalSubTotal'];
    $tax_amountValue = (is_null($result['taxTotal'])) ? '0.0000' : $result['taxTotal'];
    $total_dueValue = (is_null($result['finalGrandTotal'])) ? '0.0000' : $result['finalGrandTotal'];

    // Get total qty
    $itemsQuery = "SELECT * FROM `se_orderitem` WHERE `yahooOrderIdNumeric` = " . $result['yahooOrderIdNumeric'];
    $itemsResult = $writeConnection->query($itemsQuery);
    $itemCount = 0;
    foreach ($itemsResult as $itemResult) {
	$itemCount += 1;//number of items not quantites
    }
    if ($itemCount == 0) {
	fwrite($transactionLogHandle, "      ->NO ITEMS FOUND      : " . $result['yahooOrderIdNumeric'] . "\n");
    }
    $total_qty_orderedValue = $itemCount . '.0000';//Derived from item qty count
    $updated_atValue = date("Y-m-d H:i:s");
    $updated_at_timestampValue = strtotime($real_created_atValue);
    $weightValue = '0.0000'; //No weight data available

    //Shipping
    $shippingCityValue = (is_null($result['shipCity'])) ? '' : $result['shipCity'];
    $shippingCountryValue = (is_null($result['shipCountry'])) ? '' : $result['shipCountry'];
    $shippingEmailValue = (is_null($result['email'])) ? '' : $result['email'];
    $shippingFirstnameValue = (is_null($result['shipName'])) ? '' : $result['shipName'];
    $shippingLastnameValue = '';
    $shippingNameValue = $result['shipName'];
    $shippingPostcodeValue = (is_null($result['shipZip'])) ? '' : $result['shipZip'];
    if (strtolower($shippingCountryValue) == 'united states') {
	$shippingRegionValue = (is_null($result['shipState'])) ? '' : strtoupper($result['shipState']);
    } else {
	$shippingRegionValue = (is_null($result['shipState'])) ? '' : $result['shipState'];
    }
    $shippingRegion_idValue = '';//Seems to work without conversion
    if ((!is_null($result['shipAddress1']) && $result['shipAddress1'] != '') && (is_null($result['shipAddress2']) || $result['shipAddress2'] == '')) {
	$shippingStreetValue = $result['shipAddress1'];
    } elseif ((!is_null($result['shipAddress1']) && $result['shipAddress1'] != '') && (!is_null($result['shipAddress2']) && $result['shipAddress2'] != '')) {
	$shippingStreetValue = $result['shipAddress2'] . '&#10;' . $result['shipAddress2']; //Include CR/LF
    } elseif ((is_null($result['shipAddress1']) || $result['shipAddress1'] == '') && (!is_null($result['shipAddress2']) && $result['shipAddress2'] != '')) {
	$shippingStreetValue = $result['shipAddress2'];
    } else {
	$shippingStreetValue = '';
    }
    $shippingTelephoneValue = (is_null($result['shipPhone'])) ? '' : $result['shipPhone'];
    
    //Billing
    $billingCityValue = (is_null($result['billCity'])) ? '' : $result['billCity'];
    $billingCountryValue = (is_null($result['billCountry'])) ? '' : $result['billCountry'];
    $billingEmailValue = (is_null($result['email'])) ? '' : $result['email'];
    $billingFirstnameValue = (is_null($result['billName'])) ? '' : $result['billName'];
    $billingLastnameValue = '';
    $billingNameValue = $result['billName'];
    $billingPostcodeValue = (is_null($result['billZip'])) ? '' : $result['billZip'];
    if (strtolower($billingCountryValue) == 'united states') {
	$billingRegionValue = (is_null($result['billState'])) ? '' : strtoupper($result['billState']);
    } else {
	$billingRegionValue = (is_null($result['billState'])) ? '' : $result['billState'];
    }
    $billingRegion_idValue = '';//Seems to work without conversion
    if ((!is_null($result['billAddress1']) && $result['billAddress1'] != '') && (is_null($result['billAddress2']) || $result['billAddress2'] == '')) {
	$billingStreetValue = $result['billAddress1'];
    } elseif ((!is_null($result['billAddress1']) && $result['billAddress1'] != '') && (!is_null($result['billAddress2']) && $result['billAddress2'] != '')) {
	$billingStreetValue = $result['billAddress2'] . '&#10;' . $result['billAddress2']; //Include CR/LF
    } elseif ((is_null($result['billAddress1']) || $result['billAddress1'] == '') && (!is_null($result['billAddress2']) && $result['billAddress2'] != '')) {
	$billingStreetValue = $result['billAddress2'];
    } else {
	$billingStreetValue = '';
    }
    $billingTelephoneValue = (is_null($result['billPhone'])) ? '' : $result['billPhone'];
    
    //Payment
    switch ($result['paymentType']) {
	case 'Visa':
	    $cc_typeValue = 'VI';
            $methodValue = 'authorizenet';
	    break;
	case 'AMEX':
	    $cc_typeValue = 'AE';
            $methodValue = 'authorizenet';
            break;
	case 'Mastercard':
	    $cc_typeValue = 'MC';
            $methodValue = 'authorizenet';
            break;
	case 'Discover':
	    $cc_typeValue = 'DI';
            $methodValue = 'authorizenet';
	    break;
	case 'Paypal':
	    $cc_typeValue = '';
            $methodValue = 'paypal_express';
	    break;
	case 'C.O.D.':
	    $cc_typeValue = '';
            $methodValue = 'free';
	    break;
	case 'GiftCert':
	    //100% payed with giftcard
	    $cc_typeValue = '';
            $methodValue = 'free';
	    break;
	default: //NULL
	    $cc_typeValue = '';
	    $methodValue = 'free';
    }
    $amount_authorizedValue = (is_null($result['finalGrandTotal'])) ? '' : $result['finalGrandTotal'];
    $amount_orderedValue = (is_null($result['finalGrandTotal'])) ? '' : $result['finalGrandTotal'];
    $base_amount_authorizedValue = (is_null($result['finalGrandTotal'])) ? '' : $result['finalGrandTotal'];
    $base_amount_orderedValue = (is_null($result['finalGrandTotal'])) ? '' : $result['finalGrandTotal'];
    $base_shipping_amountValue = (is_null($result['shippingCost'])) ? '' : $result['shippingCost'];
    $cc_approvalValue = (is_null($result['ccApprovalNumber'])) ? '' : $result['ccApprovalNumber'];
    $cc_cid_statusValue = (is_null($result['ccCvvResponse'])) ? '' : $result['ccCvvResponse'];
    $ccExpiration = (is_null($result['ccExpiration'])) ? '' : explode('/', $result['ccExpiration']);
    if (is_null($ccExpiration)) {
        $cc_exp_monthValue = '';
        $cc_exp_yearValue = '';
    } else {
        $cc_exp_monthValue = $ccExpiration[0];
        $cc_exp_yearValue = $ccExpiration[1];
    }
    $cc_last4Value = (is_null($result['ccExpiration'])) ? '' : '****';//data not available
    $anet_trans_methodValue = '';//***
    $cc_avs_statusValue = '';//***
    $cc_trans_idValue = '';//***
    $last_trans_idValue = '';//***
    $shipping_amountValue = (is_null($result['shippingCost'])) ? '' : $result['shippingCost'];

    $order = $orders->appendChild($dom->createElement('order'));

    $adjustment_negative = $order->appendChild($dom->createElement('adjustment_negative'));
    $adjustment_negative->appendChild($dom->createTextNode(''));
    $adjustment_positive = $order->appendChild($dom->createElement('adjustment_positive'));
    $adjustment_positive->appendChild($dom->createTextNode(''));
    $applied_rule_ids = $order->appendChild($dom->createElement('applied_rule_ids'));
    $applied_rule_ids->appendChild($dom->createTextNode(''));//none used -- only used for military until migration complete
    $base_adjustment_negative = $order->appendChild($dom->createElement('base_adjustment_negative'));
    $base_adjustment_negative->appendChild($dom->createTextNode(''));
    $base_adjustment_positive = $order->appendChild($dom->createElement('base_adjustment_positive'));
    $base_adjustment_positive->appendChild($dom->createTextNode(''));
    $base_currency_code = $order->appendChild($dom->createElement('base_currency_code'));
    $base_currency_code->appendChild($dom->createTextNode('USD'));// Always USD
    $base_custbalance_amount = $order->appendChild($dom->createElement('base_custbalance_amount'));
    $base_custbalance_amount->appendChild($dom->createTextNode(''));
    $base_discount_amount = $order->appendChild($dom->createElement('base_discount_amount'));
    $base_discount_amount->appendChild($dom->createTextNode($base_discount_amountValue));
    $base_discount_canceled = $order->appendChild($dom->createElement('base_discount_canceled'));
    $base_discount_canceled->appendChild($dom->createTextNode(''));
    $base_discount_invoiced = $order->appendChild($dom->createElement('base_discount_invoiced'));
    $base_discount_invoiced->appendChild($dom->createTextNode(''));
    $base_discount_refunded = $order->appendChild($dom->createElement('base_discount_refunded'));
    $base_discount_refunded->appendChild($dom->createTextNode(''));
    $base_grand_total = $order->appendChild($dom->createElement('base_grand_total'));
    $base_grand_total->appendChild($dom->createTextNode($base_grand_totalValue));
    $base_hidden_tax_amount = $order->appendChild($dom->createElement('base_hidden_tax_amount'));
    $base_hidden_tax_amount->appendChild($dom->createTextNode(''));
    $base_hidden_tax_invoiced = $order->appendChild($dom->createElement('base_hidden_tax_invoiced'));
    $base_hidden_tax_invoiced->appendChild($dom->createTextNode(''));
    $base_hidden_tax_refunded = $order->appendChild($dom->createElement('base_hidden_tax_refunded'));
    $base_hidden_tax_refunded->appendChild($dom->createTextNode(''));
    $base_shipping_amount = $order->appendChild($dom->createElement('base_shipping_amount'));
    $base_shipping_amount->appendChild($dom->createTextNode($base_shipping_amountValue));
    $base_shipping_canceled = $order->appendChild($dom->createElement('base_shipping_canceled'));
    $base_shipping_canceled->appendChild($dom->createTextNode(''));
    $base_shipping_discount_amount = $order->appendChild($dom->createElement('base_shipping_discount_amount'));
    $base_shipping_discount_amount->appendChild($dom->createTextNode('0.0000'));//Always 0
    $base_shipping_hidden_tax_amount = $order->appendChild($dom->createElement('base_shipping_hidden_tax_amount'));
    $base_shipping_hidden_tax_amount->appendChild($dom->createTextNode(''));
    $base_shipping_incl_tax = $order->appendChild($dom->createElement('base_shipping_incl_tax'));
    $base_shipping_incl_tax->appendChild($dom->createTextNode($base_shipping_incl_taxValue));
    $base_shipping_invoiced = $order->appendChild($dom->createElement('base_shipping_invoiced'));
    $base_shipping_invoiced->appendChild($dom->createTextNode(''));
    $base_shipping_refunded = $order->appendChild($dom->createElement('base_shipping_refunded'));
    $base_shipping_refunded->appendChild($dom->createTextNode(''));
    $base_shipping_tax_amount = $order->appendChild($dom->createElement('base_shipping_tax_amount'));
    $base_shipping_tax_amount->appendChild($dom->createTextNode('0.0000'));//Always 0
    $base_shipping_tax_refunded = $order->appendChild($dom->createElement('base_shipping_tax_refunded'));
    $base_shipping_tax_refunded->appendChild($dom->createTextNode(''));
    $base_subtotal = $order->appendChild($dom->createElement('base_subtotal'));
    $base_subtotal->appendChild($dom->createTextNode($base_subtotalValue));
    $base_subtotal_canceled = $order->appendChild($dom->createElement('base_subtotal_canceled'));
    $base_subtotal_canceled->appendChild($dom->createTextNode(''));
    $base_subtotal_incl_tax = $order->appendChild($dom->createElement('base_subtotal_incl_tax'));
    $base_subtotal_incl_tax->appendChild($dom->createTextNode($base_subtotal_incl_taxValue));
    $base_subtotal_invoiced = $order->appendChild($dom->createElement('base_subtotal_invoiced'));
    $base_subtotal_invoiced->appendChild($dom->createTextNode(''));
    $base_subtotal_refunded = $order->appendChild($dom->createElement('base_subtotal_refunded'));
    $base_subtotal_refunded->appendChild($dom->createTextNode(''));
    $base_tax_amount = $order->appendChild($dom->createElement('base_tax_amount'));
    $base_tax_amount->appendChild($dom->createTextNode($base_tax_amountValue));
    $base_tax_canceled = $order->appendChild($dom->createElement('base_tax_canceled'));
    $base_tax_canceled->appendChild($dom->createTextNode(''));
    $base_tax_invoiced = $order->appendChild($dom->createElement('base_tax_invoiced'));
    $base_tax_invoiced->appendChild($dom->createTextNode(''));
    $base_tax_refunded = $order->appendChild($dom->createElement('base_tax_refunded'));
    $base_tax_refunded->appendChild($dom->createTextNode(''));
    $base_to_global_rate = $order->appendChild($dom->createElement('base_to_global_rate'));
    $base_to_global_rate->appendChild($dom->createTextNode('1'));//Always 1
    $base_to_order_rate = $order->appendChild($dom->createElement('base_to_order_rate'));
    $base_to_order_rate->appendChild($dom->createTextNode('1'));//Always 1
    $base_total_canceled = $order->appendChild($dom->createElement('base_total_canceled'));
    $base_total_canceled->appendChild($dom->createTextNode('0.0000'));
    $base_total_due = $order->appendChild($dom->createElement('base_total_due'));
    $base_total_due->appendChild($dom->createTextNode($base_total_dueValue));
    $base_total_invoiced = $order->appendChild($dom->createElement('base_total_invoiced'));
    $base_total_invoiced->appendChild($dom->createTextNode('0.0000'));
    $base_total_invoiced_cost = $order->appendChild($dom->createElement('base_total_invoiced_cost'));
    $base_total_invoiced_cost->appendChild($dom->createTextNode(''));
    $base_total_offline_refunded = $order->appendChild($dom->createElement('base_total_offline_refunded'));
    $base_total_offline_refunded->appendChild($dom->createTextNode('0.0000'));
    $base_total_online_refunded = $order->appendChild($dom->createElement('base_total_online_refunded'));
    $base_total_online_refunded->appendChild($dom->createTextNode('0.0000'));
    $base_total_paid = $order->appendChild($dom->createElement('base_total_paid'));
    $base_total_paid->appendChild($dom->createTextNode('0.0000'));
    $base_total_qty_ordered = $order->appendChild($dom->createElement('base_total_qty_ordered'));
    $base_total_qty_ordered->appendChild($dom->createTextNode(''));//Always NULL
    $base_total_refunded = $order->appendChild($dom->createElement('base_total_refunded'));
    $base_total_refunded->appendChild($dom->createTextNode('0.0000'));
    $can_ship_partially = $order->appendChild($dom->createElement('can_ship_partially'));
    $can_ship_partially->appendChild($dom->createTextNode(''));
    $can_ship_partially_item = $order->appendChild($dom->createElement('can_ship_partially_item'));
    $can_ship_partially_item->appendChild($dom->createTextNode(''));
    $coupon_code = $order->appendChild($dom->createElement('coupon_code'));
    $coupon_code->appendChild($dom->createTextNode(''));
    $real_created_at = $order->appendChild($dom->createElement('real_created_at'));
    $real_created_at->appendChild($dom->createTextNode($real_created_atValue));
    $created_at_timestamp = $order->appendChild($dom->createElement('created_at_timestamp'));
    $created_at_timestamp->appendChild($dom->createTextNode($created_at_timestampValue));
    $custbalance_amount = $order->appendChild($dom->createElement('custbalance_amount'));
    $custbalance_amount->appendChild($dom->createTextNode(''));
    $customer_dob = $order->appendChild($dom->createElement('customer_dob'));
    $customer_dob->appendChild($dom->createTextNode(''));
    $customer_email = $order->appendChild($dom->createElement('customer_email'));
    $customer_email->appendChild($dom->createTextNode($customer_emailValue));
    $customer_firstname = $order->appendChild($dom->createElement('customer_firstname'));
    $customer_firstname->appendChild($dom->createTextNode($customer_firstnameValue));
    $customer_gender = $order->appendChild($dom->createElement('customer_gender'));
    $customer_gender->appendChild($dom->createTextNode(''));
    $customer_group_id = $order->appendChild($dom->createElement('customer_group_id'));
    $customer_group_id->appendChild($dom->createTextNode($customer_group_idValue));
    $customer_lastname = $order->appendChild($dom->createElement('customer_lastname'));
    $customer_lastname->appendChild($dom->createTextNode($customer_lastnameValue));
    $customer_middlename = $order->appendChild($dom->createElement('customer_middlename'));
    $customer_middlename->appendChild($dom->createTextNode(''));
    $customer_name = $order->appendChild($dom->createElement('customer_name'));
    $customer_name->appendChild($dom->createTextNode($customer_nameValue));
    $customer_note = $order->appendChild($dom->createElement('customer_note'));
    $customer_note->appendChild($dom->createTextNode(''));
    $customer_note_notify = $order->appendChild($dom->createElement('customer_note_notify'));
    $customer_note_notify->appendChild($dom->createTextNode('1'));
    $customer_prefix = $order->appendChild($dom->createElement('customer_prefix'));
    $customer_prefix->appendChild($dom->createTextNode(''));
    $customer_suffix = $order->appendChild($dom->createElement('customer_suffix'));
    $customer_suffix->appendChild($dom->createTextNode(''));
    $customer_taxvat = $order->appendChild($dom->createElement('customer_taxvat'));
    $customer_taxvat->appendChild($dom->createTextNode(''));
    $discount_amount = $order->appendChild($dom->createElement('discount_amount'));
    $discount_amount->appendChild($dom->createTextNode($discount_amountValue));
    $discount_canceled = $order->appendChild($dom->createElement('discount_canceled'));
    $discount_canceled->appendChild($dom->createTextNode(''));
    $discount_invoiced = $order->appendChild($dom->createElement('discount_invoiced'));
    $discount_invoiced->appendChild($dom->createTextNode(''));
    $discount_refunded = $order->appendChild($dom->createElement('discount_refunded'));
    $discount_refunded->appendChild($dom->createTextNode(''));
    $email_sent = $order->appendChild($dom->createElement('email_sent'));
    $email_sent->appendChild($dom->createTextNode('1'));//Always 1
    $ext_customer_id = $order->appendChild($dom->createElement('ext_customer_id'));
    $ext_customer_id->appendChild($dom->createTextNode(''));
    $ext_order_id = $order->appendChild($dom->createElement('ext_order_id'));
    $ext_order_id->appendChild($dom->createTextNode(''));
    $forced_do_shipment_with_invoice = $order->appendChild($dom->createElement('forced_do_shipment_with_invoice'));
    $forced_do_shipment_with_invoice->appendChild($dom->createTextNode(''));
    $global_currency_code = $order->appendChild($dom->createElement('global_currency_code'));
    $global_currency_code->appendChild($dom->createTextNode('USD'));
    $grand_total = $order->appendChild($dom->createElement('grand_total'));
    $grand_total->appendChild($dom->createTextNode($grand_totalValue));
    $hidden_tax_amount = $order->appendChild($dom->createElement('hidden_tax_amount'));
    $hidden_tax_amount->appendChild($dom->createTextNode(''));
    $hidden_tax_invoiced = $order->appendChild($dom->createElement('hidden_tax_invoiced'));
    $hidden_tax_invoiced->appendChild($dom->createTextNode(''));
    $hidden_tax_refunded = $order->appendChild($dom->createElement('hidden_tax_refunded'));
    $hidden_tax_refunded->appendChild($dom->createTextNode(''));
    $hold_before_state = $order->appendChild($dom->createElement('hold_before_state'));
    $hold_before_state->appendChild($dom->createTextNode(''));
    $hold_before_status = $order->appendChild($dom->createElement('hold_before_status'));
    $hold_before_status->appendChild($dom->createTextNode(''));
    $increment_id = $order->appendChild($dom->createElement('increment_id'));
    $increment_id->appendChild($dom->createTextNode($increment_idValue));
    $is_hold = $order->appendChild($dom->createElement('is_hold'));
    $is_hold->appendChild($dom->createTextNode(''));
    $is_multi_payment = $order->appendChild($dom->createElement('is_multi_payment'));
    $is_multi_payment->appendChild($dom->createTextNode(''));
    $is_virtual = $order->appendChild($dom->createElement('is_virtual'));
    $is_virtual->appendChild($dom->createTextNode('0'));//Always 0
    $order_currency_code = $order->appendChild($dom->createElement('order_currency_code'));
    $order_currency_code->appendChild($dom->createTextNode('USD'));
    $payment_authorization_amount = $order->appendChild($dom->createElement('payment_authorization_amount'));
    $payment_authorization_amount->appendChild($dom->createTextNode(''));
    $payment_authorization_expiration = $order->appendChild($dom->createElement('payment_authorization_expiration'));
    $payment_authorization_expiration->appendChild($dom->createTextNode(''));
    $paypal_ipn_customer_notified = $order->appendChild($dom->createElement('paypal_ipn_customer_notified'));
    $paypal_ipn_customer_notified->appendChild($dom->createTextNode(''));
    $real_order_id = $order->appendChild($dom->createElement('real_order_id'));
    $real_order_id->appendChild($dom->createTextNode(''));
    $remote_ip = $order->appendChild($dom->createElement('remote_ip'));
    $remote_ip->appendChild($dom->createTextNode(''));
    $shipping_amount = $order->appendChild($dom->createElement('shipping_amount'));
    $shipping_amount->appendChild($dom->createTextNode($shipping_amountValue));
    $shipping_canceled = $order->appendChild($dom->createElement('shipping_canceled'));
    $shipping_canceled->appendChild($dom->createTextNode(''));
    $shipping_discount_amount = $order->appendChild($dom->createElement('shipping_discount_amount'));
    $shipping_discount_amount->appendChild($dom->createTextNode('0.0000'));
    $shipping_hidden_tax_amount = $order->appendChild($dom->createElement('shipping_hidden_tax_amount'));
    $shipping_hidden_tax_amount->appendChild($dom->createTextNode(''));
    $shipping_incl_tax = $order->appendChild($dom->createElement('shipping_incl_tax'));
    $shipping_incl_tax->appendChild($dom->createTextNode($shipping_incl_taxValue));
    $shipping_invoiced = $order->appendChild($dom->createElement('shipping_invoiced'));
    $shipping_invoiced->appendChild($dom->createTextNode(''));
    $shipping_method = $order->appendChild($dom->createElement('shipping_method'));
    $shipping_method->appendChild($dom->createTextNode($shipping_methodValue));
    $shipping_refunded = $order->appendChild($dom->createElement('shipping_refunded'));
    $shipping_refunded->appendChild($dom->createTextNode(''));
    $shipping_tax_amount = $order->appendChild($dom->createElement('shipping_tax_amount'));
    $shipping_tax_amount->appendChild($dom->createTextNode('0.0000'));
    $shipping_tax_refunded = $order->appendChild($dom->createElement('shipping_tax_refunded'));
    $shipping_tax_refunded->appendChild($dom->createTextNode(''));
    $state = $order->appendChild($dom->createElement('state'));
    $state->appendChild($dom->createTextNode($stateValue));
    $status = $order->appendChild($dom->createElement('status'));
    $status->appendChild($dom->createTextNode($statusValue));
    $store = $order->appendChild($dom->createElement('store'));
    $store->appendChild($dom->createTextNode('sneakerhead_cn'));
    $subtotal = $order->appendChild($dom->createElement('subTotal'));
    $subtotal->appendChild($dom->createTextNode($subtotalValue));
    $subtotal_canceled = $order->appendChild($dom->createElement('subtotal_canceled'));
    $subtotal_canceled->appendChild($dom->createTextNode(''));
    $subtotal_incl_tax = $order->appendChild($dom->createElement('subtotal_incl_tax'));
    $subtotal_incl_tax->appendChild($dom->createTextNode($subtotal_incl_taxValue));
    $subtotal_invoiced = $order->appendChild($dom->createElement('subtotal_invoiced'));
    $subtotal_invoiced->appendChild($dom->createTextNode(''));
    $subtotal_refunded = $order->appendChild($dom->createElement('subtotal_refunded'));
    $subtotal_refunded->appendChild($dom->createTextNode(''));
    $tax_amount = $order->appendChild($dom->createElement('tax_amount'));
    $tax_amount->appendChild($dom->createTextNode($tax_amountValue));
    $tax_canceled = $order->appendChild($dom->createElement('tax_canceled'));
    $tax_canceled->appendChild($dom->createTextNode(''));
    $tax_invoiced = $order->appendChild($dom->createElement('tax_invoiced'));
    $tax_invoiced->appendChild($dom->createTextNode(''));
    $tax_percent = $order->appendChild($dom->createElement('tax_percent'));
    $tax_percent->appendChild($dom->createTextNode($tax_percentValue));
    $tax_refunded = $order->appendChild($dom->createElement('tax_refunded'));
    $tax_refunded->appendChild($dom->createTextNode(''));
    $total_canceled = $order->appendChild($dom->createElement('total_canceled'));
    $total_canceled->appendChild($dom->createTextNode('0.0000'));
    $total_due = $order->appendChild($dom->createElement('total_due'));
    $total_due->appendChild($dom->createTextNode($total_dueValue));
    $total_invoiced = $order->appendChild($dom->createElement('total_invoiced'));
    $total_invoiced->appendChild($dom->createTextNode('0.0000'));
    $total_item_count = $order->appendChild($dom->createElement('total_item_count'));
    $total_item_count->appendChild($dom->createTextNode(''));
    $total_offline_refunded = $order->appendChild($dom->createElement('total_offline_refunded'));
    $total_offline_refunded->appendChild($dom->createTextNode('0.0000'));
    $total_online_refunded = $order->appendChild($dom->createElement('total_online_refunded'));
    $total_online_refunded->appendChild($dom->createTextNode('0.0000'));
    $total_paid = $order->appendChild($dom->createElement('total_paid'));
    $total_paid->appendChild($dom->createTextNode('0.0000'));
    $total_qty_ordered = $order->appendChild($dom->createElement('total_qty_ordered'));
    $total_qty_ordered->appendChild($dom->createTextNode($total_qty_orderedValue));
    $total_refunded = $order->appendChild($dom->createElement('total_refunded'));
    $total_refunded->appendChild($dom->createTextNode('0.0000'));
    $tracking_numbers = $order->appendChild($dom->createElement('tracking_numbers'));
    $tracking_numbers->appendChild($dom->createTextNode(''));
    $updated_at = $order->appendChild($dom->createElement('updated_at'));
    $updated_at->appendChild($dom->createTextNode($updated_atValue));
    $updated_at_timestamp = $order->appendChild($dom->createElement('updated_at_timestamp'));
    $updated_at_timestamp->appendChild($dom->createTextNode($updated_at_timestampValue));
    $weight = $order->appendChild($dom->createElement('weight'));
    $weight->appendChild($dom->createTextNode($weightValue));
    $x_forwarded_for = $order->appendChild($dom->createElement('x_forwarded_for'));
    $x_forwarded_for->appendChild($dom->createTextNode(''));

    //Build shipping
    $shipping_address = $order->appendChild($dom->createElement('shipping_address'));
    
    $shippingCity = $shipping_address->appendChild($dom->createElement('city'));
    $shippingCity->appendChild($dom->createTextNode($shippingCityValue));
    $shippingCompany = $shipping_address->appendChild($dom->createElement('company'));
    $shippingCompany->appendChild($dom->createTextNode(''));
    $shippingCountry = $shipping_address->appendChild($dom->createElement('country'));
    $shippingCountry->appendChild($dom->createTextNode($shippingCountryValue));
    $shippingCountry_id = $shipping_address->appendChild($dom->createElement('country_id'));
    $shippingCountry_id->appendChild($dom->createTextNode(''));
    $shippingCountry_iso2 = $shipping_address->appendChild($dom->createElement('country_iso2'));
    $shippingCountry_iso2->appendChild($dom->createTextNode(''));
    $shippingCountry_iso3 = $shipping_address->appendChild($dom->createElement('country_iso3'));
    $shippingCountry_iso3->appendChild($dom->createTextNode(''));
    $shippingEmail = $shipping_address->appendChild($dom->createElement('email'));
    $shippingEmail->appendChild($dom->createTextNode($shippingEmailValue));
    $shippingFax = $shipping_address->appendChild($dom->createElement('fax'));
    $shippingFax->appendChild($dom->createTextNode(''));
    $shippingFirstname = $shipping_address->appendChild($dom->createElement('firstname'));
    $shippingFirstname->appendChild($dom->createTextNode($shippingFirstnameValue));
    $shippingLastname = $shipping_address->appendChild($dom->createElement('lastname'));
    $shippingLastname->appendChild($dom->createTextNode($shippingLastnameValue));
    $shippingMiddlename = $shipping_address->appendChild($dom->createElement('middlename'));
    $shippingMiddlename->appendChild($dom->createTextNode(''));
    $shippingName = $shipping_address->appendChild($dom->createElement('name'));
    $shippingName->appendChild($dom->createTextNode($shippingNameValue));
    $shippingPostcode = $shipping_address->appendChild($dom->createElement('postcode'));
    $shippingPostcode->appendChild($dom->createTextNode($shippingPostcodeValue));
    $shippingPrefix = $shipping_address->appendChild($dom->createElement('prefix'));
    $shippingPrefix->appendChild($dom->createTextNode(''));
    $shippingRegion = $shipping_address->appendChild($dom->createElement('region'));
    $shippingRegion->appendChild($dom->createTextNode($shippingRegionValue));
    $shippingRegion_id = $shipping_address->appendChild($dom->createElement('region_id'));
    $shippingRegion_id->appendChild($dom->createTextNode($shippingRegion_idValue));
    $shippingRegion_iso2 = $shipping_address->appendChild($dom->createElement('region_iso2'));
    $shippingRegion_iso2->appendChild($dom->createTextNode(''));
    $shippingStreet = $shipping_address->appendChild($dom->createElement('street'));
    $shippingStreet->appendChild($dom->createTextNode($shippingStreetValue));
    $shippingSuffix = $shipping_address->appendChild($dom->createElement('suffix'));
    $shippingSuffix->appendChild($dom->createTextNode(''));
    $shippingTelephone = $shipping_address->appendChild($dom->createElement('telephone'));
    $shippingTelephone->appendChild($dom->createTextNode($shippingTelephoneValue));

    // Build billing
    $billing_address = $order->appendChild($dom->createElement('billing_address'));
    
    $billingCity = $billing_address->appendChild($dom->createElement('city'));
    $billingCity->appendChild($dom->createTextNode($billingCityValue));
    $billingCompany = $billing_address->appendChild($dom->createElement('company'));
    $billingCompany->appendChild($dom->createTextNode(''));
    $billingCountry = $billing_address->appendChild($dom->createElement('country'));
    $billingCountry->appendChild($dom->createTextNode($billingCountryValue));
    $billingCountry_id = $billing_address->appendChild($dom->createElement('country_id'));
    $billingCountry_id->appendChild($dom->createTextNode(''));
    $billingCountry_iso2 = $billing_address->appendChild($dom->createElement('country_iso2'));
    $billingCountry_iso2->appendChild($dom->createTextNode(''));
    $billingCountry_iso3 = $billing_address->appendChild($dom->createElement('country_iso3'));
    $billingCountry_iso3->appendChild($dom->createTextNode(''));
    $billingEmail = $billing_address->appendChild($dom->createElement('email'));
    $billingEmail->appendChild($dom->createTextNode($billingEmailValue));
    $billingFax = $billing_address->appendChild($dom->createElement('fax'));
    $billingFax->appendChild($dom->createTextNode(''));
    $billingFirstname = $billing_address->appendChild($dom->createElement('firstname'));
    $billingFirstname->appendChild($dom->createTextNode($billingFirstnameValue));
    $billingLastname = $billing_address->appendChild($dom->createElement('lastname'));
    $billingLastname->appendChild($dom->createTextNode($billingLastnameValue));
    $billingMiddlename = $billing_address->appendChild($dom->createElement('middlename'));
    $billingMiddlename->appendChild($dom->createTextNode(''));
    $billingName = $billing_address->appendChild($dom->createElement('name'));
    $billingName->appendChild($dom->createTextNode($billingNameValue));
    $billingPostcode = $billing_address->appendChild($dom->createElement('postcode'));
    $billingPostcode->appendChild($dom->createTextNode($billingPostcodeValue));
    $billingPrefix = $billing_address->appendChild($dom->createElement('prefix'));
    $billingPrefix->appendChild($dom->createTextNode(''));
    $billingRegion = $billing_address->appendChild($dom->createElement('region'));
    $billingRegion->appendChild($dom->createTextNode($billingRegionValue));
    $billingRegion_id = $billing_address->appendChild($dom->createElement('region_id'));
    $billingRegion_id->appendChild($dom->createTextNode($billingRegion_idValue));
    $billingRegion_iso2 = $billing_address->appendChild($dom->createElement('region_iso2'));
    $billingRegion_iso2->appendChild($dom->createTextNode(''));
    $billingStreet = $billing_address->appendChild($dom->createElement('street'));
    $billingStreet->appendChild($dom->createTextNode($billingStreetValue));
    $billingSuffix = $billing_address->appendChild($dom->createElement('suffix'));
    $billingSuffix->appendChild($dom->createTextNode(''));
    $billingTelephone = $billing_address->appendChild($dom->createElement('telephone'));
    $billingTelephone->appendChild($dom->createTextNode($billingTelephoneValue));
    
    // Build payment

    $payment = $order->appendChild($dom->createElement('payment'));

    $account_status = $payment->appendChild($dom->createElement('account_status'));
    $account_status->appendChild($dom->createTextNode(''));
    $address_status = $payment->appendChild($dom->createElement('address_status'));
    $address_status->appendChild($dom->createTextNode(''));
    $amount = $payment->appendChild($dom->createElement('amount'));
    $amount->appendChild($dom->createTextNode(''));
    $amount_authorized = $payment->appendChild($dom->createElement('amount_authorized'));
    $amount_authorized->appendChild($dom->createTextNode($amount_authorizedValue));
    $amount_canceled = $payment->appendChild($dom->createElement('amount_canceled'));
    $amount_canceled->appendChild($dom->createTextNode(''));
    $amount_ordered = $payment->appendChild($dom->createElement('amount_ordered'));
    $amount_ordered->appendChild($dom->createTextNode($amount_orderedValue));
    $amount_paid = $payment->appendChild($dom->createElement('amount_paid'));
    $amount_paid->appendChild($dom->createTextNode(''));
    $amount_refunded = $payment->appendChild($dom->createElement('amount_refunded'));
    $amount_refunded->appendChild($dom->createTextNode(''));
    $anet_trans_method = $payment->appendChild($dom->createElement('anet_trans_method'));
    $anet_trans_method->appendChild($dom->createTextNode($anet_trans_methodValue));
    $base_amount_authorized = $payment->appendChild($dom->createElement('base_amount_authorized'));
    $base_amount_authorized->appendChild($dom->createTextNode($base_amount_authorizedValue));
    $base_amount_canceled = $payment->appendChild($dom->createElement('base_amount_canceled'));
    $base_amount_canceled->appendChild($dom->createTextNode(''));
    $base_amount_ordered = $payment->appendChild($dom->createElement('base_amount_ordered'));
    $base_amount_ordered->appendChild($dom->createTextNode($base_amount_orderedValue));
    $base_amount_paid = $payment->appendChild($dom->createElement('base_amount_paid'));
    $base_amount_paid->appendChild($dom->createTextNode(''));
    $base_amount_paid_online = $payment->appendChild($dom->createElement('base_amount_paid_online'));
    $base_amount_paid_online->appendChild($dom->createTextNode(''));
    $base_amount_refunded = $payment->appendChild($dom->createElement('base_amount_refunded'));
    $base_amount_refunded->appendChild($dom->createTextNode(''));
    $base_amount_refunded_online = $payment->appendChild($dom->createElement('base_amount_refunded_online'));
    $base_amount_refunded_online->appendChild($dom->createTextNode(''));
    $base_shipping_amount = $payment->appendChild($dom->createElement('base_shipping_amount'));
    $base_shipping_amount->appendChild($dom->createTextNode($base_shipping_amountValue));
    $base_shipping_captured = $payment->appendChild($dom->createElement('base_shipping_captured'));
    $base_shipping_captured->appendChild($dom->createTextNode(''));
    $base_shipping_refunded = $payment->appendChild($dom->createElement('base_shipping_refunded'));
    $base_shipping_refunded->appendChild($dom->createTextNode(''));
    $cc_approval = $payment->appendChild($dom->createElement('cc_approval'));
    $cc_approval->appendChild($dom->createTextNode($cc_approvalValue));
    $cc_avs_status = $payment->appendChild($dom->createElement('cc_avs_status'));
    $cc_avs_status->appendChild($dom->createTextNode($cc_avs_statusValue));
    $cc_cid_status = $payment->appendChild($dom->createElement('cc_cid_status'));
    $cc_cid_status->appendChild($dom->createTextNode($cc_cid_statusValue));
    $cc_debug_request_body = $payment->appendChild($dom->createElement('cc_debug_request_body'));
    $cc_debug_request_body->appendChild($dom->createTextNode(''));
    $cc_debug_response_body = $payment->appendChild($dom->createElement('cc_debug_response_body'));
    $cc_debug_response_body->appendChild($dom->createTextNode(''));
    $cc_debug_response_serialized = $payment->appendChild($dom->createElement('cc_debug_response_serialized'));
    $cc_debug_response_serialized->appendChild($dom->createTextNode(''));
    $cc_exp_month = $payment->appendChild($dom->createElement('cc_exp_month'));
    $cc_exp_month->appendChild($dom->createTextNode($cc_exp_monthValue));
    $cc_exp_year = $payment->appendChild($dom->createElement('cc_exp_year'));
    $cc_exp_year->appendChild($dom->createTextNode($cc_exp_yearValue));
    $cc_last4 = $payment->appendChild($dom->createElement('cc_last4'));
    $cc_last4->appendChild($dom->createTextNode($cc_last4Value));
    $cc_number_enc = $payment->appendChild($dom->createElement('cc_number_enc'));
    $cc_number_enc->appendChild($dom->createTextNode(''));
    $cc_owner = $payment->appendChild($dom->createElement('cc_owner'));
    $cc_owner->appendChild($dom->createTextNode(''));
    $cc_raw_request = $payment->appendChild($dom->createElement('cc_raw_request'));
    $cc_raw_request->appendChild($dom->createTextNode(''));
    $cc_raw_response = $payment->appendChild($dom->createElement('cc_raw_response'));
    $cc_raw_response->appendChild($dom->createTextNode(''));
    $cc_secure_verify = $payment->appendChild($dom->createElement('cc_secure_verify'));
    $cc_secure_verify->appendChild($dom->createTextNode(''));
    $cc_ss_issue = $payment->appendChild($dom->createElement('cc_ss_issue'));
    $cc_ss_issue->appendChild($dom->createTextNode(''));
    $cc_ss_start_month = $payment->appendChild($dom->createElement('cc_ss_start_month'));
    $cc_ss_start_month->appendChild($dom->createTextNode('0'));//appears to be 0 since not used
    $cc_ss_start_year = $payment->appendChild($dom->createElement('cc_ss_start_year'));
    $cc_ss_start_year->appendChild($dom->createTextNode('0'));//appears to be 0 since not used
    $cc_status = $payment->appendChild($dom->createElement('cc_status'));
    $cc_status->appendChild($dom->createTextNode(''));
    $cc_status_description = $payment->appendChild($dom->createElement('cc_status_description'));
    $cc_status_description->appendChild($dom->createTextNode(''));
    $cc_trans_id = $payment->appendChild($dom->createElement('cc_trans_id'));
    $cc_trans_id->appendChild($dom->createTextNode($cc_trans_idValue));
    $cc_type = $payment->appendChild($dom->createElement('cc_type'));
    $cc_type->appendChild($dom->createTextNode($cc_typeValue));
    $cybersource_token = $payment->appendChild($dom->createElement('cybersource_token'));
    $cybersource_token->appendChild($dom->createTextNode(''));
    $echeck_account_name = $payment->appendChild($dom->createElement('echeck_account_name'));
    $echeck_account_name->appendChild($dom->createTextNode(''));
    $echeck_account_type = $payment->appendChild($dom->createElement('echeck_account_type'));
    $echeck_account_type->appendChild($dom->createTextNode(''));
    $echeck_bank_name = $payment->appendChild($dom->createElement('echeck_bank_name'));
    $echeck_bank_name->appendChild($dom->createTextNode(''));
    $echeck_routing_number = $payment->appendChild($dom->createElement('echeck_routing_number'));
    $echeck_routing_number->appendChild($dom->createTextNode(''));
    $echeck_type = $payment->appendChild($dom->createElement('echeck_type'));
    $echeck_type->appendChild($dom->createTextNode(''));
    $flo2cash_account_id = $payment->appendChild($dom->createElement('flo2cash_account_id'));
    $flo2cash_account_id->appendChild($dom->createTextNode(''));
    $ideal_issuer_id = $payment->appendChild($dom->createElement('ideal_issuer_id'));
    $ideal_issuer_id->appendChild($dom->createTextNode(''));
    $ideal_issuer_title = $payment->appendChild($dom->createElement('ideal_issuer_title'));
    $ideal_issuer_title->appendChild($dom->createTextNode(''));
    $ideal_transaction_checked = $payment->appendChild($dom->createElement('ideal_transaction_checked'));
    $ideal_transaction_checked->appendChild($dom->createTextNode(''));
    $last_trans_id = $payment->appendChild($dom->createElement('last_trans_id'));
    $last_trans_id->appendChild($dom->createTextNode($last_trans_idValue));
    $method = $payment->appendChild($dom->createElement('method'));
    $method->appendChild($dom->createTextNode($methodValue));
    $paybox_question_number = $payment->appendChild($dom->createElement('paybox_question_number'));
    $paybox_question_number->appendChild($dom->createTextNode(''));
    $paybox_request_number = $payment->appendChild($dom->createElement('paybox_request_number'));
    $paybox_request_number->appendChild($dom->createTextNode(''));
    $po_number = $payment->appendChild($dom->createElement('po_number'));
    $po_number->appendChild($dom->createTextNode(''));
    $protection_eligibility = $payment->appendChild($dom->createElement('protection_eligibility'));
    $protection_eligibility->appendChild($dom->createTextNode(''));
    $shipping_amount = $payment->appendChild($dom->createElement('shipping_amount'));
    $shipping_amount->appendChild($dom->createTextNode($shipping_amountValue));
    $shipping_captured = $payment->appendChild($dom->createElement('shipping_captured'));
    $shipping_captured->appendChild($dom->createTextNode(''));
    $shipping_refunded = $payment->appendChild($dom->createElement('shipping_refunded'));
    $shipping_refunded->appendChild($dom->createTextNode(''));

    // Build Items
    $items = $order->appendChild($dom->createElement('items'));
    $itemsQuery = "SELECT * FROM `se_orderitem` WHERE `yahooOrderIdNumeric` = " . $result['yahooOrderIdNumeric'];
    $itemsResult = $writeConnection->query($itemsQuery);
    $itemNumber = 1;
    foreach ($itemsResult as $itemResult) {
        $item = $items->appendChild($dom->createElement('item'));

	//Set variables
	$base_original_priceValue = $itemResult['unitPrice'];
	$base_priceValue = $itemResult['unitPrice'];
	$base_row_totalValue = $itemResult['qtyOrdered'] * $itemResult['unitPrice'];
	$real_nameValue = $itemResult['lineItemDescription'];
	$nameValue = $itemResult['lineItemDescription'];
	$original_priceValue = $itemResult['unitPrice'];
	$priceValue = $itemResult['unitPrice'];
	$qty_orderedValue = $itemResult['qtyOrdered'];
	$row_totalValue = $itemResult['qtyOrdered'] * $itemResult['unitPrice'];
	$length = strlen(end(explode('-', $itemResult['productCode'])));
	$real_skuValue = substr($itemResult['productCode'], 0, -($length + 1));
        
	fwrite($transactionLogHandle, "      ->ADDING CONFIGURABLE : " . $itemNumber . " -> " . $real_skuValue . "\n");

	$skuValue = 'Product ' . $itemNumber;
	if (!is_null($result['shipCountry']) && $result['shipState'] == 'CA') {
	    if (strtolower($result['shipCountry']) == 'united states') {
		$tax_percentCalcValue = '0.0875';
		$tax_percentValue = '8.75';
		$base_price_incl_taxValue = round($priceValue + ($priceValue * $tax_percentCalcValue), 4);//
		$base_row_total_incl_taxValue = round($qty_orderedValue * ($priceValue + ($priceValue * $tax_percentCalcValue)), 4);//
		$base_tax_amountValue = round($priceValue * $tax_percentCalcValue, 4);//THIS MAY BE WRONG -- QTY or ONE
		$price_incl_taxValue = round($priceValue + ($priceValue * $tax_percentCalcValue), 4);//
		$row_total_incl_taxValue = round($qty_orderedValue * ($priceValue + ($priceValue * $tax_percentCalcValue)), 4);//
		$tax_amountValue = round($priceValue * $tax_percentCalcValue, 4);//
	    } else {
		$tax_percentValue = '0.00';
		$base_price_incl_taxValue = $priceValue;
		$base_row_total_incl_taxValue = $qty_orderedValue * $priceValue;
		$base_tax_amountValue = '0.00';
		$price_incl_taxValue = $priceValue;
		$row_total_incl_taxValue = $qty_orderedValue * $priceValue;
		$tax_amountValue = '0.00';		
	    }
	} else {
	    $tax_percentValue = '0.00';
	    $base_price_incl_taxValue = $priceValue;
	    $base_row_total_incl_taxValue = $qty_orderedValue * $priceValue;
	    $base_tax_amountValue = '0.00';
	    $price_incl_taxValue = $priceValue;
	    $row_total_incl_taxValue = $qty_orderedValue * $priceValue;
	    $tax_amountValue = '0.00';	
	}

	//Create line item
	$amount_refunded = $item->appendChild($dom->createElement('amount_refunded'));
	$amount_refunded->appendChild($dom->createTextNode('0')); //Always 0
	$applied_rule_ids = $item->appendChild($dom->createElement('applied_rule_ids'));
	$applied_rule_ids->appendChild($dom->createTextNode(''));
	$base_amount_refunded = $item->appendChild($dom->createElement('base_amount_refunded'));
	$base_amount_refunded->appendChild($dom->createTextNode('0')); //Always 0
	$base_cost = $item->appendChild($dom->createElement('base_cost'));
	$base_cost->appendChild($dom->createTextNode(''));
	$base_discount_amount = $item->appendChild($dom->createElement('base_discount_amount'));
	$base_discount_amount->appendChild($dom->createTextNode('0')); //Always 0
	$base_discount_invoiced = $item->appendChild($dom->createElement('base_discount_invoiced'));
	$base_discount_invoiced->appendChild($dom->createTextNode('0')); //Always 0
	$base_hidden_tax_amount = $item->appendChild($dom->createElement('base_hidden_tax_amount'));
	$base_hidden_tax_amount->appendChild($dom->createTextNode(''));
	$base_hidden_tax_invoiced = $item->appendChild($dom->createElement('base_hidden_tax_invoiced'));
	$base_hidden_tax_invoiced->appendChild($dom->createTextNode(''));
	$base_hidden_tax_refunded = $item->appendChild($dom->createElement('base_hidden_tax_refunded'));
	$base_hidden_tax_refunded->appendChild($dom->createTextNode(''));
	$base_original_price = $item->appendChild($dom->createElement('base_original_price'));
	$base_original_price->appendChild($dom->createTextNode($base_original_priceValue));
	$base_price = $item->appendChild($dom->createElement('base_price'));
	$base_price->appendChild($dom->createTextNode($base_priceValue));
	$base_price_incl_tax = $item->appendChild($dom->createElement('base_price_incl_tax'));
	$base_price_incl_tax->appendChild($dom->createTextNode($base_price_incl_taxValue));
	$base_row_invoiced = $item->appendChild($dom->createElement('base_row_invoiced'));
	$base_row_invoiced->appendChild($dom->createTextNode('0'));
	$base_row_total = $item->appendChild($dom->createElement('base_row_total'));
	$base_row_total->appendChild($dom->createTextNode($base_row_totalValue));
	$base_row_total_incl_tax = $item->appendChild($dom->createElement('base_row_total_incl_tax'));
	$base_row_total_incl_tax->appendChild($dom->createTextNode($base_row_total_incl_taxValue));
	$base_tax_amount = $item->appendChild($dom->createElement('base_tax_amount'));
	$base_tax_amount->appendChild($dom->createTextNode($base_tax_amountValue));
	$base_tax_before_discount = $item->appendChild($dom->createElement('base_tax_before_discount'));
	$base_tax_before_discount->appendChild($dom->createTextNode(''));
	$base_tax_invoiced = $item->appendChild($dom->createElement('base_tax_invoiced'));
	$base_tax_invoiced->appendChild($dom->createTextNode('0')); //Always 0
	$base_weee_tax_applied_amount = $item->appendChild($dom->createElement('base_weee_tax_applied_amount'));
	$base_weee_tax_applied_amount->appendChild($dom->createTextNode('0')); //Always 0
	$base_weee_tax_applied_row_amount = $item->appendChild($dom->createElement('base_weee_tax_applied_row_amount'));
	$base_weee_tax_applied_row_amount->appendChild($dom->createTextNode('0')); //Always 0
	$base_weee_tax_disposition = $item->appendChild($dom->createElement('base_weee_tax_disposition'));
	$base_weee_tax_disposition->appendChild($dom->createTextNode('0')); //Always 0
	$base_weee_tax_row_disposition = $item->appendChild($dom->createElement('base_weee_tax_row_disposition'));
	$base_weee_tax_row_disposition->appendChild($dom->createTextNode('0')); //Always 0
	$description = $item->appendChild($dom->createElement('description'));
	$description->appendChild($dom->createTextNode(''));
	$discount_amount = $item->appendChild($dom->createElement('discount_amount'));
	$discount_amount->appendChild($dom->createTextNode('0')); //Always 0
	$discount_invoiced = $item->appendChild($dom->createElement('discount_invoiced'));
	$discount_invoiced->appendChild($dom->createTextNode('0')); //Always 0
	$discount_percent = $item->appendChild($dom->createElement('discount_percent'));
	$discount_percent->appendChild($dom->createTextNode('0')); //Always 0
	$free_shipping = $item->appendChild($dom->createElement('free_shipping'));
	$free_shipping->appendChild($dom->createTextNode('0')); //Always 0
	$hidden_tax_amount = $item->appendChild($dom->createElement('hidden_tax_amount'));
	$hidden_tax_amount->appendChild($dom->createTextNode(''));
	$hidden_tax_canceled = $item->appendChild($dom->createElement('hidden_tax_canceled'));
	$hidden_tax_canceled->appendChild($dom->createTextNode(''));
	$hidden_tax_invoiced = $item->appendChild($dom->createElement('hidden_tax_invoiced'));
	$hidden_tax_invoiced->appendChild($dom->createTextNode(''));
	$hidden_tax_refunded = $item->appendChild($dom->createElement('hidden_tax_refunded'));
	$hidden_tax_refunded->appendChild($dom->createTextNode(''));
	$is_nominal = $item->appendChild($dom->createElement('is_nominal'));
	$is_nominal->appendChild($dom->createTextNode('0')); //Always 0
	$is_qty_decimal = $item->appendChild($dom->createElement('is_qty_decimal'));
	$is_qty_decimal->appendChild($dom->createTextNode('0')); //Always 0
	$is_virtual = $item->appendChild($dom->createElement('is_virtual'));
	$is_virtual->appendChild($dom->createTextNode('0')); //Always 0
	$real_name = $item->appendChild($dom->createElement('real_name'));
	$real_name->appendChild($dom->createTextNode($real_nameValue)); //Always 0
	$name = $item->appendChild($dom->createElement('name'));
	$name->appendChild($dom->createTextNode($nameValue)); //Always 0
	$no_discount = $item->appendChild($dom->createElement('no_discount'));
	$no_discount->appendChild($dom->createTextNode('0')); //Always 0
	$original_price = $item->appendChild($dom->createElement('original_price'));
	$original_price->appendChild($dom->createTextNode($original_priceValue));
	$price = $item->appendChild($dom->createElement('price'));
	$price->appendChild($dom->createTextNode($priceValue));
	$price_incl_tax = $item->appendChild($dom->createElement('price_incl_tax'));
	$price_incl_tax->appendChild($dom->createTextNode($price_incl_taxValue));
	$qty_backordered = $item->appendChild($dom->createElement('qty_backordered'));
	$qty_backordered->appendChild($dom->createTextNode(''));
	$qty_canceled = $item->appendChild($dom->createElement('qty_canceled'));
	$qty_canceled->appendChild($dom->createTextNode('0')); //Always 0
	$qty_invoiced = $item->appendChild($dom->createElement('qty_invoiced'));
	$qty_invoiced->appendChild($dom->createTextNode('0')); //Always 0
	$qty_ordered = $item->appendChild($dom->createElement('qty_ordered'));
	$qty_ordered->appendChild($dom->createTextNode($qty_orderedValue)); //Always 0
	$qty_refunded = $item->appendChild($dom->createElement('qty_refunded'));
	$qty_refunded->appendChild($dom->createTextNode('0')); //Always 0
	$qty_shipped = $item->appendChild($dom->createElement('qty_shipped'));
	$qty_shipped->appendChild($dom->createTextNode('0')); //Always 0
	$row_invoiced = $item->appendChild($dom->createElement('row_invoiced'));
	$row_invoiced->appendChild($dom->createTextNode('0')); //Always 0
	$row_total = $item->appendChild($dom->createElement('row_total'));
	$row_total->appendChild($dom->createTextNode($row_totalValue));
	$row_total_incl_tax = $item->appendChild($dom->createElement('row_total_incl_tax'));
	$row_total_incl_tax->appendChild($dom->createTextNode($row_total_incl_taxValue));
	$row_weight = $item->appendChild($dom->createElement('row_weight'));
	$row_weight->appendChild($dom->createTextNode('0'));
	$real_sku = $item->appendChild($dom->createElement('real_sku'));
	$real_sku->appendChild($dom->createTextNode($real_skuValue));
	$sku = $item->appendChild($dom->createElement('sku'));
	$sku->appendChild($dom->createTextNode($skuValue));
	$tax_amount = $item->appendChild($dom->createElement('tax_amount'));
	$tax_amount->appendChild($dom->createTextNode($tax_amountValue));
	$tax_before_discount = $item->appendChild($dom->createElement('tax_before_discount'));
	$tax_before_discount->appendChild($dom->createTextNode(''));
	$tax_canceled = $item->appendChild($dom->createElement('tax_canceled'));
	$tax_canceled->appendChild($dom->createTextNode(''));
	$tax_invoiced = $item->appendChild($dom->createElement('tax_invoiced'));
	$tax_invoiced->appendChild($dom->createTextNode('0'));
	$tax_percent = $item->appendChild($dom->createElement('tax_percent'));
	$tax_percent->appendChild($dom->createTextNode($tax_percentValue));
	$tax_refunded = $item->appendChild($dom->createElement('tax_refunded'));
	$tax_refunded->appendChild($dom->createTextNode(''));
	$weee_tax_applied = $item->appendChild($dom->createElement('weee_tax_applied'));
	$weee_tax_applied->appendChild($dom->createTextNode('a:0:{}')); //Always 0
	$weee_tax_applied_amount = $item->appendChild($dom->createElement('weee_tax_applied_amount'));
	$weee_tax_applied_amount->appendChild($dom->createTextNode('0')); //Always 0
	$weee_tax_applied_row_amount = $item->appendChild($dom->createElement('weee_tax_applied_row_amount'));
	$weee_tax_applied_row_amount->appendChild($dom->createTextNode('0')); //Always 0
	$weee_tax_disposition = $item->appendChild($dom->createElement('weee_tax_disposition'));
	$weee_tax_disposition->appendChild($dom->createTextNode('0')); //Always 0
	$weee_tax_row_disposition = $item->appendChild($dom->createElement('weee_tax_row_disposition'));
	$weee_tax_row_disposition->appendChild($dom->createTextNode('0')); //Always 0
	$weight = $item->appendChild($dom->createElement('weight'));
	$weight->appendChild($dom->createTextNode('0'));

	//Add simple
	$item = $items->appendChild($dom->createElement('item'));
	
	//Set variables
	$base_original_priceValue = '0.0000';
	$base_priceValue = '0.0000';
	$base_row_totalValue = '0.0000';
	$real_nameValue = $itemResult['lineItemDescription'];
	$nameValue = $itemResult['lineItemDescription'];
	$original_priceValue = '0.0000';
	$priceValue = '0.0000';
	$qty_orderedValue = $itemResult['qtyOrdered'];
	$row_totalValue = '0.0000';
	$real_skuValue = $itemResult['productCode'];
	$skuValue = "Product " . $itemNumber . "-OFFLINE";
	$parent_skuValue = 'Product ' . $itemNumber;//Just for simple
	
	fwrite($transactionLogHandle, "      ->ADDING SIMPLE       : " . $itemNumber . " -> " . $real_skuValue . "\n");

	$tax_percentValue = '0.00';
	$base_price_incl_taxValue = '0.0000';
	$base_row_total_incl_taxValue = '0.0000';
	$base_tax_amountValue = '0.0000';
	$price_incl_taxValue = '0.0000';
	$row_total_incl_taxValue = '0.0000';
	$tax_amountValue = '0.0000';

	//Create line item
	$amount_refunded = $item->appendChild($dom->createElement('amount_refunded'));
	$amount_refunded->appendChild($dom->createTextNode('0')); //Always 0
	$applied_rule_ids = $item->appendChild($dom->createElement('applied_rule_ids'));
	$applied_rule_ids->appendChild($dom->createTextNode(''));
	$base_amount_refunded = $item->appendChild($dom->createElement('base_amount_refunded'));
	$base_amount_refunded->appendChild($dom->createTextNode('0')); //Always 0
	$base_cost = $item->appendChild($dom->createElement('base_cost'));
	$base_cost->appendChild($dom->createTextNode(''));
	$base_discount_amount = $item->appendChild($dom->createElement('base_discount_amount'));
	$base_discount_amount->appendChild($dom->createTextNode('0')); //Always 0
	$base_discount_invoiced = $item->appendChild($dom->createElement('base_discount_invoiced'));
	$base_discount_invoiced->appendChild($dom->createTextNode('0')); //Always 0
	$base_hidden_tax_amount = $item->appendChild($dom->createElement('base_hidden_tax_amount'));
	$base_hidden_tax_amount->appendChild($dom->createTextNode(''));
	$base_hidden_tax_invoiced = $item->appendChild($dom->createElement('base_hidden_tax_invoiced'));
	$base_hidden_tax_invoiced->appendChild($dom->createTextNode(''));
	$base_hidden_tax_refunded = $item->appendChild($dom->createElement('base_hidden_tax_refunded'));
	$base_hidden_tax_refunded->appendChild($dom->createTextNode(''));
	$base_original_price = $item->appendChild($dom->createElement('base_original_price'));
	$base_original_price->appendChild($dom->createTextNode($base_original_priceValue));
	$base_price = $item->appendChild($dom->createElement('base_price'));
	$base_price->appendChild($dom->createTextNode($base_priceValue));
	$base_price_incl_tax = $item->appendChild($dom->createElement('base_price_incl_tax'));
	$base_price_incl_tax->appendChild($dom->createTextNode($base_price_incl_taxValue));
	$base_row_invoiced = $item->appendChild($dom->createElement('base_row_invoiced'));
	$base_row_invoiced->appendChild($dom->createTextNode('0'));
	$base_row_total = $item->appendChild($dom->createElement('base_row_total'));
	$base_row_total->appendChild($dom->createTextNode($base_row_totalValue));
	$base_row_total_incl_tax = $item->appendChild($dom->createElement('base_row_total_incl_tax'));
	$base_row_total_incl_tax->appendChild($dom->createTextNode($base_row_total_incl_taxValue));
	$base_tax_amount = $item->appendChild($dom->createElement('base_tax_amount'));
	$base_tax_amount->appendChild($dom->createTextNode($base_tax_amountValue));
	$base_tax_before_discount = $item->appendChild($dom->createElement('base_tax_before_discount'));
	$base_tax_before_discount->appendChild($dom->createTextNode(''));
	$base_tax_invoiced = $item->appendChild($dom->createElement('base_tax_invoiced'));
	$base_tax_invoiced->appendChild($dom->createTextNode('0')); //Always 0
	$base_weee_tax_applied_amount = $item->appendChild($dom->createElement('base_weee_tax_applied_amount'));
	$base_weee_tax_applied_amount->appendChild($dom->createTextNode('0')); //Always 0
	$base_weee_tax_applied_row_amount = $item->appendChild($dom->createElement('base_weee_tax_applied_row_amount'));
	$base_weee_tax_applied_row_amount->appendChild($dom->createTextNode('0')); //Always 0
	$base_weee_tax_disposition = $item->appendChild($dom->createElement('base_weee_tax_disposition'));
	$base_weee_tax_disposition->appendChild($dom->createTextNode('0')); //Always 0
	$base_weee_tax_row_disposition = $item->appendChild($dom->createElement('base_weee_tax_row_disposition'));
	$base_weee_tax_row_disposition->appendChild($dom->createTextNode('0')); //Always 0
	$description = $item->appendChild($dom->createElement('description'));
	$description->appendChild($dom->createTextNode(''));
	$discount_amount = $item->appendChild($dom->createElement('discount_amount'));
	$discount_amount->appendChild($dom->createTextNode('0')); //Always 0
	$discount_invoiced = $item->appendChild($dom->createElement('discount_invoiced'));
	$discount_invoiced->appendChild($dom->createTextNode('0')); //Always 0
	$discount_percent = $item->appendChild($dom->createElement('discount_percent'));
	$discount_percent->appendChild($dom->createTextNode('0')); //Always 0
	$free_shipping = $item->appendChild($dom->createElement('free_shipping'));
	$free_shipping->appendChild($dom->createTextNode('0')); //Always 0
	$hidden_tax_amount = $item->appendChild($dom->createElement('hidden_tax_amount'));
	$hidden_tax_amount->appendChild($dom->createTextNode(''));
	$hidden_tax_canceled = $item->appendChild($dom->createElement('hidden_tax_canceled'));
	$hidden_tax_canceled->appendChild($dom->createTextNode(''));
	$hidden_tax_invoiced = $item->appendChild($dom->createElement('hidden_tax_invoiced'));
	$hidden_tax_invoiced->appendChild($dom->createTextNode(''));
	$hidden_tax_refunded = $item->appendChild($dom->createElement('hidden_tax_refunded'));
	$hidden_tax_refunded->appendChild($dom->createTextNode(''));
	$is_nominal = $item->appendChild($dom->createElement('is_nominal'));
	$is_nominal->appendChild($dom->createTextNode('0')); //Always 0
	$is_qty_decimal = $item->appendChild($dom->createElement('is_qty_decimal'));
	$is_qty_decimal->appendChild($dom->createTextNode('0')); //Always 0
	$is_virtual = $item->appendChild($dom->createElement('is_virtual'));
	$is_virtual->appendChild($dom->createTextNode('0')); //Always 0
	$real_name = $item->appendChild($dom->createElement('real_name'));
	$real_name->appendChild($dom->createTextNode($real_nameValue)); //Always 0
	$name = $item->appendChild($dom->createElement('nameValue'));
	$name->appendChild($dom->createTextNode($nameValue)); //Always 0
	$no_discount = $item->appendChild($dom->createElement('no_discount'));
	$no_discount->appendChild($dom->createTextNode('0')); //Always 0
	$original_price = $item->appendChild($dom->createElement('original_price'));
	$original_price->appendChild($dom->createTextNode($original_priceValue));
	$parent_sku = $item->appendChild($dom->createElement('parent_sku'));
	$parent_sku->appendChild($dom->createTextNode($parent_skuValue));
	$price = $item->appendChild($dom->createElement('price'));
	$price->appendChild($dom->createTextNode($priceValue));
	$price_incl_tax = $item->appendChild($dom->createElement('price_incl_tax'));
	$price_incl_tax->appendChild($dom->createTextNode($price_incl_taxValue));
	$qty_backordered = $item->appendChild($dom->createElement('qty_backordered'));
	$qty_backordered->appendChild($dom->createTextNode(''));
	$qty_canceled = $item->appendChild($dom->createElement('qty_canceled'));
	$qty_canceled->appendChild($dom->createTextNode('0')); //Always 0
	$qty_invoiced = $item->appendChild($dom->createElement('qty_invoiced'));
	$qty_invoiced->appendChild($dom->createTextNode('0')); //Always 0
	$qty_ordered = $item->appendChild($dom->createElement('qty_ordered'));
	$qty_ordered->appendChild($dom->createTextNode($qty_orderedValue)); //Always 0
	$qty_refunded = $item->appendChild($dom->createElement('qty_refunded'));
	$qty_refunded->appendChild($dom->createTextNode('0')); //Always 0
	$qty_shipped = $item->appendChild($dom->createElement('qty_shipped'));
	$qty_shipped->appendChild($dom->createTextNode('0')); //Always 0
	$row_invoiced = $item->appendChild($dom->createElement('row_invoiced'));
	$row_invoiced->appendChild($dom->createTextNode('0')); //Always 0
	$row_total = $item->appendChild($dom->createElement('row_total'));
	$row_total->appendChild($dom->createTextNode($row_totalValue));
	$row_total_incl_tax = $item->appendChild($dom->createElement('row_total_incl_tax'));
	$row_total_incl_tax->appendChild($dom->createTextNode($row_total_incl_taxValue));
	$row_weight = $item->appendChild($dom->createElement('row_weight'));
	$row_weight->appendChild($dom->createTextNode('0'));
	$real_sku = $item->appendChild($dom->createElement('real_sku'));
	$real_sku->appendChild($dom->createTextNode($real_skuValue));
	$sku = $item->appendChild($dom->createElement('sku'));
	$sku->appendChild($dom->createTextNode($skuValue));
	$tax_amount = $item->appendChild($dom->createElement('tax_amount'));
	$tax_amount->appendChild($dom->createTextNode($tax_amountValue));
	$tax_before_discount = $item->appendChild($dom->createElement('tax_before_discount'));
	$tax_before_discount->appendChild($dom->createTextNode(''));
	$tax_canceled = $item->appendChild($dom->createElement('tax_canceled'));
	$tax_canceled->appendChild($dom->createTextNode(''));
	$tax_invoiced = $item->appendChild($dom->createElement('tax_invoiced'));
	$tax_invoiced->appendChild($dom->createTextNode('0'));
	$tax_percent = $item->appendChild($dom->createElement('tax_percent'));
	$tax_percent->appendChild($dom->createTextNode($tax_percentValue));
	$tax_refunded = $item->appendChild($dom->createElement('tax_refunded'));
	$tax_refunded->appendChild($dom->createTextNode(''));
	$weee_tax_applied = $item->appendChild($dom->createElement('weee_tax_applied'));
	$weee_tax_applied->appendChild($dom->createTextNode('a:0:{}')); //Always 0
	$weee_tax_applied_amount = $item->appendChild($dom->createElement('weee_tax_applied_amount'));
	$weee_tax_applied_amount->appendChild($dom->createTextNode('0')); //Always 0
	$weee_tax_applied_row_amount = $item->appendChild($dom->createElement('weee_tax_applied_row_amount'));
	$weee_tax_applied_row_amount->appendChild($dom->createTextNode('0')); //Always 0
	$weee_tax_disposition = $item->appendChild($dom->createElement('weee_tax_disposition'));
	$weee_tax_disposition->appendChild($dom->createTextNode('0')); //Always 0
	$weee_tax_row_disposition = $item->appendChild($dom->createElement('weee_tax_row_disposition'));
	$weee_tax_row_disposition->appendChild($dom->createTextNode('0')); //Always 0
	$weight = $item->appendChild($dom->createElement('weight'));
	$weight->appendChild($dom->createTextNode('0'));
	
        $itemNumber++;
    }
}

// Make the output pretty
$dom->formatOutput = true;

// Save the XML string
$xml = $dom->saveXML();

//Write file to post directory
$handle = fopen($toolsXmlDirectory . $orderFilename, 'w');
fwrite($handle, $xml);
fclose($handle);

fwrite($transactionLogHandle, "  ->FINISH TIME   : " . $endTime[5] . '/' . $endTime[4] . '/' . $endTime[3] . ' ' . $endTime[2] . ':' . $endTime[1] . ':' . $endTime[0] . "\n");
fwrite($transactionLogHandle, "  ->CREATED       : ORDER FILE 7 " . $orderFilename . "\n");

fwrite($transactionLogHandle, "->END PROCESSING\n");
//Close transaction log
fclose($transactionLogHandle);

//FILE 8
$realTime = realTime();
//Open transaction log
$transactionLogHandle = fopen($toolsLogsDirectory . 'migration_gen_sneakerhead_order_xml_files_transaction_log', 'a+');
fwrite($transactionLogHandle, "->BEGIN PROCESSING\n");
fwrite($transactionLogHandle, "  ->START TIME    : " . $realTime[5] . '/' . $realTime[4] . '/' . $realTime[3] . ' ' . $realTime[2] . ':' . $realTime[1] . ':' . $realTime[0] . "\n");

//ORDERS
fwrite($transactionLogHandle, "  ->GETTING ORDERS\n");

$resource = Mage::getSingleton('core/resource');
$writeConnection = $resource->getConnection('core_write');
//$startDate = '2009-10-00 00:00:00';//>=
$startDate = '2010-03-15 00:00:00';//>=
$endDate = '2010-03-15 00:00:00';//<
//10,000
//$query = "SELECT `users`.`email` AS `user_email`, `users`.`firstName` AS `user_firstname`, `users`.`lastName` AS `user_lastname`, `se_order`.* FROM `se_order` LEFT JOIN `storeorder` ON `se_order`.`yahooOrderIdNumeric` = `storeorder`.`yahooOrderIdNumeric` LEFT JOIN `users` ON `storeorder`.`uid` = `users`.`uid` WHERE `se_order`.`orderCreationDate` > '2009-10-00 00:00:00' AND `se_order`.`orderCreationDate` < '2009-12-14 00:00:00'";
//25,000
//FOLLOWING 8 QUERIES TO BE RUN SEPARATELY TO GENERATE 8 DIFFERENT FILES
//$query = "SELECT `users`.`email` AS `user_email`, `users`.`firstName` AS `user_firstname`, `users`.`lastName` AS `user_lastname`, `se_order`.* FROM `se_order` LEFT JOIN `storeorder` ON `se_order`.`yahooOrderIdNumeric` = `storeorder`.`yahooOrderIdNumeric` LEFT JOIN `users` ON `storeorder`.`uid` = `users`.`uid` WHERE `se_order`.`orderCreationDate` >= '2009-10-00 00:00:00' AND `se_order`.`orderCreationDate` < '2010-03-15 00:00:00'";//191298 -> 216253
//$query = "SELECT `users`.`email` AS `user_email`, `users`.`firstName` AS `user_firstname`, `users`.`lastName` AS `user_lastname`, `se_order`.* FROM `se_order` LEFT JOIN `storeorder` ON `se_order`.`yahooOrderIdNumeric` = `storeorder`.`yahooOrderIdNumeric` LEFT JOIN `users` ON `storeorder`.`uid` = `users`.`uid` WHERE `se_order`.`orderCreationDate` >= '2010-03-15 00:00:00' AND `se_order`.`orderCreationDate` < '2010-10-28 00:00:00'";//216254 -> 241203
//$query = "SELECT `users`.`email` AS `user_email`, `users`.`firstName` AS `user_firstname`, `users`.`lastName` AS `user_lastname`, `se_order`.* FROM `se_order` LEFT JOIN `storeorder` ON `se_order`.`yahooOrderIdNumeric` = `storeorder`.`yahooOrderIdNumeric` LEFT JOIN `users` ON `storeorder`.`uid` = `users`.`uid` WHERE `se_order`.`orderCreationDate` >= '2010-10-28 00:00:00' AND `se_order`.`orderCreationDate` < '2011-02-27 00:00:00'";//241204 -> 266066
//$query = "SELECT `users`.`email` AS `user_email`, `users`.`firstName` AS `user_firstname`, `users`.`lastName` AS `user_lastname`, `se_order`.* FROM `se_order` LEFT JOIN `storeorder` ON `se_order`.`yahooOrderIdNumeric` = `storeorder`.`yahooOrderIdNumeric` LEFT JOIN `users` ON `storeorder`.`uid` = `users`.`uid` WHERE `se_order`.`orderCreationDate` >= '2011-02-27 00:00:00' AND `se_order`.`orderCreationDate` < '2011-06-27 00:00:00'";//266067 -> 291019
//$query = "SELECT `users`.`email` AS `user_email`, `users`.`firstName` AS `user_firstname`, `users`.`lastName` AS `user_lastname`, `se_order`.* FROM `se_order` LEFT JOIN `storeorder` ON `se_order`.`yahooOrderIdNumeric` = `storeorder`.`yahooOrderIdNumeric` LEFT JOIN `users` ON `storeorder`.`uid` = `users`.`uid` WHERE `se_order`.`orderCreationDate` >= '2011-06-27 00:00:00' AND `se_order`.`orderCreationDate` < '2011-12-09 00:00:00'";//291020 -> 315244
//$query = "SELECT `users`.`email` AS `user_email`, `users`.`firstName` AS `user_firstname`, `users`.`lastName` AS `user_lastname`, `se_order`.* FROM `se_order` LEFT JOIN `storeorder` ON `se_order`.`yahooOrderIdNumeric` = `storeorder`.`yahooOrderIdNumeric` LEFT JOIN `users` ON `storeorder`.`uid` = `users`.`uid` WHERE `se_order`.`orderCreationDate` >= '2011-12-09 00:00:00' AND `se_order`.`orderCreationDate` < '2012-04-24 00:00:00'";//315245 -> 340092
//$query = "SELECT `users`.`email` AS `user_email`, `users`.`firstName` AS `user_firstname`, `users`.`lastName` AS `user_lastname`, `se_order`.* FROM `se_order` LEFT JOIN `storeorder` ON `se_order`.`yahooOrderIdNumeric` = `storeorder`.`yahooOrderIdNumeric` LEFT JOIN `users` ON `storeorder`.`uid` = `users`.`uid` WHERE `se_order`.`orderCreationDate` >= '2012-04-24 00:00:00' AND `se_order`.`orderCreationDate` < '2012-10-04 00:00:00'";//340093 -> 364330
$query = "SELECT `users`.`email` AS `user_email`, `users`.`firstName` AS `user_firstname`, `users`.`lastName` AS `user_lastname`, `se_order`.* FROM `se_order` LEFT JOIN `storeorder` ON `se_order`.`yahooOrderIdNumeric` = `storeorder`.`yahooOrderIdNumeric` LEFT JOIN `users` ON `storeorder`.`uid` = `users`.`uid` WHERE `se_order`.`orderCreationDate` >= '2012-10-04 00:00:00' AND `se_order`.`orderCreationDate` < '2013-02-16 00:00:00'";//364331 -> ???
//$query = "SELECT `users`.`email` AS `user_email`, `users`.`firstName` AS `user_firstname`, `users`.`lastName` AS `user_lastname`, `se_order`.* FROM `se_order` LEFT JOIN `storeorder` ON `se_order`.`yahooOrderIdNumeric` = `storeorder`.`yahooOrderIdNumeric` LEFT JOIN `users` ON `storeorder`.`uid` = `users`.`uid` WHERE `se_order`.`orderCreationDate` >= '2013-02-16 00:00:00' AND `se_order`.`orderCreationDate` < '2013-04-23 00:00:00'";//??? -> ???
//$query = "SELECT `users`.`email` AS `user_email`, `users`.`firstName` AS `user_firstname`, `users`.`lastName` AS `user_lastname`, `se_order`.* FROM `se_order` LEFT JOIN `storeorder` ON `se_order`.`yahooOrderIdNumeric` = `storeorder`.`yahooOrderIdNumeric` LEFT JOIN `users` ON `storeorder`.`uid` = `users`.`uid` WHERE `se_order`.`orderCreationDate` >= '2013-04-23 00:00:00'"; //??? -> current (???)

$results = $writeConnection->query($query);
$orderFilename = "order_8_" . $realTime[5] . $realTime[4] . $realTime[3] . '_' . $realTime[2] . $realTime[1] . $realTime[0] . ".xml";
//Creates XML string and XML document from the DOM representation
$dom = new DomDocument('1.0');

$orders = $dom->appendChild($dom->createElement('orders'));
foreach ($results as $result) {
    
    //Add order data
    fwrite($transactionLogHandle, "    ->ADDING ORDER NUMBER   : " . $result['yahooOrderIdNumeric'] . "\n");
    
    // Set some variables
    $base_discount_amountValue = (is_null($result['discount'])) ? '0.0000' : $result['discount'];//appears to be actual total discount
    $base_grand_totalValue = (is_null($result['finalGrandTotal'])) ? '0.0000' : $result['finalGrandTotal'];
    $base_shipping_amountValue = (is_null($result['shippingCost'])) ? '0.0000' : $result['shippingCost'];
    $base_shipping_incl_taxValue = (is_null($result['shippingCost'])) ? '0.0000' : $result['shippingCost'];
    $base_subtotalValue = (is_null($result['finalSubTotal'])) ? '0.0000' : $result['finalSubTotal'];
    $base_subtotal_incl_taxValue = (is_null($result['finalSubTotal'])) ? '0.0000' : $result['finalSubTotal'];
    $base_tax_amountValue = (is_null($result['taxTotal'])) ? '0.0000' : $result['taxTotal'];

    if (!is_null($result['shipCountry']) && $result['shipState'] == 'CA') {
	if (strtolower($result['shipCountry']) == 'united states') {
	    $tax_percentValue = '8.75';
	} else {
	    $tax_percentValue = '0.00';
	}
    } else {
	$tax_percentValue = '0.00';
    }

    $base_total_dueValue = (is_null($result['finalGrandTotal'])) ? '0.0000' : $result['finalGrandTotal'];
    $real_created_atValue = (is_null($result['orderCreationDate'])) ? date("Y-m-d H:i:s") : $result['orderCreationDate'];//current date or order creation date
    $created_at_timestampValue = strtotime($real_created_atValue);//set from created date
    $customer_emailValue = (is_null($result['user_email'])) ? (is_null($result['email'])) ? '' : $result['email'] : $result['user_email'];
    $customer_firstnameValue = (is_null($result['user_firstname'])) ? (is_null($result['firstName'])) ? '' : $result['firstName'] : $result['user_firstname'];
    $customer_lastnameValue = (is_null($result['user_lastname'])) ? (is_null($result['lastName'])) ? '' : $result['lastName'] : $result['user_lastname'];
    if (is_null($result['user_firstname'])) {
	$customer_nameValue = '';
    } else {
	$customer_nameValue = $customer_firstnameValue . ' ' . $customer_lastnameValue;
    }
    $customer_nameValue = $customer_firstnameValue . ' ' . $customer_lastnameValue;
    //Lookup customer
    if ($result['user_email'] == NULL) {
	$customer_group_idValue = 0;
    } else {
	$customerQuery = "SELECT `entity_id` FROM `customer_entity` WHERE `email` = '" . $result['user_email'] . "'";
	$customerResults = $writeConnection->query($customerQuery);
	$customerFound = NULL;
	foreach ($customerResults as $customerResult) {  
	    $customerFound = 1;
	}
	if (!$customerFound) {
	    fwrite($transactionLogHandle, "    ->CUSTOMER NOT FOUND    : " . $result['yahooOrderIdNumeric'] . "\n");	    
	    $customer_group_idValue = 0;
	} else {
	    $customer_group_idValue = 1;
	}
    }
    
    $discount_amountValue = (is_null($result['discount'])) ? '0.0000' : $result['discount'];//appears to be actual total discount
    $grand_totalValue = (is_null($result['finalGrandTotal'])) ? '0.0000' : $result['finalGrandTotal'];
    $increment_idValue = $result['yahooOrderIdNumeric'];//import script adds value to 600000000
    $shipping_amountValue = (is_null($result['shippingCost'])) ? '0.0000' : $result['shippingCost'];
    $shipping_incl_taxValue = (is_null($result['shippingCost'])) ? '0.0000' : $result['shippingCost'];
    switch ($result['shippingMethod']) {
	case 'UPS Ground (3-7 Business Days)':
	    $shipping_methodValue = 'ups_03';
	    break;
	case 'APO & FPO Addresses (5-30 Business Days by USPS)':
	    $shipping_methodValue = 'usps_Priority Mail';
	    break;
	case 'UPS Next Day Air (2-3 Business Days)':
	    $shipping_methodValue = 'ups_01';
	    break;
	case '"Alaska, Hawaii, U.S. Virgin Islands & Puerto Rico':
	    $shipping_methodValue = 'usps_Priority Mail';
	    break;
	case 'UPS 2nd Day Air (3-4 Business Days)':
	    $shipping_methodValue = 'ups_02';
	    break;
	case 'International Express (Shipped with Shoebox)':
	    $shipping_methodValue = 'ups_08';
	    break;
	case 'International Express (Shipped without Shoebox)':
	    $shipping_methodValue = 'ups_08';
	    break;
	case 'USPS Priority Mail (4-5 Business Days)':
	    $shipping_methodValue = 'usps_Priority Mail';
	    break;
	case 'UPS 3 Day Select (4-5 Business Days)':
	    $shipping_methodValue = 'ups_12';
	    break;
	case 'EMS - International':
	    $shipping_methodValue = 'ups_08';
	    break;
	case 'Canada Express (4-7 Business Days)':
	    $shipping_methodValue = 'ups_08';
	    break;
	case 'EMS Canada':
	    $shipping_methodValue = 'ups_08';
	    break;
	case 'Christmas Express (Delivered by Dec. 24th)':
	    $shipping_methodValue = 'ups_03';
	    break;
	case 'COD Ground':
	    $shipping_methodValue = 'ups_03';
	    break;
	case 'COD Overnight':
	    $shipping_methodValue = 'ups_01';
	    break;
	case 'Free Christmas Express (Delivered by Dec. 24th)':
	    $shipping_methodValue = 'ups_03';
	    break;
	case 'New Year Express (Delivered by Dec. 31st)':
	    $shipping_methodValue = 'ups_03';
	    break;
	case 'Free UPS Ground (3-7 Business Days)':
	    $shipping_methodValue = 'ups_03';
	    break;
	case 'COD 2-Day':
	    $shipping_methodValue = 'ups_02';
	    break;
	case 'MSI International Express':
	    $shipping_methodValue = 'ups_08';
	    break;
	case 'Customer Pickup':
	    $shipping_methodValue = 'ups_03';
	    break;
	case 'UPS Ground':
	    $shipping_methodValue = 'ups_03';
	    break;
	case 'UPS 2nd Day Air':
	    $shipping_methodValue = 'ups_02';
	    break;
	case 'APO & FPO Addresses':
	    $shipping_methodValue = 'usps_Priority Mail';
	    break;
	case 'UPS Next Day Air Saver':
	    $shipping_methodValue = 'ups_01';
	    break;
	case 'UPS 3 Day Select':
	    $shipping_methodValue = 'ups_12';
	    break;
	case 'International Express':
	    $shipping_methodValue = 'ups_08';
	    break;
	case 'USPS Priority Mail':
	    $shipping_methodValue = 'usps_Priority Mail';
	    break;
	case 'Canada Express':
	    $shipping_methodValue = 'ups_08';
	    break;
	case 'UPS Next Day Air':
	    $shipping_methodValue = 'ups_01';
	    break;
	case 'Holiday Shipping (Delivered by Dec. 24th)':
	    $shipping_methodValue = 'ups_03';
	    break;
	default://case 'NULL'
	    $shipping_methodValue = '';
	    break;
    }
    
    $stateValue = 'new';//Always new -- will set on order status update
    $statusValue = 'pending';//Always Pending -- will set on order status update
    $subtotalValue = (is_null($result['finalSubTotal'])) ? '0.0000' : $result['finalSubTotal'];
    $subtotal_incl_taxValue = (is_null($result['finalSubTotal'])) ? '0.0000' : $result['finalSubTotal'];
    $tax_amountValue = (is_null($result['taxTotal'])) ? '0.0000' : $result['taxTotal'];
    $total_dueValue = (is_null($result['finalGrandTotal'])) ? '0.0000' : $result['finalGrandTotal'];

    // Get total qty
    $itemsQuery = "SELECT * FROM `se_orderitem` WHERE `yahooOrderIdNumeric` = " . $result['yahooOrderIdNumeric'];
    $itemsResult = $writeConnection->query($itemsQuery);
    $itemCount = 0;
    foreach ($itemsResult as $itemResult) {
	$itemCount += 1;//number of items not quantites
    }
    if ($itemCount == 0) {
	fwrite($transactionLogHandle, "      ->NO ITEMS FOUND      : " . $result['yahooOrderIdNumeric'] . "\n");
    }
    $total_qty_orderedValue = $itemCount . '.0000';//Derived from item qty count
    $updated_atValue = date("Y-m-d H:i:s");
    $updated_at_timestampValue = strtotime($real_created_atValue);
    $weightValue = '0.0000'; //No weight data available

    //Shipping
    $shippingCityValue = (is_null($result['shipCity'])) ? '' : $result['shipCity'];
    $shippingCountryValue = (is_null($result['shipCountry'])) ? '' : $result['shipCountry'];
    $shippingEmailValue = (is_null($result['email'])) ? '' : $result['email'];
    $shippingFirstnameValue = (is_null($result['shipName'])) ? '' : $result['shipName'];
    $shippingLastnameValue = '';
    $shippingNameValue = $result['shipName'];
    $shippingPostcodeValue = (is_null($result['shipZip'])) ? '' : $result['shipZip'];
    if (strtolower($shippingCountryValue) == 'united states') {
	$shippingRegionValue = (is_null($result['shipState'])) ? '' : strtoupper($result['shipState']);
    } else {
	$shippingRegionValue = (is_null($result['shipState'])) ? '' : $result['shipState'];
    }
    $shippingRegion_idValue = '';//Seems to work without conversion
    if ((!is_null($result['shipAddress1']) && $result['shipAddress1'] != '') && (is_null($result['shipAddress2']) || $result['shipAddress2'] == '')) {
	$shippingStreetValue = $result['shipAddress1'];
    } elseif ((!is_null($result['shipAddress1']) && $result['shipAddress1'] != '') && (!is_null($result['shipAddress2']) && $result['shipAddress2'] != '')) {
	$shippingStreetValue = $result['shipAddress2'] . '&#10;' . $result['shipAddress2']; //Include CR/LF
    } elseif ((is_null($result['shipAddress1']) || $result['shipAddress1'] == '') && (!is_null($result['shipAddress2']) && $result['shipAddress2'] != '')) {
	$shippingStreetValue = $result['shipAddress2'];
    } else {
	$shippingStreetValue = '';
    }
    $shippingTelephoneValue = (is_null($result['shipPhone'])) ? '' : $result['shipPhone'];
    
    //Billing
    $billingCityValue = (is_null($result['billCity'])) ? '' : $result['billCity'];
    $billingCountryValue = (is_null($result['billCountry'])) ? '' : $result['billCountry'];
    $billingEmailValue = (is_null($result['email'])) ? '' : $result['email'];
    $billingFirstnameValue = (is_null($result['billName'])) ? '' : $result['billName'];
    $billingLastnameValue = '';
    $billingNameValue = $result['billName'];
    $billingPostcodeValue = (is_null($result['billZip'])) ? '' : $result['billZip'];
    if (strtolower($billingCountryValue) == 'united states') {
	$billingRegionValue = (is_null($result['billState'])) ? '' : strtoupper($result['billState']);
    } else {
	$billingRegionValue = (is_null($result['billState'])) ? '' : $result['billState'];
    }
    $billingRegion_idValue = '';//Seems to work without conversion
    if ((!is_null($result['billAddress1']) && $result['billAddress1'] != '') && (is_null($result['billAddress2']) || $result['billAddress2'] == '')) {
	$billingStreetValue = $result['billAddress1'];
    } elseif ((!is_null($result['billAddress1']) && $result['billAddress1'] != '') && (!is_null($result['billAddress2']) && $result['billAddress2'] != '')) {
	$billingStreetValue = $result['billAddress2'] . '&#10;' . $result['billAddress2']; //Include CR/LF
    } elseif ((is_null($result['billAddress1']) || $result['billAddress1'] == '') && (!is_null($result['billAddress2']) && $result['billAddress2'] != '')) {
	$billingStreetValue = $result['billAddress2'];
    } else {
	$billingStreetValue = '';
    }
    $billingTelephoneValue = (is_null($result['billPhone'])) ? '' : $result['billPhone'];
    
    //Payment
    switch ($result['paymentType']) {
	case 'Visa':
	    $cc_typeValue = 'VI';
            $methodValue = 'authorizenet';
	    break;
	case 'AMEX':
	    $cc_typeValue = 'AE';
            $methodValue = 'authorizenet';
            break;
	case 'Mastercard':
	    $cc_typeValue = 'MC';
            $methodValue = 'authorizenet';
            break;
	case 'Discover':
	    $cc_typeValue = 'DI';
            $methodValue = 'authorizenet';
	    break;
	case 'Paypal':
	    $cc_typeValue = '';
            $methodValue = 'paypal_express';
	    break;
	case 'C.O.D.':
	    $cc_typeValue = '';
            $methodValue = 'free';
	    break;
	case 'GiftCert':
	    //100% payed with giftcard
	    $cc_typeValue = '';
            $methodValue = 'free';
	    break;
	default: //NULL
	    $cc_typeValue = '';
	    $methodValue = 'free';
    }
    $amount_authorizedValue = (is_null($result['finalGrandTotal'])) ? '' : $result['finalGrandTotal'];
    $amount_orderedValue = (is_null($result['finalGrandTotal'])) ? '' : $result['finalGrandTotal'];
    $base_amount_authorizedValue = (is_null($result['finalGrandTotal'])) ? '' : $result['finalGrandTotal'];
    $base_amount_orderedValue = (is_null($result['finalGrandTotal'])) ? '' : $result['finalGrandTotal'];
    $base_shipping_amountValue = (is_null($result['shippingCost'])) ? '' : $result['shippingCost'];
    $cc_approvalValue = (is_null($result['ccApprovalNumber'])) ? '' : $result['ccApprovalNumber'];
    $cc_cid_statusValue = (is_null($result['ccCvvResponse'])) ? '' : $result['ccCvvResponse'];
    $ccExpiration = (is_null($result['ccExpiration'])) ? '' : explode('/', $result['ccExpiration']);
    if (is_null($ccExpiration)) {
        $cc_exp_monthValue = '';
        $cc_exp_yearValue = '';
    } else {
        $cc_exp_monthValue = $ccExpiration[0];
        $cc_exp_yearValue = $ccExpiration[1];
    }
    $cc_last4Value = (is_null($result['ccExpiration'])) ? '' : '****';//data not available
    $anet_trans_methodValue = '';//***
    $cc_avs_statusValue = '';//***
    $cc_trans_idValue = '';//***
    $last_trans_idValue = '';//***
    $shipping_amountValue = (is_null($result['shippingCost'])) ? '' : $result['shippingCost'];

    $order = $orders->appendChild($dom->createElement('order'));

    $adjustment_negative = $order->appendChild($dom->createElement('adjustment_negative'));
    $adjustment_negative->appendChild($dom->createTextNode(''));
    $adjustment_positive = $order->appendChild($dom->createElement('adjustment_positive'));
    $adjustment_positive->appendChild($dom->createTextNode(''));
    $applied_rule_ids = $order->appendChild($dom->createElement('applied_rule_ids'));
    $applied_rule_ids->appendChild($dom->createTextNode(''));//none used -- only used for military until migration complete
    $base_adjustment_negative = $order->appendChild($dom->createElement('base_adjustment_negative'));
    $base_adjustment_negative->appendChild($dom->createTextNode(''));
    $base_adjustment_positive = $order->appendChild($dom->createElement('base_adjustment_positive'));
    $base_adjustment_positive->appendChild($dom->createTextNode(''));
    $base_currency_code = $order->appendChild($dom->createElement('base_currency_code'));
    $base_currency_code->appendChild($dom->createTextNode('USD'));// Always USD
    $base_custbalance_amount = $order->appendChild($dom->createElement('base_custbalance_amount'));
    $base_custbalance_amount->appendChild($dom->createTextNode(''));
    $base_discount_amount = $order->appendChild($dom->createElement('base_discount_amount'));
    $base_discount_amount->appendChild($dom->createTextNode($base_discount_amountValue));
    $base_discount_canceled = $order->appendChild($dom->createElement('base_discount_canceled'));
    $base_discount_canceled->appendChild($dom->createTextNode(''));
    $base_discount_invoiced = $order->appendChild($dom->createElement('base_discount_invoiced'));
    $base_discount_invoiced->appendChild($dom->createTextNode(''));
    $base_discount_refunded = $order->appendChild($dom->createElement('base_discount_refunded'));
    $base_discount_refunded->appendChild($dom->createTextNode(''));
    $base_grand_total = $order->appendChild($dom->createElement('base_grand_total'));
    $base_grand_total->appendChild($dom->createTextNode($base_grand_totalValue));
    $base_hidden_tax_amount = $order->appendChild($dom->createElement('base_hidden_tax_amount'));
    $base_hidden_tax_amount->appendChild($dom->createTextNode(''));
    $base_hidden_tax_invoiced = $order->appendChild($dom->createElement('base_hidden_tax_invoiced'));
    $base_hidden_tax_invoiced->appendChild($dom->createTextNode(''));
    $base_hidden_tax_refunded = $order->appendChild($dom->createElement('base_hidden_tax_refunded'));
    $base_hidden_tax_refunded->appendChild($dom->createTextNode(''));
    $base_shipping_amount = $order->appendChild($dom->createElement('base_shipping_amount'));
    $base_shipping_amount->appendChild($dom->createTextNode($base_shipping_amountValue));
    $base_shipping_canceled = $order->appendChild($dom->createElement('base_shipping_canceled'));
    $base_shipping_canceled->appendChild($dom->createTextNode(''));
    $base_shipping_discount_amount = $order->appendChild($dom->createElement('base_shipping_discount_amount'));
    $base_shipping_discount_amount->appendChild($dom->createTextNode('0.0000'));//Always 0
    $base_shipping_hidden_tax_amount = $order->appendChild($dom->createElement('base_shipping_hidden_tax_amount'));
    $base_shipping_hidden_tax_amount->appendChild($dom->createTextNode(''));
    $base_shipping_incl_tax = $order->appendChild($dom->createElement('base_shipping_incl_tax'));
    $base_shipping_incl_tax->appendChild($dom->createTextNode($base_shipping_incl_taxValue));
    $base_shipping_invoiced = $order->appendChild($dom->createElement('base_shipping_invoiced'));
    $base_shipping_invoiced->appendChild($dom->createTextNode(''));
    $base_shipping_refunded = $order->appendChild($dom->createElement('base_shipping_refunded'));
    $base_shipping_refunded->appendChild($dom->createTextNode(''));
    $base_shipping_tax_amount = $order->appendChild($dom->createElement('base_shipping_tax_amount'));
    $base_shipping_tax_amount->appendChild($dom->createTextNode('0.0000'));//Always 0
    $base_shipping_tax_refunded = $order->appendChild($dom->createElement('base_shipping_tax_refunded'));
    $base_shipping_tax_refunded->appendChild($dom->createTextNode(''));
    $base_subtotal = $order->appendChild($dom->createElement('base_subtotal'));
    $base_subtotal->appendChild($dom->createTextNode($base_subtotalValue));
    $base_subtotal_canceled = $order->appendChild($dom->createElement('base_subtotal_canceled'));
    $base_subtotal_canceled->appendChild($dom->createTextNode(''));
    $base_subtotal_incl_tax = $order->appendChild($dom->createElement('base_subtotal_incl_tax'));
    $base_subtotal_incl_tax->appendChild($dom->createTextNode($base_subtotal_incl_taxValue));
    $base_subtotal_invoiced = $order->appendChild($dom->createElement('base_subtotal_invoiced'));
    $base_subtotal_invoiced->appendChild($dom->createTextNode(''));
    $base_subtotal_refunded = $order->appendChild($dom->createElement('base_subtotal_refunded'));
    $base_subtotal_refunded->appendChild($dom->createTextNode(''));
    $base_tax_amount = $order->appendChild($dom->createElement('base_tax_amount'));
    $base_tax_amount->appendChild($dom->createTextNode($base_tax_amountValue));
    $base_tax_canceled = $order->appendChild($dom->createElement('base_tax_canceled'));
    $base_tax_canceled->appendChild($dom->createTextNode(''));
    $base_tax_invoiced = $order->appendChild($dom->createElement('base_tax_invoiced'));
    $base_tax_invoiced->appendChild($dom->createTextNode(''));
    $base_tax_refunded = $order->appendChild($dom->createElement('base_tax_refunded'));
    $base_tax_refunded->appendChild($dom->createTextNode(''));
    $base_to_global_rate = $order->appendChild($dom->createElement('base_to_global_rate'));
    $base_to_global_rate->appendChild($dom->createTextNode('1'));//Always 1
    $base_to_order_rate = $order->appendChild($dom->createElement('base_to_order_rate'));
    $base_to_order_rate->appendChild($dom->createTextNode('1'));//Always 1
    $base_total_canceled = $order->appendChild($dom->createElement('base_total_canceled'));
    $base_total_canceled->appendChild($dom->createTextNode('0.0000'));
    $base_total_due = $order->appendChild($dom->createElement('base_total_due'));
    $base_total_due->appendChild($dom->createTextNode($base_total_dueValue));
    $base_total_invoiced = $order->appendChild($dom->createElement('base_total_invoiced'));
    $base_total_invoiced->appendChild($dom->createTextNode('0.0000'));
    $base_total_invoiced_cost = $order->appendChild($dom->createElement('base_total_invoiced_cost'));
    $base_total_invoiced_cost->appendChild($dom->createTextNode(''));
    $base_total_offline_refunded = $order->appendChild($dom->createElement('base_total_offline_refunded'));
    $base_total_offline_refunded->appendChild($dom->createTextNode('0.0000'));
    $base_total_online_refunded = $order->appendChild($dom->createElement('base_total_online_refunded'));
    $base_total_online_refunded->appendChild($dom->createTextNode('0.0000'));
    $base_total_paid = $order->appendChild($dom->createElement('base_total_paid'));
    $base_total_paid->appendChild($dom->createTextNode('0.0000'));
    $base_total_qty_ordered = $order->appendChild($dom->createElement('base_total_qty_ordered'));
    $base_total_qty_ordered->appendChild($dom->createTextNode(''));//Always NULL
    $base_total_refunded = $order->appendChild($dom->createElement('base_total_refunded'));
    $base_total_refunded->appendChild($dom->createTextNode('0.0000'));
    $can_ship_partially = $order->appendChild($dom->createElement('can_ship_partially'));
    $can_ship_partially->appendChild($dom->createTextNode(''));
    $can_ship_partially_item = $order->appendChild($dom->createElement('can_ship_partially_item'));
    $can_ship_partially_item->appendChild($dom->createTextNode(''));
    $coupon_code = $order->appendChild($dom->createElement('coupon_code'));
    $coupon_code->appendChild($dom->createTextNode(''));
    $real_created_at = $order->appendChild($dom->createElement('real_created_at'));
    $real_created_at->appendChild($dom->createTextNode($real_created_atValue));
    $created_at_timestamp = $order->appendChild($dom->createElement('created_at_timestamp'));
    $created_at_timestamp->appendChild($dom->createTextNode($created_at_timestampValue));
    $custbalance_amount = $order->appendChild($dom->createElement('custbalance_amount'));
    $custbalance_amount->appendChild($dom->createTextNode(''));
    $customer_dob = $order->appendChild($dom->createElement('customer_dob'));
    $customer_dob->appendChild($dom->createTextNode(''));
    $customer_email = $order->appendChild($dom->createElement('customer_email'));
    $customer_email->appendChild($dom->createTextNode($customer_emailValue));
    $customer_firstname = $order->appendChild($dom->createElement('customer_firstname'));
    $customer_firstname->appendChild($dom->createTextNode($customer_firstnameValue));
    $customer_gender = $order->appendChild($dom->createElement('customer_gender'));
    $customer_gender->appendChild($dom->createTextNode(''));
    $customer_group_id = $order->appendChild($dom->createElement('customer_group_id'));
    $customer_group_id->appendChild($dom->createTextNode($customer_group_idValue));
    $customer_lastname = $order->appendChild($dom->createElement('customer_lastname'));
    $customer_lastname->appendChild($dom->createTextNode($customer_lastnameValue));
    $customer_middlename = $order->appendChild($dom->createElement('customer_middlename'));
    $customer_middlename->appendChild($dom->createTextNode(''));
    $customer_name = $order->appendChild($dom->createElement('customer_name'));
    $customer_name->appendChild($dom->createTextNode($customer_nameValue));
    $customer_note = $order->appendChild($dom->createElement('customer_note'));
    $customer_note->appendChild($dom->createTextNode(''));
    $customer_note_notify = $order->appendChild($dom->createElement('customer_note_notify'));
    $customer_note_notify->appendChild($dom->createTextNode('1'));
    $customer_prefix = $order->appendChild($dom->createElement('customer_prefix'));
    $customer_prefix->appendChild($dom->createTextNode(''));
    $customer_suffix = $order->appendChild($dom->createElement('customer_suffix'));
    $customer_suffix->appendChild($dom->createTextNode(''));
    $customer_taxvat = $order->appendChild($dom->createElement('customer_taxvat'));
    $customer_taxvat->appendChild($dom->createTextNode(''));
    $discount_amount = $order->appendChild($dom->createElement('discount_amount'));
    $discount_amount->appendChild($dom->createTextNode($discount_amountValue));
    $discount_canceled = $order->appendChild($dom->createElement('discount_canceled'));
    $discount_canceled->appendChild($dom->createTextNode(''));
    $discount_invoiced = $order->appendChild($dom->createElement('discount_invoiced'));
    $discount_invoiced->appendChild($dom->createTextNode(''));
    $discount_refunded = $order->appendChild($dom->createElement('discount_refunded'));
    $discount_refunded->appendChild($dom->createTextNode(''));
    $email_sent = $order->appendChild($dom->createElement('email_sent'));
    $email_sent->appendChild($dom->createTextNode('1'));//Always 1
    $ext_customer_id = $order->appendChild($dom->createElement('ext_customer_id'));
    $ext_customer_id->appendChild($dom->createTextNode(''));
    $ext_order_id = $order->appendChild($dom->createElement('ext_order_id'));
    $ext_order_id->appendChild($dom->createTextNode(''));
    $forced_do_shipment_with_invoice = $order->appendChild($dom->createElement('forced_do_shipment_with_invoice'));
    $forced_do_shipment_with_invoice->appendChild($dom->createTextNode(''));
    $global_currency_code = $order->appendChild($dom->createElement('global_currency_code'));
    $global_currency_code->appendChild($dom->createTextNode('USD'));
    $grand_total = $order->appendChild($dom->createElement('grand_total'));
    $grand_total->appendChild($dom->createTextNode($grand_totalValue));
    $hidden_tax_amount = $order->appendChild($dom->createElement('hidden_tax_amount'));
    $hidden_tax_amount->appendChild($dom->createTextNode(''));
    $hidden_tax_invoiced = $order->appendChild($dom->createElement('hidden_tax_invoiced'));
    $hidden_tax_invoiced->appendChild($dom->createTextNode(''));
    $hidden_tax_refunded = $order->appendChild($dom->createElement('hidden_tax_refunded'));
    $hidden_tax_refunded->appendChild($dom->createTextNode(''));
    $hold_before_state = $order->appendChild($dom->createElement('hold_before_state'));
    $hold_before_state->appendChild($dom->createTextNode(''));
    $hold_before_status = $order->appendChild($dom->createElement('hold_before_status'));
    $hold_before_status->appendChild($dom->createTextNode(''));
    $increment_id = $order->appendChild($dom->createElement('increment_id'));
    $increment_id->appendChild($dom->createTextNode($increment_idValue));
    $is_hold = $order->appendChild($dom->createElement('is_hold'));
    $is_hold->appendChild($dom->createTextNode(''));
    $is_multi_payment = $order->appendChild($dom->createElement('is_multi_payment'));
    $is_multi_payment->appendChild($dom->createTextNode(''));
    $is_virtual = $order->appendChild($dom->createElement('is_virtual'));
    $is_virtual->appendChild($dom->createTextNode('0'));//Always 0
    $order_currency_code = $order->appendChild($dom->createElement('order_currency_code'));
    $order_currency_code->appendChild($dom->createTextNode('USD'));
    $payment_authorization_amount = $order->appendChild($dom->createElement('payment_authorization_amount'));
    $payment_authorization_amount->appendChild($dom->createTextNode(''));
    $payment_authorization_expiration = $order->appendChild($dom->createElement('payment_authorization_expiration'));
    $payment_authorization_expiration->appendChild($dom->createTextNode(''));
    $paypal_ipn_customer_notified = $order->appendChild($dom->createElement('paypal_ipn_customer_notified'));
    $paypal_ipn_customer_notified->appendChild($dom->createTextNode(''));
    $real_order_id = $order->appendChild($dom->createElement('real_order_id'));
    $real_order_id->appendChild($dom->createTextNode(''));
    $remote_ip = $order->appendChild($dom->createElement('remote_ip'));
    $remote_ip->appendChild($dom->createTextNode(''));
    $shipping_amount = $order->appendChild($dom->createElement('shipping_amount'));
    $shipping_amount->appendChild($dom->createTextNode($shipping_amountValue));
    $shipping_canceled = $order->appendChild($dom->createElement('shipping_canceled'));
    $shipping_canceled->appendChild($dom->createTextNode(''));
    $shipping_discount_amount = $order->appendChild($dom->createElement('shipping_discount_amount'));
    $shipping_discount_amount->appendChild($dom->createTextNode('0.0000'));
    $shipping_hidden_tax_amount = $order->appendChild($dom->createElement('shipping_hidden_tax_amount'));
    $shipping_hidden_tax_amount->appendChild($dom->createTextNode(''));
    $shipping_incl_tax = $order->appendChild($dom->createElement('shipping_incl_tax'));
    $shipping_incl_tax->appendChild($dom->createTextNode($shipping_incl_taxValue));
    $shipping_invoiced = $order->appendChild($dom->createElement('shipping_invoiced'));
    $shipping_invoiced->appendChild($dom->createTextNode(''));
    $shipping_method = $order->appendChild($dom->createElement('shipping_method'));
    $shipping_method->appendChild($dom->createTextNode($shipping_methodValue));
    $shipping_refunded = $order->appendChild($dom->createElement('shipping_refunded'));
    $shipping_refunded->appendChild($dom->createTextNode(''));
    $shipping_tax_amount = $order->appendChild($dom->createElement('shipping_tax_amount'));
    $shipping_tax_amount->appendChild($dom->createTextNode('0.0000'));
    $shipping_tax_refunded = $order->appendChild($dom->createElement('shipping_tax_refunded'));
    $shipping_tax_refunded->appendChild($dom->createTextNode(''));
    $state = $order->appendChild($dom->createElement('state'));
    $state->appendChild($dom->createTextNode($stateValue));
    $status = $order->appendChild($dom->createElement('status'));
    $status->appendChild($dom->createTextNode($statusValue));
    $store = $order->appendChild($dom->createElement('store'));
    $store->appendChild($dom->createTextNode('sneakerhead_cn'));
    $subtotal = $order->appendChild($dom->createElement('subTotal'));
    $subtotal->appendChild($dom->createTextNode($subtotalValue));
    $subtotal_canceled = $order->appendChild($dom->createElement('subtotal_canceled'));
    $subtotal_canceled->appendChild($dom->createTextNode(''));
    $subtotal_incl_tax = $order->appendChild($dom->createElement('subtotal_incl_tax'));
    $subtotal_incl_tax->appendChild($dom->createTextNode($subtotal_incl_taxValue));
    $subtotal_invoiced = $order->appendChild($dom->createElement('subtotal_invoiced'));
    $subtotal_invoiced->appendChild($dom->createTextNode(''));
    $subtotal_refunded = $order->appendChild($dom->createElement('subtotal_refunded'));
    $subtotal_refunded->appendChild($dom->createTextNode(''));
    $tax_amount = $order->appendChild($dom->createElement('tax_amount'));
    $tax_amount->appendChild($dom->createTextNode($tax_amountValue));
    $tax_canceled = $order->appendChild($dom->createElement('tax_canceled'));
    $tax_canceled->appendChild($dom->createTextNode(''));
    $tax_invoiced = $order->appendChild($dom->createElement('tax_invoiced'));
    $tax_invoiced->appendChild($dom->createTextNode(''));
    $tax_percent = $order->appendChild($dom->createElement('tax_percent'));
    $tax_percent->appendChild($dom->createTextNode($tax_percentValue));
    $tax_refunded = $order->appendChild($dom->createElement('tax_refunded'));
    $tax_refunded->appendChild($dom->createTextNode(''));
    $total_canceled = $order->appendChild($dom->createElement('total_canceled'));
    $total_canceled->appendChild($dom->createTextNode('0.0000'));
    $total_due = $order->appendChild($dom->createElement('total_due'));
    $total_due->appendChild($dom->createTextNode($total_dueValue));
    $total_invoiced = $order->appendChild($dom->createElement('total_invoiced'));
    $total_invoiced->appendChild($dom->createTextNode('0.0000'));
    $total_item_count = $order->appendChild($dom->createElement('total_item_count'));
    $total_item_count->appendChild($dom->createTextNode(''));
    $total_offline_refunded = $order->appendChild($dom->createElement('total_offline_refunded'));
    $total_offline_refunded->appendChild($dom->createTextNode('0.0000'));
    $total_online_refunded = $order->appendChild($dom->createElement('total_online_refunded'));
    $total_online_refunded->appendChild($dom->createTextNode('0.0000'));
    $total_paid = $order->appendChild($dom->createElement('total_paid'));
    $total_paid->appendChild($dom->createTextNode('0.0000'));
    $total_qty_ordered = $order->appendChild($dom->createElement('total_qty_ordered'));
    $total_qty_ordered->appendChild($dom->createTextNode($total_qty_orderedValue));
    $total_refunded = $order->appendChild($dom->createElement('total_refunded'));
    $total_refunded->appendChild($dom->createTextNode('0.0000'));
    $tracking_numbers = $order->appendChild($dom->createElement('tracking_numbers'));
    $tracking_numbers->appendChild($dom->createTextNode(''));
    $updated_at = $order->appendChild($dom->createElement('updated_at'));
    $updated_at->appendChild($dom->createTextNode($updated_atValue));
    $updated_at_timestamp = $order->appendChild($dom->createElement('updated_at_timestamp'));
    $updated_at_timestamp->appendChild($dom->createTextNode($updated_at_timestampValue));
    $weight = $order->appendChild($dom->createElement('weight'));
    $weight->appendChild($dom->createTextNode($weightValue));
    $x_forwarded_for = $order->appendChild($dom->createElement('x_forwarded_for'));
    $x_forwarded_for->appendChild($dom->createTextNode(''));

    //Build shipping
    $shipping_address = $order->appendChild($dom->createElement('shipping_address'));
    
    $shippingCity = $shipping_address->appendChild($dom->createElement('city'));
    $shippingCity->appendChild($dom->createTextNode($shippingCityValue));
    $shippingCompany = $shipping_address->appendChild($dom->createElement('company'));
    $shippingCompany->appendChild($dom->createTextNode(''));
    $shippingCountry = $shipping_address->appendChild($dom->createElement('country'));
    $shippingCountry->appendChild($dom->createTextNode($shippingCountryValue));
    $shippingCountry_id = $shipping_address->appendChild($dom->createElement('country_id'));
    $shippingCountry_id->appendChild($dom->createTextNode(''));
    $shippingCountry_iso2 = $shipping_address->appendChild($dom->createElement('country_iso2'));
    $shippingCountry_iso2->appendChild($dom->createTextNode(''));
    $shippingCountry_iso3 = $shipping_address->appendChild($dom->createElement('country_iso3'));
    $shippingCountry_iso3->appendChild($dom->createTextNode(''));
    $shippingEmail = $shipping_address->appendChild($dom->createElement('email'));
    $shippingEmail->appendChild($dom->createTextNode($shippingEmailValue));
    $shippingFax = $shipping_address->appendChild($dom->createElement('fax'));
    $shippingFax->appendChild($dom->createTextNode(''));
    $shippingFirstname = $shipping_address->appendChild($dom->createElement('firstname'));
    $shippingFirstname->appendChild($dom->createTextNode($shippingFirstnameValue));
    $shippingLastname = $shipping_address->appendChild($dom->createElement('lastname'));
    $shippingLastname->appendChild($dom->createTextNode($shippingLastnameValue));
    $shippingMiddlename = $shipping_address->appendChild($dom->createElement('middlename'));
    $shippingMiddlename->appendChild($dom->createTextNode(''));
    $shippingName = $shipping_address->appendChild($dom->createElement('name'));
    $shippingName->appendChild($dom->createTextNode($shippingNameValue));
    $shippingPostcode = $shipping_address->appendChild($dom->createElement('postcode'));
    $shippingPostcode->appendChild($dom->createTextNode($shippingPostcodeValue));
    $shippingPrefix = $shipping_address->appendChild($dom->createElement('prefix'));
    $shippingPrefix->appendChild($dom->createTextNode(''));
    $shippingRegion = $shipping_address->appendChild($dom->createElement('region'));
    $shippingRegion->appendChild($dom->createTextNode($shippingRegionValue));
    $shippingRegion_id = $shipping_address->appendChild($dom->createElement('region_id'));
    $shippingRegion_id->appendChild($dom->createTextNode($shippingRegion_idValue));
    $shippingRegion_iso2 = $shipping_address->appendChild($dom->createElement('region_iso2'));
    $shippingRegion_iso2->appendChild($dom->createTextNode(''));
    $shippingStreet = $shipping_address->appendChild($dom->createElement('street'));
    $shippingStreet->appendChild($dom->createTextNode($shippingStreetValue));
    $shippingSuffix = $shipping_address->appendChild($dom->createElement('suffix'));
    $shippingSuffix->appendChild($dom->createTextNode(''));
    $shippingTelephone = $shipping_address->appendChild($dom->createElement('telephone'));
    $shippingTelephone->appendChild($dom->createTextNode($shippingTelephoneValue));

    // Build billing
    $billing_address = $order->appendChild($dom->createElement('billing_address'));
    
    $billingCity = $billing_address->appendChild($dom->createElement('city'));
    $billingCity->appendChild($dom->createTextNode($billingCityValue));
    $billingCompany = $billing_address->appendChild($dom->createElement('company'));
    $billingCompany->appendChild($dom->createTextNode(''));
    $billingCountry = $billing_address->appendChild($dom->createElement('country'));
    $billingCountry->appendChild($dom->createTextNode($billingCountryValue));
    $billingCountry_id = $billing_address->appendChild($dom->createElement('country_id'));
    $billingCountry_id->appendChild($dom->createTextNode(''));
    $billingCountry_iso2 = $billing_address->appendChild($dom->createElement('country_iso2'));
    $billingCountry_iso2->appendChild($dom->createTextNode(''));
    $billingCountry_iso3 = $billing_address->appendChild($dom->createElement('country_iso3'));
    $billingCountry_iso3->appendChild($dom->createTextNode(''));
    $billingEmail = $billing_address->appendChild($dom->createElement('email'));
    $billingEmail->appendChild($dom->createTextNode($billingEmailValue));
    $billingFax = $billing_address->appendChild($dom->createElement('fax'));
    $billingFax->appendChild($dom->createTextNode(''));
    $billingFirstname = $billing_address->appendChild($dom->createElement('firstname'));
    $billingFirstname->appendChild($dom->createTextNode($billingFirstnameValue));
    $billingLastname = $billing_address->appendChild($dom->createElement('lastname'));
    $billingLastname->appendChild($dom->createTextNode($billingLastnameValue));
    $billingMiddlename = $billing_address->appendChild($dom->createElement('middlename'));
    $billingMiddlename->appendChild($dom->createTextNode(''));
    $billingName = $billing_address->appendChild($dom->createElement('name'));
    $billingName->appendChild($dom->createTextNode($billingNameValue));
    $billingPostcode = $billing_address->appendChild($dom->createElement('postcode'));
    $billingPostcode->appendChild($dom->createTextNode($billingPostcodeValue));
    $billingPrefix = $billing_address->appendChild($dom->createElement('prefix'));
    $billingPrefix->appendChild($dom->createTextNode(''));
    $billingRegion = $billing_address->appendChild($dom->createElement('region'));
    $billingRegion->appendChild($dom->createTextNode($billingRegionValue));
    $billingRegion_id = $billing_address->appendChild($dom->createElement('region_id'));
    $billingRegion_id->appendChild($dom->createTextNode($billingRegion_idValue));
    $billingRegion_iso2 = $billing_address->appendChild($dom->createElement('region_iso2'));
    $billingRegion_iso2->appendChild($dom->createTextNode(''));
    $billingStreet = $billing_address->appendChild($dom->createElement('street'));
    $billingStreet->appendChild($dom->createTextNode($billingStreetValue));
    $billingSuffix = $billing_address->appendChild($dom->createElement('suffix'));
    $billingSuffix->appendChild($dom->createTextNode(''));
    $billingTelephone = $billing_address->appendChild($dom->createElement('telephone'));
    $billingTelephone->appendChild($dom->createTextNode($billingTelephoneValue));
    
    // Build payment

    $payment = $order->appendChild($dom->createElement('payment'));

    $account_status = $payment->appendChild($dom->createElement('account_status'));
    $account_status->appendChild($dom->createTextNode(''));
    $address_status = $payment->appendChild($dom->createElement('address_status'));
    $address_status->appendChild($dom->createTextNode(''));
    $amount = $payment->appendChild($dom->createElement('amount'));
    $amount->appendChild($dom->createTextNode(''));
    $amount_authorized = $payment->appendChild($dom->createElement('amount_authorized'));
    $amount_authorized->appendChild($dom->createTextNode($amount_authorizedValue));
    $amount_canceled = $payment->appendChild($dom->createElement('amount_canceled'));
    $amount_canceled->appendChild($dom->createTextNode(''));
    $amount_ordered = $payment->appendChild($dom->createElement('amount_ordered'));
    $amount_ordered->appendChild($dom->createTextNode($amount_orderedValue));
    $amount_paid = $payment->appendChild($dom->createElement('amount_paid'));
    $amount_paid->appendChild($dom->createTextNode(''));
    $amount_refunded = $payment->appendChild($dom->createElement('amount_refunded'));
    $amount_refunded->appendChild($dom->createTextNode(''));
    $anet_trans_method = $payment->appendChild($dom->createElement('anet_trans_method'));
    $anet_trans_method->appendChild($dom->createTextNode($anet_trans_methodValue));
    $base_amount_authorized = $payment->appendChild($dom->createElement('base_amount_authorized'));
    $base_amount_authorized->appendChild($dom->createTextNode($base_amount_authorizedValue));
    $base_amount_canceled = $payment->appendChild($dom->createElement('base_amount_canceled'));
    $base_amount_canceled->appendChild($dom->createTextNode(''));
    $base_amount_ordered = $payment->appendChild($dom->createElement('base_amount_ordered'));
    $base_amount_ordered->appendChild($dom->createTextNode($base_amount_orderedValue));
    $base_amount_paid = $payment->appendChild($dom->createElement('base_amount_paid'));
    $base_amount_paid->appendChild($dom->createTextNode(''));
    $base_amount_paid_online = $payment->appendChild($dom->createElement('base_amount_paid_online'));
    $base_amount_paid_online->appendChild($dom->createTextNode(''));
    $base_amount_refunded = $payment->appendChild($dom->createElement('base_amount_refunded'));
    $base_amount_refunded->appendChild($dom->createTextNode(''));
    $base_amount_refunded_online = $payment->appendChild($dom->createElement('base_amount_refunded_online'));
    $base_amount_refunded_online->appendChild($dom->createTextNode(''));
    $base_shipping_amount = $payment->appendChild($dom->createElement('base_shipping_amount'));
    $base_shipping_amount->appendChild($dom->createTextNode($base_shipping_amountValue));
    $base_shipping_captured = $payment->appendChild($dom->createElement('base_shipping_captured'));
    $base_shipping_captured->appendChild($dom->createTextNode(''));
    $base_shipping_refunded = $payment->appendChild($dom->createElement('base_shipping_refunded'));
    $base_shipping_refunded->appendChild($dom->createTextNode(''));
    $cc_approval = $payment->appendChild($dom->createElement('cc_approval'));
    $cc_approval->appendChild($dom->createTextNode($cc_approvalValue));
    $cc_avs_status = $payment->appendChild($dom->createElement('cc_avs_status'));
    $cc_avs_status->appendChild($dom->createTextNode($cc_avs_statusValue));
    $cc_cid_status = $payment->appendChild($dom->createElement('cc_cid_status'));
    $cc_cid_status->appendChild($dom->createTextNode($cc_cid_statusValue));
    $cc_debug_request_body = $payment->appendChild($dom->createElement('cc_debug_request_body'));
    $cc_debug_request_body->appendChild($dom->createTextNode(''));
    $cc_debug_response_body = $payment->appendChild($dom->createElement('cc_debug_response_body'));
    $cc_debug_response_body->appendChild($dom->createTextNode(''));
    $cc_debug_response_serialized = $payment->appendChild($dom->createElement('cc_debug_response_serialized'));
    $cc_debug_response_serialized->appendChild($dom->createTextNode(''));
    $cc_exp_month = $payment->appendChild($dom->createElement('cc_exp_month'));
    $cc_exp_month->appendChild($dom->createTextNode($cc_exp_monthValue));
    $cc_exp_year = $payment->appendChild($dom->createElement('cc_exp_year'));
    $cc_exp_year->appendChild($dom->createTextNode($cc_exp_yearValue));
    $cc_last4 = $payment->appendChild($dom->createElement('cc_last4'));
    $cc_last4->appendChild($dom->createTextNode($cc_last4Value));
    $cc_number_enc = $payment->appendChild($dom->createElement('cc_number_enc'));
    $cc_number_enc->appendChild($dom->createTextNode(''));
    $cc_owner = $payment->appendChild($dom->createElement('cc_owner'));
    $cc_owner->appendChild($dom->createTextNode(''));
    $cc_raw_request = $payment->appendChild($dom->createElement('cc_raw_request'));
    $cc_raw_request->appendChild($dom->createTextNode(''));
    $cc_raw_response = $payment->appendChild($dom->createElement('cc_raw_response'));
    $cc_raw_response->appendChild($dom->createTextNode(''));
    $cc_secure_verify = $payment->appendChild($dom->createElement('cc_secure_verify'));
    $cc_secure_verify->appendChild($dom->createTextNode(''));
    $cc_ss_issue = $payment->appendChild($dom->createElement('cc_ss_issue'));
    $cc_ss_issue->appendChild($dom->createTextNode(''));
    $cc_ss_start_month = $payment->appendChild($dom->createElement('cc_ss_start_month'));
    $cc_ss_start_month->appendChild($dom->createTextNode('0'));//appears to be 0 since not used
    $cc_ss_start_year = $payment->appendChild($dom->createElement('cc_ss_start_year'));
    $cc_ss_start_year->appendChild($dom->createTextNode('0'));//appears to be 0 since not used
    $cc_status = $payment->appendChild($dom->createElement('cc_status'));
    $cc_status->appendChild($dom->createTextNode(''));
    $cc_status_description = $payment->appendChild($dom->createElement('cc_status_description'));
    $cc_status_description->appendChild($dom->createTextNode(''));
    $cc_trans_id = $payment->appendChild($dom->createElement('cc_trans_id'));
    $cc_trans_id->appendChild($dom->createTextNode($cc_trans_idValue));
    $cc_type = $payment->appendChild($dom->createElement('cc_type'));
    $cc_type->appendChild($dom->createTextNode($cc_typeValue));
    $cybersource_token = $payment->appendChild($dom->createElement('cybersource_token'));
    $cybersource_token->appendChild($dom->createTextNode(''));
    $echeck_account_name = $payment->appendChild($dom->createElement('echeck_account_name'));
    $echeck_account_name->appendChild($dom->createTextNode(''));
    $echeck_account_type = $payment->appendChild($dom->createElement('echeck_account_type'));
    $echeck_account_type->appendChild($dom->createTextNode(''));
    $echeck_bank_name = $payment->appendChild($dom->createElement('echeck_bank_name'));
    $echeck_bank_name->appendChild($dom->createTextNode(''));
    $echeck_routing_number = $payment->appendChild($dom->createElement('echeck_routing_number'));
    $echeck_routing_number->appendChild($dom->createTextNode(''));
    $echeck_type = $payment->appendChild($dom->createElement('echeck_type'));
    $echeck_type->appendChild($dom->createTextNode(''));
    $flo2cash_account_id = $payment->appendChild($dom->createElement('flo2cash_account_id'));
    $flo2cash_account_id->appendChild($dom->createTextNode(''));
    $ideal_issuer_id = $payment->appendChild($dom->createElement('ideal_issuer_id'));
    $ideal_issuer_id->appendChild($dom->createTextNode(''));
    $ideal_issuer_title = $payment->appendChild($dom->createElement('ideal_issuer_title'));
    $ideal_issuer_title->appendChild($dom->createTextNode(''));
    $ideal_transaction_checked = $payment->appendChild($dom->createElement('ideal_transaction_checked'));
    $ideal_transaction_checked->appendChild($dom->createTextNode(''));
    $last_trans_id = $payment->appendChild($dom->createElement('last_trans_id'));
    $last_trans_id->appendChild($dom->createTextNode($last_trans_idValue));
    $method = $payment->appendChild($dom->createElement('method'));
    $method->appendChild($dom->createTextNode($methodValue));
    $paybox_question_number = $payment->appendChild($dom->createElement('paybox_question_number'));
    $paybox_question_number->appendChild($dom->createTextNode(''));
    $paybox_request_number = $payment->appendChild($dom->createElement('paybox_request_number'));
    $paybox_request_number->appendChild($dom->createTextNode(''));
    $po_number = $payment->appendChild($dom->createElement('po_number'));
    $po_number->appendChild($dom->createTextNode(''));
    $protection_eligibility = $payment->appendChild($dom->createElement('protection_eligibility'));
    $protection_eligibility->appendChild($dom->createTextNode(''));
    $shipping_amount = $payment->appendChild($dom->createElement('shipping_amount'));
    $shipping_amount->appendChild($dom->createTextNode($shipping_amountValue));
    $shipping_captured = $payment->appendChild($dom->createElement('shipping_captured'));
    $shipping_captured->appendChild($dom->createTextNode(''));
    $shipping_refunded = $payment->appendChild($dom->createElement('shipping_refunded'));
    $shipping_refunded->appendChild($dom->createTextNode(''));

    // Build Items
    $items = $order->appendChild($dom->createElement('items'));
    $itemsQuery = "SELECT * FROM `se_orderitem` WHERE `yahooOrderIdNumeric` = " . $result['yahooOrderIdNumeric'];
    $itemsResult = $writeConnection->query($itemsQuery);
    $itemNumber = 1;
    foreach ($itemsResult as $itemResult) {
        $item = $items->appendChild($dom->createElement('item'));

	//Set variables
	$base_original_priceValue = $itemResult['unitPrice'];
	$base_priceValue = $itemResult['unitPrice'];
	$base_row_totalValue = $itemResult['qtyOrdered'] * $itemResult['unitPrice'];
	$real_nameValue = $itemResult['lineItemDescription'];
	$nameValue = $itemResult['lineItemDescription'];
	$original_priceValue = $itemResult['unitPrice'];
	$priceValue = $itemResult['unitPrice'];
	$qty_orderedValue = $itemResult['qtyOrdered'];
	$row_totalValue = $itemResult['qtyOrdered'] * $itemResult['unitPrice'];
	$length = strlen(end(explode('-', $itemResult['productCode'])));
	$real_skuValue = substr($itemResult['productCode'], 0, -($length + 1));
        
	fwrite($transactionLogHandle, "      ->ADDING CONFIGURABLE : " . $itemNumber . " -> " . $real_skuValue . "\n");

	$skuValue = 'Product ' . $itemNumber;
	if (!is_null($result['shipCountry']) && $result['shipState'] == 'CA') {
	    if (strtolower($result['shipCountry']) == 'united states') {
		$tax_percentCalcValue = '0.0875';
		$tax_percentValue = '8.75';
		$base_price_incl_taxValue = round($priceValue + ($priceValue * $tax_percentCalcValue), 4);//
		$base_row_total_incl_taxValue = round($qty_orderedValue * ($priceValue + ($priceValue * $tax_percentCalcValue)), 4);//
		$base_tax_amountValue = round($priceValue * $tax_percentCalcValue, 4);//THIS MAY BE WRONG -- QTY or ONE
		$price_incl_taxValue = round($priceValue + ($priceValue * $tax_percentCalcValue), 4);//
		$row_total_incl_taxValue = round($qty_orderedValue * ($priceValue + ($priceValue * $tax_percentCalcValue)), 4);//
		$tax_amountValue = round($priceValue * $tax_percentCalcValue, 4);//
	    } else {
		$tax_percentValue = '0.00';
		$base_price_incl_taxValue = $priceValue;
		$base_row_total_incl_taxValue = $qty_orderedValue * $priceValue;
		$base_tax_amountValue = '0.00';
		$price_incl_taxValue = $priceValue;
		$row_total_incl_taxValue = $qty_orderedValue * $priceValue;
		$tax_amountValue = '0.00';		
	    }
	} else {
	    $tax_percentValue = '0.00';
	    $base_price_incl_taxValue = $priceValue;
	    $base_row_total_incl_taxValue = $qty_orderedValue * $priceValue;
	    $base_tax_amountValue = '0.00';
	    $price_incl_taxValue = $priceValue;
	    $row_total_incl_taxValue = $qty_orderedValue * $priceValue;
	    $tax_amountValue = '0.00';	
	}

	//Create line item
	$amount_refunded = $item->appendChild($dom->createElement('amount_refunded'));
	$amount_refunded->appendChild($dom->createTextNode('0')); //Always 0
	$applied_rule_ids = $item->appendChild($dom->createElement('applied_rule_ids'));
	$applied_rule_ids->appendChild($dom->createTextNode(''));
	$base_amount_refunded = $item->appendChild($dom->createElement('base_amount_refunded'));
	$base_amount_refunded->appendChild($dom->createTextNode('0')); //Always 0
	$base_cost = $item->appendChild($dom->createElement('base_cost'));
	$base_cost->appendChild($dom->createTextNode(''));
	$base_discount_amount = $item->appendChild($dom->createElement('base_discount_amount'));
	$base_discount_amount->appendChild($dom->createTextNode('0')); //Always 0
	$base_discount_invoiced = $item->appendChild($dom->createElement('base_discount_invoiced'));
	$base_discount_invoiced->appendChild($dom->createTextNode('0')); //Always 0
	$base_hidden_tax_amount = $item->appendChild($dom->createElement('base_hidden_tax_amount'));
	$base_hidden_tax_amount->appendChild($dom->createTextNode(''));
	$base_hidden_tax_invoiced = $item->appendChild($dom->createElement('base_hidden_tax_invoiced'));
	$base_hidden_tax_invoiced->appendChild($dom->createTextNode(''));
	$base_hidden_tax_refunded = $item->appendChild($dom->createElement('base_hidden_tax_refunded'));
	$base_hidden_tax_refunded->appendChild($dom->createTextNode(''));
	$base_original_price = $item->appendChild($dom->createElement('base_original_price'));
	$base_original_price->appendChild($dom->createTextNode($base_original_priceValue));
	$base_price = $item->appendChild($dom->createElement('base_price'));
	$base_price->appendChild($dom->createTextNode($base_priceValue));
	$base_price_incl_tax = $item->appendChild($dom->createElement('base_price_incl_tax'));
	$base_price_incl_tax->appendChild($dom->createTextNode($base_price_incl_taxValue));
	$base_row_invoiced = $item->appendChild($dom->createElement('base_row_invoiced'));
	$base_row_invoiced->appendChild($dom->createTextNode('0'));
	$base_row_total = $item->appendChild($dom->createElement('base_row_total'));
	$base_row_total->appendChild($dom->createTextNode($base_row_totalValue));
	$base_row_total_incl_tax = $item->appendChild($dom->createElement('base_row_total_incl_tax'));
	$base_row_total_incl_tax->appendChild($dom->createTextNode($base_row_total_incl_taxValue));
	$base_tax_amount = $item->appendChild($dom->createElement('base_tax_amount'));
	$base_tax_amount->appendChild($dom->createTextNode($base_tax_amountValue));
	$base_tax_before_discount = $item->appendChild($dom->createElement('base_tax_before_discount'));
	$base_tax_before_discount->appendChild($dom->createTextNode(''));
	$base_tax_invoiced = $item->appendChild($dom->createElement('base_tax_invoiced'));
	$base_tax_invoiced->appendChild($dom->createTextNode('0')); //Always 0
	$base_weee_tax_applied_amount = $item->appendChild($dom->createElement('base_weee_tax_applied_amount'));
	$base_weee_tax_applied_amount->appendChild($dom->createTextNode('0')); //Always 0
	$base_weee_tax_applied_row_amount = $item->appendChild($dom->createElement('base_weee_tax_applied_row_amount'));
	$base_weee_tax_applied_row_amount->appendChild($dom->createTextNode('0')); //Always 0
	$base_weee_tax_disposition = $item->appendChild($dom->createElement('base_weee_tax_disposition'));
	$base_weee_tax_disposition->appendChild($dom->createTextNode('0')); //Always 0
	$base_weee_tax_row_disposition = $item->appendChild($dom->createElement('base_weee_tax_row_disposition'));
	$base_weee_tax_row_disposition->appendChild($dom->createTextNode('0')); //Always 0
	$description = $item->appendChild($dom->createElement('description'));
	$description->appendChild($dom->createTextNode(''));
	$discount_amount = $item->appendChild($dom->createElement('discount_amount'));
	$discount_amount->appendChild($dom->createTextNode('0')); //Always 0
	$discount_invoiced = $item->appendChild($dom->createElement('discount_invoiced'));
	$discount_invoiced->appendChild($dom->createTextNode('0')); //Always 0
	$discount_percent = $item->appendChild($dom->createElement('discount_percent'));
	$discount_percent->appendChild($dom->createTextNode('0')); //Always 0
	$free_shipping = $item->appendChild($dom->createElement('free_shipping'));
	$free_shipping->appendChild($dom->createTextNode('0')); //Always 0
	$hidden_tax_amount = $item->appendChild($dom->createElement('hidden_tax_amount'));
	$hidden_tax_amount->appendChild($dom->createTextNode(''));
	$hidden_tax_canceled = $item->appendChild($dom->createElement('hidden_tax_canceled'));
	$hidden_tax_canceled->appendChild($dom->createTextNode(''));
	$hidden_tax_invoiced = $item->appendChild($dom->createElement('hidden_tax_invoiced'));
	$hidden_tax_invoiced->appendChild($dom->createTextNode(''));
	$hidden_tax_refunded = $item->appendChild($dom->createElement('hidden_tax_refunded'));
	$hidden_tax_refunded->appendChild($dom->createTextNode(''));
	$is_nominal = $item->appendChild($dom->createElement('is_nominal'));
	$is_nominal->appendChild($dom->createTextNode('0')); //Always 0
	$is_qty_decimal = $item->appendChild($dom->createElement('is_qty_decimal'));
	$is_qty_decimal->appendChild($dom->createTextNode('0')); //Always 0
	$is_virtual = $item->appendChild($dom->createElement('is_virtual'));
	$is_virtual->appendChild($dom->createTextNode('0')); //Always 0
	$real_name = $item->appendChild($dom->createElement('real_name'));
	$real_name->appendChild($dom->createTextNode($real_nameValue)); //Always 0
	$name = $item->appendChild($dom->createElement('name'));
	$name->appendChild($dom->createTextNode($nameValue)); //Always 0
	$no_discount = $item->appendChild($dom->createElement('no_discount'));
	$no_discount->appendChild($dom->createTextNode('0')); //Always 0
	$original_price = $item->appendChild($dom->createElement('original_price'));
	$original_price->appendChild($dom->createTextNode($original_priceValue));
	$price = $item->appendChild($dom->createElement('price'));
	$price->appendChild($dom->createTextNode($priceValue));
	$price_incl_tax = $item->appendChild($dom->createElement('price_incl_tax'));
	$price_incl_tax->appendChild($dom->createTextNode($price_incl_taxValue));
	$qty_backordered = $item->appendChild($dom->createElement('qty_backordered'));
	$qty_backordered->appendChild($dom->createTextNode(''));
	$qty_canceled = $item->appendChild($dom->createElement('qty_canceled'));
	$qty_canceled->appendChild($dom->createTextNode('0')); //Always 0
	$qty_invoiced = $item->appendChild($dom->createElement('qty_invoiced'));
	$qty_invoiced->appendChild($dom->createTextNode('0')); //Always 0
	$qty_ordered = $item->appendChild($dom->createElement('qty_ordered'));
	$qty_ordered->appendChild($dom->createTextNode($qty_orderedValue)); //Always 0
	$qty_refunded = $item->appendChild($dom->createElement('qty_refunded'));
	$qty_refunded->appendChild($dom->createTextNode('0')); //Always 0
	$qty_shipped = $item->appendChild($dom->createElement('qty_shipped'));
	$qty_shipped->appendChild($dom->createTextNode('0')); //Always 0
	$row_invoiced = $item->appendChild($dom->createElement('row_invoiced'));
	$row_invoiced->appendChild($dom->createTextNode('0')); //Always 0
	$row_total = $item->appendChild($dom->createElement('row_total'));
	$row_total->appendChild($dom->createTextNode($row_totalValue));
	$row_total_incl_tax = $item->appendChild($dom->createElement('row_total_incl_tax'));
	$row_total_incl_tax->appendChild($dom->createTextNode($row_total_incl_taxValue));
	$row_weight = $item->appendChild($dom->createElement('row_weight'));
	$row_weight->appendChild($dom->createTextNode('0'));
	$real_sku = $item->appendChild($dom->createElement('real_sku'));
	$real_sku->appendChild($dom->createTextNode($real_skuValue));
	$sku = $item->appendChild($dom->createElement('sku'));
	$sku->appendChild($dom->createTextNode($skuValue));
	$tax_amount = $item->appendChild($dom->createElement('tax_amount'));
	$tax_amount->appendChild($dom->createTextNode($tax_amountValue));
	$tax_before_discount = $item->appendChild($dom->createElement('tax_before_discount'));
	$tax_before_discount->appendChild($dom->createTextNode(''));
	$tax_canceled = $item->appendChild($dom->createElement('tax_canceled'));
	$tax_canceled->appendChild($dom->createTextNode(''));
	$tax_invoiced = $item->appendChild($dom->createElement('tax_invoiced'));
	$tax_invoiced->appendChild($dom->createTextNode('0'));
	$tax_percent = $item->appendChild($dom->createElement('tax_percent'));
	$tax_percent->appendChild($dom->createTextNode($tax_percentValue));
	$tax_refunded = $item->appendChild($dom->createElement('tax_refunded'));
	$tax_refunded->appendChild($dom->createTextNode(''));
	$weee_tax_applied = $item->appendChild($dom->createElement('weee_tax_applied'));
	$weee_tax_applied->appendChild($dom->createTextNode('a:0:{}')); //Always 0
	$weee_tax_applied_amount = $item->appendChild($dom->createElement('weee_tax_applied_amount'));
	$weee_tax_applied_amount->appendChild($dom->createTextNode('0')); //Always 0
	$weee_tax_applied_row_amount = $item->appendChild($dom->createElement('weee_tax_applied_row_amount'));
	$weee_tax_applied_row_amount->appendChild($dom->createTextNode('0')); //Always 0
	$weee_tax_disposition = $item->appendChild($dom->createElement('weee_tax_disposition'));
	$weee_tax_disposition->appendChild($dom->createTextNode('0')); //Always 0
	$weee_tax_row_disposition = $item->appendChild($dom->createElement('weee_tax_row_disposition'));
	$weee_tax_row_disposition->appendChild($dom->createTextNode('0')); //Always 0
	$weight = $item->appendChild($dom->createElement('weight'));
	$weight->appendChild($dom->createTextNode('0'));

	//Add simple
	$item = $items->appendChild($dom->createElement('item'));
	
	//Set variables
	$base_original_priceValue = '0.0000';
	$base_priceValue = '0.0000';
	$base_row_totalValue = '0.0000';
	$real_nameValue = $itemResult['lineItemDescription'];
	$nameValue = $itemResult['lineItemDescription'];
	$original_priceValue = '0.0000';
	$priceValue = '0.0000';
	$qty_orderedValue = $itemResult['qtyOrdered'];
	$row_totalValue = '0.0000';
	$real_skuValue = $itemResult['productCode'];
	$skuValue = "Product " . $itemNumber . "-OFFLINE";
	$parent_skuValue = 'Product ' . $itemNumber;//Just for simple
	
	fwrite($transactionLogHandle, "      ->ADDING SIMPLE       : " . $itemNumber . " -> " . $real_skuValue . "\n");

	$tax_percentValue = '0.00';
	$base_price_incl_taxValue = '0.0000';
	$base_row_total_incl_taxValue = '0.0000';
	$base_tax_amountValue = '0.0000';
	$price_incl_taxValue = '0.0000';
	$row_total_incl_taxValue = '0.0000';
	$tax_amountValue = '0.0000';

	//Create line item
	$amount_refunded = $item->appendChild($dom->createElement('amount_refunded'));
	$amount_refunded->appendChild($dom->createTextNode('0')); //Always 0
	$applied_rule_ids = $item->appendChild($dom->createElement('applied_rule_ids'));
	$applied_rule_ids->appendChild($dom->createTextNode(''));
	$base_amount_refunded = $item->appendChild($dom->createElement('base_amount_refunded'));
	$base_amount_refunded->appendChild($dom->createTextNode('0')); //Always 0
	$base_cost = $item->appendChild($dom->createElement('base_cost'));
	$base_cost->appendChild($dom->createTextNode(''));
	$base_discount_amount = $item->appendChild($dom->createElement('base_discount_amount'));
	$base_discount_amount->appendChild($dom->createTextNode('0')); //Always 0
	$base_discount_invoiced = $item->appendChild($dom->createElement('base_discount_invoiced'));
	$base_discount_invoiced->appendChild($dom->createTextNode('0')); //Always 0
	$base_hidden_tax_amount = $item->appendChild($dom->createElement('base_hidden_tax_amount'));
	$base_hidden_tax_amount->appendChild($dom->createTextNode(''));
	$base_hidden_tax_invoiced = $item->appendChild($dom->createElement('base_hidden_tax_invoiced'));
	$base_hidden_tax_invoiced->appendChild($dom->createTextNode(''));
	$base_hidden_tax_refunded = $item->appendChild($dom->createElement('base_hidden_tax_refunded'));
	$base_hidden_tax_refunded->appendChild($dom->createTextNode(''));
	$base_original_price = $item->appendChild($dom->createElement('base_original_price'));
	$base_original_price->appendChild($dom->createTextNode($base_original_priceValue));
	$base_price = $item->appendChild($dom->createElement('base_price'));
	$base_price->appendChild($dom->createTextNode($base_priceValue));
	$base_price_incl_tax = $item->appendChild($dom->createElement('base_price_incl_tax'));
	$base_price_incl_tax->appendChild($dom->createTextNode($base_price_incl_taxValue));
	$base_row_invoiced = $item->appendChild($dom->createElement('base_row_invoiced'));
	$base_row_invoiced->appendChild($dom->createTextNode('0'));
	$base_row_total = $item->appendChild($dom->createElement('base_row_total'));
	$base_row_total->appendChild($dom->createTextNode($base_row_totalValue));
	$base_row_total_incl_tax = $item->appendChild($dom->createElement('base_row_total_incl_tax'));
	$base_row_total_incl_tax->appendChild($dom->createTextNode($base_row_total_incl_taxValue));
	$base_tax_amount = $item->appendChild($dom->createElement('base_tax_amount'));
	$base_tax_amount->appendChild($dom->createTextNode($base_tax_amountValue));
	$base_tax_before_discount = $item->appendChild($dom->createElement('base_tax_before_discount'));
	$base_tax_before_discount->appendChild($dom->createTextNode(''));
	$base_tax_invoiced = $item->appendChild($dom->createElement('base_tax_invoiced'));
	$base_tax_invoiced->appendChild($dom->createTextNode('0')); //Always 0
	$base_weee_tax_applied_amount = $item->appendChild($dom->createElement('base_weee_tax_applied_amount'));
	$base_weee_tax_applied_amount->appendChild($dom->createTextNode('0')); //Always 0
	$base_weee_tax_applied_row_amount = $item->appendChild($dom->createElement('base_weee_tax_applied_row_amount'));
	$base_weee_tax_applied_row_amount->appendChild($dom->createTextNode('0')); //Always 0
	$base_weee_tax_disposition = $item->appendChild($dom->createElement('base_weee_tax_disposition'));
	$base_weee_tax_disposition->appendChild($dom->createTextNode('0')); //Always 0
	$base_weee_tax_row_disposition = $item->appendChild($dom->createElement('base_weee_tax_row_disposition'));
	$base_weee_tax_row_disposition->appendChild($dom->createTextNode('0')); //Always 0
	$description = $item->appendChild($dom->createElement('description'));
	$description->appendChild($dom->createTextNode(''));
	$discount_amount = $item->appendChild($dom->createElement('discount_amount'));
	$discount_amount->appendChild($dom->createTextNode('0')); //Always 0
	$discount_invoiced = $item->appendChild($dom->createElement('discount_invoiced'));
	$discount_invoiced->appendChild($dom->createTextNode('0')); //Always 0
	$discount_percent = $item->appendChild($dom->createElement('discount_percent'));
	$discount_percent->appendChild($dom->createTextNode('0')); //Always 0
	$free_shipping = $item->appendChild($dom->createElement('free_shipping'));
	$free_shipping->appendChild($dom->createTextNode('0')); //Always 0
	$hidden_tax_amount = $item->appendChild($dom->createElement('hidden_tax_amount'));
	$hidden_tax_amount->appendChild($dom->createTextNode(''));
	$hidden_tax_canceled = $item->appendChild($dom->createElement('hidden_tax_canceled'));
	$hidden_tax_canceled->appendChild($dom->createTextNode(''));
	$hidden_tax_invoiced = $item->appendChild($dom->createElement('hidden_tax_invoiced'));
	$hidden_tax_invoiced->appendChild($dom->createTextNode(''));
	$hidden_tax_refunded = $item->appendChild($dom->createElement('hidden_tax_refunded'));
	$hidden_tax_refunded->appendChild($dom->createTextNode(''));
	$is_nominal = $item->appendChild($dom->createElement('is_nominal'));
	$is_nominal->appendChild($dom->createTextNode('0')); //Always 0
	$is_qty_decimal = $item->appendChild($dom->createElement('is_qty_decimal'));
	$is_qty_decimal->appendChild($dom->createTextNode('0')); //Always 0
	$is_virtual = $item->appendChild($dom->createElement('is_virtual'));
	$is_virtual->appendChild($dom->createTextNode('0')); //Always 0
	$real_name = $item->appendChild($dom->createElement('real_name'));
	$real_name->appendChild($dom->createTextNode($real_nameValue)); //Always 0
	$name = $item->appendChild($dom->createElement('nameValue'));
	$name->appendChild($dom->createTextNode($nameValue)); //Always 0
	$no_discount = $item->appendChild($dom->createElement('no_discount'));
	$no_discount->appendChild($dom->createTextNode('0')); //Always 0
	$original_price = $item->appendChild($dom->createElement('original_price'));
	$original_price->appendChild($dom->createTextNode($original_priceValue));
	$parent_sku = $item->appendChild($dom->createElement('parent_sku'));
	$parent_sku->appendChild($dom->createTextNode($parent_skuValue));
	$price = $item->appendChild($dom->createElement('price'));
	$price->appendChild($dom->createTextNode($priceValue));
	$price_incl_tax = $item->appendChild($dom->createElement('price_incl_tax'));
	$price_incl_tax->appendChild($dom->createTextNode($price_incl_taxValue));
	$qty_backordered = $item->appendChild($dom->createElement('qty_backordered'));
	$qty_backordered->appendChild($dom->createTextNode(''));
	$qty_canceled = $item->appendChild($dom->createElement('qty_canceled'));
	$qty_canceled->appendChild($dom->createTextNode('0')); //Always 0
	$qty_invoiced = $item->appendChild($dom->createElement('qty_invoiced'));
	$qty_invoiced->appendChild($dom->createTextNode('0')); //Always 0
	$qty_ordered = $item->appendChild($dom->createElement('qty_ordered'));
	$qty_ordered->appendChild($dom->createTextNode($qty_orderedValue)); //Always 0
	$qty_refunded = $item->appendChild($dom->createElement('qty_refunded'));
	$qty_refunded->appendChild($dom->createTextNode('0')); //Always 0
	$qty_shipped = $item->appendChild($dom->createElement('qty_shipped'));
	$qty_shipped->appendChild($dom->createTextNode('0')); //Always 0
	$row_invoiced = $item->appendChild($dom->createElement('row_invoiced'));
	$row_invoiced->appendChild($dom->createTextNode('0')); //Always 0
	$row_total = $item->appendChild($dom->createElement('row_total'));
	$row_total->appendChild($dom->createTextNode($row_totalValue));
	$row_total_incl_tax = $item->appendChild($dom->createElement('row_total_incl_tax'));
	$row_total_incl_tax->appendChild($dom->createTextNode($row_total_incl_taxValue));
	$row_weight = $item->appendChild($dom->createElement('row_weight'));
	$row_weight->appendChild($dom->createTextNode('0'));
	$real_sku = $item->appendChild($dom->createElement('real_sku'));
	$real_sku->appendChild($dom->createTextNode($real_skuValue));
	$sku = $item->appendChild($dom->createElement('sku'));
	$sku->appendChild($dom->createTextNode($skuValue));
	$tax_amount = $item->appendChild($dom->createElement('tax_amount'));
	$tax_amount->appendChild($dom->createTextNode($tax_amountValue));
	$tax_before_discount = $item->appendChild($dom->createElement('tax_before_discount'));
	$tax_before_discount->appendChild($dom->createTextNode(''));
	$tax_canceled = $item->appendChild($dom->createElement('tax_canceled'));
	$tax_canceled->appendChild($dom->createTextNode(''));
	$tax_invoiced = $item->appendChild($dom->createElement('tax_invoiced'));
	$tax_invoiced->appendChild($dom->createTextNode('0'));
	$tax_percent = $item->appendChild($dom->createElement('tax_percent'));
	$tax_percent->appendChild($dom->createTextNode($tax_percentValue));
	$tax_refunded = $item->appendChild($dom->createElement('tax_refunded'));
	$tax_refunded->appendChild($dom->createTextNode(''));
	$weee_tax_applied = $item->appendChild($dom->createElement('weee_tax_applied'));
	$weee_tax_applied->appendChild($dom->createTextNode('a:0:{}')); //Always 0
	$weee_tax_applied_amount = $item->appendChild($dom->createElement('weee_tax_applied_amount'));
	$weee_tax_applied_amount->appendChild($dom->createTextNode('0')); //Always 0
	$weee_tax_applied_row_amount = $item->appendChild($dom->createElement('weee_tax_applied_row_amount'));
	$weee_tax_applied_row_amount->appendChild($dom->createTextNode('0')); //Always 0
	$weee_tax_disposition = $item->appendChild($dom->createElement('weee_tax_disposition'));
	$weee_tax_disposition->appendChild($dom->createTextNode('0')); //Always 0
	$weee_tax_row_disposition = $item->appendChild($dom->createElement('weee_tax_row_disposition'));
	$weee_tax_row_disposition->appendChild($dom->createTextNode('0')); //Always 0
	$weight = $item->appendChild($dom->createElement('weight'));
	$weight->appendChild($dom->createTextNode('0'));
	
        $itemNumber++;
    }
}

// Make the output pretty
$dom->formatOutput = true;

// Save the XML string
$xml = $dom->saveXML();

//Write file to post directory
$handle = fopen($toolsXmlDirectory . $orderFilename, 'w');
fwrite($handle, $xml);
fclose($handle);

fwrite($transactionLogHandle, "  ->FINISH TIME   : " . $endTime[5] . '/' . $endTime[4] . '/' . $endTime[3] . ' ' . $endTime[2] . ':' . $endTime[1] . ':' . $endTime[0] . "\n");
fwrite($transactionLogHandle, "  ->CREATED       : ORDER FILE 8 " . $orderFilename . "\n");

fwrite($transactionLogHandle, "->END PROCESSING\n");
//Close transaction log
fclose($transactionLogHandle);

//FILE 9
$realTime = realTime();
//Open transaction log
$transactionLogHandle = fopen($toolsLogsDirectory . 'migration_gen_sneakerhead_order_xml_files_transaction_log', 'a+');
fwrite($transactionLogHandle, "->BEGIN PROCESSING\n");
fwrite($transactionLogHandle, "  ->START TIME    : " . $realTime[5] . '/' . $realTime[4] . '/' . $realTime[3] . ' ' . $realTime[2] . ':' . $realTime[1] . ':' . $realTime[0] . "\n");

//ORDERS
fwrite($transactionLogHandle, "  ->GETTING ORDERS\n");

$resource = Mage::getSingleton('core/resource');
$writeConnection = $resource->getConnection('core_write');
//$startDate = '2009-10-00 00:00:00';//>=
$startDate = '2010-03-15 00:00:00';//>=
$endDate = '2010-03-15 00:00:00';//<
//10,000
//$query = "SELECT `users`.`email` AS `user_email`, `users`.`firstName` AS `user_firstname`, `users`.`lastName` AS `user_lastname`, `se_order`.* FROM `se_order` LEFT JOIN `storeorder` ON `se_order`.`yahooOrderIdNumeric` = `storeorder`.`yahooOrderIdNumeric` LEFT JOIN `users` ON `storeorder`.`uid` = `users`.`uid` WHERE `se_order`.`orderCreationDate` > '2009-10-00 00:00:00' AND `se_order`.`orderCreationDate` < '2009-12-14 00:00:00'";
//25,000
//FOLLOWING 8 QUERIES TO BE RUN SEPARATELY TO GENERATE 8 DIFFERENT FILES
//$query = "SELECT `users`.`email` AS `user_email`, `users`.`firstName` AS `user_firstname`, `users`.`lastName` AS `user_lastname`, `se_order`.* FROM `se_order` LEFT JOIN `storeorder` ON `se_order`.`yahooOrderIdNumeric` = `storeorder`.`yahooOrderIdNumeric` LEFT JOIN `users` ON `storeorder`.`uid` = `users`.`uid` WHERE `se_order`.`orderCreationDate` >= '2009-10-00 00:00:00' AND `se_order`.`orderCreationDate` < '2010-03-15 00:00:00'";//191298 -> 216253
//$query = "SELECT `users`.`email` AS `user_email`, `users`.`firstName` AS `user_firstname`, `users`.`lastName` AS `user_lastname`, `se_order`.* FROM `se_order` LEFT JOIN `storeorder` ON `se_order`.`yahooOrderIdNumeric` = `storeorder`.`yahooOrderIdNumeric` LEFT JOIN `users` ON `storeorder`.`uid` = `users`.`uid` WHERE `se_order`.`orderCreationDate` >= '2010-03-15 00:00:00' AND `se_order`.`orderCreationDate` < '2010-10-28 00:00:00'";//216254 -> 241203
//$query = "SELECT `users`.`email` AS `user_email`, `users`.`firstName` AS `user_firstname`, `users`.`lastName` AS `user_lastname`, `se_order`.* FROM `se_order` LEFT JOIN `storeorder` ON `se_order`.`yahooOrderIdNumeric` = `storeorder`.`yahooOrderIdNumeric` LEFT JOIN `users` ON `storeorder`.`uid` = `users`.`uid` WHERE `se_order`.`orderCreationDate` >= '2010-10-28 00:00:00' AND `se_order`.`orderCreationDate` < '2011-02-27 00:00:00'";//241204 -> 266066
//$query = "SELECT `users`.`email` AS `user_email`, `users`.`firstName` AS `user_firstname`, `users`.`lastName` AS `user_lastname`, `se_order`.* FROM `se_order` LEFT JOIN `storeorder` ON `se_order`.`yahooOrderIdNumeric` = `storeorder`.`yahooOrderIdNumeric` LEFT JOIN `users` ON `storeorder`.`uid` = `users`.`uid` WHERE `se_order`.`orderCreationDate` >= '2011-02-27 00:00:00' AND `se_order`.`orderCreationDate` < '2011-06-27 00:00:00'";//266067 -> 291019
//$query = "SELECT `users`.`email` AS `user_email`, `users`.`firstName` AS `user_firstname`, `users`.`lastName` AS `user_lastname`, `se_order`.* FROM `se_order` LEFT JOIN `storeorder` ON `se_order`.`yahooOrderIdNumeric` = `storeorder`.`yahooOrderIdNumeric` LEFT JOIN `users` ON `storeorder`.`uid` = `users`.`uid` WHERE `se_order`.`orderCreationDate` >= '2011-06-27 00:00:00' AND `se_order`.`orderCreationDate` < '2011-12-09 00:00:00'";//291020 -> 315244
//$query = "SELECT `users`.`email` AS `user_email`, `users`.`firstName` AS `user_firstname`, `users`.`lastName` AS `user_lastname`, `se_order`.* FROM `se_order` LEFT JOIN `storeorder` ON `se_order`.`yahooOrderIdNumeric` = `storeorder`.`yahooOrderIdNumeric` LEFT JOIN `users` ON `storeorder`.`uid` = `users`.`uid` WHERE `se_order`.`orderCreationDate` >= '2011-12-09 00:00:00' AND `se_order`.`orderCreationDate` < '2012-04-24 00:00:00'";//315245 -> 340092
//$query = "SELECT `users`.`email` AS `user_email`, `users`.`firstName` AS `user_firstname`, `users`.`lastName` AS `user_lastname`, `se_order`.* FROM `se_order` LEFT JOIN `storeorder` ON `se_order`.`yahooOrderIdNumeric` = `storeorder`.`yahooOrderIdNumeric` LEFT JOIN `users` ON `storeorder`.`uid` = `users`.`uid` WHERE `se_order`.`orderCreationDate` >= '2012-04-24 00:00:00' AND `se_order`.`orderCreationDate` < '2012-10-04 00:00:00'";//340093 -> 364330
//$query = "SELECT `users`.`email` AS `user_email`, `users`.`firstName` AS `user_firstname`, `users`.`lastName` AS `user_lastname`, `se_order`.* FROM `se_order` LEFT JOIN `storeorder` ON `se_order`.`yahooOrderIdNumeric` = `storeorder`.`yahooOrderIdNumeric` LEFT JOIN `users` ON `storeorder`.`uid` = `users`.`uid` WHERE `se_order`.`orderCreationDate` >= '2012-10-04 00:00:00' AND `se_order`.`orderCreationDate` < '2013-02-16 00:00:00'";//364331 -> ???
$query = "SELECT `users`.`email` AS `user_email`, `users`.`firstName` AS `user_firstname`, `users`.`lastName` AS `user_lastname`, `se_order`.* FROM `se_order` LEFT JOIN `storeorder` ON `se_order`.`yahooOrderIdNumeric` = `storeorder`.`yahooOrderIdNumeric` LEFT JOIN `users` ON `storeorder`.`uid` = `users`.`uid` WHERE `se_order`.`orderCreationDate` >= '2013-02-16 00:00:00' AND `se_order`.`orderCreationDate` < '2013-04-23 00:00:00'";//??? -> ???
//$query = "SELECT `users`.`email` AS `user_email`, `users`.`firstName` AS `user_firstname`, `users`.`lastName` AS `user_lastname`, `se_order`.* FROM `se_order` LEFT JOIN `storeorder` ON `se_order`.`yahooOrderIdNumeric` = `storeorder`.`yahooOrderIdNumeric` LEFT JOIN `users` ON `storeorder`.`uid` = `users`.`uid` WHERE `se_order`.`orderCreationDate` >= '2013-04-23 00:00:00'"; //??? -> current (???)

$results = $writeConnection->query($query);
$orderFilename = "order_9_" . $realTime[5] . $realTime[4] . $realTime[3] . '_' . $realTime[2] . $realTime[1] . $realTime[0] . ".xml";
//Creates XML string and XML document from the DOM representation
$dom = new DomDocument('1.0');

$orders = $dom->appendChild($dom->createElement('orders'));
foreach ($results as $result) {
    
    //Add order data
    fwrite($transactionLogHandle, "    ->ADDING ORDER NUMBER   : " . $result['yahooOrderIdNumeric'] . "\n");
    
    // Set some variables
    $base_discount_amountValue = (is_null($result['discount'])) ? '0.0000' : $result['discount'];//appears to be actual total discount
    $base_grand_totalValue = (is_null($result['finalGrandTotal'])) ? '0.0000' : $result['finalGrandTotal'];
    $base_shipping_amountValue = (is_null($result['shippingCost'])) ? '0.0000' : $result['shippingCost'];
    $base_shipping_incl_taxValue = (is_null($result['shippingCost'])) ? '0.0000' : $result['shippingCost'];
    $base_subtotalValue = (is_null($result['finalSubTotal'])) ? '0.0000' : $result['finalSubTotal'];
    $base_subtotal_incl_taxValue = (is_null($result['finalSubTotal'])) ? '0.0000' : $result['finalSubTotal'];
    $base_tax_amountValue = (is_null($result['taxTotal'])) ? '0.0000' : $result['taxTotal'];

    if (!is_null($result['shipCountry']) && $result['shipState'] == 'CA') {
	if (strtolower($result['shipCountry']) == 'united states') {
	    $tax_percentValue = '8.75';
	} else {
	    $tax_percentValue = '0.00';
	}
    } else {
	$tax_percentValue = '0.00';
    }

    $base_total_dueValue = (is_null($result['finalGrandTotal'])) ? '0.0000' : $result['finalGrandTotal'];
    $real_created_atValue = (is_null($result['orderCreationDate'])) ? date("Y-m-d H:i:s") : $result['orderCreationDate'];//current date or order creation date
    $created_at_timestampValue = strtotime($real_created_atValue);//set from created date
    $customer_emailValue = (is_null($result['user_email'])) ? (is_null($result['email'])) ? '' : $result['email'] : $result['user_email'];
    $customer_firstnameValue = (is_null($result['user_firstname'])) ? (is_null($result['firstName'])) ? '' : $result['firstName'] : $result['user_firstname'];
    $customer_lastnameValue = (is_null($result['user_lastname'])) ? (is_null($result['lastName'])) ? '' : $result['lastName'] : $result['user_lastname'];
    if (is_null($result['user_firstname'])) {
	$customer_nameValue = '';
    } else {
	$customer_nameValue = $customer_firstnameValue . ' ' . $customer_lastnameValue;
    }
    $customer_nameValue = $customer_firstnameValue . ' ' . $customer_lastnameValue;
    //Lookup customer
    if ($result['user_email'] == NULL) {
	$customer_group_idValue = 0;
    } else {
	$customerQuery = "SELECT `entity_id` FROM `customer_entity` WHERE `email` = '" . $result['user_email'] . "'";
	$customerResults = $writeConnection->query($customerQuery);
	$customerFound = NULL;
	foreach ($customerResults as $customerResult) {  
	    $customerFound = 1;
	}
	if (!$customerFound) {
	    fwrite($transactionLogHandle, "    ->CUSTOMER NOT FOUND    : " . $result['yahooOrderIdNumeric'] . "\n");	    
	    $customer_group_idValue = 0;
	} else {
	    $customer_group_idValue = 1;
	}
    }
    
    $discount_amountValue = (is_null($result['discount'])) ? '0.0000' : $result['discount'];//appears to be actual total discount
    $grand_totalValue = (is_null($result['finalGrandTotal'])) ? '0.0000' : $result['finalGrandTotal'];
    $increment_idValue = $result['yahooOrderIdNumeric'];//import script adds value to 600000000
    $shipping_amountValue = (is_null($result['shippingCost'])) ? '0.0000' : $result['shippingCost'];
    $shipping_incl_taxValue = (is_null($result['shippingCost'])) ? '0.0000' : $result['shippingCost'];
    switch ($result['shippingMethod']) {
	case 'UPS Ground (3-7 Business Days)':
	    $shipping_methodValue = 'ups_03';
	    break;
	case 'APO & FPO Addresses (5-30 Business Days by USPS)':
	    $shipping_methodValue = 'usps_Priority Mail';
	    break;
	case 'UPS Next Day Air (2-3 Business Days)':
	    $shipping_methodValue = 'ups_01';
	    break;
	case '"Alaska, Hawaii, U.S. Virgin Islands & Puerto Rico':
	    $shipping_methodValue = 'usps_Priority Mail';
	    break;
	case 'UPS 2nd Day Air (3-4 Business Days)':
	    $shipping_methodValue = 'ups_02';
	    break;
	case 'International Express (Shipped with Shoebox)':
	    $shipping_methodValue = 'ups_08';
	    break;
	case 'International Express (Shipped without Shoebox)':
	    $shipping_methodValue = 'ups_08';
	    break;
	case 'USPS Priority Mail (4-5 Business Days)':
	    $shipping_methodValue = 'usps_Priority Mail';
	    break;
	case 'UPS 3 Day Select (4-5 Business Days)':
	    $shipping_methodValue = 'ups_12';
	    break;
	case 'EMS - International':
	    $shipping_methodValue = 'ups_08';
	    break;
	case 'Canada Express (4-7 Business Days)':
	    $shipping_methodValue = 'ups_08';
	    break;
	case 'EMS Canada':
	    $shipping_methodValue = 'ups_08';
	    break;
	case 'Christmas Express (Delivered by Dec. 24th)':
	    $shipping_methodValue = 'ups_03';
	    break;
	case 'COD Ground':
	    $shipping_methodValue = 'ups_03';
	    break;
	case 'COD Overnight':
	    $shipping_methodValue = 'ups_01';
	    break;
	case 'Free Christmas Express (Delivered by Dec. 24th)':
	    $shipping_methodValue = 'ups_03';
	    break;
	case 'New Year Express (Delivered by Dec. 31st)':
	    $shipping_methodValue = 'ups_03';
	    break;
	case 'Free UPS Ground (3-7 Business Days)':
	    $shipping_methodValue = 'ups_03';
	    break;
	case 'COD 2-Day':
	    $shipping_methodValue = 'ups_02';
	    break;
	case 'MSI International Express':
	    $shipping_methodValue = 'ups_08';
	    break;
	case 'Customer Pickup':
	    $shipping_methodValue = 'ups_03';
	    break;
	case 'UPS Ground':
	    $shipping_methodValue = 'ups_03';
	    break;
	case 'UPS 2nd Day Air':
	    $shipping_methodValue = 'ups_02';
	    break;
	case 'APO & FPO Addresses':
	    $shipping_methodValue = 'usps_Priority Mail';
	    break;
	case 'UPS Next Day Air Saver':
	    $shipping_methodValue = 'ups_01';
	    break;
	case 'UPS 3 Day Select':
	    $shipping_methodValue = 'ups_12';
	    break;
	case 'International Express':
	    $shipping_methodValue = 'ups_08';
	    break;
	case 'USPS Priority Mail':
	    $shipping_methodValue = 'usps_Priority Mail';
	    break;
	case 'Canada Express':
	    $shipping_methodValue = 'ups_08';
	    break;
	case 'UPS Next Day Air':
	    $shipping_methodValue = 'ups_01';
	    break;
	case 'Holiday Shipping (Delivered by Dec. 24th)':
	    $shipping_methodValue = 'ups_03';
	    break;
	default://case 'NULL'
	    $shipping_methodValue = '';
	    break;
    }
    
    $stateValue = 'new';//Always new -- will set on order status update
    $statusValue = 'pending';//Always Pending -- will set on order status update
    $subtotalValue = (is_null($result['finalSubTotal'])) ? '0.0000' : $result['finalSubTotal'];
    $subtotal_incl_taxValue = (is_null($result['finalSubTotal'])) ? '0.0000' : $result['finalSubTotal'];
    $tax_amountValue = (is_null($result['taxTotal'])) ? '0.0000' : $result['taxTotal'];
    $total_dueValue = (is_null($result['finalGrandTotal'])) ? '0.0000' : $result['finalGrandTotal'];

    // Get total qty
    $itemsQuery = "SELECT * FROM `se_orderitem` WHERE `yahooOrderIdNumeric` = " . $result['yahooOrderIdNumeric'];
    $itemsResult = $writeConnection->query($itemsQuery);
    $itemCount = 0;
    foreach ($itemsResult as $itemResult) {
	$itemCount += 1;//number of items not quantites
    }
    if ($itemCount == 0) {
	fwrite($transactionLogHandle, "      ->NO ITEMS FOUND      : " . $result['yahooOrderIdNumeric'] . "\n");
    }
    $total_qty_orderedValue = $itemCount . '.0000';//Derived from item qty count
    $updated_atValue = date("Y-m-d H:i:s");
    $updated_at_timestampValue = strtotime($real_created_atValue);
    $weightValue = '0.0000'; //No weight data available

    //Shipping
    $shippingCityValue = (is_null($result['shipCity'])) ? '' : $result['shipCity'];
    $shippingCountryValue = (is_null($result['shipCountry'])) ? '' : $result['shipCountry'];
    $shippingEmailValue = (is_null($result['email'])) ? '' : $result['email'];
    $shippingFirstnameValue = (is_null($result['shipName'])) ? '' : $result['shipName'];
    $shippingLastnameValue = '';
    $shippingNameValue = $result['shipName'];
    $shippingPostcodeValue = (is_null($result['shipZip'])) ? '' : $result['shipZip'];
    if (strtolower($shippingCountryValue) == 'united states') {
	$shippingRegionValue = (is_null($result['shipState'])) ? '' : strtoupper($result['shipState']);
    } else {
	$shippingRegionValue = (is_null($result['shipState'])) ? '' : $result['shipState'];
    }
    $shippingRegion_idValue = '';//Seems to work without conversion
    if ((!is_null($result['shipAddress1']) && $result['shipAddress1'] != '') && (is_null($result['shipAddress2']) || $result['shipAddress2'] == '')) {
	$shippingStreetValue = $result['shipAddress1'];
    } elseif ((!is_null($result['shipAddress1']) && $result['shipAddress1'] != '') && (!is_null($result['shipAddress2']) && $result['shipAddress2'] != '')) {
	$shippingStreetValue = $result['shipAddress2'] . '&#10;' . $result['shipAddress2']; //Include CR/LF
    } elseif ((is_null($result['shipAddress1']) || $result['shipAddress1'] == '') && (!is_null($result['shipAddress2']) && $result['shipAddress2'] != '')) {
	$shippingStreetValue = $result['shipAddress2'];
    } else {
	$shippingStreetValue = '';
    }
    $shippingTelephoneValue = (is_null($result['shipPhone'])) ? '' : $result['shipPhone'];
    
    //Billing
    $billingCityValue = (is_null($result['billCity'])) ? '' : $result['billCity'];
    $billingCountryValue = (is_null($result['billCountry'])) ? '' : $result['billCountry'];
    $billingEmailValue = (is_null($result['email'])) ? '' : $result['email'];
    $billingFirstnameValue = (is_null($result['billName'])) ? '' : $result['billName'];
    $billingLastnameValue = '';
    $billingNameValue = $result['billName'];
    $billingPostcodeValue = (is_null($result['billZip'])) ? '' : $result['billZip'];
    if (strtolower($billingCountryValue) == 'united states') {
	$billingRegionValue = (is_null($result['billState'])) ? '' : strtoupper($result['billState']);
    } else {
	$billingRegionValue = (is_null($result['billState'])) ? '' : $result['billState'];
    }
    $billingRegion_idValue = '';//Seems to work without conversion
    if ((!is_null($result['billAddress1']) && $result['billAddress1'] != '') && (is_null($result['billAddress2']) || $result['billAddress2'] == '')) {
	$billingStreetValue = $result['billAddress1'];
    } elseif ((!is_null($result['billAddress1']) && $result['billAddress1'] != '') && (!is_null($result['billAddress2']) && $result['billAddress2'] != '')) {
	$billingStreetValue = $result['billAddress2'] . '&#10;' . $result['billAddress2']; //Include CR/LF
    } elseif ((is_null($result['billAddress1']) || $result['billAddress1'] == '') && (!is_null($result['billAddress2']) && $result['billAddress2'] != '')) {
	$billingStreetValue = $result['billAddress2'];
    } else {
	$billingStreetValue = '';
    }
    $billingTelephoneValue = (is_null($result['billPhone'])) ? '' : $result['billPhone'];
    
    //Payment
    switch ($result['paymentType']) {
	case 'Visa':
	    $cc_typeValue = 'VI';
            $methodValue = 'authorizenet';
	    break;
	case 'AMEX':
	    $cc_typeValue = 'AE';
            $methodValue = 'authorizenet';
            break;
	case 'Mastercard':
	    $cc_typeValue = 'MC';
            $methodValue = 'authorizenet';
            break;
	case 'Discover':
	    $cc_typeValue = 'DI';
            $methodValue = 'authorizenet';
	    break;
	case 'Paypal':
	    $cc_typeValue = '';
            $methodValue = 'paypal_express';
	    break;
	case 'C.O.D.':
	    $cc_typeValue = '';
            $methodValue = 'free';
	    break;
	case 'GiftCert':
	    //100% payed with giftcard
	    $cc_typeValue = '';
            $methodValue = 'free';
	    break;
	default: //NULL
	    $cc_typeValue = '';
	    $methodValue = 'free';
    }
    $amount_authorizedValue = (is_null($result['finalGrandTotal'])) ? '' : $result['finalGrandTotal'];
    $amount_orderedValue = (is_null($result['finalGrandTotal'])) ? '' : $result['finalGrandTotal'];
    $base_amount_authorizedValue = (is_null($result['finalGrandTotal'])) ? '' : $result['finalGrandTotal'];
    $base_amount_orderedValue = (is_null($result['finalGrandTotal'])) ? '' : $result['finalGrandTotal'];
    $base_shipping_amountValue = (is_null($result['shippingCost'])) ? '' : $result['shippingCost'];
    $cc_approvalValue = (is_null($result['ccApprovalNumber'])) ? '' : $result['ccApprovalNumber'];
    $cc_cid_statusValue = (is_null($result['ccCvvResponse'])) ? '' : $result['ccCvvResponse'];
    $ccExpiration = (is_null($result['ccExpiration'])) ? '' : explode('/', $result['ccExpiration']);
    if (is_null($ccExpiration)) {
        $cc_exp_monthValue = '';
        $cc_exp_yearValue = '';
    } else {
        $cc_exp_monthValue = $ccExpiration[0];
        $cc_exp_yearValue = $ccExpiration[1];
    }
    $cc_last4Value = (is_null($result['ccExpiration'])) ? '' : '****';//data not available
    $anet_trans_methodValue = '';//***
    $cc_avs_statusValue = '';//***
    $cc_trans_idValue = '';//***
    $last_trans_idValue = '';//***
    $shipping_amountValue = (is_null($result['shippingCost'])) ? '' : $result['shippingCost'];

    $order = $orders->appendChild($dom->createElement('order'));

    $adjustment_negative = $order->appendChild($dom->createElement('adjustment_negative'));
    $adjustment_negative->appendChild($dom->createTextNode(''));
    $adjustment_positive = $order->appendChild($dom->createElement('adjustment_positive'));
    $adjustment_positive->appendChild($dom->createTextNode(''));
    $applied_rule_ids = $order->appendChild($dom->createElement('applied_rule_ids'));
    $applied_rule_ids->appendChild($dom->createTextNode(''));//none used -- only used for military until migration complete
    $base_adjustment_negative = $order->appendChild($dom->createElement('base_adjustment_negative'));
    $base_adjustment_negative->appendChild($dom->createTextNode(''));
    $base_adjustment_positive = $order->appendChild($dom->createElement('base_adjustment_positive'));
    $base_adjustment_positive->appendChild($dom->createTextNode(''));
    $base_currency_code = $order->appendChild($dom->createElement('base_currency_code'));
    $base_currency_code->appendChild($dom->createTextNode('USD'));// Always USD
    $base_custbalance_amount = $order->appendChild($dom->createElement('base_custbalance_amount'));
    $base_custbalance_amount->appendChild($dom->createTextNode(''));
    $base_discount_amount = $order->appendChild($dom->createElement('base_discount_amount'));
    $base_discount_amount->appendChild($dom->createTextNode($base_discount_amountValue));
    $base_discount_canceled = $order->appendChild($dom->createElement('base_discount_canceled'));
    $base_discount_canceled->appendChild($dom->createTextNode(''));
    $base_discount_invoiced = $order->appendChild($dom->createElement('base_discount_invoiced'));
    $base_discount_invoiced->appendChild($dom->createTextNode(''));
    $base_discount_refunded = $order->appendChild($dom->createElement('base_discount_refunded'));
    $base_discount_refunded->appendChild($dom->createTextNode(''));
    $base_grand_total = $order->appendChild($dom->createElement('base_grand_total'));
    $base_grand_total->appendChild($dom->createTextNode($base_grand_totalValue));
    $base_hidden_tax_amount = $order->appendChild($dom->createElement('base_hidden_tax_amount'));
    $base_hidden_tax_amount->appendChild($dom->createTextNode(''));
    $base_hidden_tax_invoiced = $order->appendChild($dom->createElement('base_hidden_tax_invoiced'));
    $base_hidden_tax_invoiced->appendChild($dom->createTextNode(''));
    $base_hidden_tax_refunded = $order->appendChild($dom->createElement('base_hidden_tax_refunded'));
    $base_hidden_tax_refunded->appendChild($dom->createTextNode(''));
    $base_shipping_amount = $order->appendChild($dom->createElement('base_shipping_amount'));
    $base_shipping_amount->appendChild($dom->createTextNode($base_shipping_amountValue));
    $base_shipping_canceled = $order->appendChild($dom->createElement('base_shipping_canceled'));
    $base_shipping_canceled->appendChild($dom->createTextNode(''));
    $base_shipping_discount_amount = $order->appendChild($dom->createElement('base_shipping_discount_amount'));
    $base_shipping_discount_amount->appendChild($dom->createTextNode('0.0000'));//Always 0
    $base_shipping_hidden_tax_amount = $order->appendChild($dom->createElement('base_shipping_hidden_tax_amount'));
    $base_shipping_hidden_tax_amount->appendChild($dom->createTextNode(''));
    $base_shipping_incl_tax = $order->appendChild($dom->createElement('base_shipping_incl_tax'));
    $base_shipping_incl_tax->appendChild($dom->createTextNode($base_shipping_incl_taxValue));
    $base_shipping_invoiced = $order->appendChild($dom->createElement('base_shipping_invoiced'));
    $base_shipping_invoiced->appendChild($dom->createTextNode(''));
    $base_shipping_refunded = $order->appendChild($dom->createElement('base_shipping_refunded'));
    $base_shipping_refunded->appendChild($dom->createTextNode(''));
    $base_shipping_tax_amount = $order->appendChild($dom->createElement('base_shipping_tax_amount'));
    $base_shipping_tax_amount->appendChild($dom->createTextNode('0.0000'));//Always 0
    $base_shipping_tax_refunded = $order->appendChild($dom->createElement('base_shipping_tax_refunded'));
    $base_shipping_tax_refunded->appendChild($dom->createTextNode(''));
    $base_subtotal = $order->appendChild($dom->createElement('base_subtotal'));
    $base_subtotal->appendChild($dom->createTextNode($base_subtotalValue));
    $base_subtotal_canceled = $order->appendChild($dom->createElement('base_subtotal_canceled'));
    $base_subtotal_canceled->appendChild($dom->createTextNode(''));
    $base_subtotal_incl_tax = $order->appendChild($dom->createElement('base_subtotal_incl_tax'));
    $base_subtotal_incl_tax->appendChild($dom->createTextNode($base_subtotal_incl_taxValue));
    $base_subtotal_invoiced = $order->appendChild($dom->createElement('base_subtotal_invoiced'));
    $base_subtotal_invoiced->appendChild($dom->createTextNode(''));
    $base_subtotal_refunded = $order->appendChild($dom->createElement('base_subtotal_refunded'));
    $base_subtotal_refunded->appendChild($dom->createTextNode(''));
    $base_tax_amount = $order->appendChild($dom->createElement('base_tax_amount'));
    $base_tax_amount->appendChild($dom->createTextNode($base_tax_amountValue));
    $base_tax_canceled = $order->appendChild($dom->createElement('base_tax_canceled'));
    $base_tax_canceled->appendChild($dom->createTextNode(''));
    $base_tax_invoiced = $order->appendChild($dom->createElement('base_tax_invoiced'));
    $base_tax_invoiced->appendChild($dom->createTextNode(''));
    $base_tax_refunded = $order->appendChild($dom->createElement('base_tax_refunded'));
    $base_tax_refunded->appendChild($dom->createTextNode(''));
    $base_to_global_rate = $order->appendChild($dom->createElement('base_to_global_rate'));
    $base_to_global_rate->appendChild($dom->createTextNode('1'));//Always 1
    $base_to_order_rate = $order->appendChild($dom->createElement('base_to_order_rate'));
    $base_to_order_rate->appendChild($dom->createTextNode('1'));//Always 1
    $base_total_canceled = $order->appendChild($dom->createElement('base_total_canceled'));
    $base_total_canceled->appendChild($dom->createTextNode('0.0000'));
    $base_total_due = $order->appendChild($dom->createElement('base_total_due'));
    $base_total_due->appendChild($dom->createTextNode($base_total_dueValue));
    $base_total_invoiced = $order->appendChild($dom->createElement('base_total_invoiced'));
    $base_total_invoiced->appendChild($dom->createTextNode('0.0000'));
    $base_total_invoiced_cost = $order->appendChild($dom->createElement('base_total_invoiced_cost'));
    $base_total_invoiced_cost->appendChild($dom->createTextNode(''));
    $base_total_offline_refunded = $order->appendChild($dom->createElement('base_total_offline_refunded'));
    $base_total_offline_refunded->appendChild($dom->createTextNode('0.0000'));
    $base_total_online_refunded = $order->appendChild($dom->createElement('base_total_online_refunded'));
    $base_total_online_refunded->appendChild($dom->createTextNode('0.0000'));
    $base_total_paid = $order->appendChild($dom->createElement('base_total_paid'));
    $base_total_paid->appendChild($dom->createTextNode('0.0000'));
    $base_total_qty_ordered = $order->appendChild($dom->createElement('base_total_qty_ordered'));
    $base_total_qty_ordered->appendChild($dom->createTextNode(''));//Always NULL
    $base_total_refunded = $order->appendChild($dom->createElement('base_total_refunded'));
    $base_total_refunded->appendChild($dom->createTextNode('0.0000'));
    $can_ship_partially = $order->appendChild($dom->createElement('can_ship_partially'));
    $can_ship_partially->appendChild($dom->createTextNode(''));
    $can_ship_partially_item = $order->appendChild($dom->createElement('can_ship_partially_item'));
    $can_ship_partially_item->appendChild($dom->createTextNode(''));
    $coupon_code = $order->appendChild($dom->createElement('coupon_code'));
    $coupon_code->appendChild($dom->createTextNode(''));
    $real_created_at = $order->appendChild($dom->createElement('real_created_at'));
    $real_created_at->appendChild($dom->createTextNode($real_created_atValue));
    $created_at_timestamp = $order->appendChild($dom->createElement('created_at_timestamp'));
    $created_at_timestamp->appendChild($dom->createTextNode($created_at_timestampValue));
    $custbalance_amount = $order->appendChild($dom->createElement('custbalance_amount'));
    $custbalance_amount->appendChild($dom->createTextNode(''));
    $customer_dob = $order->appendChild($dom->createElement('customer_dob'));
    $customer_dob->appendChild($dom->createTextNode(''));
    $customer_email = $order->appendChild($dom->createElement('customer_email'));
    $customer_email->appendChild($dom->createTextNode($customer_emailValue));
    $customer_firstname = $order->appendChild($dom->createElement('customer_firstname'));
    $customer_firstname->appendChild($dom->createTextNode($customer_firstnameValue));
    $customer_gender = $order->appendChild($dom->createElement('customer_gender'));
    $customer_gender->appendChild($dom->createTextNode(''));
    $customer_group_id = $order->appendChild($dom->createElement('customer_group_id'));
    $customer_group_id->appendChild($dom->createTextNode($customer_group_idValue));
    $customer_lastname = $order->appendChild($dom->createElement('customer_lastname'));
    $customer_lastname->appendChild($dom->createTextNode($customer_lastnameValue));
    $customer_middlename = $order->appendChild($dom->createElement('customer_middlename'));
    $customer_middlename->appendChild($dom->createTextNode(''));
    $customer_name = $order->appendChild($dom->createElement('customer_name'));
    $customer_name->appendChild($dom->createTextNode($customer_nameValue));
    $customer_note = $order->appendChild($dom->createElement('customer_note'));
    $customer_note->appendChild($dom->createTextNode(''));
    $customer_note_notify = $order->appendChild($dom->createElement('customer_note_notify'));
    $customer_note_notify->appendChild($dom->createTextNode('1'));
    $customer_prefix = $order->appendChild($dom->createElement('customer_prefix'));
    $customer_prefix->appendChild($dom->createTextNode(''));
    $customer_suffix = $order->appendChild($dom->createElement('customer_suffix'));
    $customer_suffix->appendChild($dom->createTextNode(''));
    $customer_taxvat = $order->appendChild($dom->createElement('customer_taxvat'));
    $customer_taxvat->appendChild($dom->createTextNode(''));
    $discount_amount = $order->appendChild($dom->createElement('discount_amount'));
    $discount_amount->appendChild($dom->createTextNode($discount_amountValue));
    $discount_canceled = $order->appendChild($dom->createElement('discount_canceled'));
    $discount_canceled->appendChild($dom->createTextNode(''));
    $discount_invoiced = $order->appendChild($dom->createElement('discount_invoiced'));
    $discount_invoiced->appendChild($dom->createTextNode(''));
    $discount_refunded = $order->appendChild($dom->createElement('discount_refunded'));
    $discount_refunded->appendChild($dom->createTextNode(''));
    $email_sent = $order->appendChild($dom->createElement('email_sent'));
    $email_sent->appendChild($dom->createTextNode('1'));//Always 1
    $ext_customer_id = $order->appendChild($dom->createElement('ext_customer_id'));
    $ext_customer_id->appendChild($dom->createTextNode(''));
    $ext_order_id = $order->appendChild($dom->createElement('ext_order_id'));
    $ext_order_id->appendChild($dom->createTextNode(''));
    $forced_do_shipment_with_invoice = $order->appendChild($dom->createElement('forced_do_shipment_with_invoice'));
    $forced_do_shipment_with_invoice->appendChild($dom->createTextNode(''));
    $global_currency_code = $order->appendChild($dom->createElement('global_currency_code'));
    $global_currency_code->appendChild($dom->createTextNode('USD'));
    $grand_total = $order->appendChild($dom->createElement('grand_total'));
    $grand_total->appendChild($dom->createTextNode($grand_totalValue));
    $hidden_tax_amount = $order->appendChild($dom->createElement('hidden_tax_amount'));
    $hidden_tax_amount->appendChild($dom->createTextNode(''));
    $hidden_tax_invoiced = $order->appendChild($dom->createElement('hidden_tax_invoiced'));
    $hidden_tax_invoiced->appendChild($dom->createTextNode(''));
    $hidden_tax_refunded = $order->appendChild($dom->createElement('hidden_tax_refunded'));
    $hidden_tax_refunded->appendChild($dom->createTextNode(''));
    $hold_before_state = $order->appendChild($dom->createElement('hold_before_state'));
    $hold_before_state->appendChild($dom->createTextNode(''));
    $hold_before_status = $order->appendChild($dom->createElement('hold_before_status'));
    $hold_before_status->appendChild($dom->createTextNode(''));
    $increment_id = $order->appendChild($dom->createElement('increment_id'));
    $increment_id->appendChild($dom->createTextNode($increment_idValue));
    $is_hold = $order->appendChild($dom->createElement('is_hold'));
    $is_hold->appendChild($dom->createTextNode(''));
    $is_multi_payment = $order->appendChild($dom->createElement('is_multi_payment'));
    $is_multi_payment->appendChild($dom->createTextNode(''));
    $is_virtual = $order->appendChild($dom->createElement('is_virtual'));
    $is_virtual->appendChild($dom->createTextNode('0'));//Always 0
    $order_currency_code = $order->appendChild($dom->createElement('order_currency_code'));
    $order_currency_code->appendChild($dom->createTextNode('USD'));
    $payment_authorization_amount = $order->appendChild($dom->createElement('payment_authorization_amount'));
    $payment_authorization_amount->appendChild($dom->createTextNode(''));
    $payment_authorization_expiration = $order->appendChild($dom->createElement('payment_authorization_expiration'));
    $payment_authorization_expiration->appendChild($dom->createTextNode(''));
    $paypal_ipn_customer_notified = $order->appendChild($dom->createElement('paypal_ipn_customer_notified'));
    $paypal_ipn_customer_notified->appendChild($dom->createTextNode(''));
    $real_order_id = $order->appendChild($dom->createElement('real_order_id'));
    $real_order_id->appendChild($dom->createTextNode(''));
    $remote_ip = $order->appendChild($dom->createElement('remote_ip'));
    $remote_ip->appendChild($dom->createTextNode(''));
    $shipping_amount = $order->appendChild($dom->createElement('shipping_amount'));
    $shipping_amount->appendChild($dom->createTextNode($shipping_amountValue));
    $shipping_canceled = $order->appendChild($dom->createElement('shipping_canceled'));
    $shipping_canceled->appendChild($dom->createTextNode(''));
    $shipping_discount_amount = $order->appendChild($dom->createElement('shipping_discount_amount'));
    $shipping_discount_amount->appendChild($dom->createTextNode('0.0000'));
    $shipping_hidden_tax_amount = $order->appendChild($dom->createElement('shipping_hidden_tax_amount'));
    $shipping_hidden_tax_amount->appendChild($dom->createTextNode(''));
    $shipping_incl_tax = $order->appendChild($dom->createElement('shipping_incl_tax'));
    $shipping_incl_tax->appendChild($dom->createTextNode($shipping_incl_taxValue));
    $shipping_invoiced = $order->appendChild($dom->createElement('shipping_invoiced'));
    $shipping_invoiced->appendChild($dom->createTextNode(''));
    $shipping_method = $order->appendChild($dom->createElement('shipping_method'));
    $shipping_method->appendChild($dom->createTextNode($shipping_methodValue));
    $shipping_refunded = $order->appendChild($dom->createElement('shipping_refunded'));
    $shipping_refunded->appendChild($dom->createTextNode(''));
    $shipping_tax_amount = $order->appendChild($dom->createElement('shipping_tax_amount'));
    $shipping_tax_amount->appendChild($dom->createTextNode('0.0000'));
    $shipping_tax_refunded = $order->appendChild($dom->createElement('shipping_tax_refunded'));
    $shipping_tax_refunded->appendChild($dom->createTextNode(''));
    $state = $order->appendChild($dom->createElement('state'));
    $state->appendChild($dom->createTextNode($stateValue));
    $status = $order->appendChild($dom->createElement('status'));
    $status->appendChild($dom->createTextNode($statusValue));
    $store = $order->appendChild($dom->createElement('store'));
    $store->appendChild($dom->createTextNode('sneakerhead_cn'));
    $subtotal = $order->appendChild($dom->createElement('subTotal'));
    $subtotal->appendChild($dom->createTextNode($subtotalValue));
    $subtotal_canceled = $order->appendChild($dom->createElement('subtotal_canceled'));
    $subtotal_canceled->appendChild($dom->createTextNode(''));
    $subtotal_incl_tax = $order->appendChild($dom->createElement('subtotal_incl_tax'));
    $subtotal_incl_tax->appendChild($dom->createTextNode($subtotal_incl_taxValue));
    $subtotal_invoiced = $order->appendChild($dom->createElement('subtotal_invoiced'));
    $subtotal_invoiced->appendChild($dom->createTextNode(''));
    $subtotal_refunded = $order->appendChild($dom->createElement('subtotal_refunded'));
    $subtotal_refunded->appendChild($dom->createTextNode(''));
    $tax_amount = $order->appendChild($dom->createElement('tax_amount'));
    $tax_amount->appendChild($dom->createTextNode($tax_amountValue));
    $tax_canceled = $order->appendChild($dom->createElement('tax_canceled'));
    $tax_canceled->appendChild($dom->createTextNode(''));
    $tax_invoiced = $order->appendChild($dom->createElement('tax_invoiced'));
    $tax_invoiced->appendChild($dom->createTextNode(''));
    $tax_percent = $order->appendChild($dom->createElement('tax_percent'));
    $tax_percent->appendChild($dom->createTextNode($tax_percentValue));
    $tax_refunded = $order->appendChild($dom->createElement('tax_refunded'));
    $tax_refunded->appendChild($dom->createTextNode(''));
    $total_canceled = $order->appendChild($dom->createElement('total_canceled'));
    $total_canceled->appendChild($dom->createTextNode('0.0000'));
    $total_due = $order->appendChild($dom->createElement('total_due'));
    $total_due->appendChild($dom->createTextNode($total_dueValue));
    $total_invoiced = $order->appendChild($dom->createElement('total_invoiced'));
    $total_invoiced->appendChild($dom->createTextNode('0.0000'));
    $total_item_count = $order->appendChild($dom->createElement('total_item_count'));
    $total_item_count->appendChild($dom->createTextNode(''));
    $total_offline_refunded = $order->appendChild($dom->createElement('total_offline_refunded'));
    $total_offline_refunded->appendChild($dom->createTextNode('0.0000'));
    $total_online_refunded = $order->appendChild($dom->createElement('total_online_refunded'));
    $total_online_refunded->appendChild($dom->createTextNode('0.0000'));
    $total_paid = $order->appendChild($dom->createElement('total_paid'));
    $total_paid->appendChild($dom->createTextNode('0.0000'));
    $total_qty_ordered = $order->appendChild($dom->createElement('total_qty_ordered'));
    $total_qty_ordered->appendChild($dom->createTextNode($total_qty_orderedValue));
    $total_refunded = $order->appendChild($dom->createElement('total_refunded'));
    $total_refunded->appendChild($dom->createTextNode('0.0000'));
    $tracking_numbers = $order->appendChild($dom->createElement('tracking_numbers'));
    $tracking_numbers->appendChild($dom->createTextNode(''));
    $updated_at = $order->appendChild($dom->createElement('updated_at'));
    $updated_at->appendChild($dom->createTextNode($updated_atValue));
    $updated_at_timestamp = $order->appendChild($dom->createElement('updated_at_timestamp'));
    $updated_at_timestamp->appendChild($dom->createTextNode($updated_at_timestampValue));
    $weight = $order->appendChild($dom->createElement('weight'));
    $weight->appendChild($dom->createTextNode($weightValue));
    $x_forwarded_for = $order->appendChild($dom->createElement('x_forwarded_for'));
    $x_forwarded_for->appendChild($dom->createTextNode(''));

    //Build shipping
    $shipping_address = $order->appendChild($dom->createElement('shipping_address'));
    
    $shippingCity = $shipping_address->appendChild($dom->createElement('city'));
    $shippingCity->appendChild($dom->createTextNode($shippingCityValue));
    $shippingCompany = $shipping_address->appendChild($dom->createElement('company'));
    $shippingCompany->appendChild($dom->createTextNode(''));
    $shippingCountry = $shipping_address->appendChild($dom->createElement('country'));
    $shippingCountry->appendChild($dom->createTextNode($shippingCountryValue));
    $shippingCountry_id = $shipping_address->appendChild($dom->createElement('country_id'));
    $shippingCountry_id->appendChild($dom->createTextNode(''));
    $shippingCountry_iso2 = $shipping_address->appendChild($dom->createElement('country_iso2'));
    $shippingCountry_iso2->appendChild($dom->createTextNode(''));
    $shippingCountry_iso3 = $shipping_address->appendChild($dom->createElement('country_iso3'));
    $shippingCountry_iso3->appendChild($dom->createTextNode(''));
    $shippingEmail = $shipping_address->appendChild($dom->createElement('email'));
    $shippingEmail->appendChild($dom->createTextNode($shippingEmailValue));
    $shippingFax = $shipping_address->appendChild($dom->createElement('fax'));
    $shippingFax->appendChild($dom->createTextNode(''));
    $shippingFirstname = $shipping_address->appendChild($dom->createElement('firstname'));
    $shippingFirstname->appendChild($dom->createTextNode($shippingFirstnameValue));
    $shippingLastname = $shipping_address->appendChild($dom->createElement('lastname'));
    $shippingLastname->appendChild($dom->createTextNode($shippingLastnameValue));
    $shippingMiddlename = $shipping_address->appendChild($dom->createElement('middlename'));
    $shippingMiddlename->appendChild($dom->createTextNode(''));
    $shippingName = $shipping_address->appendChild($dom->createElement('name'));
    $shippingName->appendChild($dom->createTextNode($shippingNameValue));
    $shippingPostcode = $shipping_address->appendChild($dom->createElement('postcode'));
    $shippingPostcode->appendChild($dom->createTextNode($shippingPostcodeValue));
    $shippingPrefix = $shipping_address->appendChild($dom->createElement('prefix'));
    $shippingPrefix->appendChild($dom->createTextNode(''));
    $shippingRegion = $shipping_address->appendChild($dom->createElement('region'));
    $shippingRegion->appendChild($dom->createTextNode($shippingRegionValue));
    $shippingRegion_id = $shipping_address->appendChild($dom->createElement('region_id'));
    $shippingRegion_id->appendChild($dom->createTextNode($shippingRegion_idValue));
    $shippingRegion_iso2 = $shipping_address->appendChild($dom->createElement('region_iso2'));
    $shippingRegion_iso2->appendChild($dom->createTextNode(''));
    $shippingStreet = $shipping_address->appendChild($dom->createElement('street'));
    $shippingStreet->appendChild($dom->createTextNode($shippingStreetValue));
    $shippingSuffix = $shipping_address->appendChild($dom->createElement('suffix'));
    $shippingSuffix->appendChild($dom->createTextNode(''));
    $shippingTelephone = $shipping_address->appendChild($dom->createElement('telephone'));
    $shippingTelephone->appendChild($dom->createTextNode($shippingTelephoneValue));

    // Build billing
    $billing_address = $order->appendChild($dom->createElement('billing_address'));
    
    $billingCity = $billing_address->appendChild($dom->createElement('city'));
    $billingCity->appendChild($dom->createTextNode($billingCityValue));
    $billingCompany = $billing_address->appendChild($dom->createElement('company'));
    $billingCompany->appendChild($dom->createTextNode(''));
    $billingCountry = $billing_address->appendChild($dom->createElement('country'));
    $billingCountry->appendChild($dom->createTextNode($billingCountryValue));
    $billingCountry_id = $billing_address->appendChild($dom->createElement('country_id'));
    $billingCountry_id->appendChild($dom->createTextNode(''));
    $billingCountry_iso2 = $billing_address->appendChild($dom->createElement('country_iso2'));
    $billingCountry_iso2->appendChild($dom->createTextNode(''));
    $billingCountry_iso3 = $billing_address->appendChild($dom->createElement('country_iso3'));
    $billingCountry_iso3->appendChild($dom->createTextNode(''));
    $billingEmail = $billing_address->appendChild($dom->createElement('email'));
    $billingEmail->appendChild($dom->createTextNode($billingEmailValue));
    $billingFax = $billing_address->appendChild($dom->createElement('fax'));
    $billingFax->appendChild($dom->createTextNode(''));
    $billingFirstname = $billing_address->appendChild($dom->createElement('firstname'));
    $billingFirstname->appendChild($dom->createTextNode($billingFirstnameValue));
    $billingLastname = $billing_address->appendChild($dom->createElement('lastname'));
    $billingLastname->appendChild($dom->createTextNode($billingLastnameValue));
    $billingMiddlename = $billing_address->appendChild($dom->createElement('middlename'));
    $billingMiddlename->appendChild($dom->createTextNode(''));
    $billingName = $billing_address->appendChild($dom->createElement('name'));
    $billingName->appendChild($dom->createTextNode($billingNameValue));
    $billingPostcode = $billing_address->appendChild($dom->createElement('postcode'));
    $billingPostcode->appendChild($dom->createTextNode($billingPostcodeValue));
    $billingPrefix = $billing_address->appendChild($dom->createElement('prefix'));
    $billingPrefix->appendChild($dom->createTextNode(''));
    $billingRegion = $billing_address->appendChild($dom->createElement('region'));
    $billingRegion->appendChild($dom->createTextNode($billingRegionValue));
    $billingRegion_id = $billing_address->appendChild($dom->createElement('region_id'));
    $billingRegion_id->appendChild($dom->createTextNode($billingRegion_idValue));
    $billingRegion_iso2 = $billing_address->appendChild($dom->createElement('region_iso2'));
    $billingRegion_iso2->appendChild($dom->createTextNode(''));
    $billingStreet = $billing_address->appendChild($dom->createElement('street'));
    $billingStreet->appendChild($dom->createTextNode($billingStreetValue));
    $billingSuffix = $billing_address->appendChild($dom->createElement('suffix'));
    $billingSuffix->appendChild($dom->createTextNode(''));
    $billingTelephone = $billing_address->appendChild($dom->createElement('telephone'));
    $billingTelephone->appendChild($dom->createTextNode($billingTelephoneValue));
    
    // Build payment

    $payment = $order->appendChild($dom->createElement('payment'));

    $account_status = $payment->appendChild($dom->createElement('account_status'));
    $account_status->appendChild($dom->createTextNode(''));
    $address_status = $payment->appendChild($dom->createElement('address_status'));
    $address_status->appendChild($dom->createTextNode(''));
    $amount = $payment->appendChild($dom->createElement('amount'));
    $amount->appendChild($dom->createTextNode(''));
    $amount_authorized = $payment->appendChild($dom->createElement('amount_authorized'));
    $amount_authorized->appendChild($dom->createTextNode($amount_authorizedValue));
    $amount_canceled = $payment->appendChild($dom->createElement('amount_canceled'));
    $amount_canceled->appendChild($dom->createTextNode(''));
    $amount_ordered = $payment->appendChild($dom->createElement('amount_ordered'));
    $amount_ordered->appendChild($dom->createTextNode($amount_orderedValue));
    $amount_paid = $payment->appendChild($dom->createElement('amount_paid'));
    $amount_paid->appendChild($dom->createTextNode(''));
    $amount_refunded = $payment->appendChild($dom->createElement('amount_refunded'));
    $amount_refunded->appendChild($dom->createTextNode(''));
    $anet_trans_method = $payment->appendChild($dom->createElement('anet_trans_method'));
    $anet_trans_method->appendChild($dom->createTextNode($anet_trans_methodValue));
    $base_amount_authorized = $payment->appendChild($dom->createElement('base_amount_authorized'));
    $base_amount_authorized->appendChild($dom->createTextNode($base_amount_authorizedValue));
    $base_amount_canceled = $payment->appendChild($dom->createElement('base_amount_canceled'));
    $base_amount_canceled->appendChild($dom->createTextNode(''));
    $base_amount_ordered = $payment->appendChild($dom->createElement('base_amount_ordered'));
    $base_amount_ordered->appendChild($dom->createTextNode($base_amount_orderedValue));
    $base_amount_paid = $payment->appendChild($dom->createElement('base_amount_paid'));
    $base_amount_paid->appendChild($dom->createTextNode(''));
    $base_amount_paid_online = $payment->appendChild($dom->createElement('base_amount_paid_online'));
    $base_amount_paid_online->appendChild($dom->createTextNode(''));
    $base_amount_refunded = $payment->appendChild($dom->createElement('base_amount_refunded'));
    $base_amount_refunded->appendChild($dom->createTextNode(''));
    $base_amount_refunded_online = $payment->appendChild($dom->createElement('base_amount_refunded_online'));
    $base_amount_refunded_online->appendChild($dom->createTextNode(''));
    $base_shipping_amount = $payment->appendChild($dom->createElement('base_shipping_amount'));
    $base_shipping_amount->appendChild($dom->createTextNode($base_shipping_amountValue));
    $base_shipping_captured = $payment->appendChild($dom->createElement('base_shipping_captured'));
    $base_shipping_captured->appendChild($dom->createTextNode(''));
    $base_shipping_refunded = $payment->appendChild($dom->createElement('base_shipping_refunded'));
    $base_shipping_refunded->appendChild($dom->createTextNode(''));
    $cc_approval = $payment->appendChild($dom->createElement('cc_approval'));
    $cc_approval->appendChild($dom->createTextNode($cc_approvalValue));
    $cc_avs_status = $payment->appendChild($dom->createElement('cc_avs_status'));
    $cc_avs_status->appendChild($dom->createTextNode($cc_avs_statusValue));
    $cc_cid_status = $payment->appendChild($dom->createElement('cc_cid_status'));
    $cc_cid_status->appendChild($dom->createTextNode($cc_cid_statusValue));
    $cc_debug_request_body = $payment->appendChild($dom->createElement('cc_debug_request_body'));
    $cc_debug_request_body->appendChild($dom->createTextNode(''));
    $cc_debug_response_body = $payment->appendChild($dom->createElement('cc_debug_response_body'));
    $cc_debug_response_body->appendChild($dom->createTextNode(''));
    $cc_debug_response_serialized = $payment->appendChild($dom->createElement('cc_debug_response_serialized'));
    $cc_debug_response_serialized->appendChild($dom->createTextNode(''));
    $cc_exp_month = $payment->appendChild($dom->createElement('cc_exp_month'));
    $cc_exp_month->appendChild($dom->createTextNode($cc_exp_monthValue));
    $cc_exp_year = $payment->appendChild($dom->createElement('cc_exp_year'));
    $cc_exp_year->appendChild($dom->createTextNode($cc_exp_yearValue));
    $cc_last4 = $payment->appendChild($dom->createElement('cc_last4'));
    $cc_last4->appendChild($dom->createTextNode($cc_last4Value));
    $cc_number_enc = $payment->appendChild($dom->createElement('cc_number_enc'));
    $cc_number_enc->appendChild($dom->createTextNode(''));
    $cc_owner = $payment->appendChild($dom->createElement('cc_owner'));
    $cc_owner->appendChild($dom->createTextNode(''));
    $cc_raw_request = $payment->appendChild($dom->createElement('cc_raw_request'));
    $cc_raw_request->appendChild($dom->createTextNode(''));
    $cc_raw_response = $payment->appendChild($dom->createElement('cc_raw_response'));
    $cc_raw_response->appendChild($dom->createTextNode(''));
    $cc_secure_verify = $payment->appendChild($dom->createElement('cc_secure_verify'));
    $cc_secure_verify->appendChild($dom->createTextNode(''));
    $cc_ss_issue = $payment->appendChild($dom->createElement('cc_ss_issue'));
    $cc_ss_issue->appendChild($dom->createTextNode(''));
    $cc_ss_start_month = $payment->appendChild($dom->createElement('cc_ss_start_month'));
    $cc_ss_start_month->appendChild($dom->createTextNode('0'));//appears to be 0 since not used
    $cc_ss_start_year = $payment->appendChild($dom->createElement('cc_ss_start_year'));
    $cc_ss_start_year->appendChild($dom->createTextNode('0'));//appears to be 0 since not used
    $cc_status = $payment->appendChild($dom->createElement('cc_status'));
    $cc_status->appendChild($dom->createTextNode(''));
    $cc_status_description = $payment->appendChild($dom->createElement('cc_status_description'));
    $cc_status_description->appendChild($dom->createTextNode(''));
    $cc_trans_id = $payment->appendChild($dom->createElement('cc_trans_id'));
    $cc_trans_id->appendChild($dom->createTextNode($cc_trans_idValue));
    $cc_type = $payment->appendChild($dom->createElement('cc_type'));
    $cc_type->appendChild($dom->createTextNode($cc_typeValue));
    $cybersource_token = $payment->appendChild($dom->createElement('cybersource_token'));
    $cybersource_token->appendChild($dom->createTextNode(''));
    $echeck_account_name = $payment->appendChild($dom->createElement('echeck_account_name'));
    $echeck_account_name->appendChild($dom->createTextNode(''));
    $echeck_account_type = $payment->appendChild($dom->createElement('echeck_account_type'));
    $echeck_account_type->appendChild($dom->createTextNode(''));
    $echeck_bank_name = $payment->appendChild($dom->createElement('echeck_bank_name'));
    $echeck_bank_name->appendChild($dom->createTextNode(''));
    $echeck_routing_number = $payment->appendChild($dom->createElement('echeck_routing_number'));
    $echeck_routing_number->appendChild($dom->createTextNode(''));
    $echeck_type = $payment->appendChild($dom->createElement('echeck_type'));
    $echeck_type->appendChild($dom->createTextNode(''));
    $flo2cash_account_id = $payment->appendChild($dom->createElement('flo2cash_account_id'));
    $flo2cash_account_id->appendChild($dom->createTextNode(''));
    $ideal_issuer_id = $payment->appendChild($dom->createElement('ideal_issuer_id'));
    $ideal_issuer_id->appendChild($dom->createTextNode(''));
    $ideal_issuer_title = $payment->appendChild($dom->createElement('ideal_issuer_title'));
    $ideal_issuer_title->appendChild($dom->createTextNode(''));
    $ideal_transaction_checked = $payment->appendChild($dom->createElement('ideal_transaction_checked'));
    $ideal_transaction_checked->appendChild($dom->createTextNode(''));
    $last_trans_id = $payment->appendChild($dom->createElement('last_trans_id'));
    $last_trans_id->appendChild($dom->createTextNode($last_trans_idValue));
    $method = $payment->appendChild($dom->createElement('method'));
    $method->appendChild($dom->createTextNode($methodValue));
    $paybox_question_number = $payment->appendChild($dom->createElement('paybox_question_number'));
    $paybox_question_number->appendChild($dom->createTextNode(''));
    $paybox_request_number = $payment->appendChild($dom->createElement('paybox_request_number'));
    $paybox_request_number->appendChild($dom->createTextNode(''));
    $po_number = $payment->appendChild($dom->createElement('po_number'));
    $po_number->appendChild($dom->createTextNode(''));
    $protection_eligibility = $payment->appendChild($dom->createElement('protection_eligibility'));
    $protection_eligibility->appendChild($dom->createTextNode(''));
    $shipping_amount = $payment->appendChild($dom->createElement('shipping_amount'));
    $shipping_amount->appendChild($dom->createTextNode($shipping_amountValue));
    $shipping_captured = $payment->appendChild($dom->createElement('shipping_captured'));
    $shipping_captured->appendChild($dom->createTextNode(''));
    $shipping_refunded = $payment->appendChild($dom->createElement('shipping_refunded'));
    $shipping_refunded->appendChild($dom->createTextNode(''));

    // Build Items
    $items = $order->appendChild($dom->createElement('items'));
    $itemsQuery = "SELECT * FROM `se_orderitem` WHERE `yahooOrderIdNumeric` = " . $result['yahooOrderIdNumeric'];
    $itemsResult = $writeConnection->query($itemsQuery);
    $itemNumber = 1;
    foreach ($itemsResult as $itemResult) {
        $item = $items->appendChild($dom->createElement('item'));

	//Set variables
	$base_original_priceValue = $itemResult['unitPrice'];
	$base_priceValue = $itemResult['unitPrice'];
	$base_row_totalValue = $itemResult['qtyOrdered'] * $itemResult['unitPrice'];
	$real_nameValue = $itemResult['lineItemDescription'];
	$nameValue = $itemResult['lineItemDescription'];
	$original_priceValue = $itemResult['unitPrice'];
	$priceValue = $itemResult['unitPrice'];
	$qty_orderedValue = $itemResult['qtyOrdered'];
	$row_totalValue = $itemResult['qtyOrdered'] * $itemResult['unitPrice'];
	$length = strlen(end(explode('-', $itemResult['productCode'])));
	$real_skuValue = substr($itemResult['productCode'], 0, -($length + 1));
        
	fwrite($transactionLogHandle, "      ->ADDING CONFIGURABLE : " . $itemNumber . " -> " . $real_skuValue . "\n");

	$skuValue = 'Product ' . $itemNumber;
	if (!is_null($result['shipCountry']) && $result['shipState'] == 'CA') {
	    if (strtolower($result['shipCountry']) == 'united states') {
		$tax_percentCalcValue = '0.0875';
		$tax_percentValue = '8.75';
		$base_price_incl_taxValue = round($priceValue + ($priceValue * $tax_percentCalcValue), 4);//
		$base_row_total_incl_taxValue = round($qty_orderedValue * ($priceValue + ($priceValue * $tax_percentCalcValue)), 4);//
		$base_tax_amountValue = round($priceValue * $tax_percentCalcValue, 4);//THIS MAY BE WRONG -- QTY or ONE
		$price_incl_taxValue = round($priceValue + ($priceValue * $tax_percentCalcValue), 4);//
		$row_total_incl_taxValue = round($qty_orderedValue * ($priceValue + ($priceValue * $tax_percentCalcValue)), 4);//
		$tax_amountValue = round($priceValue * $tax_percentCalcValue, 4);//
	    } else {
		$tax_percentValue = '0.00';
		$base_price_incl_taxValue = $priceValue;
		$base_row_total_incl_taxValue = $qty_orderedValue * $priceValue;
		$base_tax_amountValue = '0.00';
		$price_incl_taxValue = $priceValue;
		$row_total_incl_taxValue = $qty_orderedValue * $priceValue;
		$tax_amountValue = '0.00';		
	    }
	} else {
	    $tax_percentValue = '0.00';
	    $base_price_incl_taxValue = $priceValue;
	    $base_row_total_incl_taxValue = $qty_orderedValue * $priceValue;
	    $base_tax_amountValue = '0.00';
	    $price_incl_taxValue = $priceValue;
	    $row_total_incl_taxValue = $qty_orderedValue * $priceValue;
	    $tax_amountValue = '0.00';	
	}

	//Create line item
	$amount_refunded = $item->appendChild($dom->createElement('amount_refunded'));
	$amount_refunded->appendChild($dom->createTextNode('0')); //Always 0
	$applied_rule_ids = $item->appendChild($dom->createElement('applied_rule_ids'));
	$applied_rule_ids->appendChild($dom->createTextNode(''));
	$base_amount_refunded = $item->appendChild($dom->createElement('base_amount_refunded'));
	$base_amount_refunded->appendChild($dom->createTextNode('0')); //Always 0
	$base_cost = $item->appendChild($dom->createElement('base_cost'));
	$base_cost->appendChild($dom->createTextNode(''));
	$base_discount_amount = $item->appendChild($dom->createElement('base_discount_amount'));
	$base_discount_amount->appendChild($dom->createTextNode('0')); //Always 0
	$base_discount_invoiced = $item->appendChild($dom->createElement('base_discount_invoiced'));
	$base_discount_invoiced->appendChild($dom->createTextNode('0')); //Always 0
	$base_hidden_tax_amount = $item->appendChild($dom->createElement('base_hidden_tax_amount'));
	$base_hidden_tax_amount->appendChild($dom->createTextNode(''));
	$base_hidden_tax_invoiced = $item->appendChild($dom->createElement('base_hidden_tax_invoiced'));
	$base_hidden_tax_invoiced->appendChild($dom->createTextNode(''));
	$base_hidden_tax_refunded = $item->appendChild($dom->createElement('base_hidden_tax_refunded'));
	$base_hidden_tax_refunded->appendChild($dom->createTextNode(''));
	$base_original_price = $item->appendChild($dom->createElement('base_original_price'));
	$base_original_price->appendChild($dom->createTextNode($base_original_priceValue));
	$base_price = $item->appendChild($dom->createElement('base_price'));
	$base_price->appendChild($dom->createTextNode($base_priceValue));
	$base_price_incl_tax = $item->appendChild($dom->createElement('base_price_incl_tax'));
	$base_price_incl_tax->appendChild($dom->createTextNode($base_price_incl_taxValue));
	$base_row_invoiced = $item->appendChild($dom->createElement('base_row_invoiced'));
	$base_row_invoiced->appendChild($dom->createTextNode('0'));
	$base_row_total = $item->appendChild($dom->createElement('base_row_total'));
	$base_row_total->appendChild($dom->createTextNode($base_row_totalValue));
	$base_row_total_incl_tax = $item->appendChild($dom->createElement('base_row_total_incl_tax'));
	$base_row_total_incl_tax->appendChild($dom->createTextNode($base_row_total_incl_taxValue));
	$base_tax_amount = $item->appendChild($dom->createElement('base_tax_amount'));
	$base_tax_amount->appendChild($dom->createTextNode($base_tax_amountValue));
	$base_tax_before_discount = $item->appendChild($dom->createElement('base_tax_before_discount'));
	$base_tax_before_discount->appendChild($dom->createTextNode(''));
	$base_tax_invoiced = $item->appendChild($dom->createElement('base_tax_invoiced'));
	$base_tax_invoiced->appendChild($dom->createTextNode('0')); //Always 0
	$base_weee_tax_applied_amount = $item->appendChild($dom->createElement('base_weee_tax_applied_amount'));
	$base_weee_tax_applied_amount->appendChild($dom->createTextNode('0')); //Always 0
	$base_weee_tax_applied_row_amount = $item->appendChild($dom->createElement('base_weee_tax_applied_row_amount'));
	$base_weee_tax_applied_row_amount->appendChild($dom->createTextNode('0')); //Always 0
	$base_weee_tax_disposition = $item->appendChild($dom->createElement('base_weee_tax_disposition'));
	$base_weee_tax_disposition->appendChild($dom->createTextNode('0')); //Always 0
	$base_weee_tax_row_disposition = $item->appendChild($dom->createElement('base_weee_tax_row_disposition'));
	$base_weee_tax_row_disposition->appendChild($dom->createTextNode('0')); //Always 0
	$description = $item->appendChild($dom->createElement('description'));
	$description->appendChild($dom->createTextNode(''));
	$discount_amount = $item->appendChild($dom->createElement('discount_amount'));
	$discount_amount->appendChild($dom->createTextNode('0')); //Always 0
	$discount_invoiced = $item->appendChild($dom->createElement('discount_invoiced'));
	$discount_invoiced->appendChild($dom->createTextNode('0')); //Always 0
	$discount_percent = $item->appendChild($dom->createElement('discount_percent'));
	$discount_percent->appendChild($dom->createTextNode('0')); //Always 0
	$free_shipping = $item->appendChild($dom->createElement('free_shipping'));
	$free_shipping->appendChild($dom->createTextNode('0')); //Always 0
	$hidden_tax_amount = $item->appendChild($dom->createElement('hidden_tax_amount'));
	$hidden_tax_amount->appendChild($dom->createTextNode(''));
	$hidden_tax_canceled = $item->appendChild($dom->createElement('hidden_tax_canceled'));
	$hidden_tax_canceled->appendChild($dom->createTextNode(''));
	$hidden_tax_invoiced = $item->appendChild($dom->createElement('hidden_tax_invoiced'));
	$hidden_tax_invoiced->appendChild($dom->createTextNode(''));
	$hidden_tax_refunded = $item->appendChild($dom->createElement('hidden_tax_refunded'));
	$hidden_tax_refunded->appendChild($dom->createTextNode(''));
	$is_nominal = $item->appendChild($dom->createElement('is_nominal'));
	$is_nominal->appendChild($dom->createTextNode('0')); //Always 0
	$is_qty_decimal = $item->appendChild($dom->createElement('is_qty_decimal'));
	$is_qty_decimal->appendChild($dom->createTextNode('0')); //Always 0
	$is_virtual = $item->appendChild($dom->createElement('is_virtual'));
	$is_virtual->appendChild($dom->createTextNode('0')); //Always 0
	$real_name = $item->appendChild($dom->createElement('real_name'));
	$real_name->appendChild($dom->createTextNode($real_nameValue)); //Always 0
	$name = $item->appendChild($dom->createElement('name'));
	$name->appendChild($dom->createTextNode($nameValue)); //Always 0
	$no_discount = $item->appendChild($dom->createElement('no_discount'));
	$no_discount->appendChild($dom->createTextNode('0')); //Always 0
	$original_price = $item->appendChild($dom->createElement('original_price'));
	$original_price->appendChild($dom->createTextNode($original_priceValue));
	$price = $item->appendChild($dom->createElement('price'));
	$price->appendChild($dom->createTextNode($priceValue));
	$price_incl_tax = $item->appendChild($dom->createElement('price_incl_tax'));
	$price_incl_tax->appendChild($dom->createTextNode($price_incl_taxValue));
	$qty_backordered = $item->appendChild($dom->createElement('qty_backordered'));
	$qty_backordered->appendChild($dom->createTextNode(''));
	$qty_canceled = $item->appendChild($dom->createElement('qty_canceled'));
	$qty_canceled->appendChild($dom->createTextNode('0')); //Always 0
	$qty_invoiced = $item->appendChild($dom->createElement('qty_invoiced'));
	$qty_invoiced->appendChild($dom->createTextNode('0')); //Always 0
	$qty_ordered = $item->appendChild($dom->createElement('qty_ordered'));
	$qty_ordered->appendChild($dom->createTextNode($qty_orderedValue)); //Always 0
	$qty_refunded = $item->appendChild($dom->createElement('qty_refunded'));
	$qty_refunded->appendChild($dom->createTextNode('0')); //Always 0
	$qty_shipped = $item->appendChild($dom->createElement('qty_shipped'));
	$qty_shipped->appendChild($dom->createTextNode('0')); //Always 0
	$row_invoiced = $item->appendChild($dom->createElement('row_invoiced'));
	$row_invoiced->appendChild($dom->createTextNode('0')); //Always 0
	$row_total = $item->appendChild($dom->createElement('row_total'));
	$row_total->appendChild($dom->createTextNode($row_totalValue));
	$row_total_incl_tax = $item->appendChild($dom->createElement('row_total_incl_tax'));
	$row_total_incl_tax->appendChild($dom->createTextNode($row_total_incl_taxValue));
	$row_weight = $item->appendChild($dom->createElement('row_weight'));
	$row_weight->appendChild($dom->createTextNode('0'));
	$real_sku = $item->appendChild($dom->createElement('real_sku'));
	$real_sku->appendChild($dom->createTextNode($real_skuValue));
	$sku = $item->appendChild($dom->createElement('sku'));
	$sku->appendChild($dom->createTextNode($skuValue));
	$tax_amount = $item->appendChild($dom->createElement('tax_amount'));
	$tax_amount->appendChild($dom->createTextNode($tax_amountValue));
	$tax_before_discount = $item->appendChild($dom->createElement('tax_before_discount'));
	$tax_before_discount->appendChild($dom->createTextNode(''));
	$tax_canceled = $item->appendChild($dom->createElement('tax_canceled'));
	$tax_canceled->appendChild($dom->createTextNode(''));
	$tax_invoiced = $item->appendChild($dom->createElement('tax_invoiced'));
	$tax_invoiced->appendChild($dom->createTextNode('0'));
	$tax_percent = $item->appendChild($dom->createElement('tax_percent'));
	$tax_percent->appendChild($dom->createTextNode($tax_percentValue));
	$tax_refunded = $item->appendChild($dom->createElement('tax_refunded'));
	$tax_refunded->appendChild($dom->createTextNode(''));
	$weee_tax_applied = $item->appendChild($dom->createElement('weee_tax_applied'));
	$weee_tax_applied->appendChild($dom->createTextNode('a:0:{}')); //Always 0
	$weee_tax_applied_amount = $item->appendChild($dom->createElement('weee_tax_applied_amount'));
	$weee_tax_applied_amount->appendChild($dom->createTextNode('0')); //Always 0
	$weee_tax_applied_row_amount = $item->appendChild($dom->createElement('weee_tax_applied_row_amount'));
	$weee_tax_applied_row_amount->appendChild($dom->createTextNode('0')); //Always 0
	$weee_tax_disposition = $item->appendChild($dom->createElement('weee_tax_disposition'));
	$weee_tax_disposition->appendChild($dom->createTextNode('0')); //Always 0
	$weee_tax_row_disposition = $item->appendChild($dom->createElement('weee_tax_row_disposition'));
	$weee_tax_row_disposition->appendChild($dom->createTextNode('0')); //Always 0
	$weight = $item->appendChild($dom->createElement('weight'));
	$weight->appendChild($dom->createTextNode('0'));

	//Add simple
	$item = $items->appendChild($dom->createElement('item'));
	
	//Set variables
	$base_original_priceValue = '0.0000';
	$base_priceValue = '0.0000';
	$base_row_totalValue = '0.0000';
	$real_nameValue = $itemResult['lineItemDescription'];
	$nameValue = $itemResult['lineItemDescription'];
	$original_priceValue = '0.0000';
	$priceValue = '0.0000';
	$qty_orderedValue = $itemResult['qtyOrdered'];
	$row_totalValue = '0.0000';
	$real_skuValue = $itemResult['productCode'];
	$skuValue = "Product " . $itemNumber . "-OFFLINE";
	$parent_skuValue = 'Product ' . $itemNumber;//Just for simple
	
	fwrite($transactionLogHandle, "      ->ADDING SIMPLE       : " . $itemNumber . " -> " . $real_skuValue . "\n");

	$tax_percentValue = '0.00';
	$base_price_incl_taxValue = '0.0000';
	$base_row_total_incl_taxValue = '0.0000';
	$base_tax_amountValue = '0.0000';
	$price_incl_taxValue = '0.0000';
	$row_total_incl_taxValue = '0.0000';
	$tax_amountValue = '0.0000';

	//Create line item
	$amount_refunded = $item->appendChild($dom->createElement('amount_refunded'));
	$amount_refunded->appendChild($dom->createTextNode('0')); //Always 0
	$applied_rule_ids = $item->appendChild($dom->createElement('applied_rule_ids'));
	$applied_rule_ids->appendChild($dom->createTextNode(''));
	$base_amount_refunded = $item->appendChild($dom->createElement('base_amount_refunded'));
	$base_amount_refunded->appendChild($dom->createTextNode('0')); //Always 0
	$base_cost = $item->appendChild($dom->createElement('base_cost'));
	$base_cost->appendChild($dom->createTextNode(''));
	$base_discount_amount = $item->appendChild($dom->createElement('base_discount_amount'));
	$base_discount_amount->appendChild($dom->createTextNode('0')); //Always 0
	$base_discount_invoiced = $item->appendChild($dom->createElement('base_discount_invoiced'));
	$base_discount_invoiced->appendChild($dom->createTextNode('0')); //Always 0
	$base_hidden_tax_amount = $item->appendChild($dom->createElement('base_hidden_tax_amount'));
	$base_hidden_tax_amount->appendChild($dom->createTextNode(''));
	$base_hidden_tax_invoiced = $item->appendChild($dom->createElement('base_hidden_tax_invoiced'));
	$base_hidden_tax_invoiced->appendChild($dom->createTextNode(''));
	$base_hidden_tax_refunded = $item->appendChild($dom->createElement('base_hidden_tax_refunded'));
	$base_hidden_tax_refunded->appendChild($dom->createTextNode(''));
	$base_original_price = $item->appendChild($dom->createElement('base_original_price'));
	$base_original_price->appendChild($dom->createTextNode($base_original_priceValue));
	$base_price = $item->appendChild($dom->createElement('base_price'));
	$base_price->appendChild($dom->createTextNode($base_priceValue));
	$base_price_incl_tax = $item->appendChild($dom->createElement('base_price_incl_tax'));
	$base_price_incl_tax->appendChild($dom->createTextNode($base_price_incl_taxValue));
	$base_row_invoiced = $item->appendChild($dom->createElement('base_row_invoiced'));
	$base_row_invoiced->appendChild($dom->createTextNode('0'));
	$base_row_total = $item->appendChild($dom->createElement('base_row_total'));
	$base_row_total->appendChild($dom->createTextNode($base_row_totalValue));
	$base_row_total_incl_tax = $item->appendChild($dom->createElement('base_row_total_incl_tax'));
	$base_row_total_incl_tax->appendChild($dom->createTextNode($base_row_total_incl_taxValue));
	$base_tax_amount = $item->appendChild($dom->createElement('base_tax_amount'));
	$base_tax_amount->appendChild($dom->createTextNode($base_tax_amountValue));
	$base_tax_before_discount = $item->appendChild($dom->createElement('base_tax_before_discount'));
	$base_tax_before_discount->appendChild($dom->createTextNode(''));
	$base_tax_invoiced = $item->appendChild($dom->createElement('base_tax_invoiced'));
	$base_tax_invoiced->appendChild($dom->createTextNode('0')); //Always 0
	$base_weee_tax_applied_amount = $item->appendChild($dom->createElement('base_weee_tax_applied_amount'));
	$base_weee_tax_applied_amount->appendChild($dom->createTextNode('0')); //Always 0
	$base_weee_tax_applied_row_amount = $item->appendChild($dom->createElement('base_weee_tax_applied_row_amount'));
	$base_weee_tax_applied_row_amount->appendChild($dom->createTextNode('0')); //Always 0
	$base_weee_tax_disposition = $item->appendChild($dom->createElement('base_weee_tax_disposition'));
	$base_weee_tax_disposition->appendChild($dom->createTextNode('0')); //Always 0
	$base_weee_tax_row_disposition = $item->appendChild($dom->createElement('base_weee_tax_row_disposition'));
	$base_weee_tax_row_disposition->appendChild($dom->createTextNode('0')); //Always 0
	$description = $item->appendChild($dom->createElement('description'));
	$description->appendChild($dom->createTextNode(''));
	$discount_amount = $item->appendChild($dom->createElement('discount_amount'));
	$discount_amount->appendChild($dom->createTextNode('0')); //Always 0
	$discount_invoiced = $item->appendChild($dom->createElement('discount_invoiced'));
	$discount_invoiced->appendChild($dom->createTextNode('0')); //Always 0
	$discount_percent = $item->appendChild($dom->createElement('discount_percent'));
	$discount_percent->appendChild($dom->createTextNode('0')); //Always 0
	$free_shipping = $item->appendChild($dom->createElement('free_shipping'));
	$free_shipping->appendChild($dom->createTextNode('0')); //Always 0
	$hidden_tax_amount = $item->appendChild($dom->createElement('hidden_tax_amount'));
	$hidden_tax_amount->appendChild($dom->createTextNode(''));
	$hidden_tax_canceled = $item->appendChild($dom->createElement('hidden_tax_canceled'));
	$hidden_tax_canceled->appendChild($dom->createTextNode(''));
	$hidden_tax_invoiced = $item->appendChild($dom->createElement('hidden_tax_invoiced'));
	$hidden_tax_invoiced->appendChild($dom->createTextNode(''));
	$hidden_tax_refunded = $item->appendChild($dom->createElement('hidden_tax_refunded'));
	$hidden_tax_refunded->appendChild($dom->createTextNode(''));
	$is_nominal = $item->appendChild($dom->createElement('is_nominal'));
	$is_nominal->appendChild($dom->createTextNode('0')); //Always 0
	$is_qty_decimal = $item->appendChild($dom->createElement('is_qty_decimal'));
	$is_qty_decimal->appendChild($dom->createTextNode('0')); //Always 0
	$is_virtual = $item->appendChild($dom->createElement('is_virtual'));
	$is_virtual->appendChild($dom->createTextNode('0')); //Always 0
	$real_name = $item->appendChild($dom->createElement('real_name'));
	$real_name->appendChild($dom->createTextNode($real_nameValue)); //Always 0
	$name = $item->appendChild($dom->createElement('nameValue'));
	$name->appendChild($dom->createTextNode($nameValue)); //Always 0
	$no_discount = $item->appendChild($dom->createElement('no_discount'));
	$no_discount->appendChild($dom->createTextNode('0')); //Always 0
	$original_price = $item->appendChild($dom->createElement('original_price'));
	$original_price->appendChild($dom->createTextNode($original_priceValue));
	$parent_sku = $item->appendChild($dom->createElement('parent_sku'));
	$parent_sku->appendChild($dom->createTextNode($parent_skuValue));
	$price = $item->appendChild($dom->createElement('price'));
	$price->appendChild($dom->createTextNode($priceValue));
	$price_incl_tax = $item->appendChild($dom->createElement('price_incl_tax'));
	$price_incl_tax->appendChild($dom->createTextNode($price_incl_taxValue));
	$qty_backordered = $item->appendChild($dom->createElement('qty_backordered'));
	$qty_backordered->appendChild($dom->createTextNode(''));
	$qty_canceled = $item->appendChild($dom->createElement('qty_canceled'));
	$qty_canceled->appendChild($dom->createTextNode('0')); //Always 0
	$qty_invoiced = $item->appendChild($dom->createElement('qty_invoiced'));
	$qty_invoiced->appendChild($dom->createTextNode('0')); //Always 0
	$qty_ordered = $item->appendChild($dom->createElement('qty_ordered'));
	$qty_ordered->appendChild($dom->createTextNode($qty_orderedValue)); //Always 0
	$qty_refunded = $item->appendChild($dom->createElement('qty_refunded'));
	$qty_refunded->appendChild($dom->createTextNode('0')); //Always 0
	$qty_shipped = $item->appendChild($dom->createElement('qty_shipped'));
	$qty_shipped->appendChild($dom->createTextNode('0')); //Always 0
	$row_invoiced = $item->appendChild($dom->createElement('row_invoiced'));
	$row_invoiced->appendChild($dom->createTextNode('0')); //Always 0
	$row_total = $item->appendChild($dom->createElement('row_total'));
	$row_total->appendChild($dom->createTextNode($row_totalValue));
	$row_total_incl_tax = $item->appendChild($dom->createElement('row_total_incl_tax'));
	$row_total_incl_tax->appendChild($dom->createTextNode($row_total_incl_taxValue));
	$row_weight = $item->appendChild($dom->createElement('row_weight'));
	$row_weight->appendChild($dom->createTextNode('0'));
	$real_sku = $item->appendChild($dom->createElement('real_sku'));
	$real_sku->appendChild($dom->createTextNode($real_skuValue));
	$sku = $item->appendChild($dom->createElement('sku'));
	$sku->appendChild($dom->createTextNode($skuValue));
	$tax_amount = $item->appendChild($dom->createElement('tax_amount'));
	$tax_amount->appendChild($dom->createTextNode($tax_amountValue));
	$tax_before_discount = $item->appendChild($dom->createElement('tax_before_discount'));
	$tax_before_discount->appendChild($dom->createTextNode(''));
	$tax_canceled = $item->appendChild($dom->createElement('tax_canceled'));
	$tax_canceled->appendChild($dom->createTextNode(''));
	$tax_invoiced = $item->appendChild($dom->createElement('tax_invoiced'));
	$tax_invoiced->appendChild($dom->createTextNode('0'));
	$tax_percent = $item->appendChild($dom->createElement('tax_percent'));
	$tax_percent->appendChild($dom->createTextNode($tax_percentValue));
	$tax_refunded = $item->appendChild($dom->createElement('tax_refunded'));
	$tax_refunded->appendChild($dom->createTextNode(''));
	$weee_tax_applied = $item->appendChild($dom->createElement('weee_tax_applied'));
	$weee_tax_applied->appendChild($dom->createTextNode('a:0:{}')); //Always 0
	$weee_tax_applied_amount = $item->appendChild($dom->createElement('weee_tax_applied_amount'));
	$weee_tax_applied_amount->appendChild($dom->createTextNode('0')); //Always 0
	$weee_tax_applied_row_amount = $item->appendChild($dom->createElement('weee_tax_applied_row_amount'));
	$weee_tax_applied_row_amount->appendChild($dom->createTextNode('0')); //Always 0
	$weee_tax_disposition = $item->appendChild($dom->createElement('weee_tax_disposition'));
	$weee_tax_disposition->appendChild($dom->createTextNode('0')); //Always 0
	$weee_tax_row_disposition = $item->appendChild($dom->createElement('weee_tax_row_disposition'));
	$weee_tax_row_disposition->appendChild($dom->createTextNode('0')); //Always 0
	$weight = $item->appendChild($dom->createElement('weight'));
	$weight->appendChild($dom->createTextNode('0'));
	
        $itemNumber++;
    }
}

// Make the output pretty
$dom->formatOutput = true;

// Save the XML string
$xml = $dom->saveXML();

//Write file to post directory
$handle = fopen($toolsXmlDirectory . $orderFilename, 'w');
fwrite($handle, $xml);
fclose($handle);

fwrite($transactionLogHandle, "  ->FINISH TIME   : " . $endTime[5] . '/' . $endTime[4] . '/' . $endTime[3] . ' ' . $endTime[2] . ':' . $endTime[1] . ':' . $endTime[0] . "\n");
fwrite($transactionLogHandle, "  ->CREATED       : ORDER FILE 9 " . $orderFilename . "\n");

fwrite($transactionLogHandle, "->END PROCESSING\n");
//Close transaction log
fclose($transactionLogHandle);

//FILE 10
$realTime = realTime();
//Open transaction log
$transactionLogHandle = fopen($toolsLogsDirectory . 'migration_gen_sneakerhead_order_xml_files_transaction_log', 'a+');
fwrite($transactionLogHandle, "->BEGIN PROCESSING\n");
fwrite($transactionLogHandle, "  ->START TIME    : " . $realTime[5] . '/' . $realTime[4] . '/' . $realTime[3] . ' ' . $realTime[2] . ':' . $realTime[1] . ':' . $realTime[0] . "\n");

//ORDERS
fwrite($transactionLogHandle, "  ->GETTING ORDERS\n");

$resource = Mage::getSingleton('core/resource');
$writeConnection = $resource->getConnection('core_write');
//$startDate = '2009-10-00 00:00:00';//>=
$startDate = '2010-03-15 00:00:00';//>=
$endDate = '2010-03-15 00:00:00';//<
//10,000
//$query = "SELECT `users`.`email` AS `user_email`, `users`.`firstName` AS `user_firstname`, `users`.`lastName` AS `user_lastname`, `se_order`.* FROM `se_order` LEFT JOIN `storeorder` ON `se_order`.`yahooOrderIdNumeric` = `storeorder`.`yahooOrderIdNumeric` LEFT JOIN `users` ON `storeorder`.`uid` = `users`.`uid` WHERE `se_order`.`orderCreationDate` > '2009-10-00 00:00:00' AND `se_order`.`orderCreationDate` < '2009-12-14 00:00:00'";
//25,000
//FOLLOWING 8 QUERIES TO BE RUN SEPARATELY TO GENERATE 8 DIFFERENT FILES
//$query = "SELECT `users`.`email` AS `user_email`, `users`.`firstName` AS `user_firstname`, `users`.`lastName` AS `user_lastname`, `se_order`.* FROM `se_order` LEFT JOIN `storeorder` ON `se_order`.`yahooOrderIdNumeric` = `storeorder`.`yahooOrderIdNumeric` LEFT JOIN `users` ON `storeorder`.`uid` = `users`.`uid` WHERE `se_order`.`orderCreationDate` >= '2009-10-00 00:00:00' AND `se_order`.`orderCreationDate` < '2010-03-15 00:00:00'";//191298 -> 216253
//$query = "SELECT `users`.`email` AS `user_email`, `users`.`firstName` AS `user_firstname`, `users`.`lastName` AS `user_lastname`, `se_order`.* FROM `se_order` LEFT JOIN `storeorder` ON `se_order`.`yahooOrderIdNumeric` = `storeorder`.`yahooOrderIdNumeric` LEFT JOIN `users` ON `storeorder`.`uid` = `users`.`uid` WHERE `se_order`.`orderCreationDate` >= '2010-03-15 00:00:00' AND `se_order`.`orderCreationDate` < '2010-10-28 00:00:00'";//216254 -> 241203
//$query = "SELECT `users`.`email` AS `user_email`, `users`.`firstName` AS `user_firstname`, `users`.`lastName` AS `user_lastname`, `se_order`.* FROM `se_order` LEFT JOIN `storeorder` ON `se_order`.`yahooOrderIdNumeric` = `storeorder`.`yahooOrderIdNumeric` LEFT JOIN `users` ON `storeorder`.`uid` = `users`.`uid` WHERE `se_order`.`orderCreationDate` >= '2010-10-28 00:00:00' AND `se_order`.`orderCreationDate` < '2011-02-27 00:00:00'";//241204 -> 266066
//$query = "SELECT `users`.`email` AS `user_email`, `users`.`firstName` AS `user_firstname`, `users`.`lastName` AS `user_lastname`, `se_order`.* FROM `se_order` LEFT JOIN `storeorder` ON `se_order`.`yahooOrderIdNumeric` = `storeorder`.`yahooOrderIdNumeric` LEFT JOIN `users` ON `storeorder`.`uid` = `users`.`uid` WHERE `se_order`.`orderCreationDate` >= '2011-02-27 00:00:00' AND `se_order`.`orderCreationDate` < '2011-06-27 00:00:00'";//266067 -> 291019
//$query = "SELECT `users`.`email` AS `user_email`, `users`.`firstName` AS `user_firstname`, `users`.`lastName` AS `user_lastname`, `se_order`.* FROM `se_order` LEFT JOIN `storeorder` ON `se_order`.`yahooOrderIdNumeric` = `storeorder`.`yahooOrderIdNumeric` LEFT JOIN `users` ON `storeorder`.`uid` = `users`.`uid` WHERE `se_order`.`orderCreationDate` >= '2011-06-27 00:00:00' AND `se_order`.`orderCreationDate` < '2011-12-09 00:00:00'";//291020 -> 315244
//$query = "SELECT `users`.`email` AS `user_email`, `users`.`firstName` AS `user_firstname`, `users`.`lastName` AS `user_lastname`, `se_order`.* FROM `se_order` LEFT JOIN `storeorder` ON `se_order`.`yahooOrderIdNumeric` = `storeorder`.`yahooOrderIdNumeric` LEFT JOIN `users` ON `storeorder`.`uid` = `users`.`uid` WHERE `se_order`.`orderCreationDate` >= '2011-12-09 00:00:00' AND `se_order`.`orderCreationDate` < '2012-04-24 00:00:00'";//315245 -> 340092
//$query = "SELECT `users`.`email` AS `user_email`, `users`.`firstName` AS `user_firstname`, `users`.`lastName` AS `user_lastname`, `se_order`.* FROM `se_order` LEFT JOIN `storeorder` ON `se_order`.`yahooOrderIdNumeric` = `storeorder`.`yahooOrderIdNumeric` LEFT JOIN `users` ON `storeorder`.`uid` = `users`.`uid` WHERE `se_order`.`orderCreationDate` >= '2012-04-24 00:00:00' AND `se_order`.`orderCreationDate` < '2012-10-04 00:00:00'";//340093 -> 364330
//$query = "SELECT `users`.`email` AS `user_email`, `users`.`firstName` AS `user_firstname`, `users`.`lastName` AS `user_lastname`, `se_order`.* FROM `se_order` LEFT JOIN `storeorder` ON `se_order`.`yahooOrderIdNumeric` = `storeorder`.`yahooOrderIdNumeric` LEFT JOIN `users` ON `storeorder`.`uid` = `users`.`uid` WHERE `se_order`.`orderCreationDate` >= '2012-10-04 00:00:00' AND `se_order`.`orderCreationDate` < '2013-02-16 00:00:00'";//364331 -> ???
//$query = "SELECT `users`.`email` AS `user_email`, `users`.`firstName` AS `user_firstname`, `users`.`lastName` AS `user_lastname`, `se_order`.* FROM `se_order` LEFT JOIN `storeorder` ON `se_order`.`yahooOrderIdNumeric` = `storeorder`.`yahooOrderIdNumeric` LEFT JOIN `users` ON `storeorder`.`uid` = `users`.`uid` WHERE `se_order`.`orderCreationDate` >= '2013-02-16 00:00:00' AND `se_order`.`orderCreationDate` < '2013-04-23 00:00:00'";//??? -> ???
$query = "SELECT `users`.`email` AS `user_email`, `users`.`firstName` AS `user_firstname`, `users`.`lastName` AS `user_lastname`, `se_order`.* FROM `se_order` LEFT JOIN `storeorder` ON `se_order`.`yahooOrderIdNumeric` = `storeorder`.`yahooOrderIdNumeric` LEFT JOIN `users` ON `storeorder`.`uid` = `users`.`uid` WHERE `se_order`.`orderCreationDate` >= '2013-04-23 00:00:00'"; //??? -> current (???)

$results = $writeConnection->query($query);
$orderFilename = "order_10_" . $realTime[5] . $realTime[4] . $realTime[3] . '_' . $realTime[2] . $realTime[1] . $realTime[0] . ".xml";
//Creates XML string and XML document from the DOM representation
$dom = new DomDocument('1.0');

$orders = $dom->appendChild($dom->createElement('orders'));
foreach ($results as $result) {
    
    //Add order data
    fwrite($transactionLogHandle, "    ->ADDING ORDER NUMBER   : " . $result['yahooOrderIdNumeric'] . "\n");
    
    // Set some variables
    $base_discount_amountValue = (is_null($result['discount'])) ? '0.0000' : $result['discount'];//appears to be actual total discount
    $base_grand_totalValue = (is_null($result['grandTotal'])) ? '0.0000' : $result['grandTotal'];
    $base_shipping_amountValue = (is_null($result['shippingCost'])) ? '0.0000' : $result['shippingCost'];
    $base_shipping_incl_taxValue = (is_null($result['shippingCost'])) ? '0.0000' : $result['shippingCost'];
    $base_subtotalValue = (is_null($result['subTotal'])) ? '0.0000' : $result['subTotal'];
    $base_subtotal_incl_taxValue = (is_null($result['subTotal'])) ? '0.0000' : $result['subTotal'];
    $base_tax_amountValue = (is_null($result['taxTotal'])) ? '0.0000' : $result['taxTotal'];

    if (!is_null($result['shipCountry']) && $result['shipState'] == 'CA') {
	if (strtolower($result['shipCountry']) == 'united states') {
	    $tax_percentValue = '8.75';
	} else {
	    $tax_percentValue = '0.00';
	}
    } else {
	$tax_percentValue = '0.00';
    }

    $base_total_dueValue = (is_null($result['grandTotal'])) ? '0.0000' : $result['grandTotal'];
    $real_created_atValue = (is_null($result['orderCreationDate'])) ? date("Y-m-d H:i:s") : $result['orderCreationDate'];//current date or order creation date
    $created_at_timestampValue = strtotime($real_created_atValue);//set from created date
    $customer_emailValue = (is_null($result['user_email'])) ? (is_null($result['email'])) ? '' : $result['email'] : $result['user_email'];
    $customer_firstnameValue = (is_null($result['user_firstname'])) ? (is_null($result['firstName'])) ? '' : $result['firstName'] : $result['user_firstname'];
    $customer_lastnameValue = (is_null($result['user_lastname'])) ? (is_null($result['lastName'])) ? '' : $result['lastName'] : $result['user_lastname'];
    if (is_null($result['user_firstname'])) {
	$customer_nameValue = '';
    } else {
	$customer_nameValue = $customer_firstnameValue . ' ' . $customer_lastnameValue;
    }
    $customer_nameValue = $customer_firstnameValue . ' ' . $customer_lastnameValue;
    //Lookup customer
    if ($result['user_email'] == NULL) {
	$customer_group_idValue = 0;
    } else {
	$customerQuery = "SELECT `entity_id` FROM `customer_entity` WHERE `email` = '" . $result['user_email'] . "'";
	$customerResults = $writeConnection->query($customerQuery);
	$customerFound = NULL;
	foreach ($customerResults as $customerResult) {  
	    $customerFound = 1;
	}
	if (!$customerFound) {
	    fwrite($transactionLogHandle, "    ->CUSTOMER NOT FOUND    : " . $result['yahooOrderIdNumeric'] . "\n");	    
	    $customer_group_idValue = 0;
	} else {
	    $customer_group_idValue = 1;
	}
    }
    
    $discount_amountValue = (is_null($result['discount'])) ? '0.0000' : $result['discount'];//appears to be actual total discount
    $grand_totalValue = (is_null($result['grandTotal'])) ? '0.0000' : $result['grandTotal'];
    $increment_idValue = $result['yahooOrderIdNumeric'];//import script adds value to 600000000
    $shipping_amountValue = (is_null($result['shippingCost'])) ? '0.0000' : $result['shippingCost'];
    $shipping_incl_taxValue = (is_null($result['shippingCost'])) ? '0.0000' : $result['shippingCost'];
    switch ($result['shippingMethod']) {
	case 'UPS Ground (3-7 Business Days)':
	    $shipping_methodValue = 'ups_03';
	    break;
	case 'APO & FPO Addresses (5-30 Business Days by USPS)':
	    $shipping_methodValue = 'usps_Priority Mail';
	    break;
	case 'UPS Next Day Air (2-3 Business Days)':
	    $shipping_methodValue = 'ups_01';
	    break;
	case '"Alaska, Hawaii, U.S. Virgin Islands & Puerto Rico':
	    $shipping_methodValue = 'usps_Priority Mail';
	    break;
	case 'UPS 2nd Day Air (3-4 Business Days)':
	    $shipping_methodValue = 'ups_02';
	    break;
	case 'International Express (Shipped with Shoebox)':
	    $shipping_methodValue = 'ups_08';
	    break;
	case 'International Express (Shipped without Shoebox)':
	    $shipping_methodValue = 'ups_08';
	    break;
	case 'USPS Priority Mail (4-5 Business Days)':
	    $shipping_methodValue = 'usps_Priority Mail';
	    break;
	case 'UPS 3 Day Select (4-5 Business Days)':
	    $shipping_methodValue = 'ups_12';
	    break;
	case 'EMS - International':
	    $shipping_methodValue = 'ups_08';
	    break;
	case 'Canada Express (4-7 Business Days)':
	    $shipping_methodValue = 'ups_08';
	    break;
	case 'EMS Canada':
	    $shipping_methodValue = 'ups_08';
	    break;
	case 'Christmas Express (Delivered by Dec. 24th)':
	    $shipping_methodValue = 'ups_03';
	    break;
	case 'COD Ground':
	    $shipping_methodValue = 'ups_03';
	    break;
	case 'COD Overnight':
	    $shipping_methodValue = 'ups_01';
	    break;
	case 'Free Christmas Express (Delivered by Dec. 24th)':
	    $shipping_methodValue = 'ups_03';
	    break;
	case 'New Year Express (Delivered by Dec. 31st)':
	    $shipping_methodValue = 'ups_03';
	    break;
	case 'Free UPS Ground (3-7 Business Days)':
	    $shipping_methodValue = 'ups_03';
	    break;
	case 'COD 2-Day':
	    $shipping_methodValue = 'ups_02';
	    break;
	case 'MSI International Express':
	    $shipping_methodValue = 'ups_08';
	    break;
	case 'Customer Pickup':
	    $shipping_methodValue = 'ups_03';
	    break;
	case 'UPS Ground':
	    $shipping_methodValue = 'ups_03';
	    break;
	case 'UPS 2nd Day Air':
	    $shipping_methodValue = 'ups_02';
	    break;
	case 'APO & FPO Addresses':
	    $shipping_methodValue = 'usps_Priority Mail';
	    break;
	case 'UPS Next Day Air Saver':
	    $shipping_methodValue = 'ups_01';
	    break;
	case 'UPS 3 Day Select':
	    $shipping_methodValue = 'ups_12';
	    break;
	case 'International Express':
	    $shipping_methodValue = 'ups_08';
	    break;
	case 'USPS Priority Mail':
	    $shipping_methodValue = 'usps_Priority Mail';
	    break;
	case 'Canada Express':
	    $shipping_methodValue = 'ups_08';
	    break;
	case 'UPS Next Day Air':
	    $shipping_methodValue = 'ups_01';
	    break;
	case 'Holiday Shipping (Delivered by Dec. 24th)':
	    $shipping_methodValue = 'ups_03';
	    break;
	default://case 'NULL'
	    $shipping_methodValue = '';
	    break;
    }
    
    $stateValue = 'new';//Always new -- will set on order status update
    $statusValue = 'pending';//Always Pending -- will set on order status update
    $subtotalValue = (is_null($result['subTotal'])) ? '0.0000' : $result['subTotal'];
    $subtotal_incl_taxValue = (is_null($result['subTotal'])) ? '0.0000' : $result['subTotal'];
    $tax_amountValue = (is_null($result['taxTotal'])) ? '0.0000' : $result['taxTotal'];
    $total_dueValue = (is_null($result['grandTotal'])) ? '0.0000' : $result['grandTotal'];

    // Get total qty
    $itemsQuery = "SELECT * FROM `se_orderitem` WHERE `yahooOrderIdNumeric` = " . $result['yahooOrderIdNumeric'];
    $itemsResult = $writeConnection->query($itemsQuery);
    $itemCount = 0;
    foreach ($itemsResult as $itemResult) {
	$itemCount += 1;//number of items not quantites
    }
    if ($itemCount == 0) {
	fwrite($transactionLogHandle, "      ->NO ITEMS FOUND      : " . $result['yahooOrderIdNumeric'] . "\n");
    }
    $total_qty_orderedValue = $itemCount . '.0000';//Derived from item qty count
    $updated_atValue = date("Y-m-d H:i:s");
    $updated_at_timestampValue = strtotime($real_created_atValue);
    $weightValue = '0.0000'; //No weight data available

    //Shipping
    $shippingCityValue = (is_null($result['shipCity'])) ? '' : $result['shipCity'];
    $shippingCountryValue = (is_null($result['shipCountry'])) ? '' : $result['shipCountry'];
    $shippingEmailValue = (is_null($result['email'])) ? '' : $result['email'];
    $shippingFirstnameValue = (is_null($result['shipName'])) ? '' : $result['shipName'];
    $shippingLastnameValue = '';
    $shippingNameValue = $result['shipName'];
    $shippingPostcodeValue = (is_null($result['shipZip'])) ? '' : $result['shipZip'];
    if (strtolower($shippingCountryValue) == 'united states') {
	$shippingRegionValue = (is_null($result['shipState'])) ? '' : strtoupper($result['shipState']);
    } else {
	$shippingRegionValue = (is_null($result['shipState'])) ? '' : $result['shipState'];
    }
    $shippingRegion_idValue = '';//Seems to work without conversion
    if ((!is_null($result['shipAddress1']) && $result['shipAddress1'] != '') && (is_null($result['shipAddress2']) || $result['shipAddress2'] == '')) {
	$shippingStreetValue = $result['shipAddress1'];
    } elseif ((!is_null($result['shipAddress1']) && $result['shipAddress1'] != '') && (!is_null($result['shipAddress2']) && $result['shipAddress2'] != '')) {
	$shippingStreetValue = $result['shipAddress2'] . '&#10;' . $result['shipAddress2']; //Include CR/LF
    } elseif ((is_null($result['shipAddress1']) || $result['shipAddress1'] == '') && (!is_null($result['shipAddress2']) && $result['shipAddress2'] != '')) {
	$shippingStreetValue = $result['shipAddress2'];
    } else {
	$shippingStreetValue = '';
    }
    $shippingTelephoneValue = (is_null($result['shipPhone'])) ? '' : $result['shipPhone'];
    
    //Billing
    $billingCityValue = (is_null($result['billCity'])) ? '' : $result['billCity'];
    $billingCountryValue = (is_null($result['billCountry'])) ? '' : $result['billCountry'];
    $billingEmailValue = (is_null($result['email'])) ? '' : $result['email'];
    $billingFirstnameValue = (is_null($result['billName'])) ? '' : $result['billName'];
    $billingLastnameValue = '';
    $billingNameValue = $result['billName'];
    $billingPostcodeValue = (is_null($result['billZip'])) ? '' : $result['billZip'];
    if (strtolower($billingCountryValue) == 'united states') {
	$billingRegionValue = (is_null($result['billState'])) ? '' : strtoupper($result['billState']);
    } else {
	$billingRegionValue = (is_null($result['billState'])) ? '' : $result['billState'];
    }
    $billingRegion_idValue = '';//Seems to work without conversion
    if ((!is_null($result['billAddress1']) && $result['billAddress1'] != '') && (is_null($result['billAddress2']) || $result['billAddress2'] == '')) {
	$billingStreetValue = $result['billAddress1'];
    } elseif ((!is_null($result['billAddress1']) && $result['billAddress1'] != '') && (!is_null($result['billAddress2']) && $result['billAddress2'] != '')) {
	$billingStreetValue = $result['billAddress2'] . '&#10;' . $result['billAddress2']; //Include CR/LF
    } elseif ((is_null($result['billAddress1']) || $result['billAddress1'] == '') && (!is_null($result['billAddress2']) && $result['billAddress2'] != '')) {
	$billingStreetValue = $result['billAddress2'];
    } else {
	$billingStreetValue = '';
    }
    $billingTelephoneValue = (is_null($result['billPhone'])) ? '' : $result['billPhone'];
    
    //Payment
    switch ($result['paymentType']) {
	case 'Visa':
	    $cc_typeValue = 'VI';
            $methodValue = 'authorizenet';
	    break;
	case 'AMEX':
	    $cc_typeValue = 'AE';
            $methodValue = 'authorizenet';
            break;
	case 'Mastercard':
	    $cc_typeValue = 'MC';
            $methodValue = 'authorizenet';
            break;
	case 'Discover':
	    $cc_typeValue = 'DI';
            $methodValue = 'authorizenet';
	    break;
	case 'Paypal':
	    $cc_typeValue = '';
            $methodValue = 'paypal_express';
	    break;
	case 'C.O.D.':
	    $cc_typeValue = '';
            $methodValue = 'free';
	    break;
	case 'GiftCert':
	    //100% payed with giftcard
	    $cc_typeValue = '';
            $methodValue = 'free';
	    break;
	default: //NULL
	    $cc_typeValue = '';
	    $methodValue = 'free';
    }
    $amount_authorizedValue = (is_null($result['grandTotal'])) ? '' : $result['grandTotal'];
    $amount_orderedValue = (is_null($result['grandTotal'])) ? '' : $result['grandTotal'];
    $base_amount_authorizedValue = (is_null($result['grandTotal'])) ? '' : $result['grandTotal'];
    $base_amount_orderedValue = (is_null($result['grandTotal'])) ? '' : $result['grandTotal'];
    $base_shipping_amountValue = (is_null($result['shippingCost'])) ? '' : $result['shippingCost'];
    $cc_approvalValue = (is_null($result['ccApprovalNumber'])) ? '' : $result['ccApprovalNumber'];
    $cc_cid_statusValue = (is_null($result['ccCvvResponse'])) ? '' : $result['ccCvvResponse'];
    $ccExpiration = (is_null($result['ccExpiration'])) ? '' : explode('/', $result['ccExpiration']);
    if (is_null($ccExpiration)) {
        $cc_exp_monthValue = '';
        $cc_exp_yearValue = '';
    } else {
        $cc_exp_monthValue = $ccExpiration[0];
        $cc_exp_yearValue = $ccExpiration[1];
    }
    $cc_last4Value = (is_null($result['ccExpiration'])) ? '' : '****';//data not available
    $anet_trans_methodValue = '';//***
    $cc_avs_statusValue = '';//***
    $cc_trans_idValue = '';//***
    $last_trans_idValue = '';//***
    $shipping_amountValue = (is_null($result['shippingCost'])) ? '' : $result['shippingCost'];

    $order = $orders->appendChild($dom->createElement('order'));

    $adjustment_negative = $order->appendChild($dom->createElement('adjustment_negative'));
    $adjustment_negative->appendChild($dom->createTextNode(''));
    $adjustment_positive = $order->appendChild($dom->createElement('adjustment_positive'));
    $adjustment_positive->appendChild($dom->createTextNode(''));
    $applied_rule_ids = $order->appendChild($dom->createElement('applied_rule_ids'));
    $applied_rule_ids->appendChild($dom->createTextNode(''));//none used -- only used for military until migration complete
    $base_adjustment_negative = $order->appendChild($dom->createElement('base_adjustment_negative'));
    $base_adjustment_negative->appendChild($dom->createTextNode(''));
    $base_adjustment_positive = $order->appendChild($dom->createElement('base_adjustment_positive'));
    $base_adjustment_positive->appendChild($dom->createTextNode(''));
    $base_currency_code = $order->appendChild($dom->createElement('base_currency_code'));
    $base_currency_code->appendChild($dom->createTextNode('USD'));// Always USD
    $base_custbalance_amount = $order->appendChild($dom->createElement('base_custbalance_amount'));
    $base_custbalance_amount->appendChild($dom->createTextNode(''));
    $base_discount_amount = $order->appendChild($dom->createElement('base_discount_amount'));
    $base_discount_amount->appendChild($dom->createTextNode($base_discount_amountValue));
    $base_discount_canceled = $order->appendChild($dom->createElement('base_discount_canceled'));
    $base_discount_canceled->appendChild($dom->createTextNode(''));
    $base_discount_invoiced = $order->appendChild($dom->createElement('base_discount_invoiced'));
    $base_discount_invoiced->appendChild($dom->createTextNode(''));
    $base_discount_refunded = $order->appendChild($dom->createElement('base_discount_refunded'));
    $base_discount_refunded->appendChild($dom->createTextNode(''));
    $base_grand_total = $order->appendChild($dom->createElement('base_grand_total'));
    $base_grand_total->appendChild($dom->createTextNode($base_grand_totalValue));
    $base_hidden_tax_amount = $order->appendChild($dom->createElement('base_hidden_tax_amount'));
    $base_hidden_tax_amount->appendChild($dom->createTextNode(''));
    $base_hidden_tax_invoiced = $order->appendChild($dom->createElement('base_hidden_tax_invoiced'));
    $base_hidden_tax_invoiced->appendChild($dom->createTextNode(''));
    $base_hidden_tax_refunded = $order->appendChild($dom->createElement('base_hidden_tax_refunded'));
    $base_hidden_tax_refunded->appendChild($dom->createTextNode(''));
    $base_shipping_amount = $order->appendChild($dom->createElement('base_shipping_amount'));
    $base_shipping_amount->appendChild($dom->createTextNode($base_shipping_amountValue));
    $base_shipping_canceled = $order->appendChild($dom->createElement('base_shipping_canceled'));
    $base_shipping_canceled->appendChild($dom->createTextNode(''));
    $base_shipping_discount_amount = $order->appendChild($dom->createElement('base_shipping_discount_amount'));
    $base_shipping_discount_amount->appendChild($dom->createTextNode('0.0000'));//Always 0
    $base_shipping_hidden_tax_amount = $order->appendChild($dom->createElement('base_shipping_hidden_tax_amount'));
    $base_shipping_hidden_tax_amount->appendChild($dom->createTextNode(''));
    $base_shipping_incl_tax = $order->appendChild($dom->createElement('base_shipping_incl_tax'));
    $base_shipping_incl_tax->appendChild($dom->createTextNode($base_shipping_incl_taxValue));
    $base_shipping_invoiced = $order->appendChild($dom->createElement('base_shipping_invoiced'));
    $base_shipping_invoiced->appendChild($dom->createTextNode(''));
    $base_shipping_refunded = $order->appendChild($dom->createElement('base_shipping_refunded'));
    $base_shipping_refunded->appendChild($dom->createTextNode(''));
    $base_shipping_tax_amount = $order->appendChild($dom->createElement('base_shipping_tax_amount'));
    $base_shipping_tax_amount->appendChild($dom->createTextNode('0.0000'));//Always 0
    $base_shipping_tax_refunded = $order->appendChild($dom->createElement('base_shipping_tax_refunded'));
    $base_shipping_tax_refunded->appendChild($dom->createTextNode(''));
    $base_subtotal = $order->appendChild($dom->createElement('base_subtotal'));
    $base_subtotal->appendChild($dom->createTextNode($base_subtotalValue));
    $base_subtotal_canceled = $order->appendChild($dom->createElement('base_subtotal_canceled'));
    $base_subtotal_canceled->appendChild($dom->createTextNode(''));
    $base_subtotal_incl_tax = $order->appendChild($dom->createElement('base_subtotal_incl_tax'));
    $base_subtotal_incl_tax->appendChild($dom->createTextNode($base_subtotal_incl_taxValue));
    $base_subtotal_invoiced = $order->appendChild($dom->createElement('base_subtotal_invoiced'));
    $base_subtotal_invoiced->appendChild($dom->createTextNode(''));
    $base_subtotal_refunded = $order->appendChild($dom->createElement('base_subtotal_refunded'));
    $base_subtotal_refunded->appendChild($dom->createTextNode(''));
    $base_tax_amount = $order->appendChild($dom->createElement('base_tax_amount'));
    $base_tax_amount->appendChild($dom->createTextNode($base_tax_amountValue));
    $base_tax_canceled = $order->appendChild($dom->createElement('base_tax_canceled'));
    $base_tax_canceled->appendChild($dom->createTextNode(''));
    $base_tax_invoiced = $order->appendChild($dom->createElement('base_tax_invoiced'));
    $base_tax_invoiced->appendChild($dom->createTextNode(''));
    $base_tax_refunded = $order->appendChild($dom->createElement('base_tax_refunded'));
    $base_tax_refunded->appendChild($dom->createTextNode(''));
    $base_to_global_rate = $order->appendChild($dom->createElement('base_to_global_rate'));
    $base_to_global_rate->appendChild($dom->createTextNode('1'));//Always 1
    $base_to_order_rate = $order->appendChild($dom->createElement('base_to_order_rate'));
    $base_to_order_rate->appendChild($dom->createTextNode('1'));//Always 1
    $base_total_canceled = $order->appendChild($dom->createElement('base_total_canceled'));
    $base_total_canceled->appendChild($dom->createTextNode('0.0000'));
    $base_total_due = $order->appendChild($dom->createElement('base_total_due'));
    $base_total_due->appendChild($dom->createTextNode($base_total_dueValue));
    $base_total_invoiced = $order->appendChild($dom->createElement('base_total_invoiced'));
    $base_total_invoiced->appendChild($dom->createTextNode('0.0000'));
    $base_total_invoiced_cost = $order->appendChild($dom->createElement('base_total_invoiced_cost'));
    $base_total_invoiced_cost->appendChild($dom->createTextNode(''));
    $base_total_offline_refunded = $order->appendChild($dom->createElement('base_total_offline_refunded'));
    $base_total_offline_refunded->appendChild($dom->createTextNode('0.0000'));
    $base_total_online_refunded = $order->appendChild($dom->createElement('base_total_online_refunded'));
    $base_total_online_refunded->appendChild($dom->createTextNode('0.0000'));
    $base_total_paid = $order->appendChild($dom->createElement('base_total_paid'));
    $base_total_paid->appendChild($dom->createTextNode('0.0000'));
    $base_total_qty_ordered = $order->appendChild($dom->createElement('base_total_qty_ordered'));
    $base_total_qty_ordered->appendChild($dom->createTextNode(''));//Always NULL
    $base_total_refunded = $order->appendChild($dom->createElement('base_total_refunded'));
    $base_total_refunded->appendChild($dom->createTextNode('0.0000'));
    $can_ship_partially = $order->appendChild($dom->createElement('can_ship_partially'));
    $can_ship_partially->appendChild($dom->createTextNode(''));
    $can_ship_partially_item = $order->appendChild($dom->createElement('can_ship_partially_item'));
    $can_ship_partially_item->appendChild($dom->createTextNode(''));
    $coupon_code = $order->appendChild($dom->createElement('coupon_code'));
    $coupon_code->appendChild($dom->createTextNode(''));
    $real_created_at = $order->appendChild($dom->createElement('real_created_at'));
    $real_created_at->appendChild($dom->createTextNode($real_created_atValue));
    $created_at_timestamp = $order->appendChild($dom->createElement('created_at_timestamp'));
    $created_at_timestamp->appendChild($dom->createTextNode($created_at_timestampValue));
    $custbalance_amount = $order->appendChild($dom->createElement('custbalance_amount'));
    $custbalance_amount->appendChild($dom->createTextNode(''));
    $customer_dob = $order->appendChild($dom->createElement('customer_dob'));
    $customer_dob->appendChild($dom->createTextNode(''));
    $customer_email = $order->appendChild($dom->createElement('customer_email'));
    $customer_email->appendChild($dom->createTextNode($customer_emailValue));
    $customer_firstname = $order->appendChild($dom->createElement('customer_firstname'));
    $customer_firstname->appendChild($dom->createTextNode($customer_firstnameValue));
    $customer_gender = $order->appendChild($dom->createElement('customer_gender'));
    $customer_gender->appendChild($dom->createTextNode(''));
    $customer_group_id = $order->appendChild($dom->createElement('customer_group_id'));
    $customer_group_id->appendChild($dom->createTextNode($customer_group_idValue));
    $customer_lastname = $order->appendChild($dom->createElement('customer_lastname'));
    $customer_lastname->appendChild($dom->createTextNode($customer_lastnameValue));
    $customer_middlename = $order->appendChild($dom->createElement('customer_middlename'));
    $customer_middlename->appendChild($dom->createTextNode(''));
    $customer_name = $order->appendChild($dom->createElement('customer_name'));
    $customer_name->appendChild($dom->createTextNode($customer_nameValue));
    $customer_note = $order->appendChild($dom->createElement('customer_note'));
    $customer_note->appendChild($dom->createTextNode(''));
    $customer_note_notify = $order->appendChild($dom->createElement('customer_note_notify'));
    $customer_note_notify->appendChild($dom->createTextNode('1'));
    $customer_prefix = $order->appendChild($dom->createElement('customer_prefix'));
    $customer_prefix->appendChild($dom->createTextNode(''));
    $customer_suffix = $order->appendChild($dom->createElement('customer_suffix'));
    $customer_suffix->appendChild($dom->createTextNode(''));
    $customer_taxvat = $order->appendChild($dom->createElement('customer_taxvat'));
    $customer_taxvat->appendChild($dom->createTextNode(''));
    $discount_amount = $order->appendChild($dom->createElement('discount_amount'));
    $discount_amount->appendChild($dom->createTextNode($discount_amountValue));
    $discount_canceled = $order->appendChild($dom->createElement('discount_canceled'));
    $discount_canceled->appendChild($dom->createTextNode(''));
    $discount_invoiced = $order->appendChild($dom->createElement('discount_invoiced'));
    $discount_invoiced->appendChild($dom->createTextNode(''));
    $discount_refunded = $order->appendChild($dom->createElement('discount_refunded'));
    $discount_refunded->appendChild($dom->createTextNode(''));
    $email_sent = $order->appendChild($dom->createElement('email_sent'));
    $email_sent->appendChild($dom->createTextNode('1'));//Always 1
    $ext_customer_id = $order->appendChild($dom->createElement('ext_customer_id'));
    $ext_customer_id->appendChild($dom->createTextNode(''));
    $ext_order_id = $order->appendChild($dom->createElement('ext_order_id'));
    $ext_order_id->appendChild($dom->createTextNode(''));
    $forced_do_shipment_with_invoice = $order->appendChild($dom->createElement('forced_do_shipment_with_invoice'));
    $forced_do_shipment_with_invoice->appendChild($dom->createTextNode(''));
    $global_currency_code = $order->appendChild($dom->createElement('global_currency_code'));
    $global_currency_code->appendChild($dom->createTextNode('USD'));
    $grand_total = $order->appendChild($dom->createElement('grand_total'));
    $grand_total->appendChild($dom->createTextNode($grand_totalValue));
    $hidden_tax_amount = $order->appendChild($dom->createElement('hidden_tax_amount'));
    $hidden_tax_amount->appendChild($dom->createTextNode(''));
    $hidden_tax_invoiced = $order->appendChild($dom->createElement('hidden_tax_invoiced'));
    $hidden_tax_invoiced->appendChild($dom->createTextNode(''));
    $hidden_tax_refunded = $order->appendChild($dom->createElement('hidden_tax_refunded'));
    $hidden_tax_refunded->appendChild($dom->createTextNode(''));
    $hold_before_state = $order->appendChild($dom->createElement('hold_before_state'));
    $hold_before_state->appendChild($dom->createTextNode(''));
    $hold_before_status = $order->appendChild($dom->createElement('hold_before_status'));
    $hold_before_status->appendChild($dom->createTextNode(''));
    $increment_id = $order->appendChild($dom->createElement('increment_id'));
    $increment_id->appendChild($dom->createTextNode($increment_idValue));
    $is_hold = $order->appendChild($dom->createElement('is_hold'));
    $is_hold->appendChild($dom->createTextNode(''));
    $is_multi_payment = $order->appendChild($dom->createElement('is_multi_payment'));
    $is_multi_payment->appendChild($dom->createTextNode(''));
    $is_virtual = $order->appendChild($dom->createElement('is_virtual'));
    $is_virtual->appendChild($dom->createTextNode('0'));//Always 0
    $order_currency_code = $order->appendChild($dom->createElement('order_currency_code'));
    $order_currency_code->appendChild($dom->createTextNode('USD'));
    $payment_authorization_amount = $order->appendChild($dom->createElement('payment_authorization_amount'));
    $payment_authorization_amount->appendChild($dom->createTextNode(''));
    $payment_authorization_expiration = $order->appendChild($dom->createElement('payment_authorization_expiration'));
    $payment_authorization_expiration->appendChild($dom->createTextNode(''));
    $paypal_ipn_customer_notified = $order->appendChild($dom->createElement('paypal_ipn_customer_notified'));
    $paypal_ipn_customer_notified->appendChild($dom->createTextNode(''));
    $real_order_id = $order->appendChild($dom->createElement('real_order_id'));
    $real_order_id->appendChild($dom->createTextNode(''));
    $remote_ip = $order->appendChild($dom->createElement('remote_ip'));
    $remote_ip->appendChild($dom->createTextNode(''));
    $shipping_amount = $order->appendChild($dom->createElement('shipping_amount'));
    $shipping_amount->appendChild($dom->createTextNode($shipping_amountValue));
    $shipping_canceled = $order->appendChild($dom->createElement('shipping_canceled'));
    $shipping_canceled->appendChild($dom->createTextNode(''));
    $shipping_discount_amount = $order->appendChild($dom->createElement('shipping_discount_amount'));
    $shipping_discount_amount->appendChild($dom->createTextNode('0.0000'));
    $shipping_hidden_tax_amount = $order->appendChild($dom->createElement('shipping_hidden_tax_amount'));
    $shipping_hidden_tax_amount->appendChild($dom->createTextNode(''));
    $shipping_incl_tax = $order->appendChild($dom->createElement('shipping_incl_tax'));
    $shipping_incl_tax->appendChild($dom->createTextNode($shipping_incl_taxValue));
    $shipping_invoiced = $order->appendChild($dom->createElement('shipping_invoiced'));
    $shipping_invoiced->appendChild($dom->createTextNode(''));
    $shipping_method = $order->appendChild($dom->createElement('shipping_method'));
    $shipping_method->appendChild($dom->createTextNode($shipping_methodValue));
    $shipping_refunded = $order->appendChild($dom->createElement('shipping_refunded'));
    $shipping_refunded->appendChild($dom->createTextNode(''));
    $shipping_tax_amount = $order->appendChild($dom->createElement('shipping_tax_amount'));
    $shipping_tax_amount->appendChild($dom->createTextNode('0.0000'));
    $shipping_tax_refunded = $order->appendChild($dom->createElement('shipping_tax_refunded'));
    $shipping_tax_refunded->appendChild($dom->createTextNode(''));
    $state = $order->appendChild($dom->createElement('state'));
    $state->appendChild($dom->createTextNode($stateValue));
    $status = $order->appendChild($dom->createElement('status'));
    $status->appendChild($dom->createTextNode($statusValue));
    $store = $order->appendChild($dom->createElement('store'));
    $store->appendChild($dom->createTextNode('sneakerhead_cn'));
    $subtotal = $order->appendChild($dom->createElement('subTotal'));
    $subtotal->appendChild($dom->createTextNode($subtotalValue));
    $subtotal_canceled = $order->appendChild($dom->createElement('subtotal_canceled'));
    $subtotal_canceled->appendChild($dom->createTextNode(''));
    $subtotal_incl_tax = $order->appendChild($dom->createElement('subtotal_incl_tax'));
    $subtotal_incl_tax->appendChild($dom->createTextNode($subtotal_incl_taxValue));
    $subtotal_invoiced = $order->appendChild($dom->createElement('subtotal_invoiced'));
    $subtotal_invoiced->appendChild($dom->createTextNode(''));
    $subtotal_refunded = $order->appendChild($dom->createElement('subtotal_refunded'));
    $subtotal_refunded->appendChild($dom->createTextNode(''));
    $tax_amount = $order->appendChild($dom->createElement('tax_amount'));
    $tax_amount->appendChild($dom->createTextNode($tax_amountValue));
    $tax_canceled = $order->appendChild($dom->createElement('tax_canceled'));
    $tax_canceled->appendChild($dom->createTextNode(''));
    $tax_invoiced = $order->appendChild($dom->createElement('tax_invoiced'));
    $tax_invoiced->appendChild($dom->createTextNode(''));
    $tax_percent = $order->appendChild($dom->createElement('tax_percent'));
    $tax_percent->appendChild($dom->createTextNode($tax_percentValue));
    $tax_refunded = $order->appendChild($dom->createElement('tax_refunded'));
    $tax_refunded->appendChild($dom->createTextNode(''));
    $total_canceled = $order->appendChild($dom->createElement('total_canceled'));
    $total_canceled->appendChild($dom->createTextNode('0.0000'));
    $total_due = $order->appendChild($dom->createElement('total_due'));
    $total_due->appendChild($dom->createTextNode($total_dueValue));
    $total_invoiced = $order->appendChild($dom->createElement('total_invoiced'));
    $total_invoiced->appendChild($dom->createTextNode('0.0000'));
    $total_item_count = $order->appendChild($dom->createElement('total_item_count'));
    $total_item_count->appendChild($dom->createTextNode(''));
    $total_offline_refunded = $order->appendChild($dom->createElement('total_offline_refunded'));
    $total_offline_refunded->appendChild($dom->createTextNode('0.0000'));
    $total_online_refunded = $order->appendChild($dom->createElement('total_online_refunded'));
    $total_online_refunded->appendChild($dom->createTextNode('0.0000'));
    $total_paid = $order->appendChild($dom->createElement('total_paid'));
    $total_paid->appendChild($dom->createTextNode('0.0000'));
    $total_qty_ordered = $order->appendChild($dom->createElement('total_qty_ordered'));
    $total_qty_ordered->appendChild($dom->createTextNode($total_qty_orderedValue));
    $total_refunded = $order->appendChild($dom->createElement('total_refunded'));
    $total_refunded->appendChild($dom->createTextNode('0.0000'));
    $tracking_numbers = $order->appendChild($dom->createElement('tracking_numbers'));
    $tracking_numbers->appendChild($dom->createTextNode(''));
    $updated_at = $order->appendChild($dom->createElement('updated_at'));
    $updated_at->appendChild($dom->createTextNode($updated_atValue));
    $updated_at_timestamp = $order->appendChild($dom->createElement('updated_at_timestamp'));
    $updated_at_timestamp->appendChild($dom->createTextNode($updated_at_timestampValue));
    $weight = $order->appendChild($dom->createElement('weight'));
    $weight->appendChild($dom->createTextNode($weightValue));
    $x_forwarded_for = $order->appendChild($dom->createElement('x_forwarded_for'));
    $x_forwarded_for->appendChild($dom->createTextNode(''));

    //Build shipping
    $shipping_address = $order->appendChild($dom->createElement('shipping_address'));
    
    $shippingCity = $shipping_address->appendChild($dom->createElement('city'));
    $shippingCity->appendChild($dom->createTextNode($shippingCityValue));
    $shippingCompany = $shipping_address->appendChild($dom->createElement('company'));
    $shippingCompany->appendChild($dom->createTextNode(''));
    $shippingCountry = $shipping_address->appendChild($dom->createElement('country'));
    $shippingCountry->appendChild($dom->createTextNode($shippingCountryValue));
    $shippingCountry_id = $shipping_address->appendChild($dom->createElement('country_id'));
    $shippingCountry_id->appendChild($dom->createTextNode(''));
    $shippingCountry_iso2 = $shipping_address->appendChild($dom->createElement('country_iso2'));
    $shippingCountry_iso2->appendChild($dom->createTextNode(''));
    $shippingCountry_iso3 = $shipping_address->appendChild($dom->createElement('country_iso3'));
    $shippingCountry_iso3->appendChild($dom->createTextNode(''));
    $shippingEmail = $shipping_address->appendChild($dom->createElement('email'));
    $shippingEmail->appendChild($dom->createTextNode($shippingEmailValue));
    $shippingFax = $shipping_address->appendChild($dom->createElement('fax'));
    $shippingFax->appendChild($dom->createTextNode(''));
    $shippingFirstname = $shipping_address->appendChild($dom->createElement('firstname'));
    $shippingFirstname->appendChild($dom->createTextNode($shippingFirstnameValue));
    $shippingLastname = $shipping_address->appendChild($dom->createElement('lastname'));
    $shippingLastname->appendChild($dom->createTextNode($shippingLastnameValue));
    $shippingMiddlename = $shipping_address->appendChild($dom->createElement('middlename'));
    $shippingMiddlename->appendChild($dom->createTextNode(''));
    $shippingName = $shipping_address->appendChild($dom->createElement('name'));
    $shippingName->appendChild($dom->createTextNode($shippingNameValue));
    $shippingPostcode = $shipping_address->appendChild($dom->createElement('postcode'));
    $shippingPostcode->appendChild($dom->createTextNode($shippingPostcodeValue));
    $shippingPrefix = $shipping_address->appendChild($dom->createElement('prefix'));
    $shippingPrefix->appendChild($dom->createTextNode(''));
    $shippingRegion = $shipping_address->appendChild($dom->createElement('region'));
    $shippingRegion->appendChild($dom->createTextNode($shippingRegionValue));
    $shippingRegion_id = $shipping_address->appendChild($dom->createElement('region_id'));
    $shippingRegion_id->appendChild($dom->createTextNode($shippingRegion_idValue));
    $shippingRegion_iso2 = $shipping_address->appendChild($dom->createElement('region_iso2'));
    $shippingRegion_iso2->appendChild($dom->createTextNode(''));
    $shippingStreet = $shipping_address->appendChild($dom->createElement('street'));
    $shippingStreet->appendChild($dom->createTextNode($shippingStreetValue));
    $shippingSuffix = $shipping_address->appendChild($dom->createElement('suffix'));
    $shippingSuffix->appendChild($dom->createTextNode(''));
    $shippingTelephone = $shipping_address->appendChild($dom->createElement('telephone'));
    $shippingTelephone->appendChild($dom->createTextNode($shippingTelephoneValue));

    // Build billing
    $billing_address = $order->appendChild($dom->createElement('billing_address'));
    
    $billingCity = $billing_address->appendChild($dom->createElement('city'));
    $billingCity->appendChild($dom->createTextNode($billingCityValue));
    $billingCompany = $billing_address->appendChild($dom->createElement('company'));
    $billingCompany->appendChild($dom->createTextNode(''));
    $billingCountry = $billing_address->appendChild($dom->createElement('country'));
    $billingCountry->appendChild($dom->createTextNode($billingCountryValue));
    $billingCountry_id = $billing_address->appendChild($dom->createElement('country_id'));
    $billingCountry_id->appendChild($dom->createTextNode(''));
    $billingCountry_iso2 = $billing_address->appendChild($dom->createElement('country_iso2'));
    $billingCountry_iso2->appendChild($dom->createTextNode(''));
    $billingCountry_iso3 = $billing_address->appendChild($dom->createElement('country_iso3'));
    $billingCountry_iso3->appendChild($dom->createTextNode(''));
    $billingEmail = $billing_address->appendChild($dom->createElement('email'));
    $billingEmail->appendChild($dom->createTextNode($billingEmailValue));
    $billingFax = $billing_address->appendChild($dom->createElement('fax'));
    $billingFax->appendChild($dom->createTextNode(''));
    $billingFirstname = $billing_address->appendChild($dom->createElement('firstname'));
    $billingFirstname->appendChild($dom->createTextNode($billingFirstnameValue));
    $billingLastname = $billing_address->appendChild($dom->createElement('lastname'));
    $billingLastname->appendChild($dom->createTextNode($billingLastnameValue));
    $billingMiddlename = $billing_address->appendChild($dom->createElement('middlename'));
    $billingMiddlename->appendChild($dom->createTextNode(''));
    $billingName = $billing_address->appendChild($dom->createElement('name'));
    $billingName->appendChild($dom->createTextNode($billingNameValue));
    $billingPostcode = $billing_address->appendChild($dom->createElement('postcode'));
    $billingPostcode->appendChild($dom->createTextNode($billingPostcodeValue));
    $billingPrefix = $billing_address->appendChild($dom->createElement('prefix'));
    $billingPrefix->appendChild($dom->createTextNode(''));
    $billingRegion = $billing_address->appendChild($dom->createElement('region'));
    $billingRegion->appendChild($dom->createTextNode($billingRegionValue));
    $billingRegion_id = $billing_address->appendChild($dom->createElement('region_id'));
    $billingRegion_id->appendChild($dom->createTextNode($billingRegion_idValue));
    $billingRegion_iso2 = $billing_address->appendChild($dom->createElement('region_iso2'));
    $billingRegion_iso2->appendChild($dom->createTextNode(''));
    $billingStreet = $billing_address->appendChild($dom->createElement('street'));
    $billingStreet->appendChild($dom->createTextNode($billingStreetValue));
    $billingSuffix = $billing_address->appendChild($dom->createElement('suffix'));
    $billingSuffix->appendChild($dom->createTextNode(''));
    $billingTelephone = $billing_address->appendChild($dom->createElement('telephone'));
    $billingTelephone->appendChild($dom->createTextNode($billingTelephoneValue));
    
    // Build payment

    $payment = $order->appendChild($dom->createElement('payment'));

    $account_status = $payment->appendChild($dom->createElement('account_status'));
    $account_status->appendChild($dom->createTextNode(''));
    $address_status = $payment->appendChild($dom->createElement('address_status'));
    $address_status->appendChild($dom->createTextNode(''));
    $amount = $payment->appendChild($dom->createElement('amount'));
    $amount->appendChild($dom->createTextNode(''));
    $amount_authorized = $payment->appendChild($dom->createElement('amount_authorized'));
    $amount_authorized->appendChild($dom->createTextNode($amount_authorizedValue));
    $amount_canceled = $payment->appendChild($dom->createElement('amount_canceled'));
    $amount_canceled->appendChild($dom->createTextNode(''));
    $amount_ordered = $payment->appendChild($dom->createElement('amount_ordered'));
    $amount_ordered->appendChild($dom->createTextNode($amount_orderedValue));
    $amount_paid = $payment->appendChild($dom->createElement('amount_paid'));
    $amount_paid->appendChild($dom->createTextNode(''));
    $amount_refunded = $payment->appendChild($dom->createElement('amount_refunded'));
    $amount_refunded->appendChild($dom->createTextNode(''));
    $anet_trans_method = $payment->appendChild($dom->createElement('anet_trans_method'));
    $anet_trans_method->appendChild($dom->createTextNode($anet_trans_methodValue));
    $base_amount_authorized = $payment->appendChild($dom->createElement('base_amount_authorized'));
    $base_amount_authorized->appendChild($dom->createTextNode($base_amount_authorizedValue));
    $base_amount_canceled = $payment->appendChild($dom->createElement('base_amount_canceled'));
    $base_amount_canceled->appendChild($dom->createTextNode(''));
    $base_amount_ordered = $payment->appendChild($dom->createElement('base_amount_ordered'));
    $base_amount_ordered->appendChild($dom->createTextNode($base_amount_orderedValue));
    $base_amount_paid = $payment->appendChild($dom->createElement('base_amount_paid'));
    $base_amount_paid->appendChild($dom->createTextNode(''));
    $base_amount_paid_online = $payment->appendChild($dom->createElement('base_amount_paid_online'));
    $base_amount_paid_online->appendChild($dom->createTextNode(''));
    $base_amount_refunded = $payment->appendChild($dom->createElement('base_amount_refunded'));
    $base_amount_refunded->appendChild($dom->createTextNode(''));
    $base_amount_refunded_online = $payment->appendChild($dom->createElement('base_amount_refunded_online'));
    $base_amount_refunded_online->appendChild($dom->createTextNode(''));
    $base_shipping_amount = $payment->appendChild($dom->createElement('base_shipping_amount'));
    $base_shipping_amount->appendChild($dom->createTextNode($base_shipping_amountValue));
    $base_shipping_captured = $payment->appendChild($dom->createElement('base_shipping_captured'));
    $base_shipping_captured->appendChild($dom->createTextNode(''));
    $base_shipping_refunded = $payment->appendChild($dom->createElement('base_shipping_refunded'));
    $base_shipping_refunded->appendChild($dom->createTextNode(''));
    $cc_approval = $payment->appendChild($dom->createElement('cc_approval'));
    $cc_approval->appendChild($dom->createTextNode($cc_approvalValue));
    $cc_avs_status = $payment->appendChild($dom->createElement('cc_avs_status'));
    $cc_avs_status->appendChild($dom->createTextNode($cc_avs_statusValue));
    $cc_cid_status = $payment->appendChild($dom->createElement('cc_cid_status'));
    $cc_cid_status->appendChild($dom->createTextNode($cc_cid_statusValue));
    $cc_debug_request_body = $payment->appendChild($dom->createElement('cc_debug_request_body'));
    $cc_debug_request_body->appendChild($dom->createTextNode(''));
    $cc_debug_response_body = $payment->appendChild($dom->createElement('cc_debug_response_body'));
    $cc_debug_response_body->appendChild($dom->createTextNode(''));
    $cc_debug_response_serialized = $payment->appendChild($dom->createElement('cc_debug_response_serialized'));
    $cc_debug_response_serialized->appendChild($dom->createTextNode(''));
    $cc_exp_month = $payment->appendChild($dom->createElement('cc_exp_month'));
    $cc_exp_month->appendChild($dom->createTextNode($cc_exp_monthValue));
    $cc_exp_year = $payment->appendChild($dom->createElement('cc_exp_year'));
    $cc_exp_year->appendChild($dom->createTextNode($cc_exp_yearValue));
    $cc_last4 = $payment->appendChild($dom->createElement('cc_last4'));
    $cc_last4->appendChild($dom->createTextNode($cc_last4Value));
    $cc_number_enc = $payment->appendChild($dom->createElement('cc_number_enc'));
    $cc_number_enc->appendChild($dom->createTextNode(''));
    $cc_owner = $payment->appendChild($dom->createElement('cc_owner'));
    $cc_owner->appendChild($dom->createTextNode(''));
    $cc_raw_request = $payment->appendChild($dom->createElement('cc_raw_request'));
    $cc_raw_request->appendChild($dom->createTextNode(''));
    $cc_raw_response = $payment->appendChild($dom->createElement('cc_raw_response'));
    $cc_raw_response->appendChild($dom->createTextNode(''));
    $cc_secure_verify = $payment->appendChild($dom->createElement('cc_secure_verify'));
    $cc_secure_verify->appendChild($dom->createTextNode(''));
    $cc_ss_issue = $payment->appendChild($dom->createElement('cc_ss_issue'));
    $cc_ss_issue->appendChild($dom->createTextNode(''));
    $cc_ss_start_month = $payment->appendChild($dom->createElement('cc_ss_start_month'));
    $cc_ss_start_month->appendChild($dom->createTextNode('0'));//appears to be 0 since not used
    $cc_ss_start_year = $payment->appendChild($dom->createElement('cc_ss_start_year'));
    $cc_ss_start_year->appendChild($dom->createTextNode('0'));//appears to be 0 since not used
    $cc_status = $payment->appendChild($dom->createElement('cc_status'));
    $cc_status->appendChild($dom->createTextNode(''));
    $cc_status_description = $payment->appendChild($dom->createElement('cc_status_description'));
    $cc_status_description->appendChild($dom->createTextNode(''));
    $cc_trans_id = $payment->appendChild($dom->createElement('cc_trans_id'));
    $cc_trans_id->appendChild($dom->createTextNode($cc_trans_idValue));
    $cc_type = $payment->appendChild($dom->createElement('cc_type'));
    $cc_type->appendChild($dom->createTextNode($cc_typeValue));
    $cybersource_token = $payment->appendChild($dom->createElement('cybersource_token'));
    $cybersource_token->appendChild($dom->createTextNode(''));
    $echeck_account_name = $payment->appendChild($dom->createElement('echeck_account_name'));
    $echeck_account_name->appendChild($dom->createTextNode(''));
    $echeck_account_type = $payment->appendChild($dom->createElement('echeck_account_type'));
    $echeck_account_type->appendChild($dom->createTextNode(''));
    $echeck_bank_name = $payment->appendChild($dom->createElement('echeck_bank_name'));
    $echeck_bank_name->appendChild($dom->createTextNode(''));
    $echeck_routing_number = $payment->appendChild($dom->createElement('echeck_routing_number'));
    $echeck_routing_number->appendChild($dom->createTextNode(''));
    $echeck_type = $payment->appendChild($dom->createElement('echeck_type'));
    $echeck_type->appendChild($dom->createTextNode(''));
    $flo2cash_account_id = $payment->appendChild($dom->createElement('flo2cash_account_id'));
    $flo2cash_account_id->appendChild($dom->createTextNode(''));
    $ideal_issuer_id = $payment->appendChild($dom->createElement('ideal_issuer_id'));
    $ideal_issuer_id->appendChild($dom->createTextNode(''));
    $ideal_issuer_title = $payment->appendChild($dom->createElement('ideal_issuer_title'));
    $ideal_issuer_title->appendChild($dom->createTextNode(''));
    $ideal_transaction_checked = $payment->appendChild($dom->createElement('ideal_transaction_checked'));
    $ideal_transaction_checked->appendChild($dom->createTextNode(''));
    $last_trans_id = $payment->appendChild($dom->createElement('last_trans_id'));
    $last_trans_id->appendChild($dom->createTextNode($last_trans_idValue));
    $method = $payment->appendChild($dom->createElement('method'));
    $method->appendChild($dom->createTextNode($methodValue));
    $paybox_question_number = $payment->appendChild($dom->createElement('paybox_question_number'));
    $paybox_question_number->appendChild($dom->createTextNode(''));
    $paybox_request_number = $payment->appendChild($dom->createElement('paybox_request_number'));
    $paybox_request_number->appendChild($dom->createTextNode(''));
    $po_number = $payment->appendChild($dom->createElement('po_number'));
    $po_number->appendChild($dom->createTextNode(''));
    $protection_eligibility = $payment->appendChild($dom->createElement('protection_eligibility'));
    $protection_eligibility->appendChild($dom->createTextNode(''));
    $shipping_amount = $payment->appendChild($dom->createElement('shipping_amount'));
    $shipping_amount->appendChild($dom->createTextNode($shipping_amountValue));
    $shipping_captured = $payment->appendChild($dom->createElement('shipping_captured'));
    $shipping_captured->appendChild($dom->createTextNode(''));
    $shipping_refunded = $payment->appendChild($dom->createElement('shipping_refunded'));
    $shipping_refunded->appendChild($dom->createTextNode(''));

    // Build Items
    $items = $order->appendChild($dom->createElement('items'));
    $itemsQuery = "SELECT * FROM `se_orderitem` WHERE `yahooOrderIdNumeric` = " . $result['yahooOrderIdNumeric'];
    $itemsResult = $writeConnection->query($itemsQuery);
    $itemNumber = 1;
    foreach ($itemsResult as $itemResult) {
        $item = $items->appendChild($dom->createElement('item'));

	//Set variables
	$base_original_priceValue = $itemResult['unitPrice'];
	$base_priceValue = $itemResult['unitPrice'];
	$base_row_totalValue = $itemResult['qtyOrdered'] * $itemResult['unitPrice'];
	$real_nameValue = $itemResult['lineItemDescription'];
	$nameValue = $itemResult['lineItemDescription'];
	$original_priceValue = $itemResult['unitPrice'];
	$priceValue = $itemResult['unitPrice'];
	$qty_orderedValue = $itemResult['qtyOrdered'];
	$row_totalValue = $itemResult['qtyOrdered'] * $itemResult['unitPrice'];
	$length = strlen(end(explode('-', $itemResult['productCode'])));
	$real_skuValue = substr($itemResult['productCode'], 0, -($length + 1));
        
	fwrite($transactionLogHandle, "      ->ADDING CONFIGURABLE : " . $itemNumber . " -> " . $real_skuValue . "\n");

	$skuValue = 'Product ' . $itemNumber;
	if (!is_null($result['shipCountry']) && $result['shipState'] == 'CA') {
	    if (strtolower($result['shipCountry']) == 'united states') {
		$tax_percentCalcValue = '0.0875';
		$tax_percentValue = '8.75';
		$base_price_incl_taxValue = round($priceValue + ($priceValue * $tax_percentCalcValue), 4);//
		$base_row_total_incl_taxValue = round($qty_orderedValue * ($priceValue + ($priceValue * $tax_percentCalcValue)), 4);//
		$base_tax_amountValue = round($priceValue * $tax_percentCalcValue, 4);//THIS MAY BE WRONG -- QTY or ONE
		$price_incl_taxValue = round($priceValue + ($priceValue * $tax_percentCalcValue), 4);//
		$row_total_incl_taxValue = round($qty_orderedValue * ($priceValue + ($priceValue * $tax_percentCalcValue)), 4);//
		$tax_amountValue = round($priceValue * $tax_percentCalcValue, 4);//
	    } else {
		$tax_percentValue = '0.00';
		$base_price_incl_taxValue = $priceValue;
		$base_row_total_incl_taxValue = $qty_orderedValue * $priceValue;
		$base_tax_amountValue = '0.00';
		$price_incl_taxValue = $priceValue;
		$row_total_incl_taxValue = $qty_orderedValue * $priceValue;
		$tax_amountValue = '0.00';		
	    }
	} else {
	    $tax_percentValue = '0.00';
	    $base_price_incl_taxValue = $priceValue;
	    $base_row_total_incl_taxValue = $qty_orderedValue * $priceValue;
	    $base_tax_amountValue = '0.00';
	    $price_incl_taxValue = $priceValue;
	    $row_total_incl_taxValue = $qty_orderedValue * $priceValue;
	    $tax_amountValue = '0.00';	
	}

	//Create line item
	$amount_refunded = $item->appendChild($dom->createElement('amount_refunded'));
	$amount_refunded->appendChild($dom->createTextNode('0')); //Always 0
	$applied_rule_ids = $item->appendChild($dom->createElement('applied_rule_ids'));
	$applied_rule_ids->appendChild($dom->createTextNode(''));
	$base_amount_refunded = $item->appendChild($dom->createElement('base_amount_refunded'));
	$base_amount_refunded->appendChild($dom->createTextNode('0')); //Always 0
	$base_cost = $item->appendChild($dom->createElement('base_cost'));
	$base_cost->appendChild($dom->createTextNode(''));
	$base_discount_amount = $item->appendChild($dom->createElement('base_discount_amount'));
	$base_discount_amount->appendChild($dom->createTextNode('0')); //Always 0
	$base_discount_invoiced = $item->appendChild($dom->createElement('base_discount_invoiced'));
	$base_discount_invoiced->appendChild($dom->createTextNode('0')); //Always 0
	$base_hidden_tax_amount = $item->appendChild($dom->createElement('base_hidden_tax_amount'));
	$base_hidden_tax_amount->appendChild($dom->createTextNode(''));
	$base_hidden_tax_invoiced = $item->appendChild($dom->createElement('base_hidden_tax_invoiced'));
	$base_hidden_tax_invoiced->appendChild($dom->createTextNode(''));
	$base_hidden_tax_refunded = $item->appendChild($dom->createElement('base_hidden_tax_refunded'));
	$base_hidden_tax_refunded->appendChild($dom->createTextNode(''));
	$base_original_price = $item->appendChild($dom->createElement('base_original_price'));
	$base_original_price->appendChild($dom->createTextNode($base_original_priceValue));
	$base_price = $item->appendChild($dom->createElement('base_price'));
	$base_price->appendChild($dom->createTextNode($base_priceValue));
	$base_price_incl_tax = $item->appendChild($dom->createElement('base_price_incl_tax'));
	$base_price_incl_tax->appendChild($dom->createTextNode($base_price_incl_taxValue));
	$base_row_invoiced = $item->appendChild($dom->createElement('base_row_invoiced'));
	$base_row_invoiced->appendChild($dom->createTextNode('0'));
	$base_row_total = $item->appendChild($dom->createElement('base_row_total'));
	$base_row_total->appendChild($dom->createTextNode($base_row_totalValue));
	$base_row_total_incl_tax = $item->appendChild($dom->createElement('base_row_total_incl_tax'));
	$base_row_total_incl_tax->appendChild($dom->createTextNode($base_row_total_incl_taxValue));
	$base_tax_amount = $item->appendChild($dom->createElement('base_tax_amount'));
	$base_tax_amount->appendChild($dom->createTextNode($base_tax_amountValue));
	$base_tax_before_discount = $item->appendChild($dom->createElement('base_tax_before_discount'));
	$base_tax_before_discount->appendChild($dom->createTextNode(''));
	$base_tax_invoiced = $item->appendChild($dom->createElement('base_tax_invoiced'));
	$base_tax_invoiced->appendChild($dom->createTextNode('0')); //Always 0
	$base_weee_tax_applied_amount = $item->appendChild($dom->createElement('base_weee_tax_applied_amount'));
	$base_weee_tax_applied_amount->appendChild($dom->createTextNode('0')); //Always 0
	$base_weee_tax_applied_row_amount = $item->appendChild($dom->createElement('base_weee_tax_applied_row_amount'));
	$base_weee_tax_applied_row_amount->appendChild($dom->createTextNode('0')); //Always 0
	$base_weee_tax_disposition = $item->appendChild($dom->createElement('base_weee_tax_disposition'));
	$base_weee_tax_disposition->appendChild($dom->createTextNode('0')); //Always 0
	$base_weee_tax_row_disposition = $item->appendChild($dom->createElement('base_weee_tax_row_disposition'));
	$base_weee_tax_row_disposition->appendChild($dom->createTextNode('0')); //Always 0
	$description = $item->appendChild($dom->createElement('description'));
	$description->appendChild($dom->createTextNode(''));
	$discount_amount = $item->appendChild($dom->createElement('discount_amount'));
	$discount_amount->appendChild($dom->createTextNode('0')); //Always 0
	$discount_invoiced = $item->appendChild($dom->createElement('discount_invoiced'));
	$discount_invoiced->appendChild($dom->createTextNode('0')); //Always 0
	$discount_percent = $item->appendChild($dom->createElement('discount_percent'));
	$discount_percent->appendChild($dom->createTextNode('0')); //Always 0
	$free_shipping = $item->appendChild($dom->createElement('free_shipping'));
	$free_shipping->appendChild($dom->createTextNode('0')); //Always 0
	$hidden_tax_amount = $item->appendChild($dom->createElement('hidden_tax_amount'));
	$hidden_tax_amount->appendChild($dom->createTextNode(''));
	$hidden_tax_canceled = $item->appendChild($dom->createElement('hidden_tax_canceled'));
	$hidden_tax_canceled->appendChild($dom->createTextNode(''));
	$hidden_tax_invoiced = $item->appendChild($dom->createElement('hidden_tax_invoiced'));
	$hidden_tax_invoiced->appendChild($dom->createTextNode(''));
	$hidden_tax_refunded = $item->appendChild($dom->createElement('hidden_tax_refunded'));
	$hidden_tax_refunded->appendChild($dom->createTextNode(''));
	$is_nominal = $item->appendChild($dom->createElement('is_nominal'));
	$is_nominal->appendChild($dom->createTextNode('0')); //Always 0
	$is_qty_decimal = $item->appendChild($dom->createElement('is_qty_decimal'));
	$is_qty_decimal->appendChild($dom->createTextNode('0')); //Always 0
	$is_virtual = $item->appendChild($dom->createElement('is_virtual'));
	$is_virtual->appendChild($dom->createTextNode('0')); //Always 0
	$real_name = $item->appendChild($dom->createElement('real_name'));
	$real_name->appendChild($dom->createTextNode($real_nameValue)); //Always 0
	$name = $item->appendChild($dom->createElement('name'));
	$name->appendChild($dom->createTextNode($nameValue)); //Always 0
	$no_discount = $item->appendChild($dom->createElement('no_discount'));
	$no_discount->appendChild($dom->createTextNode('0')); //Always 0
	$original_price = $item->appendChild($dom->createElement('original_price'));
	$original_price->appendChild($dom->createTextNode($original_priceValue));
	$price = $item->appendChild($dom->createElement('price'));
	$price->appendChild($dom->createTextNode($priceValue));
	$price_incl_tax = $item->appendChild($dom->createElement('price_incl_tax'));
	$price_incl_tax->appendChild($dom->createTextNode($price_incl_taxValue));
	$qty_backordered = $item->appendChild($dom->createElement('qty_backordered'));
	$qty_backordered->appendChild($dom->createTextNode(''));
	$qty_canceled = $item->appendChild($dom->createElement('qty_canceled'));
	$qty_canceled->appendChild($dom->createTextNode('0')); //Always 0
	$qty_invoiced = $item->appendChild($dom->createElement('qty_invoiced'));
	$qty_invoiced->appendChild($dom->createTextNode('0')); //Always 0
	$qty_ordered = $item->appendChild($dom->createElement('qty_ordered'));
	$qty_ordered->appendChild($dom->createTextNode($qty_orderedValue)); //Always 0
	$qty_refunded = $item->appendChild($dom->createElement('qty_refunded'));
	$qty_refunded->appendChild($dom->createTextNode('0')); //Always 0
	$qty_shipped = $item->appendChild($dom->createElement('qty_shipped'));
	$qty_shipped->appendChild($dom->createTextNode('0')); //Always 0
	$row_invoiced = $item->appendChild($dom->createElement('row_invoiced'));
	$row_invoiced->appendChild($dom->createTextNode('0')); //Always 0
	$row_total = $item->appendChild($dom->createElement('row_total'));
	$row_total->appendChild($dom->createTextNode($row_totalValue));
	$row_total_incl_tax = $item->appendChild($dom->createElement('row_total_incl_tax'));
	$row_total_incl_tax->appendChild($dom->createTextNode($row_total_incl_taxValue));
	$row_weight = $item->appendChild($dom->createElement('row_weight'));
	$row_weight->appendChild($dom->createTextNode('0'));
	$real_sku = $item->appendChild($dom->createElement('real_sku'));
	$real_sku->appendChild($dom->createTextNode($real_skuValue));
	$sku = $item->appendChild($dom->createElement('sku'));
	$sku->appendChild($dom->createTextNode($skuValue));
	$tax_amount = $item->appendChild($dom->createElement('tax_amount'));
	$tax_amount->appendChild($dom->createTextNode($tax_amountValue));
	$tax_before_discount = $item->appendChild($dom->createElement('tax_before_discount'));
	$tax_before_discount->appendChild($dom->createTextNode(''));
	$tax_canceled = $item->appendChild($dom->createElement('tax_canceled'));
	$tax_canceled->appendChild($dom->createTextNode(''));
	$tax_invoiced = $item->appendChild($dom->createElement('tax_invoiced'));
	$tax_invoiced->appendChild($dom->createTextNode('0'));
	$tax_percent = $item->appendChild($dom->createElement('tax_percent'));
	$tax_percent->appendChild($dom->createTextNode($tax_percentValue));
	$tax_refunded = $item->appendChild($dom->createElement('tax_refunded'));
	$tax_refunded->appendChild($dom->createTextNode(''));
	$weee_tax_applied = $item->appendChild($dom->createElement('weee_tax_applied'));
	$weee_tax_applied->appendChild($dom->createTextNode('a:0:{}')); //Always 0
	$weee_tax_applied_amount = $item->appendChild($dom->createElement('weee_tax_applied_amount'));
	$weee_tax_applied_amount->appendChild($dom->createTextNode('0')); //Always 0
	$weee_tax_applied_row_amount = $item->appendChild($dom->createElement('weee_tax_applied_row_amount'));
	$weee_tax_applied_row_amount->appendChild($dom->createTextNode('0')); //Always 0
	$weee_tax_disposition = $item->appendChild($dom->createElement('weee_tax_disposition'));
	$weee_tax_disposition->appendChild($dom->createTextNode('0')); //Always 0
	$weee_tax_row_disposition = $item->appendChild($dom->createElement('weee_tax_row_disposition'));
	$weee_tax_row_disposition->appendChild($dom->createTextNode('0')); //Always 0
	$weight = $item->appendChild($dom->createElement('weight'));
	$weight->appendChild($dom->createTextNode('0'));

	//Add simple
	$item = $items->appendChild($dom->createElement('item'));
	
	//Set variables
	$base_original_priceValue = '0.0000';
	$base_priceValue = '0.0000';
	$base_row_totalValue = '0.0000';
	$real_nameValue = $itemResult['lineItemDescription'];
	$nameValue = $itemResult['lineItemDescription'];
	$original_priceValue = '0.0000';
	$priceValue = '0.0000';
	$qty_orderedValue = $itemResult['qtyOrdered'];
	$row_totalValue = '0.0000';
	$real_skuValue = $itemResult['productCode'];
	$skuValue = "Product " . $itemNumber . "-OFFLINE";
	$parent_skuValue = 'Product ' . $itemNumber;//Just for simple
	
	fwrite($transactionLogHandle, "      ->ADDING SIMPLE       : " . $itemNumber . " -> " . $real_skuValue . "\n");

	$tax_percentValue = '0.00';
	$base_price_incl_taxValue = '0.0000';
	$base_row_total_incl_taxValue = '0.0000';
	$base_tax_amountValue = '0.0000';
	$price_incl_taxValue = '0.0000';
	$row_total_incl_taxValue = '0.0000';
	$tax_amountValue = '0.0000';

	//Create line item
	$amount_refunded = $item->appendChild($dom->createElement('amount_refunded'));
	$amount_refunded->appendChild($dom->createTextNode('0')); //Always 0
	$applied_rule_ids = $item->appendChild($dom->createElement('applied_rule_ids'));
	$applied_rule_ids->appendChild($dom->createTextNode(''));
	$base_amount_refunded = $item->appendChild($dom->createElement('base_amount_refunded'));
	$base_amount_refunded->appendChild($dom->createTextNode('0')); //Always 0
	$base_cost = $item->appendChild($dom->createElement('base_cost'));
	$base_cost->appendChild($dom->createTextNode(''));
	$base_discount_amount = $item->appendChild($dom->createElement('base_discount_amount'));
	$base_discount_amount->appendChild($dom->createTextNode('0')); //Always 0
	$base_discount_invoiced = $item->appendChild($dom->createElement('base_discount_invoiced'));
	$base_discount_invoiced->appendChild($dom->createTextNode('0')); //Always 0
	$base_hidden_tax_amount = $item->appendChild($dom->createElement('base_hidden_tax_amount'));
	$base_hidden_tax_amount->appendChild($dom->createTextNode(''));
	$base_hidden_tax_invoiced = $item->appendChild($dom->createElement('base_hidden_tax_invoiced'));
	$base_hidden_tax_invoiced->appendChild($dom->createTextNode(''));
	$base_hidden_tax_refunded = $item->appendChild($dom->createElement('base_hidden_tax_refunded'));
	$base_hidden_tax_refunded->appendChild($dom->createTextNode(''));
	$base_original_price = $item->appendChild($dom->createElement('base_original_price'));
	$base_original_price->appendChild($dom->createTextNode($base_original_priceValue));
	$base_price = $item->appendChild($dom->createElement('base_price'));
	$base_price->appendChild($dom->createTextNode($base_priceValue));
	$base_price_incl_tax = $item->appendChild($dom->createElement('base_price_incl_tax'));
	$base_price_incl_tax->appendChild($dom->createTextNode($base_price_incl_taxValue));
	$base_row_invoiced = $item->appendChild($dom->createElement('base_row_invoiced'));
	$base_row_invoiced->appendChild($dom->createTextNode('0'));
	$base_row_total = $item->appendChild($dom->createElement('base_row_total'));
	$base_row_total->appendChild($dom->createTextNode($base_row_totalValue));
	$base_row_total_incl_tax = $item->appendChild($dom->createElement('base_row_total_incl_tax'));
	$base_row_total_incl_tax->appendChild($dom->createTextNode($base_row_total_incl_taxValue));
	$base_tax_amount = $item->appendChild($dom->createElement('base_tax_amount'));
	$base_tax_amount->appendChild($dom->createTextNode($base_tax_amountValue));
	$base_tax_before_discount = $item->appendChild($dom->createElement('base_tax_before_discount'));
	$base_tax_before_discount->appendChild($dom->createTextNode(''));
	$base_tax_invoiced = $item->appendChild($dom->createElement('base_tax_invoiced'));
	$base_tax_invoiced->appendChild($dom->createTextNode('0')); //Always 0
	$base_weee_tax_applied_amount = $item->appendChild($dom->createElement('base_weee_tax_applied_amount'));
	$base_weee_tax_applied_amount->appendChild($dom->createTextNode('0')); //Always 0
	$base_weee_tax_applied_row_amount = $item->appendChild($dom->createElement('base_weee_tax_applied_row_amount'));
	$base_weee_tax_applied_row_amount->appendChild($dom->createTextNode('0')); //Always 0
	$base_weee_tax_disposition = $item->appendChild($dom->createElement('base_weee_tax_disposition'));
	$base_weee_tax_disposition->appendChild($dom->createTextNode('0')); //Always 0
	$base_weee_tax_row_disposition = $item->appendChild($dom->createElement('base_weee_tax_row_disposition'));
	$base_weee_tax_row_disposition->appendChild($dom->createTextNode('0')); //Always 0
	$description = $item->appendChild($dom->createElement('description'));
	$description->appendChild($dom->createTextNode(''));
	$discount_amount = $item->appendChild($dom->createElement('discount_amount'));
	$discount_amount->appendChild($dom->createTextNode('0')); //Always 0
	$discount_invoiced = $item->appendChild($dom->createElement('discount_invoiced'));
	$discount_invoiced->appendChild($dom->createTextNode('0')); //Always 0
	$discount_percent = $item->appendChild($dom->createElement('discount_percent'));
	$discount_percent->appendChild($dom->createTextNode('0')); //Always 0
	$free_shipping = $item->appendChild($dom->createElement('free_shipping'));
	$free_shipping->appendChild($dom->createTextNode('0')); //Always 0
	$hidden_tax_amount = $item->appendChild($dom->createElement('hidden_tax_amount'));
	$hidden_tax_amount->appendChild($dom->createTextNode(''));
	$hidden_tax_canceled = $item->appendChild($dom->createElement('hidden_tax_canceled'));
	$hidden_tax_canceled->appendChild($dom->createTextNode(''));
	$hidden_tax_invoiced = $item->appendChild($dom->createElement('hidden_tax_invoiced'));
	$hidden_tax_invoiced->appendChild($dom->createTextNode(''));
	$hidden_tax_refunded = $item->appendChild($dom->createElement('hidden_tax_refunded'));
	$hidden_tax_refunded->appendChild($dom->createTextNode(''));
	$is_nominal = $item->appendChild($dom->createElement('is_nominal'));
	$is_nominal->appendChild($dom->createTextNode('0')); //Always 0
	$is_qty_decimal = $item->appendChild($dom->createElement('is_qty_decimal'));
	$is_qty_decimal->appendChild($dom->createTextNode('0')); //Always 0
	$is_virtual = $item->appendChild($dom->createElement('is_virtual'));
	$is_virtual->appendChild($dom->createTextNode('0')); //Always 0
	$real_name = $item->appendChild($dom->createElement('real_name'));
	$real_name->appendChild($dom->createTextNode($real_nameValue)); //Always 0
	$name = $item->appendChild($dom->createElement('nameValue'));
	$name->appendChild($dom->createTextNode($nameValue)); //Always 0
	$no_discount = $item->appendChild($dom->createElement('no_discount'));
	$no_discount->appendChild($dom->createTextNode('0')); //Always 0
	$original_price = $item->appendChild($dom->createElement('original_price'));
	$original_price->appendChild($dom->createTextNode($original_priceValue));
	$parent_sku = $item->appendChild($dom->createElement('parent_sku'));
	$parent_sku->appendChild($dom->createTextNode($parent_skuValue));
	$price = $item->appendChild($dom->createElement('price'));
	$price->appendChild($dom->createTextNode($priceValue));
	$price_incl_tax = $item->appendChild($dom->createElement('price_incl_tax'));
	$price_incl_tax->appendChild($dom->createTextNode($price_incl_taxValue));
	$qty_backordered = $item->appendChild($dom->createElement('qty_backordered'));
	$qty_backordered->appendChild($dom->createTextNode(''));
	$qty_canceled = $item->appendChild($dom->createElement('qty_canceled'));
	$qty_canceled->appendChild($dom->createTextNode('0')); //Always 0
	$qty_invoiced = $item->appendChild($dom->createElement('qty_invoiced'));
	$qty_invoiced->appendChild($dom->createTextNode('0')); //Always 0
	$qty_ordered = $item->appendChild($dom->createElement('qty_ordered'));
	$qty_ordered->appendChild($dom->createTextNode($qty_orderedValue)); //Always 0
	$qty_refunded = $item->appendChild($dom->createElement('qty_refunded'));
	$qty_refunded->appendChild($dom->createTextNode('0')); //Always 0
	$qty_shipped = $item->appendChild($dom->createElement('qty_shipped'));
	$qty_shipped->appendChild($dom->createTextNode('0')); //Always 0
	$row_invoiced = $item->appendChild($dom->createElement('row_invoiced'));
	$row_invoiced->appendChild($dom->createTextNode('0')); //Always 0
	$row_total = $item->appendChild($dom->createElement('row_total'));
	$row_total->appendChild($dom->createTextNode($row_totalValue));
	$row_total_incl_tax = $item->appendChild($dom->createElement('row_total_incl_tax'));
	$row_total_incl_tax->appendChild($dom->createTextNode($row_total_incl_taxValue));
	$row_weight = $item->appendChild($dom->createElement('row_weight'));
	$row_weight->appendChild($dom->createTextNode('0'));
	$real_sku = $item->appendChild($dom->createElement('real_sku'));
	$real_sku->appendChild($dom->createTextNode($real_skuValue));
	$sku = $item->appendChild($dom->createElement('sku'));
	$sku->appendChild($dom->createTextNode($skuValue));
	$tax_amount = $item->appendChild($dom->createElement('tax_amount'));
	$tax_amount->appendChild($dom->createTextNode($tax_amountValue));
	$tax_before_discount = $item->appendChild($dom->createElement('tax_before_discount'));
	$tax_before_discount->appendChild($dom->createTextNode(''));
	$tax_canceled = $item->appendChild($dom->createElement('tax_canceled'));
	$tax_canceled->appendChild($dom->createTextNode(''));
	$tax_invoiced = $item->appendChild($dom->createElement('tax_invoiced'));
	$tax_invoiced->appendChild($dom->createTextNode('0'));
	$tax_percent = $item->appendChild($dom->createElement('tax_percent'));
	$tax_percent->appendChild($dom->createTextNode($tax_percentValue));
	$tax_refunded = $item->appendChild($dom->createElement('tax_refunded'));
	$tax_refunded->appendChild($dom->createTextNode(''));
	$weee_tax_applied = $item->appendChild($dom->createElement('weee_tax_applied'));
	$weee_tax_applied->appendChild($dom->createTextNode('a:0:{}')); //Always 0
	$weee_tax_applied_amount = $item->appendChild($dom->createElement('weee_tax_applied_amount'));
	$weee_tax_applied_amount->appendChild($dom->createTextNode('0')); //Always 0
	$weee_tax_applied_row_amount = $item->appendChild($dom->createElement('weee_tax_applied_row_amount'));
	$weee_tax_applied_row_amount->appendChild($dom->createTextNode('0')); //Always 0
	$weee_tax_disposition = $item->appendChild($dom->createElement('weee_tax_disposition'));
	$weee_tax_disposition->appendChild($dom->createTextNode('0')); //Always 0
	$weee_tax_row_disposition = $item->appendChild($dom->createElement('weee_tax_row_disposition'));
	$weee_tax_row_disposition->appendChild($dom->createTextNode('0')); //Always 0
	$weight = $item->appendChild($dom->createElement('weight'));
	$weight->appendChild($dom->createTextNode('0'));
	
        $itemNumber++;
    }
}
    
// Make the output pretty
$dom->formatOutput = true;

// Save the XML string
$xml = $dom->saveXML();

//Write file to post directory
$handle = fopen($toolsXmlDirectory . $orderFilename, 'w');
fwrite($handle, $xml);
fclose($handle);

fwrite($transactionLogHandle, "  ->FINISH TIME   : " . $endTime[5] . '/' . $endTime[4] . '/' . $endTime[3] . ' ' . $endTime[2] . ':' . $endTime[1] . ':' . $endTime[0] . "\n");
fwrite($transactionLogHandle, "  ->CREATED       : ORDER FILE 10 " . $orderFilename . "\n");

fwrite($transactionLogHandle, "->END PROCESSING\n");
//Close transaction log
fclose($transactionLogHandle);

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
