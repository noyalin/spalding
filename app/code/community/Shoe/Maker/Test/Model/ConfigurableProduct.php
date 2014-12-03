<?php
/**
 * Created by PhpStorm.
 * User: fang
 * Date: 5/1/14
 * Time: 1:59 PM
 */
class Shoe_Maker_Test_Model_ConfigurableProduct extends EcomDev_PHPUnit_Test_Case{
    public $model = null;

    public function __construct(){
        $this->model = new Shoe_Maker_Model_ConfigurableProduct();
    }
    public function testGetStoreId(){
        $store = 'admin';
        $storeId = $this->model->getStoreId($store);
        $this->assertNotNull($storeId);
        $this->assertEquals($storeId,0);
    }

    public function testGetWebsiteIds(){
        $websites = "sneakerhead,sneakerrx,military";
        $ids =  $this->model->getWebsiteIds($websites);
        $this->assertNotNull($ids);
        $this->assertEquals($ids,array(21,1,20));
    }
    public function testGetXmlElementFromString(){
        $content = <<<HTML
<?xml version="1.0" encoding="UTF-8"?>
<root updateType="1">
    <Configurable>
        <Product>
            <Id>29132</Id>
            <Store>admin</Store>
            <Websites>sneakerhead</Websites>
            <CategoryIds>6,28,534</CategoryIds>
            <AttributeSetName>mensize</AttributeSetName>
            <Name>Adidas Adicourt Stripes</Name>
            <Description>Kick back in the casual Adidas Adicourt Stripes. This lifestyle sneaker has a comfortable and breathable canvas upper, signature 3-Stripes, metal eyelets, and a vulcanized rubber outsole.</Description>
            <ShortDescription>Adidas Adicourt Stripes Skateboard Shoes</ShortDescription>
            <Sku>001001b10-choco</Sku>
            <Weight>2.50</Weight>
            <Status>1</Status>
            <Abstract>Adidas Adicourt Stripes&lt;br&gt;Skateboard Shoes</Abstract>
            <Accessory />
            <Msrp>60.00</Msrp>
            <ColorSn>Black / Run White</ColorSn>
            <UrlKey>adidas-court-stripes-skateboard-shoes-d73945</UrlKey>
            <Price>54.990</Price>
            <CreatedAt>04/19/2014</CreatedAt>
            <Model>adicourtstripes</Model>
            <ImageCount>5</ImageCount>
            <AssociatedBlog />
            <Brand>Nike</Brand>
            <Taxable>2</Taxable>
            <IsNewArrival>1</IsNewArrival>
            <IsOnSale>0</IsOnSale>
            <PromotionTag>Shoes</PromotionTag>
            <PrimaryProductId>29132</PrimaryProductId>
            <Canonical>adidas-court-stripes-skateboard-shoes-d73945</Canonical>
            <FreeShippingTypeId>4001</FreeShippingTypeId>
            <IsRewardEligible>1</IsRewardEligible>
            <IsDiscountEligible>1</IsDiscountEligible>
            <SalePrice>54.99</SalePrice>
            <OrderLimitCount>0</OrderLimitCount>
            <IsPhoneOrderOnly>0</IsPhoneOrderOnly>
            <PhoneOrderOnlyMessage />
            <PrimaryCategoryId>6,28</PrimaryCategoryId>
            <SEO_Title />
            <SEO_Description />
            <SEO_Keywords />
            <Testimonial>they really came through when i couldnt find the shoes i wanted</Testimonial>
            <LifeFrom />
            <LifeTo />
            <Shipped>0</Shipped>
            <Returned>0</Returned>
            <ReturnRatePercentage>0.00</ReturnRatePercentage>
            <Popularity>0</Popularity>
            <PopularityPercentage>0</PopularityPercentage>
            <PriceHistory />
            <ColorMap>Black</ColorMap>
            <MaterialFabric_01>Canvas</MaterialFabric_01>
            <MensSizeCode />
            <WomensSizeCode />
            <BigKidsSizeCode />
            <PreSchoolSizeCode />
            <ToddlerSizeCode />
        </Product>
    </Configurable>
</root>
HTML;
        list($rootXmlElement,$products) = Shoe_Maker_Model_ConfigurableProduct :: getXmlElementFromString($content);
        $this->assertNotNull($rootXmlElement);
        return $rootXmlElement;
    }

    /**
     * @depends testGetXmlElementFromString
     */
    public function testProductEntity($rootXmlElement){
        foreach ($rootXmlElement->Configurable as $configurableProducts) {
            foreach ($configurableProducts->Product as $entity) {
                $this->assertNotNull($entity);
                return $entity;
            }
        }
    }

    /**
     *  @depends testProductEntity
     */
    public function testGetValueAttr($entity){
        $valueArr = $this->model->getValueAttr($entity);
        $this->assertNotNull($valueArr);
        $this->assertArrayHasKey('name', $valueArr);
        $this->assertArrayHasKey('sku', $valueArr);
        return $valueArr;
    }

