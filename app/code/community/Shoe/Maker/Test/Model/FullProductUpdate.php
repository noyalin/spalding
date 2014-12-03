<?php
class Shoe_Maker_Test_Model_FullProductUpdate extends EcomDev_PHPUnit_Test_Case{
    public function testModel(){
       // $this->assert
        $this->assertEquals(5,5);
    }

    public function testWeb(){
        Mage::app('admin');
        $store = "admin";
        $existingWebsites = Mage::app()->getWebsites(true);
        //Set store id
        $storeId = null;
        foreach ($existingWebsites as $existingWebsite) {
            foreach ($existingWebsite->getGroups() as $group) {
                $existingStores = $group->getStores();
                foreach ($existingStores as $existingStore) {
                    if ($store == $existingStore->getCode()) {
                        $storeId = $existingStore->getId();
                    }
                }
            }
        }

//        echo $storeId." here ";
    }

    public function testCreate(){
        $sku = "631722-101-8";
        $size = end(explode('-', $sku));
        echo $size;
//        echo substr($sku, 0, -(strlen($size) + 1));
//        $configurableProduct = Mage::getModel('catalog/product')->loadByAttribute('sku', substr($sku, 0, -(strlen($size) + 1)));
    }
}