<?php
class Shoe_Maker_Test_Model_CategoryXmlGenerator extends EcomDev_PHPUnit_Test_Case{
    public $model = null;

    public function __construct(){
        $this->model = new Shoe_Maker_Model_CategoryXmlGenerator();
    }

    public function testGeneratorProductArray(){
        $this->model->generateProductArray();
        $arr = $this->model->productsArray;
        $this->assertNotNull($arr);
        return $this->model;
    }
    /**
     * @depends testGeneratorProductArray
     */
    public function testGenerateXmlFile($model){
        $this->model =$model;
        $this->model->generateXmlFile();

        $productsArray = $this->model->productsArray;
        foreach ($productsArray as $categoryId => $productSet) {

            //Creates XML string and XML document from the DOM representation
            $itemFilename = 'category-' . $categoryId . '.xml';
            $this->assertFileExists($this->model->newCategoryInventoryDirectory.$itemFilename);
        }
        return $this->model;
    }
    /**
     * @depends testGenerateXmlFile
     */
    public function testGenerateTestCategoryFile($model){
        $this->model =$model;
        $this->model->generateTestCategoryFile();
        $this->assertFileExists($this->model->newCategoryInventoryDirectory."category-test.xml");
    }

    public function testExecute(){
        $this->model->execute();
    }


}
