<?php
/**
 * Magento Enterprise Edition
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magento Enterprise Edition License
 * that is bundled with this package in the file LICENSE_EE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.magentocommerce.com/license/enterprise-edition
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
 * @license     http://www.magentocommerce.com/license/enterprise-edition
 */


/**
 * Customer address controller
 *
 * @category   Mage
 * @package    Mage_Customer
 * @author      Magento Core Team <core@magentocommerce.com>
 */
require_once Mage::getModuleDir('controllers', 'Mage_Customer').DS.'AddressController.php';
class Devicom_Customer_AddressController extends Mage_Customer_AddressController
{
    public function testAction(){
        echo "aa";
    }
    public function formPostAjaxAction(){
        // Save data
        if ($this->getRequest()->isPost()) {
            $customer = $this->_getSession()->getCustomer();
            $params = $this->getRequest()->getParams();
            /* @var $address Mage_Customer_Model_Address */
            $address  = Mage::getModel('customer/address');
            $addressId = $this->getRequest()->getParam('id');
            if ($addressId) {
                $existsAddress = $customer->getAddressById($addressId);
                if ($existsAddress->getId() && $existsAddress->getCustomerId() == $customer->getId()) {
                    $address->setId($existsAddress->getId());
                    $existsAddress->setRegionId($params['region_id']);
                    $existsAddress->setCityId($params['city_id']);
                    $existsAddress->setCity($params['city']);
                    $existsAddress->setDistrictId($params['district_id']);
                    $existsAddress->setDistrict($params['district']);
                    $existsAddress->save();
                    $this->_getSession()->addSuccess($this->__('The address has been saved.'));
                    $this->_redirectSuccess(Mage::getUrl('*/*/index', array('_secure'=>true)));
                    return;
                }
            }

            $errors = array();

            /* @var $addressForm Mage_Customer_Model_Form */
            $addressForm = Mage::getModel('customer/form');
            $addressForm->setFormCode('customer_address_edit')
                ->setEntity($address);

            $paraFromPost = $this->getRequest();
//            $addressData    = $addressForm->extractData($this->getRequest());
            $addressData    = array(
                'firstname' => $params['firstName'],
                'company' => false,
                'street' => array(0=>$params['street']),
                'city' => $params['city'],
                'country_id' => 'CN',
                'region' => $params['region_id'],
                'region_id' => $params['region_id'],
                'postcode' =>  $params['postcode'],
                'telephone' => $params['telephone'],
                'fax' => false,
                'district' => $params['district'],
                'district_id' =>$params['district_id'],
                'city_id' => $params['city_id'],
            );
            $addressErrors  = $addressForm->validateData($addressData);
            if ($addressErrors !== true) {
                $errors = $addressErrors;
            }

            try {
                $addressForm->compactData($addressData);
                $address->setCustomerId($customer->getId())
                    ->setIsDefaultBilling($this->getRequest()->getParam('default_billing', false))
                    ->setIsDefaultShipping($this->getRequest()->getParam('default_shipping', false));

                $addressErrors = $address->validate();
                if ($addressErrors !== true) {
                    $errors = array_merge($errors, $addressErrors);
                }

                if (count($errors) === 0) {
                    $address->save();
//                    $this->_getSession()->addSuccess($this->__('The address has been saved.'));
//                    $this->_redirectSuccess(Mage::getUrl('*/*/index', array('_secure'=>true)));
//                    return;
                } else {
                    $this->_getSession()->setAddressFormData($this->getRequest()->getPost());
                    foreach ($errors as $errorMessage) {
                        $this->_getSession()->addError($errorMessage);
                    }
                }
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->setAddressFormData($this->getRequest()->getPost())
                    ->addException($e, $e->getMessage());
            } catch (Exception $e) {
                $this->_getSession()->setAddressFormData($this->getRequest()->getPost())
                    ->addException($e, $this->__('Cannot save address.'));
            }
            $newId = $address->getId();
            $label = $address->format('oneline');
            $billingId = "billing_address_id_".$newId;
            $result['id'] = $newId;
            $result['label'] = $label;
            /*
            $return = <<<HTML
                    <div class="step_radio">
                        <input type="radio" value="$newId"  id="$billingId"  name="billing_address_id">
                        <label for="<?php echo $billingId?>">
                            <span class="city_name">
                                $label
                            </span>
                        </label>
                        <div class="btns">
                            <a class="btn edit" data-addrid="<?php echo $newId?>" href="javascript:;"><span>修改地址</span></a>
                            <a class="btn set_def" href="javascript:;"><span>设为默认</span></a>
                        </div>
                        <i class="arrow"></i>
                    </div>
HTML;
*/
            echo json_encode($result);
        }
    }

}
