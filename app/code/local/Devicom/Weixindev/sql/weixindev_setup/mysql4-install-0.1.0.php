<?php
$installer = $this;

$installer->startSetup();

Mage::log("Running installer weixin dev");

$installer->run("

DROP TABLE IF EXISTS `{$this->getTable('weixin_user')}` ;
CREATE TABLE `{$this->getTable('weixin_user')}` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `openid` varchar(500) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `orderid` varchar(100) NOT NULL,
  `createtime` int(11) NOT NULL,
  `status` int(11) NOT NULL DEFAULT '1',
  `TrackingID` varchar(50) NOT NULL,
  `sessionlast` varchar(100) DEFAULT NULL,
  `session_identity_num` varchar(200) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `openid` (`openid`(255))
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1  ;

DROP TABLE IF EXISTS `{$this->getTable('weixin_identity')}` ;
CREATE TABLE `{$this->getTable('weixin_identity')}` (
   `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `username` varchar(50) NOT NULL,
  `identity_number` char(18) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB   DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

");


$installer->endSetup();

