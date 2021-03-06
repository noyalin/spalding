<?php
class Task_Tools_IndexController extends Mage_Core_Controller_Front_Action{


    public function skinAction(){

        $oss_sdk_service = new OSS_ALIOSS();
        $oss_sdk_service->set_debug_mode(FALSE);
//设置是否打开curl调试模式
        //取得文件下所有的
        $dir = "/home/davis/Documents/spaldingimage/product";
        $arr  =  scandir($dir);
        echo "<pre>";
        $i=0;
        foreach($arr as $sku){
            //$sku = "74-413";
            if($sku != 'a' && $sku != 'b'  && $sku != 'cache'  && $sku != 'fr59618'  && $sku != 'l'  && $sku != '.'   && $sku != '..' ){
                $bucket = 'spalding-products';
                $object = "media/catalog/product/$sku";
                $configurableProduct = Mage::getModel('catalog/product')->loadByAttribute('sku', $sku);
                if(!$configurableProduct){
                    continue;
                }
                $urlKey = $configurableProduct->getUrlKey();
                $object = "media/catalog/product/$sku/$urlKey-1.jpg";
                //根据SKU 取得PRODUCT

                $response = $oss_sdk_service->is_object_exist($bucket,$object);
                if($response->status != 200){
                    echo $sku."      URL:      ".  $urlKey."              object    $object"."<br/>";
                    $i++;
//                    if($sku == '65-848y'){
                    $options = array(
                        'bucket' 	=> 'spalding-products',
                        'object'	=> "media/catalog/product/$sku",
                        'directory' => "/home/davis/Documents/spaldingimage/product/$sku",
                    );
                    $response = $oss_sdk_service->batch_upload_file($options,$urlKey,$sku);
                    //$this->_format($response);
                    if($i>200){
                        break;
                    }
//                    }

                }
            }
        }
    }


