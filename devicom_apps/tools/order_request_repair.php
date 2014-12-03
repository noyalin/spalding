<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *\
 * MagentoCommerce/Order Manager integration script					  *
 * (C) Stone Edge Technologies Inc.  All rights reserved.             *
 *                                                                    *
 * Processes requests for data transfer between MagentoCommerce       *
 * shopping cart and Stone Edge Order Manager.          			  *
 * By Mark Setzer, October 2008 for use with Magento 1.x.	      	  *
 *                                                                    *
 \* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

//PCN BEGIN
// Set allowed IP Addresses to prevent unwanted requests from being processed
$ip = $_SERVER['REMOTE_ADDR'];
$allowed = array('74.212.242.42'); // these are the IP's that are allowed to view the site.

$salesLogsDirectory = '/chroot/home/rxkicksc/rxkicks.com/html/devicom_apps/sales/logs/';
$realTime = realTime();
    
//Only allow POST from allowed IP addresses
//if ($_SERVER['REQUEST_METHOD'] == 'POST' && in_array($ip, $allowed))
//{
    //Save Stone Edge request to file
    $request = file_get_contents('php://input');
    $requestLogHandle = fopen($salesLogsDirectory . 'new_order_request_log', 'a+');
    fwrite($requestLogHandle, "->REAL TIME  : " . $realTime[5] . '/' . $realTime[4] . '/' . $realTime[3] . ' ' . $realTime[2] . ':' . $realTime[1] . ':' . $realTime[0] . "\n");
    fwrite($requestLogHandle, "->POST       : " . $request . "\n");
    fclose($requestLogHandle);
    //PCN END

    // PHP 5.2 or higher?
    if (version_compare(phpversion(), '5.2.0', '<')===true) {
	echo 'SETIError: Magento integration requires PHP version 5.2.0 or newer. Please contact your webhost to resolve this issue.';
	    exit;	
    }

    // Load Magento core
    //PCN BEGIN
    $mageFilename = '/chroot/home/rxkicksc/rxkicks.com/html/app/Mage.php';
    //PCN END
    if (!file_exists($mageFilename)) {
	    echo 'SETIError: Could not locate "app" directory or load Magento core files. Please check your script installation and try again.';
	    exit;
    }
    require_once $mageFilename;

    // Call script dispatcher and exit
    $debug = isset($_REQUEST['debug']) && ($_REQUEST['debug'] == 'true');
    $dssMode = isset($_REQUEST['rundssmode']) && (strtolower($_REQUEST['rundssmode']) == 'true'); 
    $handler = new StoneEdge_MagentoImport($debug, $dssMode);
    $handler->dispatcher();
    exit;
//}

final class StoneEdge_MagentoImport {
	private static $_debug, $_storeId, $_dssMode;
	const SCRIPT_VERSION = 1.227;
	const ZEND_DATE_FORMAT = 'dd MMM YYYY HH:mm:ss';

	const QOH_RESULT_NOT_FOUND = 'NF';
	const QOH_RESULT_OK = 'OK';
	const QOH_RESULT_NA = 'NA';
	
	public function __construct($debug, $dssMode) {
		self::$_debug = $debug;
		self::$_dssMode = $dssMode;
	}
	
	public function dispatcher() {
		try {
			$function = (isset($_REQUEST['setifunction']) ? strtolower($_REQUEST["setifunction"]) : '');
			
			if ($function == 'sendversion') { 
				self::sendversion(); 
			} elseif (method_exists($this, $function)) {
	 			if (self::getStore()) { call_user_func(array($this, $function)); }
			} else {
				echo 'SETIError: Function "'.$function.'" was not found.';
			}	
		} catch (Exception $e) {
			echo "SETIError: Unexpected error, can't continue!\r\nDetails:\r\n$e";

			//Append error to exception log
			$salesLogsDirectory = '/chroot/home/rxkicksc/rxkicks.com/html/devicom_apps/sales/logs/';
			$handle = fopen($salesLogsDirectory . 'exception_log', 'a');

			$request = file_get_contents('php://input');

			fwrite($handle, "->DISPATCHING FAILED  : \n  ->REQUEST           : " . $request . "\n  ->ERROR MESSAGE     : " . $e->getMessage() . "\n  ->FULL DETAILS      : " . $e . "\n");
			fclose($handle);
		}
	}

	private static function sendversion() {
		echo 'SETIResponse: version=' . self::SCRIPT_VERSION;
	}
	
	private static function getStore() {
		$user = (isset($_REQUEST['setiuser']) ? $_REQUEST['setiuser'] : ''); 
		$pass = (isset($_REQUEST['password']) ? $_REQUEST['password'] : '');
		$code = (isset($_REQUEST['code']) ? $_REQUEST['code'] : '');

		try {
			if (self::$_debug) { echo "Acquiring app handle for store code '$code'...\r\n"; }
			$app = Mage::app($code);
			self::$_storeId = $app->getStore()->getData('store_id');
			if (self::$_debug) { echo "Using store id '"  . self::$_storeId ."'.\r\n"; }		
		} catch (Exception $e) {
			echo "SETIError: Couldn't open store connection. Please check your username, password and code (if provided) and try again. (Details: $e )";

			//Append error to exception log
			$salesLogsDirectory = '/chroot/home/rxkicksc/rxkicks.com/html/devicom_apps/sales/logs/';
			$handle = fopen($salesLogsDirectory . 'exception_log', 'a');

			$request = file_get_contents('php://input');

			fwrite($handle, "->GET STORE FAILED    : \n  ->REQUEST           : " . $request . "\n  ->ERROR MESSAGE     : " . $e->getMessage() . "\n  ->FULL DETAILS      : " . $e . "\n");
			fclose($handle);
			
			return false;
		}

		$admin = Mage::getModel('admin/user');
		if (!$admin->authenticate($user, $pass)) { 	
			echo 'SETIError: Access denied. Please check your login and store code (if provided).';
			return false;
		} 
		
		return true;
	}
	
