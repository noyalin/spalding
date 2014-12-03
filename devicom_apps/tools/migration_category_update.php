<?php
//NOTES
//ONLY NEED TO BUILD NEEDED CMS CAREGORIES
//1. Set use parent category settings for all CMS subcategories, Outlet subcategories, and Sneakerfoilio subcategories
//2. 171 - 374 is the range of ids available for categories used for CMS pages
//3. Map post to attributes (default category, positioning, etc.)
//4. Set use parent settings for any category that its parent is Outlet (150)
//5. Set use parent settings for any category that its parent is Sneakerfolio (375)
//6. Any year category (not brand) needs to have include in menu set to NO
set_time_limit(0);//no timout
ini_set('memory_limit', '1024M');

$toolsLogsDirectory = '/chroot/home/rxkicksc/rxkicks.com/html/devicom_apps/tools/logs/';

// initialize magento environment for 'default' store
require_once '/chroot/home/rxkicksc/rxkicks.com/html/app/Mage.php';
umask(0);
Mage::app('admin'); // Default or your store view name.

// Get resource
$resource = Mage::getSingleton('core/resource');
$writeConnection = $resource->getConnection('core_write');

//Open transaction log
$transactionLogHandle = fopen($toolsLogsDirectory . 'category_update_transaction_log', 'a+');
fwrite($transactionLogHandle, "->BEGIN PROCESSING...\n");

// Build array from csv file
fwrite($transactionLogHandle, "  ->BUILDING ARRAY...\n");

//Open CSV
//Formatting should be...
$handle = fopen("csv_files/categories.csv", "r");
$data = fgetcsv($handle, 0, ',','"');

$highestCategoryId = 2; //Start at default category since already in system

while ($data = fgetcsv($handle, 0, ',','"')) {

    $categories[$data[0]]['category_id'] = $data[0];
    $categories[$data[0]]['is_active'] = $data[1];

    if ($data[2] == 1) {
        $categories[$data[0]]['sneakerhead_only'] = 1;
        //36, 150
    } else {
        $categories[$data[0]]['sneakerhead_only'] = 0;
    }

    //if (substr($itemResult['productCode'], 0, -($length + 1)) == 36 || == 150 == 375) {
       // $categories[$data[0]]['is_offline_category'] = 1;
        //36, 150
    //} else {
        $categories[$data[0]]['is_offline_category'] = $data[3];
    //}
    $categories[$data[0]]['include_in_menu'] = $data[4];
    $categories[$data[0]]['is_anchor'] = $data[5];
    $categories[$data[0]]['parent_id'] = $data[6];
    $categories[$data[0]]['path'] = $data[7];
    $categories[$data[0]]['position'] = $data[8];
    $categories[$data[0]]['level'] = $data[9];
    $categories[$data[0]]['children_count'] = $data[10];
    $categories[$data[0]]['name'] = $data[11];
    $categories[$data[0]]['heading'] = $data[12];
    $categories[$data[0]]['meta_title'] = $data[13];
    $categories[$data[0]]['url_key'] = $data[14];
    $categories[$data[0]]['highlighted_product'] = $data[15];

    if ($data[0] > $highestCategoryId) {
	$highestCategoryId = $data[0];
    }
}

// Select next available ID to determine if any new category placeholders are required


//get a new category object
$category = Mage::getModel('catalog/category');
$category->setStoreId(0); // 0 = default/all store view. If you want to save data for a specific store view, replace 0 by Mage::app()->getStore()->getId().
$category->load(1);

//$rootcatId= Mage::app()->getStore()->getRootCategoryId();

$numberOfNewCategories = $highestCategoryId - $category->getChildrenCount() - 1;

fwrite($transactionLogHandle, "  ->HIGHEST CATEGORY        ->" . $highestCategoryId . "\n");
fwrite($transactionLogHandle, "  ->ROOT CATEGORY COUNT     ->" . $category->getChildrenCount() . "\n");
fwrite($transactionLogHandle, "  ->NUMBER OF NEW CATEGORIES->" . $numberOfNewCategories . "\n");

$category->setChildrenCount($highestCategoryId - 1);
$category->save();

fwrite($transactionLogHandle, "  ->SAVED ROOT CAT CHILDREN ->" . $category->getChildrenCount() . "\n");

//Disable indexes
fwrite($transactionLogHandle, "  ->CHANGE INDEX  :MANUAL\n");
$processCollection = Mage::getSingleton('index/indexer')->getProcessesCollection(); 
foreach ($processCollection as $process) {
  $process->setMode(Mage_Index_Model_Process::MODE_MANUAL)->save();
}
fwrite($transactionLogHandle, "  ->CHANGED INDEX :MANUAL\n");

