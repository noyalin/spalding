<?php
/**
 * Created by PhpStorm.
 * User: fang
 * Date: 5/1/14
 * Time: 6:13 PM
 */
require_once 'abstract.php';
$dir = dirname(__FILE__).DIRECTORY_SEPARATOR;
set_include_path($dir.'../app/code/community/Shoe/Maker/Model');
class Devicom_DB extends Mage_Shell_Abstract {
    public function run(){
         //Post New Order:  php shell/devicom.php  --type Sale:PostOrder_NewOrder
         //Failed Order tracker:  php shell/devicom.php  --type Sale:PostOrder_FailedTracker
         //Reward Validator:  php shell/devicom.php  --type Sale:RewardValidator
         //Order status update:  php shell/devicom.php  --type Sale:OrderStatusUpdate

         //Flush Cache:  php shell/devicom.php  --type Tools:FlushCache
         //Reset taobao tag:  php shell/devicom.php  --type Tools:ResetImage
         //DownloadPicTask:  php shell/devicom.php  --type Tools:DownloadPicTask
         //Add image gallert  php shell/devicom.php  --type Tools:AddGallery
        //Stock Reindexer:  php shell/devicom.php  --type Tools:StockReindexer
        //Sitemap Generator:  php shell/devicom.php  --type Tools:SitemapGenerator
        //Catalog Process Tracker:  php shell/devicom.php  --type Tools:Tracker_CatalogProcessTracker
         //Error Tracker:  php shell/devicom.php  --type Tools:Tracker_ErrorTracker
         //Sale Log Tracker:  php shell/devicom.php  --type Tools:Tracker_SaleProcessTracker

        //php shell/devicom.php  --type InventoryUpdate
        //php shell/devicom.php  --type IncrementalUpdate
        //php shell/devicom.php  --type CategoryUpdate
        //php shell/devicom.php  --type FullInventoryUpdate
        //php shell/devicom.php  --type CategoryXmlGenerator
        //php shell/devicom.php  --type CategoryPositionUpdate
        $type = $this->getArg('type');
        if(strstr($type,":")){
            $arr = explode(":",$type);
            $type = $arr[0];
        }

        switch($type){
            case 'Tools':
                $className = "Task_Tools_Model_".$arr[1];
                $obj = new $className();
                $obj->execute();
                break;
            case 'Sale':
                $className = "Shoe_Sale_Model_".$arr[1];
                $obj = new $className();
                $obj->execute();
                break;
            default:
                $className = "Shoe_Maker_Model_$type";
                $obj = new $className();
                $obj->execute();
        }

    }
}
$db = new Devicom_DB;
$db->run();