	private static function getOrderEntityId($db, $sql, DateTime $lastDate, $lastOrder) {
		$res = Mage::getSingleton('core/resource');
		$ordersTable = $res->getTableName('sales/order');
				
		$sql = $db->select()
				  ->from($ordersTable, 'entity_id')
				  ->where('store_id=?', self::$_storeId, Zend_Db::INT_TYPE)
				  ->where("increment_id = '$lastOrder'") // OR updated_at >= '{$lastDate->format('Y-m-d')}'")
				  ->order('entity_id');
		if (self::$_debug) { echo 'Executing SQL: '.$sql->__toString()."\r\n"; }
		$entityId = (int)$db->fetchOne($sql);
		return $entityId;
	}
	
	private static function ordercount() {
		$lastDate = new DateTime();
		$lastOrder = ((isset($_REQUEST['lastorder']) && strtolower($_REQUEST['lastorder']) != 'all') ? $_REQUEST['lastorder'] : 0);
		$lastDate = ((isset($_REQUEST['lastdate']) && strtolower($_REQUEST['lastdate']) != 'all') ? date_create($_REQUEST['lastdate']) : date_create(date('Y-m-d')));

		$res = Mage::getSingleton('core/resource'); $ordersTable = $res->getTableName('sales/order');
		$db = $res->getConnection('sales_read'); 
		$lastEntityId = 0; $sql = new Varien_Db_Select($db);
		if ($lastOrder) { $lastEntityId = self::getOrderEntityId($db, $sql, $lastDate, $lastOrder); }
		$sql = $db->select()
				  ->from($ordersTable, 'COUNT(*)')
				  ->where('store_id=?', self::$_storeId, Zend_Db::INT_TYPE)
				  ->where("entity_id > $lastEntityId"); // OR updated_at >= '{$lastDate->format('Y-m-d')}'");
		if (self::$_debug) { echo "Executing SQL: $sql\r\n"; }
		$orderCount = $db->fetchOne($sql);
		echo "SETIResponse: ordercount=$orderCount";
	}
	
	private static function getcustomerscount() {
		$res = Mage::getSingleton('core/resource');
		$db = $res->getConnection('customer_read');
		$custTable = $res->getTableName('customer/entity');
		$sql = $db->select()
				  ->from($custTable, 'COUNT(*)')
			  	  ->where('store_id=?', self::$_storeId);
		$custCount = $db->fetchOne($sql);
		echo "SETIResponse: itemcount=$custCount";
	}
	
	private static function getproductscount() {		
        $res = Mage::getSingleton('core/resource');
        $db = $res->getConnection('catalog_read');	
        
        $productTable = $res->getTableName('catalog/product');
        $sql = $db->select()
        		  ->from($productTable, 'COUNT(*)');
        $productCount = $db->fetchOne($sql);
		echo "SETIResponse: itemcount=$productCount";
	}

	private static function downloadcustomers() {
		$startnum =  (isset($_REQUEST['startnum']) && ((int)$_REQUEST['startnum'] > 0) ? (int)$_REQUEST['startnum'] - 1 : 0);
		$batchsize = (isset($_REQUEST['batchsize']) && (int)$_REQUEST['batchsize'] > 0 ? $_REQUEST['batchsize'] : 100);

		$res = Mage::getSingleton('core/resource');
		$db = $res->getConnection('customer_read');
		$custTable = $res->getTableName('customer/entity');
		
		$sql = $db->select()
				  ->from($custTable, array('entity_id', 'email'))
				  ->where('store_id=?', self::$_storeId, Zend_Db::INT_TYPE)
				  ->limit($batchsize, $startnum);
		if (self::$_debug) { echo "Executing SQL: $sql\r\n"; }
		$custRows = $db->fetchAll($sql);

		$xd = new DOMDocument("1.0", "UTF-8");
		if (sizeof($custRows) == 0) {
			// no products
			$ndCustomers = self::writeResponse($xd, 'Customers', 2);	
		} else {
			$ndCustomers = self::writeResponse($xd, 'Customers');
			foreach ($custRows as $custRow) {
				$custId = $custRow["entity_id"];
				$email = $custRow["email"];
				$cust = Mage::getModel('customer/customer')->load($custId);

				$ndCust = $xd->createElement("Customer");
				self::xmlAppend("WebID", $cust->getEntityId(), $ndCust, $xd);
				if ($cust->getPrimaryBillingAddress()) {
					$ndBill = $xd->createElement("BillAddr");
					self::writeCustAddress($ndBill, $cust->getPrimaryBillingAddress(), $email, $xd); 
					$ndCust->appendChild($ndBill);
				}
				if ($cust->getPrimaryShippingAddress()) { 
					$ndShip = $xd->createElement("ShipAddr");
					self::writeCustAddress($ndShip, $cust->getPrimaryShippingAddress(), '', $xd); 
					$ndCust->appendChild($ndShip);
				} 
				$ndCustomers->appendChild($ndCust);
			}
		}
		if (!self::$_debug) { header('Content-type: text/xml'); }
		$xd->appendChild($ndCustomers);
		echo $xd->saveXML();
	}
	
