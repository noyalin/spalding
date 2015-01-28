<?php
class Task_Tools_IndexController extends Mage_Core_Controller_Front_Action{
    public function indexAction(){
//        $this->loadLayout();
//        $this->renderLayout();
        mage :: log(file_get_contents("php://input"));
        $str = file_get_contents("php://input");
        if(!$str){
            $str = '{"tid":"580415280761034","name":"东北人19680624","price":"2349.0"}';
        }

        $arr = json_decode($str,true);
        $price = $arr['price'];


        $content = '<img width=800 src="'.'http://s7d5.scene7.com/is/image/sneakerhead/800x318?$800x318$&$amount='.$price.'" alt="" border="0" />';
        $content .= '<div id="homeNewarrivalTitle">
                    <h2>NEW ARRIVALS</h2>
                    <div class="clearfloats">&nbsp;</div>
                    </div>';
        Mage::getModel('cms/block')->load('tmall_1111_high')->setData('content',$content)->save();
        echo "success";
//        $layout = Mage :: getSingleton('core/layout');
//        $block = $layout->createBlock("cms/block");
//        $block->setBlockId('tmall_1111_high');
//        echo $block->toHtml();
    }
    public function authAction(){
        header("Content-type: image/PNG");
        $m = Mage::getModel('tools/imagecode');
        $m->show();
    }

    public function showAction(){
        $this->loadLayout();
        $this->renderLayout();
    }

    public function confirmAction(){
        $this->loadLayout();
        $this->renderLayout();
    }

    public function getDataAction(){
        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');
        $arr = array();

        $pname = $this->getRequest()->getParam('pname', false);
        $query = "SELECT 	address,city  FROM `store` where province='".$pname."'";
        $res = $readConnection->query($query);
        $str = '';
        foreach($res as  $key => $each){
            $tmp = array();
            $tmp['city'] = $each['city'];
            $tmp['address'] = $each['address'];
            $arr[] = $tmp;
            $background = '';
            if($key%2 == 0){
                $background = 'style="background: none repeat scroll 0% 0% rgb(40, 40, 40);"';
            }
            $str .= '
            <li class="clearfix" '.$background.'>
                <span class="shopsList_city" style="width:250px;">'.$each['city'].'</span>
                <span class="shopsList_adrs">'.$each['address'].'</span>
            </li>';
        }
//        echo json_encode($arr);
        echo $str;
    }

    public function saveContactUsAction(){
        $params = $this->getRequest()->getParams();
        $resource = Mage::getSingleton('core/resource');
        $writeConnection = $resource->getConnection('core_write');
        $readConnection = $resource->getConnection('core_read');
        $uid = 1;
        $name = $params['name'];
        $telephone = $params['telephone'];
        $content = $params['content'];
        $region_id = $params['region_id'];
        $city_id = $params['city_id'];
        $query = "insert into `contactus` ( `name`, `phone`, `province`,city,content,uid) values ('$name','$telephone','$region_id','$city_id','$content',$uid)";
        $writeConnection->query($query);
        echo "success";
    }
}