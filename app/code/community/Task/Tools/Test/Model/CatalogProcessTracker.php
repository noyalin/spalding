<?php
class Task_Tools_Test_Model_CatalogProcessTracker extends EcomDev_PHPUnit_Test_Case{
    public $model = null;

    public function __construct(){
        $this->model = new Task_Tools_Model_Tracker_CatalogProcessTracker();
    }

    public function testExecute(){
        $this->model->execute();
    }



}