	private static function writeCustAddress(DOMElement $ndAddr, Mage_Customer_Model_Address $addr, 
			$email, DOMDocument $xd) {
		
		$street = explode("\n", $addr->getStreetFull());
		self::xmlAppend("FirstName", $addr->getData('firstname'), $ndAddr, $xd);
		self::xmlAppend("LastName", $addr->getData('lastname'), $ndAddr, $xd);
		if ($email != '') { self::xmlAppend("Email", $email, $ndAddr, $xd); }
		self::xmlAppend("Company", $addr->getData('company'), $ndAddr, $xd);
		self::xmlAppend("Phone", $addr->getData('telephone'), $ndAddr, $xd);
		self::xmlAppend("Fax", $addr->getData('fax'), $ndAddr, $xd);
		if (sizeof($street) > 0) { self::xmlAppend("Addr1", $street[0], $ndAddr, $xd); }
		if (sizeof($street) > 1) { self::xmlAppend("Addr2", $street[1], $ndAddr, $xd); }
		self::xmlAppend("City", $addr->getData('city'), $ndAddr, $xd);
		self::xmlAppend("State", $addr->getRegionCode(), $ndAddr, $xd);
		self::xmlAppend("Zip", $addr->getData('postcode'), $ndAddr, $xd);
		self::xmlAppend("Country", $addr->getData('country_id'), $ndAddr, $xd);		
	}
	
	private static function downloadprods() {
		self::writeAllProds(false);
	}

	private static function downloadqoh() {
		self::writeAllProds(true);
	}

	private static function writeAllProds($qohOnly = false) {
		$startnum =  (isset($_REQUEST['startnum']) && ((int)$_REQUEST['startnum'] > 0) ? (int)$_REQUEST['startnum'] - 1 : 0);
		$batchsize = (isset($_REQUEST['batchsize']) && (int)$_REQUEST['batchsize'] > 0 ? $_REQUEST['batchsize'] : 100);
		
		$res = Mage::getSingleton('core/resource');
        $db = $res->getConnection('catalog_read');	     
        $productTable = $res->getTableName('catalog/product');
		$sql = $db->select()
				  ->from($productTable, array('entity_id', 'sku'))
				  ->limit($batchsize, $startnum);
		if (self::$_debug) { echo "Executing SQL: $sql\r\n"; }
		$prodRows = $db->fetchAll($sql);

		$xd = new DOMDocument("1.0", "UTF-8");
		if (sizeof($prodRows) == 0) {
			// no products
			$ndProds = self::writeResponse($xd, 'Products', 2);	
		} else {
			$ndProds = self::writeResponse($xd, 'Products');
			foreach ($prodRows as $prodRow) {
				$prodId = $prodRow['entity_id'];
				$sku = $prodRow['sku'];
				if (!$qohOnly) { $prod = Mage::getModel('catalog/product')->load($prodId); } else { $prod = new Mage_Catalog_Model_Product; }
				$item = Mage::getModel('cataloginventory/stock_item')->loadByProduct($prodId);		
				$ndProd = $xd->createElement("Product");
				self::writeProduct($ndProd, $prod, $item, $sku, $qohOnly, $xd);
				$ndProds->appendChild($ndProd);			
			}
		}
		if (!self::$_debug) { header('Content-type: text/xml'); }
		$xd->appendChild($ndProds);
		echo $xd->saveXML();
	}
	
	private static function writeProduct(DOMElement $ndProd, Mage_Catalog_Model_Product $prod, 
			Mage_CatalogInventory_Model_Stock_Item $item, $sku, $qohOnly, DOMDocument $xd) {
		
		self::xmlAppend("Code", $sku, $ndProd, $xd);
		self::xmlAppend("QOH", $item->getData('qty'), $ndProd, $xd);
		
		if (!$qohOnly) {
			self::xmlAppend("WebID", $prod->getId(), $ndProd, $xd);
			self::xmlAppend("Name", $prod->getName(), $ndProd, $xd);
			self::xmlAppend("Price", $prod->getPrice(), $ndProd, $xd);
			self::xmlAppend("Description", $prod->getData('short_description'), $ndProd, $xd);
			self::xmlAppend("Weight", $prod->getData('weight'), $ndProd, $xd);
	
			// custom options
			$options = $prod->getOptions();
			if (is_array($options) && sizeof($options) > 0) { 
				$ndOpt = $xd->createElement("OptionLists");
				foreach ($options as $option) {
					$ndProdOpt = $xd->createElement("ProductOption");
					self::writeProductOption($ndProdOpt, $prod, $item, $option, $xd);
					$ndOpt->appendChild($ndProdOpt);
				}
				$ndProd->appendChild($ndOpt);
			}
		}
	}
	
