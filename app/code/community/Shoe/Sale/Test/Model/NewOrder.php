<?php
class Shoe_Sale_Test_Model_NewOrder extends EcomDev_PHPUnit_Test_Case{
    public $model = null;

    public function __construct(){
        $this->model = new Shoe_Sale_Model_PostOrder_NewOrder();
    }

    public function testReadAllOrderInFolder(){
        $this->model->readAllNewOrderFiles();
        //$this->assertNotNull($this->model->newOrders);
        return $this->model;
    }

    /**
     * @depends testReadAllOrderInFolder
     */
    public function xtestSendXml($model){
        $newOrder = $this->model->readFile();
        $count = count($newOrder);
        $this->model = $model;
        $this->model->sendXml();
        $newCount = count($this->model->readFile());
        if($count <= Shoe_Sale_Model_Base::CONF_NEW_ORDER_POST_LIMIT ){
            $this->assertEquals(0,$newCount);
        }else{
            $this->assertEquals($newCount, $count - Shoe_Sale_Model_Base::CONF_NEW_ORDER_POST_LIMIT );
        }
    }

    public function testExecute(){
        $newOrder = $this->model->readFile();
        $count = count($newOrder);
       $this->model->execute();

        $newCount = count($this->model->readFile());
        if($count <= Shoe_Sale_Model_Base::CONF_NEW_ORDER_POST_LIMIT ){
            $this->assertEquals(0,$newCount);
        }else{
            $this->assertEquals($newCount, $count - Shoe_Sale_Model_Base::CONF_NEW_ORDER_POST_LIMIT );
        }
    }
}
