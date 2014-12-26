<?php
class Task_Tools_Block_List extends Mage_Core_Block_Template
{
  public function getAllStore(){
      $resource = Mage::getSingleton('core/resource');
      $arr = array();
      $readConnection = $resource->getConnection('core_read');
      $pname = "上海";
      $query = "SELECT 	address,city  FROM `store` where province='".$pname."'";
      $res = $readConnection->query($query);
      foreach($res as $each){
          $tmp = array();
          $tmp['city'] = $each['city'];
          $tmp['address'] = $each['address'];
          $arr[] = $tmp;
      }
      return $arr;
  }

    public function getAllProvince(){
        $return = array();
        $resource = Mage::getSingleton('core/resource');
        $writeConnection = $resource->getConnection('core_write');
        $readConnection = $resource->getConnection('core_read');
        $query = "SELECT distinct province FROM `store` ";
        $res = $readConnection->query($query);
        foreach($res as $each){
            $return[] = $each['province'];
        }
        return  $return;
    }
}