	private static function writeProductOption(DOMElement $ndOpt, Mage_Catalog_Model_Product $prod, 
			Mage_CatalogInventory_Model_Stock_Item $item, Mage_Catalog_Model_Product_Option $option,
			DOMDocument $xd) {
		
		self::xmlAppend("WebID", $option->getId(), $ndOpt, $xd);
		self::xmlAppend("Name", $option->getData('title'), $ndOpt, $xd);

		foreach ($option->getValues() as $optId => $optValue) {
			if ($optValue->getData('title') == '') { continue; }
			$ndOptVal = $xd->createElement("OptionValue");

			self::xmlAppend("Name", $optValue->getData('title'), $ndOptVal, $xd);
			
			$hasCode = ($optValue->getData('sku') != '');
			if ($hasCode) { self::xmlAppend("Code", $optValue->getData('sku'), $ndOptVal, $xd); }
			
			$hasPrice = ((float)$optValue->getData('price') != 0);
			if ($hasPrice) {
				if ($optValue->getData('price_type') == 'fixed') {	
					self::xmlAppend("Price", $optValue->getData('price'), $ndOptVal, $xd);
				} else {
					self::xmlAppend("Price", (float)($optValue->getdata('price') * $prod->getPrice), $ndOptVal, $xd);	
				}
			}
			$ndOpt->appendChild($ndOptVal);
		}
	}

	private static function downloadorders() {
		$lastDate = new DateTime();
		$startnum =  (isset($_REQUEST['startnum']) && ((int)$_REQUEST['startnum'] > 0) ? (int)$_REQUEST['startnum'] - 1 : 0);
		$batchsize = (isset($_REQUEST['batchsize']) && (int)$_REQUEST['batchsize'] > 0 ? $_REQUEST['batchsize'] : 100);
		$lastOrder = ((isset($_REQUEST['lastorder']) && strtolower($_REQUEST['lastorder']) != 'all') ? $_REQUEST['lastorder'] : 0);
		$lastDate = ((isset($_REQUEST['lastdate']) && strtolower($_REQUEST['lastdate']) != 'all') ? date_create($_REQUEST['lastdate']) : date_create(date('Y-m-d')));
		
		$res = Mage::getSingleton('core/resource'); $ordersTable = $res->getTableName('sales/order');
		$db = $res->getConnection('sales_read'); 
		$lastEntityId = 0; $sql = new Varien_Db_Select($db);
		if ($lastOrder) { $lastEntityId = self::getOrderEntityId($db, $sql, $lastDate, $lastOrder); }
		$sql = $db->select()
				  ->from($ordersTable, 'entity_id')
				  ->where('store_id=?', self::$_storeId, Zend_Db::INT_TYPE)
				  ->where("entity_id > $lastEntityId") // OR updated_at >= '{$lastDate->format('Y-m-d')}'")
				  ->limit($batchsize, $startnum);
		if (self::$_debug) { echo "Executing SQL: $sql\r\n"; }
		$ordRows = $db->fetchAll($sql);

		$xd = new DOMDocument("1.0", "UTF-8");
		if (sizeof($ordRows) == 0) {
			// no new orders :(
			$ndOrders = self::writeResponse($xd, 'Orders', 2);	
		} else {
			$ndOrders = self::writeResponse($xd, 'Orders');
			foreach ($ordRows as $ordRow) {
				$orderId = $ordRow['entity_id'];
				$order = Mage::getModel('sales/order')->load($orderId);
				$ndOrd = $xd->createElement("Order");
				if (self::writeOrder($ndOrd, $order, $xd)) { 
					$ndOrders->appendChild($ndOrd); 
				}
			}
		}
		if (!self::$_debug) { header('Content-type: text/xml'); }
		$xd->appendChild($ndOrders);
		echo $xd->saveXML();
	}
	
	private static function writeOrder(DOMElement $ndOrder, Mage_Sales_Model_Order $order, DOMDocument $xd) {
		self::xmlAppend("OrderNumber", $order->getData('increment_id'), $ndOrder, $xd);
				
		$orderDate = new DateTime();
		$orderDate = date_create($order->getData('created_at'));
		self::xmlAppend("OrderDate", $orderDate->format('d-M-Y g:i:s A'), $ndOrder, $xd);
				
		if (!$order->getBillingAddress()) { 
			if (self::$_debug) { echo "Order {$order->getData('increment_id')} was missing a billing address, skipping."; }
			return false; 
		}
		$ndBill = $xd->createElement("Billing");
		self::writeOrderAddress($ndBill, $order->getBillingAddress(), $order->getData('customer_email'), $xd);
		$ndOrder->appendChild($ndBill);
		
		$ndShip = $xd->createElement("Shipping");
		if (!$order->getShippingAddress()) { $addr = $order->getBillingAddress(); } else { $addr = $order->getShippingAddress(); }
		self::writeOrderAddress($ndShip, $addr, '', $xd);

		foreach ($order->getAllItems() as $orderItem) {
			if ($orderItem->getData('product_type') == 'configurable') { continue; }
			$ndProd = $xd->createElement("Product");
			self::writeOrderItem($ndProd, $orderItem, $order, $xd);
			$ndShip->appendChild($ndProd);
		}
		$ndOrder->appendChild($ndShip);
		
		self::writeOrderPayment($ndOrder, $order, $xd);
		
		$ndTot = $xd->createElement("Totals");
		self::writeOrderTotals($ndTot, $order, $xd);
		$ndOrder->appendChild($ndTot);
		
		self::writeOrderAdjustments($ndOrder, $order, $xd);
		
		$ndOther = $xd->createElement("Other");
			self::xmlAppend("IPHostName", $order->getData('remote_ip'), $ndOther, $xd);
			self::xmlAppend("TotalOrderWeight", $order->getData('weight'), $ndOther, $xd);
			self::xmlAppend("GiftMessage", self::getGiftMessage($order), $ndOther, $xd);
			self::xmlAppend("Comments", $order->getData('customer_note'), $ndOther, $xd);
		$ndOrder->appendChild($ndOther);
		return true;
	}
	
