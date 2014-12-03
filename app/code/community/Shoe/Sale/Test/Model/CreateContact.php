<?php
class Shoe_Sale_Test_Model_CreateContact extends EcomDev_PHPUnit_Test_Case{
    public $model = null;

    public function __construct(){
        $this->model = new Shoe_Sale_Model_CreateContact();
    }

    public function testXmlFile(){
//        $contents = file_get_contents($this->model->receivedDirectory . $this->model->filename);
        $contents = <<<HTML
<?xml version="1.0"?>
<Root>
  <StoreId>22</StoreId>
  <CustomerId></CustomerId>
  <Email>toby.crain@devicominc.com</Email>
  <FirstName>Joe</FirstName>
  <LastName>Black</LastName>
  <NewsList>1</NewsList>
  <ReviewsList>1</ReviewsList>
</Root>
HTML;
        return $contents;
    }



}
