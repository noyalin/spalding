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
 * Customer address edit block
 *
 * @category   Mage
 * @package    Mage_Customer
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Customer_Block_Address_Edit extends Mage_Directory_Block_Data
{
    protected $_address;
    protected $_countryCollection;
    protected $_regionCollection;

    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $this->_address = Mage::getModel('customer/address');

        // Init address object
        if ($id = $this->getRequest()->getParam('id')) {
            $this->_address->load($id);
            if ($this->_address->getCustomerId() != Mage::getSingleton('customer/session')->getCustomerId()) {
                $this->_address->setData(array());
            }
        }

        if (!$this->_address->getId()) {
            $this->_address->setPrefix($this->getCustomer()->getPrefix())
                ->setFirstname($this->getCustomer()->getFirstname())
                ->setMiddlename($this->getCustomer()->getMiddlename())
                ->setLastname($this->getCustomer()->getLastname())
                ->setSuffix($this->getCustomer()->getSuffix());
        }

        if ($headBlock = $this->getLayout()->getBlock('head')) {
            $headBlock->setTitle($this->getTitle());
        }

        if ($postedData = Mage::getSingleton('customer/session')->getAddressFormData(true)) {
            $this->_address->addData($postedData);
        }
    }

    /**
     * Generate name block html
     *
     * @return string
     */
    public function getNameBlockHtml()
    {
        $nameBlock = $this->getLayout()
            ->createBlock('customer/widget_name')
            ->setObject($this->getAddress());

        return $nameBlock->toHtml();
    }

    public function getTitle()
    {
        if ($title = $this->getData('title')) {
            return $title;
        }
        if ($this->getAddress()->getId()) {
            $title = Mage::helper('customer')->__('Edit Address');
        }
        else {
            $title = Mage::helper('customer')->__('Add New Address');
        }
        return $title;
    }

    public function getBackUrl()
    {
        if ($this->getData('back_url')) {
            return $this->getData('back_url');
        }

        if ($this->getCustomerAddressCount()) {
            return $this->getUrl('customer/address');
        } else {
            return $this->getUrl('customer/account/');
        }
    }

    public function getSaveUrl()
    {
        return Mage::getUrl('customer/address/formPost', array('_secure'=>true, 'id'=>$this->getAddress()->getId()));
    }

    public function getAddress()
    {
        return $this->_address;
    }

    public function getCountryId()
    {
        if ($countryId = $this->getAddress()->getCountryId()) {
            return $countryId;
        }
        return parent::getCountryId();
    }

    public function getRegionId()
    {
        return $this->getAddress()->getRegionId();
    }

    public function getCustomerAddressCount()
    {
        return count(Mage::getSingleton('customer/session')->getCustomer()->getAddresses());
    }

    public function canSetAsDefaultBilling()
    {
        if (!$this->getAddress()->getId()) {
            return $this->getCustomerAddressCount();
        }
        return !$this->isDefaultBilling();
    }

    public function canSetAsDefaultShipping()
    {
        if (!$this->getAddress()->getId()) {
            return $this->getCustomerAddressCount();
        }
        return !$this->isDefaultShipping();;
    }

    public function isDefaultBilling()
    {
        $defaultBilling = Mage::getSingleton('customer/session')->getCustomer()->getDefaultBilling();
        return $this->getAddress()->getId() && $this->getAddress()->getId() == $defaultBilling;
    }

    public function isDefaultShipping()
    {
        $defaultShipping = Mage::getSingleton('customer/session')->getCustomer()->getDefaultShipping();
        return $this->getAddress()->getId() && $this->getAddress()->getId() == $defaultShipping;
    }

    public function getCustomer()
    {
        return Mage::getSingleton('customer/session')->getCustomer();
    }

    public function getBackButtonUrl()
    {
        if ($this->getCustomerAddressCount()) {
            return $this->getUrl('customer/address');
        } else {
            return $this->getUrl('customer/account/');
        }
    }

    public function getCityList($id=null){
        if(!$id){
            $id = 485;
        }
        $resource = Mage::getSingleton('core/resource');
        $writeConnection = $resource->getConnection('core_read');
        // Select all simple products with a qty > 0
        $query = "SELECT city.* FROM `directory_country_city` as city
    join directory_country_region as region on (region.code = city.provincecode)
    WHERE region.region_id=$id";
        $results = $writeConnection->fetchAll($query);
        $return = "<option value=''>?????????</option>";
        foreach($results as $each){
            $code = $each['code'];
            $name = $each['name'];
            $return .= "<option value='$code'>$name</option>";
        }
        echo $return;
    }
    public function getDistrictList($id=null){
        if(!$id){
            $id = 110100;
        }

        $resource = Mage::getSingleton('core/resource');
        $writeConnection = $resource->getConnection('core_read');

        // Select all simple products with a qty > 0
        $query = "SELECT district.* FROM `directory_country_district` as district
join directory_country_city as city on (city.code = district.citycode)
WHERE city.code=$id";
        $results = $writeConnection->fetchAll($query);
        $return = "<option value=''>?????????</option>";
        foreach($results as $each){
            $code = $each['code'];
            $name = $each['name'];
            $return .= "<option value='$code'>$name</option>";
        }
        echo $return;
    }
}
