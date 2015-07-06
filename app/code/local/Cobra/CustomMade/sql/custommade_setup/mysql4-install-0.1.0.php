<?php

$installer = $this;
$installer->startSetup();

$installer->run("
DROP TABLE IF EXISTS `{$this->getTable('custommade_info')}` ;
CREATE TABLE `{$this->getTable('custommade_info')}` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` varchar(18) NOT NULL,
  `position` varchar(1) NOT NULL,
  `type` varchar(1) NOT NULL,
  `url` varchar(800) NULL,
  `size` varchar(80) NULL,
  `create_time` varchar(20) NOT NULL,
  `update_time` varchar(20) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;
");

$installer->endSetup();
