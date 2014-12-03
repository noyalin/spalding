<?php
class Shoe_Maker_Model_CategoryUpdate extends Shoe_Maker_Model_UpdateBase{
    public $reindex;
    public function __construct($reindex=null){
        // Flag used to initiate reindexing for url rewrites when new category found
        $this->reindex = $reindex;
        parent :: __construct();
    }
    public function run(){
        $categories = $this->getCategoryFromFile($this->contents);

        $this->transactionLogHandle( "  ->ACQUIRING RESOURCE CONNECTION...");


        $this->transactionLogHandle( "  ->CREATING/UPDATING CATEGORIES FOR DEFAULT STORE");

        $this->executeCategory($categories);

        $this->transactionLogHandle( "  ->CATEGORIES CREATED/UPDATED FOR DEFAULT STORE ");
        $this->transactionLogHandle( "  ->UPDATING CHILD COUNTS ");

        $this->updateChildCount($categories);

        $this->transactionLogHandle( "  ->CHILD COUNTS UPDATED ");

        // Create a unique array of just the "sneakerhead only" categories that
        // need to be modified for mobile


        $sneakerheadOnlyCategories = $this->updateSneakerheadCom($categories);

//        $this->updateSneakerheadMobile($sneakerheadOnlyCategories);

        $this->runReindex();

        $this->transactionLogHandle( "  ->UPDATE COMPLETED FOR SNEAKERHEAD ");

    }

    /**
     * useless in sneakerhead cn site
     * @param $sneakerheadOnlyCategories
     */
    /*
    public function updateSneakerheadMobile($sneakerheadOnlyCategories){
        $this->transactionLogHandle(  "  ->UPDATING SNEAKERHEAD MOBILE STORE CATEGORIES... ");

        //Sneakerhead.com MOBILE Category Pages
        foreach ($sneakerheadOnlyCategories as $categoryArray) {
            //get a new category object
            $category = Mage::getModel('catalog/category');
            $category->setStoreId(22);

            $id = $categoryArray['category_id'];
            $category->load($id);

            //Set all values to default/remove overrides using setData
            $category->setData('name', false);
            $category->setData('url_key', false);
            $category->setData('is_offline_category', false);
            $category->setData('highlighted_product', false);
            //$category->setData('thumbnail', false); //not using right now
            $category->setData('description', false);
            //$category->setData('image', false); //not using right now
            $category->setData('meta_title', false);
            $category->setData('meta_keywords', false);
            $category->setData('meta_description', false);
            $category->setData('canonical', false);
            $category->setData('include_in_menu', false);
            $category->setData('display_mode', false);
            $category->setData('landing_page', false);
            //$category->setData('is_anchor', false);//DO NOT SET -- IS GLOBAL
            $category->setData('available_sort_by', false);
            //$category->setData('default_sort_by', false); //not using right now
            $category->setData('filter_price_range', false);
            $category->setData('custom_use_parent_settings', false);
            $category->setData('custom_apply_to_products', false);
            $category->setData('custom_design', false);
            $category->setData('custom_design_from', false);
            $category->setData('custom_design_to', false);
            $category->setData('page_layout', false);
            $category->setData('custom_layout_update', false);

            $setIsActive = $categoryArray['is_active'];

            if ($setIsActive === 1) {
                // Check if this category_id or its ancestor is excluded
                // $exclude_categories defined in config.php
                $path = $categoryArray['path'];
                $parent_ids = explode('/', $path);
                if (is_array($parent_ids) && count($parent_ids)) {
                    foreach($parent_ids as $parent_id) {
                        if (in_array($parent_id, $this->mobile_exclude_categories)) {
                            $setIsActive = 0;
                            break;
                        }
                    }
                }
            }

            $category->setIsActive($setIsActive);

            try {
                $category->save();
                $this->transactionLogHandle( "    ->UPDATED CATEGORY ID   ->" . $category->getId() );
            } catch (Exception $e) {
                $this->transactionLogHandle( "    ->ERROR                 ->" . $e->getMessage()  );
            }
            unset($category);
        }
    }
*/
    public function updateSneakerheadCom($categories){
        $sneakerheadOnlyCategories = array();
        $this->transactionLogHandle( "  ->UPDATING SNEAKERHEAD STORE CATEGORIES...");
        //Sneakerhead.com Category Pages
        foreach ($categories as $categoryArray) {
            //get a new category object
            $category = Mage::getModel('catalog/category');
            $category->setStoreId(1); // 0 = default/all store view. If you want to save data for a specific store view, replace 0 by Mage::app()->getStore()->getId().

            $id = $categoryArray['category_id'];
            $category->load($id);

            //Set all values to default/remove overrides using setData
            $category->setData('name', false);
            $category->setData('url_key', false);
            $category->setData('is_offline_category', false);
            $category->setData('highlighted_product', false);
            //$category->setData('thumbnail', false); //not using right now
            $category->setData('description', false);
            //$category->setData('image', false); //not using right now
            $category->setData('meta_title', false);
            $category->setData('meta_keywords', false);
            $category->setData('meta_description', false);
            $category->setData('canonical', false);
            $category->setData('include_in_menu', false);
            $category->setData('display_mode', false);
            $category->setData('landing_page', false);
            //$category->setData('is_anchor', false);//DO NOT SET -- IS GLOBAL
            $category->setData('available_sort_by', false);
            //$category->setData('default_sort_by', false); //not using right now
            $category->setData('filter_price_range', false);
            $category->setData('custom_use_parent_settings', false);
            $category->setData('custom_apply_to_products', false);
            $category->setData('custom_design', false);
            $category->setData('custom_design_from', false);
            $category->setData('custom_design_to', false);
            $category->setData('page_layout', false);
            $category->setData('custom_layout_update', false);

            if ($categoryArray['sneakerhead_only'] == 0) {
                $category->setData('is_active', false);
            } else {
                $category->setIsActive($categoryArray['is_active']);
                $sneakerheadOnlyCategories[] = $categoryArray;
            }

            try {
                $category->save();
                $this->transactionLogHandle( "    ->UPDATED CATEGORY ID   ->" . $category->getId()  );
            } catch (Exception $e) {
                $this->transactionLogHandle( "    ->ERROR - UPDATED CATEGORY ID $id             ->" . $e->getMessage() );
            }
            unset($category);
        }
        return $sneakerheadOnlyCategories;
    }

