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
        
        if(Mage::getSingleton('customer/session')->getAfterAuthUrl()){
        	Mage::getSingleton ('customer/session' )->setBeforeAuthUrl(Mage::getSingleton('customer/session')->getAfterAuthUrl());
        }
        
        $this->getResponse()->setHeader('Login-Required', 'true');
        $this->loadLayout();
        $this->_initLayoutMessages('customer/session');
        $this->_initLayoutMessages('catalog/session');
        $this->renderLayout();
    }

    public function logoutaAction()
    {
        $this->_getSession()->logout()
            ->renewSession()
            ->setBeforeAuthUrl($this->_getRefererUrl());

        Mage::getSingleton('core/session')->setCustomerId(null);
        Mage::getSingleton('core/session')->setCustomermadeAgree(null);

        Mage::log('logoutaAction $customerId=null');
    }
}