// CREATE CATEGORY PLACEHOLDERS BASED OFF HIGHEST CATEGORY ID SET DURING ABOVE CATEGORY ARRAY CREATION
fwrite($transactionLogHandle, "  ->CREATING PLACEHOLDERS...\n");

$i = 1;

while ($i < $numberOfNewCategories) {
//$highestCategoryId
	//get a new category object
	$category = Mage::getModel('catalog/category');
	$category->setStoreId(0); // 0 = default/all store view. If you want to save data for a specific store view, replace 0 by Mage::app()->getStore()->getId().
	
	$category->setAttributeSetId($category->getDefaultAttributeSetId());
	$category->setPath('1');
	$category->setPosition(1);
	$category->setLevel(1);
	$category->setChildrenCount(0);
	$category->setName('placeholder');
	$category->setIsActive(0);
	$category->setUrlKey('placeholder');
	$category->setIsOfflineCategory(0);
	$category->setHighlightedProduct(NULL);
	//$category->setData('thumbnail', false);
	$category->setDescription(NULL);
	//$category->setData('image', false);
	$category->setMetaTitle(NULL);
	$category->setMetaKeywords(NULL);
	$category->setMetaDescription(NULL);
	$category->setIncludeInMenu(1);
	$category->setDisplayMode('PRODUCTS');//if landing assing PAGE else PRODUCTS
	$category->setLandingPage(NULL);
	$category->setIsAnchor(0);
	$category->setAvailableSortBy(NULL);
	//$category->setData('default_sort_by', false);
	$category->setFilterPriceRange(NULL);
	$category->setCustomUseParentSettings(0);
	$category->setCustomApplyToProducts(0);
	$category->setCustomDesign(NULL);
	$category->setCustomDesignFrom(NULL);
	$category->setCustomDesignTo(NULL);
	$category->setPageLayout(NULL);
	$category->setCustomLayoutUpdate(NULL);
	//$category->setUrlPath();//Automatically created from UrlKey

	try {
	    $category->save();
	    $i++;
	    fwrite($transactionLogHandle, "    ->CREATED PLACEHOLDER ID->" . $category->getId() . "\n");
	}
	catch (Exception $e){
	    fwrite($transactionLogHandle, "    ->ERROR                 ->" . $e->getMessage() . "\n");
	}
}

fwrite($transactionLogHandle, "  ->CREATION COMPLETED\n");

fwrite($transactionLogHandle, "  ->UPDATING DEFAULT STORE CATEGORIES...\n");

foreach ($categories as $categoryArray) {

    //get a new category object
    $category = Mage::getModel('catalog/category');
    $category->setStoreId(0); // 0 = default/all store view. If you want to save data for a specific store view, replace 0 by Mage::app()->getStore()->getId().

    $id = $categoryArray['category_id'];

    // starting category id to skip root and default categories since already in system
    if ($id > 1) {

	$category->load($id);

	//$category->setAttributeSetId($category->getDefaultAttributeSetId());
	$category->setParentId($categoryArray['parent_id']);
	$category->setPath($categoryArray['path']);
	$category->setPosition($categoryArray['position']);
	$category->setLevel($categoryArray['level']);
	$category->setChildrenCount($categoryArray['children_count']);

	$category->setName($categoryArray['name']);
	if ($categoryArray['sneakerhead_only'] == 0) {
	    $category->setIsActive($categoryArray['is_active']);
	} else {
	    $category->setIsActive(0);
	}
	$category->setUrlKey($categoryArray['url_key']); // for sneakerhead.com
	$category->setIsOfflineCategory($categoryArray['is_offline_category']);
	$category->setHighlightedProduct($categoryArray['highlighted_product']);
	$category->setDescription($categoryArray['heading']);
	$category->setMetaTitle($categoryArray['meta_title']);
	$category->setMetaKeywords(NULL);//Add to update
	$category->setMetaDescription(NULL);//Add to update
	$category->setIncludeInMenu($categoryArray['include_in_menu']);
	$category->setDisplayMode('PRODUCTS');
	$category->setLandingPage(NULL);
	$category->setIsAnchor($categoryArray['is_anchor']);
	$category->setAvailableSortBy(NULL);
	//$category->setData('default_sort_by', false);
	$category->setFilterPriceRange(NULL);
	$category->setCustomUseParentSettings(0);
	$category->setCustomApplyToProducts(0);
	$category->setCustomDesign(NULL);
	$category->setCustomDesignFrom(NULL);
	$category->setCustomDesignTo(NULL);
	$category->setPageLayout(NULL);
	$category->setCustomLayoutUpdate(NULL);

	try {
	    $category->save();
	    $i++;
	    fwrite($transactionLogHandle, "    ->UPDATED CATEGORY ID   ->" . $category->getId() . "\n");
	}
	catch (Exception $e){
	    fwrite($transactionLogHandle, "    ->ERROR                 ->" . $e->getMessage() . "\n");
	}
    }
}

