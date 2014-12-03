<?php

/*''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''
	API Version 1.7.1
	Copyright: Nextopia Software Corporation 2012
	Last Modified: 2012-10-11
''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''*/

error_reporting(0);

// Settings used by multiple scripts
define('PRIVATE_CLIENT_ID', CONF_NEXTOPIA_CLIENT_ID);
define('PUBLIC_CLIENT_ID', md5(PRIVATE_CLIENT_ID));
define('PUBLIC_PATH', 'http://' . $_SERVER['HTTP_HOST'] . '/');
define('ASSETS_PATH', PUBLIC_PATH . 'assets/');
define('SITE_TEMPLATE', 'Empty_Template.html');
define('CURRENCY','$');
define('USE_UTF8', true); // If you're using extended characters such as accented letters or language specific characters and they are not appearing correctly, make this true
define('USE_CURL', true); // If your server does not have curl or you wish to use a socket connection instead, set this to false

define('IMAGE_FIELD', 'Image');	// Setting your image field name affects search, compare, quickview, etc. at the same time without multiple changes needing to be made
define('URL_FIELD', 'Url'); // Setting your url field name affects search, compare, quickview, etc. at the same time without multiple changes needing to be made

//Use this to rename refines/fields. Ex: $visible_refine_names['Productline'] = "Product Line"; Also affects visible field names used in compare.php
$VISIBLE_REFINE_NAMES = array();

define('NUM_COMPARABLE_ITEMS', 4);

define('COOKIE_PATH', '/');
define('HTML_ECOMM_LOGO', '<div class="nxt-logo"><a target="_blank" href="http://www.nextopia.com">Site Search</a> by <a target="_blank" href="http://www.nextopia.com"><img border=0 title="Site Search by Nextopia" alt="Site Search by Nextopia" align="absmiddle" src="' . ASSETS_PATH . 'img/ecommerce-search-by-nextopia.gif"></a></div>');

define('SHOW_IMAGE_ZOOM', true);
define('SHOW_QUICK_VIEW', true);
define('SHOW_COMPARE', true);
//define('SHOW_PREFERENCES', true);

define('DEFAULT_RESULTS_PER_PAGE', 16);
define('GRID_COLUMNS_PER_ROW', 4); // The number of items shown in a row for the search results grid view

define('REDIRECT_ON_ONE_MATCH', true); // If only one search result comes back, the search will forward the user to that item's detail page
define('SHOW_SEARCH_SUGGESTIONS', 1); // 0 = off, 1 = only when no matches, 2 = always

define('NUM_PAGINATION_LINKS', 3);	// The number of page links to show in pagination
define('SHOW_PAGINATION_FIRST_AND_LAST', false); // Whether or not to show the first page and last page links for pagination

define('NUM_REFINES_BEFORE_NEW_ROW', 0); // Anything greater than zero will cause the refines to create a new row when that limit has been reached in the horizontal refines view
define('NUM_REFINES_SHOWN_AT_ONCE', 6); // Number of refinement options to show per refinement before the "more" link is shown
define('SHOW_CUSTOM_PRICE_REFINE', true); // Whether or not to show the custom price refine text boxes below the price refine options
define('NUM_REFINE_VALUES_BEFORE_CLEAR', 3); // How many color swatches to show in a row before starting the next one
define('REFINEMENTS_DISPLAY_STYLE', 'H'); // H for Horizontal, V for Vertical

//These options affect whether the values under the refinement are sorted alphabetically or numerically by the # of items in that refinement
define('SORT_ALL_REFINES_NUMERICALLY', false); // Make this true if you want everything sorted that way
$REFINES_SORTED_NUMERICALLY = array(); //Put refinement names in here if you wish to only have some refinements sort numerically

//Fill the array with any refinement names in the order you want them, any refines not in this array will come after the ones listed
$REFINE_ORDER = array_flip(array());
$URL_SORT_ORDER = $REFINE_ORDER;

define('IMAGE_HEIGHT', 150);	// Changing the image height and width may require changes to the css file in order for the quickview button and zoom icon to appear in the correct location
define('IMAGE_WIDTH', 150);

define('CLICKLOG_ON', true);	// Click logging will tell us what detail pages your customers have been going to and what search terms lead them there
define('CLICKLOG_COOKIES', 1);

// Our API return URLs, you should not alter this array
$SITES = array(
	'search' => array(
		'http://ecommerce-search.nextopiasoftware.com/return-results.php?',
		'http://ecommerce-search.nextopiasoftware.net/return-results.php?',
		'http://ecommerce-search-dyn.nextopia.net/return-results.php?'
	),
	'compare' => array(
		'http://ecommerce-search.nextopiasoftware.com/return-compare.php?',
		'http://ecommerce-search.nextopiasoftware.net/return-compare.php?',
		'http://ecommerce-search-dyn.nextopia.net/return-compare.php?'
	),
	'clicklog'=>'http://analytics.nextopia.net/x.php'
);

