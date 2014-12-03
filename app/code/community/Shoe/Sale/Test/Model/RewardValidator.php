<?php
class Shoe_Sale_Test_Model_RewardValidator extends EcomDev_PHPUnit_Test_Case{
    public $model = null;

    public function __construct(){
        $this->model = new Shoe_Sale_Model_RewardValidator();
    }

    public function testModuleExist(){
        //Check if reward system enabled
        $modules = Mage::getConfig()->getNode('modules')->children();
        $modulesArray = (array)$modules;
        $this->assertArrayHasKey('J2t_Rewardproductvalue',$modulesArray);
    }

    public function testCheck(){
        // Get resource
        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');
        $writeConnection = $resource->getConnection('core_write');

        // Run query to find all pending rewards and see if 30 days has passed since order shipped date -- if so, validate points
        $query = "SELECT `drv`.`increment_id` FROM `rewardpoints_account` AS `ra`
        INNER JOIN `devicom_reward_validation` AS `drv` ON `ra`.`order_id` = `drv`.`increment_id`
        WHERE `drv`.`created_at` < DATE_SUB(CURDATE(), INTERVAL 30 DAY)
        AND `ra`.`order_id` > '100000000'
        AND `ra`.`rewardpoints_referral_id` IS NULL
        AND (`drv`.`status` != 'complete')";
        $results = $readConnection->query($query);
        $this->assertNotNull($results);
        foreach ($results as $result) {
         //   echo " HERE ";var_dump($result);
        }
    }

    public function testexecute(){
        $this->model->execute();
    }



}
