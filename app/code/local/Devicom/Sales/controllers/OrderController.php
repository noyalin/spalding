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
 * @package     Mage_Sales
 * @copyright   Copyright (c) 2013 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Sales orders controller
 *
 * @category   Mage
 * @package    Mage_Sales
 * @author      Magento Core Team <core@magentocommerce.com>
 */
require_once(Mage::getModuleDir('controllers','Mage_Sales').DS.'OrderController.php');
class Devicom_Sales_OrderController extends Mage_Sales_OrderController
{
    public  function _cancelAction()
    {

        $orderId = (int) $this->getRequest()->getParam('order_id');
        $order = Mage::getModel('sales/order')->load($orderId);
        if (!$this->_loadValidOrder()) {
            return;
        }

        if ($order->canCancel())
        {
            $order->setState('canceled');
            $order->setStatus('canceled');
            $order->save();
            $order->sendOrderUpdateEmail();
        }

        $session = Mage::getSingleton('core/session');
        $session->addSuccess('The order has been canceld.');
    }

    public function trackingAction()
    {
        $this->loadLayout();

        $this->getLayout()->getBlock('head')->setTitle('物流查询');

        if ($block = $this->getLayout()->getBlock('customer.account.link.back')) {
            $block->setRefererUrl($this->_getRefererUrl());
        }
        $this->renderLayout();

    }
    
    public function checkstatusAction(){
    	$orderId = (int) $this->getRequest()->getParam('order_id');
    	$order = Mage::getModel('sales/order')->load($orderId);
    	echo json_encode($order->getStatus());
    	exit;
    }
}