	private static function writeOrderAddress(DOMElement $nd, Mage_Sales_Model_Order_Address $addr, $email = '', DOMDocument $xd) {
		self::xmlAppend("FullName", $addr->getName(), $nd, $xd);
		self::xmlAppend("Company", $addr->getData('company'), $nd, $xd);
		self::xmlAppend("Email", $email, $nd, $xd);
		self::xmlAppend("Phone", $addr->getData('telephone'), $nd, $xd);
		
		$ndAddr = $xd->createElement("Address");
			$country = $addr->getCountry();
			if ($country != 'US') { $state = $addr->getData('region'); } else { $state = $addr->getRegionCode(); }
		
			$street = explode("\n", $addr->getStreetFull());
			self::xmlAppend("Street1", $street[0], $ndAddr, $xd);
			if (sizeof($street) > 1) { self::xmlAppend("Street2", $street[1], $ndAddr, $xd); }
			self::xmlAppend("City", $addr->getData('city'), $ndAddr, $xd);
			self::xmlAppend("State", $state, $ndAddr, $xd);
			self::xmlAppend("Code", $addr->getData('postcode'), $ndAddr, $xd);
			self::xmlAppend("Country", $country, $ndAddr, $xd);
		$nd->appendChild($ndAddr);
	}

	private static function writeOrderItem(DOMElement $nd, Mage_Sales_Model_Order_Item $item, 
			Mage_Sales_Model_Order $order, DOMDocument $xd) {		

		$ok = false;
		$isBundleItem = false;
		
		if ($item->getParentItemId()) {
			$parent = $item->getParentItem();
			if (is_object($parent)) {
				$price = $parent->getData('base_price');
				$qty = $parent->getQtyOrdered();
				$opt = $parent->getProductOptions();
				$type = $parent->getData('product_type');
				$weight = $item->getData('weight');
				if ($type == 'bundle') { 
					$price = 0; 
					$weight = 0;
					$isBundleItem = true; 
					$qty = $item->getQtyOrdered();
				}
				$ok = true;
			}
		} 
		
		if (!$ok) {
			$price = $item->getData('base_price');
			$qty = $item->getQtyOrdered();
			$opt = $item->getProductOptions();
			$type = $item->getData('product_type');
			$weight = $item->getData('weight');
		}
		
		self::xmlAppend("Name", $item->getData('name'), $nd, $xd);
		self::xmlAppend("SKU", $item->getData('sku'), $nd, $xd);
		self::xmlAppend("ItemPrice", $price, $nd, $xd);
		self::xmlAppend("Quantity", $qty, $nd, $xd);
		self::xmlAppend("Weight", $weight, $nd, $xd);
		self::xmlAppend("CustomerText", self::getGiftMessage($item), $nd, $xd);		
		
		$ok = (is_array($opt));
		if (!$ok) { return; } // no options

		$ok = false;
		if (!$isBundleItem && isset($opt['options']) && is_array($opt['options'])) {
			// has regular options
			foreach ($opt['options'] as $op) {
				$opName = $op['label'];
				$opVal = $op['value'];
				self::writeOrderOption($nd, $xd, $opName, $opVal);
			}
			$ok = true;
		}
		
		if (!$isBundleItem && isset($opt['attributes_info']) && is_array($opt['attributes_info'])) {
			// has inherited attributes
			foreach ($opt['attributes_info'] as $att) {
				$opName = $att['label'];
				$opVal = $att['value'];
				self::writeOrderOption($nd, $xd, $opName, $opVal);
			}
			$ok = true;
		}
		
		if (!$ok && !$isBundleItem && isset($opt['info_buyRequest']) && isset($opt['info_buyRequest']['options'])) {		
			$opt = $item->getProductOptionByCode('info_buyRequest');
			$opt = new Varien_Object($opt);
			if (!is_array($opt->getData('options')) || sizeof($opt->getData('options')) == 0) { return; }
			$prod = Mage::getModel('catalog/product')->load($item->getData('product_id'));
			
			foreach ($opt->getData('options') as $optId => $optValId) {
				$option = $prod->getOptionById($optId);
				if (!is_object($option)) continue;
				self::writeOrderOptions($nd, $optId, $optValId, $prod, $xd);
			}		
		}
	}

	private static function getGiftMessage(Varien_Object $entity) {
        if ($giftMessageId = $entity->getGiftMessageId()) {
            $giftMessage = Mage::getModel('giftmessage/message')->load($giftMessageId);
			return $giftMessage->getMessage();            
        }
        return '';
	}
		
