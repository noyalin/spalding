<?php
class Shoe_Maker_Test_Model_FullInventoryUpdate extends EcomDev_PHPUnit_Test_Case{
    public $model = null;

    public function __construct(){
        $this->model = new Shoe_Maker_Model_FullInventoryUpdate();
    }
    public function testDB(){
        $config  = Mage::getConfig()->getResourceConnectionConfig("default_setup");

        $dbinfo = array(
            "host" => $config->host,
            "user" => $config->username,
            "pass" => $config->password,
            "dbname" => $config->dbname
        );
        $this->assertEquals($dbinfo["host"],'localhost');
        $this->assertEquals($dbinfo["dbname"],'magento_unit_tests');
    }
    public function testValidate(){
        $res = $this->model->validate();
        $this->assertEquals($res,false);
        $fileName = $this->model->filename;
        $this->assertNotNull($fileName);
        $this->assertStringStartsWith('full_inventory_update', $fileName);
        return $this->model;
    }
    /**
     * @depends testValidate
     */
    public function testGetXmlElementFromString($model){
        $this->model = $model;
        $contents = file_get_contents($this->model->receivedDirectory . $this->model->filename);
        list($rootXmlElement,$entityTypes) = Shoe_Maker_Model_FullInventoryUpdate :: getXmlElementFromString($contents);
        $this->assertNotNull($rootXmlElement);
        $this->assertNotNull($entityTypes);
        return $contents;
    }

    /**
     * @depends testGetXmlElementFromString
     * @depends testValidate
     */
    public function testConvertFile($content,$model){
        $this->model = $model;
        $this->model->generatedFileWrap();
        $csvFile = $this->model->temporaryDirectory . 'devicom_inventory_temporary.csv';
        $this->assertFileExists($csvFile);
    }
    /**
     * @depends testValidate
     */
    public function testUpdateDevicomStock($model){
        $this->model = $model;
        $this->model->updateDevicomStock();
        $sku = "647542-010-Onesize";
        $query = "SELECT * FROM devicom_inventory_temporary where sku='$sku' ";
        $resource = Mage::getSingleton('core/resource');
        $writeConnection = $resource->getConnection('core_read');
        $results = $writeConnection->fetchAll($query);
        $results = $results[0];
        $this->assertNotNull($results);
        $this->assertEquals(0,$results['qty']);
        $this->assertEquals("75,2691,2692",$results['category_ids']);
        return $this->model;
    }
    /**
     * @depends testValidate
     */
    public function testSyncDevicomInventory($model){
        $this->model = $model;
        $this->model->syncDevicomInventory();

        $sku = "647542-010-Onesize";
        $query = "SELECT * FROM devicom_inventory where sku='$sku' ";
        $resource = Mage::getSingleton('core/resource');
        $writeConnection = $resource->getConnection('core_read');
        $results = $writeConnection->fetchAll($query);
        $results = $results[0];
        $this->assertNotNull($results);
        $this->assertEquals(0,$results['qty']);
        $this->assertEquals("75,2691,2692",$results['category_ids']);
    }
    /**
     * @depends testValidate
     */
    public function testUpdateMagentoStock($model){
        $this->model = $model;
        $this->model->updateMagentoStock();

        $sku = "647542-010-Onesize";
        $query = "SELECT * FROM devicom_inventory where sku='$sku' ";
        $resource = Mage::getSingleton('core/resource');
        $writeConnection = $resource->getConnection('core_read');
        $results = $writeConnection->fetchAll($query);
        $results = $results[0];
        $this->assertNotNull($results);
        $this->assertEquals(0,$results['qty']);
        $this->assertEquals("75,2691,2692",$results['category_ids']);
        $productId = $results['product_id'];
        //check status from table cataloginventory_stock_status
        $query = "SELECT stock_status FROM cataloginventory_stock_status where product_id='$productId' ";
        $resource = Mage::getSingleton('core/resource');
        $writeConnection = $resource->getConnection('core_read');
        $results = $writeConnection->fetchOne($query);
        $this->assertEquals($results,0);

        //check status from table cataloginventory_stock_status
        $query = "SELECT qty FROM cataloginventory_stock_item where product_id='$productId' ";
        $resource = Mage::getSingleton('core/resource');
        $writeConnection = $resource->getConnection('core_read');
        $results = $writeConnection->fetchOne($query);
        $this->assertEquals($results,0);

        return $this->model = $model;
    }
    /**
     * @depends testUpdateMagentoStock
     */
    public function testRebuildXmlFiles($model){
        $this->model = $model;
        $this->model->rebuildXmlFiles();

    }


}
