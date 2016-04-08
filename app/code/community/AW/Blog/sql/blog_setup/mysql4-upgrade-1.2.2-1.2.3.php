<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

try {
    $installer->run("
	ALTER TABLE {$this->getTable('blog/blog')} ADD `mobile_short_content` TEXT NOT NULL;
	ALTER TABLE {$this->getTable('blog/blog')} ADD `mobile_content` TEXT NOT NULL;
	");
} catch (Exception $e) {

}

$installer->endSetup();
