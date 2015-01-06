<?php	 	 	 	 	 	 	 	 	 	 	 	 	 	 	 	 	 	 	 	 	 	 	
class CosmoCommerce_Sns_CallbackController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
		$this->loadLayout();     
		$this->renderLayout();
    }
    public function errorAction()
    {
		$this->loadLayout();     
		$this->renderLayout();
    }
    public function successAction()
    {
        $this->_redirectUrl(Mage::helper('core/url')->getHomeUrl());
        return;
//		$this->loadLayout();
//		$this->renderLayout();
    }
    public function failAction()
    {
		$this->loadLayout();     
		$this->renderLayout();
    }
    public function weiboAction()
    {
			
			
		$app_key=Mage::getStoreConfig('sns/sns_weibo/app_key');
		$debug=Mage::getStoreConfig('sns/sns_weibo/debug');
		$app_secret=Mage::getStoreConfig('sns/sns_weibo/app_secret');

		if($debug){
			error_reporting(E_ALL);
			ini_set('display_errors', true);
		}
		$callback=Mage::getUrl('sns/callback/weibo/',array('_secure'=>true));  

		$snsweibo=Mage::getModel('sns/sns');

				
		$snsweibo->setKey( $app_key , $app_secret );
		$snsweibo->set_debug( $debug );



		if (isset($_REQUEST['code'])) {
			$keys = array();

			// 验证state，防止伪造请求跨站攻击
			$state = $_REQUEST['state'];
			if ( empty($state) || $state !== Mage::getModel('core/session')->getData('weibo_state') ) {
				$this->_redirect('sns/callback/error');
				return;
			}
			Mage::getModel('core/session')->setData('weibo_state',null);

			$keys['code'] = $_REQUEST['code'];
			$keys['redirect_uri'] = $callback;
			try {
				$token = $snsweibo->getAccessToken( 'code', $keys ) ;
			} catch (OAuthException $e) {
			}
		}

		if ($token) {
			Mage::getModel('core/session')->setData('token',$token);
			Mage::getModel('core/cookie')->set('weibojs_'.$snsweibo->client_id, http_build_query($token));
	
	
	
	
	
	
			$snsweibo_login=Mage::getModel('sns/weibo');
			$snsweibo_login->setKey( $app_key , $app_secret,$token['access_token'] );
			$snsweibo_login->set_debug( false );
			
			
			$uid_get = $snsweibo_login->get_uid();
			$uid = $uid_get['uid'];
			$data = $snsweibo_login->show_user_by_id($uid);
            var_dump($data);die;
			$location = $data['location'];
			$province = $data['province'];
			$screen_name = $data['screen_name'];
			$name = $data['name'];
			//$profile_image_url = $data['profile_image_url'];
			
			if(isset($uid_get['error'])){
				Mage::getSingleton('core/session')->addError('新浪微博审核接入中，需要审核通过本app才能正常使用');
				$this->_redirect('sns/callback/fail');
				return;
			}
			
            $customer = Mage::getModel('customer/customer')
              ->setWebsiteId(Mage::app()->getStore()->getWebsiteId())
              ->getCollection()
              ->addAttributeToSelect('weibo_id')
              ->addAttributeToFilter('weibo_id',$uid)->load()->getFirstItem();
           
			if(!($customer->getId())){ 
				$customer->setEmail($uid."@weibo.com");
                
                $lastname=mb_substr($screen_name,0,1);
                $firstname=mb_substr($screen_name,1,mb_strlen($screen_name)-1);
				$customer->setFirstname($firstname);
				//$customer->setAvatar($profile_image_url);
//				$customer->setLastname($lastname);
				$customer->setWeiboId($uid);
				//$customer->setLocation($location);
				//$customer->setProvince($province);
				$customer->setPassword($token['access_token']);
				try {
					$customer->save();
					$customer->setConfirmation(null);
					$customer->save();
				}
				catch (Exception $ex) {
					Mage::log($ex->getMessage());
				}
				Mage::getSingleton('customer/session')->loginById($customer->getId());
				$this->_redirect('sns/callback/success');
				return;
			}else{
				Mage::getSingleton('customer/session')->loginById($customer->getId());
				$this->_redirect('customer/account');
				return;
			}
			
		}else{
		
			$this->_redirect('sns/callback/fail');
			return;
		}
    }
    public function qqAction()
    {
			
		$app_key=Mage::getStoreConfig('sns/sns_tweibo/app_key');
		$debug=Mage::getStoreConfig('sns/sns_tweibo/debug');
		$app_secret=Mage::getStoreConfig('sns/sns_tweibo/app_secret');

		if($debug){
			error_reporting(E_ALL);
			ini_set('display_errors', true);
		}
		$callback=Mage::getUrl('sns/callback/qq/',array('_secure'=>true));  
       
		$snsweibo=Mage::getModel('sns/tweibo');

				
		$snsweibo->setKey( $app_key , $app_secret );
		$snsweibo->set_debug( $debug );



		if (isset($_REQUEST['code'])) {
            $authorization_code=$_REQUEST['code'];

			// 验证state，防止伪造请求跨站攻击
			$state = $_REQUEST['state'];
			if ( empty($state) || $state !== Mage::getModel('core/session')->getData('qq_state') ) {
				$this->_redirect('sns/callback/error');
				return;
			}
			try {
				$r = $snsweibo->getAccessToken($authorization_code, $callback ) ;
                 
			} catch (OAuthException $e) {
                
			}
		}
		if (isset($r['access_token'])) {
            $token=$r['access_token'];
			Mage::getModel('core/session')->setData('t_access_token',$token);
			Mage::getModel('core/cookie')->set('qqjs_'.$snsweibo->client_id, http_build_query($token));
	
            
            
			$qq=Mage::getModel('sns/tweibo');
			$qq->setKey( $app_key , $app_secret,$token);
			$qq->set_debug( false );
            $jdata=$qq->getOpenid($token);
            if(preg_match("/\(([^()]+|(?R))*\)/",$jdata,$matches))
            {
                $jdedata=json_decode($matches[1]);
                $openid=($jdedata->openid);
                Mage::getModel('core/session')->setData('t_openid',$openid);
            }
            $userdata=json_decode($qq->api('user/get_user_info'));
//            echo "<pre>";
//           var_dump($userdata);
//            echo "</pre>";
//            die;
//            class stdClass#227 (17) {
//  public $ret =>
//  int(0)
//  public $msg =>
//  string(0) ""
//  public $is_lost =>
//  int(0)
//  public $nickname =>
//  string(9) "杜祥坤"
//  public $gender =>
//  string(3) "男"
//  public $province =>
//  string(6) "上海"
//  public $city =>
//  string(0) ""
//  public $figureurl =>
//  string(73) "http://qzapp.qlogo.cn/qzapp/101128192/4F89FC4B2925D6D55465756C06B82CD2/30"
//  public $figureurl_1 =>
//  string(73) "http://qzapp.qlogo.cn/qzapp/101128192/4F89FC4B2925D6D55465756C06B82CD2/50"
//  public $figureurl_2 =>
//  string(74) "http://qzapp.qlogo.cn/qzapp/101128192/4F89FC4B2925D6D55465756C06B82CD2/100"
//  public $figureurl_qq_1 =>
//  string(69) "http://q.qlogo.cn/qqapp/101128192/4F89FC4B2925D6D55465756C06B82CD2/40"
//  public $figureurl_qq_2 =>
//  string(70) "http://q.qlogo.cn/qqapp/101128192/4F89FC4B2925D6D55465756C06B82CD2/100"
//  public $is_yellow_vip =>
//  string(1) "0"
//  public $vip =>
//  string(1) "0"
//  public $yellow_vip_level =>
//  string(1) "0"
//  public $level =>
//  string(1) "0"
//  public $is_yellow_year_vip =>
//  string(1) "0"
//}

        if($userdata->ret==0){
                
                $customer = Mage::getModel('customer/customer')
                  ->setWebsiteId(Mage::app()->getStore()->getWebsiteId())
                  ->getCollection()
                  ->addAttributeToSelect('qq_id')
                  ->addAttributeToFilter('qq_id',$openid)->load()->getFirstItem();
               
                if(!$customer->getId()){ 
                    $customer->setEmail($openid."@spaldingchina.com.cn");
                    
                    
                    $lastname='lastname';
                    $firstname=$userdata->nickname;
                    $customer->setFirstname($firstname);
                    //$customer->setAvatar($profile_image_url);
//                    $customer->setLastname($lastname);
                    $customer->setQqId($openid);
                    //$customer->setLocation($location);
                    //$customer->setProvince($province);
                    $customer->setPassword($token);
                    try {
                        $customer->save();
                        $customer->setConfirmation(null);
                        $customer->save();
                    }
                    catch (Exception $ex) {
                        Mage::log($ex->getMessage());
                    }
                    Mage::getSingleton('customer/session')->loginById($customer->getId());
                    $this->_redirect('sns/callback/success');
                    return;
                }else{
                    Mage::getSingleton('customer/session')->loginById($customer->getId());
                    $this->_redirect('customer/account');
                    return;
                }
            }else{
                $this->_redirect('sns/callback/fail');
                return;
            }
		}else{
		
			$this->_redirect('sns/callback/fail');
			return;
		}
    }
}