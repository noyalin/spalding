<?php
class Task_Tools_Test_Model_ErrorTracker extends EcomDev_PHPUnit_Test_Case{
    public $model = null;

    public function __construct(){
        $this->model = new Task_Tools_Model_Tracker_ErrorTracker();
    }

    public function testGetSystemLogFile(){
        $exceptionLogFilename =  $this->model->getSystemLogFile();
        $this->assertNotNull($exceptionLogFilename);
        return  $this->model;
    }

    /**
     * @depends testGetSystemLogFile
     */
    public function testCheckSystemLog($model){
        $this->model = $model;
        $this->model->checkSystemLog();
    }

    public function testRun(){
        $this->model->run();
    }



}