	private static function writeOrderOption(DOMElement $nd, $xd, $opName, $opVal, $opCode = '', $opPrice = 0) {
		$ndOpt = $xd->createElement("OrderOption");
		self::xmlAppend("OptionName", $opName, $ndOpt, $xd);
		self::xmlAppend("SelectedOption", $opVal, $ndOpt, $xd);
		if ($opCode != '') { self::xmlAppend("OptionCode", $opCode, $ndOpt, $xd); }
		if ($opPrice != 0) {  self::xmlAppend("OptionPrice", $opPrice, $ndOpt, $xd); }
		$nd->appendChild($ndOpt);
	}
	
	private static function writeOrderOptions(DOMElement $nd, $optId, $optValId, Mage_Catalog_Model_Product $prod, 
			DOMDocument $xd) {
		$opPrice = 0;
		$selectedVal = '';
		$opName = '';
		$opCode = '';
		
		$option = $prod->getOptionById($optId);
		$opName = $option->getData('title');
		
		if (is_array($optValId)) {
			foreach ($optValId as $subval => $subvalId) {
				// checkbox values?
				$optVal = $option->getValueById($subvalId);
				if ($selectedVal != '') { $selectedVal .= ','; }
				$selectedVal .= $optVal->getData('title');
				$opPrice += (float)$optVal->getData('price');	
			}
		} else {
			$optVal = $option->getValueById($optValId);
			if (is_object($optVal)) { 
				$opPrice = (float)$optVal->getData('price');
				$selectedVal = $optVal->getData('title');
				$opCode = ($optVal->getData('sku') != '');
			} else {
				$selectedVal = '';
			}
		}
		self::writeOrderOption($nd, $xd, $opName, $selectedVal, $opCode, $opPrice);
	}
	
	private static function writeOrderPayment(DOMElement $ndOrd, Mage_Sales_Model_Order $order, DOMDocument $xd) {
		$payment = self::getOrderPayment($order);
		if (!$payment) { return; }

		// credit card variable initialization
		$ccNum = $ccIssuer = $ccExp = $ccName = $transId = $authCode = $avs = $cvv2 = $secKey = '';
		
		$ndPay = $xd->createElement("Payment");
		
		$payMeth = $payment->getData('method');
		switch ($payMeth) {
			case 'checkmo':
				self::xmlAppend("Check", " ", $ndPay, $xd);
				break;
				
			case 'linkpoint':
				$secKey = self::getCcTransId($payment);
				$transId = self::getCcApproval($payment);
				$authCode = false;

			case 'cc':
			case 'ccsave':
			case 'authorizenet':	
			case 'verisign':
			case 'usaepay':
				$ndCc = $xd->createElement("CreditCard");
				
				self::xmlAppend("Issuer", self::getCcIssuer($payment->getData('cc_type')), $ndCc, $xd);
				
				$ccNum = $payment->getData('cc_number');
				if (!$ccNum) { $ccNum = $payment->getData('cc_last4'); }
				if (!self::$_dssMode) { self::xmlAppend("Number", (is_numeric($ccNum) ? utf8_encode($ccNum) : ''), $ndCc, $xd); }
				
				self::xmlAppend("ExpirationDate", $payment->getData('cc_exp_month') . '/' . $payment->getData('cc_exp_year'), $ndCc, $xd);
				
				$ccName = $payment->getData('cc_owner');
				if (!$ccName) { $ccName = $order->getBillingAddress()->getName(); }
				self::xmlAppend("FullName", $ccName, $ndCc, $xd);
				
				if ($transId !== false) { 
					if (!$transId) { $transId = self::getCcTransId($payment); }
					if ($transId) { self::xmlAppend("TransID", $transId, $ndCc, $xd); }
				}
				
				if ($authCode !== false) {
					if (!$authCode) { $authCode = self::getCcApproval($payment); }
					if ($authCode) { self::xmlAppend("AuthCode", $authCode, $ndCc, $xd); }
				}
				
				if ($secKey) {
					self::xmlAppend("SecurityKey", $secKey, $ndCc, $xd);
				}
				
				$avs = '';
				if (method_exists($payment, "getCcAvsStatus")) { $avs = $payment->getCcAvsStatus(); }
				if ($avs == '') { $avs = $payment->getData('cc_avs_status'); }
//PCN BEGIN
				$avs = $order->getPayment()->getData('cc_avs_status');
//PCN END
				self::xmlAppend("AVS", $avs, $ndCc, $xd); 
				
				self::xmlAppend("VerificationValue", $payment->getData('cc_cid_status'), $ndCc, $xd);
				
				$ndPay->appendChild($ndCc);
				break;
				
			case 'paypal_direct':
			case 'paypal_express':
			case 'paypal_standard':
			case 'paypaluk_direct':
			case 'paypaluk_express':
				$ndPP = $xd->createElement("PayPal");
				$transId = self::getCcTransId($payment);
				self::xmlAppend("TransID", $transId, $ndPP, $xd);				
				$ndPay->appendChild($ndPP);
				break;
				
			case 'purchaseorder':
				$ndPo = $xd->createElement("PurchaseOrder");
				self::xmlAppend("PurchaseNumber", $payment->getData('po_number'), $ndPo, $xd);
				$ndPay->appendChild($ndPo);
				break;

			case 'googlecheckout':
			case 'google checkout':
				self::xmlAppend("GoogleCheckout", " ", $ndPay, $xd);
				break;
				
			default:
				$nd = $xd->createElement("Generic1");
				self::xmlAppend("Name", $payMeth, $nd, $xd);
				self::xmlAppend("Description", "Unrecognized payment type: '{$payMeth}'", $nd, $xd);
				$ndPay->appendChild($nd);
				break;
		}
		$ndOrd->appendChild($ndPay);
	}

