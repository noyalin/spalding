<?php
class Task_Tools_Test_Model_SitemapGenerator extends EcomDev_PHPUnit_Test_Case{
    public $model = null;

    public function __construct(){
        $this->model = new Task_Tools_Model_SitemapGenerator();
    }

    public function tesRun(){
        $id = 1;
        $sitemap = Mage::getModel('sitemap/sitemap');
        /* @var $sitemap Mage_Sitemap_Model_Sitemap */
        $sitemap->load($id);
        $this->assertNotNull($sitemap->getId());
    }
    public function testExecute(){
        $this->model->execute();
    }


}
