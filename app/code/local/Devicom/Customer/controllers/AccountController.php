<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Customer
 * @copyright   Copyright (c) 2013 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Customer account controller
 *
 * @category   Mage
 * @package    Mage_Customer
 * @author      Magento Core Team <core@magentocommerce.com>
 */
require_once Mage::getModuleDir('controllers', 'Mage_Customer').DS.'AccountController.php';
class Devicom_Customer_AccountController extends Mage_Customer_AccountController
{
    public function loginAction()
    {
        if ($this->_getSession()->isLoggedIn()) {
            $this->_redirect('*/*/');
            return;
        }
        /**
         * 判断当前是否为手机
         */
        $storeCode = Mage::app()->getStore()->getCode();
        if($storeCode == 'sneakerhead_cn_mobile'){
            //需要通过微信获取用户信息
            $this->authWeixin();
        }


        $this->getResponse()->setHeader('Login-Required', 'true');
        $this->loadLayout();
        $this->_initLayoutMessages('customer/session');
        $this->_initLayoutMessages('catalog/session');
        $this->renderLayout();
    }

    public function authWeixin(){
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
                $customer->setPassword('111111');
                try {
                    $customer->save();
                    $customer->setConfirmation(null);
                    $customer->save();
                }
                catch (Exception $ex) {
                    Mage::log($ex->getMessage());
                }
                Mage::getSingleton('customer/session')->loginById($customer->getId());
                mage :: log(" create user id ".$customer->getId());
                $this->_redirect('customer/account');
                return;
            }else{
                Mage::getSingleton('customer/session')->loginById($customer->getId());
                mage :: log(" existed user id ".$customer->getId());
                $this->_redirect('customer/account');
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

}
