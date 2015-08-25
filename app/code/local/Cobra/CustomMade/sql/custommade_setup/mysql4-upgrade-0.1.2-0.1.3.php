<?php
$installer = $this;

$installer->startSetup();

$installer->run("
DROP TABLE IF EXISTS `{$this->getTable('custommade_customer')}` ;
CREATE TABLE `{$this->getTable('custommade_customer')}` (
  `customer_id` int(20) NOT NULL AUTO_INCREMENT,
  `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`customer_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

DROP TABLE IF EXISTS `{$this->getTable('custommade_session')}` ;
CREATE TABLE `{$this->getTable('custommade_session')}` (
  `session_id` int(20) NOT NULL AUTO_INCREMENT,
  `customer_id` int(20) NOT NULL,
  `sku` varchar(50) NOT NULL,
  `custom_status` int(1) DEFAULT NULL,
  `pos` int(1) DEFAULT NULL,
  `type_p1` int(1) DEFAULT NULL,
  `content1_p1` varchar(800) DEFAULT NULL,
  `content2_p1` varchar(800) DEFAULT NULL,
  `content3_p1` varchar(800) DEFAULT NULL,
  `content4_p1` varchar(800) DEFAULT NULL,
  `type_p2` int(1) DEFAULT NULL,
  `content1_p2` varchar(800) DEFAULT NULL,
  `content2_p2` varchar(800) DEFAULT NULL,
  `content3_p2` varchar(800) DEFAULT NULL,
  `content4_p2` varchar(800) DEFAULT NULL,
  `test_mode` int(1) DEFAULT NULL,
  `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`session_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
");

$installer->endSetup();
