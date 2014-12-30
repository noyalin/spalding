<?php
require_once(Mage::getModuleDir('controllers','Mage_Sales').DS.'GuestController.php');
class Devicom_Sales_GuestController extends Mage_Sales_GuestController
{
    /**
     * Order view form page
     */
    public function formAction()
    {
        if (Mage::getSingleton('customer/session')->isLoggedIn()) {
            $this->_redirect('sales/order/history/');
            return;
        }
        $this->loadLayout();
        Mage::helper('sales/guest')->getBreadcrumbs($this);
        $this->renderLayout();
    }
}
