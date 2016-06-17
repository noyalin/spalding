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
            $this->pushImageToOss($sku,$urlKey);
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

    public function pushImageToOss($sku,$urlKey){
        //首先判断此SKU是否存在
        $oss_sdk_service = new OSS_ALIOSS();

        //设置是否打开curl调试模式
        $oss_sdk_service->set_debug_mode(FALSE);
        $bucket = 'spalding-products';
        $object = "media/catalog/product/$sku/$urlKey-1.jpg";

        $response = $oss_sdk_service->is_object_exist($bucket,$object);
        if(true){//$response->status != 200
            $dir = Mage::getBaseDir()."/media/catalog/product/";
            //上传这个文件夹到OSS
            $options = array(
                'bucket' 	=> 'spalding-products',
                'object'	=> "media/catalog/product/$sku",
                'directory' => $dir.$sku,
            );
            try{
                $response = $oss_sdk_service->batch_upload_file($options,$urlKey,$sku);
            }catch (Exception $e){
                $exceptionLogHandle = fopen($this->catalogLogsDirectory . 'exception_log', 'a');
                fwrite($exceptionLogHandle, '->' . $this->filename . " - " . $e->getMessage() . "\n");
                fclose($exceptionLogHandle);
            }
            Mage :: log($response);
        }else{
            $this->transactionLogHandle("    ->UPDATING    :".$sku." has been pushed to OSS, do nothing \n");
        }

    }

    public function validate(){
        return false;
    }
}