    public function updateChildCount($categories){
        $resource = Mage::getSingleton('core/resource');
        $writeConnection = $resource->getConnection('core_write');
        foreach ($categories as $categoryArray) {
            $query = "SELECT COUNT(*) AS `count` FROM `catalog_category_entity` WHERE `path` LIKE '" . $categoryArray['path'] . "/%'";
            $results = $writeConnection->query($query);
            foreach ($results as $result) {
                $category = Mage::getModel('catalog/category');
                $category->setStoreId(0); // 0 = default/all store view.
                $category->load($categoryArray['category_id']);
                $category->setChildrenCount($result['count']);
                try {
                    $category->save();
                    $this->transactionLogHandle( "    ->CHILD COUNT UPDATED   ->" . $category->getId() . "->" . $result['count'] );
                } catch (Exception $e) {
                    $idError = $categoryArray['category_id'];
                    $this->transactionLogHandle( "    ->ERROR  CHILD COUNT UPDATED   $idError            ->" . $e->getMessage() );
                }
                unset($category);
            }
        }
    }

    public function executeCategory($categories){
        // Get resource
        $resource = Mage::getSingleton('core/resource');
        $writeConnection = $resource->getConnection('core_write');
        $realTime = $this->realTime();
        foreach ($categories as $categoryArray) {
            //get a new category object
            $category = Mage::getModel('catalog/category');
            $category->setStoreId(0); // 0 = default/all store view.
            $category->load($categoryArray['category_id']);
            if (!$category->getId()) {
                //ATTEMPT TO CREATE IT
                //$query = "ALTER TABLE `catalog_category_entity` AUTO_INCREMENT = " . $categoryArray['category_id'];

                $query = "INSERT INTO `catalog_category_entity` (`entity_id`, `entity_type_id`, `attribute_set_id`, `parent_id`, `created_at`, `updated_at`, `path`, `position`, `level`, `children_count`) VALUES
(" . $categoryArray['category_id'] . ", 3, 3, 2, '" . $realTime[5] . '-' . $realTime[4] . '-' . $realTime[3] . ' ' . $realTime[2] . ':' . $realTime[1] . ':' . $realTime[0] . "', NULL, '1/2/" . $categoryArray['category_id'] . "', 10000, 2, 0)";

                $results = $writeConnection->query($query);
                $this->transactionLogHandle( "    ->CREATING CATEGORY     ->" . $categoryArray['category_id'] );

                $reindex = NULL;//turn off due to issues
                //
                //		$category->setAttributeSetId($category->getDefaultAttributeSetId());
                //		$category->setParentId(1);
                //		$category->setPath('1');
                //		$category->setPosition(1);
                //		$category->setLevel(1);
                //		$category->setChildrenCount(0);
                //		$category->setName('placeholder');
                //		$category->setIsActive(0);
                //		$category->setUrlKey('placeholder');
                //		$category->setIsOfflineCategory(0);
                //		$category->setHighlightedProduct(NULL);
                //		//$category->setData('thumbnail', false); //not using right now
                //		$category->setDescription(NULL);
                //		//$category->setData('image', false); //not using right now
                //		$category->setMetaTitle(NULL);
                //		$category->setMetaKeywords(NULL);
                //		$category->setMetaDescription(NULL);
                //		$category->setIncludeInMenu(1);
                //		$category->setDisplayMode('PRODUCTS');//if landing assing PAGE else PRODUCTS
                //		$category->setLandingPage(NULL);
                //		$category->setIsAnchor(0);
                //		$category->setAvailableSortBy(NULL);
                //		//$category->setData('default_sort_by', false); //not using right now
                //		$category->setFilterPriceRange(NULL);
                //		$category->setCustomUseParentSettings(0);
                //		$category->setCustomApplyToProducts(0);
                //		$category->setCustomDesign(NULL);
                //		$category->setCustomDesignFrom(NULL);
                //		$category->setCustomDesignTo(NULL);
                //		$category->setPageLayout(NULL);
                //		$category->setCustomLayoutUpdate(NULL);
                //		//$category->setUrlPath();//Automatically created from UrlKey//NO NEED TO SET
                //
                //		try {
                //		    $category->save();
                //		    $reindex = true;
                //		}
                //		catch (Exception $e){
                //		    fwrite($transactionLogHandle, "    ->ERROR                 ->" . $e->getMessage() . "\n");
                //		}
                //		unset ($category);
            }

            $category = Mage::getModel('catalog/category');
            $category->setStoreId(0); // 0 = default/all store view.
            $category->load($categoryArray['category_id']);

            $this->transactionLogHandle( "    ->UPDATING CATEGORY     ->" . $category->getId() );

            //$category->setAttributeSetId($category->getDefaultAttributeSetId());//Does not need to be set again as it does not change
            $category->setParentId($categoryArray['parent_id']);
            $category->setPath($categoryArray['path']);
            $category->setPosition($categoryArray['position']);
            $category->setLevel($categoryArray['level']);
            //$category->setChildrenCount($categoryArray['children_count']); //Set below

            $category->setName($categoryArray['name']);
            if ($categoryArray['sneakerhead_only'] == 0) {
                $category->setIsActive($categoryArray['is_active']);
            } else {
                $category->setIsActive(0);
            }
            $category->setUrlKey($categoryArray['url_key']); // for sneakerhead.com
            $category->setIsOfflineCategory($categoryArray['is_offline_category']);
            if ($categoryArray['highlighted_product'] != '') {
                $category->setHighlightedProduct($categoryArray['highlighted_product']);
            } else {
                $category->setHighlightedProduct(NULL);
            }
            //$category->setData('thumbnail', false); //not using right now
            if ($categoryArray['heading'] != '') {
                $category->setDescription($categoryArray['heading']);
            } else {
                $category->setDescription(NULL);
            }
            //$category->setData('image', false); //not using right now
            $category->setMetaTitle($categoryArray['meta_title']);
            if ($categoryArray['meta_description'] != '') {
                $category->setMetaDescription($categoryArray['meta_description']);
            } else {
                $category->setMetaDescription(NULL);
            }
            if ($categoryArray['meta_keywords'] != '') {
                $category->setMetaKeywords($categoryArray['meta_keywords']);
            } else {
                $category->setMetaKeywords(NULL);
            }
            if ($categoryArray['canonical'] != '') {
                $category->setCanonical($categoryArray['canonical']);
            } else {
//                $category->setMetaKeywords(NULL);
            }
            $category->setIncludeInMenu($categoryArray['include_in_menu']);
            //$category->setDisplayMode('PRODUCTS'); // Set via admin panel -- prevent overwrite
            //$category->setLandingPage(NULL); // Set via admin panel -- prevent overwrite
            $category->setIsAnchor($categoryArray['is_anchor']);
            //$category->setAvailableSortBy(NULL); //not using right now
            //$category->setData('default_sort_by', false); //not using right now
            //$category->setFilterPriceRange(NULL); //not using right now
            $category->setCustomUseParentSettings($categoryArray['custom_use_parent_settings']);
            //$category->setCustomApplyToProducts(0); //not using right now
            //$category->setCustomDesign(NULL); //not using right now
            //$category->setCustomDesignFrom(NULL); //not using right now
            //$category->setCustomDesignTo(NULL); //not using right now
            //$category->setPageLayout(NULL); //not using right now
            //$category->setCustomLayoutUpdate(NULL); // Set via admin panel -- prevent overwrite
            //$category->setUrlPath();//Automatically created from UrlKey//NO NEED TO SET

            try {
                $category->save();
            } catch (Exception $e) {
                $categoryIdError = $categoryArray['category_id'];
                $this->transactionLogHandle( "    ->ERROR   in function   executeCategory $categoryIdError           ->" . $e->getMessage() );
            }

            if ($category->getId() != $categoryArray['category_id']) {
                $this->transactionLogHandle( "    ->ERROR                 ->CATEGORY ID MISMATCH ");
                throw new Exception("CATEGORY ID MISMATCH");
            } else {
                $this->transactionLogHandle( "    ->CREATED/UPDATED CAT   ->" . $category->getId() . "\n");
            }
            unset($category);
        }
    }

