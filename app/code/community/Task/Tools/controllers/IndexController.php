<?php
class Task_Tools_IndexController extends Mage_Core_Controller_Front_Action{
    public function testAction(){
//        header("Location: http://www.baidu.com");
//        $url = mage :: getUrl('alipay/payment/wapredirect', array('_secure'=>true));
        $url = 'http://www.baidu.com';
//        $this->_redirect($url);

        Mage::app()->getFrontController()->getResponse()->setRedirect($url);
    }

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

    public function noticeAction(){
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
    function imageAction(){
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
            $sku = $configurableProduct->getSku();
//            if($sku == '73-901y'){
                $this->getAllImagesByUrlkey($sku,$urlKey,$imageCount);
                echo $sku."    ".$i."<br/>";
//            }

            $i++;

//            $skuImage = $configurableProduct->getSku();
//            for($i=1;$i<=$imageCount;$i++){
//                $urlKeyImage = $urlKey."-$i.jpg";
//                $file  = "/$skuImage/$urlKeyImage";
//                $query = "insert into `catalog_product_entity_media_gallery` ( `attribute_id`, `entity_id`, `value`) values (88,$entityId,'$file')";
//
//                echo $query."\n";
//                $res = $writeConnection->query($query);
//            }

        }
        //echo $i;
    }

    public function getAllImagesByUrlkey($sku,$urlKey,$count){
        $dir = Mage::getBaseDir()."/media/catalog/product/";

        //取得详情页三个大图
        for($i=1;$i<=$count;$i++){
//            $url = "http://image.sneakerhead.com/is/image/sneakerhead/$urlKey-$i?$270$";
            $url = 'http://s7d5.scene7.com/is/image/sneakerhead/spalding1200?$1200x1200$&$image='."$urlKey-$i";
            $url = 'http://s7d5.scene7.com/is/image/sneakerhead/bigball1200?$1200x1200$&$imagemoban='."$urlKey-$i";
            $needDir = $dir.$sku."/";
            if(!file_exists($needDir)){
                mkdir($needDir);
            }
            $filename = $needDir."$urlKey-$i.jpg";
            if(file_exists($filename)){
                continue;
            }
            $return = $this->grabImage($url,$filename);
            if($return){
                // means save succcess, save database
            }else{
                //重新获取
            }
        }

        //取得产品列表小图
        $configurableProduct = Mage::getModel('catalog/product')->loadByAttribute('sku', $sku);
        $productNorm = $configurableProduct->getProductNorm();
        if($productNorm == 6){
            $urlProductList = 'http://s7d5.scene7.com/is/image/sneakerhead/spalding220pxsmall?$220x220$&$image='.$urlKey.'-1';
        }else{
            $urlProductList = 'http://s7d5.scene7.com/is/image/sneakerhead/spalding220px?$220x220$&$image='.$urlKey.'-1';
        }
        $this->getImageVByUrl($urlProductList,$sku,$urlKey,4);

        //detail page left image
        for($m=1;$m<=$count;$m++){
            $urlProductList = 'http://s7d5.scene7.com/is/image/sneakerhead/spalding330px?$330x330$&$image330='.$urlKey.'-'.$m;
            $this->getImageVByUrl($urlProductList,$sku,$urlKey,4+$m);
        }



        //detail small image under left image
        for($m=1;$m<=$count;$m++){
            $smallImageUnderLeftImage = 'http://s7d5.scene7.com/is/image/sneakerhead/spalding50px?$50x50$&$image50='.$urlKey.'-'.$m;
            $this->getImageVByUrl($smallImageUnderLeftImage,$sku,$urlKey,7+$m);
        }

        //Front image
        for($m=1;$m<=$count;$m++){
            $smallImageUnderLeftImage = 'http://s7d5.scene7.com/is/image/sneakerhead/ballTemplates?$960x598$&$image488px='.$urlKey.'-'.$m;
            $this->getImageVByUrl($smallImageUnderLeftImage,$sku,$urlKey,10+$m);
        }

        //购物车 small image
        $urlProductList = 'http://s7d5.scene7.com/is/image/sneakerhead/spalding102px?$102x102$&$image='.$urlKey.'-1';
        $this->getImageVByUrl($urlProductList,$sku,$urlKey,14);

        //my order image 66-996-66996y-1
        $urlProductList = 'http://s7d5.scene7.com/is/image/sneakerhead/spalding102px?$102x102$&$image='.$urlKey.'-1';
        $this->getImageVByUrl($urlProductList,$sku,$urlKey,15);

        //detail page - view more other product
        $urlProductList = 'http://s7d5.scene7.com/is/image/sneakerhead/sku220px%2D1?$220x220$&$image220px='.$urlKey.'-1';
        $this->getImageVByUrl($urlProductList,$sku,$urlKey,16);
    }