    /**
     *  @depends testGetValueAttr
     * In order to test this function, need undelete any data before run it
     */
    public function testSaveNewConfigurableProduct($valueArr){
        //delete all product, if test create new product

        //But in order to test function testUpdateSimpleProductIfUpdateConfigurable, Need do two things
        // 1: comment this line
        // 2: run simple product to insert simple product
        $this->model->deleteAllProduct();

        $sku = $valueArr['sku'];
        //Check if configurable product already exists
        $product = $this->model->checkProductExist($sku);
        if(!$product){
            $product = $this->model->saveNewConfigurableProduct($valueArr);
            $productId = $product->entity_id;
            $this->assertGreaterThan(0,$productId);
        }
        return array($product,$valueArr);
    }
    /**
     *  @depends testSaveNewConfigurableProduct
     */
    public function testSaveOtherStore($arr){
        list($product,$valueArr) = $arr;
        $this->assertNotNull($product);
        $this->assertNotNull($valueArr);
        $return = $this->model->saveOtherStore($product,$valueArr);
        if($return){
            //check table enterprise_url_rewrite
            $urlKey = $valueArr['urlKey'];
            $urlKeyM = $urlKey."-m";
            $query = "SELECT url_rewrite_id FROM enterprise_url_rewrite where request_path='$urlKeyM' ";
            $resource = Mage::getSingleton('core/resource');
            $writeConnection = $resource->getConnection('core_read');
            $results = $writeConnection->fetchOne($query);
            $this->assertGreaterThan(0,$results);
        }

    }

    /**
     * @depends testGetValueAttr
     */
    public function testUpdateConfigurableProduct($valueArr){
        $sku = $valueArr['sku'];
        //Check if configurable product already exists
        $product = $this->model->checkProductExist($sku);
        if($product){
            $testName = "Hello world";//function testUpdateSimpleProductIfUpdateConfigurable also need
            $valueArr['name'] = $testName;
            $this->model->updateConfigurableProduct($product,$valueArr);
            $this->assertEquals($testName,$product->getName());
        }

        return array($this->model,$valueArr);
    }
    /**
     * @depends testUpdateConfigurableProduct
     * Need run simple product test first to tes this function.
     * Add simple to configurable first.
     */
    public function  testUpdateSimpleProductIfUpdateConfigurable($arr){
        list($model,$valueArr)  = $arr;
        $this->model = $model;
        $configurableProductsGroupsToUpdate = $this->model->configurableProductsGroupsToUpdate;

        $this->assertNotNull($configurableProductsGroupsToUpdate);
        $sku = $valueArr['sku'];
        $sku = (string)$sku;
        $this->assertArrayHasKey($sku,$configurableProductsGroupsToUpdate);
        if(count($configurableProductsGroupsToUpdate["$sku"][0])){
            $this->model->updateSimpleProductIfUpdateConfigurable();

            $configProduct = Mage::getModel('catalog/product')->loadByAttribute('sku', $sku);
            $this->assertNotNull($configProduct);
            //Load all simple product belong to this configurable product
            $configProduct = Mage::getModel('catalog/product')->loadByAttribute('sku', $sku);
            $this->assertEquals($configProduct->getTypeId(),'configurable');
            $conf = Mage :: getModel('catalog/product_type_configurable')->setProduct($configProduct);
            $simpleCollection = $conf->getUsedProductCollection()
                ->addAttributeToSelect('*')
                ->addFilterByRequiredOptions();
            $arrSku = array();
            $childSku = null;
            foreach($simpleCollection as $simpleProduct){
                if(!$childSku){
                    $childSku = $simpleProduct->getSku();
                }
                $this->assertEquals($simpleProduct->getName(),$valueArr['name']);
            }

            //check table devicom_inventory
            $query = "SELECT old_price FROM devicom_inventory where sku='$childSku' ";
            $resource = Mage::getSingleton('core/resource');
            $writeConnection = $resource->getConnection('core_read');
            $results = $writeConnection->fetchOne($query);
            $this->assertEquals($results,$configProduct->getPrice());
        }


    }

    /**
     * @depends testGetProductListFromXml
     */
//    public function testIncrementProducts($rootXmlElement){
//        $this->model->incrementProducts();
//        $i = 0;
//        foreach ($rootXmlElement->Configurable as $configurableProducts) {
//            foreach ($configurableProducts->Product as $entity) {
//                $this->assertNotNull($entity);
//                $i++;
//            }
//        }
//
//        //check the table catalog_prouct_entity
//        $query = "SELECT count(entity_id) FROM catalog_product_entity where type_id='configurable' ";
//        $resource = Mage::getSingleton('core/resource');
//        $writeConnection = $resource->getConnection('core_read');
//        $results = $writeConnection->fetchOne($query);
//        $this->assertEquals($results,$i);
//    }





//    public function xtestGetMaxEntityIdFromTableCatalogProductEntity(){
//        $result = $this->model->getMaxEntityIdFromTableCatalogProductEntity();
//        $this->assertGreaterThanOrEqual(0,$result);
//    }


}