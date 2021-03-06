<?php

/******************************/
/* GLOBAL CONFIGURATIONS
/******************************/

// Show/hide debug messages and errors
define("DEBUG_MODE", true);

ini_set("display_errors", DEBUG_MODE);

// Load local script library
require(dirname(__FILE__) . "/common.php");

// Set allowed IP Addresses to prevent unwanted requests from being processed
$allowed = array(
	'74.212.242.42','76.95.184.50','64.94.118.193',
);

/******************************/
/* AKAMAI NETSTORAGE RSYNC
/******************************/

define("CONF_RSYNC_DOMAIN_NAME", "sneakerheads"); // "sneakerhead" xpairs
define("CONF_RSYNC_AKAMAI_ID", "216152"); // "216156" xpairs

/******************************/
/* NewOrder Receiver URL
/******************************/
define("CONF_NEWORDER_RECEIVER_ASPX_URL", "http://74.212.242.42/Net4/NewOrder/Receiver.aspx"); // rxkicks
//define("CONF_NEWORDER_RECEIVER_ASPX_URL", "http://74.212.242.42/Net4/NewOrder/Receiver.aspx"); // xpairs

define("CONF_NEW_ORDER_POST_LIMIT", 25);

/**********************************/
/* FULL PRODUCT UPDATE DB CONNECT
/**********************************/

//rxkicksc_devicom for rxkicks.com and ikicksaa_devicom for xpairs.com
define("CONF_DATABASE_CONNECTION_USERNAME", "root");
//same for all
define("CONF_DATABASE_CONNECTION_PASSWORD", "Sneakerhead2014");
//localhost for rxkicks.com and mce042-db-int for xpairs.com
define("CONF_DATABASE_CONNECTION_HOSTNAME", "localhost");
//rxkicksc_magento_11202_31 for rxkicks.com and ikicksaa_magento_11202_31 for xpairs.com
define("CONF_DATABASE_CONNECTION_DBNAME", "spalding");

/******************************/
/* SYSTEM NOTIFICATIONS
/******************************/

// This is a global configuration for system notifications. All on/off
define("CONF_SYSTEM_NOTIFICATION_ENABLED", true);
define("CONF_SYSTEM_NOTIFICATION_TO_ADDRESS", "davis.du@sneakerhead.com");
define("CONF_SYSTEM_NOTIFICATION_CC_ADDRESS", "davis.du@sneakerhead.com");
define("CONF_SYSTEM_NOTIFICATION_FROM_ADDRESS", "davis.du@sneakerhead.com");

/**************************************/
/* CONSTANT CONTACT CONFIGURATIONS
/**************************************/

// 12 for rxkicks.com and 14 for xpairs.com
define("CREATE_CONTACT_LIST_NEWSLETTER", 12);
// 11 for rxkicks.com and 15 for xpairs.com
define("CREATE_CONTACT_LIST_REVIEWS", 11);
// Points earned for subscribing
define("CREATE_CONTACT_POINTS_EARNED", 0);
// Constant Contact API info
//define("CREATE_CONTACT_CC_USERNAME", 'dennis.zhang');
//define("CREATE_CONTACT_CC_PASSWORD", 'dirdir');

/**************************************/
/* TRANSACTIONAL EMAIL CONFIGURATIONS
/**************************************/

// When DEBUG_MODE true (testing), use this email to override actual order's (customer) email address
define("CONF_DEBUG_EMAIL_OVERRIDE", "davis.du@sneakerhead.com");

// When DEBUG_MODE true (testing), sets query order qty LIMIT
define("CONF_DEBUG_ORDER_LIMIT", 1);

// Needed for table blocks in product review
define("CONF_IMAGES_BASE_URL", 'http://image.sneakerhead.com/is/image/sneakerhead/csicon?$100$&$img=sneakerhead/');

// Default customer first name (if not provided)
define("CONF_GUEST_DEFAULT_FIRSTNAME", "Sneakerhead");

// GUEST_INVITE
/******************************/
// Targets guest orders created between 7 & 30 days ago.
define("CONF_GUEST_INVITE_TARGET_ORDER_INTERVAL_DAYS_START", 7);
define("CONF_GUEST_INVITE_TARGET_ORDER_INTERVAL_DAYS_END", 30);

// Comma-delimited list of valid target storeIds for this message type
define("CONF_GUEST_INVITE_TARGET_STORES", "21,22");

// Set boundary to prevent resending of guest_invite_email in case of multiple orders within this number of days
define("CONF_GUEST_INVITE_RESEND_BOUNDARY_DAYS", 30);

// PRODUCT_REVIEW
/******************************/
// Targets orders shipped between this number of days ago & 30 days ago
define("CONF_PRODUCT_REVIEW_TARGET_ORDER_INTERVAL_DAYS_START", 10);
define("CONF_PRODUCT_REVIEW_TARGET_ORDER_INTERVAL_DAYS_END", 30);

// Comma-delimited list of valid target storeIds for this message type
define("CONF_PRODUCT_REVIEW_TARGET_STORES", "21,22");

// Set to "0" to prevent resending ever for current order
define("CONF_PRODUCT_REVIEW_RESEND_BOUNDARY_DAYS", 0);

// STORE_TESTIMONIAL
/******************************/
// Targets orders shipped between this number of days ago & 30 days ago
define("CONF_STORE_TESTIMONIAL_INVITE_TARGET_ORDER_INTERVAL_DAYS_START", 10);
define("CONF_STORE_TESTIMONIAL_INVITE_TARGET_ORDER_INTERVAL_DAYS_END", 30);

// Comma-delimited list of valid target storeIds for this message type
define("CONF_STORE_TESTIMONIAL_INVITE_TARGET_STORES", "21,22");

// Set to "0" to prevent resending ever for current order
define("CONF_STORE_TESTIMONIAL_INVITE_RESEND_BOUNDARY_DAYS", 0);

// SEARCH PAGE CONFIGURATION
/******************************/
//define("CONF_NEXTOPIA_CLIENT_ID", "53c08ba26fd046ce79cc3399a49ed5c5");

// CATEGORY UPDATE CONFIGURATION
/******************************/
//Build list of category IDs not to update and send notification if found
$categoryListToNotProcess = array(6, 7, 9, 10, 11, 12, 25, 30, 75, 123, 150, 375, 542, 543, 544, 545);

// SNEAKERHEAD MOBILE EXCLUSIONS (always inactive)
/******************************/
$mobile_exclude_categories = array(
	300,	// Blog/Brands
	375,	// Sneakerfolio
	546,	// Special Category
	2747,	// New Item Pending Category
	2760,	// Pending Categories
	238,	// Alertbot
	237,	// Nextopia Search Template
);
?>