    public function productimage(){

        $oss_sdk_service = new OSS_ALIOSS();
        $oss_sdk_service->set_debug_mode(FALSE);
//设置是否打开curl调试模式
        //取得文件下所有的
        $dir = "/home/davis/Documents/spaldingimage/product";
        $arr  =  scandir($dir);
        echo "<pre>";
        $i=0;
        foreach($arr as $sku){
            //$sku = "74-413";
            if($sku != 'a' && $sku != 'b'  && $sku != 'cache'  && $sku != 'fr59618'  && $sku != 'l'  && $sku != '.'   && $sku != '..' ){
                $bucket = 'spalding-products';
                $object = "media/catalog/product/$sku";
                $configurableProduct = Mage::getModel('catalog/product')->loadByAttribute('sku', $sku);
                if(!$configurableProduct){
                    continue;
                }
                $urlKey = $configurableProduct->getUrlKey();
                $object = "media/catalog/product/$sku/$urlKey-1.jpg";
                //根据SKU 取得PRODUCT

                $response = $oss_sdk_service->is_object_exist($bucket,$object);
                if($response->status != 200){
                    echo $sku."      URL:      ".  $urlKey."              object    $object"."<br/>";
                    $i++;
//                    if($sku == '65-848y'){
                         $options = array(
                            'bucket' 	=> 'spalding-products',
                            'object'	=> "media/catalog/product/$sku",
                            'directory' => "/home/davis/Documents/spaldingimage/product/$sku",
                        );
                        $response = $oss_sdk_service->batch_upload_file($options,$urlKey,$sku);
                        //$this->_format($response);
                        if($i>200){
                            break;
                        }
//                    }

                }
            }
        }
    }
    function _format($response) {
        if($response){
            echo "<pre>";
            var_dump($response->status);
        }

    }
    public function pushImagetoOSS(){

        $oss_sdk_service = new OSS_ALIOSS();
        $oss_sdk_service->set_debug_mode(FALSE);
//设置是否打开curl调试模式
        //取得文件下所有的
        $dir = "/home/davis/Documents/spaldingimage/product";
        $arr  =  scandir($dir);
        echo "<pre>";
        foreach($arr as $sku){
            //$sku = "74-413";
            echo "$sku <br/>";
            if($sku != 'a' && $sku != 'b'  && $sku != 'cache'  && $sku != 'fr59618'  && $sku != 'l'  && $sku != '.'   && $sku != '..' ){
                $bucket = 'spalding-products';
                $object = "media/catalog/product/$sku";
                $response = $oss_sdk_service->is_object_exist($bucket,$object);
                if($response->status != 200){
                    $options = array(
                        'bucket' 	=> 'spalding-products',
                        'object'	=> "media/catalog/product/$sku",
                        'directory' => "/home/davis/Documents/spaldingimage/product/$sku",
                    );
                    $response = $oss_sdk_service->batch_upload_file($options);
                    $this->_format($response);
                }
            }
        }
    }
    public function indexAction(){
//        $this->loadLayout();
//        $this->renderLayout();
//        $str = file_get_contents("php://input");
//        if(!$str){
//            $str = '{"tid":"580415280761034","name":"东北人19680624","price":"2349.0"}';
//        }
//
//        $arr = json_decode($str,true);
//        $price = $arr['price'];
//
//
//        $content = '<img width=800 src="'.'http://s7d5.scene7.com/is/image/sneakerhead/800x318?$800x318$&$amount='.$price.'" alt="" border="0" />';
//        $content .= '<div id="homeNewarrivalTitle">
//                    <h2>NEW ARRIVALS</h2>
//                    <div class="clearfloats">&nbsp;</div>
//                    </div>';
//        Mage::getModel('cms/block')->load('tmall_1111_high')->setData('content',$content)->save();
//        echo "success";
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

    public function trackingAction(){
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
//            $background = 'style="background: none repeat scroll 0% 0% rgb(212,200,158);"';
            if($key%2 == 0){
                $background = 'style="background: none repeat scroll 0% 0% rgb(234,236,239);"';
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
//         $params = $this->getRequest()->getParams();
//         $resource = Mage::getSingleton('core/resource');
//         $writeConnection = $resource->getConnection('core_write');
//         $readConnection = $resource->getConnection('core_read');
//         $uid = 1;
//         $name = addslashes($params['name']);
//         $telephone = addslashes($params['telephone']);
//         $content = addslashes($params['content']);
//         $region_id = addslashes($params['region_id']);
//         $city_id = addslashes($params['city_id']);
//         $query = "insert into `contactus` ( `name`, `phone`, `province`,city,content,uid) values ('$name','$telephone','$region_id','$city_id','$content',$uid)";
//         $writeConnection->query($query);
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
        $appid = 'wx79873079dca36474';
        $appsecret = 'ba74acc7f680e7bbe62203815df1df41';

        /**
         * sneakerhead
         */
//        $appid = 'wx36026301d4b1cb01';
//        $appsecret = '79311ea02ea318af5f228492bf119104';

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
            /**
             * {"openid":"oYkdqs6YN282-he6W8cPxMKS2D-c","nickname":"davis","sex":1,"language":"zh_CN","city":"浦东新区","province":"上海","country":"中国","headimgurl":"http:\/\/wx.qlogo.cn\/mmopen\/ykF2ySc7iaKqibg3B0XP1nich7ia8FBYEUWLSF9owUlaTE3hMBcOEHWmicmWOhibRRibbcrbgtibWRITmS1gT8ZUgNsbMTqErKjCT4kG\/0","privilege":[]}
             */
            $userInfoObj = json_decode($useinfo);
            $openId = $userInfoObj->openid;

            //根据openid判断用户是否存在
            $customer = Mage::getModel('customer/customer')
                ->setWebsiteId(Mage::app()->getStore()->getWebsiteId())
                ->getCollection()
                ->addAttributeToSelect('weixin_openid')
                ->addAttributeToFilter('weixin_openid',$openId)->load()->getFirstItem();

            if(!$customer->getId()){
                $customers = Mage::getModel('customer/customer')->getCollection();
                $customerCount = $customers->count();
                $customerCount = $customerCount*1 + 1;
                $customer->setEmail("Spalding_"."1000$customerCount"."@spaldingchina.com.cn");
                $nickname = $userInfoObj->nickname;
                $city = $userInfoObj->city;
                $province = $userInfoObj->province;
                $country = $userInfoObj->country;
                $imageUrl = $userInfoObj->headimgurl;

                $customer->setFirstname($nickname);
                //$customer->setAvatar($profile_image_url);
//                    $customer->setLastname($lastname);
                $customer->setWeixinOpenid($openId);
                $customer->setWeixinHeadimgurl($imageUrl);
                //$customer->setLocation($location);
                //$customer->setProvince($province);
                $customer->setPassword('SpaldingOnly1Ball');
                try {
                    $customer->save();
                    $customer->setConfirmation(null);
                    $customer->save();
                }
                catch (Exception $ex) {
                    Mage::log($ex->getMessage());
                }
                Mage::getSingleton('customer/session')->loginById($customer->getId());
               // $this->_redirect('customer/account');
                $this->_loginPostRedirect();
                return;
            }else{
                Mage::getSingleton('customer/session')->loginById($customer->getId());
               // $this->_redirect('customer/account');
                $this->_loginPostRedirect();
                return;
            }


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

    protected function _loginPostRedirect()
    {
        $session = Mage::getSingleton('customer/session');

        if (!$session->getBeforeAuthUrl() || $session->getBeforeAuthUrl() == Mage::getBaseUrl()) {
            // Set default URL to redirect customer to
            $session->setBeforeAuthUrl(Mage::helper('customer')->getAccountUrl());
            // Redirect customer to the last page visited after logging in
            if ($session->isLoggedIn()) {
                if (!Mage::getStoreConfigFlag(
                    Mage_Customer_Helper_Data::XML_PATH_CUSTOMER_STARTUP_REDIRECT_TO_DASHBOARD
                )) {
                    $referer = $this->getRequest()->getParam(Mage_Customer_Helper_Data::REFERER_QUERY_PARAM_NAME);
                    if ($referer) {
                        // Rebuild referer URL to handle the case when SID was changed
                        $referer = $this->_getModel('core/url')
                            ->getRebuiltUrl( Mage::helper('core')->urlDecode($referer));
                        if ($this->_isUrlInternal($referer)) {
                            $session->setBeforeAuthUrl($referer);
                        }
                    }
                } else if ($session->getAfterAuthUrl()) {
                    $session->setBeforeAuthUrl($session->getAfterAuthUrl(true));
                }
            } else {
                $session->setBeforeAuthUrl( Mage::helper('customer')->getLoginUrl());
            }
        } else if ($session->getBeforeAuthUrl() ==  Mage::helper('customer')->getLogoutUrl()) {
            $session->setBeforeAuthUrl( Mage::helper('customer')->getDashboardUrl());
        } else {
            if (!$session->getAfterAuthUrl()) {
                $session->setAfterAuthUrl($session->getBeforeAuthUrl());
            }
            if ($session->isLoggedIn()) {
                $session->setBeforeAuthUrl($session->getAfterAuthUrl(true));
            }
        }
        $this->_redirectUrl($session->getBeforeAuthUrl(true));
    }


    public function previewAction(){
        $this->loadLayout();
        $this->renderLayout();
    }
    
    
    public function saveAuthenticationAction(){
    	$params = $this->getRequest()->getParams();
    	$resource = Mage::getSingleton('core/resource');
    	$writeConnection = $resource->getConnection('core_write');
    	
    	$name = trim($this->getRequest()->getParam('name'));
    	$tel = trim($this->getRequest()->getParam('tel'));
    	$email = trim($this->getRequest()->getParam('email'));
    	$questionType = trim($this->getRequest()->getParam('questionType'));
    	$store = trim($this->getRequest()->getParam('store'));
    	$country = trim($this->getRequest()->getParam('country'));
    	$desc = trim($this->getRequest()->getParam('desc'));
    	
    	$patternEmail = '/^[\w-]+(\.[\w-]+)*@([\w-]+\.)+[a-zA-Z]+$/'; 
    	$patternMobile = '/^(((13[0-9]{1})|(15[0-9]{1})|(18[0-9]{1})|(17[0-9]{1})|(14[0-9]{1}))+\d{8})$/';
    	$patternPhone = '/^(?:(?:0\d{2,3})-)?(?:\d{7,8})(-(?:\d{3,}))?$/';
    	if($name == '' || $questionType == '' || $store == '' || $country == '' || $desc == ''){
    		echo "信息必须填写";
    	}elseif(!preg_match($patternEmail, $email)){
    		echo "邮箱格式不正确";
    	}elseif(!preg_match($patternMobile, $tel) && !preg_match($patternPhone, $tel)){
    		echo "电话号码格式不正确";
    	}else{
    		$name = addslashes($name);
    		$tel = addslashes($tel);
    		$email = addslashes($email);
    		$questionType = addslashes($questionType);
    		$store = addslashes($store);
    		$country = addslashes($country);
    		$desc = addslashes($desc);
    		$time = date('Y-m-d H:i:s');
    		$query = "insert into `verify_code_data` ( `name`, `tel`, `email`,`question_type`,`store`,`country`,`desc`,`status`,`create_time`,`update_time`) values ('$name','$tel','$email','$questionType','$store','$country','$desc','0','$time','$time')";
    		try {
    			$writeConnection->query($query);
    			echo "success";
    		}catch (Exception $ex) {
            	echo $ex->getMessage();
            }
    	}
    	exit;
    }

}