    function getImageVByUrl($urlProductList,$sku,$urlKey,$i){
        $dir = Mage::getBaseDir()."/media/catalog/product/";
        $needDir = $dir.$sku."/";
        $filename = $needDir."$urlKey-$i.jpg";
        $return = null;
        if(file_exists($filename)){
            //do nothing
        }else{
            if($i == 4){
                //删除原图
//                unlink($filename);
//                echo $filename."     删除成功!<br/>";
            }
            $return = $this->grabImage($urlProductList,$filename);
            if($return){
                echo $urlProductList."     产品列表页面小图保存成功!<br/>";
            }
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
            $fp2=@fopen($filename, "a");
            fwrite($fp2,$img);
            fclose($fp2);
            return $filename;
        }else{
            return null;
        }



    }

    public function authWeixinAction(){
        $appid = 'wx36026301d4b1cb01';
        $appsecret = '79311ea02ea318af5f228492bf119104';
        $currentUrl = Mage::helper('core/url')->getCurrentUrl();
        $redirectUrl = urlencode ($currentUrl);
        $url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=$appid&redirect_uri=$redirectUrl&response_type=code&scope=snsapi_userinfo&state=sneakerhead#wechat_redirect";
        $code = null;
        if(isset($_GET["code"])){
            $code = trim($_GET["code"]);
        }
        $state = null;
        if(isset($_GET['state'])){
            $state = trim($_GET['state']);
        }

        if ($code && $state == 'sneakerhead') {
            $token_url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=$appid&secret=$appsecret&code=$code&grant_type=authorization_code";
            $token_data = $this->httpdata($token_url);
            $obj = json_decode($token_data);
            $openId = $obj->openid;
            $accessToken = $obj->access_token;
            $userUrl = "https://api.weixin.qq.com/sns/userinfo?access_token=$accessToken&openid=$openId&lang=zh_CN";
            $useinfo =  $this->httpdata($userUrl);
            mage :: log($useinfo);
        }else{
            Mage::app()->getFrontController()->getResponse()->setRedirect($url);
        }
    }

    function httpdata($url, $method="get", $postfields = null, $headers = array(), $debug = false)
    {
        $ci = curl_init();
        /* Curl settings */
        curl_setopt($ci, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ci, CURLOPT_TIMEOUT, 30);
        curl_setopt($ci, CURLOPT_RETURNTRANSFER, true);

        switch ($method) {
            case 'POST':
                curl_setopt($ci, CURLOPT_POST, true);
                if (!empty($postfields)) {
                    curl_setopt($ci, CURLOPT_POSTFIELDS, $postfields);
                    $this->postdata = $postfields;
                }
                break;
        }
        curl_setopt($ci, CURLOPT_URL, $url);
        curl_setopt($ci, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ci, CURLINFO_HEADER_OUT, true);

        $response = curl_exec($ci);
        $http_code = curl_getinfo($ci, CURLINFO_HTTP_CODE);

        if ($debug) {
            echo "=====post data======\r\n";
            var_dump($postfields);

            echo '=====info=====' . "\r\n";
            print_r(curl_getinfo($ci));

            echo '=====$response=====' . "\r\n";
            print_r($response);
        }
        curl_close($ci);
        return $response;
    }

}