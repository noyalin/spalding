<?php
$installer = $this;

$installer->startSetup();

Mage::log("Running installer weixinpay");

$installer->run("

DROP TABLE IF EXISTS `{$this->getTable('weixin_log')}` ;
CREATE TABLE `{$this->getTable('weixin_log')}` (
  `weixin_id` int(10) unsigned NOT NULL auto_increment,
  `log_at` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `order_id` varchar(16) NOT NULL default '',
  `trade_no` varchar(100) NOT NULL default '',
  `type` varchar(16) NOT NULL default '',
  `post_data` text,
  PRIMARY KEY  (`weixin_id`),
  KEY `log_at` (`log_at`),
  KEY `trade_no` (`trade_no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
    ");

$installer->endSetup();

