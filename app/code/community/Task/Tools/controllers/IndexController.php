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
}