fwrite($transactionLogHandle, "  ->UPDATE COMPLETED\n");
fwrite($transactionLogHandle, "  ->UPDATING SNEAKERHEAD STORE CATEGORIES...\n");

//Set Root Category Children to 
//Sneakerhead.com Category Pages
foreach ($categories as $categoryArray) {
    //get a new category object
    $category = Mage::getModel('catalog/category');
    $category->setStoreId(21); // 0 = default/all store view. If you want to save data for a specific store view, replace 0 by Mage::app()->getStore()->getId().

    if ($categoryArray['sneakerhead_only'] == 0) {
	continue;
    }
    
    $id = $categoryArray['category_id'];

    // starting category id to skip root and default categories since already in system
    if ($id > 1) {

	$category->load($id);

	$category->setData('name', false);
	$category->setIsActive($categoryArray['is_active']);
	$category->setData('url_key', false);
	$category->setData('is_offline_category', false);
	$category->setData('highlighted_product', false);
	//$category->setData('thumbnail', false);
	$category->setData('description', false);
	//$category->setData('image', false);
	$category->setData('meta_title', false);
	$category->setData('meta_keywords', false);
	$category->setData('meta_description', false);
	$category->setData('include_in_menu', false);
	$category->setData('display_mode', false);
	$category->setData('landing_page', false);
	//$category->setData('is_anchor', false);//DO NOT SET -- IS GLOBAL
	$category->setData('available_sort_by', false);
	//$category->setData('default_sort_by', false);
	$category->setData('filter_price_range', false);
	$category->setData('custom_use_parent_settings', false);
	$category->setData('custom_apply_to_products', false);
	$category->setData('custom_design', false);
	$category->setData('custom_design_from', false);
	$category->setData('custom_design_to', false);
	$category->setData('page_layout', false);
	$category->setData('custom_layout_update', false);

	try {
	    $category->save();
	    $i++;
	    fwrite($transactionLogHandle, "    ->UPDATED CATEGORY ID   ->" . $category->getId() . "\n");
	}
	catch (Exception $e){
	    fwrite($transactionLogHandle, "    ->ERROR                 ->" . $e->getMessage() . "\n");
	}
    }
}

fwrite($transactionLogHandle, "  ->UPDATE COMPLETED\n");
fwrite($transactionLogHandle, "  ->UPDATING SNEAKERRX STORE CATEGORIES...\n");

