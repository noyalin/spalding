<?php
$installer = $this;

$installer->startSetup();

Mage::log("Running installer couponorder");

$installer->run("

DROP TABLE IF EXISTS `{$this->getTable('couponorder')}` ;
CREATE TABLE `{$this->getTable('couponorder')}` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` varchar(50) NOT NULL,
  `order_increment_id` varchar(50) NOT NULL,
  `coupon_code` varchar(100) NOT NULL,
  `coupon_rule_id` int(11) NOT NULL,
  `coupon_rule_name` varchar(100) NOT NULL,
  `create_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
    ");

$installer->endSetup();

