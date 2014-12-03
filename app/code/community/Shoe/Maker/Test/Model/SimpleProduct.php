<?php
class Shoe_Maker_Test_Model_SimpleProduct extends EcomDev_PHPUnit_Test_Case{
    public $model = null;

    public function __construct(){
        $this->model = new Shoe_Maker_Model_SimpleProduct();
    }

    public function testDeleteAllSimpleProduct(){
        $this->model->deleteAllSimpleProduct();
    }
    public function testGetXmlElementFromString(){
        $content = <<<HTML
<?xml version="1.0" encoding="UTF-8"?>
<root updateType="1">
    <Simple>
        <Product>
            <Sku>001001b10-choco-7.5</Sku>
            <Quantity>1</Quantity>
        </Product>
        <Product>
            <Sku>001001b10-choco-8</Sku>
            <Quantity>1</Quantity>
        </Product>
        <Product>
            <Sku>001001b10-choco-8.5</Sku>
            <Quantity>3</Quantity>
        </Product>
        <Product>
            <Sku>001001b10-choco-9</Sku>
            <Quantity>4</Quantity>
        </Product>
        <Product>
            <Sku>001001b10-choco-9.5</Sku>
            <Quantity>8</Quantity>
        </Product>
    </Simple>
</root>
HTML;
        list($rootXmlElement,$products) = Shoe_Maker_Model_CategoryUpdate :: getXmlElementFromString($content);
        $this->assertNotNull($rootXmlElement);
        return $rootXmlElement;
    }

    /**
     * @depends testGetXmlElementFromString
     */
    public function testEntity($rootXmlElement){
        foreach ($rootXmlElement->Simple as $simpleProducts) {
            $itemCounter = 1;
            foreach ($simpleProducts->Product as $entity) {
                $this->assertNotNull($entity);
                $this->assertNotNull($entity->Quantity);
                $this->assertNotNull($entity->Sku);
                $this->assertNotNull($entity->Size);
                return $entity;
            }
        }
    }

    /**
     * @depends testEntity
     */
    public function testHandleEachSimpleEntity($entity){
        //delete all simple product by sql
        $resource = Mage::getSingleton('core/resource');
        $writeConnection = $resource->getConnection('core_write');
        $query = "delete from catalog_product_entity where type_id='simple'";
        $results = $writeConnection->query($query);

        $this->model->handleEachSimpleEntity($entity);

        //check whether this  simple exist in database
        $sku = $entity->Sku;
        $simpleProduct = Mage::getModel('catalog/product')->loadByAttribute('sku', $sku);
        $this->assertNotNull($simpleProduct);
        $this->assertEquals($simpleProduct->getTypeId(),'simple');

        $query = "SELECT label FROM devicom_inventory where sku='$sku' ";
        $writeConnection = $resource->getConnection('core_read');
        $results = $writeConnection->fetchOne($query);
//        $this->assertEquals($results,"男士 38");
//        var_dump($results);
        return $this->model;
    }

    /**
     * @depends testHandleEachSimpleEntity
     */
    public function testUpdateRelatedProductGroups($model){
        $this->model = $model;
        $newRelatedProductGroups = $this->model->newRelatedProductGroups;
        $this->assertNotNull($newRelatedProductGroups);

        $oldCount = null;
        foreach($newRelatedProductGroups as $existingConfigurableProduct =>$newRelatedProductGroup ){
            //check the configurable product
            $configProduct = Mage::getModel('catalog/product')->loadByAttribute('sku', $existingConfigurableProduct);
            $this->assertNotNull($configProduct);
            $this->assertEquals($configProduct->getTypeId(),'configurable');
            //get all the child by configurable product
            $relatedProducts = Mage::getModel('catalog/product_type_configurable')->getChildrenIds($configProduct->getId());
            $oldCount = count($relatedProducts[0]);
        }

        $this->assertNotNull($this->model->newRelatedProductGroups);
        $this->model->updateRelatedProductGroups();

        $newCount = null;
        foreach($newRelatedProductGroups as $existingConfigurableProduct =>$newRelatedProductGroup ){
            //check the configurable product
            $configProduct = Mage::getModel('catalog/product')->loadByAttribute('sku', $existingConfigurableProduct);
            $this->assertNotNull($configProduct);
            $this->assertEquals($configProduct->getTypeId(),'configurable');
            //get all the child by configurable product
            $relatedProducts = Mage::getModel('catalog/product_type_configurable')->getChildrenIds($configProduct->getId());
            $newCount = count($relatedProducts[0]);
        }
        $this->assertEquals($newCount,$oldCount*1+1);
        return $this->model;
    }

    /**
     * @depends testUpdateRelatedProductGroups
     * Test whether simple product set the relation to his parent configurable product
     */
    public function testRelatedSimpleProduct($model){
        $this->model = $model;
        $newRelatedProductGroups = $this->model->newRelatedProductGroups;
        $this->assertNotNull($newRelatedProductGroups);
        foreach($newRelatedProductGroups as $existingConfigurableProduct =>$newRelatedProductGroup ){
            //check the configurable product
            $configProduct = Mage::getModel('catalog/product')->loadByAttribute('sku', $existingConfigurableProduct);
            $this->assertNotNull($configProduct);
            $this->assertEquals($configProduct->getTypeId(),'configurable');
            $conf = Mage :: getModel('catalog/product_type_configurable')->setProduct($configProduct);
            $simpleCollection = $conf->getUsedProductCollection()
                ->addAttributeToSelect('*')
                ->addFilterByRequiredOptions();
            $arrSku = array();
            foreach($simpleCollection as $simpleProduct){
                $arrSku[] = $simpleProduct->getSku();
            }
        }
        return $this->model;
    }

    /**
     * @depends testEntity
     */
    public function testHandleUpdateQuantityForEachSimpleEntity($entity){
        //Set up prepared env
        $this->assertNotNull($entity);
        $this->assertNotNull($entity->Quantity);
        $this->assertNotNull($entity->Sku);
        $this->assertGreaterThan(0,$entity->Quantity);

        $this->model->handleUpdateQuantityForEachSimpleEntity($entity);

        //check table devicom_inventory
        $sku = $entity->Sku;
        $arr = explode('-', $sku);
        $size = end($arr);
        $qty = $entity->Quantity;

        $query = "SELECT qty FROM devicom_inventory where sku='$sku' ";
        $resource = Mage::getSingleton('core/resource');
        $writeConnection = $resource->getConnection('core_read');
        $results = $writeConnection->fetchOne($query);
        //var_dump($results);
        $this->assertEquals($qty,$results);

        //check whether the xml generated
//        $itemFilename =  $this->model->inventoryDirectory. '647542-010' . '.xml';
//        $this->assertFileExists($itemFilename);

    }



}