//Sneakerrx.com Category Landing Pages
foreach ($categories as $categoryArray) {

    //get a new category object
    $category = Mage::getModel('catalog/category');
    $category->setStoreId(1); // 0 = default/all store view. If you want to save data for a specific store view, replace 0 by Mage::app()->getStore()->getId().

    $id = $categoryArray['category_id'];

    // starting category id to skip root and default categories since already in system
    if ($id > 1) {

	$category->load($id);

	// Mens
	if ($category->getId() == 6) {
	    $category->setData('name', false);
	    $category->setData('is_active', false);
	    $category->setData('url_key', false);
	    $category->setData('is_offline_category', false);
	    $category->setData('highlighted_product', false);
	    //$category->setData('thumbnail', false);
	    $category->setData('description', false);
	    //$category->setData('image', false);
	    $category->setData('meta_title', false);
	    $category->setData('meta_keywords', false);
	    $category->setData('meta_description', false);
	    $category->setData('include_in_menu', false);
	    //$category->setData('display_mode', false);
	    //$category->setData('landing_page', false);
	    //$category->setData('is_anchor', false);//DO NOT SET -- IS GLOBAL
	    $category->setData('available_sort_by', false);
	    //$category->setData('default_sort_by', false);
	    $category->setData('filter_price_range', false);
	    $category->setData('custom_use_parent_settings', false);
	    $category->setData('custom_apply_to_products', false);
	    $category->setData('custom_design', false);
	    $category->setData('custom_design_from', false);
	    $category->setData('custom_design_to', false);
	    $category->setData('page_layout', false);
	    //$category->setData('custom_layout_update', false);

	    $category->setDisplayMode('PAGE');//if landing assing PAGE else PRODUCTS		    
	    $category->setLandingPage(20);//assign if landing page
	    $category->setCustomLayoutUpdate('<reference name="head">        \r\n        <action method="addCss"><stylesheet>css/landing.css</stylesheet></action>        \r\n        </reference>\r\n<reference name="banner">\r\n    <block type="cms/block" name="sample_block" before="-">\r\n        <action method="setBlockId"><block_id>mens-banner</block_id></action>\r\n    </block>    \r\n</reference>');//if landing assign reference
	}

	// Mens Nike
	if ($category->getId() == 7) {
	    $category->setData('name', false);
	    $category->setData('is_active', false);
	    $category->setData('url_key', false);
	    $category->setData('is_offline_category', false);
	    $category->setData('highlighted_product', false);
	    //$category->setData('thumbnail', false);
	    $category->setData('description', false);
	    //$category->setData('image', false);
	    $category->setData('meta_title', false);
	    $category->setData('meta_keywords', false);
	    $category->setData('meta_description', false);
	    $category->setData('include_in_menu', false);
	    //$category->setData('display_mode', false);
	    //$category->setData('landing_page', false);
	    //$category->setData('is_anchor', false);//DO NOT SET -- IS GLOBAL
	    $category->setData('available_sort_by', false);
	    //$category->setData('default_sort_by', false);
	    $category->setData('filter_price_range', false);
	    $category->setData('custom_use_parent_settings', false);
	    $category->setData('custom_apply_to_products', false);
	    $category->setData('custom_design', false);
	    $category->setData('custom_design_from', false);
	    $category->setData('custom_design_to', false);
	    $category->setData('page_layout', false);
	    //$category->setData('custom_layout_update', false);
	    
	    $category->setDisplayMode('PAGE');//if landing assing PAGE else PRODUCTS		    
	    $category->setLandingPage(26);//assign if landing page
	    $category->setCustomLayoutUpdate('<reference name="head">        \r\n        <action method="addCss"><stylesheet>css/landing.css</stylesheet></action>        \r\n        </reference>\r\n<reference name="banner">\r\n    <block type="cms/block" name="sample_block" before="-">\r\n        <action method="setBlockId"><block_id>mens-nike-banner</block_id></action>\r\n    </block>    \r\n</reference>');//if landing assign reference
	}

	// Womens
	if ($category->getId() == 9) {
	    $category->setData('name', false);
	    $category->setData('is_active', false);
	    $category->setData('url_key', false);
	    $category->setData('is_offline_category', false);
	    $category->setData('highlighted_product', false);
	    //$category->setData('thumbnail', false);
	    $category->setData('description', false);
	    //$category->setData('image', false);
	    $category->setData('meta_title', false);
	    $category->setData('meta_keywords', false);
	    $category->setData('meta_description', false);
	    $category->setData('include_in_menu', false);
	    //$category->setData('display_mode', false);
	    //$category->setData('landing_page', false);
	    //$category->setData('is_anchor', false);//DO NOT SET -- IS GLOBAL
	    $category->setData('available_sort_by', false);
	    //$category->setData('default_sort_by', false);
	    $category->setData('filter_price_range', false);
	    $category->setData('custom_use_parent_settings', false);
	    $category->setData('custom_apply_to_products', false);
	    $category->setData('custom_design', false);
	    $category->setData('custom_design_from', false);
	    $category->setData('custom_design_to', false);
	    $category->setData('page_layout', false);
	    //$category->setData('custom_layout_update', false);
	    
	    $category->setDisplayMode('PAGE');//if landing assing PAGE else PRODUCTS		    
	    $category->setLandingPage(22);//assign if landing page
	    $category->setCustomLayoutUpdate('<reference name="head">        \r\n        <action method="addCss"><stylesheet>css/landing.css</stylesheet></action>        \r\n        </reference>\r\n<reference name="banner">\r\n    <block type="cms/block" name="sample_block" before="-">\r\n        <action method="setBlockId"><block_id>womens-banner</block_id></action>\r\n    </block>    \r\n</reference>');//if landing assign reference
	}

	// Womens Nike
	if ($category->getId() == 11) {
	    $category->setData('name', false);
	    $category->setData('is_active', false);
	    $category->setData('url_key', false);
	    $category->setData('is_offline_category', false);
	    $category->setData('highlighted_product', false);
	    //$category->setData('thumbnail', false);
	    $category->setData('description', false);
	    //$category->setData('image', false);
	    $category->setData('meta_title', false);
	    $category->setData('meta_keywords', false);
	    $category->setData('meta_description', false);
	    $category->setData('include_in_menu', false);
	    //$category->setData('display_mode', false);
	    //$category->setData('landing_page', false);
	    //$category->setData('is_anchor', false);//DO NOT SET -- IS GLOBAL
	    $category->setData('available_sort_by', false);
	    //$category->setData('default_sort_by', false);
	    $category->setData('filter_price_range', false);
	    $category->setData('custom_use_parent_settings', false);
	    $category->setData('custom_apply_to_products', false);
	    $category->setData('custom_design', false);
	    $category->setData('custom_design_from', false);
	    $category->setData('custom_design_to', false);
	    $category->setData('page_layout', false);
	    //$category->setData('custom_layout_update', false);
	    
	    $category->setDisplayMode('PAGE');//if landing assing PAGE else PRODUCTS		    
	    $category->setLandingPage(28);//assign if landing page
	    $category->setCustomLayoutUpdate('<reference name="head">        \r\n        <action method="addCss"><stylesheet>css/landing.css</stylesheet></action>        \r\n        </reference>\r\n<reference name="banner">\r\n    <block type="cms/block" name="sample_block" before="-">\r\n        <action method="setBlockId"><block_id>womens-nike-banner</block_id></action>\r\n    </block>    \r\n</reference>');//if landing assign reference
	}

	// Kids
	if ($category->getId() == 10) {
	    $category->setData('name', false);
	    $category->setData('is_active', false);
	    $category->setData('url_key', false);
	    $category->setData('is_offline_category', false);
	    $category->setData('highlighted_product', false);
	    //$category->setData('thumbnail', false);
	    $category->setData('description', false);
	    //$category->setData('image', false);
	    $category->setData('meta_title', false);
	    $category->setData('meta_keywords', false);
	    $category->setData('meta_description', false);
	    $category->setData('include_in_menu', false);
	    //$category->setData('display_mode', false);
	    //$category->setData('landing_page', false);
	    //$category->setData('is_anchor', false);//DO NOT SET -- IS GLOBAL
	    $category->setData('available_sort_by', false);
	    //$category->setData('default_sort_by', false);
	    $category->setData('filter_price_range', false);
	    $category->setData('custom_use_parent_settings', false);
	    $category->setData('custom_apply_to_products', false);
	    $category->setData('custom_design', false);
	    $category->setData('custom_design_from', false);
	    $category->setData('custom_design_to', false);
	    $category->setData('page_layout', false);
	    //$category->setData('custom_layout_update', false);
	    
	    $category->setDisplayMode('PAGE');//if landing assing PAGE else PRODUCTS		    
	    $category->setLandingPage(24);//assign if landing page
	    $category->setCustomLayoutUpdate('<reference name="head">        \r\n        <action method="addCss"><stylesheet>css/landing.css</stylesheet></action>        \r\n        </reference>\r\n<reference name="banner">\r\n    <block type="cms/block" name="sample_block" before="-">\r\n        <action method="setBlockId"><block_id>kids-banner</block_id></action>\r\n    </block>    \r\n</reference>');//if landing assign reference
	}


	// Kids Nike
	if ($category->getId() == 12) {
	    $category->setData('name', false);
	    $category->setData('is_active', false);
	    $category->setData('url_key', false);
	    $category->setData('is_offline_category', false);
	    $category->setData('highlighted_product', false);
	    //$category->setData('thumbnail', false);
	    $category->setData('description', false);
	    //$category->setData('image', false);
	    $category->setData('meta_title', false);
	    $category->setData('meta_keywords', false);
	    $category->setData('meta_description', false);
	    $category->setData('include_in_menu', false);
	    //$category->setData('display_mode', false);
	    //$category->setData('landing_page', false);
	    //$category->setData('is_anchor', false);//DO NOT SET -- IS GLOBAL
	    $category->setData('available_sort_by', false);
	    //$category->setData('default_sort_by', false);
	    $category->setData('filter_price_range', false);
	    $category->setData('custom_use_parent_settings', false);
	    $category->setData('custom_apply_to_products', false);
	    $category->setData('custom_design', false);
	    $category->setData('custom_design_from', false);
	    $category->setData('custom_design_to', false);
	    $category->setData('page_layout', false);
	    //$category->setData('custom_layout_update', false);
	    
	    $category->setDisplayMode('PAGE');//if landing assing PAGE else PRODUCTS		    
	    $category->setLandingPage(30);//assign if landing page
	    $category->setCustomLayoutUpdate('<reference name="head">        \r\n        <action method="addCss"><stylesheet>css/landing.css</stylesheet></action>        \r\n        </reference>\r\n<reference name="banner">\r\n    <block type="cms/block" name="sample_block" before="-">\r\n        <action method="setBlockId"><block_id>kids-nike-banner</block_id></action>\r\n    </block>    \r\n</reference>');//if landing assign reference
	}

	try {
	    $category->save();
	    $i++;
	    fwrite($transactionLogHandle, "    ->UPDATED CATEGORY ID   ->" . $category->getId() . "\n");
	}
	catch (Exception $e){
	    fwrite($transactionLogHandle, "    ->ERROR                 ->" . $e->getMessage() . "\n");
	}
    }
}

