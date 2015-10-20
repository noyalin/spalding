<?php
$installer = $this;

$installer->startSetup();

$installer->run("
DROP TABLE IF EXISTS `{$this->getTable('custommade_info')}` ;
CREATE TABLE `{$this->getTable('custommade_info')}` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` varchar(18) NOT NULL,
  `sku` varchar(50) NOT NULL,
  `type_p1` int(1) DEFAULT NULL,
  `msg1_p1` varchar(800) DEFAULT NULL,
  `msg2_p1` varchar(800) DEFAULT NULL,
  `msg3_p1` varchar(800) DEFAULT NULL,
  `msg4_p1` varchar(800) DEFAULT NULL,
  `msg5_p1` varchar(800) DEFAULT NULL,
  `msg6_p1` varchar(800) DEFAULT NULL,
  `type_p2` int(1) DEFAULT NULL,
  `msg1_p2` varchar(800) DEFAULT NULL,
  `msg2_p2` varchar(800) DEFAULT NULL,
  `msg3_p2` varchar(800) DEFAULT NULL,
  `msg4_p2` varchar(800) DEFAULT NULL,
  `msg5_p2` varchar(800) DEFAULT NULL,
  `msg6_p2` varchar(800) DEFAULT NULL,
  `user1_approve` int(1) DEFAULT 0,
  `user1_reason` varchar(800) DEFAULT NULL,
  `user2_approve` int(1) DEFAULT 0,
  `user2_reason` varchar(800) DEFAULT NULL,
  `user3_approve` int(1) DEFAULT 0,
  `user3_reason` varchar(800) DEFAULT NULL,
  `user4_approve` int(1) DEFAULT 0,
  `user4_reason` varchar(800) DEFAULT NULL,
  `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `update_time` timestamp NULL DEFAULT NULL,
  `status` int(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;
");

$installer->endSetup();