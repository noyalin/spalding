<?php
class Task_Tools_Model_AddGallery extends Task_Tools_Model_Base{
   public function execute(){
       $resource = Mage::getSingleton('core/resource');
       $writeConnection = $resource->getConnection('core_write');
       $readConnection = $resource->getConnection('core_read');


       $collectionConfigurable = Mage::getResourceModel('catalog/product_collection')
           ->addAttributeToFilter('type_id', array('eq' => 'configurable'));
       $i = 0;
       foreach( $collectionConfigurable as $_product){
           $entityId = $_product->getEntityId();
           $configurableProduct = Mage::getModel('catalog/product')->load($entityId);
            $imageCount = $configurableProduct->getImageCount();
            $urlKey = $configurableProduct->getUrlKey();
           echo $imageCount. "    ".$urlKey."\n";
           $skuImage = $configurableProduct->getSku();
           for($i=1;$i<=$imageCount;$i++){
               $urlKeyImage = $urlKey."-$i.jpg";
               $file  = "/$skuImage/$urlKeyImage";
               $query = "insert into `catalog_product_entity_media_gallery` ( `attribute_id`, `entity_id`, `value`) values (88,$entityId,'$file')";

               echo $query."\n";
               $res = $writeConnection->query($query);
           }

       }

   }
    public function validate(){
        return false;
    }
}