fwrite($transactionLogHandle, "  ->UPDATE COMPLETED\n");
fwrite($transactionLogHandle, "  ->UPDATING MILITARY STORE CATEGORIES...\n");

//Military Sneakerrx.com Category Landing Pages
foreach ($categories as $categoryArray) {

    //get a new category object
    $category = Mage::getModel('catalog/category');
    $category->setStoreId(20); // 0 = default/all store view. If you want to save data for a specific store view, replace 0 by Mage::app()->getStore()->getId().

    $id = $categoryArray['category_id'];

    // starting category id to skip root and default categories since already in system
    if ($id > 1) {

	$category->load($id);

	// Mens
	if ($category->getId() == 6) {
	    $category->setData('name', false);
	    $category->setData('is_active', false);
	    $category->setData('url_key', false);
	    $category->setData('is_offline_category', false);
	    $category->setData('highlighted_product', false);
	    //$category->setData('thumbnail', false);
	    $category->setData('description', false);
	    //$category->setData('image', false);
	    $category->setData('meta_title', false);
	    $category->setData('meta_keywords', false);
	    $category->setData('meta_description', false);
	    $category->setData('include_in_menu', false);
	    //$category->setData('display_mode', false);
	    //$category->setData('landing_page', false);
	    //$category->setData('is_anchor', false);//DO NOT SET -- IS GLOBAL
	    $category->setData('available_sort_by', false);
	    //$category->setData('default_sort_by', false);
	    $category->setData('filter_price_range', false);
	    $category->setData('custom_use_parent_settings', false);
	    $category->setData('custom_apply_to_products', false);
	    $category->setData('custom_design', false);
	    $category->setData('custom_design_from', false);
	    $category->setData('custom_design_to', false);
	    $category->setData('page_layout', false);
	    //$category->setData('custom_layout_update', false);
	    
	    $category->setDisplayMode('PAGE');//if landing assing PAGE else PRODUCTS		    
	    $category->setLandingPage(20);//assign if landing page
	    $category->setCustomLayoutUpdate('<reference name="head">        \r\n        <action method="addCss"><stylesheet>css/landing.css</stylesheet></action>        \r\n        </reference>\r\n<reference name="banner">\r\n    <block type="cms/block" name="sample_block" before="-">\r\n        <action method="setBlockId"><block_id>mens-banner</block_id></action>\r\n    </block>    \r\n</reference>');//if landing assign reference
	}

	// Mens Nike
	if ($category->getId() == 7) {
	    $category->setData('name', false);
	    $category->setData('is_active', false);
	    $category->setData('url_key', false);
	    $category->setData('is_offline_category', false);
	    $category->setData('highlighted_product', false);
	    //$category->setData('thumbnail', false);
	    $category->setData('description', false);
	    //$category->setData('image', false);
	    $category->setData('meta_title', false);
	    $category->setData('meta_keywords', false);
	    $category->setData('meta_description', false);
	    $category->setData('include_in_menu', false);
	    //$category->setData('display_mode', false);
	    //$category->setData('landing_page', false);
	    //$category->setData('is_anchor', false);//DO NOT SET -- IS GLOBAL
	    $category->setData('available_sort_by', false);
	    //$category->setData('default_sort_by', false);
	    $category->setData('filter_price_range', false);
	    $category->setData('custom_use_parent_settings', false);
	    $category->setData('custom_apply_to_products', false);
	    $category->setData('custom_design', false);
	    $category->setData('custom_design_from', false);
	    $category->setData('custom_design_to', false);
	    $category->setData('page_layout', false);
	    //$category->setData('custom_layout_update', false);
	    
	    $category->setDisplayMode('PAGE');//if landing assing PAGE else PRODUCTS		    
	    $category->setLandingPage(26);//assign if landing page
	    $category->setCustomLayoutUpdate('<reference name="head">        \r\n        <action method="addCss"><stylesheet>css/landing.css</stylesheet></action>        \r\n        </reference>\r\n<reference name="banner">\r\n    <block type="cms/block" name="sample_block" before="-">\r\n        <action method="setBlockId"><block_id>mens-nike-banner</block_id></action>\r\n    </block>    \r\n</reference>');//if landing assign reference
	}

	// Womens
	if ($category->getId() == 9) {
	    $category->setData('name', false);
	    $category->setData('is_active', false);
	    $category->setData('url_key', false);
	    $category->setData('is_offline_category', false);
	    $category->setData('highlighted_product', false);
	    //$category->setData('thumbnail', false);
	    $category->setData('description', false);
	    //$category->setData('image', false);
	    $category->setData('meta_title', false);
	    $category->setData('meta_keywords', false);
	    $category->setData('meta_description', false);
	    $category->setData('include_in_menu', false);
	    //$category->setData('display_mode', false);
	    //$category->setData('landing_page', false);
	    //$category->setData('is_anchor', false);//DO NOT SET -- IS GLOBAL
	    $category->setData('available_sort_by', false);
	    //$category->setData('default_sort_by', false);
	    $category->setData('filter_price_range', false);
	    $category->setData('custom_use_parent_settings', false);
	    $category->setData('custom_apply_to_products', false);
	    $category->setData('custom_design', false);
	    $category->setData('custom_design_from', false);
	    $category->setData('custom_design_to', false);
	    $category->setData('page_layout', false);
	    //$category->setData('custom_layout_update', false);
	    
	    $category->setDisplayMode('PAGE');//if landing assing PAGE else PRODUCTS		    
	    $category->setLandingPage(22);//assign if landing page
	    $category->setCustomLayoutUpdate('<reference name="head">        \r\n        <action method="addCss"><stylesheet>css/landing.css</stylesheet></action>        \r\n        </reference>\r\n<reference name="banner">\r\n    <block type="cms/block" name="sample_block" before="-">\r\n        <action method="setBlockId"><block_id>womens-banner</block_id></action>\r\n    </block>    \r\n</reference>');//if landing assign reference
	}

	// Womens Nike
	if ($category->getId() == 11) {
	    $category->setData('name', false);
	    $category->setData('is_active', false);
	    $category->setData('url_key', false);
	    $category->setData('is_offline_category', false);
	    $category->setData('highlighted_product', false);
	    //$category->setData('thumbnail', false);
	    $category->setData('description', false);
	    //$category->setData('image', false);
	    $category->setData('meta_title', false);
	    $category->setData('meta_keywords', false);
	    $category->setData('meta_description', false);
	    $category->setData('include_in_menu', false);
	    //$category->setData('display_mode', false);
	    //$category->setData('landing_page', false);
	    //$category->setData('is_anchor', false);//DO NOT SET -- IS GLOBAL
	    $category->setData('available_sort_by', false);
	    //$category->setData('default_sort_by', false);
	    $category->setData('filter_price_range', false);
	    $category->setData('custom_use_parent_settings', false);
	    $category->setData('custom_apply_to_products', false);
	    $category->setData('custom_design', false);
	    $category->setData('custom_design_from', false);
	    $category->setData('custom_design_to', false);
	    $category->setData('page_layout', false);
	    //$category->setData('custom_layout_update', false);
	    
	    $category->setDisplayMode('PAGE');//if landing assing PAGE else PRODUCTS		    
	    $category->setLandingPage(28);//assign if landing page
	    $category->setCustomLayoutUpdate('<reference name="head">        \r\n        <action method="addCss"><stylesheet>css/landing.css</stylesheet></action>        \r\n        </reference>\r\n<reference name="banner">\r\n    <block type="cms/block" name="sample_block" before="-">\r\n        <action method="setBlockId"><block_id>womens-nike-banner</block_id></action>\r\n    </block>    \r\n</reference>');//if landing assign reference
	}

	// Kids
	if ($category->getId() == 10) {
	    $category->setData('name', false);
	    $category->setData('is_active', false);
	    $category->setData('url_key', false);
	    $category->setData('is_offline_category', false);
	    $category->setData('highlighted_product', false);
	    //$category->setData('thumbnail', false);
	    $category->setData('description', false);
	    //$category->setData('image', false);
	    $category->setData('meta_title', false);
	    $category->setData('meta_keywords', false);
	    $category->setData('meta_description', false);
	    $category->setData('include_in_menu', false);
	    //$category->setData('display_mode', false);
	    //$category->setData('landing_page', false);
	    //$category->setData('is_anchor', false);//DO NOT SET -- IS GLOBAL
	    $category->setData('available_sort_by', false);
	    //$category->setData('default_sort_by', false);
	    $category->setData('filter_price_range', false);
	    $category->setData('custom_use_parent_settings', false);
	    $category->setData('custom_apply_to_products', false);
	    $category->setData('custom_design', false);
	    $category->setData('custom_design_from', false);
	    $category->setData('custom_design_to', false);
	    $category->setData('page_layout', false);
	    //$category->setData('custom_layout_update', false);
	    
	    $category->setDisplayMode('PAGE');//if landing assing PAGE else PRODUCTS		    
	    $category->setLandingPage(24);//assign if landing page
	    $category->setCustomLayoutUpdate('<reference name="head">        \r\n        <action method="addCss"><stylesheet>css/landing.css</stylesheet></action>        \r\n        </reference>\r\n<reference name="banner">\r\n    <block type="cms/block" name="sample_block" before="-">\r\n        <action method="setBlockId"><block_id>kids-banner</block_id></action>\r\n    </block>    \r\n</reference>');//if landing assign reference
	}


	// Kids Nike
	if ($category->getId() == 12) {
	    $category->setData('name', false);
	    $category->setData('is_active', false);
	    $category->setData('url_key', false);
	    $category->setData('is_offline_category', false);
	    $category->setData('highlighted_product', false);
	    //$category->setData('thumbnail', false);
	    $category->setData('description', false);
	    //$category->setData('image', false);
	    $category->setData('meta_title', false);
	    $category->setData('meta_keywords', false);
	    $category->setData('meta_description', false);
	    $category->setData('include_in_menu', false);
	    //$category->setData('display_mode', false);
	    //$category->setData('landing_page', false);
	    //$category->setData('is_anchor', false);//DO NOT SET -- IS GLOBAL
	    $category->setData('available_sort_by', false);
	    //$category->setData('default_sort_by', false);
	    $category->setData('filter_price_range', false);
	    $category->setData('custom_use_parent_settings', false);
	    $category->setData('custom_apply_to_products', false);
	    $category->setData('custom_design', false);
	    $category->setData('custom_design_from', false);
	    $category->setData('custom_design_to', false);
	    $category->setData('page_layout', false);
	    //$category->setData('custom_layout_update', false);
	    
	    $category->setDisplayMode('PAGE');//if landing assing PAGE else PRODUCTS		    
	    $category->setLandingPage(30);//assign if landing page
	    $category->setCustomLayoutUpdate('<reference name="head">        \r\n        <action method="addCss"><stylesheet>css/landing.css</stylesheet></action>        \r\n        </reference>\r\n<reference name="banner">\r\n    <block type="cms/block" name="sample_block" before="-">\r\n        <action method="setBlockId"><block_id>kids-nike-banner</block_id></action>\r\n    </block>    \r\n</reference>');//if landing assign reference
	}

	try {
	    $category->save();
	    $i++;
	    fwrite($transactionLogHandle, "    ->UPDATED CATEGORY ID   ->" . $category->getId() . "\n");
	}
	catch (Exception $e){
	    fwrite($transactionLogHandle, "    ->ERROR                 ->" . $e->getMessage() . "\n");
	}
    }
}

