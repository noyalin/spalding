<?php
$installer = $this;

$installer->startSetup();

$installer->run("
DROP TABLE IF EXISTS `{$this->getTable('customclothes_order')}` ;
CREATE TABLE `{$this->getTable('customclothes_order')}` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` varchar(18) NOT NULL,
  `sku` varchar(50) NOT NULL,
  `font` int(1) DEFAULT NULL,
  `font_style` int(1) DEFAULT NULL,
  `team_name` varchar(20) DEFAULT NULL,
  `color` varchar(100) DEFAULT NULL,
  `font_color` varchar(100) DEFAULT NULL,
  `result_image` text,
  `order_count` varchar(800) DEFAULT NULL,
  `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `update_time` timestamp NULL DEFAULT NULL,
  `double` varchar(100) DEFAULT NULL,
  `status` int(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;


DROP TABLE IF EXISTS `{$this->getTable('customclothes_order_info')}` ;
CREATE TABLE `{$this->getTable('customclothes_order_info')}` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` varchar(18) NOT NULL,
  `clothes_sku` varchar(50) DEFAULT NULL,
  `pants_sku` varchar(50) DEFAULT NULL,
  `member_name` varchar(100) DEFAULT NULL,
  `member_number` varchar(100) DEFAULT NULL,
  `clothes_size` varchar(20) DEFAULT NULL,
  `pants_size` varchar(20) DEFAULT NULL,
  `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

DROP TABLE IF EXISTS `{$this->getTable('customclothes_temp')}` ;
CREATE TABLE `{$this->getTable('customclothes_temp')}` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` varchar(18) NOT NULL,
  `sku` varchar(50) NOT NULL,
  `font` int(1) DEFAULT NULL,
  `font_style` int(1) DEFAULT NULL,
  `team_name` varchar(100) DEFAULT NULL,
  `color` varchar(100) DEFAULT NULL,
  `font_color` varchar(100) DEFAULT NULL,
  `double` varchar(100) DEFAULT NULL,
  `result_image` text,
  `order_count` varchar(20) DEFAULT NULL,
  `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

DROP TABLE IF EXISTS `{$this->getTable('customclothes_temp_info')}` ;
CREATE TABLE `{$this->getTable('customclothes_temp_info')}` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `clothes_sku` varchar(50) DEFAULT NULL,
  `pants_sku` varchar(50) DEFAULT NULL,
  `customer_id` varchar(18) NOT NULL,
  `member_name` varchar(100) DEFAULT NULL,
  `member_number` varchar(100) DEFAULT NULL,
  `clothes_size` varchar(20) DEFAULT NULL,
  `pants_size` varchar(20) DEFAULT NULL,
  `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

");

$installer->endSetup();