    /**
     * @param $contents
     * @return array
     * Get $categories from xml file
     */
    public function getCategoryFromFile($contents){
        $categories = array();
        list($rootXmlElement,$entityTypes) = self :: getXmlElementFromString($contents);
        //Process categories if entity is present
        if (isset($entityTypes['Categories'])) {

            $this->transactionLogHandle( "  ->BUILDING ARRAY... ");

            //Step into entities array to access configurable products
            foreach ($rootXmlElement->Categories as $entities) {
                //Loop through categories
                foreach ($entities->Category as $entity) {
                    $this->handleEachEntity($entity,$categories);
                }
            }
        }
        return $categories;
    }

    public function handleEachEntity($entity,&$categories){
        $categoryId = (string) $entity->Id;

        if (in_array($categoryId, $this->categoryListToNotProcess)) {

            $this->transactionLogHandle( "    ->SKIPPING CATEGORY " . $categoryId . "...");

            $message = "Category Update: " . $this->filename . "\r\n\r\nA category update for a landing page was received and was skipped to force a manual changed.";
            $this->sendNotification( 'Category Is Landing Page - Not Updated', $message);
            return;
        }

        $categories[$categoryId]['category_id'] = $categoryId;
        $categories[$categoryId]['parent_id'] = (string) $entity->ParentId;
        $categories[$categoryId]['path'] = (string) $entity->CategoryPath;
        $categories[$categoryId]['position'] = (string) $entity->DisplayOrder;
        $categories[$categoryId]['name'] = (string) $entity->Name;
        $categories[$categoryId]['sneakerhead_only'] = $entity->IsSneakerheadOnly;
        $categories[$categoryId]['is_active'] = $entity->IsPublished;
        $categories[$categoryId]['url_key'] = (string) $entity->UrlKey;
        $categories[$categoryId]['highlighted_product'] = (string) $entity->HighlightedProductCode;
        $categories[$categoryId]['heading'] = (string) $entity->HeaderTitle;
        $categories[$categoryId]['meta_title'] = (string) $entity->SEO_Title;
        $categories[$categoryId]['meta_description'] = (string) $entity->SEO_Description;
        $categories[$categoryId]['meta_keywords'] = (string) $entity->SEO_Keywords;
        $categories[$categoryId]['canonical'] = (string) $entity->SEO_Canonical;
        $categories[$categoryId]['include_in_menu'] = $entity->IsVisibleOnMenu;
        $categories[$categoryId]['is_anchor'] = $entity->IsEnableFilter;
        $categoryPath = explode('/', (string) $entity->CategoryPath);
        $counter = 0;
        foreach ($categoryPath as $checkCategory) {
            if ($checkCategory == $entity->Id) {
                $categories[$categoryId]['level'] = $counter;
            }
            $counter++;
        }

        //Set offline for sneakerfolio category
        if (count($categoryPath)>2 &&  $categoryPath[2] == 375) {
            $categories[$categoryId]['is_offline_category'] = 1;
        } else {
            $categories[$categoryId]['is_offline_category'] = 0;
        }

        //Set user parent settings for outlet, customer service, blog and sneakerfolio
        if (($categoryId != 150 && count($categoryPath)>2 && $categoryPath[2] == 150)
            || ($categoryId != 200 && $categoryId != 213 && count($categoryPath)>2  && $categoryPath[2] == 200)
            || ($categoryId != 300 && count($categoryPath)>2 && $categoryPath[2] == 300)
            || ($categoryId != 375 && count($categoryPath)>2 && $categoryPath[2] == 375)) {
            $categories[$categoryId]['custom_use_parent_settings'] = 1;
        } else {
            $categories[$categoryId]['custom_use_parent_settings'] = 0;
        }
    }