	private static function getOrderPayment(Mage_Sales_Model_Order $order) {
		$payments = $order->getAllPayments();
		if (!$payments || !is_array($payments)) { return false; }
		$payment = $payments[0]; // for now, we're only interested in the first payment

		if (!$payment || !is_object($payment)) { return false; }

		$method = $payment->getData('method');
		if (!self::isCcPayment($method)) { return $payment; }
		if ($payment->getData('cc_type')) { return $payment; }
		
		// Payment data may be nested. We have to go deeper...
		$p = self::getPayData($payment->getData());
		if (!$p) { return false; }
		$p->setData('method', $method);
		return $p;
	}
	
	private static function isCcPayment($method) {
		switch ($method) {
			case 'cc':
			case 'ccsave':
			case 'authorizenet':
			case 'linkpoint':
			case 'verisign':
			case 'usaepay':
				return true;
			default:		
				return false;
		}
	}	
	
	private static function getPayData($p) {
		if (is_array($p)) { 
			if (isset($p['cc_type'])) { return new Varien_Object($p); }
			
			foreach ($p as $val) {
				$x = self::getPayData($val);
				if (is_object($x)) { return $x; }
			}
		}
		return false;
	}	
	
	private static function getCcIssuer($issuer) {
		switch (strtolower($issuer)) {
			case 'vi':
				return "Visa";
			case 'di':
				return "Discover";
			case 'mc':
				return "MasterCard";
			case 'ae':
				return "AMEX";
			case 'ot':
				return "Other";				
		}
		
	}	
	
	private static function getCcTransId(Varien_Object $payment) {
		$transId = false;
		$transId = $payment->getData('last_trans_id');
		if (!$transId && method_exists($payment, "getRefundTransactionId")) { $transId = $payment->getRefundTransactionId(); }
		if (!$transId) { $transId = $payment->getData('cc_trans_id'); }
		return $transId;		
	}
	
	private static function getCcApproval(Varien_Object $payment) {
		$authCode = false;
		if (method_exists($payment, "getCcApproval")) { $authCode = $payment->getCcApproval(); }
		if (!$authCode) { $authCode = $payment->getData('cc_approval'); }
		return $authCode;		
	}

	private static function writeOrderTotals(DOMElement $ndTot, Mage_Sales_Model_Order $order, DOMDocument $xd) {	
		self::xmlAppend("ProductTotal", $order->getData('base_subtotal'), $ndTot, $xd);
		
		$ndTax = $xd->createElement("Tax");
			self::xmlAppend("TaxAmount", $order->getData('base_tax_amount'), $ndTax, $xd);
		$ndTot->appendChild($ndTax);
			
		self::xmlAppend("GrandTotal", $order->getData('base_grand_total'), $ndTot, $xd);
		
		$ndShip = $xd->createElement("ShippingTotal");
			self::xmlAppend("Total", $order->getData('base_shipping_amount'), $ndShip, $xd);
			self::xmlAppend("Description", $order->getData('shipping_description'), $ndShip, $xd);
		$ndTot->appendChild($ndShip);
	}

	private static function writeOrderAdjustments(DOMElement $ndOrd, Mage_Sales_Model_Order $order, DOMDocument $xd) {
		if ((float)$order->getData('discount_amount') == 0) { return; }
		$ndCoupon = $xd->createElement("Coupon");
			self::xmlAppend('Name', $order->getData('coupon_code'), $ndCoupon, $xd);
			self::xmlAppend('Total', $order->getData('base_discount_amount'), $ndCoupon, $xd);		
		$ndOrd->appendChild($ndCoupon);
	}
	
	private static function qohreplace() {
		$update = (isset($_REQUEST['update']) ? $_REQUEST['update'] : false);
		if (!$update) {
			echo 'SETIError: Invalid/missing update command: ' . $_REQUEST['update'];
			return;
		}

		$res = Mage::getSingleton('core/resource');
		$db = $res->getConnection('catalog_read');
		
		$response =  "SETIResponse\r\n";
		
		$skuList = explode( '|', $update);
		foreach ($skuList as $skuQohPair) {
			if ($skuQohPair == '') { continue; }
			$skuAndQoh = explode('~', $skuQohPair);
			if (sizeof($skuAndQoh) < 2) {
				$response .= "$sku=Err: Invalid SKU pair '$skuQohPair'\r\n";
				continue;
			}				
			$sku = $skuAndQoh[0];
			$qoh = $skuAndQoh[1];
			
			$result = self::updateStock($res, $db, $sku, $qoh, false);
			switch ($result) {
				case self::QOH_RESULT_OK:
					$response .= "$sku=OK\r\n";
					break;
				case self::QOH_RESULT_NOT_FOUND:
					$response .= "$sku=NF\r\n";
					break;
				case self::QOH_RESULT_NA:
					$response .= "$sku=NA\r\n";
					break;
				default:
					$response .= "$sku=Err\r\n";
					break;				
			}
		}
		
		$response .= "SETIEndOfData";
		echo $response;		
	}
	
