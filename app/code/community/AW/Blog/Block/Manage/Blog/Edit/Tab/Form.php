<?php

/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/LICENSE-L.txt
 *
 * @category   AW
 * @package    AW_Blog
 * @copyright  Copyright (c) 2009-2010 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/LICENSE-L.txt
 */
class AW_Blog_Block_Manage_Blog_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form {

    protected function _prepareForm() {

        $form = new Varien_Data_Form();
        $this->setForm($form);
        $fieldset = $form->addFieldset('blog_form', array('legend' => Mage::helper('blog')->__('Post information')));

        $fieldset->addField('title', 'text', array(
            'label' => Mage::helper('blog')->__('Title'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'title',
        ));

        $fieldset->addField('identifier', 'text', array(
            'label' => Mage::helper('blog')->__('Identifier'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'identifier',
            'class' => 'aw-blog-validate-identifier',
            'after_element_html' => '<span class="hint">' . Mage::helper('blog')->__('(eg: domain.com/blog/identifier)') . '</span>'
            . "<script>
                    Validation.add(
                    'aw-blog-validate-identifier', 
                    '" . addslashes(Mage::helper('blog')->__("Please use only letters (a-z or A-Z), numbers (0-9) or symbols '-' and '_' in this field")) . "',
                    function(v, elm) {
                                    var regex = new RegExp(/^[a-zA-Z0-9_-]+$/);
                                    return v.match(regex);
                         }
                    );
                    </script>",)
        );

        /**
         * Check is single store mode
         */
        if (!Mage::app()->isSingleStoreMode()) {
            $fieldset->addField('store_id', 'multiselect', array(
                'name' => 'stores[]',
                'label' => Mage::helper('cms')->__('Store View'),
                'title' => Mage::helper('cms')->__('Store View'),
                'required' => true,
                'values' => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(false, true),
            ));
        }

        $categories = array();
        $collection = Mage::getModel('blog/cat')->getCollection()->setOrder('sort_order', 'asc');
        foreach ($collection as $cat) {
            $categories[] = ( array(
                'label' => (string) $cat->getTitle(),
                'value' => $cat->getCatId()
                    ));
        }

        $fieldset->addField('cat_id', 'multiselect', array(
            'name' => 'cats[]',
            'label' => Mage::helper('blog')->__('Category'),
            'title' => Mage::helper('blog')->__('Category'),
            'required' => true,
            'style' => 'height:100px',
            'values' => $categories,
        ));

        $fieldset->addField('status', 'select', array(
            'label' => Mage::helper('blog')->__('Status'),
            'name' => 'status',
            'values' => array(
                array(
                    'value' => 1,
                    'label' => Mage::helper('blog')->__('Enabled'),
                ),
                array(
                    'value' => 2,
                    'label' => Mage::helper('blog')->__('Disabled'),
                ),
                array(
                    'value' => 3,
                    'label' => Mage::helper('blog')->__('Hidden'),
                ),
            ),
            'after_element_html' => '<span class="hint">' . Mage::helper('blog')->__('(Hidden Pages will not show in the blog but can still be accessed directly)') . '</span>',
        ));

        $fieldset->addField('comments', 'select', array(
            'label' => Mage::helper('blog')->__('Enable Comments'),
            'name' => 'comments',
            'values' => array(
                array(
                    'value' => 0,
                    'label' => Mage::helper('blog')->__('Enabled'),
                ),
                array(
                    'value' => 1,
                    'label' => Mage::helper('blog')->__('Disabled'),
                ),
            ),
            'after_element_html' => '<span class="hint">' . Mage::helper('blog')->__('Disabling will close the post to new comments') . '</span>',
        ));

        $fieldset->addField('tags', 'text', array(
            'name' => 'tags',
            'label' => Mage::helper('blog')->__('Tags'),
            'title' => Mage::helper('blog')->__('tags'),
            'style' => 'width:700px;',
            'after_element_html' => Mage::helper('blog')->__('Use space or comma as separators'),
        ));

        try {
            $config = Mage::getSingleton('cms/wysiwyg_config')->getConfig();
            $config->setData(Mage::helper('blog')->recursiveReplace(
                            '/blog_admin/', '/' . (string) Mage::app()->getConfig()->getNode('admin/routers/adminhtml/args/frontName') . '/', $config->getData()
                    )
            );
        } catch (Exception $ex) {
            $config = null;
        }

        if (Mage::getStoreConfig('blog/blog/useshortcontent')) {
            $fieldset->addField('short_content', 'editor', array(
                'name' => 'short_content',
                'label' => Mage::helper('blog')->__('Short Content'),
                'title' => Mage::helper('blog')->__('Short Content'),
                'style' => 'width:700px; height:100px;',
                'config' => $config,
            ));
        }
        $fieldset->addField('post_content', 'editor', array(
            'name' => 'post_content',
            'label' => Mage::helper('blog')->__('Content'),
            'title' => Mage::helper('blog')->__('Content'),
            'style' => 'width:700px; height:500px;',
            'config' => $config
        ));

        if (Mage::getStoreConfig('blog/blog/useshortcontent')) {
            $fieldset->addField('mobile_short_content', 'editor', array(
                'name' => 'mobile_short_content',
                'label' => Mage::helper('blog')->__('Mobile Short Content'),
                'title' => Mage::helper('blog')->__('Mobile Short Content'),
                'style' => 'width:700px; height:100px;',
                'config' => $config,
            ));
        }
        $fieldset->addField('mobile_content', 'editor', array(
            'name' => 'mobile_content',
            'label' => Mage::helper('blog')->__('Mobile Content'),
            'title' => Mage::helper('blog')->__('Mobile Content'),
            'style' => 'width:700px; height:500px;',
            'config' => $config
        ));

        if (Mage::getSingleton('adminhtml/session')->getBlogData()) {
            $form->setValues(Mage::getSingleton('adminhtml/session')->getBlogData());
            Mage::getSingleton('adminhtml/session')->setBlogData(null);
        } elseif (Mage::registry('blog_data')) {
            Mage::registry('blog_data')->setTags(Mage::helper('blog')->convertSlashes(Mage::registry('blog_data')->getTags()));
            $form->setValues(Mage::registry('blog_data')->getData());
        }
        return parent::_prepareForm();
    }

}