function our_htmlentities($value) {
	if (USE_UTF8 === true) {
		return htmlentities($value, ENT_QUOTES, 'UTF-8');
	}
	return htmlentities($value);
}

function build_search_url($query_string_parts) {
	// query_string_parts is a hashtable array of key=>value pairs: ex: 'keywords'=>'dogs'
	if (count($query_string_parts) > 1) {
		uksort($query_string_parts, 'uksort_url');
	}
	return '?' . http_build_query($query_string_parts);
}

function uksort_url($a, $b) {
	global $URL_SORT_ORDER;
	if (isset($URL_SORT_ORDER[$a]) && isset($URL_SORT_ORDER[$b])) {
		return $URL_SORT_ORDER[$a] > $URL_SORT_ORDER[$b];
	}
	elseif(isset($URL_SORT_ORDER[$a])) {
		return 0;
	}
	elseif(isset($URL_SORT_ORDER[$b])) {
		return 1;
	}
	else {
		return strcmp($a, $b);
	}
}

function build_cart_and_more_info_links($product_values) {
	return
		'<input type="button" class="prod-call-to-action nxt_addtocart" onclick="top.location.href=\'' . $product_values[URL_FIELD] . '\'" value="Add to Cart"><br/>' .
		'';//'<input type="button" class="prod-call-to-action nxt_moreinfo" onclick="top.location.href=\'' . $product_values[URL_FIELD] . '\'" value="More Info">';
}

function return_main_template_with_replaced_tags($tag_hash, $template = null) {
	/*
	tag_hash:
		- contains a hashtable array of the tags and their replacements ex: [MERCHANDIZING]=>"50% off"
		- we have an array within this function called constant_tags, these tags are ones that appear on template but may not be replaced by every script, this ensures they'll be replaced with default values if not specifically set in tag hash
			- an example of this is the fact that [MERCHANDIZING] is not used on the compare page, if we didn't have the constant_tags any current or new tag would have to be defined in every script
		- in general we've followed the convention that tags contained within [] such as [CONTENT] replace the tag itself, tags defined like --TITLE-- are often more complex replacements that replace or add to content found within the tamplate such as the title and meta tags

	template: - if you wish to use a template that differs from the default pass the content of the template in this variable, otherwise it will open the template file specified by SITE_TEMPLATE above
	*/

	if (is_null($template)) {
		$template = file_get_contents(SITE_TEMPLATE);
	}

	$constant_tags = array(
		'[CONTENT]'=>'',
		'[MERCHANDIZING]'=>'',
		'[REFINEMENTS]'=>''
	);

	foreach($constant_tags as $tag => $default_value) {
		if (!isset($tag_hash[$tag])) {
			$tag_hash[$tag] = $default_value;
		}
	}

	foreach($tag_hash as $tag => $val) {
		switch($tag) {
			case "--TITLE--" :
				$template = preg_replace('/<title([^>]*?)>(.*?)<\/title>/si', '<title' . '\\1' . '>' . $val . ' - ' . '\\2' . '</title>', $template, 1);
				break;
			case '--META-DESCRIPTION--' :
			case '--META-KEYWORDS--' :
				$actual_metatag_name = strtolower(substr($tag, 7, strlen($tag)-7-2));
				$our_meta_tag = '<meta name="' . $actual_metatag_name . '" content="' . our_htmlentities($val) . '" />';
				// Replace the mta tag if it exists
				$template = preg_replace(
						'/<[\s]*meta[\s]*name[\s]*=[\s]*["\']?' . $actual_metatag_name . '["\']?[\s]*content[\s]*=[\s]*["\']?[^>"\']*["\']?[\s]*[\/]?[\s]*>/si',
						$our_meta_tag,
						$template,
						-1,
						$times_replaced);
				// If it does not exist in the template, insert it into the head tag
				if ($times_replaced == 0) {
					$template = str_replace('<head>', '<head>' . $our_meta_tag . "\n", $template);
				}
				break;
			default:
				$template = str_replace($tag, $val, $template);
		}
	}

	$template = str_replace('<head>', '<head>' . return_css_head(), $template);
	return $template;
}

function return_css_head() {
	return '<link type="text/css" rel="stylesheet" href="' . ASSETS_PATH . 'css/nxt_styles.css"/>' . "\n";
}