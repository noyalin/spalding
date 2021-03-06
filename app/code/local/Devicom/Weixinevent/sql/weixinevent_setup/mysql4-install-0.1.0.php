<?php
$installer = $this;

$installer->startSetup();

Mage::log("Running installer weixinpay");

$installer->run("

DROP TABLE IF EXISTS `{$this->getTable('weixin_appset')}` ;
CREATE TABLE `{$this->getTable('weixin_appset')}` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `appid` varchar(18) NOT NULL,
  `appsecret` varchar(32) NOT NULL,
  `access_token` varchar(800)  NULL,
  `create_time` varchar(80)  NULL,
  `ticket` varchar(500)  NULL,
  `ticket_time` varchar(80)  NULL,
  `web_access_token` varchar(800)  NULL,
  `refresh_token` varchar(800)  NULL,
  `web_createtime` int(12)  NULL,
  `refresh_token_createtime` int(12)  NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `{$this->getTable('weixin_promotion')}` ;
CREATE TABLE `{$this->getTable('weixin_promotion')}` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` varchar(50) NOT NULL,
  `open_id` varchar(50) NOT NULL,
  `act_id` varchar(50) NOT NULL,
  `sponsor_flag` int(1)  NULL,
  `operation` varchar(800)  NULL,
  `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `update_time` timestamp NULL DEFAULT NULL,
  `telephone_no` varchar(50) NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `{$this->getTable('weixin_captcha')}` ;
CREATE TABLE `{$this->getTable('weixin_captcha')}` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `open_id` varchar(50) NOT NULL,
  `telephone_no` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `{$this->getTable('weixin_coupon')}` ;
CREATE TABLE `{$this->getTable('weixin_coupon')}` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(50) NOT NULL,
  `types` varchar(50) NOT NULL,
  `status` int(1) NOT NULL,
  `start_time` varchar(14) NOT NULL,
  `end_time` varchar(14) NOT NULL,
  `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `update_time` timestamp NULL DEFAULT NULL,
  `uid` varchar(50) NULL,
  `act_id` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
    ");

$installer->run("
Insert into `{$this->getTable('weixin_appset')}`  (`id`, `appid`, `appsecret`, `access_token`, `create_time`, `ticket`, `ticket_time`, `web_access_token`, `refresh_token`, `web_createtime`, `refresh_token_createtime`) VALUES (NULL, 'wx79873079dca36474', 'ba74acc7f680e7bbe62203815df1df41', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
");


$installer->endSetup();

