<?php
class Task_Tools_Model_ResetImage extends Task_Tools_Model_Base{
   public function execute(){
       $collectionConfigurable = Mage::getResourceModel('catalog/product_collection')
           ->addAttributeToFilter('type_id', array('eq' => 'configurable'));
       $i = 0;
       foreach( $collectionConfigurable as $_product){
           $entityId = $_product->getEntityId();
           $configurableProduct = Mage::getModel('catalog/product')->load($entityId);
           $description = $configurableProduct->getDescription();
           if($description && stristr($description,"taobaocdn.com")){
               $description = str_replace("&lt;img","<img",$description);
               $description = str_replace("/&gt;","/>",$description);
               $configurableProduct->setDescription($description);
               $configurableProduct->save();
           }
       }

   }
    public function validate(){
        return false;
    }
}