    /**
     * Does not appear to be needed except on a full rebuild of the categories
     */
    public function runReindex(){
        //Does not appear to be needed except on a full rebuild of the categories
        if ($this->reindex) {
            //Load adminhtml area of config.xml to initialize adminhtml observers
            Mage::app()->loadArea('adminhtml');

            $this->transactionLogHandle( "  ->INDEXING" );

            try {
                // Reindex urls
                $processIds = array(3);

                foreach ($processIds as $processId) {
                    $this->transactionLogHandle( "    ->START       : " . $processId  );
                    $process = Mage::getModel('index/process')->load($processId);
                    $process->reindexAll();
                    $this->transactionLogHandle( "    ->END         : " . $processId );
                }

                $this->transactionLogHandle( "  ->COMPLETED ");
            } catch (Exception $e) {
                $this->transactionLogHandle( "  ->NOT COMPLETED : " . $e );
            }
        }
    }

    public function validate(){
        $catalogLogsDirectory = $this->catalogLogsDirectory;
        $receivedDirectory = $this->receivedDirectory;

        if(count(glob($catalogLogsDirectory . '*.lock')) > 0){
            echo "Log file exist. exit... There is no xml file to be executed \n";
            return true;
        }
        if(file_exists($catalogLogsDirectory . 'stage')){
            echo "Full update do not finish. exit... There is no xml file to be executed \n";
            return true;
        }
        if(count(glob($receivedDirectory . 'incremental_product_update*')) > 0){
            echo "Incremental update do not finish. exit... There is no xml file to be executed \n";
            return true;
        }
        if(count(glob($receivedDirectory . 'full_inventory_update*')) > 0){
            echo "Full update do not finish. exit... There is no xml file to be executed \n";
            return true;
        }
        if( count(glob($receivedDirectory . 'category_update*')) == 0 ){
            echo "category_update* file not find. exit... There is no xml file to be executed \n";
            return true;
        }
        $filename = rtrim(basename(shell_exec('ls -t -r ' . $receivedDirectory . 'category_update* | head --lines 1')));
        $this->filename = $filename;

        return false;
    }
    public static function getXmlElementFromString($contents){
        //Create new XML object
        $rootXmlElement = new SimpleXMLElement($contents);

        foreach ($rootXmlElement->children() as $child) {
            $entityTypes[$child->getName()] = $child->getName();
        }
        return array($rootXmlElement,$entityTypes);
    }
}