fwrite($transactionLogHandle, "  ->UPDATE COMPLETED\n");

//REFRESH block level cache (refreshes category and product detail pages)-- This will be targeted by ID in the future
//Note full_page doesn't seem to be required???

//$types=array( 
//    2=>"block_html",
//    7=>"full_page"
//);
//$allTypes = Mage::app()->useCache();
//
//$updatedTypes = 0;
//if (!empty($types)) {
//    echo "->REFRESHING CACHE\n";
//
//    foreach ($types as $type) {
//	echo "  ->START       : " . $type . "\n";
//	Mage::dispatchEvent('adminhtml_cache_refresh_type', array('type' => $type));
//	echo "  ->END         : " . $type . "\n";
//    }
//    echo "->REFRESHED\n";
//}

//Enable indexes
fwrite($transactionLogHandle, "  ->CHANGE INDEX  :AUTO\n");
$processCollection = Mage::getSingleton('index/indexer')->getProcessesCollection(); 
foreach ($processCollection as $process) {
  $process->setMode(Mage_Index_Model_Process::MODE_REAL_TIME)->save();
}
fwrite($transactionLogHandle, "  ->CHANGED INDEX :AUTO\n");

fwrite($transactionLogHandle, "->END PROCESSING\n");
//Close transaction log
fclose($transactionLogHandle);

?>