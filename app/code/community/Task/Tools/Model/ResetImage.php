<?php
class Task_Tools_Model_ResetImage extends Task_Tools_Model_Base{
    public function execute(){
        $collectionConfigurable = Mage::getResourceModel('catalog/product_collection')
            ->addAttributeToFilter('type_id', array('eq' => 'configurable'));
        foreach( $collectionConfigurable as $_product){
            $entityId = $_product->getEntityId();
            $configurableProduct = Mage::getModel('catalog/product')->load($entityId);
            $count = $configurableProduct->getImageCount();
            $urlKey =  $configurableProduct->getUrlKey();
            $sku = $configurableProduct->getSku();
            for($m=1;$m<=$count;$m++){
                $smallImageUnderLeftImage = 'http://s7d5.scene7.com/is/image/sneakerhead/xiangqingye_first?$spalding_1242$&$images=sneakerhead/'.$urlKey.'-'.$m;
                $this->getImageVByUrl($smallImageUnderLeftImage,$sku,$urlKey,60+$m);
            }
        }

    }

    function getImageVByUrl($urlProductList,$sku,$urlKey,$i){
        $dir = Mage::getBaseDir()."/media/catalog/product/";
        $needDir = $dir.$sku."/";
        if(!file_exists($needDir)){
            mkdir($needDir);
        }
        $filename = $needDir."$urlKey-$i.jpg";
        $return = null;

        $return = $this->grabImage($urlProductList,$filename);
        if($return){
//            echo $urlProductList."     产品列表页面小图保存成功!<br/>";
        }

    }

    function grabImage($url,$filename="") {
        if($url==""):return false;endif;

        if($filename=="") {
            $filename=date("dMYHis").'jpg';
        }

        ob_start();
        readfile($url);
        $img = ob_get_contents();
        ob_end_clean();
        $size = strlen($img);
        if($img && $size){
            $fp2=@fopen($filename, "w");
            fwrite($fp2,$img);
            fclose($fp2);
            return $filename;
        }else{
            return null;
        }
    }

    public function validate(){
        return false;
    }
}