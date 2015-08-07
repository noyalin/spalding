<?php

$installer = $this;
$installer->startSetup();

$installer->run("
DROP TABLE IF EXISTS `{$this->getTable('devicom_physical_inventory')}` ;
CREATE TABLE `{$this->getTable('devicom_physical_inventory')}` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `sku` varchar(50) NOT NULL,
  `qty` int(10)  NOT NULL DEFAULT 0,
  `create_time` timestamp NOT NULL,
  `modify_time` timestamp NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");


$installer->endSetup();