	private static function invupdate() {
		$update = (isset($_REQUEST['update']) ? $_REQUEST['update'] : false);
		if (!$update) {
			echo 'SETIError: Invalid/missing update command: ' . $_REQUEST['update'];
			return;
		}

		$skuAndQoh = explode('~', $update);
		if (sizeof($skuAndQoh) < 1) {
			echo 'SETIError: Invalid update command: ' . $update;
			return;
		}
				
		$sku = $skuAndQoh[0];
		$qoh = $skuAndQoh[1];

		$res = Mage::getSingleton('core/resource');
        $db = $res->getConnection('catalog_read');	     

        $result = self::updateStock($res, $db, $sku, $qoh, true);
		switch ($result) {
			case self::QOH_RESULT_OK:
				echo 'SETIResponse=OK;QOH='.$qoh;
				break;
			case self::QOH_RESULT_NOT_FOUND:
				echo 'SETIResponse=OK;Note=SKU ' . $sku . ' was not found.';
				break;
			case self::QOH_RESULT_NA:
				echo 'SETIResponse=OK;Note=SKU ' . $sku . ' is not being tracked.';
				break;
			default:
				echo 'SETIResponse=False;Note=Unknown error updating stock, will retry';
				break;
		}
	        	        
	}
	
	private static function updateStock($res, $db, $sku, &$qoh, $relative = false) {
        $productTable = $res->getTableName('catalog/product');
    		$sql = $db->select()
				  ->from($productTable, 'entity_id')
				  ->where('sku = ?', $sku);			
		if (self::$_debug) { echo "Executing SQL: $sql\r\n"; }
		$entityId = $db->fetchOne($sql);
		
		if ($entityId == '' || $entityId == null) {
			return self::QOH_RESULT_NOT_FOUND;
		}

		$item = new Mage_CatalogInventory_Model_Stock_Item();
		$item = Mage::getModel('cataloginventory/stock_item')->loadByProduct($entityId);
		if (!$item->getManageStock()) {
			return self::QOH_RESULT_NA;	
		}

		if ($relative) {
			$change = (float)$qoh;
		} else {
			$change = (float)$qoh - (float)$item->getData('qty');			
		}
		
		if ($change < 0) {
			$item->subtractQty(abs($change));
		} else {
			$item->addQty($change);
		}
		
		$item->save();
		
		$qoh = $item->getData('qty');
		if ($qoh > 0) {
			// set the stock status to "In Stock" again
			$newStatus = Mage_CatalogInventory_Model_Stock_Status::STATUS_IN_STOCK;
			$item->setData('is_in_stock', $newStatus);
			$item->save();
			
			$stockStatus = Mage::getSingleton('cataloginventory/stock_status');
			$stockStatus->changeItemStatus($item);			
		}
		return self::QOH_RESULT_OK;
	}
	
	private static function updatestatus() {
//PCN BEGIN
		$raw_xml = $_REQUEST['update'];
		$xml = str_replace("&", "&amp;", $raw_xml);
		// Send response back to Stone Edge to make it thinkg the order update was successful
		echo 'SETIResponse=OK;';
//PCN END
	}

//PCN BEGIN
//Removed updateOrderStatus
//PCN END
	private static function trackingNumberExists($trackNum, $shipments) {
		if (!is_array($shipments)) return false;
		if (!isset($shipments['tracks']) || count($shipments['tracks']) == 0) return false;
		foreach ($shipments['tracks'] as $track) {
			if ($track['number'] == $trackNum) return true; 
		}
		return false;
	}
	
	private static function parseOrderState($status) {
		$s = strtolower($status);
		$s = str_replace(' ', '_', $s);
		switch ($s) {
			case 'closed':
			case 'canceled':
			case 'complete':
			case 'holded':
			case 'new':
			case 'pending_payment':
			case 'processing':
				return $s;
			default:
				return false;				
		}	
	}
	
	private static function parseShipmentCarrier($orderNumber, $carrier, Mage_Sales_Model_Order_Shipment_Api $api) {
		$carrier = strtolower($carrier);
		if ($carrier == 'fx') { $carrier = 'fedex'; }
		$carriers = $api->getCarriers($orderNumber);
		foreach ($carriers as $code => $title) {
			if ($code == $carrier || $title == $carrier) { return $code; }	
		}		
		return 'custom';
	}
	
	private static function writeResponse(DOMDocument $xd, $respType, $respCode = 1, $respDesc = 'success') {
		$ndDoc = $xd->createElement("SETI$respType");
		$ndResp = $xd->createElement("Response");
		self::xmlAppend("ResponseCode", $respCode, $ndResp, $xd);
		self::xmlAppend("ResponseDescription", $respDesc, $ndResp, $xd);
		$ndDoc->appendChild($ndResp);
		$xd->appendChild($ndDoc);
		return $ndDoc;		
	}

	private static function xmlAppend($ndBname, $ndBtxt, DOMElement $ndA, DOMDocument $xd) {
		$ndB = $xd->createElement($ndBname);
		$ndB->appendChild($xd->createTextNode($ndBtxt));
		$ndA->appendChild($ndB);
		return $ndA;		
	}		
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
