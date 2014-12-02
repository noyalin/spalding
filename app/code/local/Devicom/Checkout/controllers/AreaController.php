<?php
class Devicom_Checkout_AreaController extends Mage_Checkout_Controller_Action{
    public function testAction(){
        echo "here";
//        var_dump($this->getRequest()->getParam('foo'));
//        var_dump($this->getRequest()->getPost('foo'));
    }

    public function getCityListAction($id=null){
        $id = $this->getRequest()->getPost('id');
        if(!$id){
            $id = 485;
        }
        $resource = Mage::getSingleton('core/resource');
        $writeConnection = $resource->getConnection('core_read');
        // Select all simple products with a qty > 0
        $query = "SELECT city.* FROM `directory_country_city` as city
join directory_country_region as region on (region.code = city.provincecode)
WHERE region.region_id=$id";
        $results = $writeConnection->fetchAll($query);
        $return = "<option value=''>请选择</option>";
        foreach($results as $each){
            $code = $each['code'];
            $name = $each['name'];
            $return .= "<option value='$code'>$name</option>";
        }
        echo $return;
    }

    public function getDistrictListAction($id=null){
        $id = $this->getRequest()->getPost('id');
        if(!$id){
            $id = 110100;
        }

        $resource = Mage::getSingleton('core/resource');
        $writeConnection = $resource->getConnection('core_read');

        // Select all simple products with a qty > 0
        $query = "SELECT district.* FROM `directory_country_district` as district
join directory_country_city as city on (city.code = district.citycode)
WHERE city.code=$id";
        $results = $writeConnection->fetchAll($query);
        $return = "<option value=''>请选择</option>";
        foreach($results as $each){
            $code = $each['code'];
            $name = $each['name'];
            $return .= "<option value='$code'>$name</option>";
        }
        echo $return;
    }

}