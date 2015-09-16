<?php
class CosmoCommerce_Sns_AlipayController extends Mage_Core_Controller_Front_Action
{ 
	
	public function _construct(){
	
		return parent::_construct();
	} 
	
    public function loginAction()
    {

//建立请求
        $alipaySubmit = Mage::getModel('sns/alipaysubmit');
        $html_text = $alipaySubmit->buildRequestForm("get", "正在跳转到支付宝网站");
        echo $html_text;

    }

    public function redirectAction(){
        $alipayNotify = Mage::getModel('sns/alipaynotify');
        $verify_result = $alipayNotify->verifyReturn();
        if($verify_result) {//验证成功
            /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
            //请在这里加上商户的业务逻辑程序代码

            //——请根据您的业务逻辑来编写程序（以下代码仅作参考）——
            //获取支付宝的通知返回参数，可参考技术文档中页面跳转同步通知参数列表

            //支付宝用户号

            $userId = $_GET['user_id'];

            //授权令牌
            $token = $_GET['token'];


            //判断是否在商户网站中已经做过了这次通知返回的处理
            //如果没有做过处理，那么执行商户的业务程序
            //如果有做过处理，那么不执行商户的业务程序

            //echo "验证成功<br />";
            $customer = Mage::getModel('customer/customer')
                ->setWebsiteId(Mage::app()->getStore()->getWebsiteId())
                ->getCollection()
                ->addAttributeToSelect('alipay_user_id')
                ->addAttributeToFilter('alipay_user_id',$userId)->load()->getFirstItem();
            if(!$customer->getId()){
                $customer->setEmail("Spalding_".$userId."@spaldingchina.com.cn");
                //$lastname='lastname';
                $customer->setFirstname($_GET['real_name']);
                //$customer->setAvatar($profile_image_url);
                //$customer->setLastname($lastname);
                $customer->setAlipayUserId($userId);
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
                $this->_redirectUrl(Mage::getSingleton('customer/session')->getBeforeAuthUrl(true));
                return;
            }
        }
        else {
            //验证失败
            //如要调试，请看alipay_notify.php页面的verifyReturn函数
            echo "验证失败";
        }
    }
}