<?php
$installer = $this;

$installer->startSetup();

$installer->run("

DROP TABLE IF EXISTS `{$this->getTable('weixin_nba')}` ;
CREATE TABLE `{$this->getTable('weixin_nba')}` (
  `openid` varchar(50) NOT NULL,
  `username` varchar(50) NOT NULL,
  `telephone` varchar(20) NOT NULL,
  `city` varchar(100) NOT NULL,
  `slogan` text NOT NULL,
  `join_time` varchar(20) NOT NULL,
  PRIMARY KEY (`openid`)
) ENGINE=InnoDB   DEFAULT CHARSET=utf8;

");

$installer->endSetup();