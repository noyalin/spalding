<?php
$installer = new Mage_Customer_Model_Entity_Setup('core_setup');
$installer->startSetup();

$installer->addAttribute(
    'customer',
    'weixin_headimgurl',
    array(
        'group'                => 'Default',
        'type'                 => 'varchar',
        'label'                => 'Weixin_headimgurl',
        'input'                => 'text',
        'source'               => 'eav/entity_attribute_source_text',
        'global'               => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
        'required'             => 0,
        'default'              => 0,
        'visible'              => 0,
        'adminhtml_only'       => 1,
        'user_defined'  => 1,
    )
);

$oAttribute = Mage::getSingleton('eav/config')->getAttribute('customer', 'weixin_headimgurl');
$oAttribute->setData('used_in_forms', array('adminhtml_customer'));
$oAttribute->save();
$installer->endSetup();
